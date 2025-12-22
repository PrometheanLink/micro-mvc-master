<?php
/**
 * Admin Control Panel - PHOENIX Dashboard Builder
 *
 * This is the admin interface for controlling what appears
 * on the public-facing PHOENIX dashboard at root (/)
 */

if (!defined('micro_mvc'))
    exit();

class Admin_Model
{
    private static $configFile = 'phoenix/config/dashboard.json';

    public static function Get_Data()
    {
        // Load current dashboard config
        $config = self::loadConfig();

        return [
            'phoenix' => true,  // Use PHOENIX bypass
            'title' => 'PHOENIX Control Panel',
            'theme' => 'cyber',
            'config' => $config,
            'is_admin' => true
        ];
    }

    private static function loadConfig()
    {
        $configPath = dirname(dirname(dirname(__DIR__))) . '/' . self::$configFile;

        if (file_exists($configPath)) {
            $json = file_get_contents($configPath);
            return json_decode($json, true) ?? [];
        }

        return self::getDefaultConfig();
    }

    public static function saveConfig($config)
    {
        $configPath = dirname(dirname(dirname(__DIR__))) . '/' . self::$configFile;
        $dir = dirname($configPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));
    }

    private static function getDefaultConfig()
    {
        return [
            'title' => 'PHOENIX Dashboard',
            'subtitle' => 'AI-Powered Dashboard Builder',
            'theme' => 'cyber',
            'show_particles' => true,
            'stats' => [
                ['title' => 'Total Users', 'value' => 12847, 'icon' => 'users', 'color' => 'cyan', 'trend' => ['direction' => 'up', 'value' => '+24%']],
                ['title' => 'Revenue', 'value' => 84250, 'prefix' => '$', 'icon' => 'money', 'color' => 'green', 'trend' => ['direction' => 'up', 'value' => '+18%']],
                ['title' => 'Orders', 'value' => 3429, 'icon' => 'cart', 'color' => 'purple', 'trend' => ['direction' => 'up', 'value' => '+12%']],
                ['title' => 'Conversion', 'value' => 4.8, 'suffix' => '%', 'icon' => 'target', 'color' => 'pink', 'trend' => ['direction' => 'down', 'value' => '-2%']]
            ]
        ];
    }
}
?>
