# BDELive

## Description
BDELive est le site d'internet du Bureau Des Etudiants du BUT Informatique d'Aix-en-Provence, permettant de se renseigner sur les différentes actualités et événements du BDE.

## Fonctionnalité :
Sur BDELive, il est possible de :
- Créer un compte
- Se connecter
- Réinitialiser son mot de passe via email (token)
Dans une version prochaine, il sera aussi possible de créer des évenements, et bien plus...

## Lien du site
bdelivesae.alwaysdata.net

## Language de programmation utilisée
- **PHP**
- **SQL**
- **HTML / CSS**

## Hébergement et base de donnée 
Le site est hébérgé sur AlwaysData, ainsi que la base de données (propulsé par phpMyAdmin)

## Structure du projet : 
Le site suit cette structure : 
```
BDELIVE_REAL/
│
├─ app/
│  ├─ assets/
│  │  ├─ css/
│  │  └─ img/
│  │
│  ├─ config/
│  │  ├─ config.php
│  │  └─ Database.php
│  │
│  ├─ include/
│  │  ├─ AuthController.php
│  │  └─ include.inc.php
│  │
│  ├─ modules/
│  │  ├─ controllers/
│  │  │  ├─ HomePageController.php
│  │  │  ├─ LegalTermsPageController.php
│  │  │  ├─ LoginController.php
│  │  │  └─ RegisterController.php
│  │  │
│  │  ├─ models/
│  │  │  └─ UserManager.php
│  │  │
│  │  └─ views/
│  │     ├─ homePageView.php
│  │     ├─ legalTermsPageView.php
│  │     ├─ loginPageView.php
│  │     └─ registerPageView.php
│
├─ .htaccess
├─ autoload.php
├─ DOCUMENTATION.md
├─ index.php
└─ Router.php
```

Le projet suit une organisation en module MVC (Model - View - Controller)

## Structure de le base de données
La base de données est représentée par le schéma suivant : 

<img width="892" height="340" alt="image" src="https://github.com/user-attachments/assets/4c6c2363-da5c-47d6-99f5-854286a32db4" />

## Auteurs
- AHAMED Nasser
- BOUDHIB Mohammed-Amine
- CANTOR Romain
- CHETIOUI Willem
- HELALI Amin
- PALOT Thomas

## Sécurité

### Configuration des sessions
Le projet implémente une configuration sécurisée des cookies de session PHP conforme aux bonnes pratiques OWASP :

- **httponly**: Les cookies de session ne sont pas accessibles via JavaScript (protection contre XSS)
- **secure**: Les cookies ne sont transmis que sur des connexions HTTPS
- **samesite**: Protection contre les attaques CSRF
- **Durée de vie**: 30 minutes pour limiter l'exposition en cas de compromission

Cette configuration est définie dans `app/index.php` et s'applique à l'ensemble du site.

### Protection Anti-CSRF
Tous les formulaires sont protégés contre les attaques Cross-Site Request Forgery :

- **Génération de jetons**: Chaque formulaire reçoit un jeton unique et aléatoire
- **Validation côté serveur**: Tous les contrôleurs vérifient la validité du jeton avant traitement
- **Expiration**: Les jetons expirent après 1 heure pour limiter les risques
- **Formulaires protégés**: Login, Register, Forgot Password, Reset Password, Verify Token, Create Event

### En-têtes de sécurité HTTP
Le site envoie des en-têtes de sécurité pour renforcer la protection :

- **X-Frame-Options**: Protection anti-clickjacking
- **Strict-Transport-Security**: Force l'utilisation d'HTTPS
- **X-Content-Type-Options**: Prévient le MIME-sniffing
- **X-XSS-Protection**: Protection contre les attaques XSS
- **Referrer-Policy**: Contrôle des informations de référent

### Conformité OWASP
- **A01:2021 - Broken Access Control**: Protection des sessions et CSRF
- **A03:2021 - Injection**: Protection contre le vol de cookies via XSS et headers de sécurité
- **A05:2021 - Security Misconfiguration**: Configuration correcte des cookies et en-têtes HTTP
- **A07:2021 - Identification and Authentication Failures**: Jetons de session sécurisés

## Licence 
Projet académique - usage pédagogique uniquement
