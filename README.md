# BDELIVE

BDELive est le site d'internet du Bureau Des Etudiants du BUT Informatique d'Aix-en-Provence (aussi appelé Inform'Aix'), permettant de se renseigner sur les différentes actualités et événements du BDE.


![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![Licence](https://img.shields.io/badge/licence-MIT-green.svg)

---

## Table des matières

- [À propos](#-à-propos)
- [Fonctionnalités](#-fonctionnalités)
- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Utilisation](#-utilisation)
- [Tests](#-tests)
- [Structure du projet](#-structure-du-projet)
- [Auteurs](#-auteurs)
- [Essayez le site](#-essayez-le-site)
- [Licence](#-licence)
- [Remerciements](#-remerciements)

---

## À propos

BDELive est une solution numérique centralisant la communication et l'organisation pour optimiser les interactions entre le BDE et l’ensemble des acteurs du campus. Elle a été créer par un groupe d'étudiant concernant un projet universitaire dit "SAE" (Situation d'Apprentissage Evalué).
---

## Fonctionnalités

Sur BDELive, il est possible de 

- Se renseigner sur le BDE Inform'Aix 
- Se connecter, créer un compte, rénitialiser sont mot de passe
- Modifier ses informations de profil 
- Créer, modifier ou s'inscrire a un événement
- Créer modifier, ou visualiser un article d'actualité
- Consulter son emploie du temps 
- Gérer les membres

---

## Prérequis

Avant de commencer, assurez-vous d'avoir installé :

- [PHP](https://www.php.net/) >= 8.0
- [Composer](https://getcomposer.org/)
- [MySQL](https://www.mysql.com/) >= 5.7

---

## Installation

1. **Cloner le dépôt**
- Via bash
   ```bash
   git clone https://github.com/utilisateur/projet.git
   cd projet
   ```
Si vous utilisez un IDE, vous pouvez aussi le clonez via l'interface de l'IDE.

3. **Installer les dépendances**
   ```bash
   composer install
   ```

4. **Copier les fichiers de configuration**
   ```bash
   cp .env.example .env
   ```
---

## Configuration

1. Modifiez le fichier `.env` avec vos paramètres :
   ```env
   DB_HOST=localhost
   DB_NAME=nom_base
   DB_USER=utilisateur
   DB_PASS=mot_de_passe
   ```

---

## Utilisation

Lancer le projet en local avec le logiciel de serveur local de votre choix (exemple : Laragon, Wamp...)

---

## Tests

Pour exécuter les tests :
1. Sur bash Linux
```bash
# Tests unitaires
./vendor/bin/phpunit



# Ou avec le chemin complet 
php vendor/bin/phpunit

```
2. Sur powershell
```
# Tests unitaires
.\vendor\bin\phpunit
```
---

## Structure du projet

```
BdeLive/
├── app/
│   ├── Config/                    # Configuration de l'application
│   ├── Core/                      # Noyau du framework
│   │   ├── Auth/                  # Authentification
│   │   ├── Exception/             # Gestion des exceptions
│   │   ├── Http/                  # Requêtes/Réponses HTTP
│   │   ├── Security/              # Sécurité
│   │   └── Session/               # Gestion des sessions
│   ├── Modules/
│   │   ├── Controllers/           # Contrôleurs
│   │   │   ├── Admin/             # Administration
│   │   │   ├── Articles/          # Gestion des articles
│   │   │   ├── Cookie/            # Consentement cookies
│   │   │   ├── Events/            # Gestion des événements
│   │   │   ├── Public/            # Pages publiques
│   │   │   ├── Pwd/               # Réinitialisation mot de passe
│   │   │   └── Users/             # Gestion des utilisateurs
│   │   ├── Helpers/               # Classes utilitaires
│   │   ├── Models/                # Modèles de données
│   │   │   ├── Admin/             # Modèles admin
│   │   │   ├── Pwd/               # Modèles mot de passe
│   │   │   └── Users/             # Modèles utilisateurs
│   │   ├── Repositories/          # Couche d'accès aux données
│   │   └── views/                 # Vues (templates PHP)
│   ├── Schedule/                  # Gestion du planning
│   ├── Services/                  # Services métier
│   ├── assets/                    # CSS, JS, images
│   ├── cron/                      # Tâches planifiées
│   └── include/                   # Fichiers d'inclusion
├── docs/                          # Documentation
├── scripts/                       # Scripts utilitaires
├── tests/
│   └── Unit/                      # Tests unitaires
└── vendor/                        # Dépendances Composer
```

---

## Auteurs

- AHAMED Nasser
- BOUDHIB Mohamed-Amine
- CANTOR Romain
- CHETIOUI Willem
- HELALI Amin
- PALOT Thomas

---

## Essayez le site 

Essayez le site : bdelivesae.alwaysdata.net

--

## Licence

Projet académique - usage pédagogique uniquement

---

## Remerciements

Mentions spéciale à M. Olivier Gérard pour nous avoir guider durant ce projet et M. Chtioui Samir pour nous avoir aider a nous organiser
Un grand merci au personnel ensaignant du département informatique de l'IUT Aix-En-Provence.

---
