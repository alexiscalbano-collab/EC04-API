<?php

namespace App\Controller;

use App\Entity\SacItem;
use App\Repository\ProductRepository;
use App\Repository\SacItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/sac')]
class SacController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(SacItemRepository $repo): JsonResponse
    {
        $livreur = $this->getUser();
        $items = $repo->findBy(['livreur' => $livreur]);

        $data = array_map(fn($item) => [
            'id'       => $item->getId(),
            'product'  => [
                'id'    => $item->getProduct()->getId(),
                'nom'   => $item->getProduct()->getNom(),
                'prix'  => $item->getProduct()->getPrix(),
            ],
            'quantite' => $item->getQuantite(),
        ], $items);

        return $this->json($data);
    }

    #[Route('', methods: ['POST'])]
    public function add(
        Request $request,
        ProductRepository $productRepo,
        SacItemRepository $sacRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $livreur = $this->getUser();

        $product = $productRepo->find($data['productId'] ?? 0);
        if (!$product) {
            return $this->json(['message' => 'Produit introuvable'], Response::HTTP_NOT_FOUND);
        }

        // contrainte métier : produit doit être disponible
        if (!$product->isDisponible()) {
            return $this->json(
                ['message' => "Ce produit n'est pas disponible aujourd'hui"],
                Response::HTTP_BAD_REQUEST
            );
        }

        $quantite = max(1, (int) ($data['quantite'] ?? 1));

        // si déjà dans le sac, on augmente la quantité
        $existant = $sacRepo->findOneBy(['livreur' => $livreur, 'product' => $product]);
        if ($existant) {
            $existant->setQuantite($existant->getQuantite() + $quantite);
            $em->flush();
            $item = $existant;
        } else {
            $item = new SacItem();
            $item->setLivreur($livreur);
            $item->setProduct($product);
            $item->setQuantite($quantite);
            $em->persist($item);
            $em->flush();
        }

        return $this->json([
            'id'       => $item->getId(),
            'product'  => ['id' => $product->getId(), 'nom' => $product->getNom()],
            'quantite' => $item->getQuantite(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{productId}', methods: ['DELETE'])]
    public function remove(int $productId, SacItemRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $livreur = $this->getUser();
        $item = $repo->findOneBy(['livreur' => $livreur, 'product' => $productId]);

        if (!$item) {
            return $this->json(['message' => "Ce produit n'est pas dans votre sac"], Response::HTTP_NOT_FOUND);
        }

        $em->remove($item);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
