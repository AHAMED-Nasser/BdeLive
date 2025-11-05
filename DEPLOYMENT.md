# Guide de Déploiement - BDELive

## Déploiement sur AlwaysData

### 1. Prérequis

- Accès SSH à votre serveur AlwaysData
- Composer installé sur le serveur (ou accessible via SSH)

### 2. Étapes de déploiement

#### Étape 1 : Cloner ou transférer le projet

```bash
# Via Git
git clone https://github.com/AHAMED-Nasser/BdeLive.git
cd BdeLive

# OU transférer les fichiers via FTP/SFTP vers votre répertoire web
```

#### Étape 2 : Installer les dépendances Composer

**IMPORTANT** : Le dossier `vendor/` n'est pas dans Git (dans `.gitignore`). Il faut l'installer sur le serveur :

```bash
# Installer Composer si pas déjà installé
curl -sS https://getcomposer.org/installer | php

# Installer les dépendances (sans les dépendances de dev pour la production)
php composer.phar install --no-dev --optimize-autoloader

# OU si Composer est installé globalement
composer install --no-dev --optimize-autoloader
```

#### Étape 3 : Configurer la base de données

1. Créer le fichier `app/config/config.php` sur le serveur (il est dans `.gitignore` pour la sécurité) :

```php
<?php

declare(strict_types=1);

define('DB_HOST', 'mysql-bdelivesae.alwaysdata.net');
define('DB_NAME', 'bdelivesae_db');
define('DB_USER', 'votre_utilisateur');
define('DB_PASSWORD', 'votre_mot_de_passe');
define('DB_CHARSET', 'utf8mb4');

// Configuration events
define('ADMIN_EMAIL', 'events@events.iut');
define('ADMIN_PWD', 'events');
```

2. Importer le schéma de base de données :

```bash
mysql -h mysql-bdelivesae.alwaysdata.net -u votre_utilisateur -p bdelivesae_db < BDELive_database_1.1.sql
```

#### Étape 4 : Configurer le serveur web

**Point important** : Le document root doit pointer vers le dossier `app/` du projet.

**Configuration Apache (.htaccess déjà présent dans `app/`) :**

Le fichier `.htaccess` dans le dossier `app/` configure déjà le réécriture d'URL. Assurez-vous que :

1. Le document root pointe vers `/home/bdelivesae/www/myProjectGame/app/` (ou votre chemin)
2. Le module `mod_rewrite` est activé
3. Les permissions sont correctes : `chmod 755 app/`

#### Étape 5 : Vérifier les permissions

```bash
# Permissions sur les dossiers
chmod 755 app/
chmod 755 app/modules/
chmod 755 app/modules/views/

# Permissions sur les fichiers de configuration (lecture seule pour les autres)
chmod 644 app/config/config.php
```

### 3. Vérification post-déploiement

#### Vérifier que tout fonctionne :

1. **Composer autoloader** : Vérifier que `vendor/autoload.php` existe
   ```bash
   ls -la vendor/autoload.php
   ```

2. **Configuration** : Vérifier que `app/config/config.php` existe et contient les bonnes informations

3. **Base de données** : Tester la connexion
   ```bash
   php -r "require 'app/config/config.php'; echo 'DB_HOST: ' . DB_HOST . PHP_EOL;"
   ```

4. **Site web** : Accéder à `https://bdelivesae.alwaysdata.net` et vérifier qu'il n'y a pas d'erreurs

### 4. Problèmes courants

#### Erreur : "Failed to open stream: vendor/autoload.php"

**Solution** : Exécuter `composer install --no-dev` sur le serveur

```bash
cd /home/bdelivesae/www/myProjectGame
composer install --no-dev --optimize-autoloader
```

#### Erreur : "Page non trouvée"

**Solution** : Vérifier que :
- Le document root pointe vers le dossier `app/`
- Le fichier `.htaccess` est présent dans `app/`
- Le module `mod_rewrite` est activé

#### Erreur de connexion à la base de données

**Solution** : Vérifier les identifiants dans `app/config/config.php` et que la base de données est accessible depuis AlwaysData

### 5. Mise à jour du site

```bash
# 1. Se connecter en SSH
ssh bdelivesae@ssh.bdelivesae.alwaysdata.net

# 2. Aller dans le répertoire du projet
cd www/myProjectGame

# 3. Récupérer les dernières modifications
git pull origin DEV  # ou main selon votre branche

# 4. Réinstaller les dépendances si nécessaire
composer install --no-dev --optimize-autoloader

# 5. Vérifier que tout fonctionne
```

### 6. Structure des fichiers sur le serveur

```
/home/bdelivesae/www/myProjectGame/
├── app/                    # Document root du serveur web
│   ├── index.php          # Point d'entrée
│   ├── config/
│   │   └── config.php     # À créer manuellement (pas dans Git)
│   ├── modules/
│   └── ...
├── vendor/                # Généré par Composer (pas dans Git)
├── composer.json
├── composer.lock
└── ...
```

## Notes importantes

- **Le dossier `vendor/`** doit être généré avec `composer install` sur le serveur
- **Le fichier `app/config/config.php`** doit être créé manuellement (sécurité)
- **Le document root** doit pointer vers `app/` et non vers la racine du projet
- **Les erreurs PHP** sont affichées en développement mais devraient être masquées en production

## Support

En cas de problème, vérifier les logs d'erreur PHP :
- AlwaysData : `~/logs/error.log` ou via le panneau d'administration

