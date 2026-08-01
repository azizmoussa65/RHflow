from datetime import datetime

from flask import Blueprint, jsonify, request

from ..extensions import get_db
from ..serializers import evaluation_to_dict
from ..utils import oid, roles_required

evaluations_bp = Blueprint("evaluations", __name__, url_prefix="/api/evaluations")


def _compute_note(data, existing=None):
    def val(key):
        if key in data:
            return float(data[key])
        return float(existing.get(key, 0)) if existing else 0.0

    comp, team, init = val("competences"), val("teamwork"), val("initiative")
    return comp, team, init, round((comp + team + init) / 3, 1)


def _serialize_many(db, items):
    ids = {e["employeId"] for e in items if e.get("employeId")}
    employes = {u["_id"]: u for u in db.users.find({"_id": {"$in": list(ids)}})}
    return [evaluation_to_dict(e, employes.get(e.get("employeId"))) for e in items]


@evaluations_bp.get("")
@roles_required("ADMIN", "RH")
def list_evaluations():
    db = get_db()
    items = list(db.evaluations.find({}).sort("createdAt", -1))
    return jsonify(_serialize_many(db, items))


@evaluations_bp.get("/<id>")
@roles_required("ADMIN", "RH")
def show_evaluation(id):
    db = get_db()
    e = db.evaluations.find_one({"_id": oid(id)})
    if not e:
        return jsonify({"message": "Introuvable."}), 404
    employe = db.users.find_one({"_id": e.get("employeId")})
    return jsonify(evaluation_to_dict(e, employe))


@evaluations_bp.post("")
@roles_required("ADMIN", "RH")
def create_evaluation():
    db = get_db()
    data = request.get_json(silent=True) or {}

    employe_id = oid(data.get("employeId"))
    employe = db.users.find_one({"_id": employe_id}) if employe_id else None
    if not employe:
        return jsonify({"message": "Employé introuvable."}), 404

    comp, team, init, note = _compute_note(data)
    doc = {
        "employeId": employe_id,
        "periode": data.get("periode", ""),
        "competences": comp,
        "teamwork": team,
        "initiative": init,
        "noteGlobale": note,
        "commentaire": data.get("commentaire"),
        "createdAt": datetime.utcnow(),
    }
    result = db.evaluations.insert_one(doc)
    doc["_id"] = result.inserted_id
    return jsonify(evaluation_to_dict(doc, employe)), 201


@evaluations_bp.put("/<id>")
@roles_required("ADMIN", "RH")
def update_evaluation(id):
    db = get_db()
    eval_id = oid(id)
    e = db.evaluations.find_one({"_id": eval_id})
    if not e:
        return jsonify({"message": "Introuvable."}), 404

    data = request.get_json(silent=True) or {}
    updates = {k: data[k] for k in ("periode", "commentaire") if k in data}
    comp, team, init, note = _compute_note(data, existing=e)
    updates.update({"competences": comp, "teamwork": team, "initiative": init, "noteGlobale": note})

    db.evaluations.update_one({"_id": eval_id}, {"$set": updates})
    e = db.evaluations.find_one({"_id": eval_id})
    employe = db.users.find_one({"_id": e.get("employeId")})
    return jsonify(evaluation_to_dict(e, employe))


@evaluations_bp.delete("/<id>")
@roles_required("ADMIN", "RH")
def delete_evaluation(id):
    db = get_db()
    result = db.evaluations.delete_one({"_id": oid(id)})
    if result.deleted_count == 0:
        return jsonify({"message": "Introuvable."}), 404
    return jsonify({"message": "Supprimé."})
