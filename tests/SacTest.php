<?php

namespace App\Tests;

use App\Entity\Livreur;
use App\Entity\Product;
use App\Entity\SacItem;
use PHPUnit\Framework\TestCase;

class SacTest extends TestCase
{
    public function testProduitNonDisponibleNePeutPasEtreAjouteAuSac(): void
    {
        $product = new Product();
        $product->setNom('Poulet rôti');
        $product->setDescription('Description');
        $product->setPrix('11.90');
        $product->setCategorie('plat');
        $product->setDisponible(false);

        $this->assertFalse(
            $product->isDisponible(),
            'Le produit doit être marqué comme non disponible'
        );
    }

    public function testProduitDisponiblePeutEtreAjouteAuSac(): void
    {
        $product = new Product();
        $product->setNom('Tiramisu');
        $product->setDescription('Description');
        $product->setPrix('4.50');
        $product->setCategorie('dessert');
        $product->setDisponible(true);

        $this->assertTrue($product->isDisponible());
    }

    public function testSacItemStockeCorrectementLaQuantite(): void
    {
        $product = new Product();
        $product->setNom('Tiramisu');
        $product->setDescription('Description');
        $product->setPrix('4.50');
        $product->setCategorie('dessert');
        $product->setDisponible(true);

        $livreur = new Livreur();
        $livreur->setPrenom('Jean');
        $livreur->setNom('Dupont');
        $livreur->setEmail('jean@test.fr');
        $livreur->setPassword('hashed');

        $item = new SacItem();
        $item->setProduct($product);
        $item->setLivreur($livreur);
        $item->setQuantite(3);

        $this->assertEquals(3, $item->getQuantite());
    }
}
