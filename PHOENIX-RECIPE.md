# PHOENIX Platform Builder Recipe
## PrometheanLink AI-Powered Site Builder

**Created:** December 21, 2024
**Status:** Foundation Complete - Ready for AI Integration
**Theme:** PrometheanLink Industrial Dark

---

## Vision

PHOENIX is an AI-powered platform builder that transforms natural language into fully functional web applications. Built on micro-MVC, it uses a template + widget architecture that can generate:

- **Dashboards** - Analytics, KPIs, real-time monitoring
- **CMS Pages** - Articles, landing pages, documentation
- **Webstores** - Product catalogs, carts, checkout flows
- **Blogs** - Posts, categories, comments, RSS
- **Galleries** - Photo albums, portfolios, lightboxes
- **Project Management** - Kanban boards, timelines, task tracking
- **Custom Applications** - Any combination of the above

The AI understands your intent and generates the appropriate template, widgets, and data structure.

---

## Current State (What We Have)

### Working Infrastructure

```
phoenix/
├── assets/
│   ├── css/phoenix-core.css    ← PrometheanLink Industrial Dark theme
│   └── js/phoenix-core.js      ← Core JavaScript utilities
├── api/
│   └── save-config.php         ← Dashboard config API
├── config/
│   └── dashboard.json          ← Dynamic dashboard configuration
├── mcp-server/
│   ├── server.js               ← Express API (port 5678)
│   ├── lib/
│   │   └── dashboard-manager.js ← State management
│   └── tools/
│       ├── create-dashboard.js  ← Generates route + model + view
│       ├── add-widget.js        ← Inserts widgets into pages
│       ├── list-dashboards.js   ← Lists existing dashboards
│       └── analyze-framework.js ← Framework analysis
├── templates/                   ← Page layout templates (to build)
└── widgets/                     ← Reusable components (to build)

framework/mvc/
├── models/
│   ├── root.php                ← PHOENIX dashboard (homepage)
│   ├── admin.php               ← Control Panel
│   └── sales-dashboard.php     ← Example dashboard
└── views/
    ├── root.phtml              ← Dashboard view (PrometheanLink theme)
    ├── admin.phtml             ← Control Panel view
    └── sales-dashboard.phtml   ← Example dashboard view
```

### Working URLs

- `http://localhost:8888/en/` - Main PHOENIX Dashboard
- `http://localhost:8888/en/admin/` - Control Panel
- `http://localhost:8888/en/sales-dashboard/` - Example Dashboard

### Express API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/config` | Get framework configuration |
| GET | `/api/templates` | List available templates |
| GET | `/api/widgets` | List available widgets |
| GET | `/api/pages` | List all pages |
| GET | `/api/pages/:route` | Get page source code |
| POST | `/api/pages` | Create new page |
| DELETE | `/api/pages/:route` | Delete page |
| GET | `/api/tools` | List AI tools |
| POST | `/api/tools/:tool` | Execute tool |

---

## Architecture

### Core Concept: Templates + Widgets + AI

```
┌─────────────────────────────────────────────────────────────────┐
│                         USER PROMPT                              │
│        "Create a product catalog for my sneaker store"          │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      AI INTERPRETATION                           │
│  • Page Type: Webstore                                          │
│  • Template: product-grid                                        │
│  • Widgets: product-card, filters, search, cart-button          │
│  • Theme: PrometheanLink Industrial Dark                        │
│  • Data: Products array with name, price, image, category       │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                     CODE GENERATION                              │
│  1. Add route to routes.cfg                                     │
│  2. Generate PHP model with data structure                      │
│  3. Generate PHTML view with template + widgets                 │
│  4. Create any necessary assets                                 │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      LIVE PAGE                                   │
│              http://localhost:8888/en/sneaker-catalog/          │
└─────────────────────────────────────────────────────────────────┘
```

### Template Types

Each template defines a **layout structure** with **slots** for widgets:

```
┌────────────────────────────────────────────┐
│                  HEADER                     │  ← slot: header
├────────────────────────────────────────────┤
│  SIDEBAR  │         MAIN CONTENT           │  ← slots: sidebar, main
│           │                                 │
│           │  ┌─────┐ ┌─────┐ ┌─────┐       │
│           │  │WIDGT│ │WIDGT│ │WIDGT│       │  ← widgets in slots
│           │  └─────┘ └─────┘ └─────┘       │
│           │                                 │
├───────────┴────────────────────────────────┤
│                  FOOTER                     │  ← slot: footer
└────────────────────────────────────────────┘
```

