<?php
// Root index.php for InfinityFree
// This file goes in htdocs/index.php (keeps your existing structure intact)

// Check if this is an API request
$requestUri = $_SERVER['REQUEST_URI'] ?? '';

if (strpos($requestUri, '/api') === 0) {
    // API request - include the existing API handler
    if (file_exists(__DIR__ . '/api/public/index.php')) {
        require_once __DIR__ . '/api/public/index.php';
        exit();
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'API not found']);
        exit();
    }
}

// Default landing page for root domain
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scandiweb Test - Backend API</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .api-link {
            display: block;
            background: #007cba;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
            font-size: 18px;
        }
        .api-link:hover {
            background: #005a87;
        }
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .status {
            color: #28a745;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Scandiweb Test - Backend API</h1>
        
        <div class="info">
            <p><strong>Backend Status:</strong> <span class="status">✅ Online</span></p>
            <p><strong>Frontend URL:</strong> <a href="https://scandiweb-test-badrs-projects-6643e546.vercel.app/" target="_blank">https://scandiweb-test-badrs-projects-6643e546.vercel.app/</a></p>
            <p><strong>API Endpoint:</strong> <a href="/api/" target="_blank">/api/</a></p>
        </div>

        <a href="/api/" class="api-link">Access GraphQL API</a>

        <div class="info">
            <h3>📋 API Information:</h3>
            <ul>
                <li><strong>Framework:</strong> PHP 8.3 + GraphQL</li>
                <li><strong>Database:</strong> MySQL 8.0</li>
                <li><strong>Hosting:</strong> InfinityFree</li>
                <li><strong>CORS:</strong> Configured for Vercel frontend</li>
            </ul>
        </div>

        <div class="info">
            <h3>🛠️ Available Endpoints:</h3>
            <ul>
                <li><code>POST /api/</code> - GraphQL endpoint</li>
                <li><code>GET /api/</code> - GraphQL playground</li>
            </ul>
        </div>

        <p style="text-align: center; color: #666; margin-top: 30px;">
            <small>Backend deployed successfully for Scandiweb Fullstack Test</small>
        </p>
    </div>
</body>
</html> 