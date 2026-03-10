<?php

declare(strict_types=1);

namespace App\Modules\Entities;

use DateTime;

/**
 * Event Entity - Business object representing an event
 *
 * This entity encapsulates all event data and business logic following
 * Domain-Driven Design principles. Properties are private to ensure
 * data integrity and encapsulation.
 *
 * Business Logic:
 * - isPast(): Check if event has already occurred
 * - isFull(): Check if event has reached maximum capacity (requires registration data)
 * - getFormattedDate(): Get human-readable date
 *
 * @package BdeLive\Entities
 * @author BdeLive - Group 8
 * @version 1.0.0
 */
class Event
{
    /**
     * @param int|null $id Unique identifier (null for new events)
     * @param string $name Event name/title
     * @param string $slug SEO-friendly URL slug
     * @param string $date Event date (Y-m-d format)
     * @param string $time Event time (H:i format)
     * @param string $location Event location/venue
     * @param string $theme Event theme/category
     * @param string $statusParticipating Allowed participant statuses (comma-separated)
     * @param string $description Event description
     * @param string $images JSON string of Cloudinary image URLs
     * @param bool $isGroupEvent Whether this is a group event
     * @param int $teamSize Maximum team size for group events
     */
    public function __construct(
        private ?int $id,
        private string $name,
        private string $slug,
        private string $date,
        private string $time,
        private string $location,
        private string $theme,
        private string $statusParticipating,
        private string $description,
        private string $images,
        private bool $isGroupEvent,
        private int $teamSize
    ) {
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getTime(): string
    {
        return $this->time;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function getStatusParticipating(): string
    {
        return $this->statusParticipating;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getImages(): string
    {
        return $this->images;
    }

    public function isGroupEvent(): bool
    {
        return $this->isGroupEvent;
    }

    public function getTeamSize(): int
    {
        return $this->teamSize;
    }



    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function setDate(string $date): void
    {
        $this->date = $date;
    }

    public function setTime(string $time): void
    {
        $this->time = $time;
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    public function setTheme(string $theme): void
    {
        $this->theme = $theme;
    }

    public function setStatusParticipating(string $statusParticipating): void
    {
        $this->statusParticipating = $statusParticipating;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setImages(string $images): void
    {
        $this->images = $images;
    }

    public function setIsGroupEvent(bool $isGroupEvent): void
    {
        $this->isGroupEvent = $isGroupEvent;
    }

    public function setTeamSize(int $teamSize): void
    {
        $this->teamSize = $teamSize;
    }


    /**
     * Check if the event has already occurred
     *
     * Compares the event date/time with current date/time to determine
     * if the event is in the past.
     *
     * @return bool True if event is past, false otherwise
     */
    public function isPast(): bool
    {
        try {
            $eventDateTime = new DateTime($this->date . ' ' . $this->time);
            $now = new DateTime();
            return $eventDateTime < $now;
        } catch (\Exception $e) {
            error_log('Event::isPast - Invalid date/time: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get formatted date for display (French format: dd/mm/YYYY)
     *
     * @return string Formatted date string
     */
    public function getFormattedDate(): string
    {
        try {
            $dateTime = new DateTime($this->date);
            return $dateTime->format('d/m/Y');
        } catch (\Exception $e) {
            error_log('Event::getFormattedDate - Invalid date: ' . $e->getMessage());
            return $this->date;
        }
    }

    /**
     * Get formatted time for display (HH:MM format)
     *
     * @return string Formatted time string
     */
    public function getFormattedTime(): string
    {
        try {
            $dateTime = new DateTime($this->time);
            return $dateTime->format('H:i');
        } catch (\Exception $e) {
            error_log('Event::getFormattedTime - Invalid time: ' . $e->getMessage());
            return $this->time;
        }
    }

    /**
     * Get images as decoded array
     *
     * Decodes the JSON images string into an array of image URLs.
     *
     * @return array<int, mixed> Array of image data
     */
    public function getImagesArray(): array
    {
        if (empty($this->images)) {
            return [];
        }

        $decoded = json_decode($this->images, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Check if event has images
     *
     * @return bool True if event has at least one image
     */
    public function hasImages(): bool
    {
        return !empty($this->getImagesArray());
    }
}
