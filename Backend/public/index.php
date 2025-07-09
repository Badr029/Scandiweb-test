<?php

require_once __DIR__ . '/../vendor/autoload.php';

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
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Content-Type: application/json");

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->post('/graphql', 'graphql');
});

$routeInfo = $dispatcher->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        if ($handler === 'graphql') {
            echo App\Controller\GraphQL::handle();
        }
        break;
}
