<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Gallery Model
 * 
 * Handles product image gallery operations
 * 
 * @package App\Models
 */
class Gallery
{
    private ?int $id = null;
    private string $productId;
    private string $imageUrl;
    private int $sortOrder;
    private ?string $createdAt = null;

    public function __construct(
        string $productId,
        string $imageUrl,
        int $sortOrder = 0
    ) {
        $this->productId = $productId;
        $this->imageUrl = $imageUrl;
        $this->sortOrder = $sortOrder;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    // Setters
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setImageUrl(string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Validate gallery item data
     */
    public function validate(): bool
    {
        return !empty($this->productId) && 
               !empty($this->imageUrl) && 
               filter_var($this->imageUrl, FILTER_VALIDATE_URL) !== false &&
               $this->sortOrder >= 0;
    }

    /**
     * Save gallery item to database
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        if ($this->id === null) {
            return $this->insert();
        }
        
        return $this->update();
    }

    protected function insert(): bool
    {
        $sql = "INSERT INTO product_gallery (product_id, image_url, sort_order) 
                VALUES (?, ?, ?)";
        
        return Database::execute($sql, [
            $this->productId,
            $this->imageUrl,
            $this->sortOrder
        ]);
    }

    protected function update(): bool
    {
        $sql = "UPDATE product_gallery SET 
                image_url = ?, 
                sort_order = ? 
                WHERE id = ?";
        
        return Database::execute($sql, [
            $this->imageUrl,
            $this->sortOrder,
            $this->id
        ]);
    }

    /**
     * Check if this is the primary (first) image
     */
    public function isPrimaryImage(): bool
    {
        return $this->sortOrder === 0;
    }

    /**
     * Get image file extension
     */
    public function getImageExtension(): string
    {
        $pathInfo = pathinfo($this->imageUrl);
        return $pathInfo['extension'] ?? '';
    }

    /**
     * Get image filename
     */
    public function getImageFilename(): string
    {
        $pathInfo = pathinfo($this->imageUrl);
        return $pathInfo['basename'] ?? '';
    }

    /**
     * Check if image URL is valid
     */
    public function isValidImageUrl(): bool
    {
        if (!filter_var($this->imageUrl, FILTER_VALIDATE_URL)) {
            return false;
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower($this->getImageExtension());
        
        return in_array($extension, $allowedExtensions);
    }

    /**
     * Generate thumbnail URL (placeholder implementation)
     */
    public function getThumbnailUrl(int $width = 150, int $height = 150): string
    {
        // This would typically integrate with an image service
        // For now, return the original URL
        return $this->imageUrl;
    }

    /**
     * Generate optimized URL for specific dimensions
     */
    public function getOptimizedUrl(int $width, int $height): string
    {
        // This would typically integrate with an image optimization service
        // For now, return the original URL
        return $this->imageUrl;
    }

    /**
     * Get all gallery items for a product, ordered by sort_order
     */
    public static function getByProductId(string $productId): array
    {
        $sql = "SELECT * FROM product_gallery 
                WHERE product_id = ? 
                ORDER BY sort_order ASC";
        
        $rows = Database::fetchAll($sql, [$productId]);
        
        $galleries = [];
        foreach ($rows as $row) {
            $gallery = new self($row['product_id'], $row['image_url'], $row['sort_order']);
            $gallery->setId($row['id']);
            if ($row['created_at']) {
                $gallery->setCreatedAt($row['created_at']);
            }
            $galleries[] = $gallery;
        }
        
        return $galleries;
    }

    /**
     * Delete gallery item
     */
    public function delete(): bool
    {
        if ($this->id === null) {
            return false;
        }

        $sql = "DELETE FROM product_gallery WHERE id = ?";
        return Database::execute($sql, [$this->id]);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'productId' => $this->productId,
            'imageUrl' => $this->imageUrl,
            'sortOrder' => $this->sortOrder,
            'isPrimary' => $this->isPrimaryImage(),
            'extension' => $this->getImageExtension(),
            'filename' => $this->getImageFilename(),
            'created_at' => $this->createdAt
        ];
    }

    public function toGraphQLArray(): array
    {
        return [
            'imageUrl' => $this->imageUrl,
            '__typename' => 'Gallery'
        ];
    }
} 