# Respect du pattern MVC – BdeLive

## Rôle de chaque couche

| Couche   | Rôle | Dans BdeLive |
|----------|------|----------------|
| **Model**  | Données + logique métier + persistance | **Entities** (Article, Event), **Repositories** (accès BDD), **Factories** (PDO → Entity). Le contrôleur ne fait pas de SQL, il utilise les interfaces de repository. |
| **View**   | Affichage uniquement, pas de logique métier | Fichiers dans `app/Modules/views/` (ex. `showEventView.php`, `listArticlesView.php`). Ils reçoivent des variables passées par `render()` et affichent du HTML (avec `htmlspecialchars()`). |
| **Controller** | Orchestration : requête → Model → View | Controllers dans `app/Modules/Controllers/`. Ils reçoivent les repositories par **injection de dépendances** (Container), appellent le Model (ex. `$repository->findBySlug()`), puis `$this->render('vue', $data)`. |

## Flux de requête

```
Requête HTTP
    → index.php (bootstrap)
    → rooter.php (résolution page → Controller)
    → ContainerFactory::create() puis container->get(Controller)
    → Controller (ex. ShowEventController) reçoit EventRepositoryInterface
    → Controller appelle $this->eventRepository->findBySlug()  [Model]
    → Controller appelle $this->render('events/showEventView', ['event' => $event])  [View]
    → La vue affiche les données (pas d’accès direct à la BDD ni aux repositories)
```

## Points conformes au MVC

- **Séparation Model / View / Controller** : les vues ne contiennent pas de requêtes SQL ni d’instanciation de repositories ; les contrôleurs ne génèrent pas de HTML, ils passent les données aux vues.
- **Model = données + persistance** : Entities (logique métier) + Repositories (persistance) + Factories (hydratation). Les contrôleurs dépendent des **interfaces** (EventRepositoryInterface, ArticleRepositoryInterface), pas des classes concrètes.
- **Controller = orchestration** : un contrôleur utilise le Model (repositories) et choisit la View et les données à lui passer.
- **View = présentation** : les vues utilisent les variables fournies (`$event`, `$article`, etc.) et les getters des entités pour l’affichage.

## Bonnes pratiques supplémentaires

- **Injection de dépendances** : les contrôleurs reçoivent leurs dépendances (repositories) par le constructeur via le Container ; pas de `new EventRepository()` dans les contrôleurs.
- **Interfaces** : les contrôleurs type-hint des interfaces (Dependency Inversion), pas les implémentations.
- **Entités plutôt que tableaux** : le Model expose des objets (Article, Event) avec getters/setters, pas des tableaux associatifs bruts.

## Conclusion

**Oui, l’architecture respecte bien le pattern MVC** : Model (Entities + Repositories + Factories), View (fichiers de vues), Controller (orchestration avec DI). Le Data Mapper et le Container renforcent la séparation des responsabilités sans casser le MVC.
