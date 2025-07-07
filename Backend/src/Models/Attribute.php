<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Abstract Attribute Model
 * 
 * Base class for all attribute types demonstrating polymorphism.
 * Provides common functionality and enforces type-specific implementation
 * through abstract methods. Supports TextAttribute and SwatchAttribute types.
 * 
 * @package App\Models
 */
abstract class Attribute
{
    protected string $id;
    protected string $name;
    protected string $type;
    protected ?string $createdAt = null;
    protected array $items = [];

    /**
     * Attribute constructor
     * 
     * @param string $id Unique attribute identifier
     * @param string $name Attribute name
     * @param string $type Attribute type (text, swatch, etc.)
     */
    public function __construct(string $id, string $name, string $type)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * Get attribute ID
     * 
     * @return string Attribute identifier
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get attribute name
     * 
     * @return string Attribute name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get attribute type
     * 
     * @return string Attribute type
     */
    public function getType(): string
    {
        return $this->type;
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
     * Get attribute items/options
     * 
     * @return array Array of attribute items
     */
    public function getItems(): array
    {
        return $this->items;
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
     * Set attribute items/options
     * 
     * @param array $items Array of attribute items
     * @return self For method chaining
     */
    public function setItems(array $items): self
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Validate attribute data - type-specific validation logic
     * 
     * @return bool True if valid, false otherwise
     */
    abstract public function validate(): bool;

    /**
     * Process raw value - type-specific value processing
     * 
     * @param string $value Raw value to process
     * @return string Processed value
     */
    abstract public function processValue(string $value): string;

    /**
     * Format display value - type-specific display formatting
     * 
     * @param string $displayValue Display value to format
     * @return string Formatted display value
     */
    abstract public function formatDisplayValue(string $displayValue): string;

    /**
     * Check if attribute supports the given value - type-specific validation
     * 
     * @param string $value Value to check
     * @return bool True if value is supported
     */
    abstract public function supportsValue(string $value): bool;

    /**
     * Render attribute for UI - type-specific rendering configuration
     * 
     * @return array UI rendering configuration
     */
    abstract public function renderForUI(): array;

    /**
     * Get input type for forms - type-specific input type
     * 
     * @return string HTML input type
     */
    abstract public function getInputType(): string;

    /**
     * Save attribute to database with upsert logic
     * 
     * @return bool True on success, false on failure
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $sql = "INSERT INTO attributes (id, name, type) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                name = VALUES(name), 
                type = VALUES(type)";
        
        return Database::execute($sql, [
            $this->id,
            $this->name,
            $this->type
        ]);
    }

    /**
     * Factory method to create appropriate attribute type based on type parameter
     * Uses polymorphism to instantiate correct subclass
     * 
     * @param string $id Attribute ID
     * @param string $name Attribute name
     * @param string $type Attribute type
     * @return self Appropriate Attribute subclass instance
     * @throws \InvalidArgumentException If attribute type is not supported
     */
    public static function create(string $id, string $name, string $type): self
    {
        return match ($type) {
            'swatch' => new SwatchAttribute($id, $name, $type),
            'text' => new TextAttribute($id, $name, $type),
            default => throw new \InvalidArgumentException("Unknown attribute type: {$type}"),
        };
    }

    /**
     * Convert attribute to array representation
     * 
     * @return array Attribute data as associative array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'items' => $this->items,
            'inputType' => $this->getInputType(),
            'createdAt' => $this->createdAt
        ];
    }
}

/**
 * Text Attribute Class
 * 
 * Represents text-based attributes like sizes, capacities, and other string values.
 * Renders as selectable buttons or dropdown options in the UI.
 * 
 * @package App\Models
 */
class TextAttribute extends Attribute
{
    /**
     * Validate text attribute data
     * 
     * @return bool True if valid (requires ID, name, and correct type)
     */
    public function validate(): bool
    {
        return !empty($this->id) && 
               !empty($this->name) && 
               $this->type === 'text';
    }

    /**
     * Process text value by trimming whitespace
     * 
     * @param string $value Raw text value
     * @return string Trimmed text value
     */
    public function processValue(string $value): string
    {
        return trim($value);
    }

    /**
     * Format display value by trimming whitespace
     * 
     * @param string $displayValue Display value to format
     * @return string Formatted display value
     */
    public function formatDisplayValue(string $displayValue): string
    {
        return trim($displayValue);
    }

    /**
     * Check if text attribute supports the given value
     * 
     * @param string $value Value to validate
     * @return bool True if value is non-empty after trimming
     */
    public function supportsValue(string $value): bool
    {
        return !empty(trim($value));
    }

    /**
     * Render text attribute for UI as selectable buttons
     * 
     * @return array UI configuration for text attribute
     */
    public function renderForUI(): array
    {
        return [
            'type' => 'text',
            'displayAs' => 'buttons',
            'allowMultiple' => false,
            'validation' => [
                'required' => true,
                'type' => 'string'
            ],
            'options' => $this->formatOptionsForUI()
        ];
    }

    /**
     * Get input type for text attribute forms
     * 
     * @return string Always returns 'select'
     */
    public function getInputType(): string
    {
        return 'select';
    }

    /**
     * Format attribute items for UI display
     * 
     * @return array Formatted options for UI
     */
    private function formatOptionsForUI(): array
    {
        $options = [];
        
        foreach ($this->items as $item) {
            $options[] = [
                'value' => $item['value'] ?? '',
                'displayValue' => $item['displayValue'] ?? $item['value'] ?? '',
                'available' => true
            ];
        }
        
        return $options;
    }

    /**
     * Get available sizes for size-based text attributes
     * 
     * @return array Array of available size values
     */
    public function getAvailableSizes(): array
    {
        if (!$this->isSizeAttribute()) {
            return [];
        }
        
        return array_map(fn($item) => $item['value'] ?? '', $this->items);
    }

    /**
     * Check if this is a size-related attribute
     * 
     * @return bool True if attribute represents sizes
     */
    public function isSizeAttribute(): bool
    {
        return strtolower($this->name) === 'size';
    }

    /**
     * Check if this is a capacity-related attribute
     * 
     * @return bool True if attribute represents capacity
     */
    public function isCapacityAttribute(): bool
    {
        return strtolower($this->name) === 'capacity';
    }
}

/**
 * Swatch Attribute Class
 * 
 * Represents color-based attributes that display as visual swatches.
 * Supports hex color values and renders as colored squares in the UI.
 * 
 * @package App\Models
 */
class SwatchAttribute extends Attribute
{
    /**
     * Validate swatch attribute data
     * 
     * @return bool True if valid (requires ID, name, and correct type)
     */
    public function validate(): bool
    {
        return !empty($this->id) && 
               !empty($this->name) && 
               $this->type === 'swatch';
    }

    /**
     * Process color value and validate hex format
     * 
     * @param string $value Raw color value
     * @return string Processed color value with # prefix
     */
    public function processValue(string $value): string
    {
        $value = trim($value);
        
        // Ensure hex color has # prefix
        if (!empty($value) && $value[0] !== '#') {
            $value = '#' . $value;
        }
        
        // Validate hex color format
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
            throw new \InvalidArgumentException("Invalid hex color format: {$value}");
        }
        
        return strtolower($value);
    }

    /**
     * Format display value for swatch attribute
     * 
     * @param string $displayValue Display value to format
     * @return string Formatted display value
     */
    public function formatDisplayValue(string $displayValue): string
    {
        return ucfirst(trim($displayValue));
    }

    /**
     * Check if swatch attribute supports the given color value
     * 
     * @param string $value Color value to validate
     * @return bool True if valid hex color format
     */
    public function supportsValue(string $value): bool
    {
        try {
            $this->processValue($value);
            return true;
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Render swatch attribute for UI as color swatches
     * 
     * @return array UI configuration for swatch attribute
     */
    public function renderForUI(): array
    {
        return [
            'type' => 'swatch',
            'displayAs' => 'swatches',
            'allowMultiple' => false,
            'validation' => [
                'required' => true,
                'type' => 'color'
            ],
            'swatches' => $this->formatSwatchesForUI()
        ];
    }

    /**
     * Get input type for swatch attribute forms
     * 
     * @return string Always returns 'color'
     */
    public function getInputType(): string
    {
        return 'color';
    }

    /**
     * Format swatch items for UI display
     * 
     * @return array Formatted swatches for UI
     */
    private function formatSwatchesForUI(): array
    {
        $swatches = [];
        
        foreach ($this->items as $item) {
            $value = $item['value'] ?? '';
            $swatches[] = [
                'value' => $value,
                'displayValue' => $item['displayValue'] ?? ucfirst($value),
                'hexColor' => $value,
                'cssStyle' => $this->getCssStyle($value),
                'available' => true
            ];
        }
        
        return $swatches;
    }

    /**
     * Get color palette from all swatch items
     * 
     * @return array Array of hex color values
     */
    public function getColorPalette(): array
    {
        return array_map(function($item) {
            return $item['value'] ?? '';
        }, $this->items);
    }

    /**
     * Check if this is a color-related attribute
     * 
     * @return bool Always true for swatch attributes
     */
    public function isColorAttribute(): bool
    {
        return true;
    }

    /**
     * Generate CSS style string for a color value
     * 
     * @param string $colorValue Hex color value
     * @return string CSS background-color style
     */
    public function getCssStyle(string $colorValue): string
    {
        return "background-color: {$colorValue};";
    }
} 