### Widget Structure

Each widget is self-contained with:

```
widgets/stat-card/
├── widget.json         ← Configuration schema
├── widget.phtml        ← PHP/HTML template
├── widget.css          ← Scoped styles
└── widget.js           ← Optional JavaScript
```

---

## Implementation Phases

### Phase 1: MCP Integration (Priority: HIGH)

**Goal:** Enable Claude Code to create pages through conversation.

#### Tasks:

1. **Create MCP Server Entry Point**
   ```
   phoenix/mcp-server/index.js
   ```
   - Implement MCP protocol (stdio transport)
   - Register tools with schemas
   - Handle tool execution

2. **Define Tool Schemas**
   ```javascript
   tools: [
     {
       name: "create_page",
       description: "Create a new page with template and widgets",
       inputSchema: {
         type: "object",
         properties: {
           name: { type: "string", description: "Page title" },
           route: { type: "string", description: "URL path" },
           template: { type: "string", enum: ["dashboard", "catalog", "blog", "gallery"] },
           widgets: { type: "array", items: { type: "object" } },
           theme: { type: "string", default: "cyber" }
         },
         required: ["name", "route", "template"]
       }
     },
     {
       name: "add_widget",
       description: "Add a widget to an existing page",
       inputSchema: { ... }
     },
     {
       name: "update_config",
       description: "Update page configuration",
       inputSchema: { ... }
     },
     {
       name: "list_pages",
       description: "List all pages in the system",
       inputSchema: {}
     },
     {
       name: "get_templates",
       description: "Get available templates and their slots",
       inputSchema: {}
     },
     {
       name: "get_widgets",
       description: "Get available widgets and their schemas",
       inputSchema: {}
     }
   ]
   ```

3. **Add to Claude Code MCP Config**
   ```json
   // ~/.claude/claude_desktop_config.json or similar
   {
     "mcpServers": {
       "phoenix": {
         "command": "node",
         "args": ["path/to/phoenix/mcp-server/index.js"],
         "env": {
           "FRAMEWORK_PATH": "path/to/micro-MVC-master"
         }
       }
     }
   }
   ```

4. **Test Conversational Flow**
   ```
   User: "Create a dashboard for tracking my fitness goals"

   Claude: I'll create a fitness dashboard for you.
   [Calls create_page tool]

   Created! View at: http://localhost:8888/en/fitness-dashboard/

   User: "Add a progress bar showing my weight loss goal"

   Claude: [Calls add_widget tool]

   Added progress bar widget. Refresh to see it.
   ```

---

### Phase 2: Template Library (Priority: HIGH)

**Goal:** Create reusable templates for different page types.

#### Templates to Build:

1. **dashboard** (exists - enhance)
   - Slots: stats-row, chart-area, sidebar, activity-feed
   - Use case: Analytics, monitoring, KPIs

2. **catalog** (new)
   - Slots: filters-sidebar, product-grid, pagination
   - Use case: Product listings, portfolios

3. **article** (new)
   - Slots: header-image, content, sidebar, comments
   - Use case: Blog posts, documentation, news

4. **gallery** (new)
   - Slots: album-header, media-grid, lightbox
   - Use case: Photo galleries, portfolios

5. **kanban** (new)
   - Slots: board-header, columns, card-modal
   - Use case: Project management, task tracking

6. **landing** (new)
   - Slots: hero, features, testimonials, cta, footer
   - Use case: Marketing pages, product launches

#### Template Structure:

```
phoenix/templates/catalog/
├── template.json       ← Metadata + slot definitions
├── template.phtml      ← Layout with slot placeholders
├── template.css        ← Template-specific styles
├── template.js         ← Template JavaScript
└── preview.png         ← Preview image for UI
```

#### template.json Example:

```json
{
  "id": "catalog",
  "name": "Product Catalog",
  "description": "Grid layout for products, portfolios, or any collection",
  "category": "ecommerce",
  "slots": {
    "filters": {
      "position": "sidebar-left",
      "accepts": ["filter-panel", "search-box", "category-list"]
    },
    "products": {
      "position": "main",
      "accepts": ["product-card", "portfolio-item", "media-card"],
      "repeatable": true
    },
    "pagination": {
      "position": "footer",
      "accepts": ["pagination", "load-more"]
    }
  },
  "config": {
    "columns": { "type": "number", "default": 3, "min": 1, "max": 6 },
    "showFilters": { "type": "boolean", "default": true },
    "itemsPerPage": { "type": "number", "default": 12 }
  }
}
```

---

### Phase 3: Widget Library (Priority: HIGH)

