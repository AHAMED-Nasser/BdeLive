# 📋 DOCUMENTATION PROJET BDE LIVE

**Version :** 1.0  
**Date :** Octobre 2025  
**Auteurs :** Ahamed Nasser, Boudhib Mohameed-Amine, Cantor Romain, Chetioui Willem, Helali Amin, Palot Thomas  
**Université :** IUT Aix-En-Provence

---

## 🆕 FONCTIONNALITÉS IMPLÉMENTÉES

### ✅ Système d'authentification complet
- Inscription utilisateur avec validation
- Connexion avec email/mot de passe
- Déconnexion sécurisée
- Gestion des sessions

### ✅ Réinitialisation de mot de passe (3 étapes)
1. **Demande de réinitialisation** : L'utilisateur entre son email
2. **Vérification du code** : Un token de 64 caractères est envoyé par email
3. **Nouveau mot de passe** : L'utilisateur définit son nouveau mot de passe

### ✅ Sécurité
- Tokens avec expiration (3 heures)
- Hachage SHA-1 des mots de passe
- Protection contre les injections SQL (requêtes préparées)
- Validation des entrées utilisateur
- Protection XSS avec htmlspecialchars()

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

#### `app/config/Database.php`
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

#### `app/config/mailer.php` ⭐ **NOUVEAU**
**Utilité :** Classe pour l'envoi d'emails via PHPMailer (réinitialisation mot de passe)

**Configuration SMTP :**
- Host: smtp-bdelivesae.alwaysdata.net
- Port: 587 (STARTTLS)
- Username: bdelivesae@alwaysdata.net

**Méthode principale :**
```php
public function sendPasswordResetEmail(string $to_email, string $to_name, string $token): bool
```

**Exemple d'utilisation :**
```php
$mailer = new Mailer();
$success = $mailer->sendPasswordResetEmail(
    'user@example.com',
    'Jean Dupont',
    'a1b2c3d4...' // Token de 64 caractères
);
```

---

#### `app/config/create_password_reset_table.sql` ⭐ **NOUVEAU**
**Utilité :** Script SQL pour créer la table des tokens de réinitialisation

**Structure de la table :**
```sql
CREATE TABLE MDP_OUBLIES_TOKEN (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expire_dans DATETIME NOT NULL,
    utilise TINYINT(1) DEFAULT 0,
    cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES Utilisateur(utilisateur_id)
);
```

---

### 🎯 **2. Modèles (Data Access Layer)**

#### `app/modules/models/PasswordReset.php` ⭐ **NOUVEAU**
**Utilité :** Gère les tokens de réinitialisation de mot de passe

**Principe :** Responsabilité unique - Gestion des tokens et réinitialisation

**Méthodes principales :**

| Méthode | Paramètres | Retour | Description |
|---------|-----------|--------|-------------|
| `getUserByEmail()` | `string $email` | `array\|false` | Trouve un utilisateur par email |
| `createToken()` | `int $utilisateur_id` | `string\|false` | Génère un token de 64 caractères (expire dans 3h) |
| `verifyToken()` | `string $token` | `array` | Vérifie validité du token (non expiré, non utilisé) |
| `markTokenAsUsed()` | `string $token` | `bool` | Marque le token comme utilisé |
| `deleteToken()` | `string $token` | `bool` | Supprime un token |
| `updatePassword()` | `int $utilisateur_id, string $new_password` | `bool` | Met à jour le mot de passe (avec hash SHA-1) |
| `cleanExpiredTokens()` | - | `bool` | Supprime les tokens expirés/utilisés |

**Sécurité :**
- ✅ Token aléatoire cryptographiquement sécurisé (`random_bytes`)
- ✅ Expiration automatique après 3 heures
- ✅ Token marqué comme utilisé après réinitialisation
- ✅ Suppression automatique des tokens expirés

