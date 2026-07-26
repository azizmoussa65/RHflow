<?php
namespace App\Controller;

use App\Entity\Conge;
use App\Entity\Contrat;
use App\Entity\DossierAdmin;
use App\Entity\Evaluation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Employé CRUD — accessible par MANAGER et RH.
 * Chaque userId (ROLE_EMPLOYE) est géré ici comme "employé".
 */
#[Route('/api/employes', name: 'employe_')]
class EmployeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface      $em,
        private UserPasswordHasherInterface $hasher,
    ) {}

    /** GET /api/employes — Liste tous les employés */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $qb = $this->em->getRepository(User::class)->createQueryBuilder('u')
            ->where('u.roles NOT LIKE :manager AND u.roles NOT LIKE :rh')
            ->setParameter('manager', '%ROLE_MANAGER%')
            ->setParameter('rh',      '%ROLE_RH%');

        if ($dept = $request->query->get('departement')) {
            $qb->andWhere('u.departement = :dept')->setParameter('dept', $dept);
        }
        if ($statut = $request->query->get('statut')) {
            $qb->andWhere('u.statut = :statut')->setParameter('statut', $statut);
        }

        $users = $qb->getQuery()->getResult();
        return $this->json(array_map(fn(User $u) => $u->toArray(), $users));
    }

    /** GET /api/employes/:id */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $u = $this->em->find(User::class, $id);
        return $u ? $this->json($u->toArray()) : $this->json(['message' => 'Introuvable.'], 404);
    }

    /** POST /api/employes — Créer un employé */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email'])
             ->setPrenom($data['prenom'])
             ->setNom($data['nom'])
             ->setTelephone($data['telephone'] ?? null)
             ->setDepartement($data['departement'] ?? null)
             ->setPoste($data['poste'] ?? null)
             ->setRoles(['ROLE_EMPLOYE'])
             ->setPassword($this->hasher->hashPassword($user, $data['password'] ?? 'password'))
             ->setStatut('Actif');

        if (!empty($data['dateEmbauche'])) {
            $user->setDateEmbauche(new \DateTimeImmutable($data['dateEmbauche']));
        }

        $this->em->persist($user);
        $this->em->flush();

        return $this->json($user->toArray(), 201);
    }

    /** PUT /api/employes/:id — Modifier un employé */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->em->find(User::class, $id);
        if (!$user) return $this->json(['message' => 'Introuvable.'], 404);

        $data = json_decode($request->getContent(), true);
        if (isset($data['prenom']))      $user->setPrenom($data['prenom']);
        if (isset($data['nom']))         $user->setNom($data['nom']);
        if (isset($data['email']))       $user->setEmail($data['email']);
        if (isset($data['telephone']))   $user->setTelephone($data['telephone']);
        if (isset($data['departement'])) $user->setDepartement($data['departement']);
        if (isset($data['poste']))       $user->setPoste($data['poste']);
        if (isset($data['statut']))      $user->setStatut($data['statut']);

        $this->em->flush();
        return $this->json($user->toArray());
    }

    /** DELETE /api/employes/:id */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->em->find(User::class, $id);
        if (!$user) return $this->json(['message' => 'Introuvable.'], 404);

        // Supprimer les données liées avant de supprimer l'employé
        foreach ($this->em->getRepository(Conge::class)->findBy(['employe' => $user]) as $r) $this->em->remove($r);
        foreach ($this->em->getRepository(Contrat::class)->findBy(['employe' => $user]) as $r) $this->em->remove($r);
        foreach ($this->em->getRepository(Evaluation::class)->findBy(['employe' => $user]) as $r) $this->em->remove($r);
        foreach ($this->em->getRepository(DossierAdmin::class)->findBy(['employe' => $user]) as $r) $this->em->remove($r);

        $this->em->remove($user);
        $this->em->flush();
        return $this->json(['message' => 'Supprimé.']);
    }
}
