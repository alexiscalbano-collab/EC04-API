<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/v1/products')]
class ProductController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(ProductRepository $repo): JsonResponse
    {
        return $this->json($repo->findAll());
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id, ProductRepository $repo): JsonResponse
    {
        $product = $repo->find($id);
        if (!$product) {
            return $this->json(['message' => 'Produit introuvable'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($product);
    }

    #[Route('', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $product = $serializer->deserialize($request->getContent(), Product::class, 'json');

        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($product);
        $em->flush();

        return $this->json($product, Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        ProductRepository $repo,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $product = $repo->find($id);
        if (!$product) {
            return $this->json(['message' => 'Produit introuvable'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['nom'])) $product->setNom($data['nom']);
        if (isset($data['description'])) $product->setDescription($data['description']);
        if (isset($data['prix'])) $product->setPrix($data['prix']);
        if (isset($data['categorie'])) $product->setCategorie($data['categorie']);
        if (isset($data['disponible'])) $product->setDisponible($data['disponible']);

        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->flush();
        return $this->json($product);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id, ProductRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $product = $repo->find($id);
        if (!$product) {
            return $this->json(['message' => 'Produit introuvable'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($product);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
