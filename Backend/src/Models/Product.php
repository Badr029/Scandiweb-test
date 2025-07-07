<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Abstract Product Model
 * 
 * Base class for all product types demonstrating polymorphism.
 * Provides common functionality and enforces type-specific implementation
 * through abstract methods. Subclasses handle different product behaviors.
 * 
 * @package App\Models
 */
abstract class Product
{
    protected string $id;
    protected string $name;
    protected string $brand;
    protected ?string $description = null;
    protected string $category;
    protected bool $inStock;
    protected ?string $createdAt = null;
    protected array $gallery = [];
    protected array $prices = [];
    protected array $attributes = [];

    /**
     * Product constructor
     * 
     * @param string $id Unique product identifier
     * @param string $name Product name
     * @param string $brand Product brand
     * @param string $category Product category
     * @param bool $inStock Whether product is in stock
     */
    public function __construct(
        string $id,
        string $name,
        string $brand,
        string $category,
        bool $inStock = true
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->brand = $brand;
        $this->category = $category;
        $this->inStock = $inStock;
    }

    /**
     * Get product ID
     * 
     * @return string Product identifier
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get product name
     * 
     * @return string Product name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get product brand
     * 
     * @return string Product brand
     */
    public function getBrand(): string
    {
        return $this->brand;
    }

