from datetime import datetime

from flask import Blueprint, jsonify, request

from ..extensions import get_db
from ..serializers import projet_to_dict
from ..utils import oid, parse_date, roles_required

projets_bp = Blueprint("projets", __name__, url_prefix="/api/projets")


def _serialize(db, p):
    membre_ids = p.get("membres", [])
    membres = list(db.users.find({"_id": {"$in": membre_ids}})) if membre_ids else []
    return projet_to_dict(p, membres)


@projets_bp.get("")
def list_projets():
    db = get_db()
    query = {}
    if statut := request.args.get("statut"):
        query["statut"] = statut
    items = list(db.projets.find(query).sort("createdAt", -1))
    return jsonify([_serialize(db, p) for p in items])


@projets_bp.get("/<id>")
def show_projet(id):
    db = get_db()
    p = db.projets.find_one({"_id": oid(id)})
    if not p:
        return jsonify({"message": "Introuvable."}), 404
    return jsonify(_serialize(db, p))


@projets_bp.post("")
@roles_required("ADMIN", "RH")
def create_projet():
    db = get_db()
    data = request.get_json(silent=True) or {}

    doc = {
        "nom": data.get("nom", ""),
        "description": data.get("description"),
        "categorie": data.get("categorie", "Général"),
        "statut": data.get("statut", "En cours"),
        "avancement": int(data.get("avancement", 0)),
        "deadline": parse_date(data.get("deadline")),
        "couleur": data.get("couleur", "#3b82f6"),
        "membres": [],
        "createdAt": datetime.utcnow(),
    }
    result = db.projets.insert_one(doc)
    doc["_id"] = result.inserted_id
    return jsonify(_serialize(db, doc)), 201


@projets_bp.put("/<id>")
@roles_required("ADMIN", "RH")
def update_projet(id):
    db = get_db()
    projet_id = oid(id)
    p = db.projets.find_one({"_id": projet_id})
    if not p:
        return jsonify({"message": "Introuvable."}), 404

    data = request.get_json(silent=True) or {}
    updates = {k: data[k] for k in ("nom", "description", "categorie", "statut") if k in data}
    if "avancement" in data:
        updates["avancement"] = int(data["avancement"])
    if data.get("couleur"):
        updates["couleur"] = data["couleur"]
    if data.get("deadline"):
        updates["deadline"] = parse_date(data["deadline"])

    if updates:
        db.projets.update_one({"_id": projet_id}, {"$set": updates})
        p = db.projets.find_one({"_id": projet_id})

    return jsonify(_serialize(db, p))


@projets_bp.delete("/<id>")
@roles_required("ADMIN", "RH")
def delete_projet(id):
    db = get_db()
    projet_id = oid(id)
    result = db.projets.delete_one({"_id": projet_id})
    if result.deleted_count == 0:
        return jsonify({"message": "Introuvable."}), 404
    db.taches.delete_many({"projetId": projet_id})
    return jsonify({"message": "Supprimé."})
