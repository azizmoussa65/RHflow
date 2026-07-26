<?php
namespace App\Controller;

use App\Entity\DossierAdmin;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/dossiers', name: 'dossier_')]
class DossierController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $items = $this->em->getRepository(DossierAdmin::class)
            ->findBy([], ['dateAjout' => 'DESC']);
        return $this->json(array_map(fn(DossierAdmin $d) => $d->toArray(), $items));
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $d = $this->em->find(DossierAdmin::class, $id);
        return $d ? $this->json($d->toArray()) : $this->json(['message' => 'Introuvable.'], 404);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // Supports JSON and multipart/form-data
        $contentType = $request->getContentTypeFormat();
        if ($contentType === 'json') {
            $data    = json_decode($request->getContent(), true) ?? [];
            $employe = $this->em->find(User::class, $data['employeId'] ?? 0) ?? $this->getUser();
            $titre   = $data['titre'] ?? ($data['type'] ?? 'Document');
            $type    = $data['type'] ?? 'Autre';
        } else {
            $data    = $request->request->all();
            $employe = $this->em->find(User::class, $data['employeId'] ?? 0) ?? $this->getUser();
            $titre   = $data['titre'] ?? ($data['type'] ?? 'Document');
            $type    = $data['type'] ?? 'Autre';
        }

        $d = new DossierAdmin();
        $d->setEmploye($employe)
          ->setTitre($titre)
          ->setType($type);

        $file = $request->files->get('fichier');
        if ($file) {
            $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/dossiers/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = uniqid().'.'.$file->guessExtension();
            $file->move($uploadDir, $filename);
            $d->setFichierPath('uploads/dossiers/'.$filename);
        }

        $this->em->persist($d);
        $this->em->flush();
        return $this->json($d->toArray(), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $d = $this->em->find(DossierAdmin::class, $id);
        if (!$d) return $this->json(['message' => 'Introuvable.'], 404);

        $data = json_decode($request->getContent(), true);
        if (isset($data['titre']))  $d->setTitre($data['titre']);
        if (isset($data['type']))   $d->setType($data['type']);
        if (isset($data['statut'])) $d->setStatut($data['statut']);

        $this->em->flush();
        return $this->json($d->toArray());
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $d = $this->em->find(DossierAdmin::class, $id);
        if (!$d) return $this->json(['message' => 'Introuvable.'], 404);
        $this->em->remove($d);
        $this->em->flush();
        return $this->json(['message' => 'Supprimé.']);
    }

    #[Route('/{id}/valider', name: 'valider', methods: ['PATCH'])]
    public function valider(int $id): JsonResponse
    {
        $d = $this->em->find(DossierAdmin::class, $id);
        if (!$d) return $this->json(['message' => 'Introuvable.'], 404);
        $d->setStatut('Validé');
        $this->em->flush();
        return $this->json($d->toArray());
    }

    #[Route('/{id}/refuser', name: 'refuser', methods: ['PATCH'])]
    public function refuser(int $id): JsonResponse
    {
        $d = $this->em->find(DossierAdmin::class, $id);
        if (!$d) return $this->json(['message' => 'Introuvable.'], 404);
        $d->setStatut('Refusé');
        $this->em->flush();
        return $this->json($d->toArray());
    }
}
