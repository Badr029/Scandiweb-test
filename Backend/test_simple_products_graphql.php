<?php

require_once 'vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

use App\Repositories\ProductRepository;
use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;

echo "Testing Simple Products GraphQL\n";
echo "==============================\n\n";

try {
    echo "1. Initialize repository...\n";
    $productRepository = new ProductRepository();
    
    echo "2. Create simple Product type with basic fields only...\n";
    $productType = new ObjectType([
        'name' => 'Product',
        'fields' => [
            'id' => [
                'type' => Type::string(),
                'resolve' => function($product) {
                    return $product->getId();
                }
            ],
            'name' => [
                'type' => Type::string(),
                'resolve' => function($product) {
                    return $product->getName();
                }
            ],
            'brand' => [
                'type' => Type::string(),
                'resolve' => function($product) {
                    return $product->getBrand();
                }
            ]
        ]
    ]);
    
    echo "✓ Simple Product type created\n";
    
    echo "3. Create Query type...\n";
    $queryType = new ObjectType([
        'name' => 'Query',
        'fields' => [
            'products' => [
                'type' => Type::listOf($productType),
                'resolve' => function() use ($productRepository) {
                    return $productRepository->findAll();
                }
            ]
        ]
    ]);
    
    echo "✓ Query type created\n";
    
    echo "4. Create schema...\n";
    $schema = new Schema(
        (new SchemaConfig())
        ->setQuery($queryType)
    );
    
    echo "✓ Schema created\n";
    
    echo "5. Test GraphQL execution...\n";
    $query = '{ products { id name brand } }';
    
    $result = GraphQLBase::executeQuery($schema, $query);
    $output = $result->toArray();
    
    if (isset($output['errors'])) {
        echo "❌ GraphQL Errors:\n";
        foreach ($output['errors'] as $error) {
            echo "- " . $error['message'] . "\n";
        }
    } else {
        echo "✅ Simple products query works!\n";
        echo "Found " . count($output['data']['products']) . " products\n";
        echo "First product: " . json_encode($output['data']['products'][0] ?? null) . "\n";
    }
    
} catch (Throwable $e) {
    echo "❌ Error occurred:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} 