<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240101000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables livreur et sac_item (EC04)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE livreur (
            id INT AUTO_INCREMENT NOT NULL,
            prenom VARCHAR(100) NOT NULL,
            nom VARCHAR(100) NOT NULL,
            email VARCHAR(180) NOT NULL,
            password VARCHAR(255) NOT NULL,
            actif TINYINT(1) NOT NULL DEFAULT 1,
            UNIQUE INDEX UNIQ_EMAIL_LIVREUR (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');

        $this->addSql('CREATE TABLE sac_item (
            id INT AUTO_INCREMENT NOT NULL,
            livreur_id INT NOT NULL,
            product_id INT NOT NULL,
            quantite INT NOT NULL DEFAULT 1,
            CONSTRAINT fk_sac_livreur FOREIGN KEY (livreur_id) REFERENCES livreur(id) ON DELETE CASCADE,
            CONSTRAINT fk_sac_product FOREIGN KEY (product_id) REFERENCES product(id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE sac_item');
        $this->addSql('DROP TABLE livreur');
    }
}
