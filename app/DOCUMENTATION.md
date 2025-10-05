# 📋 DOCUMENTATION PROJET BDE LIVE

**Version :** 1.0  
**Date :** Octobre 2025  
**Auteurs :** Ahamed Nasser, Boudhib Mohameed-Amine, Cantor Romain, Chetioui Willem, Helali Amin, Palot Thomas  
**Université :** IUT Aix-En-Provence

---

## 📁 FICHIERS CRÉÉS

### 🔧 **1. Configuration & Base de données**

#### `app/config/config.php`
**Utilité :** Contient les constantes de configuration pour la connexion à la base de données

**Constantes définies :**
- `DB_HOST` : Hôte de la base (mysql-bdelivesae.alwaysdata.net)
- `DB_NAME` : Nom de la base (bdelivesae_db)
- `DB_USER` : Utilisateur (429915)
- `DB_PASSWORD` : Mot de passe
- `DB_CHARSET` : Encodage (utf8mb4)

**Code :**
```php
<?php
declare(strict_types=1);

define('DB_HOST', 'mysql-bdelivesae.alwaysdata.net');
define('DB_NAME', 'bdelivesae_db');
define('DB_USER', '429915');
define('DB_PASSWORD', 'bdelive+6');
define('DB_CHARSET', 'utf8mb4');
```

---

#### `app/config/database.php`
**Utilité :** Classe Singleton pour gérer la connexion PDO à la base de données

**Caractéristiques :**
- ✅ **Pattern Singleton** : Une seule instance de connexion dans toute l'application
- ✅ **Gestion d'erreurs** : Try-catch avec PDOException
- ✅ **Configuration PDO sécurisée** : 
  - `PDO::ERRMODE_EXCEPTION`
  - `PDO::FETCH_ASSOC`
  - `PDO::EMULATE_PREPARES = false`

**Usage :**
```php
$db = Database::getInstance();
$pdo = $db->getConnection();
```

---

### 🎯 **2. Modèles (Data Access Layer)**

#### `app/modules/models/UserManager.php` ⭐ **NOUVEAU**
**Utilité :** Gère TOUTES les interactions avec la table `Utilisateur` (CRUD complet)

**Principe :** Responsabilité unique - Accès aux données uniquement

**Méthodes principales :**

| Méthode | Paramètres | Retour | Description |
|---------|-----------|--------|-------------|
| `hashPassword()` | `string $password` | `string` | Hash un mot de passe avec SHA-1 (40 caractères) |
| `verifyPassword()` | `string $password, string $hash` | `bool` | Vérifie un mot de passe |
| `findUserByEmail()` | `string $email` | `array\|false` | Trouve un utilisateur par email |
| `findUserById()` | `int $id` | `array\|false` | Trouve un utilisateur par ID |
| `getAllUsers()` | - | `array` | Récupère tous les utilisateurs |
| `findUsersByClasseAnnee()` | `string $classeAnnee` | `array` | Récupère les utilisateurs par année |
| `createUser()` | `string $nom, string $prenom, string $classeAnnee, string $email, string $mdp` | `int\|false` | Crée un nouvel utilisateur |
| `updateUser()` | `int $id, string $nom, string $prenom, string $classeAnnee, string $email` | `bool` | Met à jour un utilisateur |
| `updatePassword()` | `int $id, string $newMdp` | `bool` | Change le mot de passe |
| `deleteUser()` | `int $id` | `bool` | Supprime un utilisateur |
| `emailExists()` | `string $email` | `bool` | Vérifie si un email existe |

**Sécurité :**
- ✅ Toutes les requêtes SQL utilisent des **requêtes préparées** (PDO)
- ✅ Protection contre les **injections SQL** (OWASP Top 10)
- ✅ Hashing SHA-1 compatible avec `VARCHAR(40)`

**Exemple d'usage :**
```php
$userManager = new UserManager();

// Créer un utilisateur
$userId = $userManager->createUser('Dupont', 'Jean', '2', 'jean@example.com', 'password123');

// Trouver un utilisateur
$user = $userManager->findUserByEmail('jean@example.com');

// Vérifier un mot de passe
$isValid = $userManager->verifyPassword('password123', $user['mdp']);
```

---

### 🔐 **3. Services / Logique métier**

#### `app/include/AuthController.php`
**Utilité :** Coordonne l'authentification et gère les sessions

