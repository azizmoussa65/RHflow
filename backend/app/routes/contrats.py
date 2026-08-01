from flask import Blueprint, jsonify, request

from ..extensions import get_db
from ..serializers import contrat_to_dict
from ..utils import oid, parse_date, roles_required

contrats_bp = Blueprint("contrats", __name__, url_prefix="/api/contrats")


def _serialize_many(db, items):
    ids = {c["employeId"] for c in items if c.get("employeId")}
    employes = {u["_id"]: u for u in db.users.find({"_id": {"$in": list(ids)}})}
    return [contrat_to_dict(c, employes.get(c.get("employeId"))) for c in items]


@contrats_bp.get("")
@roles_required("ADMIN", "RH")
def list_contrats():
    db = get_db()
    query = {}
    if statut := request.args.get("statut"):
        query["statut"] = statut
    items = list(db.contrats.find(query).sort("dateDebut", -1))
    return jsonify(_serialize_many(db, items))


@contrats_bp.get("/<id>")
@roles_required("ADMIN", "RH")
def show_contrat(id):
    db = get_db()
    c = db.contrats.find_one({"_id": oid(id)})
    if not c:
        return jsonify({"message": "Introuvable."}), 404
    employe = db.users.find_one({"_id": c.get("employeId")})
    return jsonify(contrat_to_dict(c, employe))


@contrats_bp.post("")
@roles_required("ADMIN", "RH")
def create_contrat():
    db = get_db()
    data = request.get_json(silent=True) or {}

    employe_id = oid(data.get("employeId"))
    employe = db.users.find_one({"_id": employe_id}) if employe_id else None
    if not employe:
        return jsonify({"message": "Employé introuvable."}), 404

    doc = {
        "employeId": employe_id,
        "type": data.get("type", "CDI"),
        "salaire": float(data.get("salaire", 0)),
        "dateDebut": parse_date(data.get("dateDebut")),
        "dateFin": parse_date(data.get("dateFin")),
        "statut": data.get("statut", "Actif"),
    }
    result = db.contrats.insert_one(doc)
    doc["_id"] = result.inserted_id
    return jsonify(contrat_to_dict(doc, employe)), 201


@contrats_bp.put("/<id>")
@roles_required("ADMIN", "RH")
def update_contrat(id):
    db = get_db()
    contrat_id = oid(id)
    c = db.contrats.find_one({"_id": contrat_id})
    if not c:
        return jsonify({"message": "Introuvable."}), 404

    data = request.get_json(silent=True) or {}
    updates = {}
    if "type" in data:
        updates["type"] = data["type"]
    if "salaire" in data:
        updates["salaire"] = float(data["salaire"])
    if "statut" in data:
        updates["statut"] = data["statut"]
    if data.get("dateDebut"):
        updates["dateDebut"] = parse_date(data["dateDebut"])
    if data.get("dateFin"):
        updates["dateFin"] = parse_date(data["dateFin"])

    if updates:
        db.contrats.update_one({"_id": contrat_id}, {"$set": updates})
        c = db.contrats.find_one({"_id": contrat_id})

    employe = db.users.find_one({"_id": c.get("employeId")})
    return jsonify(contrat_to_dict(c, employe))


@contrats_bp.delete("/<id>")
@roles_required("ADMIN", "RH")
def delete_contrat(id):
    db = get_db()
    result = db.contrats.delete_one({"_id": oid(id)})
    if result.deleted_count == 0:
        return jsonify({"message": "Introuvable."}), 404
    return jsonify({"message": "Supprimé."})
