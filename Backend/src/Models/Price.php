<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Price Model
 * 
 * Handles product pricing data and currency operations
 * 
 * @package App\Models
 */
class Price
{
    private ?int $id = null;
    private string $productId;
    private float $amount;
    private string $currency;
    private string $label;
    private string $symbol;
    private ?string $createdAt = null;

    public function __construct(
        string $productId,
        float $amount,
        string $currency,
        string $label,
        string $symbol
    ) {
        $this->productId = $productId;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->label = $label;
        $this->symbol = $symbol;
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

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
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

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;
        return $this;
    }

    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Validate price data
     */
    public function validate(): bool
    {
        return !empty($this->productId) && 
               $this->amount >= 0 && 
               !empty($this->currency) && 
               !empty($this->label) && 
               !empty($this->symbol);
    }

    /**
     * Save price to database
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
        $sql = "INSERT INTO prices (product_id, amount, currency_label, currency_symbol) 
                VALUES (?, ?, ?, ?)";
        
        return Database::execute($sql, [
            $this->productId,
            $this->amount,
            $this->label,
            $this->symbol
        ]);
    }

    protected function update(): bool
    {
        $sql = "UPDATE prices SET 
                amount = ?, 
                currency_label = ?, 
                currency_symbol = ? 
                WHERE id = ?";
        
        return Database::execute($sql, [
            $this->amount,
            $this->label,
            $this->symbol,
            $this->id
        ]);
    }

    /**
     * Get formatted price string
     */
    public function getFormattedPrice(): string
    {
        return $this->symbol . number_format($this->amount, 2);
    }

    /**
     * Get formatted price with currency label
     */
    public function getFormattedPriceWithLabel(): string
    {
        return $this->getFormattedPrice() . ' ' . $this->label;
    }

    /**
     * Check if this is the default currency (USD)
     */
    public function isDefaultCurrency(): bool
    {
        return strtoupper($this->currency) === 'USD';
    }

    /**
     * Get currency display information
     */
    public function getCurrencyInfo(): array
    {
        return [
            'currency' => $this->currency,
            'label' => $this->label,
            'symbol' => $this->symbol
        ];
    }

    /**
     * Compare price with another price
     */
    public function isLowerThan(Price $otherPrice): bool
    {
        if ($this->currency !== $otherPrice->getCurrency()) {
            throw new \InvalidArgumentException('Cannot compare prices with different currencies');
        }
        
        return $this->amount < $otherPrice->getAmount();
    }

    /**
     * Calculate discount percentage compared to another price
     */
    public function getDiscountPercentage(Price $originalPrice): float
    {
        if ($this->currency !== $originalPrice->getCurrency()) {
            throw new \InvalidArgumentException('Cannot calculate discount for different currencies');
        }
        
        if ($originalPrice->getAmount() === 0.0) {
            return 0.0;
        }
        
        return (($originalPrice->getAmount() - $this->amount) / $originalPrice->getAmount()) * 100;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'productId' => $this->productId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'label' => $this->label,
            'symbol' => $this->symbol,
            'formattedPrice' => $this->getFormattedPrice(),
            'created_at' => $this->createdAt
        ];
    }

    public function toGraphQLArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => [
                'label' => $this->label,
                'symbol' => $this->symbol
            ],
            '__typename' => 'Price'
        ];
    }
} 