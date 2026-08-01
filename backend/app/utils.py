from datetime import datetime, date
from functools import wraps

from bson import ObjectId
from bson.errors import InvalidId
from flask import jsonify
from flask_jwt_extended import get_jwt, verify_jwt_in_request


def oid(value):
    """Cast a string to ObjectId, or None if invalid/empty."""
    if not value:
        return None
    try:
        return ObjectId(value)
    except (InvalidId, TypeError):
        return None


def initials(prenom, nom):
    p = (prenom or "")[:1]
    n = (nom or "")[:1]
    return (p + n).upper() or "??"


def fmt_date(value, fmt="%d/%m/%Y"):
    if not value:
        return None
    if isinstance(value, str):
        return value
    return value.strftime(fmt)


def parse_date(value):
    """Parse a 'YYYY-MM-DD' (or ISO) string coming from the frontend into a datetime."""
    if not value:
        return None
    value = value.split("T")[0]
    return datetime.strptime(value, "%Y-%m-%d")


def roles_required(*allowed_roles):
    def decorator(fn):
        @wraps(fn)
        def wrapper(*args, **kwargs):
            verify_jwt_in_request()
            claims = get_jwt()
            if claims.get("role") not in allowed_roles:
                return jsonify({"message": "Accès refusé."}), 403
            return fn(*args, **kwargs)
        return wrapper
    return decorator