**Goal:** Build reusable widgets for all template types.

#### Widget Categories:

**Data Display**
- `stat-card` - Single metric with trend
- `chart-bar` - Bar chart
- `chart-line` - Line chart
- `chart-pie` - Pie/donut chart
- `data-table` - Sortable table
- `progress-bar` - Progress indicator
- `metric-group` - Multiple metrics in row

**Content**
- `text-block` - Rich text content
- `image-block` - Image with caption
- `video-embed` - YouTube/Vimeo embed
- `code-block` - Syntax-highlighted code
- `quote-block` - Blockquote with attribution
- `cta-button` - Call-to-action button

**Navigation**
- `breadcrumb` - Breadcrumb trail
- `pagination` - Page navigation
- `tabs` - Tabbed content
- `accordion` - Collapsible sections
- `sidebar-nav` - Vertical navigation

**E-commerce**
- `product-card` - Product with image, price, cart
- `cart-widget` - Shopping cart summary
- `filter-panel` - Filter checkboxes/sliders
- `price-range` - Price range slider
- `add-to-cart` - Add to cart button

**Social/Interactive**
- `comment-section` - Comments with replies
- `share-buttons` - Social share buttons
- `rating-stars` - Star rating display
- `like-button` - Like/upvote button
- `activity-feed` - Recent activity list

**Forms**
- `contact-form` - Contact form
- `newsletter-signup` - Email signup
- `search-box` - Search input
- `login-form` - Login form

**Project Management**
- `kanban-column` - Kanban column with cards
- `task-card` - Task card with status
- `timeline` - Vertical timeline
- `gantt-bar` - Gantt chart bar
- `milestone` - Milestone marker

#### Widget Structure:

```
phoenix/widgets/product-card/
├── widget.json
├── widget.phtml
├── widget.css
└── widget.js
```

#### widget.json Example:

```json
{
  "id": "product-card",
  "name": "Product Card",
  "description": "Product display with image, title, price, and cart button",
  "category": "ecommerce",
  "icon": "shopping-bag",
  "config": {
    "showImage": { "type": "boolean", "default": true },
    "showPrice": { "type": "boolean", "default": true },
    "showRating": { "type": "boolean", "default": false },
    "imageAspect": { "type": "string", "enum": ["square", "portrait", "landscape"], "default": "square" },
    "priceFormat": { "type": "string", "default": "${{price}}" }
  },
  "dataSchema": {
    "type": "object",
    "properties": {
      "id": { "type": "string" },
      "name": { "type": "string" },
      "price": { "type": "number" },
      "image": { "type": "string" },
      "rating": { "type": "number" },
      "inStock": { "type": "boolean" }
    },
    "required": ["id", "name", "price"]
  }
}
```

---

### Phase 4: Enhanced Control Panel (Priority: MEDIUM)

**Goal:** Visual page builder interface.

#### Features:

1. **Page Manager**
   - List all pages with type icons
   - Create new page wizard
   - Duplicate, edit, delete pages
   - Preview in iframe

2. **Template Selector**
   - Visual template gallery
   - Preview layouts
   - Template customization

3. **Widget Palette**
   - Drag-and-drop widgets
   - Widget configuration panel
   - Live preview

4. **Theme Editor**
   - Color palette customization
   - Typography settings
   - Save custom themes

5. **Data Manager**
   - Edit page data (JSON editor)
   - Import/export data
   - Connect external APIs

#### UI Mockup:

```
┌────────────────────────────────────────────────────────────────────┐
│  🔥 PHOENIX Control Panel                      [View Site] [Save] │
├──────────────┬─────────────────────────────────────────────────────┤
│              │                                                      │
│  📄 Pages    │   ┌──────────────────────────────────────────────┐  │
│  ─────────   │   │                                              │  │
│  • Dashboard │   │           PAGE PREVIEW (iframe)              │  │
│  • Products  │   │                                              │  │
│  • Blog      │   │                                              │  │
│  + New Page  │   │                                              │  │
│              │   └──────────────────────────────────────────────┘  │
│  🧩 Widgets  │                                                      │
│  ─────────   │   Widget Configuration                              │
│  stat-card   │   ┌──────────────────────────────────────────────┐  │
│  chart       │   │ Title: [Revenue          ]                   │  │
│  table       │   │ Value: [84250            ]                   │  │
│  product     │   │ Icon:  [💰 Money ▼       ]                   │  │
│              │   │ Color: [● ● ● ● ●        ]                   │  │
│  🎨 Theme    │   └──────────────────────────────────────────────┘  │
│              │                                                      │
└──────────────┴─────────────────────────────────────────────────────┘
```