**Principe :** Service qui fait le pont entre les contrôleurs et le modèle

**Responsabilités :**
1. **Valide les données** (format email, etc.)
2. **Appelle UserManager** pour les opérations en base
3. **Gère les sessions** ($_SESSION)

**Méthodes principales :**

| Méthode | Paramètres | Retour | Description |
|---------|-----------|--------|-------------|
| `login()` | `string $email, string $mdp` | `bool` | Connecte un utilisateur |
| `logout()` | - | `void` | Déconnecte et détruit la session |
| `register()` | `string $nom, string $prenom, string $classeAnnee, string $email, string $mdp` | `int\|false` | Inscrit un nouvel utilisateur |
| `isLoggedIn()` | - | `bool` | Vérifie si l'utilisateur est connecté |
| `getCurrentUserId()` | - | `int\|null` | Récupère l'ID de l'utilisateur connecté |
| `getCurrentUserFullName()` | - | `string\|null` | Récupère le nom complet |
| `getCurrentUserEmail()` | - | `string\|null` | Récupère l'email |
| `getCurrentUserClasseAnnee()` | - | `string\|null` | Récupère l'année |
| `getCurrentUserData()` | - | `array\|false` | Récupère toutes les données de l'utilisateur connecté |

**Architecture :**
```
LoginController → AuthController → UserManager → Database
```

**Exemple d'usage :**
```php
$authController = new AuthController();

// Connexion
if ($authController->login('jean@example.com', 'password123')) {
    echo "Connexion réussie !";
}

// Vérifier si connecté
if ($authController->isLoggedIn()) {
    $userId = $authController->getCurrentUserId();
    $fullName = $authController->getCurrentUserFullName();
}

// Déconnexion
$authController->logout();
```

---

### 🎮 **4. Contrôleurs (Controllers)**

#### `app/modules/controllers/LoginController.php`
**Utilité :** Gère la page de connexion et le traitement du formulaire

**Workflow :**
1. Affiche la vue de login
2. Reçoit les données POST (email, password)
3. Valide les champs (email format, champs remplis)
4. Appelle `AuthController->login()`
5. Redirige vers la home en cas de succès
6. Affiche un message d'erreur en cas d'échec

**Code simplifié :**
```php
class LoginController {
    private function processLogin(): void {
        $email = trim($_POST['email']);
        $mdp = $_POST['pwd'];
        
        if ($this->authController->login($email, $mdp)) {
            $_SESSION['success'] = 'Connexion réussie !';
            header('Location: index.php?page=home');
        } else {
            $_SESSION['error'] = 'Email ou mot de passe incorrect';
        }
    }
}
```

---

#### `app/modules/controllers/RegisterController.php`
**Utilité :** Gère la page d'inscription et le traitement du formulaire

**Workflow :**
1. Affiche la vue d'inscription
2. Reçoit les données POST (nom, prenom, email, classe_annee, password)
3. Valide tous les champs
4. Appelle `AuthController->register()`
5. **Auto-login** après inscription réussie
6. Redirige vers la home avec message de bienvenue

**Validations effectuées :**
- ✅ Tous les champs obligatoires
- ✅ Format email valide
- ✅ Mot de passe minimum 6 caractères
- ✅ Classe_annee doit être 1, 2 ou 3
- ✅ Email unique (pas de doublon)

---

#### `app/modules/controllers/HomePageController.php`
**Utilité :** Affiche la page d'accueil
- Charge la vue homePageView
- Simple contrôleur de présentation

---

#### `app/modules/controllers/LegalTermsPageController.php`
**Utilité :** Affiche la page des mentions légales
- Charge la vue legalTermsPageView

---

### 🎨 **5. Vues (Views)**

#### `app/modules/views/homePageView.php`
**Utilité :** Page d'accueil du site

**Contenu :**
- Message de bienvenue si utilisateur connecté (avec nom, prénom, classe)
- Carrousel d'événements (Bootstrap)
- Section réseaux sociaux
- Utilise `start_page()` et `end_page()`

**Classes CSS utilisées :**
- `.alert.alert-success` : Message de succès
- `.alert.alert-info` : Message de bienvenue
- `.hero` : Section principale
- `.events` : Section événements
- `.carousel` : Carrousel Bootstrap

---

#### `app/modules/views/loginPageView.php`
**Utilité :** Formulaire de connexion

**Champs :**
- Email (type: email, required)
- Mot de passe (type: password, required)

