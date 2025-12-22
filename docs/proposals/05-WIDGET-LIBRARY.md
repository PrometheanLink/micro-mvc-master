# Widget Library Specification

## Overview

Widgets are the building blocks that populate template slots. Each widget is a self-contained, configurable UI component with its own styling, behavior, and optional data binding.

---

## Widget Structure

```
phoenix/widgets/{widget-name}/
├── widget.json       # Definition & configuration schema
├── widget.phtml      # PHP view
├── widget.css        # Scoped styles
├── widget.js         # Interactivity
└── preview.png       # Thumbnail
```

---

## Widget Definition Schema

```json
{
  "$schema": "phoenix/schemas/widget.schema.json",
  "name": "stats-card",
  "version": "1.0.0",
  "displayName": "Stats Card",
  "description": "Displays a single statistic with icon, value, and optional trend",
  "category": "display",
  "tags": ["stats", "metrics", "kpi"],

  "config": {
    "title": {
      "type": "string",
      "required": true,
      "description": "Card title"
    },
    "value": {
      "type": ["string", "number"],
      "required": true,
      "description": "Main value to display"
    },
    "icon": {
      "type": "string",
      "default": "chart",
      "description": "Icon name or emoji"
    },
    "color": {
      "type": "string",
      "enum": ["cyan", "purple", "pink", "green", "orange", "red"],
      "default": "cyan"
    },
    "prefix": {
      "type": "string",
      "description": "Value prefix (e.g., $)"
    },
    "suffix": {
      "type": "string",
      "description": "Value suffix (e.g., %)"
    },
    "trend": {
      "type": "object",
      "properties": {
        "direction": {
          "type": "string",
          "enum": ["up", "down", "flat"]
        },
        "value": {
          "type": "string",
          "description": "Trend text (e.g., '+12%')"
        },
        "period": {
          "type": "string",
          "description": "Time period (e.g., 'vs last week')"
        }
      }
    },
    "link": {
      "type": "string",
      "description": "URL when card is clicked"
    }
  },

  "data": {
    "static": {
      "description": "Static value provided in config"
    },
    "query": {
      "type": "object",
      "properties": {
        "table": { "type": "string" },
        "operation": { "type": "string", "enum": ["count", "sum", "avg"] },
        "column": { "type": "string" },
        "where": { "type": "object" }
      }
    },
    "api": {
      "type": "object",
      "properties": {
        "url": { "type": "string" },
        "method": { "type": "string" },
        "path": { "type": "string", "description": "JSON path to value" }
      }
    }
  },

  "refresh": {
    "enabled": true,
    "interval": 30,
    "animation": "fade"
  },

  "slots": {
    "compatible": ["stats", "main", "sidebar"],
    "size": {
      "min": { "columns": 1 },
      "max": { "columns": 2 },
      "default": { "columns": 1 }
    }
  }
}
```

---

## Widget Categories & Inventory

### Display Widgets (15)

| Widget | Description | Config Highlights |
|--------|-------------|-------------------|
| `stats-card` | Single stat with trend | value, icon, color, trend |
| `chart-bar` | Bar chart | data, labels, colors, stacked |
| `chart-line` | Line chart | data, labels, smooth, fill |
| `chart-pie` | Pie chart | data, labels, donut |
| `chart-donut` | Donut chart | data, labels, centerText |
| `chart-area` | Area chart | data, labels, gradient |
| `progress-bar` | Horizontal progress | value, max, color, label |
| `progress-ring` | Circular progress | value, max, size, color |
| `alert-box` | Alert/notice box | type, title, message, dismissible |
| `info-card` | Information card | title, content, icon, actions |
| `timeline` | Vertical timeline | items, alternating, icons |
| `activity-feed` | Activity list | items, avatars, timestamps |
| `notification-list` | Notifications | items, unread, actions |
| `countdown` | Countdown timer | target, format, onComplete |
| `clock` | Live clock | format, timezone, showDate |

### Data Widgets (10)

| Widget | Description | Config Highlights |
|--------|-------------|-------------------|
| `data-table` | Sortable table | columns, data, sortable, searchable |
| `crud-table` | Full CRUD table | table, columns, actions, forms |
| `tree-view` | Hierarchical tree | items, expandable, selectable |
| `list-group` | Grouped list | items, groups, badges |
| `pagination` | Page navigation | total, perPage, current |
| `search-bar` | Search input | placeholder, instant, filters |
| `filter-panel` | Filter controls | filters, applied, onFilter |
| `sort-controls` | Sort dropdown | options, current, onSort |
| `export-button` | Export data | formats, filename, data |
| `import-button` | Import data | formats, mapping, onImport |

