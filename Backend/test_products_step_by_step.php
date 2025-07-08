<?php

require_once 'vendor/autoload.php';

use App\Repositories\ProductRepository;

echo "Testing Products Step by Step\n";
echo "============================\n\n";

try {
    echo "1. Testing ProductRepository::findAll()...\n";
    $productRepository = new ProductRepository();
    $products = $productRepository->findAll();
    echo "✓ Found " . count($products) . " products\n\n";
    
    if (count($products) > 0) {
        $product = $products[0];
        echo "2. Testing individual Product methods...\n";
        
        echo "Testing basic fields:\n";
        echo "- getId(): " . $product->getId() . "\n";
        echo "- getName(): " . $product->getName() . "\n";
        echo "- getBrand(): " . $product->getBrand() . "\n";
        echo "- getCategory(): " . $product->getCategory() . "\n";
        echo "- getType(): " . $product->getType() . "\n";
        
        echo "\nTesting boolean fields:\n";
        try {
            echo "- isInStock(): " . ($product->isInStock() ? 'true' : 'false') . "\n";
        } catch (Exception $e) {
            echo "- isInStock() ERROR: " . $e->getMessage() . "\n";
        }
        
        try {
            echo "- hasConfigurableOptions(): " . ($product->hasConfigurableOptions() ? 'true' : 'false') . "\n";
        } catch (Exception $e) {
            echo "- hasConfigurableOptions() ERROR: " . $e->getMessage() . "\n";
        }
        
        echo "\nTesting optional fields:\n";
        try {
            $desc = $product->getDescription();
            echo "- getDescription(): " . (strlen($desc) > 50 ? substr($desc, 0, 50) . "..." : $desc) . "\n";
        } catch (Exception $e) {
            echo "- getDescription() ERROR: " . $e->getMessage() . "\n";
        }
        
        echo "\nTesting array fields:\n";
        try {
            $gallery = $product->getGallery();
            echo "- getGallery(): " . count($gallery) . " images\n";
        } catch (Exception $e) {
            echo "- getGallery() ERROR: " . $e->getMessage() . "\n";
        }
        
        try {
            $prices = $product->getPrices();
            echo "- getPrices(): " . count($prices) . " prices\n";
            if (count($prices) > 0) {
                echo "  First price: " . json_encode($prices[0]) . "\n";
            }
        } catch (Exception $e) {
            echo "- getPrices() ERROR: " . $e->getMessage() . "\n";
        }
        
        try {
            $attributes = $product->getAttributes();
            echo "- getAttributes(): " . count($attributes) . " attributes\n";
            if (count($attributes) > 0) {
                $attr = $attributes[0];
                echo "  First attribute class: " . get_class($attr) . "\n";
                echo "  First attribute ID: " . $attr->getId() . "\n";
                echo "  First attribute name: " . $attr->getName() . "\n";
                echo "  First attribute items: " . count($attr->getItems()) . " items\n";
            }
        } catch (Exception $e) {
            echo "- getAttributes() ERROR: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Throwable $e) {
    echo "❌ Error occurred:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} 