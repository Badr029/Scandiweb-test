<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Add CORS headers to allow frontend access
header("Access-Control-Allow-Origin: https://scandiweb-test-mohamedbadr.web1337.net");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Set content type for JSON responses
header('Content-Type: application/json');

// Handle GraphQL requests directly (since this IS the GraphQL endpoint)
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle GraphQL mutations and queries
        echo App\Controller\GraphQL::handle();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Provide GraphQL endpoint information
        echo json_encode([
            'message' => 'GraphQL endpoint is ready',
            'endpoint' => '/api/public/index.php',
            'methods' => ['POST'],
            'example' => [
                'query' => 'query { categories { id name } }'
            ]
        ]);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed. Use POST for GraphQL queries.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error: ' . $e->getMessage()]);
}