---

### Phase 5: AI Enhancements (Priority: MEDIUM)

**Goal:** Smarter AI interactions.

#### Features:

1. **Natural Language Processing**
   - Parse complex prompts
   - Understand industry-specific terms
   - Suggest improvements

2. **Smart Defaults**
   - Recommend widgets based on page type
   - Auto-generate sample data
   - Suggest color schemes

3. **Conversational Editing**
   - "Make the header bigger"
   - "Change the chart to show monthly data"
   - "Add a search bar to the products page"

4. **Data Integration**
   - "Connect this to my Shopify store"
   - "Pull data from this spreadsheet"
   - "Show live stock prices"

5. **Export/Deploy**
   - Generate static HTML
   - Deploy to hosting
   - Export as standalone app

---

## File Checklist

### Phase 1: MCP Integration ✅ COMPLETE
- [x] `phoenix/mcp-server/index.js` - MCP protocol entry point (8 tools implemented)
- [x] `phoenix/mcp-server/schemas/tools.json` - Tool schemas
- [x] Template registry exists at `phoenix/templates/registry.json`
- [x] Widget registry exists at `phoenix/widgets/registry.json`
- [ ] Update Claude Code MCP configuration (add to settings)

### Phase 2: Templates ✅ COMPLETE
- [x] `phoenix/templates/registry.json` - Template registry (16 templates)
- [x] `phoenix/templates/dashboard/` - Dashboard template (complete)
- [x] `phoenix/templates/cards-grid/` - Cards grid template (catalog-ready)
- [x] `phoenix/templates/data-table/` - Data table template (complete)
- [x] `phoenix/templates/form-page/` - Form page template (complete)
- [x] `phoenix/templates/catalog/` - Product catalog (filters, cart, product grid)
- [x] `phoenix/templates/article/` - Blog/article (reading progress, ToC, comments)
- [x] `phoenix/templates/gallery/` - Media gallery (masonry, lightbox, lazy load)
- [x] `phoenix/templates/kanban/` - Kanban board (drag-drop, cards, columns)
- [x] `phoenix/templates/landing/` - Landing page (hero, features, pricing, CTA)

### Phase 3: Widgets (PARTIALLY COMPLETE)
- [x] `phoenix/widgets/registry.json` - Widget registry (11 widgets)
- [x] `phoenix/widgets/stats-card/` - Stats card widget
- [x] `phoenix/widgets/chart-bar/` - Bar chart widget
- [x] `phoenix/widgets/progress-bar/` - Progress bar widget
- [x] `phoenix/widgets/activity-feed/` - Activity feed widget
- [x] `phoenix/widgets/data-table/` - Data table widget
- [ ] `phoenix/widgets/product-card/` - Product card widget
- [ ] `phoenix/widgets/text-block/` - Text content widget
- [ ] `phoenix/widgets/kanban-column/` - Kanban column widget

### Phase 4: Control Panel
- [ ] `phoenix/mcp-server/control-panel/index.html` - Enhanced UI
- [ ] `phoenix/mcp-server/control-panel/pages.html` - Page manager
- [ ] `phoenix/mcp-server/control-panel/editor.html` - Visual editor
- [ ] `phoenix/mcp-server/control-panel/assets/` - UI assets

---

## Quick Start for Next Session

```bash
# 1. Start Docker (micro-MVC)
cd micro-MVC-master
docker-compose up -d

# 2. Start PHOENIX MCP Server
cd phoenix/mcp-server
npm start

# 3. Verify everything works
# Dashboard: http://localhost:8888/en/
# Admin: http://localhost:8888/en/admin/
# API: http://localhost:5678/api/config

# 4. Begin Phase 1: MCP Integration
# Create phoenix/mcp-server/index.js with MCP protocol
```

---

## Success Metrics

- [ ] Can create a new dashboard via Claude Code conversation
- [ ] Can add widgets to existing pages via conversation
- [ ] Templates render correctly with the PrometheanLink theme
- [ ] Widgets are configurable and reusable
- [ ] Control Panel provides visual editing
- [ ] Full CRUD operations work via API

---

## Notes

- All pages use the PrometheanLink Industrial Dark theme
- The system is language-agnostic (supports micro-MVC's multi-language routing)
- Docker container runs on port 8888
- Express API runs on port 5678
- MCP tools use stdio transport for Claude Code integration

---

**Let's build something incredible.**

🔥 *Industrial Strength Digital Operations*
