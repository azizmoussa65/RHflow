from flask import Blueprint, jsonify

from ..extensions import get_db
from ..utils import roles_required

dashboard_bp = Blueprint("dashboard", __name__, url_prefix="/api/dashboard")


@dashboard_bp.get("/stats")
@roles_required("ADMIN", "RH")
def stats():
    db = get_db()
    return jsonify({
        "totalEmployes": db.users.count_documents({"role": "EMPLOYE"}),
        "projetsActifs": db.projets.count_documents({"statut": "En cours"}),
        "congesEnAttente": db.conges.count_documents({"statut": "EN_ATTENTE"}),
        "contratsActifs": db.contrats.count_documents({"statut": "Actif"}),
    })
