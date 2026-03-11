<?php

declare(strict_types=1);

namespace App\Modules\Entities;

use DateTime;

/**
 * Article Entity - Business object representing an article
 *
 * This entity encapsulates all article data and business logic following
 * Domain-Driven Design principles. Properties are private to ensure
 * data integrity and encapsulation.
 *
 * Business Logic:
 * - getShortDescription(): Get truncated description for previews
 * - getFormattedDate(): Get human-readable creation date
 *
 * @package BdeLive\Entities
 * @author BdeLive - Group 8
 * @version 1.0.0
 */
class Article
{
    /**
     * @param int|null $id Unique identifier (null for new articles)
     * @param string $title Article title
     * @param string $slug SEO-friendly URL slug
     * @param string $description Article content/description
     * @param string|null $imageUrl Cloudinary image URL (nullable)
     * @param string $author Author full name (legacy / fallback)
     * @param string $createdAt Creation timestamp
     * @param int|null $adminCreatorId User ID of admin/superadmin who published (nullable for legacy)
     * @param string $publisherName "Prénom Nom" of admin who published (for display "publié par X")
     */
    public function __construct(
        private ?int $id,
        private string $title,
        private string $slug,
        private string $description,
        private ?string $imageUrl,
        private string $author,
        private string $createdAt,
        private ?int $adminCreatorId = null,
        private string $publisherName = ''
    ) {
    }

    /** @return int|null */
    public function getId(): ?int
    {
        return $this->id;
    }

    /** @return string */
    public function getTitle(): string
    {
        return $this->title;
    }

    /** @return string */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /** @return string */
    public function getDescription(): string
    {
        return $this->description;
    }

    /** @return string|null */
    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    /** @return string */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /** @return string */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /** @return int|null */
    public function getAdminCreatorId(): ?int
    {
        return $this->adminCreatorId;
    }

    /**
     * Get display name for "publié par X" (admin who published)
     * Falls back to author if no publisher name (legacy articles)
     */
    public function getPublisherName(): string
    {
        return $this->publisherName !== '' ? $this->publisherName : $this->author;
    }

    /** @param string $title */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /** @param string $slug */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /** @param string $description */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /** @param string|null $imageUrl */
    public function setImageUrl(?string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    /** @param string $author */
    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }


    /**
     * Get truncated description for article previews
     *
     * Returns a shortened version of the description with ellipsis
     * if it exceeds the specified limit. Useful for article listings
     * and previews where full content is not needed.
     *
     * @param int $limit Maximum character length (default: 150)
     * @return string Truncated description with ellipsis if needed
     */
    public function getShortDescription(int $limit = 150): string
    {
        if (mb_strlen($this->description) <= $limit) {
            return $this->description;
        }

        // Truncate and add ellipsis
        $truncated = mb_substr($this->description, 0, $limit);

        // Try to cut at last space to avoid cutting words
        $lastSpace = mb_strrpos($truncated, ' ');
        if ($lastSpace !== false && $lastSpace > ($limit * 0.8)) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        return $truncated . '...';
    }

    /**
     * Get formatted creation date for display (French format: dd/mm/YYYY)
     *
     * @return string Formatted date string
     */
    public function getFormattedDate(): string
    {
        try {
            $dateTime = new DateTime($this->createdAt);
            return $dateTime->format('d/m/Y');
        } catch (\Exception $e) {
            error_log('Article::getFormattedDate - Invalid date: ' . $e->getMessage());
            return $this->createdAt;
        }
    }

    /**
     * Get formatted creation date with time (French format: dd/mm/YYYY à HH:MM)
     *
     * @return string Formatted datetime string
     */
    public function getFormattedDateTime(): string
    {
        try {
            $dateTime = new DateTime($this->createdAt);
            return $dateTime->format('d/m/Y à H:i');
        } catch (\Exception $e) {
            error_log('Article::getFormattedDateTime - Invalid datetime: ' . $e->getMessage());
            return $this->createdAt;
        }
    }

    /**
     * Check if article has an image
     *
     * @return bool True if article has an image URL
     */
    public function hasImage(): bool
    {
        return !empty($this->imageUrl);
    }

    /**
     * Get a safe image URL (returns placeholder if no image)
     *
     * @param string $placeholder Default placeholder URL
     * @return string Image URL or placeholder
     */
    public function getImageUrlOrPlaceholder(string $placeholder = '/assets/img/default-article.jpg'): string
    {
        return $this->imageUrl ?? $placeholder;
    }
}
