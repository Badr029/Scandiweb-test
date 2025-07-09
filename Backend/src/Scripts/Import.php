<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Config\Database;
use App\Models\Category;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\AttributeItem;
use App\Models\Price;
use App\Models\Gallery;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;

/**
 * Database Import Service
 * 
 * Imports data from data.json into the database using OOP models
 * Demonstrates proper polymorphism and delegation of responsibilities
 * 
 * @package App\Scripts
 */
class DatabaseImporter
{
    private array $data;
    private int $importedCount = 0;
    private CategoryRepository $categoryRepository;
    private ProductRepository $productRepository;
    private bool $forceReimport = false;

    public function __construct(bool $forceReimport = false)
    {
        $this->forceReimport = $forceReimport;
        $this->categoryRepository = new CategoryRepository();
        $this->productRepository = new ProductRepository();
        $this->loadDataFromJson();
    }

    /**
     * Load and validate data from JSON file
     */
    private function loadDataFromJson(): void
    {
        $jsonPath = __DIR__ . '/../../data.json';
        
        if (!file_exists($jsonPath)) {
            throw new \RuntimeException("Data file not found: {$jsonPath}");
        }

        $jsonContent = file_get_contents($jsonPath);
        if ($jsonContent === false) {
            throw new \RuntimeException("Failed to read data file: {$jsonPath}");
        }

        $this->data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid JSON data: " . json_last_error_msg());
        }