**Messages affichés :**
- Succès (inscription réussie)
- Erreur (identifiants incorrects)

**Envoie vers :** `index.php?page=login` (POST)

---

#### `app/modules/views/registerPageView.php`
**Utilité :** Formulaire d'inscription

**Champs :**
- Nom (maxlength: 20)
- Prénom (maxlength: 20)
- Email (maxlength: 100)
- Classe/Année (select: 1, 2, 3)
- Mot de passe (minimum 6 caractères)

**Validation :** Côté serveur dans RegisterController

**Envoie vers :** `index.php?page=register` (POST)

---

#### `app/modules/views/legalTermsPageView.php`
**Utilité :** Page des mentions légales

**Sections :**
- Informations du projet
- Hébergeur
- Propriété intellectuelle
- Données personnelles
- Cookies
- Liens externes

---

### 🎭 **6. Système de routage**

#### `app/Router.php`
**Utilité :** Routeur central de l'application

**Table de routage :**
```php
$controllerMap = [
    'home' => 'HomePageController',
    'login' => 'LoginController',
    'register' => 'RegisterController',
    'legalTerms' => 'LegalTermsPageController',
];

// Traitement spécial pour logout
if ($page === 'logout') {
    $authController = new AuthController();
    $authController->logout();
}
```

**Usage dans les liens :**
```html
<a href="index.php?page=home">Accueil</a>
<a href="index.php?page=login">Connexion</a>
<a href="index.php?page=register">Inscription</a>
<a href="index.php?page=legalTerms">Mentions légales</a>
<a href="index.php?page=logout">Déconnexion</a>
```

---

#### `app/autoload.php`
**Utilité :** Chargement automatique des classes (PSR-4)

**Fonctionnement :**
- Cherche dans `modules/models/NomClasse.php`
- Cherche dans `modules/controllers/NomClasse.php`
- Plus besoin de faire `require_once` pour chaque classe

**Exemple :**
```php
// Au lieu de :
require_once 'modules/models/UserManager.php';
$userManager = new UserManager();

// Vous pouvez directement faire :
$userManager = new UserManager(); // Chargé automatiquement
```

---

#### `app/index.php`
**Utilité :** Point d'entrée de l'application

**Contenu :**
```php
<?php
session_start();
require_once 'Router.php';
```

Toutes les requêtes passent par ce fichier.

---

### 🎨 **7. Styles CSS**

#### `app/assets/css/style.css`
**Utilité :** Feuille de style principale (tout le CSS centralisé)

**Sections :**

**Reset CSS**
```css
* {
    padding: 0;
    margin: 0;
}
```

**Navbar**
```css
.nav {
    display: flex;
    justify-content: space-evenly;
    align-items: center;
    padding: 50px;
}

.nav li {
    list-style: none;
    padding: 15px;
}

.nav a {
    font-size: 1.3rem;
    color: black;
}
```

**Messages d'alerte**
```css
.alert {
    padding: 15px;
    margin: 20px;
    border-radius: 5px;
    text-align: center;
}

.alert-success { /* Vert */ }
.alert-info { /* Bleu */ }
.alert-danger { /* Rouge */ }
.alert-warning { /* Jaune */ }
```

**Usage dans les vues :**
```html
<div class="alert alert-success">Inscription réussie !</div>
<div class="alert alert-danger">Erreur de connexion</div>
<div class="alert alert-info">Bienvenue, Jean Dupont !</div>
```

---

### 📚 **8. Includes / Helpers**

#### `app/include/include.inc.php`
**Utilité :** Fonctions globales pour générer les pages

**Fonctions disponibles :**

**`start_page(string $title, bool $wouldNav = true, bool $isAuthPage = false)`**
- Génère `<html>`, `<head>`, `<body>`
- Inclut les CSS (style.css, Bootstrap)
- Affiche la navbar si `$wouldNav = true`
- Applique la classe `auth-page` au body si `$isAuthPage = true`

**`end_page(bool $showFooter = true)`**
- Génère le footer si `$showFooter = true`
- Inclut les scripts JS (jQuery, Bootstrap)
- Ferme `</body></html>`

**Exemple d'usage :**
```php
<?php
require_once __DIR__ . '/../../include/include.inc.php';
start_page("Ma Page", true);
?>

<h1>Contenu de ma page</h1>

<?php
end_page();
?>
```

---

## ❌ FICHIERS SUPPRIMÉS