**Exemple d'usage :**
```php
$passwordReset = new PasswordReset();

// Créer un token
$token = $passwordReset->createToken($userId);

// Vérifier un token
$result = $passwordReset->verifyToken($token);
if ($result['valid']) {
    // Token valide
    $userId = $result['utilisateur_id'];
}

// Changer le mot de passe
$passwordReset->updatePassword($userId, 'nouveauMotDePasse');
$passwordReset->markTokenAsUsed($token);
```

---

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

#### `app/modules/controllers/ForgotPasswordController.php` ⭐ **NOUVEAU**
**Utilité :** Gère la demande de réinitialisation de mot de passe (étape 1/3)

**Workflow :**
1. Affiche le formulaire de saisie d'email
2. Valide l'email saisi
3. Vérifie que l'email existe en base
4. Génère un token de réinitialisation
5. Envoie le token par email via PHPMailer
6. Redirige vers la page de vérification du code

**Gestion d'erreurs :**
- Email vide → Message d'erreur
- Email inexistant → Message d'erreur
- Erreur envoi email → Message d'erreur

---

#### `app/modules/controllers/VerifyTokenController.php` ⭐ **NOUVEAU**
**Utilité :** Gère la vérification du code reçu par email (étape 2/3)

**Workflow :**
1. Vérifie que l'utilisateur vient bien de forgot_password (session)
2. Affiche le formulaire de saisie du code
3. Valide le token saisi
4. Vérifie que le token existe, n'est pas expiré et n'est pas utilisé
5. Stocke le token en session
6. Redirige vers la page de réinitialisation

**Vérifications :**
- ✅ Token existe en base
- ✅ Token non expiré (< 3h)
- ✅ Token non utilisé
- ✅ Session valide

---

#### `app/modules/controllers/ResetPasswordController.php` ⭐ **NOUVEAU**
**Utilité :** Gère la définition du nouveau mot de passe (étape 3/3)

**Workflow :**
1. Vérifie que l'utilisateur a bien validé le token (session)
2. Affiche le formulaire de nouveau mot de passe
3. Valide les deux saisies de mot de passe
4. Met à jour le mot de passe en base (hachage SHA-1)
5. Marque le token comme utilisé
6. Nettoie les sessions
7. Redirige vers la page de connexion avec message de succès

**Validations :**
- ✅ Mot de passe minimum 6 caractères
- ✅ Les deux saisies correspondent
- ✅ Token valide en session

---

#### `app/modules/controllers/LogoutController.php` ⭐ **NOUVEAU**
**Utilité :** Gère la déconnexion utilisateur

**Workflow :**
1. Vide toutes les variables de session
2. Supprime le cookie de session
3. Détruit la session
4. Crée une nouvelle session
5. Définit un message de succès
6. Redirige vers la page d'accueil

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

#### `app/modules/views/forgotPasswordView.php` ⭐ **NOUVEAU**
**Utilité :** Formulaire de demande de réinitialisation (étape 1/3)

**Champs :**
- Email (type: email, required)

**Messages affichés :**
- Erreur (email vide, email inexistant, erreur envoi)
- Succès (code envoyé par email)

**Envoie vers :** `index.php?page=forgot_password` (POST)

---

#### `app/modules/views/verifyTokenView.php` ⭐ **NOUVEAU**
**Utilité :** Formulaire de vérification du code (étape 2/3)

**Champs :**
- Token (type: text, required, maxlength: 64)

**Messages affichés :**
- Erreur (code invalide, expiré, déjà utilisé)

**Envoie vers :** `index.php?page=verify_token` (POST)

---

#### `app/modules/views/resetPasswordView.php` ⭐ **NOUVEAU**
**Utilité :** Formulaire de nouveau mot de passe (étape 3/3)

**Champs :**
- Nouveau mot de passe (type: password, required, minlength: 6)
- Confirmation mot de passe (type: password, required, minlength: 6)

**Validations :**
- Minimum 6 caractères
- Les deux saisies doivent correspondre

**Envoie vers :** `index.php?page=reset_password` (POST)

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

