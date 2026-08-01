from datetime import datetime

from flask import Blueprint, jsonify, request

from ..extensions import get_db
from ..notifications import notify_admins_and_rh
from ..serializers import demande_to_dict
from ..utils import oid, roles_required

contact_bp = Blueprint("contact", __name__, url_prefix="/api/contact")


@contact_bp.post("")
def create_demande():
    data = request.get_json(silent=True) or {}
    nom = (data.get("nom") or "").strip()
    email = (data.get("email") or "").strip()
    entreprise = (data.get("entreprise") or "").strip()
    message = (data.get("message") or "").strip()

    if not nom or not email:
        return jsonify({"message": "Le nom et l'email sont requis."}), 400

    db = get_db()
    doc = {
        "nom": nom,
        "email": email,
        "entreprise": entreprise,
        "message": message,
        "traite": False,
        "createdAt": datetime.utcnow(),
    }
    result = db.demandes_demo.insert_one(doc)
    doc["_id"] = result.inserted_id

    notify_admins_and_rh(
        db, "demo_request", "Nouvelle demande de démo",
        f"{nom}{' (' + entreprise + ')' if entreprise else ''} souhaite réserver une démo.",
        link="/demandes-demo",
    )

    return jsonify(demande_to_dict(doc)), 201


@contact_bp.get("")
@roles_required("ADMIN", "RH")
def list_demandes():
    db = get_db()
    items = list(db.demandes_demo.find({}).sort("createdAt", -1))
    return jsonify([demande_to_dict(d) for d in items])


@contact_bp.patch("/<id>/traiter")
@roles_required("ADMIN", "RH")
def marquer_traite(id):
    db = get_db()
    demande_id = oid(id)
    db.demandes_demo.update_one({"_id": demande_id}, {"$set": {"traite": True}})
    d = db.demandes_demo.find_one({"_id": demande_id})
    if not d:
        return jsonify({"message": "Introuvable."}), 404
    return jsonify(demande_to_dict(d))
