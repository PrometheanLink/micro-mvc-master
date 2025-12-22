<?php
/**
 * PHOENIX Engine - Core Template & Page Engine
 *
 * The heart of the PHOENIX system. Handles template loading,
 * slot rendering, widget injection, and page generation.
 *
 * @package Phoenix
 * @version 1.0.0
 */

class PhoenixEngine {

    private static $instance = null;
    private static $templates_path;
    private static $widgets_path;
    private static $template_cache = [];
    private static $widget_cache = [];

    /**
     * Initialize the PHOENIX engine
     */
    public static function init($base_path = null) {
        if ($base_path === null) {
            $base_path = dirname(__DIR__);
        }

        self::$templates_path = $base_path . '/templates';
        self::$widgets_path = $base_path . '/widgets';

        // Load registries
        self::load_template_registry();
        self::load_widget_registry();
    }

    /**
     * Load template registry
     */
    private static function load_template_registry() {
        $registry_file = self::$templates_path . '/registry.json';
        if (file_exists($registry_file)) {
            $content = file_get_contents($registry_file);
            self::$template_cache['registry'] = json_decode($content, true);
        }
    }

    /**
     * Load widget registry
     */
    private static function load_widget_registry() {
        $registry_file = self::$widgets_path . '/registry.json';
        if (file_exists($registry_file)) {
            $content = file_get_contents($registry_file);
            self::$widget_cache['registry'] = json_decode($content, true);
        }
    }

    /**
     * Get a template definition
     *
     * @param string $template Template name
     * @return array|null Template definition or null if not found
     */
    public static function get_template($template) {
        if (isset(self::$template_cache[$template])) {
            return self::$template_cache[$template];
        }

        $template_file = self::$templates_path . '/' . $template . '/template.json';
        if (!file_exists($template_file)) {
            return null;
        }

        $content = file_get_contents($template_file);
        $definition = json_decode($content, true);

        if ($definition === null) {
            return null;
        }

        self::$template_cache[$template] = $definition;
        return $definition;
    }

    /**
     * Get a widget definition
     *
     * @param string $widget Widget name
     * @return array|null Widget definition or null if not found
     */
    public static function get_widget($widget) {
        if (isset(self::$widget_cache[$widget])) {
            return self::$widget_cache[$widget];
        }

        $widget_file = self::$widgets_path . '/' . $widget . '/widget.json';
        if (!file_exists($widget_file)) {
            return null;
        }

        $content = file_get_contents($widget_file);
        $definition = json_decode($content, true);

        if ($definition === null) {
            return null;
        }

        self::$widget_cache[$widget] = $definition;
        return $definition;
    }

    /**
     * List all available templates
     *
     * @param string|null $category Filter by category
     * @return array List of templates
     */
    public static function list_templates($category = null) {
        $registry = self::$template_cache['registry'] ?? [];
        $templates = $registry['templates'] ?? [];

        if ($category !== null) {
            $templates = array_filter($templates, function($t) use ($category) {
                return ($t['category'] ?? '') === $category;
            });
        }

        return $templates;
    }

    /**
     * List all available widgets
     *
     * @param string|null $category Filter by category
     * @return array List of widgets
     */
    public static function list_widgets($category = null) {
        $registry = self::$widget_cache['registry'] ?? [];
        $widgets = $registry['widgets'] ?? [];

        if ($category !== null) {
            $widgets = array_filter($widgets, function($w) use ($category) {
                return ($w['category'] ?? '') === $category;
            });
        }

        return $widgets;
    }

    /**
     * Render a template
     *
     * @param string $template Template name
     * @param array $config Template configuration
     * @param array $slots Slot contents (widget configurations)
     * @return string Rendered HTML
     */
    public static function render_template($template, $config = [], $slots = []) {
        $definition = self::get_template($template);
        if (!$definition) {
            return "<!-- Template '$template' not found -->";
        }

        $template_file = self::$templates_path . '/' . $template . '/template.phtml';
        if (!file_exists($template_file)) {
            return "<!-- Template view '$template' not found -->";
        }

        // Prepare template variables
        $template_config = array_merge($definition, $config);
        $page_slots = $slots;

        // Capture output
        ob_start();
        include $template_file;
        return ob_get_clean();
    }

