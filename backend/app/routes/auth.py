import os
import uuid

from flask import Blueprint, current_app, jsonify, request
from flask_jwt_extended import create_access_token, get_jwt_identity, jwt_required
from werkzeug.security import check_password_hash, generate_password_hash
from werkzeug.utils import secure_filename

from ..extensions import get_db
from ..serializers import user_to_dict
from ..utils import oid

auth_bp = Blueprint("auth", __name__, url_prefix="/api/auth")

ALLOWED_AVATAR_EXT = {"png", "jpg", "jpeg", "gif", "webp"}


@auth_bp.post("/login")
def login():
    data = request.get_json(silent=True) or {}
    email = data.get("email", "")
    password = data.get("password", "")

    if not email or not password:
        return jsonify({"message": "Email et mot de passe requis."}), 400

    db = get_db()
    user = db.users.find_one({"email": email})

    if not user or not check_password_hash(user["password"], password):
        return jsonify({"message": "Email ou mot de passe incorrect."}), 401

    token = create_access_token(identity=str(user["_id"]), additional_claims={"role": user["role"]})

    return jsonify({"token": token, "user": user_to_dict(user)})


@auth_bp.get("/me")
@jwt_required()
def me():
    db = get_db()
    user = db.users.find_one({"_id": oid(get_jwt_identity())})
    if not user:
        return jsonify({"message": "Non authentifié."}), 401
    return jsonify(user_to_dict(user))


@auth_bp.patch("/profile")
@jwt_required()
def profile():
    db = get_db()
    user_id = oid(get_jwt_identity())
    user = db.users.find_one({"_id": user_id})
    if not user:
        return jsonify({"message": "Non authentifié."}), 401

    data = request.get_json(silent=True) or {}
    updates = {}
    for field in ("prenom", "nom", "telephone"):
        if field in data:
            updates[field] = data[field]
    if data.get("email"):
        existing = db.users.find_one({"email": data["email"]})
        if not existing or existing["_id"] == user_id:
            updates["email"] = data["email"]
    if data.get("password"):
        updates["password"] = generate_password_hash(data["password"])

    if updates:
        db.users.update_one({"_id": user_id}, {"$set": updates})
        user = db.users.find_one({"_id": user_id})

    return jsonify(user_to_dict(user))


@auth_bp.post("/avatar")
@jwt_required()
def upload_avatar():
    file = request.files.get("file")
    if not file or not file.filename:
        return jsonify({"message": "Aucun fichier."}), 400

    ext = secure_filename(file.filename).rsplit(".", 1)[-1].lower() if "." in file.filename else ""
    if ext not in ALLOWED_AVATAR_EXT:
        return jsonify({"message": "Format d'image non supporté."}), 400

    upload_dir = os.path.abspath(os.path.join(current_app.config["UPLOAD_DIR"], "..", "avatars"))
    os.makedirs(upload_dir, exist_ok=True)
    filename = f"{uuid.uuid4().hex}.{ext}"
    file.save(os.path.join(upload_dir, filename))

    db = get_db()
    user_id = oid(get_jwt_identity())
    db.users.update_one({"_id": user_id}, {"$set": {"avatarPath": f"uploads/avatars/{filename}"}})
    user = db.users.find_one({"_id": user_id})

    return jsonify(user_to_dict(user))