### Input Widgets (20)

| Widget | Description | Config Highlights |
|--------|-------------|-------------------|
| `text-input` | Text field | placeholder, validation, prefix |
| `textarea` | Multi-line text | rows, maxLength, counter |
| `select` | Dropdown select | options, placeholder, searchable |
| `multi-select` | Multi-select | options, tags, max |
| `checkbox` | Checkbox | label, checked, indeterminate |
| `checkbox-group` | Checkbox list | options, columns, selectAll |
| `radio-group` | Radio buttons | options, columns |
| `toggle-switch` | Toggle switch | label, onLabel, offLabel |
| `date-picker` | Date input | format, min, max, range |
| `time-picker` | Time input | format, step, min, max |
| `datetime-picker` | DateTime input | format, min, max |
| `date-range` | Date range | format, presets |
| `file-upload` | File input | accept, maxSize, multiple |
| `image-upload` | Image upload | accept, preview, crop |
| `rich-text-editor` | WYSIWYG | toolbar, placeholder |
| `code-editor` | Code input | language, theme, lineNumbers |
| `color-picker` | Color input | format, swatches, alpha |
| `slider` | Range slider | min, max, step, range |
| `rating` | Star rating | max, allowHalf, readonly |
| `tags-input` | Tags input | suggestions, max, validation |

### Media Widgets (8)

| Widget | Description | Config Highlights |
|--------|-------------|-------------------|
| `image` | Image display | src, alt, lazy, lightbox |
| `image-gallery` | Image grid | images, columns, lightbox |
| `video-player` | Video player | src, poster, controls |
| `audio-player` | Audio player | src, playlist, visualizer |
| `webcam` | Webcam capture | resolution, mirror, onCapture |
| `screen-capture` | Screen recording | audio, onStop |
| `file-manager` | File browser | root, allowUpload, actions |
| `pdf-viewer` | PDF display | src, toolbar, zoom |

### Layout Widgets (12)

| Widget | Description | Config Highlights |
|--------|-------------|-------------------|
| `card` | Container card | title, actions, collapsible |
| `accordion` | Collapsible sections | items, multiple, icons |
| `tabs` | Tabbed content | tabs, orientation, lazy |
| `modal` | Modal dialog | title, size, closable |
| `drawer` | Slide-out panel | position, size, overlay |
| `popover` | Popup content | trigger, position, arrow |
| `tooltip` | Hover tooltip | content, position, delay |
| `breadcrumbs` | Navigation path | items, separator |
| `page-header` | Page title bar | title, subtitle, actions |
| `page-footer` | Page footer | content, sticky |
| `divider` | Visual divider | orientation, text |
| `spacer` | Empty space | height |

---

## Widget View Template (widget.phtml)

```php
<?php
/**
 * PHOENIX Widget: Stats Card
 *
 * @param array $config Widget configuration
 * @param string $widget_id Unique widget instance ID
 */

$title = $config['title'] ?? 'Statistic';
$value = $config['value'] ?? '0';
$icon = $config['icon'] ?? '📊';
$color = $config['color'] ?? 'cyan';
$prefix = $config['prefix'] ?? '';
$suffix = $config['suffix'] ?? '';
$trend = $config['trend'] ?? null;
$link = $config['link'] ?? null;
?>

<div class="phoenix-widget phoenix-stats-card color-<?= $color ?>"
     id="<?= $widget_id ?>"
     data-widget="stats-card"
     data-config='<?= htmlspecialchars(json_encode($config)) ?>'>

    <?php if ($link): ?>
    <a href="<?= htmlspecialchars($link) ?>" class="widget-link">
    <?php endif; ?>

    <div class="card-header">
        <span class="card-title"><?= htmlspecialchars($title) ?></span>
        <div class="card-icon"><?= $icon ?></div>
    </div>

    <div class="card-value">
        <?php if ($prefix): ?>
        <span class="value-prefix"><?= htmlspecialchars($prefix) ?></span>
        <?php endif; ?>

        <span class="value-number" data-value="<?= htmlspecialchars($value) ?>">
            <?= htmlspecialchars($value) ?>
        </span>

        <?php if ($suffix): ?>
        <span class="value-suffix"><?= htmlspecialchars($suffix) ?></span>
        <?php endif; ?>
    </div>

    <?php if ($trend): ?>
    <div class="card-trend trend-<?= $trend['direction'] ?? 'flat' ?>">
        <span class="trend-arrow">
            <?= ($trend['direction'] ?? 'flat') === 'up' ? '↑' : (($trend['direction'] ?? 'flat') === 'down' ? '↓' : '→') ?>
        </span>
        <span class="trend-value"><?= htmlspecialchars($trend['value'] ?? '') ?></span>
        <?php if (!empty($trend['period'])): ?>
        <span class="trend-period"><?= htmlspecialchars($trend['period']) ?></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($link): ?>
    </a>
    <?php endif; ?>

</div>
```

