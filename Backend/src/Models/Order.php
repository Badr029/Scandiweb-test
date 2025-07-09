<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Abstract Order Model
 * 
 * Base class for all order types demonstrating polymorphism.
 * Provides common functionality and enforces state-specific implementation
 * through abstract methods. Supports PendingOrder, CompletedOrder, and CancelledOrder types.
 * 
 * @package App\Models
 */
abstract class Order
{
    protected ?int $id = null;
    protected string $status;
    protected float $totalAmount;
    protected string $currency;
    protected ?string $customerEmail = null;
    protected ?string $shippingAddress = null;
    protected ?string $createdAt = null;
    protected ?string $updatedAt = null;
    protected array $items = [];

    /**
     * Order constructor
     * 
     * @param float $totalAmount Order total amount
     * @param string $currency Currency code (default: USD)
     * @param string|null $customerEmail Customer email address
     */
    public function __construct(
        float $totalAmount,
        string $currency = 'USD',
        ?string $customerEmail = null
    ) {
        $this->totalAmount = $totalAmount;
        $this->currency = $currency;
        $this->customerEmail = $customerEmail;
        $this->status = $this->getInitialStatus();
    }

    /**
     * Get order ID
     * 
     * @return int|null Order identifier or null if not persisted
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get order status
     * 
     * @return string Current order status
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Get order total amount
     * 
     * @return float Total amount of the order
     */
    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    /**
     * Get order currency
     * 
     * @return string Currency code
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Get customer email
     * 
     * @return string|null Customer email or null if not provided
     */
    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail;
    }

    /**
     * Get shipping address
     * 
     * @return string|null Shipping address or null if not set
     */
    public function getShippingAddress(): ?string
    {
        return $this->shippingAddress;
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
     * Get last update timestamp
     * 
     * @return string|null Last update timestamp or null if not set
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * Get order items
     * 
     * @return array Array of order items
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Set order ID
     * 
     * @param int $id Order ID
     * @return self For method chaining
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set customer email
     * 
     * @param string|null $customerEmail Customer email
     * @return self For method chaining
     */
    public function setCustomerEmail(?string $customerEmail): self
    {
        $this->customerEmail = $customerEmail;
        return $this;
    }

    /**
     * Set shipping address
     * 
     * @param string|null $shippingAddress Shipping address
     * @return self For method chaining
     */
    public function setShippingAddress(?string $shippingAddress): self
    {
        $this->shippingAddress = $shippingAddress;
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
     * Set last update timestamp
     * 
     * @param string $updatedAt Update timestamp
     * @return self For method chaining
     */
    public function setUpdatedAt(string $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Set order items
     * 
     * @param array $items Array of order items
     * @return self For method chaining
     */
    public function setItems(array $items): self
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Get initial status for order type - must be implemented by subclasses
     * 
     * @return string Initial status for this order type
     */
    abstract protected function getInitialStatus(): string;

    /**
     * Validate order data - type-specific validation logic
     * 
     * @return bool True if valid, false otherwise
     */
    abstract public function validate(): bool;

    /**
     * Check if order can be modified - polymorphic behavior
     * 
     * @return bool True if order can be modified
     */
    abstract public function canBeModified(): bool;

    /**
     * Check if order can be cancelled - polymorphic behavior
     * 
     * @return bool True if order can be cancelled
     */
    abstract public function canBeCancelled(): bool;

    /**
     * Process order according to its current state - type-specific logic
     * 
     * @return bool True on successful processing
     */
    abstract public function process(): bool;

    /**
     * Get available actions for current order state - polymorphic behavior
     * 
     * @return array Array of available action names
     */
    abstract public function getAvailableActions(): array;

    /**
     * Save order to database with insert/update logic
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
     * Insert new order into database
     * 
     * @return bool True on success, false on failure
     */
    protected function insert(): bool
    {
        $sql = "INSERT INTO orders (status, total_amount, currency, customer_email, shipping_address) 
                VALUES (?, ?, ?, ?, ?)";
        
        $result = Database::execute($sql, [
            $this->status,
            $this->totalAmount,
            $this->currency,
            $this->customerEmail,
            $this->shippingAddress
        ]);
        
        if ($result) {
            $this->id = (int) Database::getLastInsertId();
        }
        
        return $result;
    }

    /**
     * Update existing order in database
     * 
     * @return bool True on success, false on failure
     */
    protected function update(): bool
    {
        $sql = "UPDATE orders SET 
                status = ?, 
                total_amount = ?, 
                currency = ?, 
                customer_email = ?, 
                shipping_address = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
        
        return Database::execute($sql, [
            $this->status,
            $this->totalAmount,
            $this->currency,
            $this->customerEmail,
            $this->shippingAddress,
            $this->id
        ]);
    }

    /**
     * Factory method to create appropriate order type based on status
     * Uses polymorphism to instantiate correct subclass
     * 
     * @param string $status Order status
     * @param float $totalAmount Order total amount
     * @param string $currency Currency code
     * @param string|null $customerEmail Customer email
     * @return self Appropriate Order subclass instance
     */
    public static function create(
        string $status,
        float $totalAmount,
        string $currency = 'USD',
        ?string $customerEmail = null
    ): self {
        return match ($status) {
            'pending' => new PendingOrder($totalAmount, $currency, $customerEmail),
            'completed' => new CompletedOrder($totalAmount, $currency, $customerEmail),
            'cancelled' => new CancelledOrder($totalAmount, $currency, $customerEmail),
            default => new PendingOrder($totalAmount, $currency, $customerEmail),
        };
    }

    /**
     * Convert order to array representation
     * 
     * @return array Order data as associative array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'totalAmount' => $this->totalAmount,
            'currency' => $this->currency,
            'customerEmail' => $this->customerEmail,
            'shippingAddress' => $this->shippingAddress,
            'canBeModified' => $this->canBeModified(),
            'canBeCancelled' => $this->canBeCancelled(),
            'availableActions' => $this->getAvailableActions(),
            'items' => $this->items,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }
}

/**
 * Pending Order Class
 * 
 * Represents orders that are awaiting payment or processing.
 * These orders can be modified or cancelled by customers.
 * 
 * @package App\Models
 */
class PendingOrder extends Order
{
    /**
     * Get initial status for pending orders
     * 
     * @return string Always returns 'pending'
     */
    protected function getInitialStatus(): string
    {
        return 'pending';
    }

    /**
     * Validate pending order data
     * 
     * @return bool True if valid (requires positive amount)
     */
    public function validate(): bool
    {
        return $this->totalAmount > 0 && !empty($this->currency);
    }

    /**
     * Check if pending order can be modified
     * 
     * @return bool Always true for pending orders
     */
    public function canBeModified(): bool
    {
        return true;
    }

    /**
     * Check if pending order can be cancelled
     * 
     * @return bool Always true for pending orders
     */
    public function canBeCancelled(): bool
    {
        return true;
    }

    /**
     * Process pending order (move to completed state)
     * 
     * @return bool True on successful processing
     */
    public function process(): bool
    {
        $this->status = 'completed';
        return $this->save();
    }

    /**
     * Get available actions for pending orders
     * 
     * @return array Array of action names
     */
    public function getAvailableActions(): array
    {
        return ['modify', 'cancel', 'complete', 'add_item', 'remove_item'];
    }

    /**
     * Add item to pending order
     * 
     * @param array $item Item data to add
     * @return bool True on success
     */
    public function addItem(array $item): bool
    {
        if (!$this->canBeModified()) {
            return false;
        }
        
        $this->items[] = $item;
        return true;
    }

    /**
     * Remove item from pending order by index
     * 
     * @param int $itemIndex Index of item to remove
     * @return bool True on success
     */
    public function removeItem(int $itemIndex): bool
    {
        if (!$this->canBeModified() || !isset($this->items[$itemIndex])) {
            return false;
        }
        
        unset($this->items[$itemIndex]);
        $this->items = array_values($this->items); // Re-index array
        return true;
    }
}

/**
 * Completed Order Class
 * 
 * Represents orders that have been successfully processed and paid for.
 * These orders are immutable and can generate invoices.
 * 
 * @package App\Models
 */
class CompletedOrder extends Order
{
    /**
     * Get initial status for completed orders
     * 
     * @return string Always returns 'completed'
     */
    protected function getInitialStatus(): string
    {
        return 'completed';
    }

    /**
     * Validate completed order data
     * 
     * @return bool True if valid (requires positive amount and customer email)
     */
    public function validate(): bool
    {
        return $this->totalAmount > 0 && 
               !empty($this->currency) && 
               !empty($this->customerEmail);
    }

    /**
     * Check if completed order can be modified
     * 
     * @return bool Always false for completed orders
     */
    public function canBeModified(): bool
    {
        return false;
    }

    /**
     * Check if completed order can be cancelled
     * 
     * @return bool Always false for completed orders
     */
    public function canBeCancelled(): bool
    {
        return false;
    }

    /**
     * Process completed order (already processed)
     * 
     * @return bool Always true (no further processing needed)
     */
    public function process(): bool
    {
        return true;
    }

    /**
     * Get available actions for completed orders
     * 
     * @return array Array of action names
     */
    public function getAvailableActions(): array
    {
        return ['generate_invoice', 'view_details'];
    }

    /**
     * Generate invoice data for completed order
     * 
     * @return array Invoice data with order details
     */
    public function generateInvoice(): array
    {
        return [
            'order_id' => $this->id,
            'customer_email' => $this->customerEmail,
            'total_amount' => $this->totalAmount,
            'currency' => $this->currency,
            'items' => $this->items,
            'invoice_date' => date('Y-m-d H:i:s'),
            'status' => 'paid'
        ];
    }
}

/**
 * Cancelled Order Class
 * 
 * Represents orders that have been cancelled by customers or system.
 * These orders are immutable and track cancellation information.
 * 
 * @package App\Models
 */
class CancelledOrder extends Order
{
    /**
     * Get initial status for cancelled orders
     * 
     * @return string Always returns 'cancelled'
     */
    protected function getInitialStatus(): string
    {
        return 'cancelled';
    }

    /**
     * Validate cancelled order data
     * 
     * @return bool True if valid (requires amount but no payment)
     */
    public function validate(): bool
    {
        return $this->totalAmount >= 0 && !empty($this->currency);
    }

    /**
     * Check if cancelled order can be modified
     * 
     * @return bool Always false for cancelled orders
     */
    public function canBeModified(): bool
    {
        return false;
    }

    /**
     * Check if cancelled order can be cancelled
     * 
     * @return bool Always false for cancelled orders (already cancelled)
     */
    public function canBeCancelled(): bool
    {
        return false;
    }

    /**
     * Process cancelled order (already cancelled)
     * 
     * @return bool Always true (no processing needed)
     */
    public function process(): bool
    {
        return true;
    }

    /**
     * Get available actions for cancelled orders
     * 
     * @return array Array of action names
     */
    public function getAvailableActions(): array
    {
        return ['view_cancellation_details'];
    }

    /**
     * Get cancellation information for the order
     * 
     * @return array Cancellation details
     */
    public function getCancellationInfo(): array
    {
        return [
            'order_id' => $this->id,
            'original_amount' => $this->totalAmount,
            'currency' => $this->currency,
            'cancelled_at' => $this->updatedAt ?? $this->createdAt,
            'reason' => 'Customer cancellation'
        ];
    }
} 