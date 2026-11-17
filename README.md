# Collection API

API REST du **Gestionnaire de Collection** : gère les collections d'objets (pièces, figurines, timbres…) de leurs propriétaires, avec une authentification par token.

> Application frontend associée : [collection-web](https://github.com/Mehdi-ahd/collection-web)

## Stack

| Élément | Choix |
| --- | --- |
| Framework | Laravel 13 (PHP 8.4) |
| Base de données | SQLite (dev) / MySQL ou MariaDB (prod possible) |
| Authentification | Sanctum — tokens *opaques* (Bearer) |
| Tests | PHPUnit |
| Style de code | Laravel Pint |

## Démarrage rapide

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed   # jeu de démonstration
php artisan serve     # http://localhost:8000
```

### Compte de démonstration

Créé par le seeder :

| Champ | Valeur |
| --- | --- |
| Email | `demo@collection.dev` |
| Mot de passe | `password1234` |

Avec une collection « Collection mix » contenant 3 objets (images SVG encodées en data URI, exactement comme l'upload base64 du frontend).

## Variables d'environnement

| Variable | Rôle | Défaut |
| --- | --- | --- |
| `DB_CONNECTION` | SQLite en dev (`sqlite`) | `sqlite` |
| `CORS_ALLOWED_ORIGINS` | Origines autorisées à appeler l'API (séparées par des virgules) | `http://localhost:4200` |
| `APP_URL` | URL racine de l'API | `http://localhost:8000` |

## Endpoints

### Publics

| Méthode | Route | Description | Réponse |
| --- | --- | --- | --- |
| `POST` | `/api/register` | Créer un compte (ne connecte pas) | `201` — `{message, data: {user}}` |
| `POST` | `/api/login` | Authentification, génère le token | `200` — `{message, data: {user, token}}` |

### Protégés (header `Authorization: Bearer <token>`)

| Méthode | Route | Description | Réponse |
| --- | --- | --- | --- |
| `GET` | `/api/user` | Utilisateur connecté (restauration de session) | `200` |
| `GET` | `/api/collection` | Collections de l'utilisateur (+ items) | `200` |
| `GET` | `/api/collection/{id}` | Détail d'une collection (+ items) | `200` / `404` |
| `POST` | `/api/collection` | Créer une collection | `201` |
| `PUT` | `/api/collection/{id}` | Modifier une collection | `200` |
| `DELETE` | `/api/collection/{id}` | Supprimer une collection (items inclus) | `200` |
| `POST` | `/api/collection/{id}/item` | Créer un objet dans une collection | `201` |
| `GET` | `/api/item/{id}` | Détail d'un objet | `200` / `404` |
| `PUT` | `/api/item/{id}` | Modifier un objet | `200` |
| `DELETE` | `/api/item/{id}` | Supprimer un objet | `200` |
| `POST` | `/api/logout` | Révoquer le token courant | `200` |

## Contrat de réponse

Toute réponse suit le même contrat :

```json
{
  "message": "…",
  "data": { "…": "…" }
}
```

- Succès : code 2xx + `data` rempli (ou `[]`).
- Erreur de validation : code `422` + `errors` détaillées par champ.
- Non authentifié : `401` (JSON, même sans header `Accept`).
- Ressource inexistante **ou appartenant à un autre utilisateur** : `404` (pas de fuite d'information).

## Schéma de données

```
users ──1,n──> collections ──1,n──> collection_items
```

- `users` : `lastname`, `firstname`, `email` (unique), `password` (hash).
- `collections` : `user_id`, `title`.
- `collection_items` : `collection_id`, `name`, `description`, `image` (TEXT — data URI base64), `rarity` (Common / Uncommon / Rare / Legendary — enum `CollectionRarity`), `price` (entier ≥ 0).

## Tests

```bash
php artisan test --compact
```

Couverture :
- `AuthTest` — `GET /api/user` avec/sans token ;
- `CollectionsTest` — CRUD collections et objets, isolation entre utilisateurs, validation (rareté inconnue, prix négatif, titre vide).

## Découpe du code

```
app/
├── Enums/CollectionRarity.php           # raretés autorisées (validation + seeds)
├── Http/Controllers/Auth/               # login, register, logout
├── Http/Controllers/CollectionController.php
├── Http/Controllers/CollectionItemController.php
├── Http/Requests/                       # validation par requête (FormRequest)
├── Models/                              # User, Collection, CollectionItem
database/
├── migrations/                          # schéma (incl. image TEXT nullable)
├── factories/                           # User, Collection, CollectionItem
└── seeders/DatabaseSeeder.php           # données de démo
```
