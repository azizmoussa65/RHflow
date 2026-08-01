from datetime import datetime, timezone

from flask_jwt_extended import decode_token
from flask_socketio import ConnectionRefusedError, emit, join_room

from .extensions import get_db, socketio
from .serializers import message_to_dict
from .utils import oid

# sid -> user id (str), kept in sync with each connection
_sid_to_user = {}


def _authenticate(auth):
    token = (auth or {}).get("token", "")
    if token.lower().startswith("bearer "):
        token = token[7:]
    try:
        decoded = decode_token(token)
        return decoded["sub"]
    except Exception:
        return None


@socketio.on("connect")
def on_connect(auth):
    from flask import request

    user_id = _authenticate(auth)
    if not user_id:
        raise ConnectionRefusedError("unauthorized")
    _sid_to_user[request.sid] = user_id
    join_room(f"user:{user_id}")


@socketio.on("disconnect")
def on_disconnect():
    from flask import request

    _sid_to_user.pop(request.sid, None)


@socketio.on("join_conversation")
def on_join_conversation(data):
    from flask import request

    user_id = _sid_to_user.get(request.sid)
    if not user_id:
        return
    db = get_db()
    convo_id = oid(data.get("conversationId"))
    convo = db.conversations.find_one({"_id": convo_id, "participantIds": oid(user_id)})
    if convo:
        join_room(f"conv:{convo_id}")


@socketio.on("send_message")
def on_send_message(data):
    from flask import request

    user_id = _sid_to_user.get(request.sid)
    if not user_id:
        return

    db = get_db()
    me = oid(user_id)
    convo_id = oid(data.get("conversationId"))
    convo = db.conversations.find_one({"_id": convo_id, "participantIds": me})
    if not convo:
        return

    msg_type = data.get("type", "text")
    content = (data.get("content") or "").strip()
    if msg_type == "text" and not content:
        return

    doc = {
        "conversationId": convo_id,
        "senderId": me,
        "type": msg_type,
        "content": content or None,
        "attachmentPath": data.get("attachmentPath"),
        "attachmentName": data.get("attachmentName"),
        "readBy": [me],
        "createdAt": datetime.now(timezone.utc),
    }
    result = db.messages.insert_one(doc)
    doc["_id"] = result.inserted_id

    preview = content if content else {"image": "📷 Photo", "video": "🎥 Vidéo", "file": "📎 Fichier"}.get(msg_type, "")
    db.conversations.update_one(
        {"_id": convo_id},
        {"$set": {"lastMessageText": preview, "lastMessageAt": doc["createdAt"]}},
    )

    payload = message_to_dict(doc)
    other_id = next((uid for uid in convo["participantIds"] if uid != me), None)

    emit("new_message", payload, room=f"conv:{convo_id}")
    if other_id:
        emit("conversation_updated", {"conversationId": str(convo_id)}, room=f"user:{other_id}")
