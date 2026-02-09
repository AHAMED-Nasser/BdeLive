<?php

declare(strict_types=1);

namespace App\Modules\Models\Events;

use PDO;
use PDOException;
use DateTime;
use App\Modules\Helpers\SlugGenerator;

/**
 * EventModel - Modèle unifié pour la gestion des événements
 *
 * Ce modèle centralise toutes les opérations sur les événements (lecture et écriture)
 * conformément au pattern Repository enseigné dans le cours (CM4 Slide 22).
 *
 * Responsabilités :
 * - Lecture : Récupération des événements avec pagination, filtres, etc.
 * - Écriture : Création, modification et suppression d'événements
 *
 * @package BdeLive\Models\Events
 * @author BdeLive - Group 8
 * @version 2.0.0
 */
class EventModel
{
    /**
     * Instance de connexion PDO à la base de données
     *
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Constructeur - Injection de dépendance PDO
     *
     * Conformément aux bonnes pratiques (CM4 Slide 22), la connexion PDO
     * est injectée via le constructeur pour faciliter les tests et respecter
     * le principe d'inversion de dépendances.
     *
     * @param PDO $pdo Instance de connexion à la base de données
     * @return void
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // =========================================================================
    // MÉTHODES DE LECTURE (Read)
    // =========================================================================

    /**
     * Compter le nombre total d'événements dans la base de données
     *
     * Compte tous les événements présents dans la table EVENTS.
     *
     * @return int Nombre total d'événements
     */
    public function count(): int
    {
        try {
            $stmt = $this->pdo->query('SELECT COUNT(*) FROM EVENTS');
            if ($stmt === false) {
                return 0;
            }
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('EventModel::count - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupérer un événement par son identifiant
     *
     * @param int $id Identifiant unique de l'événement
     * @return array<string, mixed>|null Données de l'événement ou null si non trouvé
     */
    public function findById(int $id): ?array
    {
        try {
            $sql = "SELECT * FROM EVENTS WHERE event_id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);

            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            return $event ?: null;
        } catch (PDOException $e) {
            error_log('EventModel::findById - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve an event by its SEO-friendly slug
     *
     * Searches for an event using its URL-friendly slug identifier.
     * This method is used for SEO-optimized URLs instead of numeric IDs.
     *
     * @param string $slug URL-friendly slug (e.g., "mon-evenement-special")
     * @return array<string, mixed>|null Event data or null if not found
     */
    public function findBySlug(string $slug): ?array
    {
        try {
            $sql = "SELECT * FROM EVENTS WHERE slug = :slug";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':slug' => $slug]);

            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            return $event ?: null;
        } catch (PDOException $e) {
            error_log('EventModel::findBySlug - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if a slug already exists in the database
     *
     * Used to ensure slug uniqueness when creating new events.
     * If a slug exists, SlugGenerator will append a number (e.g., "event-2").
     *
     * @param string $slug The slug to check
     * @return bool True if exists, false otherwise
     */
    private function slugExists(string $slug): bool
    {
        try {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM EVENTS WHERE slug = :slug');
            $stmt->execute([':slug' => $slug]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('EventModel::slugExists - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a slug exists excluding a specific event ID
     *
     * Used during event updates to allow keeping the same slug
     * while preventing conflicts with other events.
     *
     * @param string $slug The slug to check
     * @param int $excludeId Event ID to exclude from check
     * @return bool True if exists, false otherwise
     */
    private function slugExistsExcludingId(string $slug, int $excludeId): bool
    {
        try {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM EVENTS WHERE slug = :slug AND event_id != :id');
            $stmt->execute([':slug' => $slug, ':id' => $excludeId]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('EventModel::slugExistsExcludingId - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer une liste paginée d'événements
     *
     * Récupère les événements avec support de la pagination.
     * Les résultats sont triés par date et heure d'événement (décroissant).
     *
     * @param int $offset Décalage calculé par le système de pagination
     * @param int $limit Nombre d'éléments par page
     * @return array<int, array<string, mixed>> Tableau d'événements pour la page demandée
     */
    public function findPaginated(int $offset, int $limit): array
    {
        try {
            $sql = 'SELECT event_id, event_name, slug, event_date, event_time, event_location, description, images
                    FROM EVENTS
                    ORDER BY event_date DESC, event_time DESC
                    LIMIT :offset, :limit';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('EventModel::findPaginated - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer tous les événements pour l'affichage calendrier
     *
     * @return array<int, array<string, mixed>> Tableau de tous les événements
     */
    public function findAll(): array
    {
        try {
            $sql = 'SELECT event_id, event_name, slug, event_date, event_time, description FROM EVENTS';
            $stmt = $this->pdo->query($sql);

            if ($stmt === false) {
                return [];
            }

            /** @var array<int, array<string, mixed>> $results */
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $results;
        } catch (PDOException $e) {
            error_log('EventModel::findAll - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer les prochains événements à venir
     *
     * Récupère les événements à venir triés par date (ascendant) pour afficher
     * les prochains événements en premier. Ne retourne que les événements
     * avec une date supérieure ou égale à aujourd'hui.
     *
     * @param int $limit Nombre maximum d'événements à récupérer
     * @return array<int, array<string, mixed>> Tableau des événements à venir
     */
    public function findLatestEvents(int $limit): array
    {
        try {
            $sql = 'SELECT event_id, event_name, event_date, event_time, event_location, description, images
                    FROM EVENTS
                    WHERE event_date >= CURDATE()
                    ORDER BY event_date ASC, event_time ASC
                    LIMIT :limit';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            /** @var array<int, array<string, mixed>> $results */
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $results;
        } catch (PDOException $e) {
            error_log('EventModel::findLatestEvents - ' . $e->getMessage());
            return [];
        }
    }

    // =========================================================================
    // MÉTHODES D'ÉCRITURE (Write)
    // =========================================================================

    /**
     * Insérer un nouvel événement dans la base de données
     *
     * Crée un nouvel enregistrement d'événement avec toutes les informations
     * fournies, incluant les images Cloudinary et les options d'inscription en groupe.
     *
     * @param string $eventName Nom/titre de l'événement
     * @param DateTime $eventDate Date de l'événement
     * @param DateTime $eventTime Heure de l'événement
     * @param string $eventLocation Lieu/emplacement de l'événement
     * @param string $eventTheme Thème/catégorie de l'événement
     * @param string $statusParticipating Statuts des participants autorisés (séparés par virgule)
     * @param string $description Description de l'événement
     * @param string $images Chaîne JSON des URLs d'images Cloudinary
     * @param bool $isGroupEvent Indique si c'est un événement avec inscription en groupe
     * @param int $teamSize Nombre maximum de membres par équipe (uniquement pour événements de groupe)
     * @return bool True si l'insertion réussit, false sinon
     */
    public function insertEvent(
        string $eventName,
        DateTime $eventDate,
        DateTime $eventTime,
        string $eventLocation,
        string $eventTheme,
        string $statusParticipating,
        string $description,
        string $images = '',
        bool $isGroupEvent = false,
        int $teamSize = 1
    ): bool {
        // Generate unique slug from event name for SEO-friendly URLs
        $slug = SlugGenerator::generateUnique($eventName, function ($slug) {
            return $this->slugExists($slug);
        });

        try {
            $query = "INSERT INTO EVENTS (event_name, slug, event_date, event_time, event_location, " .
                "event_theme, status_participating, description, images, is_group_event, team_size) " .
                "VALUES (:event_name, :slug, :event_date, :event_time, :event_location, " .
                ":event_theme, :status_participating, :description, :images, :is_group_event, :team_size)";
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([
                ':event_name' => $eventName,
                ':slug' => $slug,
                ':event_date' => $eventDate->format('Y-m-d'),
                ':event_time' => $eventTime->format('H:i'),
                ':event_location' => $eventLocation,
                ':event_theme' => $eventTheme,
                ':status_participating' => $statusParticipating,
                ':description' => $description,
                ':images' => $images,
                ':is_group_event' => $isGroupEvent ? 1 : 0,
                ':team_size' => $teamSize
            ]);
        } catch (PDOException $e) {
            error_log('EventModel::insertEvent - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mettre à jour un événement existant dans la base de données
     *
     * Met à jour toutes les informations d'un événement existant identifié
     * par son ID. Tous les champs sont mis à jour, y compris les images
     * et les paramètres d'inscription en groupe.
     *
     * @param int $eventId Identifiant de l'événement à modifier
     * @param string $eventName Nouveau nom/titre de l'événement
     * @param DateTime $eventDate Nouvelle date de l'événement
     * @param DateTime $eventTime Nouvelle heure de l'événement
     * @param string $eventLocation Nouveau lieu de l'événement
     * @param string $eventTheme Nouveau thème de l'événement
     * @param string $statusParticipating Nouveaux statuts des participants autorisés
     * @param string $description Nouvelle description de l'événement
     * @param string $images Nouvelle chaîne JSON des URLs d'images
     * @param bool $isGroupEvent Indique si c'est un événement avec inscription en groupe
     * @param int $teamSize Nombre maximum de membres par équipe
     * @return bool True si la mise à jour réussit, false sinon
     */
    public function updateEvent(
        int $eventId,
        string $eventName,
        DateTime $eventDate,
        DateTime $eventTime,
        string $eventLocation,
        string $eventTheme,
        string $statusParticipating,
        string $description,
        string $images,
        bool $isGroupEvent = false,
        int $teamSize = 1
    ): bool {
        // Regenerate slug from event name, ensuring uniqueness (excluding current event)
        $slug = SlugGenerator::generateUnique($eventName, function ($testSlug) use ($eventId) {
            return $this->slugExistsExcludingId($testSlug, $eventId);
        });

        try {
            $sql = "UPDATE EVENTS SET
            event_name = :event_name,
            slug = :slug,
            event_date = :event_date,
            event_time = :event_time,
            event_location = :event_location,
            event_theme = :event_theme,
            status_participating = :status_participating,
            description = :description,
            images = :images,
            is_group_event = :is_group_event,
            team_size = :team_size
            WHERE event_id = :event_id";

            $stmt = $this->pdo->prepare($sql);

            return $stmt->execute([
                ':event_id' => $eventId,
                ':event_name' => $eventName,
                ':slug' => $slug,
                ':event_date' => $eventDate->format('Y-m-d'),
                ':event_time' => $eventTime->format('H:i'),
                ':event_location' => $eventLocation,
                ':event_theme' => $eventTheme,
                ':status_participating' => $statusParticipating,
                ':description' => $description,
                ':images' => $images,
                ':is_group_event' => $isGroupEvent ? 1 : 0,
                ':team_size' => $teamSize
            ]);
        } catch (PDOException $e) {
            error_log('EventModel::updateEvent - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mettre à jour uniquement les images associées à un événement
     *
     * Permet de mettre à jour le champ images d'un événement sans modifier
     * les autres informations. Utile après un upload d'images additionnel.
     *
     * @param int $eventId Identifiant unique de l'événement
     * @param string $imageJson Chaîne JSON des URLs d'images
     * @return bool True si la mise à jour réussit, false sinon
     */
    public function updateEventImages(int $eventId, string $imageJson): bool
    {
        try {
            $sql = 'UPDATE EVENTS SET images = :images WHERE event_id = :id';
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':images' => $imageJson,
                ':id' => $eventId
            ]);
        } catch (PDOException $e) {
            error_log('EventModel::updateEventImages - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprimer un événement de la base de données
     *
     * Supprime définitivement un événement identifié par son ID.
     * Cette opération est irréversible.
     *
     * @param int $eventId Identifiant de l'événement à supprimer
     * @return bool True si la suppression réussit, false sinon
     */
    public function deleteEvent(int $eventId): bool
    {
        try {
            $query = "DELETE FROM EVENTS WHERE event_id = :event_id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('EventModel::deleteEvent - ' . $e->getMessage());
            return false;
        }
    }
}
