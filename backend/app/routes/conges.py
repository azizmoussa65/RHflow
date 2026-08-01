from datetime import datetime

from flask import Blueprint, jsonify, request
from flask_jwt_extended import get_jwt, get_jwt_identity, jwt_required

from ..extensions import get_db
from ..notifications import notify, notify_admins_and_rh
from ..serializers import conge_to_dict
from ..utils import oid, parse_date, roles_required

conges_bp = Blueprint("conges", __name__, url_prefix="/api/conges")


def _serialize_many(db, items):
    ids = {c["employeId"] for c in items if c.get("employeId")}
    employes = {u["_id"]: u for u in db.users.find({"_id": {"$in": list(ids)}})}
    return [conge_to_dict(c, employes.get(c.get("employeId"))) for c in items]


@conges_bp.get("")
@jwt_required()
def list_conges():
    db = get_db()
    query = {}
    if statut := request.args.get("statut"):
        query["statut"] = statut
    if employe_id := oid(request.args.get("employeId")):
        query["employeId"] = employe_id
    items = list(db.conges.find(query).sort("createdAt", -1))
    return jsonify(_serialize_many(db, items))


@conges_bp.get("/<id>")
@jwt_required()
def show_conge(id):
    db = get_db()
    c = db.conges.find_one({"_id": oid(id)})
    if not c:
        return jsonify({"message": "Introuvable."}), 404
    employe = db.users.find_one({"_id": c.get("employeId")})
    return jsonify(conge_to_dict(c, employe))


@conges_bp.post("")
@jwt_required()
def create_conge():
    if get_jwt().get("role") == "STAGIAIRE":
        return jsonify({"message": "Les stagiaires n'ont pas accès aux congés."}), 403

    db = get_db()
    data = request.get_json(silent=True) or {}

    employe_id = oid(data.get("employeId")) or oid(get_jwt_identity())
    date_debut = parse_date(data.get("dateDebut")) or datetime.utcnow()
    date_fin = parse_date(data.get("dateFin")) or datetime.utcnow()
    nb_jours = max(1, (date_fin - date_debut).days)

    doc = {
        "employeId": employe_id,
        "type": data.get("type", "Annuel"),
        "dateDebut": date_debut,
        "dateFin": date_fin,
        "nbJours": nb_jours,
        "motif": data.get("motif"),
        "statut": "EN_ATTENTE",
        "createdAt": datetime.utcnow(),
    }
    result = db.conges.insert_one(doc)
    doc["_id"] = result.inserted_id
    employe = db.users.find_one({"_id": employe_id})

    nom_employe = f"{employe.get('prenom','')} {employe.get('nom','')}".strip() if employe else "Un employé"
    notify_admins_and_rh(
        db, "conge_submitted", "Nouvelle demande de congé",
        f"{nom_employe} a demandé un congé {doc['type']} ({doc['nbJours']} jour(s)).",
        link="/conges",
    )

    return jsonify(conge_to_dict(doc, employe)), 201


@conges_bp.delete("/<id>")
@jwt_required()
def delete_conge(id):
    db = get_db()
    result = db.conges.delete_one({"_id": oid(id)})
    if result.deleted_count == 0:
        return jsonify({"message": "Introuvable."}), 404
    return jsonify({"message": "Supprimé."})


@conges_bp.patch("/<id>/approve")
@roles_required("ADMIN", "RH")
def approve_conge(id):
    db = get_db()
    conge_id = oid(id)
    c = db.conges.find_one({"_id": conge_id})
    if not c:
        return jsonify({"message": "Introuvable."}), 404
    db.conges.update_one({"_id": conge_id}, {"$set": {"statut": "APPROUVE"}})
    c["statut"] = "APPROUVE"
    employe = db.users.find_one({"_id": c.get("employeId")})
    if c.get("employeId"):
        notify(c["employeId"], "conge_approved", "Congé approuvé",
               f"Votre demande de congé {c.get('type')} a été approuvée.", link="/mes-conges")
    return jsonify(conge_to_dict(c, employe))


@conges_bp.patch("/<id>/refuse")
@roles_required("ADMIN", "RH")
def refuse_conge(id):
    db = get_db()
    conge_id = oid(id)
    c = db.conges.find_one({"_id": conge_id})
    if not c:
        return jsonify({"message": "Introuvable."}), 404
    db.conges.update_one({"_id": conge_id}, {"$set": {"statut": "REFUSE"}})
    c["statut"] = "REFUSE"
    employe = db.users.find_one({"_id": c.get("employeId")})
    if c.get("employeId"):
        notify(c["employeId"], "conge_refused", "Congé refusé",
               f"Votre demande de congé {c.get('type')} a été refusée.", link="/mes-conges")
    return jsonify(conge_to_dict(c, employe))