**Table de routage complète :**
```php
switch ($page) {
    case 'home':
        HomePageController
    case 'login':
        LoginController
    case 'register':
        RegisterController
    case 'logout':
        LogoutController
    case 'forgot_password':
        ForgotPasswordController
    case 'verify_token':
        VerifyTokenController
    case 'reset_password':
        ResetPasswordController
    case 'legalTerms':
        LegalTermsPageController
    default:
        Redirection vers home
}
```

**Usage dans les liens :**
```html
<a href="index.php?page=home">Accueil</a>
<a href="index.php?page=login">Connexion</a>
<a href="index.php?page=register">Inscription</a>
<a href="index.php?page=logout">Déconnexion</a>
<a href="index.php?page=forgot_password">Mot de passe oublié</a>
<a href="index.php?page=legalTerms">Mentions légales</a>
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
require_once 'rooter.php';
```

Toutes les requêtes passent par ce fichier.

---

### 🎨 **7. Styles CSS**

#### `app/assets/css/style.css`
**Utilité :** Feuille de style principale (TOUT le CSS centralisé)

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
    background-color: #f8f9fa;
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

.alert-success { background: #d4edda; color: #155724; }
.alert-info { background: #d1ecf1; color: #0c5460; }
.alert-danger { background: #ffe6e6; color: #d0314c; }
.alert-warning { background: #fff3cd; color: #856404; }
```

**Pages d'authentification (mot de passe oublié, réinitialisation)** ⭐ **NOUVEAU**
```css
.forgot-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 100vh;
    background-color: #f8f9fa;
    padding-top: 80px;
}

.forgot-container form {
    background-color: #fff;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    max-width: 400px;
}

.forgot-container button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
```

**Usage dans les vues :**
```html
<div class="alert alert-success">Inscription réussie !</div>
<div class="alert alert-danger">Erreur de connexion</div>
<div class="forgot-container"><!-- Formulaire reset password --></div>
```

---

### 🕒 **8. Tâches automatisées (Cron)**

#### `app/cron/clean_expired_tokens.php` ⭐ **NOUVEAU**
**Utilité :** Script de nettoyage des tokens expirés (à exécuter régulièrement)

**Fonctionnement :**
- Supprime les tokens expirés (> 3 heures)
- Supprime les tokens déjà utilisés
- Affiche un message de confirmation

**Configuration cron (AlwaysData ou serveur Linux) :**
```bash
# Exécuter tous les jours à 2h du matin
0 2 * * * php /path/to/app/cron/clean_expired_tokens.php
```

**Exécution manuelle :**
```bash
cd app/cron
php clean_expired_tokens.php
```

**Sortie attendue :**
```
2025-10-05 14:30:00 - Tokens expires nettoyes avec succes
```

---

### 📚 **9. Includes / Helpers**

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

---

### `app/assets/css/navbar.css` ❌
**Raison de suppression :** Fusionné dans `style.css`

---

### `app/assets/css/forgotPassword.css` ❌
**Raison de suppression :** Fusionné dans `style.css`

**Avantages de la fusion :**
- ✅ Tous les styles centralisés dans un seul fichier
- ✅ Meilleure performance (une seule requête HTTP)
- ✅ Plus facile à maintenir
- ✅ Évite la duplication de code

---

## 🏗️ ARCHITECTURE FINALE

```
┌───────────────────────────────────────────────────────────────────┐
│                      PRÉSENTATION (Views)                          │
│  ┌─────────────┐ ┌──────────────┐ ┌──────────────────┐           │
│  │ loginView   │ │ registerView │ │ forgotPasswordView│           │
│  └─────────────┘ └──────────────┘ └──────────────────┘           │
│  ┌─────────────┐ ┌──────────────┐ ┌──────────────────┐           │
│  │ verifyToken │ │ resetPassword│ │ homePageView     │           │
│  └─────────────┘ └──────────────┘ └──────────────────┘           │
└────────────────────┬──────────────────────────────────────────────┘
                     │
                     ▼
┌───────────────────────────────────────────────────────────────────┐
│                   CONTRÔLEURS (Controllers)                        │
│  ┌─────────────┐ ┌──────────────┐ ┌──────────────────┐           │
│  │   Login     │ │   Register   │ │ ForgotPassword   │           │
│  └─────────────┘ └──────────────┘ └──────────────────┘           │
│  ┌─────────────┐ ┌──────────────┐ ┌──────────────────┐           │
│  │VerifyToken  │ │ ResetPassword│ │     Logout       │           │
│  └─────────────┘ └──────────────┘ └──────────────────┘           │
└────────────────────┬──────────────────────────────────────────────┘
                     │
                     ▼
┌───────────────────────────────────────────────────────────────────┐
│                LOGIQUE MÉTIER (Services)                           │
│  ┌──────────────────────┐        ┌──────────────────────┐        │
│  │   AuthController     │        │      Mailer          │        │
│  │  • Valide données    │        │  • Envoi emails      │        │
│  │  • Gère sessions     │        │  • SMTP config       │        │
│  └──────────┬───────────┘        └──────────────────────┘        │
└─────────────┼──────────────────────────────────────────────────────┘
              │
              ▼
┌───────────────────────────────────────────────────────────────────┐
│                    MODÈLE (Data Access)                            │
│  ┌──────────────────────┐        ┌──────────────────────┐        │
│  │    UserManager       │        │   PasswordReset      │        │
│  │  • Requêtes SQL      │        │  • Gestion tokens    │        │
│  │  • Hash/Verify pwd   │        │  • Reset password    │        │
│  │  • CRUD operations   │        │  • Clean expired     │        │
│  └──────────┬───────────┘        └──────────┬───────────┘        │
└─────────────┼───────────────────────────────┼────────────────────┘
              │                               │
              └───────────┬───────────────────┘
                          ▼
                ┌─────────────────────┐
                │      Database       │
                │   (Singleton PDO)   │
                └─────────────────────┘
                          │
                          ▼
                ┌─────────────────────┐
                │  MySQL Database     │
                │  • Utilisateur      │
                │  • MDP_OUBLIES_TOKEN│
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
   
2. LogoutController->index()
   
3. Destruction de la session
   ├─> $_SESSION = array()
   ├─> Suppression du cookie
   └─> session_destroy()
   
4. Redirection vers home
   └─> Message "Vous avez été déconnecté avec succès"
```

---

### **RÉINITIALISATION MOT DE PASSE** ⭐ **NOUVEAU**

#### **ÉTAPE 1/3 : Demande de réinitialisation**

```
1. Utilisateur va sur "Mot de passe oublié"
   └─> index.php?page=forgot_password
   
2. Saisie de l'email
   └─> forgotPasswordView.php
   
3. Soumission du formulaire (POST)
   └─> ForgotPasswordController->sendResetEmail()
   
4. Vérifications
   ├─> Email non vide
   └─> Email existe en base (PasswordReset->getUserByEmail())
   
5. Génération du token
   ├─> random_bytes(32) → 64 caractères hexadécimaux
   ├─> Expiration: 3 heures
   └─> INSERT INTO MDP_OUBLIES_TOKEN
   
6. Envoi de l'email
   ├─> Mailer->sendPasswordResetEmail()
   ├─> SMTP AlwaysData
   └─> Email texte brut avec le token
   
7. Stockage en session
   └─> $_SESSION['reset_email'] = $email
   
8. Redirection
   └─> index.php?page=verify_token
```

#### **ÉTAPE 2/3 : Vérification du code**

```
1. Utilisateur reçoit l'email avec le token
   
2. Saisie du code
   └─> verifyTokenView.php
   
3. Soumission du formulaire (POST)
   └─> VerifyTokenController->verifyToken()
   
4. Vérifications du token
   ├─> Token non vide
   ├─> Token existe en base
   ├─> Token non expiré (< 3h)
   └─> Token non utilisé (utilise = 0)
   
5. Si valide
   ├─> $_SESSION['reset_token'] = $token
   └─> $_SESSION['reset_user_id'] = $userId
   
6. Redirection
   └─> index.php?page=reset_password
```

#### **ÉTAPE 3/3 : Nouveau mot de passe**

```
1. Saisie du nouveau mot de passe (2 fois)
   └─> resetPasswordView.php
   
2. Soumission du formulaire (POST)
   └─> ResetPasswordController->resetPassword()
   
3. Validations
   ├─> Mot de passe non vide
   ├─> Minimum 6 caractères
   └─> Les deux saisies correspondent
   
4. Mise à jour en base
   ├─> Hash SHA-1 du nouveau mot de passe
   └─> UPDATE Utilisateur SET mdp = ...
   
5. Marquer le token comme utilisé
   └─> UPDATE MDP_OUBLIES_TOKEN SET utilise = 1
   
6. Nettoyage des sessions
   ├─> unset($_SESSION['reset_token'])
   ├─> unset($_SESSION['reset_user_id'])
   └─> unset($_SESSION['reset_email'])
   
7. Message de succès
   └─> "Votre mot de passe a été réinitialisé avec succès"
   
8. Redirection
   └─> index.php?page=login
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

#### Champs Utilisateur

| Champ | Type | Description | Contraintes |
|-------|------|-------------|-------------|
| `utilisateur_id` | INT | Identifiant unique | PRIMARY KEY, AUTO_INCREMENT |
| `nom` | VARCHAR(20) | Nom de famille | NOT NULL, max 20 caractères |
| `prenom` | VARCHAR(20) | Prénom | NOT NULL, max 20 caractères |
| `classe_annee` | VARCHAR(1) | Année (1, 2 ou 3) | NOT NULL, valeurs: '1', '2', '3' |
| `email` | VARCHAR(100) | Adresse email | UNIQUE, NOT NULL, max 100 caractères |
| `mdp` | VARCHAR(40) | Mot de passe hashé | NOT NULL, exactement 40 caractères (SHA-1) |

---

### Table `MDP_OUBLIES_TOKEN` ⭐ **NOUVEAU**

```sql
CREATE TABLE MDP_OUBLIES_TOKEN (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expire_dans DATETIME NOT NULL,
    utilise TINYINT(1) DEFAULT 0,
    cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES Utilisateur(utilisateur_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### Champs MDP_OUBLIES_TOKEN

| Champ | Type | Description | Contraintes |
|-------|------|-------------|-------------|
| `id` | INT | Identifiant unique du token | PRIMARY KEY, AUTO_INCREMENT |
| `utilisateur_id` | INT | Référence vers l'utilisateur | NOT NULL, FOREIGN KEY |
| `token` | VARCHAR(64) | Code de vérification | NOT NULL, UNIQUE, 64 caractères hexadécimaux |
| `expire_dans` | DATETIME | Date et heure d'expiration | NOT NULL, expire après 3 heures |
| `utilise` | TINYINT(1) | Token utilisé ? | DEFAULT 0 (0=non, 1=oui) |
| `cree_le` | TIMESTAMP | Date de création | DEFAULT CURRENT_TIMESTAMP |

#### Fonctionnement des tokens

1. **Création** : Token généré avec `random_bytes(32)` converti en hex (64 caractères)
2. **Expiration** : Automatique après 3 heures (défini dans `expire_dans`)
3. **Utilisation** : Marqué comme utilisé après réinitialisation réussie
4. **Nettoyage** : Script cron supprime les tokens expirés/utilisés

---

### Contraintes importantes

⚠️ **`mdp VARCHAR(40)`** - Contrainte imposée par le professeur  
Raison : SHA-1 génère exactement 40 caractères hexadécimaux  
Note : En production, `VARCHAR(255)` avec bcrypt serait recommandé

⚠️ **`token VARCHAR(64)`** - Token sécurisé de réinitialisation  
Raison : `bin2hex(random_bytes(32))` génère 64 caractères hexadécimaux  
Sécurité : Cryptographiquement sécurisé, impossible à deviner

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
require_once 'rooter.php';
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

