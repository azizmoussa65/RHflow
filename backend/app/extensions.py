from flask_jwt_extended import JWTManager
from flask_socketio import SocketIO
from pymongo import MongoClient

jwt = JWTManager()
socketio = SocketIO()

_client = None
_db = None


def init_mongo(app):
    global _client, _db
    _client = MongoClient(app.config["MONGO_URI"])
    _db = _client.get_default_database()
    _db.users.create_index("email", unique=True)


def get_db():
    return _db
