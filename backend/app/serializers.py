from .utils import fmt_date, initials


def user_summary(u):
    if not u:
        return None
    return {
        "id": str(u["_id"]),
        "prenom": u.get("prenom"),
        "nom": u.get("nom"),
        "role": u.get("role"),
        "poste": u.get("poste"),
        "initials": initials(u.get("prenom"), u.get("nom")),
    }


def candidature_to_dict(c):
    return {
        "id": str(c["_id"]),
        "filename": c.get("filename"),
        "cvPath": c.get("cvPath"),
        "poste": c.get("poste"),
        "description": c.get("description"),
        "candidat": c.get("candidat"),
        "adequation": c.get("adequation"),
        "error": c.get("error"),
        "createdAt": c["createdAt"].isoformat(),
    }


def demande_to_dict(d):
    return {
        "id": str(d["_id"]),
        "nom": d.get("nom"),
        "email": d.get("email"),
        "entreprise": d.get("entreprise"),
        "message": d.get("message"),
        "traite": d.get("traite", False),
        "createdAt": d["createdAt"].isoformat(),
    }


def notification_to_dict(n):
    return {
        "id": str(n["_id"]),
        "type": n.get("type"),
        "title": n.get("title"),
        "message": n.get("message"),
        "link": n.get("link"),
        "read": n.get("read", False),
        "createdAt": n["createdAt"].isoformat(),
    }


def message_to_dict(m):
    return {
        "id": str(m["_id"]),
        "conversationId": str(m["conversationId"]),
        "senderId": str(m["senderId"]),
        "type": m.get("type", "text"),
        "content": m.get("content"),
        "attachmentPath": m.get("attachmentPath"),
        "attachmentName": m.get("attachmentName"),
        "createdAt": m["createdAt"].isoformat(),
    }


def conversation_to_dict(c, other_user, unread_count=0):
    return {
        "id": str(c["_id"]),
        "otherUser": user_summary(other_user),
        "lastMessageText": c.get("lastMessageText"),
        "lastMessageAt": c["lastMessageAt"].isoformat() if c.get("lastMessageAt") else None,
        "unreadCount": unread_count,
    }


def user_to_dict(u):
    if not u:
        return None
    return {
        "id": str(u["_id"]),
        "email": u.get("email"),
        "prenom": u.get("prenom"),
        "nom": u.get("nom"),
        "role": u.get("role"),
        "telephone": u.get("telephone"),
        "departement": u.get("departement"),
        "poste": u.get("poste"),
        "statut": u.get("statut", "Actif"),
        "dateEmbauche": fmt_date(u.get("dateEmbauche")),
        "initials": initials(u.get("prenom"), u.get("nom")),
        "avatarUrl": u.get("avatarPath"),
        "createdAt": u["createdAt"].isoformat() if u.get("createdAt") else None,
    }


def conge_to_dict(c, employe):
    return {
        "id": str(c["_id"]),
        "employe": f'{employe.get("nom","")} {employe.get("prenom","")}'.strip() if employe else None,
        "employeId": str(c["employeId"]) if c.get("employeId") else None,
        "initials": initials(employe.get("prenom"), employe.get("nom")) if employe else "??",
        "type": c.get("type"),
        "dateDebut": fmt_date(c.get("dateDebut")),
        "dateFin": fmt_date(c.get("dateFin")),
        "nbJours": c.get("nbJours"),
        "motif": c.get("motif"),
        "statut": c.get("statut", "EN_ATTENTE"),
        "createdAt": c["createdAt"].isoformat() if c.get("createdAt") else None,
    }


def contrat_to_dict(c, employe):
    salaire = float(c.get("salaire", 0))
    date_fin = c.get("dateFin")
    expire_bientot = False
    if date_fin:
        from datetime import datetime
        expire_bientot = 0 <= (date_fin - datetime.now()).days <= 30
    return {
        "id": str(c["_id"]),
        "employe": f'{employe.get("prenom","")} {employe.get("nom","")}'.strip() if employe else None,
        "employeId": str(c["employeId"]) if c.get("employeId") else None,
        "initials": initials(employe.get("prenom"), employe.get("nom")) if employe else "??",
        "type": c.get("type"),
        "dateDebut": fmt_date(c.get("dateDebut")),
        "dateFin": fmt_date(date_fin),
        "salaire": f'{salaire:,.0f}'.replace(",", " "),
        "statut": c.get("statut", "Actif"),
        "expireBientot": expire_bientot,
    }


def dossier_to_dict(d, employe):
    fichier = d.get("fichierPath") or ""
    ext = fichier.rsplit(".", 1)[-1].lower() if "." in fichier else ""
    return {
        "id": str(d["_id"]),
        "titre": d.get("titre"),
        "type": d.get("type"),
        "employe": f'{employe.get("prenom","")} {employe.get("nom","")}'.strip() if employe else None,
        "employeId": str(d["employeId"]) if d.get("employeId") else None,
        "fichier": "img" if ext in ("png", "jpg", "jpeg") else "pdf",
        "fichierPath": d.get("fichierPath"),
        "dateAjout": fmt_date(d.get("dateAjout"), "%d %b %Y"),
        "statut": d.get("statut", "En attente"),
    }


def evaluation_to_dict(e, employe):
    return {
        "id": str(e["_id"]),
        "employe": f'{employe.get("prenom","")} {employe.get("nom","")}'.strip() if employe else None,
        "employeId": str(e["employeId"]) if e.get("employeId") else None,
        "initials": initials(employe.get("prenom"), employe.get("nom")) if employe else "??",
        "periode": e.get("periode"),
        "competences": float(e.get("competences", 0)),
        "teamwork": float(e.get("teamwork", 0)),
        "initiative": float(e.get("initiative", 0)),
        "noteGlobale": float(e.get("noteGlobale", 0)),
        "commentaire": e.get("commentaire"),
    }


def projet_to_dict(p, membres):
    return {
        "id": str(p["_id"]),
        "nom": p.get("nom"),
        "description": p.get("description"),
        "categorie": p.get("categorie"),
        "statut": p.get("statut", "En cours"),
        "avancement": p.get("avancement", 0),
        "deadline": fmt_date(p.get("deadline"), "%d %b"),
        "couleur": p.get("couleur", "#3b82f6"),
        "membres": [{"initials": initials(m.get("prenom"), m.get("nom"))} for m in membres],
        "createdAt": p["createdAt"].isoformat() if p.get("createdAt") else None,
    }


def tache_to_dict(t, assigne):
    return {
        "id": str(t["_id"]),
        "titre": t.get("titre"),
        "description": t.get("description"),
        "statut": t.get("statut", "À faire"),
        "priorite": t.get("priorite", "Normale"),
        "projetId": str(t["projetId"]) if t.get("projetId") else None,
        "assigneA": {
            "id": str(assigne["_id"]),
            "nom": f'{assigne.get("prenom","")} {assigne.get("nom","")}'.strip(),
            "initials": initials(assigne.get("prenom"), assigne.get("nom")),
            "poste": assigne.get("poste"),
        } if assigne else None,
    }
