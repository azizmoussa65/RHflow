"""
Initialise la base MongoDB avec les 2 comptes de base (aucun employé).
Usage : python seed.py
"""
from datetime import datetime

from dotenv import load_dotenv
from werkzeug.security import generate_password_hash

load_dotenv()

from app.config import Config  # noqa: E402
from pymongo import MongoClient  # noqa: E402

SEED_USERS = [
    {
        "email": "admin@satisfyinsight.cm",
        "password": "password123",
        "prenom": "Admin",
        "nom": "Satisfy",
        "role": "ADMIN",
        "departement": "Direction Générale",
        "poste": "Administrateur",
    },
    {
        "email": "rh@satisfyinsight.com",
        "password": "password123",
        "prenom": "RH",
        "nom": "Satisfy",
        "role": "RH",
        "departement": "Ressources Humaines",
        "poste": "Responsable RH",
    },
]


def run():
    client = MongoClient(Config.MONGO_URI)
    db = client.get_default_database()
    db.users.create_index("email", unique=True)

    for u in SEED_USERS:
        if db.users.find_one({"email": u["email"]}):
            print(f"- {u['email']} existe déjà, ignoré.")
            continue
        db.users.insert_one({
            "email": u["email"],
            "password": generate_password_hash(u["password"]),
            "prenom": u["prenom"],
            "nom": u["nom"],
            "role": u["role"],
            "telephone": None,
            "departement": u["departement"],
            "poste": u["poste"],
            "statut": "Actif",
            "dateEmbauche": None,
            "createdAt": datetime.utcnow(),
        })
        print(f"+ {u['email']} créé (mot de passe : {u['password']}).")

    print("Seed terminé. Aucun employé n'a été créé — utilisez l'application pour en ajouter.")


if __name__ == "__main__":
    run()
