# BDELive — Inform'Aix

*La plateforme officielle du Bureau Des Étudiants du département Informatique — IUT Aix-Marseille*

[![CI/CD Pipeline](https://github.com/AHAMED-Nasser/BdeLive/actions/workflows/phpstan.yml/badge.svg)](https://github.com/AHAMED-Nasser/BdeLive/actions/workflows/phpstan.yml)
[![Vérification PHP](https://github.com/AHAMED-Nasser/BdeLive/actions/workflows/php.yml/badge.svg)](https://github.com/AHAMED-Nasser/BdeLive/actions/workflows/php.yml)
[![Deploy Documentation](https://github.com/AHAMED-Nasser/BdeLive/actions/workflows/deploy-doc.yml/badge.svg?branch=DEV)](https://github.com/AHAMED-Nasser/BdeLive/actions/workflows/deploy-doc.yml)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-%23777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-%234479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%208-%238892BF)](https://phpstan.org/)
[![PHPUnit](https://img.shields.io/badge/PHPUnit-12-%2323A9E1?logo=php&logoColor=white)](https://phpunit.de/)
[![PSR-12](https://img.shields.io/badge/Code%20Style-PSR--12-blue)](https://www.php-fig.org/psr/psr-12/)
[![Licence](https://img.shields.io/badge/Licence-Académique-blue)](LICENSE)

**[🌐 Accéder au site →](https://bdelivesae.alwaysdata.net/index.php?page=home)**

---

> **BDELive — Inform'Aix** est la plateforme web officielle du BDE (Bureau Des Étudiants) du département
> BUT Informatique de l'IUT Aix-Marseille. Elle centralise la communication, la gestion des événements
> et l'accès aux actualités du campus, développée dans le cadre d'une **SAÉ**

---

## Table des matières

- [ Fonctionnalités Principales](#-fonctionnalités-principales)
- [ Architecture & Technologies](#️-architecture--technologies)
- [ Prérequis](#-prérequis)
- [ Installation & Démarrage Rapide](#-installation--démarrage-rapide)
- [ Tests et Qualité](#-tests-et-qualité)
- [ Auteurs & Remerciements](#-auteurs--remerciements)

---

## Fonctionnalités Principales

| Fonctionnalité | Description |
|---|---|
| **Actualités & Articles** | CRUD complet — publication, modification et suppression d'articles par les membres BDE |
| **Billetterie & Événements** | Création d'événements, inscription individuelle, **inscription en équipe** avec système d'invitations par lien |
| **Emploi du temps ADE** | Synchronisation live via parsing de fichiers ICS ADE — vue hebdomadaire pour les 3 promotions BUT |
| **Espace Utilisateur Sécurisé** | Gestion de profil, consentement RGPD, export de données, suppression de compte différée |
| **Panneau d'Administration** | Gestion des membres, modération des contenus, supervision des inscriptions |
| **Dark Mode** | Thème sombre natif CSS3, persistant sans JavaScript framework |
| **Export PDF** | Génération de billets et résumés d'inscription via DomPDF |
| **Sécurité renforcée** | reCAPTCHA v2, vérification d'e-mail à l'inscription, protection anti-bruteforce |

---

## Architecture & Technologies

### Stack Technique

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/HTML5-Sémantique-E34F26?style=flat-square&logo=html5&logoColor=white" alt="HTML5">
  <img src="https://img.shields.io/badge/CSS3-Flexbox%2FGrid-1572B6?style=flat-square&logo=css3&logoColor=white" alt="CSS3">
  <img src="https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?style=flat-square&logo=javascript&logoColor=black" alt="JavaScript">
  <img src="https://img.shields.io/badge/Composer-2.x-885630?style=flat-square&logo=composer&logoColor=white" alt="Composer">
  <img src="https://img.shields.io/badge/PHPMailer-7.0-blue?style=flat-square&logo=maildotru&logoColor=white" alt="PHPMailer">
  <img src="https://img.shields.io/badge/Cloudinary-CDN-3448C5?style=flat-square&logo=cloudinary&logoColor=white" alt="Cloudinary">
  <img src="https://img.shields.io/badge/PHPStan-Level%208-8892BF?style=flat-square" alt="PHPStan">
  <img src="https://img.shields.io/badge/PHPUnit-12-23A9E1?style=flat-square&logo=php&logoColor=white" alt="PHPUnit">
  <img src="https://img.shields.io/badge/GitHub%20Actions-CI%2FCD-2088FF?style=flat-square&logo=githubactions&logoColor=white" alt="GitHub Actions">
  <img src="https://img.shields.io/badge/DomPDF-3.0-FF6B6B?style=flat-square" alt="DomPDF">
</p>

| Couche | Technologie |
|---|---|
| **Backend** | PHP 8.2+, architecture MVC 2 custom (sans Laravel, sans Symfony) |
| **Routeur** | `app/rooter.php` + `Core/Application.php` — dispatch centralisé |
| **DI Container** | `Core/Container.php` + `Core/ContainerFactory.php` — injection de dépendances |
| **Base de données** | MySQL / MariaDB via PDO, *Prepared Statements* exclusivement |
| **Frontend** | HTML5 sémantique, CSS3 (Flexbox / Grid, Dark Mode), JavaScript Vanilla |
| **Sécurité** | CSRF global (`Core/Security/CsrfProtection.php`), XSS (`htmlspecialchars`), reCAPTCHA v2 |
| **Emails** | PHPMailer 7 (SMTP) |
| **Images CDN** | Cloudinary SDK PHP 3 |
| **Export PDF** | DomPDF 3 |
| **Tâches planifiées** | Scripts CRON — nettoyage tokens expirés, purge des comptes en attente |
| **Qualité** | PHPStan level 8, PHPUnit 12, PHP_CodeSniffer (PSR-12) |
| **CI/CD** | GitHub Actions — 3 pipelines (qualité, vérification PHP, déploiement doc) |

### Architecture MVC

BDELive suit un pattern **MVC 2** organisé par domaine fonctionnel. Le routeur central (`rooter.php`) analyse
la requête HTTP entrante et dispatche vers le contrôleur approprié via un conteneur d'injection de
dépendances. Chaque domaine (Articles, Events, Users, Admin, Public…) regroupe ses propres contrôleurs,
modèles, repositories et vues.

> La documentation technique complète (diagrammes de classes UML, diagrammes de séquence,
> diagramme des cas d'utilisation, conformité MVC) est disponible dans le dossier `docs/`.
>
> **[Voir l'Architecture Détaillée & les Diagrammes UML →](docs/)**

---

## Prérequis

Avant de commencer, assurez-vous de disposer des éléments suivants :

- **PHP** `>= 8.2` avec les extensions `pdo_mysql`, `mbstring`, `xml`, `curl`
- **Composer** `>= 2.x` — [getcomposer.org](https://getcomposer.org/)
- **MySQL** `>= 5.7` ou **MariaDB** `>= 10.4`
- Un serveur web **Apache** / **Nginx** ou le serveur CLI intégré PHP (développement uniquement)
- Accès à un compte **Cloudinary** (gratuit) et à une adresse **SMTP** pour les e-mails

---

## Installation & Démarrage Rapide

### 1. Cloner le dépôt

```bash
git clone https://github.com/AHAMED-Nasser/BdeLive.git
cd BdeLive
```

### 2. Installer les dépendances PHP

```bash
composer install --optimize-autoloader
```

### 3. Configurer l'environnement

```bash
cp .env.example .env
```

Éditez ensuite le fichier `.env` avec vos paramètres :

```env
# Base de données
DB_HOST=localhost
DB_NAME=bdelive_db
DB_USER=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe
DB_CHARSET=utf8mb4

# SMTP (PHPMailer)
SMTP_HOST=smtp.votre-fournisseur.com
SMTP_USER=votre@email.com
SMTP_PASSWORD=votre_mot_de_passe_smtp
FROM_EMAIL=noreply@votre-domaine.com

# Cloudinary (CDN images)
CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME

# Google reCAPTCHA v2
RECAPTCHA_SITE_KEY=votre_site_key
RECAPTCHA_SECRET_KEY=votre_secret_key
```

### 4. Importer la base de données

```bash
mysql -u root -p < BDELive_database_1.1.sql
```

### 5. Lancer le serveur de développement

```bash
php -S localhost:8000 -t app/
```

L'application est accessible sur [http://localhost:8000](http://localhost:8000).

> **Note :** Pour un environnement de production, utilisez Apache ou Nginx avec le `DocumentRoot`
> pointant vers `app/`. Le fichier `.htaccess` inclus gère la réécriture des URLs.

---

## Tests et Qualité

Le projet maintient un niveau de qualité élevé grâce à trois outils intégrés dans le pipeline CI/CD.

### Tests unitaires (PHPUnit 12)

```bash
./vendor/bin/phpunit --testdox
# ou via le script Composer
composer test
```

### Analyse statique (PHPStan — Level 8)

```bash
composer phpstan
```

PHPStan analyse l'intégralité du répertoire `app/` au niveau de rigueur maximal (8/10), couvrant
la résolution de types, les propriétés inaccessibles et les appels de méthodes incorrects.

### Style de code PSR-12 (PHP_CodeSniffer)

```bash
# Vérification
composer lint

# Correction automatique
composer lint:fix
```

> Les trois vérifications sont exécutées automatiquement sur chaque `push` et `pull request`
> via les workflows GitHub Actions.

---

## Auteurs & Remerciements

> Projet réalisé dans le cadre d'une **SAÉ — Semestre 3**, BUT Informatique · IUT Aix-Marseille

<table align="center">
  <tr>
    <td align="center"><b>AHAMED Nasser</b></td>
    <td align="center"><b>BOUDHIB Mohamed-Amine</b></td>
    <td align="center"><b>CANTOR Romain</b></td>
  </tr>
  <tr>
    <td align="center"><b>CHETIOUI Willem</b></td>
    <td align="center"><b>HELALI Amin</b></td>
    <td align="center"><b>PALOT Thomas</b></td>
  </tr>
</table>

### Remerciements

- **M. Olivier Gérard** — encadrement pédagogique et suivi du projet
- **M. Samir Chtioui** — accompagnement méthodologique et organisation
- L'ensemble du **corps enseignant** du département Informatique, IUT Aix-Marseille
---

*Projet académique — usage pédagogique uniquement · IUT Aix-Marseille · BUT Informatique · 2025–2026*