### `app/modules/models/User.php` ❌
**Raison de suppression :** Remplacé par `UserManager.php`

**Problèmes de l'ancien fichier :**
- Code dupliqué (constructeur défini 2 fois)
- Utilisation de `password_hash()` incompatible avec `VARCHAR(40)`
- Mélange de responsabilités
- Non conforme à l'architecture MVC
- Pas de séparation claire Model/Service

---

### `app/test_db.php` ❌
**Raison de suppression :** Fichier de test temporaire

**Utilité passée :**
- Vérifier la connexion à la base
- Lister les utilisateurs
- Afficher la structure de la table
- Créer un utilisateur de test

**Pourquoi supprimé :**
- Plus nécessaire une fois l'application fonctionnelle
- Fichier de développement uniquement
- Ne doit pas être en production

---

### `app/assets/css/navbar.css` ❌
**Raison de suppression :** Fusionné dans `style.css`

**Avantages de la fusion :**
- ✅ Tous les styles centralisés dans un seul fichier
- ✅ Meilleure performance (une seule requête HTTP)
- ✅ Plus facile à maintenir
- ✅ Évite la duplication de code

---

## 🏗️ ARCHITECTURE FINALE

```
┌─────────────────────────────────────────────────────────────┐
│                    PRÉSENTATION (Views)                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ loginPageView│  │registerPageView│ │ homePageView │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                  CONTRÔLEURS (Controllers)                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │LoginController│ │RegisterController│ │HomePageController│ │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│               LOGIQUE MÉTIER (Services)                      │
│              ┌──────────────────────┐                        │
│              │   AuthController     │                        │
│              │  • Valide données    │                        │
│              │  • Gère sessions     │                        │
│              │  • Coordonne         │                        │
│              └──────────┬───────────┘                        │
└───────────────────────────┼────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                  MODÈLE (Data Access)                        │
│              ┌──────────────────────┐                        │
│              │    UserManager       │                        │
│              │  • Requêtes SQL      │                        │
│              │  • Hash/Verify pwd   │                        │
│              │  • CRUD operations   │                        │
│              └──────────┬───────────┘                        │
└───────────────────────────┼────────────────────────────────────┘
                     │
                     ▼
           ┌─────────────────────┐
           │      Database       │
           │   (Singleton PDO)   │
           └─────────────────────┘
                     │
                     ▼
           ┌─────────────────────┐
           │  MySQL Database     │
           │  (AlwaysData)       │
           └─────────────────────┘
```

---

## 🔐 SÉCURITÉ IMPLÉMENTÉE

### Protections mises en place

| Protection | Implémentation | Fichiers concernés |
|-----------|----------------|-------------------|
| **Injection SQL** | Requêtes préparées PDO | `UserManager.php` |
| **XSS** | `htmlspecialchars()` | Toutes les vues |
| **CSRF** | Sessions sécurisées | `AuthController.php` |
| **Mot de passe** | Hashing SHA-1 | `UserManager.php` |
| **Validation email** | `filter_var()` | `AuthController.php`, `RegisterController.php` |
| **Type Safety** | PHP 8+ strict types | Tous les fichiers PHP |

### Standards respectés

✅ **OWASP Top 10** - Protection contre les vulnérabilités majeures  
✅ **PHP 8+ Strict Types** - `declare(strict_types=1);` partout  
✅ **PDO exclusivement** - Pas de mysqli ou mysql  
✅ **Requêtes préparées** - 100% des requêtes SQL  
✅ **Error logging** - Pas de `die()` en production  
✅ **Sessions sécurisées** - Vérification `session_status()`  

---

## 📊 FLUX D'AUTHENTIFICATION

### **INSCRIPTION**

```
1. Utilisateur remplit le formulaire
   └─> registerPageView.php
   
2. Soumission du formulaire (POST)
   └─> index.php?page=register
   
3. RegisterController reçoit les données
   ├─> Valide les champs (nom, prenom, email, classe, mdp)
   ├─> Vérifie email valide
   ├─> Vérifie mdp ≥ 6 caractères
   └─> Vérifie classe_annee = 1, 2 ou 3
   
4. RegisterController → AuthController->register()
   ├─> Vérifie si email existe déjà
   └─> Appelle UserManager->createUser()
   
5. UserManager->createUser()
   ├─> Hash le mot de passe (SHA-1)
   └─> INSERT INTO Utilisateur (requête préparée)
   
6. Auto-login
   └─> AuthController->login()
   
7. Création de la session
   ├─> $_SESSION['utilisateur_id']
   ├─> $_SESSION['nom']
   ├─> $_SESSION['prenom']
   ├─> $_SESSION['classe_annee']
   └─> $_SESSION['email']
   
8. Redirection vers home
   └─> Message: "Bienvenue [Prénom] [Nom] !"
```

