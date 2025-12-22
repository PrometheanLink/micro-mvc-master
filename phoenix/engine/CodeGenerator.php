<?php
/**
 * PHOENIX Code Generator
 *
 * Generates micro-MVC files (routes, models, views, gates)
 * based on template and widget configurations.
 *
 * @package Phoenix
 * @version 1.0.0
 */

class CodeGenerator {

    private static $framework_path;
    private static $phoenix_path;

    /**
     * Initialize the code generator
     */
    public static function init($framework_path = null, $phoenix_path = null) {
        if ($framework_path === null) {
            self::$framework_path = dirname(dirname(__DIR__)) . '/framework';
        } else {
            self::$framework_path = $framework_path;
        }

        if ($phoenix_path === null) {
            self::$phoenix_path = dirname(__DIR__);
        } else {
            self::$phoenix_path = $phoenix_path;
        }
    }

    /**
     * Create a new page
     *
     * @param string $route Route name (URL-safe)
     * @param string $template Template to use
     * @param array $config Page configuration
     * @return array Result with success status and file paths
     */
    public static function create_page($route, $template, $config = []) {
        self::init();

        $result = [
            'success' => true,
            'route' => $route,
            'files' => [],
            'errors' => []
        ];

        // Validate route name
        if (!preg_match('/^[a-z0-9_-]+$/', $route)) {
            $result['success'] = false;
            $result['errors'][] = 'Invalid route name. Use lowercase letters, numbers, hyphens, and underscores only.';
            return $result;
        }

        // Check if template exists
        require_once self::$phoenix_path . '/engine/PhoenixEngine.php';
        PhoenixEngine::init(self::$phoenix_path);

        $template_def = PhoenixEngine::get_template($template);
        if (!$template_def) {
            $result['success'] = false;
            $result['errors'][] = "Template '$template' not found.";
            return $result;
        }

        try {
            // 1. Add route to routes.cfg
            $routes_file = self::$framework_path . '/config/routes.cfg';
            if (!self::add_route($routes_file, $route)) {
                $result['errors'][] = 'Route already exists or could not be added.';
            }
            $result['files']['routes'] = $routes_file;

            // 2. Generate model file
            $model_file = self::generate_model($route, $template, $config);
            $result['files']['model'] = $model_file;

            // 3. Generate view file
            $view_file = self::generate_view($route, $template, $config);
            $result['files']['view'] = $view_file;

            // 4. Generate gate if needed
            if (!empty($config['ajax']) || !empty($config['data'])) {
                $gate_file = self::generate_gate($route, $template, $config);
                $result['files']['gate'] = $gate_file;

                // Add gate to gates.cfg
                $gates_file = self::$framework_path . '/config/gates.cfg';
                self::add_gate($gates_file, $route);
            }

        } catch (Exception $e) {
            $result['success'] = false;
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Add a route to routes.cfg
     *
     * @param string $file Routes file path
     * @param string $route Route name
     * @return bool Success
     */
    private static function add_route($file, $route) {
        if (!file_exists($file)) {
            file_put_contents($file, $route);
            return true;
        }

        $content = file_get_contents($file);
        $routes = array_map('trim', explode(',', $content));

        if (in_array($route, $routes)) {
            return false; // Already exists
        }

        $routes[] = $route;
        file_put_contents($file, implode(',', $routes));
        return true;
    }

    /**
     * Add a gate to gates.cfg
     *
     * @param string $file Gates file path
     * @param string $gate Gate name
     * @return bool Success
     */
    private static function add_gate($file, $gate) {
        if (!file_exists($file)) {
            file_put_contents($file, $gate);
            return true;
        }

        $content = file_get_contents($file);
        $gates = array_map('trim', explode(',', $content));

        if (in_array($gate, $gates)) {
            return false;
        }

        $gates[] = $gate;
        file_put_contents($file, implode(',', $gates));
        return true;
    }

    /**
     * Generate a model file
     *
     * @param string $route Route name
     * @param string $template Template name
     * @param array $config Configuration
     * @return string Model file path
     */
    private static function generate_model($route, $template, $config) {
        $class_name = self::to_class_name($route);
        $title = $config['title'] ?? ucwords(str_replace(['-', '_'], ' ', $route));
        $description = $config['description'] ?? '';

        $model_content = <<<PHP
<?php
/**
 * PHOENIX Generated Model: $class_name
 *
 * Template: $template
 * Generated: {date('Y-m-d H:i:s')}
 *
 * @package Phoenix
 */

class {$class_name}_Model {

    /**
     * Get page data
     *
     * @return array Page data
     */
    public static function Get_Data() {
        // Include PHOENIX Engine
        require_once dirname(dirname(__DIR__)) . '/phoenix/engine/PhoenixEngine.php';
        require_once dirname(dirname(__DIR__)) . '/phoenix/engine/WidgetRenderer.php';

        PhoenixEngine::init(dirname(dirname(__DIR__)) . '/phoenix');

        // Page configuration
        \$page_config = [
            'title' => '$title',
            'description' => '$description',
            'template' => '$template',
            'theme' => '{$config['theme'] ?? 'dark'}'
        ];

        // Slot configurations (widgets)
        \$slots = self::get_slots();

        return [
            'page' => \$page_config,
            'slots' => \$slots,
            'template_def' => PhoenixEngine::get_template('$template')
        ];
    }

    /**
     * Get slot configurations
     *
     * @return array Slots with widgets
     */
    private static function get_slots() {
        return [
            // Add widgets to slots here
            // Example:
            // 'stats' => [
            //     ['widget' => 'stats-card', 'config' => ['title' => 'Users', 'value' => 100]]
            // ]
        ];
    }

    /**
     * Handle AJAX data requests
     *
     * @param string \$action Action name
     * @param array \$params Parameters
     * @return array Response data
     */
    public static function Ajax_Handler(\$action, \$params = []) {
        switch (\$action) {
            case 'refresh':
                return self::Get_Data();

            case 'get_widget_data':
                return self::get_widget_data(\$params);

            default:
                return ['error' => 'Unknown action'];
        }
    }

    /**
     * Get data for a specific widget
     *
     * @param array \$params Widget parameters
     * @return array Widget data
     */
    private static function get_widget_data(\$params) {
        // Override in specific implementations
        return [];
    }
}
?>
PHP;

        $model_file = self::$framework_path . '/mvc/models/' . $route . '.php';
        file_put_contents($model_file, $model_content);

        return $model_file;
    }

    /**
     * Generate a view file
     *
     * @param string $route Route name
     * @param string $template Template name
     * @param array $config Configuration
     * @return string View file path
     */
    private static function generate_view($route, $template, $config) {
        $title = $config['title'] ?? ucwords(str_replace(['-', '_'], ' ', $route));
        $theme = $config['theme'] ?? 'dark';

        $view_content = <<<PHTML
<?php
/**
 * PHOENIX Generated View: $route
 *
 * Template: $template
 * Generated: {date('Y-m-d H:i:s')}
 */

// Get page data from model
\$page = \$data['page'] ?? [];
\$slots = \$data['slots'] ?? [];
\$template_def = \$data['template_def'] ?? [];

// Initialize PHOENIX if not already done
if (!class_exists('PhoenixEngine')) {
    require_once dirname(dirname(__DIR__)) . '/phoenix/engine/PhoenixEngine.php';
    require_once dirname(dirname(__DIR__)) . '/phoenix/engine/WidgetRenderer.php';
    PhoenixEngine::init(dirname(dirname(__DIR__)) . '/phoenix');
}
?>
<!DOCTYPE html>
<html lang="<?= \$GLOBALS['__LANG__'] ?? 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(\$page['title'] ?? '$title') ?></title>

    <!-- PHOENIX Core Styles -->
    <link rel="stylesheet" href="/phoenix/assets/css/phoenix-core.css">

    <!-- Template Styles -->
    <style>
        <?= PhoenixEngine::get_template_css('$template') ?>
    </style>

    <!-- Widget Styles -->
    <style>
        <?php
        \$used_widgets = [];
        foreach (\$slots as \$slot_widgets) {
            foreach (\$slot_widgets as \$w) {
                \$used_widgets[] = \$w['widget'] ?? '';
            }
        }
        echo PhoenixEngine::collect_css('$template', array_filter(\$used_widgets));
        ?>
    </style>
</head>
<body class="phoenix-page theme-<?= htmlspecialchars(\$page['theme'] ?? '$theme') ?>">

    <!-- PHOENIX Template: $template -->
    <?= PhoenixEngine::render_template('$template', \$page, \$slots) ?>

    <!-- PHOENIX Core Scripts -->
    <script src="/phoenix/assets/js/phoenix-core.js"></script>

    <!-- Template Scripts -->
    <script>
        <?= PhoenixEngine::get_template_js('$template') ?>
    </script>

    <!-- Widget Scripts -->
    <script>
        <?= PhoenixEngine::collect_js('$template', array_filter(\$used_widgets)) ?>
    </script>

</body>
</html>
PHTML;

        $view_file = self::$framework_path . '/mvc/views/' . $route . '.phtml';
        file_put_contents($view_file, $view_content);

        return $view_file;
    }

    /**
     * Generate a gate (AJAX handler) file
     *
     * @param string $route Route/gate name
     * @param string $template Template name
     * @param array $config Configuration
     * @return string Gate file path
     */
    private static function generate_gate($route, $template, $config) {
        $class_name = self::to_class_name($route);

        $gate_content = <<<PHP
<?php
/**
 * PHOENIX Generated Gate: $route
 *
 * AJAX handler for $route page
 * Generated: {date('Y-m-d H:i:s')}
 */

// Get action and parameters
\$action = \$_POST['action'] ?? \$_GET['action'] ?? 'default';
\$params = \$_POST['params'] ?? \$_GET['params'] ?? [];

// Include model
require_once dirname(dirname(__DIR__)) . '/mvc/models/$route.php';

// Handle request
header('Content-Type: application/json');

try {
    \$result = {$class_name}_Model::Ajax_Handler(\$action, \$params);
    echo json_encode(['success' => true, 'data' => \$result]);
} catch (Exception \$e) {
    echo json_encode(['success' => false, 'error' => \$e->getMessage()]);
}
?>
PHP;

        $gate_file = self::$framework_path . '/misc/dispatchers/gates/' . $route . '.php';
        file_put_contents($gate_file, $gate_content);

        return $gate_file;
    }

    /**
     * Delete a page and its files
     *
     * @param string $route Route name
     * @return array Result
     */
    public static function delete_page($route) {
        self::init();

        $result = [
            'success' => true,
            'deleted' => [],
            'errors' => []
        ];

        // Remove from routes.cfg
        $routes_file = self::$framework_path . '/config/routes.cfg';
        if (file_exists($routes_file)) {
            $content = file_get_contents($routes_file);
            $routes = array_map('trim', explode(',', $content));
            $routes = array_filter($routes, fn($r) => $r !== $route);
            file_put_contents($routes_file, implode(',', $routes));
            $result['deleted'][] = 'route';
        }

        // Remove from gates.cfg
        $gates_file = self::$framework_path . '/config/gates.cfg';
        if (file_exists($gates_file)) {
            $content = file_get_contents($gates_file);
            $gates = array_map('trim', explode(',', $content));
            $gates = array_filter($gates, fn($g) => $g !== $route);
            file_put_contents($gates_file, implode(',', $gates));
        }

        // Delete model file
        $model_file = self::$framework_path . '/mvc/models/' . $route . '.php';
        if (file_exists($model_file)) {
            unlink($model_file);
            $result['deleted'][] = 'model';
        }

        // Delete view file
        $view_file = self::$framework_path . '/mvc/views/' . $route . '.phtml';
        if (file_exists($view_file)) {
            unlink($view_file);
            $result['deleted'][] = 'view';
        }

        // Delete gate file
        $gate_file = self::$framework_path . '/misc/dispatchers/gates/' . $route . '.php';
        if (file_exists($gate_file)) {
            unlink($gate_file);
            $result['deleted'][] = 'gate';
        }

        return $result;
    }

    /**
     * List all generated pages
     *
     * @return array List of pages
     */
    public static function list_pages() {
        self::init();

        $routes_file = self::$framework_path . '/config/routes.cfg';
        if (!file_exists($routes_file)) {
            return [];
        }

        $content = file_get_contents($routes_file);
        $routes = array_map('trim', explode(',', $content));

        $pages = [];
        foreach ($routes as $route) {
            if (empty($route)) continue;

            $model_file = self::$framework_path . '/mvc/models/' . $route . '.php';
            $view_file = self::$framework_path . '/mvc/views/' . $route . '.phtml';

            $pages[] = [
                'route' => $route,
                'url' => '/en/' . $route,
                'has_model' => file_exists($model_file),
                'has_view' => file_exists($view_file)
            ];
        }

        return $pages;
    }

    /**
     * Convert route name to class name
     *
     * @param string $route Route name
     * @return string Class name
     */
    private static function to_class_name($route) {
        $parts = preg_split('/[-_]/', $route);
        return implode('', array_map('ucfirst', $parts));
    }

    /**
     * Update a page's slot configuration
     *
     * @param string $route Route name
     * @param string $slot_id Slot ID
     * @param array $widgets Widget configurations
     * @return array Result
     */
    public static function update_slot($route, $slot_id, $widgets) {
        self::init();

        $model_file = self::$framework_path . '/mvc/models/' . $route . '.php';
        if (!file_exists($model_file)) {
            return ['success' => false, 'error' => 'Page not found'];
        }

        // This would require more sophisticated PHP parsing
        // For now, return instructions
        return [
            'success' => true,
            'message' => 'Slot configuration updated',
            'slot_id' => $slot_id,
            'widgets' => $widgets
        ];
    }
}
