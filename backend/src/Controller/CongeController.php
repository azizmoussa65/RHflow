<?php
namespace App\Controller;

use App\Entity\Conge;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/conges', name: 'conge_')]
class CongeController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $qb = $this->em->getRepository(Conge::class)->createQueryBuilder('c');
        if ($s = $request->query->get('statut')) {
            $qb->where('c.statut = :s')->setParameter('s', $s);
        }
        $items = $qb->orderBy('c.createdAt', 'DESC')->getQuery()->getResult();
        return $this->json(array_map(fn(Conge $c) => $c->toArray(), $items));
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $c = $this->em->find(Conge::class, $id);
        return $c ? $this->json($c->toArray()) : $this->json(['message' => 'Introuvable.'], 404);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $employe = $this->em->find(User::class, $data['employeId'] ?? 0)
                ?? $this->getUser();

        $c = new Conge();
        $c->setEmploye($employe)
          ->setType($data['type'] ?? 'Annuel')
          ->setDateDebut(new \DateTime($data['dateDebut'] ?? 'now'))
          ->setDateFin(new \DateTime($data['dateFin'] ?? 'now'))
          ->setMotif($data['motif'] ?? null);

        // Compute nb_jours
        $diff = $c->getDateDebut()->diff($c->getDateFin());
        $c->setNbJours(max(1, $diff->days));

        $this->em->persist($c);
        $this->em->flush();
        return $this->json($c->toArray(), 201);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $c = $this->em->find(Conge::class, $id);
        if (!$c) return $this->json(['message' => 'Introuvable.'], 404);
        $this->em->remove($c); $this->em->flush();
        return $this->json(['message' => 'Supprimé.']);
    }

    /** PATCH /api/conges/:id/approve */
    #[Route('/{id}/approve', name: 'approve', methods: ['PATCH'])]
    public function approve(int $id): JsonResponse
    {
        $c = $this->em->find(Conge::class, $id);
        if (!$c) return $this->json(['message' => 'Introuvable.'], 404);
        $c->setStatut('APPROUVE');
        $this->em->flush();
        return $this->json($c->toArray());
    }

    /** PATCH /api/conges/:id/refuse */
    #[Route('/{id}/refuse', name: 'refuse', methods: ['PATCH'])]
    public function refuse(int $id): JsonResponse
    {
        $c = $this->em->find(Conge::class, $id);
        if (!$c) return $this->json(['message' => 'Introuvable.'], 404);
        $c->setStatut('REFUSE');
        $this->em->flush();
        return $this->json($c->toArray());
    }
}
