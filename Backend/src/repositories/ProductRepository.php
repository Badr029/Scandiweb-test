<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use App\Models\Product;
use App\Models\SimpleProduct;
use App\Models\ConfigurableProduct;
use App\Models\Gallery;
use App\Models\Price;
use App\Models\Attribute;
use PDO;

/**
 * Product Repository
 * 
 * Handles all product database operations using the Repository pattern.
 * Provides data access abstraction for Product entities and their related data.
 * Supports polymorphic Product types (Simple and Configurable products).
 * 
 * @package App\Repositories
 */
class ProductRepository
{
    /**
     * Find all products with their related data
     * 
     * @return Product[] Array of product objects
     */
    public function findAll(): array
    {
        $sql = "SELECT * FROM products ORDER BY name ASC";
        $rows = Database::fetchAll($sql);
        
        $products = [];
        foreach ($rows as $row) {
            $product = $this->hydrateProduct($row);
            if ($product !== null) {
                $this->loadProductRelations($product);
                $products[] = $product;
            }
        }
        
        return $products;
    }

    /**
     * Find a product by its unique identifier
     * 
     * @param string $id Product ID
     * @return Product|null Product object or null if not found
     */
    public function findById(string $id): ?Product
    {
        $sql = "SELECT * FROM products WHERE id = ?";
        $row = Database::fetchOne($sql, [$id]);
        
        if (!$row) {
            return null;
        }
        
        $product = $this->hydrateProduct($row);
        if ($product !== null) {
            $this->loadProductRelations($product);
        }
        
        return $product;
    }

    /**
     * Find products by category name
     * 
     * @param string $category Category name or 'all' for all products
     * @return Product[] Array of products in the category
     */
    public function findByCategory(string $category): array
    {
        if ($category === 'all') {
            return $this->findAll();
        }

        $sql = "SELECT * FROM products WHERE category = ? ORDER BY name ASC";
        $rows = Database::fetchAll($sql, [$category]);
        
        $products = [];
        foreach ($rows as $row) {
            $product = $this->hydrateProduct($row);
            if ($product !== null) {
                $this->loadProductRelations($product);
                $products[] = $product;
            }
        }
        
        return $products;
    }

    /**
     * Find only products that are currently in stock
     * 
     * @return Product[] Array of in-stock products
     */
    public function findInStock(): array
    {
        $sql = "SELECT * FROM products WHERE in_stock = 1 ORDER BY name ASC";
        $rows = Database::fetchAll($sql);
        
        $products = [];
        foreach ($rows as $row) {
            $product = $this->hydrateProduct($row);
            if ($product !== null) {
                $this->loadProductRelations($product);
                $products[] = $product;
            }
        }
        
        return $products;
    }

