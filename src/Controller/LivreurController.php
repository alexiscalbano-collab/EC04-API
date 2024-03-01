<?php

namespace App\Controller;

use App\Entity\Livreur;
use App\Repository\LivreurRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/livreurs')]
class LivreurController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(LivreurRepository $repo): JsonResponse
    {
        $livreurs = $repo->findAll();
        // on ne retourne jamais le password
        return $this->json($livreurs, 200, [], ['groups' => ['livreur:read']]);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id, LivreurRepository $repo): JsonResponse
    {
        $livreur = $repo->find($id);
        if (!$livreur) {
            return $this->json(['message' => 'Livreur introuvable'], Response::HTTP_NOT_FOUND);
        }
        return $this->json([
            'id'     => $livreur->getId(),
            'prenom' => $livreur->getPrenom(),
            'nom'    => $livreur->getNom(),
            'email'  => $livreur->getEmail(),
            'actif'  => $livreur->isActif(),
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        LivreurRepository $repo,
        UserPasswordHasherInterface $hasher,
        ValidatorInterface $validator,
        MailService $mailer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // vérifier email unique
        if ($repo->findOneBy(['email' => $data['email'] ?? ''])) {
            return $this->json(['message' => 'Cet email est déjà utilisé'], Response::HTTP_CONFLICT);
        }

        $livreur = new Livreur();
        $livreur->setPrenom($data['prenom'] ?? '');
        $livreur->setNom($data['nom'] ?? '');
        $livreur->setEmail($data['email'] ?? '');

        $errors = $validator->validate($livreur);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // générer un mot de passe aléatoire
        $plainPassword = $this->generatePassword();
        $livreur->setPassword($hasher->hashPassword($livreur, $plainPassword));

        $em->persist($livreur);
        $em->flush();

        // envoyer les credentials par email
        $mailer->sendCredentials($livreur->getEmail(), $livreur->getPrenom(), $plainPassword);

        return $this->json([
            'id'     => $livreur->getId(),
            'prenom' => $livreur->getPrenom(),
            'nom'    => $livreur->getNom(),
            'email'  => $livreur->getEmail(),
            'actif'  => $livreur->isActif(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        LivreurRepository $repo,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $livreur = $repo->find($id);
        if (!$livreur) {
            return $this->json(['message' => 'Livreur introuvable'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['prenom'])) $livreur->setPrenom($data['prenom']);
        if (isset($data['nom'])) $livreur->setNom($data['nom']);
        if (isset($data['email'])) $livreur->setEmail($data['email']);
        if (isset($data['actif'])) $livreur->setActif($data['actif']);

        $errors = $validator->validate($livreur);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->flush();

        return $this->json([
            'id'     => $livreur->getId(),
            'prenom' => $livreur->getPrenom(),
            'nom'    => $livreur->getNom(),
            'email'  => $livreur->getEmail(),
            'actif'  => $livreur->isActif(),
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id, LivreurRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $livreur = $repo->find($id);
        if (!$livreur) {
            return $this->json(['message' => 'Livreur introuvable'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($livreur);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function generatePassword(): string
    {
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        return implode('', array_map(
            fn() => $chars[random_int(0, strlen($chars) - 1)],
            array_fill(0, 10, null)
        ));
    }
}
