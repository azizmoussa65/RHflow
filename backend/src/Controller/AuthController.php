<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api/auth', name: 'auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface       $em,
        private UserPasswordHasherInterface  $hasher,
        private JWTTokenManagerInterface     $jwt,
        private TokenStorageInterface        $tokenStorage,
    ) {}

    /**
     * POST /api/auth/login
     * Body: { "email": "...", "password": "..." }
     * Returns: { "token": "...", "user": { id, email, prenom, nom, role } }
     */
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email    = $data['email']    ?? '';
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            return $this->json(['message' => 'Email et mot de passe requis.'], 400);
        }

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user || !$this->hasher->isPasswordValid($user, $password)) {
            return $this->json(['message' => 'Email ou mot de passe incorrect.'], 401);
        }

        $token = $this->jwt->create($user);

        return $this->json([
            'token' => $token,
            'user'  => $user->toArray(),
        ]);
    }

    /**
     * GET /api/auth/me
     * Returns current authenticated user info
     */
    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Non authentifié.'], 401);
        }
        return $this->json($user->toArray());
    }

    /**
     * PATCH /api/auth/profile
     * Update the authenticated user's own profile (prenom, nom, email, telephone)
     */
    #[Route('/profile', name: 'profile', methods: ['PATCH'])]
    public function profile(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Non authentifié.'], 401);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['prenom']))    $user->setPrenom($data['prenom']);
        if (isset($data['nom']))       $user->setNom($data['nom']);
        if (isset($data['telephone'])) $user->setTelephone($data['telephone']);
        if (isset($data['email'])) {
            $existing = $this->em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if (!$existing || $existing->getId() === $user->getId()) {
                $user->setEmail($data['email']);
            }
        }
        if (!empty($data['password'])) {
            $user->setPassword($this->hasher->hashPassword($user, $data['password']));
        }

        $this->em->flush();
        return $this->json($user->toArray());
    }
}