        $this->validateDataStructure();
    }

    /**
     * Validate the structure of loaded data
     */
    private function validateDataStructure(): void
    {
        $requiredSections = ['categories', 'products'];
        
        foreach ($requiredSections as $section) {
            if (!isset($this->data['data'][$section])) {
                throw new \RuntimeException("Missing required data section: {$section}");
            }
        }
    }

    /**
     * Import all data using transactions for data integrity
     */
    public function importAll(): bool
    {
        try {
            Database::beginTransaction();
            
            $this->importCategories();
            $this->importProducts();
            
            Database::commit();
            
            $this->logImportSummary();
            return true;
            
        } catch (\Exception $e) {
            Database::rollback();
            $this->logError('Import failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Import categories using polymorphism
     */
    private function importCategories(): void
    {
        $categories = $this->data['data']['categories'] ?? [];
        
        foreach ($categories as $categoryData) {
            $this->importSingleCategory($categoryData);
        }
        
        $this->log("Imported " . count($categories) . " categories");
    }

    /**
     * Import a single category using factory pattern
     */
    private function importSingleCategory(array $categoryData): void
    {
        // Check if category already exists (only skip if not forcing reimport)
        if (!$this->forceReimport && $this->categoryRepository->exists($categoryData['name'])) {
            $this->log("→ Category '{$categoryData['name']}' already exists, skipping");
            return;
        }
        
        $category = Category::create($categoryData['name']);
        
        if ($category->save()) {
            $this->importedCount++;
            $this->log("✓ Category '{$category->getName()}' ({$category->getType()})");
        } else {
            $this->logError("✗ Failed to import category: {$categoryData['name']}");
        }
    }

    /**
     * Import products using polymorphism
     */
    private function importProducts(): void
    {
        $products = $this->data['data']['products'] ?? [];
        $this->log("Found " . count($products) . " products to import");
        
        foreach ($products as $productData) {
            $this->importSingleProduct($productData);
        }
        
        $this->log("Processed " . count($products) . " products");
    }

    /**
     * Import a single product with all related data
     */
    private function importSingleProduct(array $productData): void
    {
        // Check if product already exists (only skip if not forcing reimport)
        if (!$this->forceReimport && $this->productRepository->findById($productData['id']) !== null) {
            $this->log("→ Product '{$productData['name']}' already exists, skipping");
            return;
        }
        
        $this->log("Importing product: " . $productData['name'] . " (ID: " . $productData['id'] . ")");
        
        // Create product using factory pattern (polymorphism)
        $product = Product::create($productData);
        
        if (isset($productData['description'])) {
            $product->setDescription($productData['description']);
        }

        if ($product->save()) {
            $this->importedCount++;
            $this->log("✓ Product '{$product->getName()}' ({$product->getType()})");
            
            // Import related data
            $this->importProductGallery($product, $productData['gallery'] ?? []);
            $this->importProductPrices($product, $productData['prices'] ?? []);
            $this->importProductAttributes($product, $productData['attributes'] ?? []);
            
        } else {
            $this->logError("✗ Failed to import product: {$productData['name']}");
        }
    }

    /**
     * Import product gallery images
     */
    private function importProductGallery(Product $product, array $galleryData): void
    {
        $sortOrder = 0;
        
        foreach ($galleryData as $imageUrl) {
            $gallery = new Gallery($product->getId(), $imageUrl, $sortOrder++);
            
            if (!$gallery->save()) {
                $this->logError("  ✗ Failed to import gallery image: {$imageUrl}");
            }
        }
        
        if (count($galleryData) > 0) {
            $this->log("  ✓ Imported " . count($galleryData) . " gallery images");
        }
    }

    /**
     * Import product prices
     */
    private function importProductPrices(Product $product, array $pricesData): void
    {
        foreach ($pricesData as $priceData) {
            $price = new Price(
                $product->getId(),
                (float) $priceData['amount'],
                $priceData['currency']['label'], // currency
                $priceData['currency']['label'], // label
                $priceData['currency']['symbol'] // symbol
            );
            
            if (!$price->save()) {
                $this->logError("  ✗ Failed to import price: {$priceData['currency']['label']}");
            }
        }
        
        if (count($pricesData) > 0) {
            $this->log("  ✓ Imported " . count($pricesData) . " prices");
        }
    }

    /**
     * Import product attributes using polymorphism
     */
    private function importProductAttributes(Product $product, array $attributesData): void
    {
        foreach ($attributesData as $attributeData) {
            $this->importSingleAttribute($product, $attributeData);
        }
        
        if (count($attributesData) > 0) {
            $this->log("  ✓ Imported " . count($attributesData) . " attributes");
        }
    }

    /**
     * Import a single attribute using factory pattern
     */
    private function importSingleAttribute(Product $product, array $attributeData): void
    {
        try {
            // Create attribute using factory pattern (polymorphism)
            $attribute = Attribute::create(
                $attributeData['id'],
                $attributeData['name'],
                $attributeData['type']
            );
            
            if ($attribute->save()) {
                $this->importAttributeItems($attribute, $attributeData['items'] ?? []);
                $this->linkAttributeToProduct($product, $attribute);
            }
            
        } catch (\InvalidArgumentException $e) {
            $this->logError("  ✗ Unknown attribute type: {$attributeData['type']}");
        }
    }

    /**
     * Import attribute items
     */
    private function importAttributeItems(Attribute $attribute, array $itemsData): void
    {
        foreach ($itemsData as $itemData) {
            $attributeItem = new AttributeItem(
                $itemData['id'],
                $attribute->getId(),
                $itemData['displayValue'],
                $itemData['value']
            );
            
            if (!$attributeItem->save()) {
                $this->logError("    ✗ Failed to import attribute item: {$itemData['displayValue']}");
            }
        }
    }

    /**
     * Link attribute to product
     */
    private function linkAttributeToProduct(Product $product, Attribute $attribute): void
    {
        $sql = "INSERT INTO product_attributes (product_id, attribute_id) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE product_id = product_id";
        
        Database::execute($sql, [$product->getId(), $attribute->getId()]);
    }

    /**
     * Check if data import is needed
     */
    public function isImportNeeded(): bool
    {
        $categoryCount = Database::fetchOne("SELECT COUNT(*) as count FROM categories")['count'] ?? 0;
        $productCount = Database::fetchOne("SELECT COUNT(*) as count FROM products")['count'] ?? 0;
        
        return $categoryCount == 0 && $productCount == 0;
    }

    /**
     * Get import statistics
     */
    public function getImportStats(): array
    {
        return [
            'categories' => Database::fetchOne("SELECT COUNT(*) as count FROM categories")['count'] ?? 0,
            'products' => Database::fetchOne("SELECT COUNT(*) as count FROM products")['count'] ?? 0,
            'attributes' => Database::fetchOne("SELECT COUNT(*) as count FROM attributes")['count'] ?? 0,
            'attribute_items' => Database::fetchOne("SELECT COUNT(*) as count FROM attribute_items")['count'] ?? 0,
            'gallery_items' => Database::fetchOne("SELECT COUNT(*) as count FROM product_gallery")['count'] ?? 0,
            'prices' => Database::fetchOne("SELECT COUNT(*) as count FROM prices")['count'] ?? 0,
        ];
    }

    /**
     * Log import summary
     */
    private function logImportSummary(): void
    {
        $stats = $this->getImportStats();
        
        $this->log("\n=== Import Summary ===");
        foreach ($stats as $table => $count) {
            $this->log("  {$table}: {$count} records");
        }
        $this->log("Total imported items: {$this->importedCount}");
    }

    /**
     * Log message
     */
    private function log(string $message): void
    {
        echo "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
    }

    /**
     * Log error message
     */
    private function logError(string $message): void
    {
        echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $message . "\n";
    }
}

// Execute import when run from command line
if (php_sapi_name() === 'cli') {
    try {
        echo "=== Scandiweb Data Import ===\n";
        
        $importer = new DatabaseImporter();
        
        if (!$importer->isImportNeeded()) {
            echo "Data already exists. Skipping import.\n";
            echo "Current statistics:\n";
            
            $stats = $importer->getImportStats();
            foreach ($stats as $table => $count) {
                echo "  {$table}: {$count} records\n";
            }
            exit(0);
        }
        
        echo "Starting data import...\n";
        
        if ($importer->importAll()) {
            echo "\n✓ Import completed successfully!\n";
            exit(0);
        } else {
            echo "\n✗ Import failed!\n";
            exit(1);
        }
        
    } catch (\Exception $e) {
        echo "\n✗ Import error: " . $e->getMessage() . "\n";
        exit(1);
    }
}