<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Abstract Category Model
 * 
 * Base class for all category types demonstrating polymorphism.
 * Provides common functionality and enforces type-specific implementation
 * through abstract methods. Supports AllCategory and ProductCategory types.
 * 
 * @package App\Models
 */
abstract class Category
{
    protected ?int $id = null;
    protected string $name;
    protected ?string $createdAt = null;

    /**
     * Category constructor
     * 
     * @param string $name Category name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get category ID
     * 
     * @return int|null Category identifier or null if not persisted
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get category name
     * 
     * @return string Category name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get creation timestamp
     * 
     * @return string|null Creation timestamp or null if not set
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * Set category ID
     * 
     * @param int $id Category ID
     * @return self For method chaining
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set creation timestamp
     * 
     * @param string $createdAt Creation timestamp
     * @return self For method chaining
     */
    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get category type - must be implemented by subclasses
     * 
     * @return string Category type identifier
     */
    abstract public function getType(): string;

    /**
     * Validate category data - type-specific validation logic
     * 
     * @return bool True if valid, false otherwise
     */
    abstract public function validate(): bool;

    /**
     * Get display name for category - type-specific formatting
     * 
     * @return string Formatted display name
     */
    abstract public function getDisplayName(): string;

    /**
     * Check if category can contain products - polymorphic behavior
     * 
     * @return bool True if category can contain products
     */
    abstract public function canContainProducts(): bool;

    /**
     * Save category to database with insert/update logic
     * 
     * @return bool True on success, false on failure
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

    /**
     * Insert new category into database
     * 
     * @return bool True on success, false on failure
     */
    protected function insert(): bool
    {
        $sql = "INSERT INTO categories (name) VALUES (?)";
        $result = Database::execute($sql, [$this->name]);
        
        if ($result) {
            // Get the last inserted ID using the proper method
            $this->id = (int) Database::getLastInsertId();
        }
        
        return $result;
    }

    /**
     * Update existing category in database
     * 
     * @return bool True on success, false on failure
     */
    protected function update(): bool
    {
        $sql = "UPDATE categories SET name = ? WHERE id = ?";
        return Database::execute($sql, [$this->name, $this->id]);
    }

    /**
     * Factory method to create appropriate category type based on name
     * Uses polymorphism to instantiate correct subclass
     * 
     * @param string $name Category name
     * @return self Appropriate Category subclass instance
     */
    public static function create(string $name): self
    {
        return match ($name) {
            'all' => new AllCategory($name),
            'clothes' => new ProductCategory($name),
            'tech' => new ProductCategory($name),
            default => new ProductCategory($name),
        };
    }

    /**
     * Convert category to array representation
     * 
     * @return array Category data as associative array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->getType(),
            'displayName' => $this->getDisplayName(),
            'canContainProducts' => $this->canContainProducts(),
            'createdAt' => $this->createdAt
        ];
    }
}

/**
 * All Category Class
 * 
 * Special category that represents all products across all categories.
 * Provides a virtual view of the entire product catalog.
 * 
 * @package App\Models
 */
class AllCategory extends Category
{
    /**
     * Get category type identifier
     * 
     * @return string Always returns 'all'
     */
    public function getType(): string
    {
        return 'all';
    }

    /**
     * Validate all category data
     * 
     * @return bool True only if name is exactly 'all'
     */
    public function validate(): bool
    {
        return $this->name === 'all';
    }

    /**
     * Get display name for all category
     * 
     * @return string Human-readable name
     */
    public function getDisplayName(): string
    {
        return 'All Products';
    }

    /**
     * Check if all category can contain products
     * 
     * @return bool Always true (virtual category showing all products)
     */
    public function canContainProducts(): bool
    {
        return true;
    }
}

/**
 * Product Category Class
 * 
 * Regular product categories that contain specific types of products.
 * Represents actual product groupings like 'clothes', 'tech', etc.
 * 
 * @package App\Models
 */
class ProductCategory extends Category
{
    /**
     * Get category type identifier
     * 
     * @return string Always returns 'product'
     */
    public function getType(): string
    {
        return 'product';
    }

    /**
     * Validate product category data
     * 
     * @return bool True if name is not empty and not 'all'
     */
    public function validate(): bool
    {
        return !empty($this->name) && $this->name !== 'all';
    }

    /**
     * Get display name for product category
     * 
     * @return string Capitalized category name
     */
    public function getDisplayName(): string
    {
        return ucfirst($this->name);
    }

    /**
     * Check if product category can contain products
     * 
     * @return bool Always true for product categories
     */
    public function canContainProducts(): bool
    {
        return true;
    }
} 