<?php
namespace App\Controller;

use App\Entity\Conge;
use App\Entity\Contrat;
use App\Entity\Projet;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/dashboard', name: 'dashboard_')]
class DashboardController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    /** GET /api/dashboard/stats */
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        $totalEmployes   = $this->em->createQuery('SELECT COUNT(u) FROM App\Entity\User u WHERE u.roles LIKE :role')
            ->setParameter('role', '%ROLE_EMPLOYE%')->getSingleScalarResult();

        $projetsActifs   = $this->em->createQuery('SELECT COUNT(p) FROM App\Entity\Projet p WHERE p.statut = :s')
            ->setParameter('s', 'En cours')->getSingleScalarResult();

        $congesEnAttente = $this->em->createQuery('SELECT COUNT(c) FROM App\Entity\Conge c WHERE c.statut = :s')
            ->setParameter('s', 'EN_ATTENTE')->getSingleScalarResult();

        $contratsActifs  = $this->em->createQuery('SELECT COUNT(c) FROM App\Entity\Contrat c WHERE c.statut = :s')
            ->setParameter('s', 'Actif')->getSingleScalarResult();

        return $this->json([
            'totalEmployes'   => (int)$totalEmployes,
            'projetsActifs'   => (int)$projetsActifs,
            'congesEnAttente' => (int)$congesEnAttente,
            'contratsActifs'  => (int)$contratsActifs,
        ]);
    }
}