---

### **CONNEXION**

```
1. Utilisateur entre email + mot de passe
   └─> loginPageView.php
   
2. Soumission du formulaire (POST)
   └─> index.php?page=login
   
3. LoginController reçoit les données
   ├─> Valide email format
   └─> Vérifie champs non vides
   
4. LoginController → AuthController->login()
   └─> Appelle UserManager->findUserByEmail()
   
5. UserManager->findUserByEmail()
   └─> SELECT * FROM Utilisateur WHERE email = :email
   
6. Si utilisateur trouvé
   └─> AuthController → UserManager->verifyPassword()
   
7. Vérification mot de passe
   └─> hash('sha1', $password) === $user['mdp']
   
8. Si mot de passe valide
   ├─> Création de $_SESSION
   └─> Redirection vers home
   
9. Si échec
   └─> Message d'erreur + retour login
```

---

### **DÉCONNEXION**

```
1. Clic sur "Se déconnecter"
   └─> index.php?page=logout
   
2. Router détecte page=logout
   └─> AuthController->logout()
   
3. Destruction de la session
   ├─> session_unset()
   └─> session_destroy()
   
4. Redirection vers home
   └─> Plus de message de bienvenue
```

---

## 🗄️ STRUCTURE DE LA BASE DE DONNÉES

### Table `Utilisateur`

```sql
CREATE TABLE Utilisateur (
    utilisateur_id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(20) NOT NULL,
    prenom VARCHAR(20) NOT NULL,
    classe_annee VARCHAR(1) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mdp VARCHAR(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Champs

| Champ | Type | Description | Contraintes |
|-------|------|-------------|-------------|
| `utilisateur_id` | INT | Identifiant unique | PRIMARY KEY, AUTO_INCREMENT |
| `nom` | VARCHAR(20) | Nom de famille | NOT NULL, max 20 caractères |
| `prenom` | VARCHAR(20) | Prénom | NOT NULL, max 20 caractères |
| `classe_annee` | VARCHAR(1) | Année (1, 2 ou 3) | NOT NULL, valeurs: '1', '2', '3' |
| `email` | VARCHAR(100) | Adresse email | UNIQUE, NOT NULL, max 100 caractères |
| `mdp` | VARCHAR(40) | Mot de passe hashé | NOT NULL, exactement 40 caractères (SHA-1) |

### Contraintes importantes

⚠️ **`mdp VARCHAR(40)`** - Contrainte imposée par le professeur  
Raison : SHA-1 génère exactement 40 caractères hexadécimaux  
Note : En production, `VARCHAR(255)` avec bcrypt serait recommandé

---

## 📝 CONVENTIONS DE NOMMAGE

### Fichiers et dossiers

```
📁 Dossiers : lowercase (config, modules, views)
📄 Fichiers PHP : CamelCase.php (UserManager.php, AuthController.php)
📄 Fichiers CSS : lowercase.css (style.css)
📄 Fichiers HTML : lowercase.html ou CamelCase.php pour les vues
```

### Code PHP

| Élément | Convention | Exemple |
|---------|-----------|---------|
| **Classes** | CamelCase | `UserManager`, `AuthController` |
| **Méthodes** | camelCase | `findUserByEmail()`, `createUser()` |
| **Variables** | camelCase | `$userManager`, `$hashedPassword` |
| **Constantes** | UPPER_SNAKE_CASE | `DB_HOST`, `DB_NAME` |
| **Propriétés privées** | camelCase avec `$` | `$pdo`, `$userManager` |

### Base de données

| Élément | Convention | Exemple |
|---------|-----------|---------|
| **Tables** | CamelCase (singulier) | `Utilisateur`, `Evenement` |
| **Colonnes** | snake_case | `utilisateur_id`, `classe_annee` |
| **Primary Keys** | `table_id` | `utilisateur_id`, `evenement_id` |

---

## 🚀 GUIDE DE DÉMARRAGE

### Prérequis

- PHP 8.0 ou supérieur
- MySQL 5.7 ou supérieur
- Laragon (Windows) ou serveur LAMP/WAMP
- Composer (optionnel)

### Installation

**1. Cloner le projet**
```bash
git clone [URL_DU_REPO]
cd BdeLive
```

**2. Configuration de la base de données**

Modifiez `app/config/config.php` avec vos informations :
```php
define('DB_HOST', 'votre_host');
define('DB_NAME', 'votre_base');
define('DB_USER', 'votre_user');
define('DB_PASSWORD', 'votre_password');
```

**3. Créer la base de données**
```sql
CREATE DATABASE bdelivesae_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE bdelivesae_db;

