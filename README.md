# SCI La Thérésière — Application web

Site et outils de gestion pour la **SCI La Thérésière** et la **Salle La Thérésière** : présentation du lieu, formulaires et parcours visiteurs, **espace membre**, **réservations** et **visites sur site**, avec **administration centralisée** (`/panel/admin`).

---

## Sommaire

1. [Fonctionnalités](#fonctionnalités)
2. [Stack technique](#stack-technique)
3. [Prérequis](#prérequis)
4. [Installation](#installation)
5. [Développement local](#développement-local)
6. [Commandes utiles](#commandes-utiles)
7. [Licence](#licence)

---

## Fonctionnalités

| Zone | Rôle | Contenu |
|------|------|--------|
| **Site public** | Tous | Accueil, carrousel, catégories, options de prestation, **tarifs** (liste publique sur `/tarif`), pages légales |
| **Compte & sécurité** | Visiteurs / utilisateurs | Inscription, connexion (`SecurityController`), profil utilisateur |
| **Réservations** | `ROLE_USER` | Demandes avec cycle de vie (statuts du type contrat / confirmation — enum `ReservationStatus`), historique paginé |
| **Visites** | Utilisateurs authentifiés | Prise de rendez‑vous, créneaux, historique personnel |
| **Contact & messages** | Public / connecté selon flux | Demandes et messagerie gérées côté admin (`ContactController`, `MessageController`) |
| **Administration** | `ROLE_ADMIN` | Tableau de bord utilisateurs / visites / réservations ; modération (promotion admin, ban / réintégration, suppression) avec soumissions **POST + CSRF** ; **édition complète des tarifs** (`/tarif/new`, `/tarif/{id}/edit`) |

Les écrans d’édition du contenu (carrousel, catégories, options, pages légales, etc.) sont réservés aux comptes disposant des droits Symfony appropriés (`ROLE_ADMIN` ou routes protégées existantes).

---

## Stack technique

| Élément | Choix du projet |
|--------|------------------|
| Runtime | PHP **≥ 8.2** |
| Framework | Symfony **7.3** |
| ORM | Doctrine **3.x** + migrations |
| Base de données | **MySQL / MariaDB** (via `DATABASE_URL`) |
| Front | Twig, **Symfony UX** (Stimulus, Turbo), **Asset Mapper** |
| Annexes fréquentes | Security, Mailer, Messenger (transport **sync** par défaut dans `.env`), **KnpPaginator** |

Voir `composer.json` pour les versions exactes et les bundles.

---

## Prérequis

- PHP **8.2+** avec extensions Symfony usuelles (`pdo_mysql`, `ctype`, `iconv`, etc.).
- **Composer** 2.x.
- **MySQL** ou **MariaDB**.
- *(Optionnel)* [Symfony CLI](https://symfony.com/download).
- *(Optionnel sous Windows)* WAMP/XAMPP : faire pointer le virtual host ou le dossier web vers le répertoire **`public/`**.

---

## Installation

### 1. Dépendances

```bash
git clone <URL_DU_DÉPÔT> sci_la_theresiere
cd sci_la_theresiere
composer install
```

Les scripts `post-install` exécutent notamment `cache:clear`, `assets:install` et `importmap:install`.

### 2. Environnement

Créez un fichier **`.env.local`** à la racine (non versionné). Définissez au minimum :

| Variable | Rôle |
|----------|------|
| `APP_SECRET` | Chaîne aléatoire suffisamment longue |
| `DATABASE_URL` | Connexion Doctrine, ex. `mysql://user:pass@127.0.0.1:3306/nom_base?serverVersion=8.0.32&charset=utf8mb4` |

Adaptez `serverVersion` au moteur réel (exemples commentés dans `.env`).

Configurez **`MAILER_DSN`** (et tout autre service externe) dans `.env.local` pour ne pas commiter de secrets.

### 3. Schéma de base

Créez la base vide côté MySQL/MariaDB, puis :

```bash
php bin/console doctrine:migrations:migrate
```

### 4. Premier administrateur

Aucun jeu de données de démo n’est imposé. Créez un utilisateur (inscription ou fixture interne), puis attribuez **`ROLE_ADMIN`** (via le panel « promotion » ou procédure SQL / console selon votre habitude).

---

## Développement local

**Recommandé — Symfony CLI**

```bash
symfony server:start
```

**Alternative — serveur PHP**

```bash
php -S 127.0.0.1:8000 -t public
```

**WAMP** : URL du type `http://localhost/sci_la_theresiere/public` selon votre configuration, ou virtual host ciblant uniquement `public/`.

En production, le document root du vhost doit être **`public/`**.

---

## Commandes utiles

| Besoin | Commande |
|--------|----------|
| Vider le cache | `php bin/console cache:clear` |
| Nouvelle migration (après changement d’entités) | `php bin/console make:migration` puis `doctrine:migrations:migrate` |
| Tests | `php bin/phpunit` |

---

## Licence

**Propriétaire** — usage réservé à la SCI La Thérésière et aux parties autorisées.
