<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Config\Database;
use App\Models\Category;
use App\Models\AllCategory;
use App\Models\ProductCategory;
use PDO;

/**
 * Category Repository
 * 
 * Handles all category database operations using the Repository pattern.
 * Provides data access abstraction for polymorphic Category entities.
 * Supports both AllCategory and ProductCategory types.
 * 
 * @package App\Repositories
 */
class CategoryRepository
{
    /**
     * Find all categories with polymorphic hydration
     * 
     * @return Category[] Array of category objects
     */
    public function findAll(): array
    {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        $rows = Database::fetchAll($sql);
        
        $categories = [];
        foreach ($rows as $row) {
            $category = $this->hydrateCategory($row);
            if ($category !== null) {
                $categories[] = $category;
            }
        }
        
        return $categories;
    }

    /**
     * Find a category by its unique identifier
     * 
     * @param int $id Category ID
     * @return Category|null Category object or null if not found
     */
    public function findById(int $id): ?Category
    {
        $sql = "SELECT * FROM categories WHERE id = ?";
        $row = Database::fetchOne($sql, [$id]);
        
        if (!$row) {
            return null;
        }
        
        return $this->hydrateCategory($row);
    }

    /**
     * Find a category by its name
     * 
     * @param string $name Category name
     * @return Category|null Category object or null if not found
     */
    public function findByName(string $name): ?Category
    {
        $sql = "SELECT * FROM categories WHERE name = ?";
        $row = Database::fetchOne($sql, [$name]);
        
        if (!$row) {
            return null;
        }
        
        return $this->hydrateCategory($row);
    }

    /**
     * Find only product categories (excludes the special "all" category)
     * 
     * @return ProductCategory[] Array of product category objects
     */
    public function findProductCategories(): array
    {
        $sql = "SELECT * FROM categories WHERE name != 'all' ORDER BY name ASC";
        $rows = Database::fetchAll($sql);
        
        $categories = [];
        foreach ($rows as $row) {
            $category = $this->hydrateCategory($row);
            if ($category instanceof ProductCategory) {
                $categories[] = $category;
            }
        }
        
        return $categories;
    }

    /**
     * Find categories with their product counts included
     * Returns array data rather than objects to include count information
     * 
     * @return array Array of category data with product counts
     */
    public function findWithProductCounts(): array
    {
        $sql = "SELECT c.*, 
                       COALESCE(p.product_count, 0) as product_count
                FROM categories c
                LEFT JOIN (
                    SELECT category, COUNT(*) as product_count 
                    FROM products 
                    WHERE in_stock = 1 
                    GROUP BY category
                ) p ON c.name = p.category
                ORDER BY c.name ASC";
        
        $rows = Database::fetchAll($sql);
        
        $categories = [];
        foreach ($rows as $row) {
            $category = $this->hydrateCategory($row);
            if ($category !== null) {
                // Add product count as additional data
                $categoryData = $category->toArray();
                $categoryData['productCount'] = (int) $row['product_count'];
                $categories[] = $categoryData;
            }
        }
        
        return $categories;
    }

    /**
     * Check if a category exists by name
     * 
     * @param string $name Category name to check
     * @return bool True if category exists, false otherwise
     */
    public function exists(string $name): bool
    {
        $sql = "SELECT COUNT(*) as count FROM categories WHERE name = ?";
        $row = Database::fetchOne($sql, [$name]);
        
        return ((int) $row['count']) > 0;
    }

    /**
     * Save a category to the database using the model's save method
     * 
     * @param Category $category Category to save
     * @return bool True on success, false on failure
     */
    public function save(Category $category): bool
    {
        return $category->save();
    }

    /**
     * Delete a category by its ID
     * 
     * @param int $id Category ID to delete
     * @return bool True on success, false on failure
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM categories WHERE id = ?";
        return Database::execute($sql, [$id]);
    }

    /**
     * Get statistical information about categories
     * 
     * @return array Array containing category statistics
     */
    public function getStatistics(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_categories,
                    COUNT(CASE WHEN name = 'all' THEN 1 END) as special_categories,
                    COUNT(CASE WHEN name != 'all' THEN 1 END) as product_categories
                FROM categories";
        
        $row = Database::fetchOne($sql);
        return $row ?: [];
    }

    /**
     * Create and hydrate a Category object from a database row
     * Uses polymorphism to create the appropriate Category subclass
     * 
     * @param array $row Database row data
     * @return Category|null Category object or null on error
     */
    private function hydrateCategory(array $row): ?Category
    {
        $category = Category::create($row['name']);
        
        if (isset($row['id'])) {
            $category->setId((int) $row['id']);
        }
        
        if (isset($row['created_at'])) {
            $category->setCreatedAt($row['created_at']);
        }
        
        return $category;
    }
} 