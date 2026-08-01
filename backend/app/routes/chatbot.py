import re
import time

import requests
from flask import Blueprint, current_app, jsonify, request

from ..extensions import get_db
from ..utils import oid

chatbot_bp = Blueprint("chatbot", __name__, url_prefix="/api/chatbot")

STOPWORDS = {
    "le", "la", "les", "un", "une", "des", "du", "de", "et", "en", "au", "aux",
    "je", "tu", "il", "elle", "nous", "vous", "ils", "elles", "me", "te", "se",
    "mon", "ma", "mes", "ton", "ta", "tes", "son", "sa", "ses", "leur", "leurs",
    "que", "qui", "quoi", "dont", "où", "mais", "donc", "ni", "car",
    "est", "sont", "été", "avoir", "être", "ont", "the", "of", "in", "is",
    "pour", "sur", "par", "avec", "sans", "sous", "dans", "entre", "vers",
    "plus", "très", "tout", "bien", "aussi", "même", "comme", "quand", "si",
    "quel", "quelle", "quels", "quelles", "combien", "comment", "pourquoi",
    "cette", "ceux", "celles", "ici", "voici", "voilà",
}


def _extract_keywords(text):
    words = re.findall(r"[a-zàâäéèêëîïôöùûüç]{3,}", text.lower())
    return list(dict.fromkeys(w for w in words if w not in STOPWORDS))


def _build_context(question, user_id):
    q = question.lower()
    parts, chunks = [], []
    db = get_db()

    if user_id and re.search(r"mes?\s+(info|profil|donn|congé|contrat)|mon\s+(contrat|profil|salaire)|moi|je\s+suis", q):
        me = db.users.find_one({"_id": oid(user_id)})
        if me:
            block = (
                "UTILISATEUR CONNECTÉ :\n"
                f"Nom : {me.get('prenom','')} {me.get('nom','')}\n"
                f"Email : {me.get('email','')}\n"
                f"Rôle : {me.get('role','')}\n"
                f"Poste : {me.get('poste') or 'N/A'}\n"
                f"Département : {me.get('departement') or 'N/A'}\n"
                f"Téléphone : {me.get('telephone') or 'N/A'}"
            )
            parts.append(block)
            chunks.append({"label": "PROFIL_UTILISATEUR", "content": block})

    employes = list(db.users.find({}))
    if employes:
        lines = [
            f"{u.get('prenom','')} {u.get('nom','')} — {u.get('poste') or 'N/A'} — "
            f"{u.get('departement') or 'N/A'} — {u.get('email','')}"
            for u in employes
        ]
        block = f"EMPLOYÉS ({len(lines)}) :\n" + "\n".join(lines)
        parts.append(block)
        chunks.append({"label": "EMPLOYES", "content": block})

    if re.search(r"cong[eé]|absence|vacance", q):
        conges = list(db.conges.find({}).limit(20))
        if conges:
            emp_map = {u["_id"]: u for u in db.users.find({"_id": {"$in": [c.get("employeId") for c in conges]}})}
            lines = []
            for c in conges:
                emp = emp_map.get(c.get("employeId"))
                nom = f"{emp.get('prenom','')} {emp.get('nom','')}" if emp else "?"
                d1 = c.get("dateDebut")
                d2 = c.get("dateFin")
                lines.append(
                    f"{nom} — {c.get('type')} du {d1.strftime('%d/%m/%Y') if d1 else '?'} "
                    f"au {d2.strftime('%d/%m/%Y') if d2 else '?'} — {c.get('statut')}"
                )
            block = "CONGÉS RÉCENTS :\n" + "\n".join(lines)
            parts.append(block)
            chunks.append({"label": "CONGES", "content": block})

    if re.search(r"projet|tâche|task|mission", q):
        projets = list(db.projets.find({}))
        if projets:
            lines = [f"{p.get('nom')} — statut: {p.get('statut') or 'N/A'}" for p in projets]
            block = "PROJETS :\n" + "\n".join(lines)
            parts.append(block)
            chunks.append({"label": "PROJETS", "content": block})

    return "\n\n".join(parts), chunks


def _call_groq(question, context, api_key):
    system = (
        "Tu es un assistant RH intelligent pour une application de gestion des ressources humaines. "
        "Réponds en français de manière concise et professionnelle. "
        "Utilise les données suivantes pour répondre :\n\n" + context
    )
    try:
        resp = requests.post(
            "https://api.groq.com/openai/v1/chat/completions",
            headers={"Content-Type": "application/json", "Authorization": f"Bearer {api_key}"},
            json={
                "model": "llama-3.1-8b-instant",
                "messages": [
                    {"role": "system", "content": system},
                    {"role": "user", "content": question},
                ],
                "max_tokens": 512,
                "temperature": 0.4,
            },
            timeout=30,
        )
    except requests.RequestException as exc:
        return f"Désolé, je ne peux pas répondre pour le moment. (erreur réseau: {exc})"

    if resp.status_code != 200:
        return f"Désolé, je ne peux pas répondre pour le moment. (erreur {resp.status_code})"

    data = resp.json()
    return data.get("choices", [{}])[0].get("message", {}).get("content", "Je n'ai pas pu générer une réponse.")


def _log_metrics(question, answer, context, chunks, elapsed):
    answer_lower = answer.lower()
    keywords = _extract_keywords(question)
    matched = [w for w in keywords if w in answer_lower]
    precision = round(len(matched) / len(keywords) * 100, 1) if keywords else 0.0

    used_labels = []
    for chunk in chunks:
        chunk_words = _extract_keywords(chunk["content"])
        if any(w in answer_lower for w in chunk_words):
            used_labels.append(chunk["label"])
    recall = round(len(used_labels) / len(chunks) * 100, 1) if chunks else 0.0

    f1 = round(2 * precision * recall / (precision + recall), 1) if (precision + recall) > 0 else 0.0
    perf = "🟢 Rapide" if elapsed <= 5 else ("🟡 Correct" if elapsed <= 15 else "🔴 Lent")

    current_app.logger.info(
        "[RAG EVAL] q=%.80s | temps=%.3fs (%s) | précision=%.1f%% (%d/%d) | "
        "rappel=%.1f%% (%d/%d) | f1=%.1f%%",
        question, elapsed, perf, precision, len(matched), len(keywords),
        recall, len(used_labels), len(chunks), f1,
    )


@chatbot_bp.post("/chat")
def chat():
    data = request.get_json(silent=True) or {}
    question = (data.get("question") or "").strip()
    user_id = data.get("userId")

    if not question:
        return jsonify({"error": "Question vide"}), 400

    context, chunks = _build_context(question, user_id)

    t_start = time.time()
    answer = _call_groq(question, context, current_app.config["GROQ_API_KEY"])
    elapsed = round(time.time() - t_start, 3)

    _log_metrics(question, answer, context, chunks, elapsed)

    return jsonify({"answer": answer})