CREATE TABLE Utilisateur (
    utilisateur_id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(20) NOT NULL,
    prenom VARCHAR(20) NOT NULL,
    classe_annee VARCHAR(1) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mdp VARCHAR(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Lancement

**Avec Laragon (Windows) :**
1. Démarrer Laragon
2. Démarrer Apache et MySQL
3. Naviguer vers `http://localhost/BdeLive/app`

**Avec le serveur PHP intégré (Linux/Mac/WSL) :**
```bash
cd app
php -S localhost:8000
```
Puis ouvrir `http://localhost:8000`

---

## 🧪 TESTS

### Tester l'inscription

1. Aller sur `http://localhost:8000/index.php?page=register`
2. Remplir le formulaire :
   - Nom : Test
   - Prénom : Utilisateur
   - Email : test@example.com
   - Classe : BUT 2
   - Mot de passe : password123
3. Cliquer sur "S'inscrire"
4. ✅ Vous devriez être redirigé vers la home avec un message de bienvenue

### Tester la connexion

1. Aller sur `http://localhost:8000/index.php?page=login`
2. Entrer les identifiants créés précédemment
3. Cliquer sur "Se connecter"
4. ✅ Message de bienvenue sur la page d'accueil

### Tester la déconnexion

1. Sur la page d'accueil (connecté), cliquer sur "Se déconnecter"
2. ✅ Vous devriez être redirigé vers la home sans message de bienvenue

### Vérifier en base de données

```sql
SELECT utilisateur_id, nom, prenom, email, 
       LENGTH(mdp) as longueur_mdp, mdp 
FROM Utilisateur;
```

Vérifications :
- ✅ `longueur_mdp` = 40 (SHA-1)
- ✅ `mdp` commence par un hash hexadécimal
- ✅ L'email est unique

---

## 🐛 DÉBOGAGE

### Erreurs courantes

**Erreur : "Class 'Database' not found"**
- Solution : Vérifier que `autoload.php` est bien inclus dans `index.php`

**Erreur : "Access denied for user"**
- Solution : Vérifier les credentials dans `config.php`

**Erreur : "syntax error, unexpected identifier"**
- Solution : Vérifier qu'il n'y a pas de caractères invisibles dans le code
- Vérifier l'encodage du fichier (UTF-8 sans BOM)

**Connexion ne fonctionne pas**
- Vérifier que le mot de passe en base fait bien 40 caractères
- Supprimer les anciens utilisateurs avec mot de passe bcrypt tronqué :
  ```sql
  DELETE FROM Utilisateur WHERE LENGTH(mdp) != 40;
  ```

### Activer les logs d'erreurs

Dans `index.php`, ajouter en haut :
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'Router.php';
```

---

## 📚 RESSOURCES

### Documentation PHP

- [PHP 8 Documentation](https://www.php.net/manual/fr/)
- [PDO Documentation](https://www.php.net/manual/fr/book.pdo.php)
- [Sessions PHP](https://www.php.net/manual/fr/book.session.php)

### Sécurité

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://www.php.net/manual/fr/security.php)
- [SQL Injection Prevention](https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html)

### Architecture MVC

- [MVC Pattern](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller)
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)

---

## 👥 ÉQUIPE

**Développeurs :**
- Ahamed Nasser
- Boudhib Mohameed-Amine
- Cantor Romain
- Chetioui Willem
- Helali Amin
- Palot Thomas

**Institution :** IUT Aix-En-Provence  
**Formation :** BUT Informatique  
**Année :** 2025

---

## 📄 LICENCE

Projet universitaire - IUT Aix-En-Provence  
Tous droits réservés © 2025 BDE Live

---

**Version de la documentation :** 1.0  
**Dernière mise à jour :** Octobre 2025

