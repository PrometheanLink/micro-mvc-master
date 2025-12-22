# PHOENIX Architecture Design

## System Architecture Overview

```
                                    ┌──────────────────┐
                                    │   AI Assistant   │
                                    │ (Claude/GPT/etc) │
                                    └────────┬─────────┘
                                             │
                                             │ Natural Language
                                             ▼
┌────────────────────────────────────────────────────────────────────────┐
│                           MCP SERVER (Node.js)                          │
├────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐   │
│  │   Tool:     │  │   Tool:     │  │   Tool:     │  │   Tool:     │   │
│  │ Page Mgmt   │  │  Widgets    │  │  Database   │  │   Theme     │   │
│  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘   │
│         │                │                │                │          │
│         └────────────────┴────────────────┴────────────────┘          │
│                                   │                                    │
│                          ┌────────▼────────┐                          │
│                          │  Code Generator │                          │
│                          └────────┬────────┘                          │
│                                   │                                    │
└───────────────────────────────────┼────────────────────────────────────┘
                                    │ HTTP/File System
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                         MICRO-MVC FRAMEWORK (PHP)                       │
├────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌──────────────────────────────────────────────────────────────────┐  │
│  │                        PHOENIX ENGINE                             │  │
│  ├──────────────────────────────────────────────────────────────────┤  │
│  │                                                                   │  │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐  │  │
│  │  │  Template   │  │   Widget    │  │    Database Abstraction │  │  │
│  │  │   Engine    │  │   Renderer  │  │    Layer (DAL)          │  │  │
│  │  └─────────────┘  └─────────────┘  └─────────────────────────┘  │  │
│  │                                                                   │  │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐  │  │
│  │  │   Form      │  │   Auth &    │  │    Asset Manager        │  │  │
│  │  │   Builder   │  │   Roles     │  │    (CSS/JS/Media)       │  │  │
│  │  └─────────────┘  └─────────────┘  └─────────────────────────┘  │  │
│  │                                                                   │  │
│  └──────────────────────────────────────────────────────────────────┘  │
│                                                                         │
│  ┌──────────────────────────────────────────────────────────────────┐  │
│  │                     MICRO-MVC CORE                                │  │
│  ├──────────────────────────────────────────────────────────────────┤  │
│  │  Routes │ Controllers │ Models │ Views │ Gates │ Extensions      │  │
│  └──────────────────────────────────────────────────────────────────┘  │
│                                                                         │
└────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌────────────────────────────────────────────────────────────────────────┐
│                            DATABASE LAYER                               │
├────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐   │
│  │   MySQL     │  │  WordPress  │  │ WooCommerce │  │   Custom    │   │
│  │  (Native)   │  │  Connector  │  │  Connector  │  │  Connector  │   │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘   │
│                                                                         │
└────────────────────────────────────────────────────────────────────────┘
```

---

## Directory Structure

```
micro-MVC-master/
├── docs/
│   └── proposals/           # Design documents
│
├── phoenix/                 # NEW: PHOENIX Engine
│   ├── mcp-server/          # MCP Server (Node.js)
│   │   ├── src/
│   │   │   ├── server.ts    # MCP server entry
│   │   │   ├── tools/       # Tool implementations
│   │   │   │   ├── pages.ts
│   │   │   │   ├── widgets.ts
│   │   │   │   ├── database.ts
│   │   │   │   ├── forms.ts
│   │   │   │   └── theme.ts
│   │   │   ├── generators/  # Code generators
│   │   │   │   ├── route.ts
│   │   │   │   ├── model.ts
│   │   │   │   ├── view.ts
│   │   │   │   └── gate.ts
│   │   │   └── validators/  # Input validation
│   │   ├── package.json
│   │   └── tsconfig.json
│   │
│   ├── templates/           # Page templates
│   │   ├── registry.json    # Template registry
│   │   ├── dashboard/
│   │   │   ├── template.json
│   │   │   ├── template.phtml
│   │   │   ├── template.css
│   │   │   └── template.js
│   │   ├── data-table/
│   │   ├── form-page/
│   │   ├── kanban/
│   │   └── ... (16 templates)
│   │
│   ├── widgets/             # Widget library
│   │   ├── registry.json    # Widget registry
│   │   ├── stats-card/
│   │   │   ├── widget.json
│   │   │   ├── widget.phtml
│   │   │   ├── widget.css
│   │   │   └── widget.js
│   │   ├── data-table/
│   │   ├── chart-bar/
│   │   └── ... (40+ widgets)
│   │
│   ├── connectors/          # Database connectors
│   │   ├── mysql.php
│   │   ├── wordpress.php
│   │   └── woocommerce.php
│   │
│   └── engine/              # PHP Engine
│       ├── TemplateEngine.php
│       ├── WidgetRenderer.php
│       ├── FormBuilder.php
│       ├── CodeGenerator.php
│       └── DAL.php          # Database Abstraction Layer
│
├── framework/               # Existing micro-MVC
│   └── ... (unchanged)
│
├── site/                    # Existing site
│   └── ... (unchanged)
│
└── generated/               # AI-generated pages
    ├── routes/
    ├── models/
    └── views/
```

---

## Component Details

### 1. MCP Server

**Technology:** Node.js + TypeScript

**Responsibilities:**
- Implement MCP protocol
- Expose tools to AI assistants
- Validate all inputs
- Call PHP engine via HTTP or file generation
- Maintain build state/history

**Key Endpoints:**
```typescript
// Tool: create_page
{
  name: "create_page",
  description: "Create a new page from a template",
  parameters: {
    template: "string (dashboard|data-table|form|...)",
    title: "string",
    route: "string",
    config: "object (template-specific options)"
  }
}

// Tool: add_widget
{
  name: "add_widget",
  description: "Add a widget to an existing page",
  parameters: {
    page: "string (route name)",
    widget: "string (widget type)",
    slot: "string (header|main|sidebar|footer)",
    config: "object (widget-specific options)"
  }
}
```

