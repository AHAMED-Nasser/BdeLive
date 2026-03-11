# Documentation BdeLive

Structure de la documentation technique du projet.

## Organisation

| Dossier | Contenu |
|---------|---------|
| [**api/**](api/) | Documentation API (PHPDoc générée par phpDocumentor) |
| [**architecture/**](architecture/) | Diagramme du flux requête/réponse, patterns MVC 2, Repository, Factory, DI |
| [**usecases/**](usecases/) | Diagrammes de cas d'utilisation par acteur (RBAC) |
| [**classes/**](classes/) | Diagramme de classes UML exhaustif |
| [**database/**](database/) | Schéma relationnel de la base de données |

## Fichiers

### API (PHPDoc)

- [Index API](api/index.html) — Documentation des classes, méthodes et namespaces
- Régénération : `composer run doc`

### Architecture

- [diagram-architecture.puml](architecture/diagram-architecture.puml) · [SVG](architecture/diagram-architecture.svg) — Cycle de vie d'une requête, Core, Controllers, Domain, Views

### Cas d'utilisation (RBAC)

- [uc-visiteur.puml](usecases/uc-visiteur.puml) · [SVG](usecases/uc-visiteur.svg) — Visiteur (emploi du temps, événements, articles)
- [uc-utilisateur.puml](usecases/uc-utilisateur.puml) · [SVG](usecases/uc-utilisateur.svg) — Utilisateur connecté (inscription, équipes, RGPD)
- [uc-admin.puml](usecases/uc-admin.puml) · [SVG](usecases/uc-admin.svg) — Administrateur BDE (événements, articles, modération, export PDF)
- [uc-superadmin.puml](usecases/uc-superadmin.puml) · [SVG](usecases/uc-superAdmin.svg) — Super Admin (promotion, rétrogradation, suppression, restauration)

### Classes

- [diagramme-classes.puml](classes/diagramme-classes.puml) · [SVG](classes/diagramme-classes.svg) — Entités, Repositories, Factories, structure du domaine

### Base de données

- [Schéma relationnel](database/schema-relationnel.png) — Modèle relationnel des tables MySQL