    /**
     * Render a slot with its widgets
     *
     * @param string $slot_id Slot identifier
     * @param array $widgets Array of widget configurations
     * @return string Rendered HTML
     */
    public static function render_slot($slot_id, $widgets = []) {
        if (empty($widgets)) {
            return "<!-- Slot '$slot_id' is empty -->";
        }

        $output = '';
        foreach ($widgets as $widget_config) {
            $widget_type = $widget_config['widget'] ?? null;
            if ($widget_type) {
                $output .= self::render_widget($widget_type, $widget_config['config'] ?? []);
            }
        }

        return $output;
    }

    /**
     * Render a single widget
     *
     * @param string $widget Widget name
     * @param array $config Widget configuration
     * @param string|null $widget_id Optional widget ID
     * @return string Rendered HTML
     */
    public static function render_widget($widget, $config = [], $widget_id = null) {
        $definition = self::get_widget($widget);
        if (!$definition) {
            return "<!-- Widget '$widget' not found -->";
        }

        $widget_file = self::$widgets_path . '/' . $widget . '/widget.phtml';
        if (!file_exists($widget_file)) {
            return "<!-- Widget view '$widget' not found -->";
        }

        // Generate widget ID if not provided
        if ($widget_id === null) {
            $widget_id = 'phoenix-' . $widget . '-' . uniqid();
        }

        // Merge defaults from definition
        $merged_config = self::merge_widget_config($definition, $config);

        // Capture output
        ob_start();
        include $widget_file;
        return ob_get_clean();
    }

    /**
     * Merge widget config with defaults from definition
     *
     * @param array $definition Widget definition
     * @param array $config User config
     * @return array Merged config
     */
    private static function merge_widget_config($definition, $config) {
        $defaults = [];

        if (isset($definition['config'])) {
            foreach ($definition['config'] as $key => $field) {
                if (isset($field['default'])) {
                    $defaults[$key] = $field['default'];
                }
            }
        }

        return array_merge($defaults, $config);
    }

