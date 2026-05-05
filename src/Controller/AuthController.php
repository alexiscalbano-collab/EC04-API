<?php

namespace App\Controller;

use App\Repository\LivreurRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/auth')]
class AuthController extends AbstractController
{
    #[Route('/login', methods: ['POST'])]
    public function login(
        Request $request,
        LivreurRepository $repo,
        UserPasswordHasherInterface $hasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $livreur = $repo->findOneBy(['email' => $data['email'] ?? '']);

        if (!$livreur || !$hasher->isPasswordValid($livreur, $data['password'] ?? '')) {
            return $this->json(['message' => 'Identifiants incorrects'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$livreur->isActif()) {
            return $this->json(['message' => 'Compte désactivé'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $jwtManager->create($livreur);

        return $this->json([
            'access_token' => $token,
            'livreur' => [
                'id'     => $livreur->getId(),
                'prenom' => $livreur->getPrenom(),
                'nom'    => $livreur->getNom(),
                'email'  => $livreur->getEmail(),
            ],
        ]);
    }
}
