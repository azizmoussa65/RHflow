import os
import uuid
from datetime import datetime, timezone

from flask import Blueprint, current_app, jsonify, request
from flask_jwt_extended import get_jwt_identity, jwt_required
from werkzeug.utils import secure_filename

from ..extensions import get_db
from ..serializers import conversation_to_dict, message_to_dict, user_summary
from ..utils import oid

messages_bp = Blueprint("messages", __name__, url_prefix="/api/messages")

ATTACHMENT_TYPES = {
    "png": "image", "jpg": "image", "jpeg": "image", "gif": "image", "webp": "image",
    "mp4": "video", "webm": "video", "mov": "video",
}


def get_or_create_conversation(db, user_a, user_b):
    pair = sorted([user_a, user_b], key=str)
    convo = db.conversations.find_one({"participantIds": pair})
    if convo:
        return convo
    doc = {
        "participantIds": pair,
        "lastMessageText": None,
        "lastMessageAt": None,
        "createdAt": datetime.now(timezone.utc),
    }
    result = db.conversations.insert_one(doc)
    doc["_id"] = result.inserted_id
    return doc


@messages_bp.get("/directory")
@jwt_required()
def directory():
    db = get_db()
    me = oid(get_jwt_identity())
    users = db.users.find({"_id": {"$ne": me}})
    return jsonify([user_summary(u) for u in users])


@messages_bp.get("/conversations")
@jwt_required()
def list_conversations():
    db = get_db()
    me = oid(get_jwt_identity())

    convos = list(db.conversations.find({"participantIds": me}).sort("lastMessageAt", -1))
    other_ids = []
    for c in convos:
        other_ids.extend(uid for uid in c["participantIds"] if uid != me)
    others = {u["_id"]: u for u in db.users.find({"_id": {"$in": other_ids}})}

    result = []
    for c in convos:
        other_id = next((uid for uid in c["participantIds"] if uid != me), None)
        unread = db.messages.count_documents({
            "conversationId": c["_id"],
            "senderId": {"$ne": me},
            "readBy": {"$ne": me},
        })
        result.append(conversation_to_dict(c, others.get(other_id), unread))
    return jsonify(result)


@messages_bp.post("/conversations")
@jwt_required()
def create_conversation():
    db = get_db()
    me = oid(get_jwt_identity())
    data = request.get_json(silent=True) or {}
    other_id = oid(data.get("userId"))
    other = db.users.find_one({"_id": other_id}) if other_id else None
    if not other:
        return jsonify({"message": "Utilisateur introuvable."}), 404

    convo = get_or_create_conversation(db, me, other_id)
    return jsonify(conversation_to_dict(convo, other, 0)), 201


@messages_bp.get("/conversations/<id>/messages")
@jwt_required()
def get_messages(id):
    db = get_db()
    me = oid(get_jwt_identity())
    convo_id = oid(id)
    convo = db.conversations.find_one({"_id": convo_id, "participantIds": me})
    if not convo:
        return jsonify({"message": "Introuvable."}), 404

    items = list(db.messages.find({"conversationId": convo_id}).sort("createdAt", 1))
    db.messages.update_many(
        {"conversationId": convo_id, "senderId": {"$ne": me}},
        {"$addToSet": {"readBy": me}},
    )
    return jsonify([message_to_dict(m) for m in items])


@messages_bp.post("/upload")
@jwt_required()
def upload_attachment():
    file = request.files.get("file")
    if not file or not file.filename:
        return jsonify({"message": "Aucun fichier."}), 400

    upload_dir = os.path.join(current_app.config["UPLOAD_DIR"], "..", "messages")
    upload_dir = os.path.abspath(upload_dir)
    os.makedirs(upload_dir, exist_ok=True)

    original = secure_filename(file.filename)
    ext = original.rsplit(".", 1)[-1].lower() if "." in original else ""
    filename = f"{uuid.uuid4().hex}.{ext}" if ext else uuid.uuid4().hex
    file.save(os.path.join(upload_dir, filename))

    return jsonify({
        "path": f"uploads/messages/{filename}",
        "name": original,
        "type": ATTACHMENT_TYPES.get(ext, "file"),
    }), 201
