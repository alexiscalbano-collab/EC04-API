# CityLunch API — EC04 (Symfony)

API REST sécurisée par JWT, développée avec Symfony 7.2.  
Partage la base MySQL du projet EC03 (tables `product`, `customer`).  
Ajoute les tables `livreur` et `sac_item`.

---

## Stack technique

| Composant | Version | Rôle |
|-----------|---------|------|
| PHP | >= 8.2 | Langage |
| Symfony | 7.2 | Framework |
| Doctrine ORM | ^3.3 | ORM MySQL |
| MySQL | 8.0 | Base de données |
| LexikJWTBundle | ^3.0 | Authentification JWT |
| Symfony Mailer | 7.2 | Envoi email credentials |
| PHPUnit | ^10 | Tests unitaires |

---

## Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/alexiscalbano-collab/EC04-API.git
cd citylunch-api-symfony
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer l'environnement

```bash
cp .env .env.local
```

Édite `.env.local` :
- `DATABASE_URL` → port 3307 si tu utilises le Docker de EC03
- `MAILER_DSN` → remplace par tes credentials Mailtrap

### 4. Générer les clés JWT

```bash
mkdir -p config/jwt
php bin/console lexik:jwt:generate-keypair
```

### 5. Démarrer MySQL (depuis EC03)

```bash
cd ../citylunch && docker-compose up -d mysql
```

### 6. Appliquer les migrations

```bash
php bin/console doctrine:migrations:migrate
```

### 7. Lancer le serveur

```bash
php -S 127.0.0.1:8001 -t public
```

API disponible sur **http://localhost:8001**

---

## Routes

### Produits (public)

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/api/v1/products` | Liste tous les produits |
| GET | `/api/v1/products/{id}` | Détail d'un produit |
| POST | `/api/v1/products` | Créer un produit |
| PUT | `/api/v1/products/{id}` | Modifier un produit |
| DELETE | `/api/v1/products/{id}` | Supprimer un produit |

### Livreurs (public)

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/api/v1/livreurs` | Liste tous les livreurs |
| GET | `/api/v1/livreurs/{id}` | Détail d'un livreur |
| POST | `/api/v1/livreurs` | Créer un livreur (envoie email) |
| PUT | `/api/v1/livreurs/{id}` | Modifier un livreur |
| DELETE | `/api/v1/livreurs/{id}` | Supprimer un livreur |

### Auth

| Méthode | Route | Description |
|---------|-------|-------------|
| POST | `/api/v1/auth/login` | Connexion → retourne JWT |

### Sac (JWT requis)

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/api/v1/sac` | Consulter son sac |
| POST | `/api/v1/sac` | Ajouter un produit |
| DELETE | `/api/v1/sac/{productId}` | Retirer un produit |

---

## Lancer les tests

```bash
vendor/bin/phpunit
```

Le test vérifie la règle métier :
> Un livreur ne peut pas ajouter un produit non disponible dans son sac (HTTP 400).

---

## MCD

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│   LIVREUR   │       │   SAC_ITEM  │       │   PRODUCT   │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ id (PK)     │──────<│ id (PK)     │>──────│ id (PK)     │
│ prenom      │       │ livreur_id  │       │ nom         │
│ nom         │       │ product_id  │       │ description │
│ email       │       │ quantite    │       │ prix        │
│ password    │       └─────────────┘       │ categorie   │
│ actif       │                             │ disponible  │
└─────────────┘                             └─────────────┘

Règles métier :
- À la création, un mot de passe est généré et envoyé par email au livreur
- Le livreur se connecte avec email + mot de passe → reçoit un JWT (24h)
- Un produit non disponible ne peut pas être ajouté au sac (HTTP 400)
- Si le produit est déjà dans le sac, la quantité est augmentée
```

---

## Dépôt Git

https://github.com/alexiscalbano-collab/EC04-API

> ⚠️ Remplacer par l'URL réelle avant de rendre.
