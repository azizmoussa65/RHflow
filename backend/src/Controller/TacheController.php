<?php
namespace App\Controller;

use App\Entity\Projet;
use App\Entity\Tache;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/projets/{projetId}/taches', name: 'tache_')]
class TacheController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(int $projetId): JsonResponse
    {
        $projet = $this->em->find(Projet::class, $projetId);
        if (!$projet) return $this->json(['message' => 'Projet introuvable.'], 404);
        $taches = $this->em->getRepository(Tache::class)->findBy(['projet' => $projet]);
        return $this->json(array_map(fn(Tache $t) => $t->toArray(), $taches));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(int $projetId, Request $request): JsonResponse
    {
        $projet = $this->em->find(Projet::class, $projetId);
        if (!$projet) return $this->json(['message' => 'Projet introuvable.'], 404);

        $data = json_decode($request->getContent(), true);
        $t = new Tache();
        $t->setTitre($data['titre'] ?? '')
          ->setDescription($data['description'] ?? null)
          ->setStatut($data['statut'] ?? 'À faire')
          ->setPriorite($data['priorite'] ?? 'Normale')
          ->setProjet($projet);

        if (!empty($data['assigneAId'])) {
            $emp = $this->em->find(User::class, $data['assigneAId']);
            if ($emp) $t->setAssigneA($emp);
        }

        $this->em->persist($t);
        $this->em->flush();
        return $this->json($t->toArray(), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $projetId, int $id, Request $request): JsonResponse
    {
        $t = $this->em->find(Tache::class, $id);
        if (!$t) return $this->json(['message' => 'Introuvable.'], 404);

        $data = json_decode($request->getContent(), true);
        if (isset($data['titre']))       $t->setTitre($data['titre']);
        if (isset($data['description'])) $t->setDescription($data['description']);
        if (isset($data['statut']))      $t->setStatut($data['statut']);
        if (isset($data['priorite']))    $t->setPriorite($data['priorite']);
        if (array_key_exists('assigneAId', $data)) {
            $emp = $data['assigneAId'] ? $this->em->find(User::class, $data['assigneAId']) : null;
            $t->setAssigneA($emp);
        }

        $this->em->flush();
        return $this->json($t->toArray());
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $projetId, int $id): JsonResponse
    {
        $t = $this->em->find(Tache::class, $id);
        if (!$t) return $this->json(['message' => 'Introuvable.'], 404);
        $this->em->remove($t);
        $this->em->flush();
        return $this->json(['message' => 'Supprimé.']);
    }
}
