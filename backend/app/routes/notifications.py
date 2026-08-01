from flask import Blueprint, jsonify
from flask_jwt_extended import get_jwt_identity, jwt_required

from ..extensions import get_db
from ..serializers import notification_to_dict
from ..utils import oid

notifications_bp = Blueprint("notifications", __name__, url_prefix="/api/notifications")


@notifications_bp.get("")
@jwt_required()
def list_notifications():
    db = get_db()
    me = oid(get_jwt_identity())
    items = list(db.notifications.find({"userId": me}).sort("createdAt", -1).limit(30))
    return jsonify([notification_to_dict(n) for n in items])


@notifications_bp.patch("/<id>/read")
@jwt_required()
def mark_read(id):
    db = get_db()
    me = oid(get_jwt_identity())
    db.notifications.update_one({"_id": oid(id), "userId": me}, {"$set": {"read": True}})
    return jsonify({"message": "OK."})


@notifications_bp.patch("/read-all")
@jwt_required()
def mark_all_read():
    db = get_db()
    me = oid(get_jwt_identity())
    db.notifications.update_many({"userId": me, "read": False}, {"$set": {"read": True}})
    return jsonify({"message": "OK."})
