# Template System Design

## Overview

Templates are pre-built page layouts that define the structure, slots, and default styling for pages. AI assistants use templates as the starting point for building pages, then populate slots with widgets.

---

## Template Structure

Each template consists of:

```
phoenix/templates/{template-name}/
├── template.json      # Definition & configuration
├── template.phtml     # PHP view template
├── template.css       # Scoped styles
├── template.js        # Optional interactivity
├── preview.png        # Thumbnail for template picker
└── README.md          # Documentation
```

---

## Template Definition Schema

```json
{
  "$schema": "phoenix/schemas/template.schema.json",
  "name": "dashboard",
  "version": "1.0.0",
  "displayName": "Dashboard",
  "description": "Multi-widget dashboard with stats, charts, and activity feeds",
  "category": "analytics",
  "tags": ["stats", "charts", "monitoring"],

  "layout": {
    "type": "grid",
    "columns": 4,
    "gap": "25px",
    "responsive": {
      "tablet": { "columns": 2 },
      "mobile": { "columns": 1 }
    }
  },

  "slots": [
    {
      "id": "header",
      "type": "single",
      "position": "top",
      "span": "full",
      "accepts": ["page-header", "breadcrumbs", "search-bar"],
      "default": "page-header"
    },
    {
      "id": "stats",
      "type": "grid",
      "columns": 4,
      "position": "row-1",
      "accepts": ["stats-card", "progress-ring", "countdown"],
      "minItems": 1,
      "maxItems": 8
    },
    {
      "id": "charts",
      "type": "grid",
      "columns": 2,
      "position": "row-2",
      "accepts": ["chart-*", "data-table"],
      "minItems": 0,
      "maxItems": 4
    },
    {
      "id": "main",
      "type": "flex",
      "direction": "row",
      "position": "row-3",
      "accepts": ["*"],
      "children": [
        {
          "id": "content",
          "flex": 2,
          "accepts": ["*"]
        },
        {
          "id": "sidebar",
          "flex": 1,
          "accepts": ["activity-feed", "notification-list", "timeline", "stats-card"]
        }
      ]
    },
    {
      "id": "footer",
      "type": "single",
      "position": "bottom",
      "span": "full",
      "accepts": ["page-footer"],
      "optional": true
    }
  ],

  "theme": {
    "supports": ["dark", "light", "custom"],
    "default": "dark",
    "variables": {
      "--template-bg": "var(--bg-primary)",
      "--template-gap": "25px",
      "--template-padding": "30px"
    }
  },

  "features": {
    "particles": true,
    "grid-background": true,
    "glassmorphism": true,
    "animations": true
  },

  "data": {
    "refresh": {
      "enabled": true,
      "interval": 30,
      "method": "ajax"
    }
  }
}
```

---

## The 16 Core Templates

### 1. Dashboard
**Purpose:** Stats and metrics overview
**Slots:** header, stats (grid-4), charts (grid-2), main/sidebar, footer
**Best for:** Admin dashboards, monitoring, KPIs

### 2. Data Table
**Purpose:** Tabular data display with CRUD
**Slots:** header, filters, table, pagination, footer
**Best for:** Product lists, user management, inventory

### 3. Form Page
**Purpose:** Data entry forms
**Slots:** header, form, actions, footer
**Best for:** Registration, settings, data entry

### 4. Kanban Board
**Purpose:** Drag-and-drop task/status management
**Slots:** header, columns (dynamic), card-modal
**Best for:** Project management, pipelines, workflows

### 5. Calendar
**Purpose:** Event and scheduling view
**Slots:** header, toolbar, calendar-grid, event-modal, sidebar
**Best for:** Scheduling, bookings, events

### 6. Gallery
**Purpose:** Media grid display
**Slots:** header, filters, gallery-grid, lightbox, upload-zone
**Best for:** Image libraries, portfolios, media management

### 7. Profile
**Purpose:** User profile and settings
**Slots:** header, avatar-section, info-tabs, activity, settings-form
**Best for:** User accounts, settings pages

### 8. Auth
**Purpose:** Login/register/password flows
**Slots:** logo, form, social-auth, footer-links
**Best for:** Authentication pages

### 9. Landing
**Purpose:** Marketing and hero sections
**Slots:** nav, hero, features, testimonials, cta, footer
**Best for:** Landing pages, marketing sites

### 10. Wizard
**Purpose:** Multi-step forms
**Slots:** header, progress-bar, step-content, navigation
**Best for:** Onboarding, checkout, complex forms

### 11. Chat
**Purpose:** Messaging interface
**Slots:** header, conversation-list, chat-window, message-input
**Best for:** Support chat, messaging apps

### 12. Timeline
**Purpose:** Chronological activity/history
**Slots:** header, filters, timeline-items, detail-panel
**Best for:** Activity logs, history, audit trails

### 13. Cards Grid
**Purpose:** Card-based item display
**Slots:** header, filters, cards-grid, pagination, detail-modal
**Best for:** Products, articles, listings

### 14. Split View
**Purpose:** Master-detail layout
**Slots:** header, list-panel, detail-panel, actions
**Best for:** Email clients, file browsers, CRM

### 15. Blank
**Purpose:** Empty canvas
**Slots:** main (full page)
**Best for:** Custom layouts, special pages

