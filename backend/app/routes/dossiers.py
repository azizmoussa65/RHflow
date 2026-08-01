import os
import uuid
from datetime import datetime

from flask import Blueprint, current_app, jsonify, request
from flask_jwt_extended import get_jwt_identity, jwt_required
from werkzeug.utils import secure_filename

from ..extensions import get_db
from ..serializers import dossier_to_dict
from ..utils import oid, roles_required

dossiers_bp = Blueprint("dossiers", __name__, url_prefix="/api/dossiers")


def _serialize_many(db, items):
    ids = {d["employeId"] for d in items if d.get("employeId")}
    employes = {u["_id"]: u for u in db.users.find({"_id": {"$in": list(ids)}})}
    return [dossier_to_dict(d, employes.get(d.get("employeId"))) for d in items]


@dossiers_bp.get("")
@jwt_required()
def list_dossiers():
    db = get_db()
    query = {}
    if employe_id := oid(request.args.get("employeId")):
        query["employeId"] = employe_id
    items = list(db.dossiers.find(query).sort("dateAjout", -1))
    return jsonify(_serialize_many(db, items))


@dossiers_bp.get("/<id>")
@jwt_required()
def show_dossier(id):
    db = get_db()
    d = db.dossiers.find_one({"_id": oid(id)})
    if not d:
        return jsonify({"message": "Introuvable."}), 404
    employe = db.users.find_one({"_id": d.get("employeId")})
    return jsonify(dossier_to_dict(d, employe))


@dossiers_bp.post("")
@jwt_required()
def create_dossier():
    db = get_db()

    if request.content_type and "multipart/form-data" in request.content_type:
        data = request.form
    else:
        data = request.get_json(silent=True) or {}

    employe_id = oid(data.get("employeId")) or oid(get_jwt_identity())
    titre = data.get("titre") or data.get("type") or "Document"
    type_ = data.get("type", "Autre")

    doc = {
        "employeId": employe_id,
        "titre": titre,
        "type": type_,
        "fichierPath": None,
        "statut": "En attente",
        "dateAjout": datetime.utcnow(),
    }

    file = request.files.get("fichier")
    if file and file.filename:
        os.makedirs(current_app.config["UPLOAD_DIR"], exist_ok=True)
        ext = secure_filename(file.filename).rsplit(".", 1)[-1] if "." in file.filename else "bin"
        filename = f"{uuid.uuid4().hex}.{ext}"
        file.save(os.path.join(current_app.config["UPLOAD_DIR"], filename))
        doc["fichierPath"] = f"uploads/dossiers/{filename}"

    result = db.dossiers.insert_one(doc)
    doc["_id"] = result.inserted_id
    employe = db.users.find_one({"_id": employe_id})
    return jsonify(dossier_to_dict(doc, employe)), 201


@dossiers_bp.put("/<id>")
@jwt_required()
def update_dossier(id):
    db = get_db()
    dossier_id = oid(id)
    d = db.dossiers.find_one({"_id": dossier_id})
    if not d:
        return jsonify({"message": "Introuvable."}), 404

    data = request.get_json(silent=True) or {}
    updates = {k: data[k] for k in ("titre", "type", "statut") if k in data}
    if updates:
        db.dossiers.update_one({"_id": dossier_id}, {"$set": updates})
        d = db.dossiers.find_one({"_id": dossier_id})

    employe = db.users.find_one({"_id": d.get("employeId")})
    return jsonify(dossier_to_dict(d, employe))


@dossiers_bp.delete("/<id>")
@jwt_required()
def delete_dossier(id):
    db = get_db()
    result = db.dossiers.delete_one({"_id": oid(id)})
    if result.deleted_count == 0:
        return jsonify({"message": "Introuvable."}), 404
    return jsonify({"message": "Supprimé."})


@dossiers_bp.patch("/<id>/valider")
@roles_required("ADMIN", "RH")
def valider_dossier(id):
    db = get_db()
    dossier_id = oid(id)
    d = db.dossiers.find_one({"_id": dossier_id})
    if not d:
        return jsonify({"message": "Introuvable."}), 404
    db.dossiers.update_one({"_id": dossier_id}, {"$set": {"statut": "Validé"}})
    d["statut"] = "Validé"
    employe = db.users.find_one({"_id": d.get("employeId")})
    return jsonify(dossier_to_dict(d, employe))


@dossiers_bp.patch("/<id>/refuser")
@roles_required("ADMIN", "RH")
def refuser_dossier(id):
    db = get_db()
    dossier_id = oid(id)
    d = db.dossiers.find_one({"_id": dossier_id})
    if not d:
        return jsonify({"message": "Introuvable."}), 404
    db.dossiers.update_one({"_id": dossier_id}, {"$set": {"statut": "Refusé"}})
    d["statut"] = "Refusé"
    employe = db.users.find_one({"_id": d.get("employeId")})
    return jsonify(dossier_to_dict(d, employe))