---

## Widget JavaScript API

```javascript
// phoenix/widgets/stats-card/widget.js

class PhoenixStatsCard extends PhoenixWidget {

    constructor(element, config) {
        super(element, config);
        this.valueElement = element.querySelector('.value-number');
    }

    // Called when widget is initialized
    init() {
        if (this.config.data?.type === 'query' || this.config.data?.type === 'api') {
            this.loadData();
        }
        if (this.config.refresh?.enabled) {
            this.startRefresh();
        }
    }

    // Load data from source
    async loadData() {
        const data = await Phoenix.fetchWidgetData(this.id, this.config.data);
        this.updateValue(data.value);
    }

    // Update displayed value with animation
    updateValue(newValue) {
        const currentValue = parseFloat(this.valueElement.textContent) || 0;
        const targetValue = parseFloat(newValue) || 0;

        this.animateValue(currentValue, targetValue, 500);
    }

    // Animate number change
    animateValue(start, end, duration) {
        const startTime = performance.now();

        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(start + (end - start) * easeOut);

            this.valueElement.textContent = current.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        requestAnimationFrame(animate);
    }

    // Start auto-refresh
    startRefresh() {
        this.refreshInterval = setInterval(() => {
            this.loadData();
        }, (this.config.refresh.interval || 30) * 1000);
    }

    // Cleanup
    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
    }
}

// Register widget
Phoenix.registerWidget('stats-card', PhoenixStatsCard);
```

---

## Widget Renderer API

```php
class WidgetRenderer {

    /**
     * Render a widget
     */
    public static function render(string $widget, array $config, string $widget_id = null): string;

    /**
     * Render multiple widgets in a slot
     */
    public static function render_slot(array $widgets): string;

    /**
     * Get widget definition
     */
    public static function get_widget(string $widget): array;

    /**
     * List all available widgets
     */
    public static function list_widgets(string $category = null): array;

    /**
     * Validate widget configuration
     */
    public static function validate_config(string $widget, array $config): array;

    /**
     * Get widget data from source
     */
    public static function fetch_data(string $widget_id, array $data_config): mixed;

}
```

---

## Data Binding

Widgets can bind to data in three ways:

### Static Data
```json
{
  "widget": "stats-card",
  "config": {
    "title": "Total Users",
    "value": 1234
  }
}
```

### Database Query
```json
{
  "widget": "stats-card",
  "config": {
    "title": "Total Users",
    "data": {
      "type": "query",
      "operation": "count",
      "table": "users",
      "where": { "status": "active" }
    }
  }
}
```

### API Endpoint
```json
{
  "widget": "stats-card",
  "config": {
    "title": "Weather",
    "data": {
      "type": "api",
      "url": "https://api.weather.com/current",
      "path": "temperature"
    }
  }
}
```

---

## Widget Registry

```json
// phoenix/widgets/registry.json
{
  "version": "1.0.0",
  "widgets": {
    "stats-card": {
      "path": "stats-card",
      "category": "display",
      "featured": true
    },
    "chart-bar": {
      "path": "chart-bar",
      "category": "display",
      "dependencies": ["chart.js"]
    }
    // ... all widgets
  },
  "categories": [
    {"id": "display", "label": "Display", "icon": "📊"},
    {"id": "data", "label": "Data", "icon": "📋"},
    {"id": "input", "label": "Input", "icon": "📝"},
    {"id": "media", "label": "Media", "icon": "🖼️"},
    {"id": "layout", "label": "Layout", "icon": "📐"}
  ]
}
```

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 0.1 | 2025-12-21 | Claude + Human | Initial widget library specification |
