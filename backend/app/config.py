import os


class Config:
    APP_SECRET = os.environ.get("APP_SECRET", "change-me-in-production")
    JWT_SECRET_KEY = os.environ.get("JWT_SECRET_KEY", "change-me-in-production")
    MONGO_URI = os.environ.get("MONGO_URI", "mongodb://localhost:27017/hrflow")
    GROQ_API_KEY = os.environ.get("GROQ_API_KEY", "")
    MISTRAL_API_KEY = os.environ.get("MISTRAL_API_KEY", "")
    CORS_ORIGINS = [
        o.strip()
        for o in os.environ.get("CORS_ORIGIN", "http://localhost:5173,http://127.0.0.1:5173").split(",")
        if o.strip()
    ]
    UPLOAD_DIR = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), "uploads", "dossiers")
