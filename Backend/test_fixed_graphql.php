<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Controller\GraphQL;

echo "=== Testing Fixed GraphQL Implementation ===\n\n";

$graphQL = new GraphQL();

// Test the full frontend query
$query = '{
  products {
    id
    name
    brand
    description
    inStock
    gallery
    category
    prices {
      amount
      currency {
        label
        symbol
      }
    }
    attributes {
      id
      name
      type
      items {
        id
        display_value
        value
      }
    }
  }
}';

echo "Testing full products query...\n";

try {
    $result = $graphQL->handle(['query' => $query]);
    $decoded = json_decode($result, true);
    
    if (isset($decoded['data']['products'])) {
        $products = $decoded['data']['products'];
        echo "✅ SUCCESS: Got " . count($products) . " products\n\n";
        
        // Show details of first product
        if (!empty($products)) {
            $firstProduct = $products[0];
            echo "First product details:\n";
            echo "- ID: " . $firstProduct['id'] . "\n";
            echo "- Name: " . $firstProduct['name'] . "\n";
            echo "- Brand: " . $firstProduct['brand'] . "\n";
            echo "- Prices: " . count($firstProduct['prices']) . " price(s)\n";
            echo "- Attributes: " . count($firstProduct['attributes']) . " attribute(s)\n";
            
            if (!empty($firstProduct['prices'])) {
                $firstPrice = $firstProduct['prices'][0];
                echo "  - First price: " . $firstPrice['amount'] . " " . $firstPrice['currency']['symbol'] . "\n";
            }
            
            if (!empty($firstProduct['attributes'])) {
                $firstAttr = $firstProduct['attributes'][0];
                echo "  - First attribute: " . $firstAttr['name'] . " (" . count($firstAttr['items']) . " items)\n";
            }
        }
    } else {
        echo "❌ ERROR: No products data in response\n";
        echo "Response: " . $result . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n"; 