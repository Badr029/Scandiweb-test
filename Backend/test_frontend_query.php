<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Controller\GraphQL;

// Test the exact query that the frontend is using
$graphQL = new GraphQL();

// GET_PRODUCTS query from Frontend/src/graphql/queries.ts
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

echo "Testing GET_PRODUCTS query...\n";
echo "Query: " . $query . "\n\n";

try {
    $result = $graphQL->handle(['query' => $query]);
    echo "Result:\n";
    echo $result . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
} 