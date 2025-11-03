<?php

class EventCreationModel {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function insertEvent(string $eventName,
                                $eventDate,
                                $eventTime,
                                string $eventLocation,
                                string $eventTheme,
                                string $statusParticipating,
                                string $description,
                                ?array $images = null
    ) {
        try {
            $imagesJson = !empty($images) ? json_encode($images) : null; // Convert images array to JSON or set to null

            $query = "INSERT INTO EVENTS (event_name, event_date, event_time, event_location, event_theme, status_participating, description, images) VALUES (:event_name, :event_date, :event_time, :event_location, :event_theme, :status_participating, :description, :images)";

            $stmt = $this->pdo->prepare($query);

            return $stmt -> execute([ // return true if query success else false
                ':event_name' => $eventName,
                ':event_date' => $eventDate,
                ':event_time' => $eventTime,
                ':event_location' => $eventLocation,
                ':event_theme' => $eventTheme,
                ':status_participating' => $statusParticipating,
                ':description' => $description,
                ':images' => $imagesJson
            ]);
        } catch (PDOException $e) {
            error_log('EventCreationModel::insertEvent - ' . $e->getMessage());
            header('Location: index.php?page=createEvent');
            exit();
        }

    }

    /**
     * Get events from an event
     * @param int $eventId
     * @return array
     */
    public function getEventImages(int $eventId): array {
        try {
            $query = "SELECT images FROM EVENTS WHERE event_id = :event_id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && $result['images']) { // If result true and result['images'] is set...
                $images = json_decode($result['images'], true); // decode JSON to PHP array

                if (!is_array($images)) { // If images is not an array, return empty array
                    return [];
                }

                // Keep only URLs (src) and public_id
                $formattedImages = [];
                foreach ($images as $img) {
                    if (!empty($img['url'])) {
                        $formattedImages[] = [
                            'src' => $img['url'],       // pour la vue / carousel
                            'public_id' => $img['public_id'] ?? null
                        ];
                    }
                }

                return $formattedImages;
            }
            return [];
        } catch (PDOException $e) {
            error_log('EventCreationModel::getEventImages - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete an event by its ID
     * @param int $eventId The event ID to delete
     * @return bool True if deletion successful, false otherwise
     */
    public function deleteEvent(int $eventId): bool
    {
        try {
            // Get images before delete
            $images = $this->getEventImages($eventId);

            // Delete images from Cloudinary if the delete success
            $query = "DELETE FROM EVENTS WHERE event_id = :event_id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            $success = $stmt->execute();

            if ($success && !empty($images)) {
                $cloudinary = new CloudinaryService();
                $publicIds = array_column($images, 'public_id'); // Get public_ids from images array
                $cloudinary->deleteMultipleImages($publicIds); // Delete each image from Cloudinary
            }

            return $success; // return true if deletion successful, false otherwise

        } catch (PDOException $e) {
            error_log('EventCreationModel::deleteEvent - ' . $e->getMessage());
            return false;
        }
    }

}