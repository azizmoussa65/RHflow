from datetime import datetime

from flask import Blueprint, jsonify, request

from ..extensions import get_db
from ..serializers import tache_to_dict
from ..utils import oid

taches_bp = Blueprint("taches", __name__, url_prefix="/api/projets/<projet_id>/taches")


def _serialize(db, t):
    assigne = db.users.find_one({"_id": t["assigneAId"]}) if t.get("assigneAId") else None
    return tache_to_dict(t, assigne)


@taches_bp.get("")
def list_taches(projet_id):
    db = get_db()
    pid = oid(projet_id)
    if not db.projets.find_one({"_id": pid}):
        return jsonify({"message": "Projet introuvable."}), 404
    items = db.taches.find({"projetId": pid})
    return jsonify([_serialize(db, t) for t in items])


@taches_bp.post("")
def create_tache(projet_id):
    db = get_db()
    pid = oid(projet_id)
    if not db.projets.find_one({"_id": pid}):
        return jsonify({"message": "Projet introuvable."}), 404

    data = request.get_json(silent=True) or {}
    doc = {
        "titre": data.get("titre", ""),
        "description": data.get("description"),
        "statut": data.get("statut", "À faire"),
        "priorite": data.get("priorite", "Normale"),
        "projetId": pid,
        "assigneAId": oid(data.get("assigneAId")),
        "createdAt": datetime.utcnow(),
    }
    result = db.taches.insert_one(doc)
    doc["_id"] = result.inserted_id
    return jsonify(_serialize(db, doc)), 201


@taches_bp.put("/<id>")
def update_tache(projet_id, id):
    db = get_db()
    tache_id = oid(id)
    t = db.taches.find_one({"_id": tache_id})
    if not t:
        return jsonify({"message": "Introuvable."}), 404

    data = request.get_json(silent=True) or {}
    updates = {k: data[k] for k in ("titre", "description", "statut", "priorite") if k in data}
    if "assigneAId" in data:
        updates["assigneAId"] = oid(data["assigneAId"])

    if updates:
        db.taches.update_one({"_id": tache_id}, {"$set": updates})
        t = db.taches.find_one({"_id": tache_id})

    return jsonify(_serialize(db, t))


@taches_bp.delete("/<id>")
def delete_tache(projet_id, id):
    db = get_db()
    result = db.taches.delete_one({"_id": oid(id)})
    if result.deleted_count == 0:
        return jsonify({"message": "Introuvable."}), 404
    return jsonify({"message": "Supprimé."})
