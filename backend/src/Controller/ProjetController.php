<?php
namespace App\Controller;

use App\Entity\Projet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/projets', name: 'projet_')]
class ProjetController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $qb = $this->em->getRepository(Projet::class)->createQueryBuilder('p');
        if ($s = $request->query->get('statut')) {
            $qb->where('p.statut = :s')->setParameter('s', $s);
        }
        $items = $qb->orderBy('p.createdAt', 'DESC')->getQuery()->getResult();
        return $this->json(array_map(fn(Projet $p) => $p->toArray(), $items));
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $p = $this->em->find(Projet::class, $id);
        return $p ? $this->json($p->toArray()) : $this->json(['message' => 'Introuvable.'], 404);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $p = new Projet();
        $p->setNom($data['nom'] ?? '')
          ->setDescription($data['description'] ?? null)
          ->setCategorie($data['categorie'] ?? 'Général')
          ->setStatut($data['statut'] ?? 'En cours')
          ->setAvancement((int)($data['avancement'] ?? 0));

        if (!empty($data['deadline'])) {
            $p->setDeadline(new \DateTime($data['deadline']));
        }
        if (!empty($data['couleur'])) {
            $p->setCouleur($data['couleur']);
        }

        $this->em->persist($p);
        $this->em->flush();
        return $this->json($p->toArray(), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $p = $this->em->find(Projet::class, $id);
        if (!$p) return $this->json(['message' => 'Introuvable.'], 404);

        $data = json_decode($request->getContent(), true);
        if (isset($data['nom']))         $p->setNom($data['nom']);
        if (isset($data['description'])) $p->setDescription($data['description']);
        if (isset($data['categorie']))   $p->setCategorie($data['categorie']);
        if (isset($data['statut']))      $p->setStatut($data['statut']);
        if (isset($data['avancement']))  $p->setAvancement((int)$data['avancement']);
        if (!empty($data['couleur']))    $p->setCouleur($data['couleur']);
        if (!empty($data['deadline']))   $p->setDeadline(new \DateTime($data['deadline']));

        $this->em->flush();
        return $this->json($p->toArray());
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $p = $this->em->find(Projet::class, $id);
        if (!$p) return $this->json(['message' => 'Introuvable.'], 404);
        $this->em->remove($p);
        $this->em->flush();
        return $this->json(['message' => 'Supprimé.']);
    }
}