    /**
     * Get product description
     * 
     * @return string|null Product description or null if not set
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get product category
     * 
     * @return string Product category
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * Check if product is in stock
     * 
     * @return bool True if in stock, false otherwise
     */
    public function isInStock(): bool
    {
        return $this->inStock;
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
     * Get product gallery URLs
     * 
     * @return array Array of image URLs
     */
    public function getGallery(): array
    {
        return $this->gallery;
    }

    /**
     * Get product prices with currency information
     * 
     * @return array Array of price data with currency details
     */
    public function getPrices(): array
    {
        return $this->prices;
    }

    /**
     * Get product attributes
     * 
     * @return array Array of product attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set product description
     * 
     * @param string|null $description Product description
     * @return self For method chaining
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set product stock status
     * 
     * @param bool $inStock Stock status
     * @return self For method chaining
     */
    public function setInStock(bool $inStock): self
    {
        $this->inStock = $inStock;
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
     * Set product gallery URLs
     * 
     * @param array $gallery Array of image URLs
     * @return self For method chaining
     */
    public function setGallery(array $gallery): self
    {
        $this->gallery = $gallery;
        return $this;
    }

    /**
     * Set product prices
     * 
     * @param array $prices Array of price data
     * @return self For method chaining
     */
    public function setPrices(array $prices): self
    {
        $this->prices = $prices;
        return $this;
    }

    /**
     * Set product attributes
     * 
     * @param array $attributes Array of product attributes
     * @return self For method chaining
     */
    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * Get product type - must be implemented by subclasses
     * 
     * @return string Product type identifier
     */
    abstract public function getType(): string;

    /**
     * Validate product data - type-specific validation logic
     * 
     * @return bool True if valid, false otherwise
     */
    abstract public function validate(): bool;

    /**
     * Process product data for display - type-specific formatting
     * 
     * @return array Processed product data ready for display
     */
    abstract public function processForDisplay(): array;

    /**
     * Check if product has configurable options - polymorphic behavior
     * 
     * @return bool True if product has configurable options
     */
    abstract public function hasConfigurableOptions(): bool;

    /**
     * Get available configuration options - polymorphic behavior
     * 
     * @return array Array of available options
     */
    abstract public function getAvailableOptions(): array;

    /**
     * Save product to database with upsert logic
     * 
     * @return bool True on success, false on failure
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $sql = "INSERT INTO products (id, name, brand, description, category, in_stock) 
                VALUES (?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                name = VALUES(name), 
                brand = VALUES(brand), 
                description = VALUES(description), 
                category = VALUES(category), 
                in_stock = VALUES(in_stock)";
        
        return Database::execute($sql, [
            $this->id,
            $this->name,
            $this->brand,
            $this->description,
            $this->category,
            $this->inStock ? 1 : 0
        ]);
    }

    /**
     * Factory method to create appropriate product type based on attributes
     * Uses polymorphism to instantiate correct subclass
     * 
     * @param array $productData Product data including attributes
     * @return self Appropriate Product subclass instance
     */
    public static function create(array $productData): self
    {
        $hasAttributes = !empty($productData['attributes']);
        
        if ($hasAttributes) {
            return new ConfigurableProduct(
                $productData['id'],
                $productData['name'],
                $productData['brand'],
                $productData['category'],
                $productData['inStock']
            );
        } else {
            return new SimpleProduct(
                $productData['id'],
                $productData['name'],
                $productData['brand'],
                $productData['category'],
                $productData['inStock']
            );
        }
    }

    /**
     * Convert product to array representation
     * 
     * @return array Product data as associative array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'description' => $this->description,
            'category' => $this->category,
            'inStock' => $this->inStock,
            'type' => $this->getType(),
            'hasConfigurableOptions' => $this->hasConfigurableOptions(),
            'gallery' => $this->gallery,
            'prices' => $this->prices,
            'attributes' => $this->attributes,
            'availableOptions' => $this->getAvailableOptions(),
            'createdAt' => $this->createdAt
        ];
    }
}

/**
 * Simple Product Class
 * 
 * Represents products without configurable options (no size, color variants, etc.).
 * Customers can purchase these products as-is without making configuration choices.
 * 
 * @package App\Models
 */
class SimpleProduct extends Product
{
    /**
     * Get product type identifier
     * 
     * @return string Always returns 'simple'
     */
    public function getType(): string
    {
        return 'simple';
    }

    /**
     * Validate simple product data
     * 
     * @return bool True if valid (simple products just need basic fields)
     */
    public function validate(): bool
    {
        return !empty($this->name) && !empty($this->brand) && !empty($this->category);
    }

    /**
     * Process simple product for display
     * 
     * @return array Processed product data with simple product specifics
     */
    public function processForDisplay(): array
    {
        $data = $this->toArray();
        $data['displayType'] = 'Simple Product';
        $data['canPurchaseDirectly'] = true;
        return $data;
    }

    /**
     * Check if product has configurable options
     * 
     * @return bool Always false for simple products
     */
    public function hasConfigurableOptions(): bool
    {
        return false;
    }

    /**
     * Get available options for simple product
     * 
     * @return array Always empty for simple products
     */
    public function getAvailableOptions(): array
    {
        return [];
    }
}

/**
 * Configurable Product Class
 * 
 * Represents products with selectable attributes (size, color, capacity, etc.).
 * Customers must make configuration choices before purchasing these products.
 * 
 * @package App\Models
 */
class ConfigurableProduct extends Product
{
    /**
     * Get product type identifier
     * 
     * @return string Always returns 'configurable'
     */
    public function getType(): string
    {
        return 'configurable';
    }

    /**
     * Validate configurable product data
     * 
     * @return bool True if valid (configurable products can be saved without attributes initially)
     */
    public function validate(): bool
    {
        return !empty($this->name) && !empty($this->brand) && !empty($this->category);
    }

    /**
     * Process configurable product for display
     * 
     * @return array Processed product data with configurable product specifics
     */
    public function processForDisplay(): array
    {
        $data = $this->toArray();
        $data['displayType'] = 'Configurable Product';
        $data['requiresConfiguration'] = true;
        $data['configurableAttributes'] = $this->getConfigurableAttributes();
        $data['defaultConfiguration'] = $this->getDefaultConfiguration();
        return $data;
    }

    /**
     * Check if product has configurable options
     * 
     * @return bool Always true for configurable products
     */
    public function hasConfigurableOptions(): bool
    {
        return true;
    }

    /**
     * Get available configuration options
     * 
     * @return array Array of available attribute values
     */
    public function getAvailableOptions(): array
    {
        if (empty($this->attributes)) {
            return [];
        }

        $options = [];
        foreach ($this->attributes as $attribute) {
            if (isset($attribute['items']) && is_array($attribute['items'])) {
                $values = array_map(function($item) {
                    return $item['displayValue'] ?? $item['value'] ?? '';
                }, $attribute['items']);
                $options[$attribute['name']] = array_filter($values);
            }
        }
        
        return $options;
    }

    /**
     * Get configurable attributes formatted for selection
     * 
     * @return array Formatted attribute data for configuration UI
     */
    private function getConfigurableAttributes(): array
    {
        if (empty($this->attributes)) {
            return [];
        }

        return array_map(function($attribute) {
            return [
                'id' => $attribute['id'],
                'name' => $attribute['name'],
                'type' => $attribute['type'],
                'required' => true,
                'options' => $attribute['items'] ?? []
            ];
        }, $this->attributes);
    }

    /**
     * Get default configuration for the product
     * 
     * @return array Default selection for each configurable attribute
     */
    private function getDefaultConfiguration(): array
    {
        if (empty($this->attributes)) {
            return [];
        }

        $defaults = [];
        foreach ($this->attributes as $attribute) {
            if (!empty($attribute['items'])) {
                $firstItem = reset($attribute['items']);
                $defaults[$attribute['name']] = $firstItem['value'] ?? '';
            }
        }

        return $defaults;
    }
} 