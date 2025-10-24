<?php

declare(strict_types=1);

/**
 * Event Repository - Accès aux données des événements
 * @package BdeLive\Repositories
 * @version 1.0.0
 */
class EventRepository
{
    private PDO $pdo;

    /**
     * Constructeur - Initialise la connexion PDO
     */
    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Compte le nombre total d'événements dans la base de données
     * Basé sur le dump SQL (table EVENTS)
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
            error_log('EventRepository::count - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère une liste paginée d'événements
     * Basé sur le dump SQL (table EVENTS)
     *
     * @param int $offset L'OFFSET calculé par Pagination->getOffset()
     * @param int $limit Le LIMIT (items par page)
     * @return array<string, mixed> La liste des événements pour la page
     */
    public function findPaginated(int $offset, int $limit): array
    {
        try {
            // SQL basé sur la structure de la table EVENTS
            $sql = 'SELECT event_id, event_name, event_date, event_time, event_location, description
                    FROM EVENTS
                    ORDER BY event_date DESC, event_time DESC
                    LIMIT :offset, :limit';
                    
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('EventRepository::findPaginated - ' . $e->getMessage());
            return [];
        }
    }
}
