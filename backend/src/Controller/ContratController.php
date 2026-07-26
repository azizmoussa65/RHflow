<?php
namespace App\Controller;

use App\Entity\Contrat;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/contrats', name: 'contrat_')]
class ContratController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $qb = $this->em->getRepository(Contrat::class)->createQueryBuilder('c');
        if ($s = $request->query->get('statut')) {
            $qb->where('c.statut = :s')->setParameter('s', $s);
        }
        $items = $qb->orderBy('c.dateDebut', 'DESC')->getQuery()->getResult();
        return $this->json(array_map(fn(Contrat $c) => $c->toArray(), $items));
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $c = $this->em->find(Contrat::class, $id);
        return $c ? $this->json($c->toArray()) : $this->json(['message' => 'Introuvable.'], 404);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data    = json_decode($request->getContent(), true);
        $employe = $this->em->find(User::class, $data['employeId'] ?? 0);
        if (!$employe) {
            return $this->json(['message' => 'Employé introuvable.'], 404);
        }

        $c = new Contrat();
        $c->setEmploye($employe)
          ->setType($data['type'] ?? 'CDI')
          ->setSalaire((string)($data['salaire'] ?? 0))
          ->setDateDebut(new \DateTime($data['dateDebut'] ?? 'now'))
          ->setStatut($data['statut'] ?? 'Actif');

        if (!empty($data['dateFin'])) {
            $c->setDateFin(new \DateTime($data['dateFin']));
        }

        $this->em->persist($c);
        $this->em->flush();
        return $this->json($c->toArray(), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $c = $this->em->find(Contrat::class, $id);
        if (!$c) return $this->json(['message' => 'Introuvable.'], 404);

        $data = json_decode($request->getContent(), true);
        if (isset($data['type']))       $c->setType($data['type']);
        if (isset($data['salaire']))    $c->setSalaire((string)$data['salaire']);
        if (isset($data['statut']))     $c->setStatut($data['statut']);
        if (!empty($data['dateDebut'])) $c->setDateDebut(new \DateTime($data['dateDebut']));
        if (!empty($data['dateFin']))   $c->setDateFin(new \DateTime($data['dateFin']));

        $this->em->flush();
        return $this->json($c->toArray());
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $c = $this->em->find(Contrat::class, $id);
        if (!$c) return $this->json(['message' => 'Introuvable.'], 404);
        $this->em->remove($c);
        $this->em->flush();
        return $this->json(['message' => 'Supprimé.']);
    }
}
