from datetime import datetime

from flask import Blueprint, jsonify, request
from werkzeug.security import generate_password_hash

from ..extensions import get_db
from ..serializers import user_to_dict
from ..utils import oid, parse_date, roles_required

stagiaires_bp = Blueprint("stagiaires", __name__, url_prefix="/api/stagiaires")


@stagiaires_bp.get("")
@roles_required("ADMIN", "RH")
def list_stagiaires():
    db = get_db()
    query = {"role": "STAGIAIRE"}
    if dept := request.args.get("departement"):
        query["departement"] = dept
    users = db.users.find(query)
    return jsonify([user_to_dict(u) for u in users])


@stagiaires_bp.get("/<id>")
@roles_required("ADMIN", "RH")
def show_stagiaire(id):
    db = get_db()
    user = db.users.find_one({"_id": oid(id), "role": "STAGIAIRE"})
    if not user:
        return jsonify({"message": "Introuvable."}), 404
    return jsonify(user_to_dict(user))


@stagiaires_bp.post("")
@roles_required("ADMIN", "RH")
def create_stagiaire():
    db = get_db()
    data = request.get_json(silent=True) or {}

    if db.users.find_one({"email": data.get("email")}):
        return jsonify({"message": "Cet email est déjà utilisé."}), 400

    doc = {
        "email": data.get("email"),
        "prenom": data.get("prenom"),
        "nom": data.get("nom"),
        "telephone": data.get("telephone"),
        "departement": data.get("departement"),
        "poste": data.get("poste"),
        "role": "STAGIAIRE",
        "password": generate_password_hash(data.get("password") or "password"),
        "statut": "Actif",
        "dateEmbauche": parse_date(data.get("dateEmbauche")),
        "createdAt": datetime.utcnow(),
    }
    result = db.users.insert_one(doc)
    doc["_id"] = result.inserted_id
    return jsonify(user_to_dict(doc)), 201


@stagiaires_bp.put("/<id>")
@roles_required("ADMIN", "RH")
def update_stagiaire(id):
    db = get_db()
    user_id = oid(id)
    user = db.users.find_one({"_id": user_id, "role": "STAGIAIRE"})
    if not user:
        return jsonify({"message": "Introuvable."}), 404

    data = request.get_json(silent=True) or {}
    updates = {}
    for field in ("prenom", "nom", "email", "telephone", "departement", "poste", "statut"):
        if field in data:
            updates[field] = data[field]

    if updates:
        db.users.update_one({"_id": user_id}, {"$set": updates})
        user = db.users.find_one({"_id": user_id})

    return jsonify(user_to_dict(user))


@stagiaires_bp.delete("/<id>")
@roles_required("ADMIN", "RH")
def delete_stagiaire(id):
    db = get_db()
    user_id = oid(id)
    user = db.users.find_one({"_id": user_id, "role": "STAGIAIRE"})
    if not user:
        return jsonify({"message": "Introuvable."}), 404

    db.users.delete_one({"_id": user_id})
    return jsonify({"message": "Supprimé."})
