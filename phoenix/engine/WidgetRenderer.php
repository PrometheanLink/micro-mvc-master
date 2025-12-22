<?php
/**
 * PHOENIX Widget Renderer
 *
 * Handles widget rendering, data binding, and dynamic updates.
 * Works with PhoenixEngine to render widgets within template slots.
 *
 * @package Phoenix
 * @version 1.0.0
 */

require_once __DIR__ . '/PhoenixEngine.php';

class WidgetRenderer {

    private static $data_cache = [];

    /**
     * Render a widget with full processing
     *
     * @param string $widget Widget name
     * @param array $config Widget configuration
     * @param string|null $widget_id Optional widget ID
     * @return string Rendered HTML
     */
    public static function render($widget, $config = [], $widget_id = null) {
        // Generate widget ID
        if ($widget_id === null) {
            $widget_id = 'phoenix-' . $widget . '-' . uniqid();
        }

        // Process data binding if configured
        if (isset($config['data'])) {
            $config = self::process_data_binding($config, $widget_id);
        }

        // Render through PhoenixEngine
        return PhoenixEngine::render_widget($widget, $config, $widget_id);
    }

    /**
     * Render multiple widgets in a slot
     *
     * @param array $widgets Array of widget configurations
     * @param string $slot_type Slot type (grid, flex, stack, single)
     * @param array $slot_config Slot configuration
     * @return string Rendered HTML
     */
    public static function render_slot($widgets, $slot_type = 'stack', $slot_config = []) {
        if (empty($widgets)) {
            return '';
        }

        $output = '';
        $wrapper_class = 'phoenix-slot phoenix-' . $slot_type;

        // Add slot-specific classes
        if ($slot_type === 'grid') {
            $cols = $slot_config['columns'] ?? 4;
            $wrapper_class .= ' cols-' . $cols;
        }

        $output .= '<div class="' . $wrapper_class . '">';

        foreach ($widgets as $index => $widget_config) {
            $widget_type = $widget_config['widget'] ?? null;
            if ($widget_type) {
                $config = $widget_config['config'] ?? [];
                $widget_id = $widget_config['id'] ?? null;

                // Wrap each widget
                $span = $config['span'] ?? 1;
                $output .= '<div class="phoenix-widget-wrapper" data-span="' . $span . '">';
                $output .= self::render($widget_type, $config, $widget_id);
                $output .= '</div>';
            }
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Process data binding for a widget
     *
     * @param array $config Widget configuration with data binding
     * @param string $widget_id Widget ID
     * @return array Processed configuration with data
     */
    private static function process_data_binding($config, $widget_id) {
        $data_config = $config['data'];
        $type = $data_config['type'] ?? 'static';

        switch ($type) {
            case 'query':
                $value = self::fetch_query_data($data_config);
                break;

            case 'api':
                $value = self::fetch_api_data($data_config);
                break;

            case 'static':
            default:
                $value = $data_config['value'] ?? null;
                break;
        }

        // Apply the fetched value to the config
        if ($value !== null) {
            $config['value'] = $value;
        }

        // Store refresh config for JavaScript
        if (isset($data_config['refresh']) || isset($config['refresh'])) {
            $config['_refresh'] = $data_config['refresh'] ?? $config['refresh'];
        }

        return $config;
    }

    /**
     * Fetch data from database query
     *
     * @param array $data_config Data configuration
     * @return mixed Query result
     */
    private static function fetch_query_data($data_config) {
        $operation = $data_config['operation'] ?? 'count';
        $table = $data_config['table'] ?? null;
        $column = $data_config['column'] ?? '*';
        $where = $data_config['where'] ?? [];

        if (!$table) {
            return null;
        }

        // Use DAL if available
        if (class_exists('PhoenixDAL')) {
            $dal = new PhoenixDAL();

            switch ($operation) {
                case 'count':
                    return $dal->count($table, $where);
                case 'sum':
                    return $dal->sum($table, $column, $where);
                case 'avg':
                    return $dal->avg($table, $column, $where);
                case 'min':
                    return $dal->min($table, $column, $where);
                case 'max':
                    return $dal->max($table, $column, $where);
                default:
                    return null;
            }
        }

        // Fallback: return placeholder
        return '---';
    }

    /**
     * Fetch data from API endpoint
     *
     * @param array $data_config Data configuration
     * @return mixed API result
     */
    private static function fetch_api_data($data_config) {
        $url = $data_config['url'] ?? null;
        $method = strtoupper($data_config['method'] ?? 'GET');
        $path = $data_config['path'] ?? null;
        $headers = $data_config['headers'] ?? [];

        if (!$url) {
            return null;
        }

        // Check cache
        $cache_key = md5($url . $method);
        if (isset(self::$data_cache[$cache_key])) {
            $data = self::$data_cache[$cache_key];
        } else {
            // Fetch from API
            $context = stream_context_create([
                'http' => [
                    'method' => $method,
                    'header' => self::build_headers($headers),
                    'timeout' => 10
                ]
            ]);

            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);
            self::$data_cache[$cache_key] = $data;
        }

        // Extract value using JSON path
        if ($path && $data) {
            return self::extract_json_path($data, $path);
        }

        return $data;
    }

    /**
     * Build HTTP headers string
     *
     * @param array $headers Headers array
     * @return string Headers string
     */
    private static function build_headers($headers) {
        $result = [];
        foreach ($headers as $key => $value) {
            $result[] = "$key: $value";
        }
        return implode("\r\n", $result);
    }

    /**
     * Extract value from JSON using dot notation path
     *
     * @param array $data JSON data
     * @param string $path Dot notation path
     * @return mixed Extracted value
     */
    private static function extract_json_path($data, $path) {
        $parts = explode('.', $path);
        $current = $data;

        foreach ($parts as $part) {
            if (is_array($current) && isset($current[$part])) {
                $current = $current[$part];
            } else {
                return null;
            }
        }

        return $current;
    }

    /**
     * Generate refresh script for a widget
     *
     * @param string $widget_id Widget ID
     * @param array $refresh_config Refresh configuration
     * @return string JavaScript code
     */
    public static function generate_refresh_script($widget_id, $refresh_config) {
        $interval = ($refresh_config['interval'] ?? 30) * 1000;
        $animation = $refresh_config['animation'] ?? 'fade';

        return <<<JS
(function() {
    const widget = document.getElementById('$widget_id');
    if (!widget) return;

    setInterval(async () => {
        try {
            const response = await fetch('/phoenix/api/widget/$widget_id/refresh');
            const data = await response.json();

            if (data.success && data.html) {
                if ('$animation' === 'fade') {
                    widget.style.opacity = '0';
                    setTimeout(() => {
                        widget.innerHTML = data.html;
                        widget.style.opacity = '1';
                    }, 200);
                } else {
                    widget.innerHTML = data.html;
                }
            }
        } catch (e) {
            console.error('Widget refresh failed:', e);
        }
    }, $interval);
})();
JS;
    }

    /**
     * Render a widget for AJAX response
     *
     * @param string $widget Widget name
     * @param array $config Widget configuration
     * @return array Response array with success and html
     */
    public static function ajax_render($widget, $config = []) {
        try {
            $html = self::render($widget, $config);
            return [
                'success' => true,
                'html' => $html
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all CSS for used widgets
     *
     * @param array $widgets List of widget names
     * @return string Combined CSS
     */
    public static function collect_widget_css($widgets) {
        $css = '';
        $widgets = array_unique($widgets);

        foreach ($widgets as $widget) {
            $widget_css = PhoenixEngine::get_widget_css($widget);
            if ($widget_css) {
                $css .= "/* Widget: $widget */\n" . $widget_css . "\n\n";
            }
        }

        return $css;
    }

    /**
     * Get all JS for used widgets
     *
     * @param array $widgets List of widget names
     * @return string Combined JS
     */
    public static function collect_widget_js($widgets) {
        $js = '';
        $widgets = array_unique($widgets);

        foreach ($widgets as $widget) {
            $widget_js = PhoenixEngine::get_widget_js($widget);
            if ($widget_js) {
                $js .= "// Widget: $widget\n" . $widget_js . "\n\n";
            }
        }

        return $js;
    }
}
