import json
import os
import uuid
from datetime import datetime

import requests
from flask import Blueprint, current_app, jsonify, request
from werkzeug.utils import secure_filename

from ..extensions import get_db
from ..serializers import candidature_to_dict
from ..utils import oid, roles_required

recrutement_bp = Blueprint("recrutement", __name__, url_prefix="/api/recrutement")

ALLOWED_CV_EXT = {"pdf", "png", "jpg", "jpeg", "webp"}
IMAGE_EXT = {"png", "jpg", "jpeg", "webp"}


def _ocr_extract(file_bytes, ext, api_key):
    import base64

    b64 = base64.b64encode(file_bytes).decode()
    if ext in IMAGE_EXT:
        mime = "image/png" if ext == "png" else f"image/{ext}"
        document = {"type": "image_url", "image_url": f"data:{mime};base64,{b64}"}
    else:
        document = {"type": "document_url", "document_url": f"data:application/pdf;base64,{b64}"}

    resp = requests.post(
        "https://api.mistral.ai/v1/ocr",
        headers={"Authorization": f"Bearer {api_key}", "Content-Type": "application/json"},
        json={"model": "mistral-ocr-latest", "document": document},
        timeout=60,
    )
    if resp.status_code != 200:
        raise RuntimeError(f"Mistral OCR a échoué ({resp.status_code}): {resp.text[:200]}")

    data = resp.json()
    pages = data.get("pages", [])
    return "\n\n".join(p.get("markdown", "") for p in pages).strip()


def _groq_analyze(cv_text, poste, description, api_key):
    system = (
        "Tu es un assistant de recrutement RH. Analyse le CV fourni pour le poste indiqué. "
        "Réponds UNIQUEMENT avec un objet JSON valide, sans texte autour, au format exact suivant:\n"
        "{\n"
        '  "candidat": {"nom": "", "email": "", "telephone": "", "competences": [], '
        '"experienceAnnees": 0, "formation": "", "resume": ""},\n'
        '  "adequation": {"correspond": true, "score": 0, "justification": ""}\n'
        "}\n"
        "Le score est un entier de 0 à 100 représentant l'adéquation du candidat au poste."
    )
    user = f"POSTE: {poste}\nEXIGENCES: {description}\n\nCONTENU DU CV:\n{cv_text}"

    resp = requests.post(
        "https://api.groq.com/openai/v1/chat/completions",
        headers={"Authorization": f"Bearer {api_key}", "Content-Type": "application/json"},
        json={
            "model": "llama-3.1-8b-instant",
            "messages": [{"role": "system", "content": system}, {"role": "user", "content": user}],
            "temperature": 0.2,
            "max_tokens": 800,
            "response_format": {"type": "json_object"},
        },
        timeout=30,
    )
    if resp.status_code != 200:
        raise RuntimeError(f"L'analyse Groq a échoué ({resp.status_code}): {resp.text[:200]}")

    content = resp.json()["choices"][0]["message"]["content"]
    return json.loads(content)


@recrutement_bp.post("/analyser")
@roles_required("ADMIN", "RH")
def analyser_cv():
    file = request.files.get("file")
    poste = request.form.get("poste", "")
    description = request.form.get("description", "")

    if not file or not file.filename:
        return jsonify({"message": "Aucun fichier."}), 400
    if not poste:
        return jsonify({"message": "Le poste est requis."}), 400

    original = secure_filename(file.filename)
    ext = original.rsplit(".", 1)[-1].lower() if "." in original else ""
    if ext not in ALLOWED_CV_EXT:
        return jsonify({"message": "Format de fichier non supporté (PDF, PNG, JPG, WEBP)."}), 400

    file_bytes = file.read()

    upload_dir = os.path.abspath(os.path.join(current_app.config["UPLOAD_DIR"], "..", "cv"))
    os.makedirs(upload_dir, exist_ok=True)
    filename = f"{uuid.uuid4().hex}.{ext}"
    with open(os.path.join(upload_dir, filename), "wb") as f:
        f.write(file_bytes)

    db = get_db()
    doc = {
        "filename": original,
        "cvPath": f"uploads/cv/{filename}",
        "poste": poste,
        "description": description,
        "candidat": None,
        "adequation": None,
        "error": None,
        "createdAt": datetime.utcnow(),
    }

    try:
        cv_text = _ocr_extract(file_bytes, ext, current_app.config["MISTRAL_API_KEY"])
        if not cv_text:
            raise RuntimeError("Aucun texte n'a pu être extrait du document.")
        result = _groq_analyze(cv_text, poste, description, current_app.config["GROQ_API_KEY"])
        doc["candidat"] = result.get("candidat")
        doc["adequation"] = result.get("adequation")
    except Exception as exc:  # noqa: BLE001 — surfaced to the user, not a crash
        doc["error"] = str(exc)

    result_doc = db.candidatures.insert_one(doc)
    doc["_id"] = result_doc.inserted_id

    status = 201 if not doc["error"] else 502
    return jsonify(candidature_to_dict(doc)), status


@recrutement_bp.get("/candidatures")
@roles_required("ADMIN", "RH")
def list_candidatures():
    db = get_db()
    items = list(db.candidatures.find({}).sort("createdAt", -1))
    return jsonify([candidature_to_dict(c) for c in items])


@recrutement_bp.get("/candidatures/<id>")
@roles_required("ADMIN", "RH")
def show_candidature(id):
    db = get_db()
    c = db.candidatures.find_one({"_id": oid(id)})
    if not c:
        return jsonify({"message": "Introuvable."}), 404
    return jsonify(candidature_to_dict(c))


@recrutement_bp.delete("/candidatures/<id>")
@roles_required("ADMIN", "RH")
def delete_candidature(id):
    db = get_db()
    result = db.candidatures.delete_one({"_id": oid(id)})
    if result.deleted_count == 0:
        return jsonify({"message": "Introuvable."}), 404
    return jsonify({"message": "Supprimé."})