### 2. Template Engine

**Technology:** PHP

**Responsibilities:**
- Parse template definitions (JSON)
- Render template with slots
- Inject widgets into slots
- Apply theme/styling

**Template Definition Example:**
```json
{
  "name": "dashboard",
  "version": "1.0.0",
  "description": "Stats dashboard with cards and charts",
  "slots": [
    {"id": "header", "type": "single", "accepts": ["page-header", "breadcrumbs"]},
    {"id": "stats", "type": "grid", "columns": 4, "accepts": ["stats-card"]},
    {"id": "main", "type": "grid", "columns": 2, "accepts": ["*"]},
    {"id": "sidebar", "type": "stack", "accepts": ["*"]},
    {"id": "footer", "type": "single", "accepts": ["page-footer"]}
  ],
  "theme": {
    "supports": ["dark", "light", "custom"],
    "default": "dark"
  }
}
```

### 3. Widget Renderer

**Technology:** PHP + JS

**Responsibilities:**
- Parse widget definitions
- Render widget HTML
- Bind data sources
- Handle interactivity

**Widget Definition Example:**
```json
{
  "name": "stats-card",
  "version": "1.0.0",
  "description": "Displays a single statistic with icon and trend",
  "config": {
    "title": {"type": "string", "required": true},
    "value": {"type": "string|number", "required": true},
    "icon": {"type": "string", "default": "chart"},
    "color": {"type": "string", "default": "cyan"},
    "trend": {"type": "object", "properties": {
      "direction": "up|down|flat",
      "value": "string",
      "period": "string"
    }},
    "data_source": {"type": "object", "properties": {
      "type": "static|query|api",
      "query": "string",
      "refresh": "number (seconds)"
    }}
  }
}
```

### 4. Database Abstraction Layer (DAL)

**Technology:** PHP

**Responsibilities:**
- Safe query building
- Connection management
- CRUD operations
- WordPress/WooCommerce integration

**Interface:**
```php
interface PhoenixDAL {
    // Connection
    public function connect(string $connector, array $config): bool;

    // CRUD
    public function select(string $table, array $columns, array $where): array;
    public function insert(string $table, array $data): int;
    public function update(string $table, array $data, array $where): int;
    public function delete(string $table, array $where): int;

    // WordPress specific
    public function wp_get_posts(array $args): array;
    public function wp_get_users(array $args): array;

    // WooCommerce specific
    public function wc_get_products(array $args): array;
    public function wc_get_orders(array $args): array;
    public function wc_get_customers(array $args): array;
}
```

### 5. Code Generator

**Technology:** Node.js (in MCP server)

**Responsibilities:**
- Generate route entries
- Generate model files
- Generate view files
- Generate AJAX gates

**Generated File Example:**
```php
<?php
// AUTO-GENERATED BY PHOENIX
// Template: dashboard
// Created: 2025-12-21

class CustomerDashboard_Model {
    public static function Get_Data() {
        $dal = new PhoenixDAL();
        return [
            'total_customers' => $dal->count('customers'),
            'new_today' => $dal->count('customers', ['created_at' => 'TODAY']),
            'revenue' => $dal->sum('orders', 'total', ['status' => 'completed'])
        ];
    }
}
?>
```

---

## Data Flow

### Creating a New Page

```
1. User → AI: "Create a customer dashboard showing total customers and recent orders"

2. AI → MCP: create_page({
     template: "dashboard",
     title: "Customer Dashboard",
     route: "customer-dashboard"
   })

3. MCP Server:
   a. Validates input
   b. Generates route config
   c. Generates model file
   d. Generates view file
   e. Returns success + page URL

4. AI → MCP: add_widget({
     page: "customer-dashboard",
     widget: "stats-card",
     slot: "stats",
     config: {
       title: "Total Customers",
       data_source: { type: "query", query: "SELECT COUNT(*) FROM customers" }
     }
   })

5. MCP Server:
   a. Validates widget config
   b. Adds widget to view
   c. Updates model with data query
   d. Returns success

6. AI → User: "Done! Your customer dashboard is live at /en/customer-dashboard"
```

---

## Security Considerations

### Input Validation
- All tool parameters validated against JSON Schema
- SQL queries parameterized, no raw SQL from AI
- File paths sanitized
- Template/widget names must exist in registry

### Authentication
- MCP server requires API key
- Optional: Tie to WordPress user roles
- Rate limiting on all operations
- Audit logging of all changes

### Sandboxing
- Generated code runs in micro-MVC sandbox
- Database operations go through DAL (no direct SQL)
- File operations limited to designated directories

---

## Performance Considerations

### Caching
- Template definitions cached
- Widget registry cached
- Generated pages cached until modified

### Lazy Loading
- Widgets load data on demand
- Large datasets paginated
- Charts render incrementally

### Optimization
- CSS/JS bundled per page
- Images optimized on upload
- Database queries optimized

---

## Extension Points

### Custom Templates
Add new templates to `phoenix/templates/` with:
- `template.json` - Definition
- `template.phtml` - PHP view
- `template.css` - Styles
- `template.js` - Scripts

### Custom Widgets
Add new widgets to `phoenix/widgets/` with same structure.

### Custom Connectors
Add database connectors to `phoenix/connectors/` implementing `PhoenixDAL` interface.

### Custom Tools
Add MCP tools in `phoenix/mcp-server/src/tools/`.

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 0.1 | 2025-12-21 | Claude + Human | Initial architecture |
