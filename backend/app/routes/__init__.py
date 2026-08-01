from .auth import auth_bp
from .chatbot import chatbot_bp
from .conges import conges_bp
from .contact import contact_bp
from .contrats import contrats_bp
from .dashboard import dashboard_bp
from .dossiers import dossiers_bp
from .employes import employes_bp
from .evaluations import evaluations_bp
from .messages import messages_bp
from .notifications import notifications_bp
from .projets import projets_bp
from .recrutement import recrutement_bp
from .stagiaires import stagiaires_bp
from .taches import taches_bp

ALL_BLUEPRINTS = [
    auth_bp,
    employes_bp,
    stagiaires_bp,
    conges_bp,
    contrats_bp,
    dossiers_bp,
    evaluations_bp,
    projets_bp,
    taches_bp,
    dashboard_bp,
    chatbot_bp,
    messages_bp,
    notifications_bp,
    recrutement_bp,
    contact_bp,
]
