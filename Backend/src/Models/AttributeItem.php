<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * AttributeItem Model
 * 
 * Represents individual attribute values
 * Handles attribute item data operations and business logic
 * 
 * @package App\Models
 */
class AttributeItem
{
    private string $id;
    private string $attributeId;
    private string $displayValue;
    private string $value;
    private ?string $createdAt = null;

    public function __construct(
        string $id,
        string $attributeId,
        string $displayValue,
        string $value
    ) {
        $this->id = $id;
        $this->attributeId = $attributeId;
        $this->displayValue = $displayValue;
        $this->value = $value;
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getAttributeId(): string
    {
        return $this->attributeId;
    }

    public function getDisplayValue(): string
    {
        return $this->displayValue;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    // Setters
    public function setDisplayValue(string $displayValue): self
    {
        $this->displayValue = $displayValue;
        return $this;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Validate attribute item data
     */
    public function validate(): bool
    {
        return !empty($this->id) && 
               !empty($this->attributeId) && 
               !empty($this->displayValue) && 
               !empty($this->value);
    }

    /**
     * Save attribute item to database
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $sql = "INSERT INTO attribute_items (id, attribute_id, display_value, value) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                display_value = VALUES(display_value), 
                value = VALUES(value)";
        
        return Database::execute($sql, [
            $this->id,
            $this->attributeId,
            $this->displayValue,
            $this->value
        ]);
    }

    /**
     * Get parent attribute
     */
    public function getAttribute(): ?Attribute
    {
        $sql = "SELECT * FROM attributes WHERE id = ?";
        $data = Database::fetchOne($sql, [$this->attributeId]);
        
        if (!$data) {
            return null;
        }
        
        return Attribute::create($data['id'], $data['name'], $data['type']);
    }

    /**
     * Check if this item is a color swatch
     */
    public function isColorSwatch(): bool
    {
        $attribute = $this->getAttribute();
        return $attribute !== null && $attribute->getType() === 'swatch';
    }

    /**
     * Check if this item is a text attribute
     */
    public function isTextAttribute(): bool
    {
        $attribute = $this->getAttribute();
        return $attribute !== null && $attribute->getType() === 'text';
    }

    /**
     * Get formatted value for display
     */
    public function getFormattedDisplayValue(): string
    {
        $attribute = $this->getAttribute();
        
        if ($attribute === null) {
            return $this->displayValue;
        }
        
        return $attribute->formatDisplayValue($this->displayValue);
    }

    /**
     * Get processed value
     */
    public function getProcessedValue(): string
    {
        $attribute = $this->getAttribute();
        
        if ($attribute === null) {
            return $this->value;
        }
        
        return $attribute->processValue($this->value);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'attributeId' => $this->attributeId,
            'displayValue' => $this->displayValue,
            'value' => $this->value,
            'created_at' => $this->createdAt
        ];
    }

    public function toGraphQLArray(): array
    {
        return [
            'id' => $this->id,
            'displayValue' => $this->displayValue,
            'value' => $this->value,
            '__typename' => 'Attribute'
        ];
    }
} 