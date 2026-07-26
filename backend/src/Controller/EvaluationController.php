<?php
namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/evaluations', name: 'evaluation_')]
class EvaluationController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $items = $this->em->getRepository(Evaluation::class)
            ->findBy([], ['createdAt' => 'DESC']);
        return $this->json(array_map(fn(Evaluation $e) => $e->toArray(), $items));
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $e = $this->em->find(Evaluation::class, $id);
        return $e ? $this->json($e->toArray()) : $this->json(['message' => 'Introuvable.'], 404);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $employe = $this->em->find(User::class, $data['employeId'] ?? 0);
        if (!$employe) {
            return $this->json(['message' => 'Employé introuvable.'], 404);
        }

        $e = new Evaluation();
        $e->setEmploye($employe)
          ->setPeriode($data['periode'] ?? '')
          ->setCompetences((string)($data['competences'] ?? 0))
          ->setTeamwork((string)($data['teamwork'] ?? 0))
          ->setInitiative((string)($data['initiative'] ?? 0))
          ->setCommentaire($data['commentaire'] ?? null);
        $e->computeGlobalNote();

        $this->em->persist($e);
        $this->em->flush();
        return $this->json($e->toArray(), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $e = $this->em->find(Evaluation::class, $id);
        if (!$e) return $this->json(['message' => 'Introuvable.'], 404);

        $data = json_decode($request->getContent(), true);
        if (isset($data['periode']))     $e->setPeriode($data['periode']);
        if (isset($data['competences'])) $e->setCompetences((string)$data['competences']);
        if (isset($data['teamwork']))    $e->setTeamwork((string)$data['teamwork']);
        if (isset($data['initiative']))  $e->setInitiative((string)$data['initiative']);
        if (isset($data['commentaire'])) $e->setCommentaire($data['commentaire']);
        $e->computeGlobalNote();

        $this->em->flush();
        return $this->json($e->toArray());
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $e = $this->em->find(Evaluation::class, $id);
        if (!$e) return $this->json(['message' => 'Introuvable.'], 404);
        $this->em->remove($e);
        $this->em->flush();
        return $this->json(['message' => 'Supprimé.']);
    }
}