    /**
     * Search products by text across name, brand, and description fields
     * 
     * @param string $query Search term
     * @return Product[] Array of matching products
     */
    public function searchByText(string $query): array
    {
        $searchTerm = '%' . $query . '%';
        $sql = "SELECT * FROM products 
                WHERE name LIKE ? OR brand LIKE ? OR description LIKE ?
                ORDER BY name ASC";
        
        $rows = Database::fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm]);
        
        $products = [];
        foreach ($rows as $row) {
            $product = $this->hydrateProduct($row);
            if ($product !== null) {
                $this->loadProductRelations($product);
                $products[] = $product;
            }
        }
        
        return $products;
    }

    /**
     * Find products that have specific attribute values
     * 
     * @param array $attributeFilters Associative array of attribute name => values
     * @return Product[] Array of products matching the attribute filters
     */
    public function findWithAttributes(array $attributeFilters = []): array
    {
        $sql = "SELECT DISTINCT p.* FROM products p
                INNER JOIN product_attributes pa ON p.id = pa.product_id
                INNER JOIN attributes a ON pa.attribute_id = a.id";
        
        $params = [];
        $whereConditions = [];
        
        if (!empty($attributeFilters)) {
            foreach ($attributeFilters as $attributeName => $values) {
                $placeholders = str_repeat('?,', count($values) - 1) . '?';
                $whereConditions[] = "a.name = ? AND a.id IN (
                    SELECT ai.attribute_id FROM attribute_items ai 
                    WHERE ai.value IN ($placeholders)
                )";
                $params[] = $attributeName;
                $params = array_merge($params, $values);
            }
        }
        
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        $sql .= " ORDER BY p.name ASC";
        
        $rows = Database::fetchAll($sql, $params);
        
        $products = [];
        foreach ($rows as $row) {
            $product = $this->hydrateProduct($row);
            if ($product !== null) {
                $this->loadProductRelations($product);
                $products[] = $product;
            }
        }
        
        return $products;
    }

    /**
     * Find only configurable products (products with selectable attributes)
     * 
     * @return ConfigurableProduct[] Array of configurable products
     */
    public function findConfigurableProducts(): array
    {
        $sql = "SELECT DISTINCT p.* FROM products p
                INNER JOIN product_attributes pa ON p.id = pa.product_id
                ORDER BY p.name ASC";
        
        $rows = Database::fetchAll($sql);
        
        $products = [];
        foreach ($rows as $row) {
            $product = $this->hydrateProduct($row);
            if ($product !== null && $product instanceof ConfigurableProduct) {
                $this->loadProductRelations($product);
                $products[] = $product;
            }
        }
        
        return $products;
    }

    /**
     * Find only simple products (products without configurable attributes)
     * 
     * @return SimpleProduct[] Array of simple products
     */
    public function findSimpleProducts(): array
    {
        $sql = "SELECT p.* FROM products p
                LEFT JOIN product_attributes pa ON p.id = pa.product_id
                WHERE pa.product_id IS NULL
                ORDER BY p.name ASC";
        
        $rows = Database::fetchAll($sql);
        
        $products = [];
        foreach ($rows as $row) {
            $product = $this->hydrateProduct($row);
            if ($product !== null && $product instanceof SimpleProduct) {
                $this->loadProductRelations($product);
                $products[] = $product;
            }
        }
        
        return $products;
    }

    /**
     * Get product count grouped by category
     * 
     * @return array Associative array of category => count
     */
    public function getCountByCategory(): array
    {
        $sql = "SELECT category, COUNT(*) as count FROM products GROUP BY category";
        $rows = Database::fetchAll($sql);
        
        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['category']] = (int) $row['count'];
        }
        
        return $counts;
    }

    /**
     * Save a product to the database using the model's save method
     * 
     * @param Product $product Product to save
     * @return bool True on success, false on failure
     */
    public function save(Product $product): bool
    {
        return $product->save();
    }

    /**
     * Delete a product and all its related data
     * 
     * @param string $id Product ID to delete
     * @return bool True on success, false on failure
     */
    public function delete(string $id): bool
    {
        try {
            // Delete related data first (foreign keys will handle cascade)
            Database::execute("DELETE FROM product_gallery WHERE product_id = ?", [$id]);
            Database::execute("DELETE FROM prices WHERE product_id = ?", [$id]);
            Database::execute("DELETE FROM product_attributes WHERE product_id = ?", [$id]);
            
            // Delete the product
            return Database::execute("DELETE FROM products WHERE id = ?", [$id]);
            
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create and hydrate a Product object from a database row
     * Uses polymorphism to create the appropriate Product subclass
     * 
     * @param array $row Database row data
     * @return Product|null Product object or null on error
     */
    private function hydrateProduct(array $row): ?Product
    {
        // Check if product has attributes to determine type
        $attributeCount = $this->getProductAttributeCount($row['id']);
        
        $productData = [
            'id' => $row['id'],
            'name' => $row['name'],
            'brand' => $row['brand'],
            'category' => $row['category'],
            'inStock' => (bool) $row['in_stock'],
            'attributes' => $attributeCount > 0 ? ['dummy'] : [] // Factory needs this for type determination
        ];
        
        $product = Product::create($productData);
        
        if (isset($row['description'])) {
            $product->setDescription($row['description']);
        }
        
        if (isset($row['created_at'])) {
            $product->setCreatedAt($row['created_at']);
        }
        
        return $product;
    }

    /**
     * Load all related data for a product (gallery, prices, attributes)
     * 
     * @param Product $product Product to load relations for
     * @return void
     */
    private function loadProductRelations(Product $product): void
    {
        // Load gallery
        $gallery = Gallery::getByProductId($product->getId());
        $galleryUrls = array_map(fn($g) => $g->getImageUrl(), $gallery);
        $product->setGallery($galleryUrls);
        
        // Load prices
        $prices = $this->loadProductPrices($product->getId());
        $product->setPrices($prices);
        
        // Load attributes for all products (empty array for simple products)
        $attributes = $this->loadProductAttributes($product->getId());
        $product->setAttributes($attributes);
    }

    /**
     * Load price data for a product
     * 
     * @param string $productId Product ID
     * @return array Array of price data with currency information
     */
    private function loadProductPrices(string $productId): array
    {
        $sql = "SELECT * FROM prices WHERE product_id = ?";
        $rows = Database::fetchAll($sql, [$productId]);
        
        $prices = [];
        foreach ($rows as $row) {
            $prices[] = [
                'amount' => (float) $row['amount'],
                'currency' => [
                    'label' => $row['currency_label'],
                    'symbol' => $row['currency_symbol']
                ]
            ];
        }
        
        return $prices;
    }

    /**
     * Load attribute data with items for a product
     * 
     * @param string $productId Product ID
     * @return array Array of attribute data with items
     */
    private function loadProductAttributes(string $productId): array
    {
        $sql = "SELECT a.*, ai.id as item_id, ai.display_value, ai.value 
                FROM attributes a
                INNER JOIN product_attributes pa ON a.id = pa.attribute_id
                INNER JOIN attribute_items ai ON a.id = ai.attribute_id
                WHERE pa.product_id = ?
                ORDER BY a.name, ai.display_value";
        
        $rows = Database::fetchAll($sql, [$productId]);
        
        $attributesData = [];
        foreach ($rows as $row) {
            $attributeId = $row['id'];
            
            if (!isset($attributesData[$attributeId])) {
                $attributesData[$attributeId] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'type' => $row['type'],
                    'items' => []
                ];
            }
            
            $attributesData[$attributeId]['items'][] = [
                'id' => $row['item_id'],
                'display_value' => $row['display_value'],
                'value' => $row['value']
            ];
        }
        
        return array_values($attributesData);
    }

    /**
     * Count the number of attributes for a product
     * 
     * @param string $productId Product ID
     * @return int Number of attributes
     */
    private function getProductAttributeCount(string $productId): int
    {
        $sql = "SELECT COUNT(*) as count FROM product_attributes WHERE product_id = ?";
        $row = Database::fetchOne($sql, [$productId]);
        
        return (int) ($row['count'] ?? 0);
    }
} 