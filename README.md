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

## Licence 
Projet académique - usage pédagogique uniquement
