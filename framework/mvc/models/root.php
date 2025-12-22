<?php
/**
 * Root (Home) - PHOENIX Showcase Dashboard
 *
 * This is the main public-facing dashboard that can be controlled
 * from the admin panel at /admin/
 */

// Check for direct access
if (!defined('micro_mvc'))
    exit();

class ROOT_MODEL
{
    // Config file for dynamic dashboard settings
    private static $configFile = 'phoenix/config/dashboard.json';

    public static function Get_Data()
    {
        // Load config if exists, otherwise use defaults
        $config = self::loadConfig();

        return [
            'phoenix' => true,
            'title' => $config['title'] ?? 'PHOENIX Dashboard',
            'subtitle' => $config['subtitle'] ?? 'AI-Powered Dashboard Builder',
            'theme' => $config['theme'] ?? 'cyber',
            'show_particles' => $config['show_particles'] ?? true,

            // Widgets configuration (editable from admin)
            'widgets' => $config['widgets'] ?? self::getDefaultWidgets(),

            // Stats
            'stats' => $config['stats'] ?? [
                ['title' => 'Total Users', 'value' => 12847, 'icon' => 'users', 'color' => 'cyan', 'trend' => ['direction' => 'up', 'value' => '+24%']],
                ['title' => 'Revenue', 'value' => 84250, 'prefix' => '$', 'icon' => 'money', 'color' => 'green', 'trend' => ['direction' => 'up', 'value' => '+18%']],
                ['title' => 'Orders', 'value' => 3429, 'icon' => 'cart', 'color' => 'purple', 'trend' => ['direction' => 'up', 'value' => '+12%']],
                ['title' => 'Conversion', 'value' => 4.8, 'suffix' => '%', 'icon' => 'target', 'color' => 'pink', 'trend' => ['direction' => 'down', 'value' => '-2%']]
            ],

            // Chart
            'chart' => $config['chart'] ?? [
                'title' => 'Performance',
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'datasets' => [
                    ['label' => 'Sales', 'data' => [12, 19, 15, 25, 22, 30], 'color' => '#00d4ff'],
                    ['label' => 'Orders', 'data' => [8, 15, 12, 18, 16, 24], 'color' => '#7b2cbf']
                ]
            ],

            // Progress
            'progress' => $config['progress'] ?? [
                ['label' => 'Storage', 'value' => 72, 'color' => 'cyan'],
                ['label' => 'Bandwidth', 'value' => 45, 'color' => 'green'],
                ['label' => 'CPU', 'value' => 88, 'color' => 'orange'],
                ['label' => 'Memory', 'value' => 61, 'color' => 'purple']
            ],

            // Activities
            'activities' => $config['activities'] ?? [
                ['user' => 'System', 'action' => 'Dashboard loaded', 'target' => 'PHOENIX', 'time' => 'Just now', 'icon' => '🔥'],
                ['user' => 'Admin', 'action' => 'Configuration updated', 'target' => 'Theme', 'time' => '5 min ago', 'icon' => '⚙️'],
                ['user' => 'AI', 'action' => 'Generated widget', 'target' => 'Stats Card', 'time' => '1 hour ago', 'icon' => '🤖']
            ]
        ];
    }

    private static function loadConfig()
    {
        $configPath = dirname(dirname(dirname(__DIR__))) . '/' . self::$configFile;

        if (file_exists($configPath)) {
            $json = file_get_contents($configPath);
            return json_decode($json, true) ?? [];
        }

        return [];
    }

    private static function getDefaultWidgets()
    {
        return [
            'stats' => ['enabled' => true, 'position' => 1],
            'chart' => ['enabled' => true, 'position' => 2],
            'progress' => ['enabled' => true, 'position' => 3],
            'activities' => ['enabled' => true, 'position' => 4]
        ];
    }
}
?>
