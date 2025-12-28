<?php
/**
 * Architecture Guide Model
 * Provides data for the interactive architecture explorer
 */
class ArchitectureGuide_Model
{
    public static function Get_Data()
    {
        return [
            'title' => 'micro-MVC Architecture Explorer',
            'phoenix' => true,
            'routes' => self::Get_Routes(),
            'gates' => self::Get_Gates(),
            'extensions' => self::Get_Extensions()
        ];
    }

    private static function Get_Routes()
    {
        $routes_file = 'framework/config/routes.cfg';
        if (file_exists($routes_file)) {
            return explode(',', file_get_contents($routes_file));
        }
        return [];
    }

    private static function Get_Gates()
    {
        $gates_file = 'framework/config/gates.cfg';
        if (file_exists($gates_file)) {
            return explode(',', file_get_contents($gates_file));
        }
        return [];
    }

    private static function Get_Extensions()
    {
        $php_registry = 'framework/config/registry/php.json';
        $js_registry = 'framework/config/registry/js.json';

        $extensions = ['php' => [], 'js' => []];

        if (file_exists($php_registry)) {
            $extensions['php'] = json_decode(file_get_contents($php_registry), true) ?: [];
        }
        if (file_exists($js_registry)) {
            $extensions['js'] = json_decode(file_get_contents($js_registry), true) ?: [];
        }

        return $extensions;
    }
}
?>
