import os

from flask import Flask, jsonify, send_from_directory
from flask_cors import CORS

from .config import Config
from .extensions import init_mongo, jwt, socketio
from .routes import ALL_BLUEPRINTS


def create_app():
    app = Flask(__name__)
    app.config.from_object(Config)
    app.config["SECRET_KEY"] = app.config["APP_SECRET"]

    CORS(app, resources={r"/api/*": {"origins": app.config["CORS_ORIGINS"]}})
    jwt.init_app(app)
    init_mongo(app)
    socketio.init_app(app, cors_allowed_origins=app.config["CORS_ORIGINS"])

    for bp in ALL_BLUEPRINTS:
        app.register_blueprint(bp)

    from . import sockets  # noqa: F401  (registers Socket.IO event handlers)

    @app.get("/uploads/dossiers/<path:filename>")
    def uploaded_file(filename):
        return send_from_directory(app.config["UPLOAD_DIR"], filename)

    @app.get("/uploads/messages/<path:filename>")
    def uploaded_message_file(filename):
        messages_dir = os.path.abspath(os.path.join(app.config["UPLOAD_DIR"], "..", "messages"))
        return send_from_directory(messages_dir, filename)

    @app.get("/uploads/avatars/<path:filename>")
    def uploaded_avatar_file(filename):
        avatars_dir = os.path.abspath(os.path.join(app.config["UPLOAD_DIR"], "..", "avatars"))
        return send_from_directory(avatars_dir, filename)

    @app.get("/uploads/cv/<path:filename>")
    def uploaded_cv_file(filename):
        cv_dir = os.path.abspath(os.path.join(app.config["UPLOAD_DIR"], "..", "cv"))
        return send_from_directory(cv_dir, filename)

    @jwt.unauthorized_loader
    def unauthorized(reason):
        return jsonify({"message": "Non authentifié."}), 401

    @jwt.invalid_token_loader
    def invalid_token(reason):
        return jsonify({"message": "Token invalide."}), 401

    @jwt.expired_token_loader
    def expired_token(header, payload):
        return jsonify({"message": "Session expirée."}), 401

    # Serve the built Vue frontend (frontend/dist) so the whole app can run behind
    # a single port in production. In dev, that directory doesn't exist and Vite
    # serves the frontend separately, so these routes simply won't match anything.
    frontend_dist = os.path.abspath(os.path.join(app.root_path, "..", "..", "frontend", "dist"))

    @app.get("/", defaults={"path": ""})
    @app.get("/<path:path>")
    def serve_frontend(path):
        target = os.path.join(frontend_dist, path) if path else None
        if target and os.path.isfile(target):
            return send_from_directory(frontend_dist, path)
        index_path = os.path.join(frontend_dist, "index.html")
        if os.path.isfile(index_path):
            return send_from_directory(frontend_dist, "index.html")
        return jsonify({"message": "Frontend non compilé."}), 404

    return app
