<?php

require_once __DIR__ . '/../vendor/autoload.php';

<<<<<<< HEAD
// Add CORS headers to allow frontend access
header("Access-Control-Allow-Origin: https://scandiweb-test-mohamedbadr.web1337.net");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
=======
// CORS Configuration for Production Deployment
// Updated Vercel URL: https://scandiweb-test-gilt.vercel.app
$allowedOrigins = [
    'http://localhost:3000',        // Development
    'http://localhost:5173',        // Vite dev server
    'https://scandiweb-test-gilt.vercel.app', // Production Vercel (NEW URL)
    'https://scandiweb-test-badrs-projects-6643e546.vercel.app', // Backup Vercel URL
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    // Production fallback - allow new Vercel domain
    header("Access-Control-Allow-Origin: https://scandiweb-test-gilt.vercel.app");
    header("Access-Control-Allow-Credentials: false");
}

header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
>>>>>>> eb7cdabb53e79ecc83ff9bed95ef43cf2512699f
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
<<<<<<< HEAD
        echo json_encode(['error' => 'Method Not Allowed. Use POST for GraphQL queries.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error: ' . $e->getMessage()]);
}
=======
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        if ($handler === 'graphql') {
            echo App\Controller\GraphQL::handle();
        }
        break;
}
>>>>>>> eb7cdabb53e79ecc83ff9bed95ef43cf2512699f
