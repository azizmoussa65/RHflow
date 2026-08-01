from datetime import datetime, timezone

from .extensions import get_db, socketio
from .serializers import notification_to_dict


def notify(user_id, type_, title, message, link=None):
    """Persist a notification for a user and push it live via Socket.IO."""
    db = get_db()
    doc = {
        "userId": user_id,
        "type": type_,
        "title": title,
        "message": message,
        "link": link,
        "read": False,
        "createdAt": datetime.now(timezone.utc),
    }
    result = db.notifications.insert_one(doc)
    doc["_id"] = result.inserted_id
    socketio.emit("notification", notification_to_dict(doc), room=f"user:{user_id}")


def notify_admins_and_rh(db, type_, title, message, link=None):
    for user in db.users.find({"role": {"$in": ["ADMIN", "RH"]}}):
        notify(user["_id"], type_, title, message, link)