### 16. Report
**Purpose:** Print-ready data reports
**Slots:** header, filters, report-body, charts, tables, footer
**Best for:** Invoices, reports, exports

---

## Template View Structure (template.phtml)

```php
<?php
/**
 * PHOENIX Template: Dashboard
 * Auto-generated template view
 */

// Template configuration is passed as $template_config
$config = $template_config ?? [];
$theme = $config['theme'] ?? 'dark';
$title = $config['title'] ?? 'Dashboard';
?>

<!-- Template Styles -->
<link rel="stylesheet" href="/phoenix/templates/dashboard/template.css">

<!-- Template Root -->
<div class="phoenix-template phoenix-dashboard theme-<?= $theme ?>"
     data-template="dashboard"
     data-config='<?= json_encode($config) ?>'>

    <!-- Slot: Header -->
    <header class="phoenix-slot" data-slot="header">
        <?php PhoenixEngine::render_slot('header', $page_id); ?>
    </header>

    <!-- Slot: Stats Row -->
    <section class="phoenix-slot phoenix-grid cols-4" data-slot="stats">
        <?php PhoenixEngine::render_slot('stats', $page_id); ?>
    </section>

    <!-- Slot: Charts Row -->
    <section class="phoenix-slot phoenix-grid cols-2" data-slot="charts">
        <?php PhoenixEngine::render_slot('charts', $page_id); ?>
    </section>

    <!-- Slot: Main Content Area -->
    <div class="phoenix-slot phoenix-flex" data-slot="main">
        <main class="phoenix-slot phoenix-content" data-slot="content">
            <?php PhoenixEngine::render_slot('content', $page_id); ?>
        </main>
        <aside class="phoenix-slot phoenix-sidebar" data-slot="sidebar">
            <?php PhoenixEngine::render_slot('sidebar', $page_id); ?>
        </aside>
    </div>

    <!-- Slot: Footer -->
    <footer class="phoenix-slot" data-slot="footer">
        <?php PhoenixEngine::render_slot('footer', $page_id); ?>
    </footer>

</div>

<!-- Template Scripts -->
<script src="/phoenix/templates/dashboard/template.js"></script>
```

---

## Slot Types

### `single`
Accepts exactly one widget.
```json
{
  "id": "header",
  "type": "single",
  "accepts": ["page-header"],
  "default": "page-header"
}
```

### `grid`
CSS Grid layout with configurable columns.
```json
{
  "id": "stats",
  "type": "grid",
  "columns": 4,
  "gap": "20px",
  "accepts": ["stats-card"]
}
```

### `flex`
Flexbox layout with configurable direction.
```json
{
  "id": "main",
  "type": "flex",
  "direction": "row",
  "gap": "25px"
}
```

### `stack`
Vertical stack of widgets.
```json
{
  "id": "sidebar",
  "type": "stack",
  "gap": "15px"
}
```

### `tabs`
Tabbed content areas.
```json
{
  "id": "content",
  "type": "tabs",
  "tabs": [
    {"id": "overview", "label": "Overview"},
    {"id": "details", "label": "Details"}
  ]
}
```

---

## Template Engine API

```php
class PhoenixEngine {

    /**
     * Load and render a template
     */
    public static function render_template(string $template, array $config): string;

    /**
     * Render a specific slot's widgets
     */
    public static function render_slot(string $slot_id, string $page_id): string;

    /**
     * Get template definition
     */
    public static function get_template(string $template): array;

    /**
     * List all available templates
     */
    public static function list_templates(): array;

    /**
     * Validate template configuration
     */
    public static function validate_template_config(string $template, array $config): bool;

    /**
     * Get available slots for a template
     */
    public static function get_slots(string $template): array;

}
```

---

## Template Registry

```json
// phoenix/templates/registry.json
{
  "version": "1.0.0",
  "templates": {
    "dashboard": {
      "path": "dashboard",
      "category": "analytics",
      "featured": true
    },
    "data-table": {
      "path": "data-table",
      "category": "data",
      "featured": true
    },
    "form-page": {
      "path": "form-page",
      "category": "forms",
      "featured": true
    }
    // ... all 16 templates
  },
  "categories": [
    {"id": "analytics", "label": "Analytics & Dashboards"},
    {"id": "data", "label": "Data Management"},
    {"id": "forms", "label": "Forms & Input"},
    {"id": "content", "label": "Content Display"},
    {"id": "communication", "label": "Communication"},
    {"id": "utility", "label": "Utility"}
  ]
}
```

---

## Creating Custom Templates

### Step 1: Create template directory
```bash
mkdir phoenix/templates/my-custom-template
```

### Step 2: Define template.json
```json
{
  "name": "my-custom-template",
  "version": "1.0.0",
  "displayName": "My Custom Template",
  "description": "A custom template for specific needs",
  "slots": [
    // Define your slots
  ]
}
```

### Step 3: Create template.phtml
```php
<div class="phoenix-template phoenix-my-custom">
  <!-- Your HTML structure with slots -->
</div>
```

### Step 4: Add to registry
```json
{
  "my-custom-template": {
    "path": "my-custom-template",
    "category": "custom"
  }
}
```

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 0.1 | 2025-12-21 | Claude + Human | Initial template system design |