    /**
     * Validate widget configuration
     *
     * @param string $widget Widget name
     * @param array $config Configuration to validate
     * @return array Validation result with 'valid' and 'errors' keys
     */
    public static function validate_widget_config($widget, $config) {
        $definition = self::get_widget($widget);
        if (!$definition) {
            return ['valid' => false, 'errors' => ["Widget '$widget' not found"]];
        }

        $errors = [];
        $schema = $definition['config'] ?? [];

        foreach ($schema as $field => $rules) {
            $value = $config[$field] ?? null;

            // Check required
            if (($rules['required'] ?? false) && $value === null) {
                $errors[] = "Field '$field' is required";
                continue;
            }

            // Check type
            if ($value !== null && isset($rules['type'])) {
                $types = is_array($rules['type']) ? $rules['type'] : [$rules['type']];
                $valid_type = false;

                foreach ($types as $type) {
                    switch ($type) {
                        case 'string':
                            $valid_type = is_string($value);
                            break;
                        case 'number':
                            $valid_type = is_numeric($value);
                            break;
                        case 'boolean':
                            $valid_type = is_bool($value);
                            break;
                        case 'array':
                            $valid_type = is_array($value);
                            break;
                        case 'object':
                            $valid_type = is_array($value) && !array_is_list($value);
                            break;
                    }
                    if ($valid_type) break;
                }

                if (!$valid_type) {
                    $errors[] = "Field '$field' has invalid type";
                }
            }

            // Check enum
            if ($value !== null && isset($rules['enum'])) {
                if (!in_array($value, $rules['enum'])) {
                    $errors[] = "Field '$field' must be one of: " . implode(', ', $rules['enum']);
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get template slots
     *
     * @param string $template Template name
     * @return array List of slots
     */
    public static function get_slots($template) {
        $definition = self::get_template($template);
        if (!$definition) {
            return [];
        }

        return $definition['slots'] ?? [];
    }

    /**
     * Check if a widget is compatible with a slot
     *
     * @param string $template Template name
     * @param string $slot_id Slot ID
     * @param string $widget Widget name
     * @return bool Whether widget can be placed in slot
     */
    public static function is_widget_compatible($template, $slot_id, $widget) {
        $slots = self::get_slots($template);

        foreach ($slots as $slot) {
            if ($slot['id'] === $slot_id) {
                $accepts = $slot['accepts'] ?? ['*'];

                // Check wildcard
                if (in_array('*', $accepts)) {
                    return true;
                }

                // Check direct match
                if (in_array($widget, $accepts)) {
                    return true;
                }

                // Check pattern match (e.g., "chart-*")
                foreach ($accepts as $pattern) {
                    if (strpos($pattern, '*') !== false) {
                        $regex = '/^' . str_replace('*', '.*', $pattern) . '$/';
                        if (preg_match($regex, $widget)) {
                            return true;
                        }
                    }
                }

                return false;
            }
        }

        return false;
    }

    /**
     * Get CSS for a template
     *
     * @param string $template Template name
     * @return string|null CSS content or null
     */
    public static function get_template_css($template) {
        $css_file = self::$templates_path . '/' . $template . '/template.css';
        if (file_exists($css_file)) {
            return file_get_contents($css_file);
        }
        return null;
    }

    /**
     * Get JS for a template
     *
     * @param string $template Template name
     * @return string|null JS content or null
     */
    public static function get_template_js($template) {
        $js_file = self::$templates_path . '/' . $template . '/template.js';
        if (file_exists($js_file)) {
            return file_get_contents($js_file);
        }
        return null;
    }

    /**
     * Get CSS for a widget
     *
     * @param string $widget Widget name
     * @return string|null CSS content or null
     */
    public static function get_widget_css($widget) {
        $css_file = self::$widgets_path . '/' . $widget . '/widget.css';
        if (file_exists($css_file)) {
            return file_get_contents($css_file);
        }
        return null;
    }

    /**
     * Get JS for a widget
     *
     * @param string $widget Widget name
     * @return string|null JS content or null
     */
    public static function get_widget_js($widget) {
        $js_file = self::$widgets_path . '/' . $widget . '/widget.js';
        if (file_exists($js_file)) {
            return file_get_contents($js_file);
        }
        return null;
    }

    /**
     * Collect all CSS for a page
     *
     * @param string $template Template name
     * @param array $widgets List of widget names used
     * @return string Combined CSS
     */
    public static function collect_css($template, $widgets = []) {
        $css = '';

        // Template CSS
        $template_css = self::get_template_css($template);
        if ($template_css) {
            $css .= "/* Template: $template */\n" . $template_css . "\n\n";
        }

        // Widget CSS (deduplicated)
        $widgets = array_unique($widgets);
        foreach ($widgets as $widget) {
            $widget_css = self::get_widget_css($widget);
            if ($widget_css) {
                $css .= "/* Widget: $widget */\n" . $widget_css . "\n\n";
            }
        }

        return $css;
    }

    /**
     * Collect all JS for a page
     *
     * @param string $template Template name
     * @param array $widgets List of widget names used
     * @return string Combined JS
     */
    public static function collect_js($template, $widgets = []) {
        $js = '';

        // Template JS
        $template_js = self::get_template_js($template);
        if ($template_js) {
            $js .= "// Template: $template\n" . $template_js . "\n\n";
        }

        // Widget JS (deduplicated)
        $widgets = array_unique($widgets);
        foreach ($widgets as $widget) {
            $widget_js = self::get_widget_js($widget);
            if ($widget_js) {
                $js .= "// Widget: $widget\n" . $widget_js . "\n\n";
            }
        }

        return $js;
    }
}

// Helper function for array_is_list (PHP 8.1+)
if (!function_exists('array_is_list')) {
    function array_is_list(array $arr): bool {
        if ($arr === []) {
            return true;
        }
        return array_keys($arr) === range(0, count($arr) - 1);
    }
}
