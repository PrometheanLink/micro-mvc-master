<?php
/**
 * PHOENIX API - Save Dashboard Config
 *
 * Saves dashboard configuration to JSON file
 */

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = file_get_contents('php://input');
$config = json_decode($input, true);

if (!$config) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit();
}

// Sanitize config values
$sanitized = [
    'title' => strip_tags($config['title'] ?? 'PHOENIX Dashboard'),
    'subtitle' => strip_tags($config['subtitle'] ?? ''),
    'theme' => in_array($config['theme'] ?? '', ['cyber', 'dark', 'midnight']) ? $config['theme'] : 'cyber',
    'show_particles' => (bool)($config['show_particles'] ?? true),
    'stats' => [],
    'chart' => $config['chart'] ?? [],
    'progress' => $config['progress'] ?? [],
    'activities' => $config['activities'] ?? []
];

// Sanitize stats
if (isset($config['stats']) && is_array($config['stats'])) {
    foreach ($config['stats'] as $stat) {
        $sanitized['stats'][] = [
            'title' => strip_tags($stat['title'] ?? 'Stat'),
            'value' => is_numeric($stat['value']) ? $stat['value'] : 0,
            'icon' => in_array($stat['icon'] ?? '', ['users', 'money', 'cart', 'target']) ? $stat['icon'] : 'users',
            'color' => in_array($stat['color'] ?? '', ['cyan', 'green', 'purple', 'pink', 'orange']) ? $stat['color'] : 'cyan',
            'prefix' => strip_tags($stat['prefix'] ?? ''),
            'suffix' => strip_tags($stat['suffix'] ?? ''),
            'trend' => $stat['trend'] ?? null
        ];
    }
}

// Save to config file
$configPath = dirname(__DIR__) . '/config/dashboard.json';
$configDir = dirname($configPath);

// Create config directory if it doesn't exist
if (!is_dir($configDir)) {
    mkdir($configDir, 0755, true);
}

// Write config
$result = file_put_contents($configPath, json_encode($sanitized, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($result === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save config']);
    exit();
}

echo json_encode([
    'success' => true,
    'message' => 'Configuration saved',
    'path' => $configPath
]);
?>
