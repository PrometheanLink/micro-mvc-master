# Development Roadmap

## Overview

This roadmap outlines the phased development approach for PHOENIX, the AI-powered dashboard builder. Each phase builds upon the previous, with clear deliverables and milestones.

---

## Phase 1: Foundation & Documentation

### Objectives
- Complete project documentation
- Establish development environment
- Set up version control and CI/CD

### Deliverables

| Item | Description | Status |
|------|-------------|--------|
| Project Vision | Core concept and value proposition | Complete |
| Architecture Design | System architecture and component design | Complete |
| MCP Server Spec | Tool definitions and API contracts | Complete |
| Template System Design | Template schema and rendering engine spec | Complete |
| Widget Library Spec | Widget definitions and configuration | Complete |
| Development Roadmap | This document | Complete |
| Git Repository | Version control setup | In Progress |

### Exit Criteria
- All documentation reviewed and approved
- Repository initialized with proper structure
- Development environment reproducible via Docker

---

## Phase 2: Core Engine

### Objectives
- Implement PHOENIX PHP engine
- Create template and widget registries
- Build code generator foundation

### Deliverables

| Item | Description | Priority |
|------|-------------|----------|
| PhoenixEngine.php | Core template engine class | High |
| WidgetRenderer.php | Widget parsing and rendering | High |
| TemplateRegistry | JSON-based template catalog | High |
| WidgetRegistry | JSON-based widget catalog | High |
| CodeGenerator.php | PHP code generation utilities | Medium |
| DAL.php | Database abstraction layer | Medium |

### Technical Tasks

```
phoenix/
├── engine/
│   ├── PhoenixEngine.php      # Template loading & rendering
│   ├── WidgetRenderer.php     # Widget instantiation
│   ├── FormBuilder.php        # Form generation
│   ├── CodeGenerator.php      # Code scaffolding
│   └── DAL.php                # Database abstraction
├── templates/
│   └── registry.json          # Template catalog
└── widgets/
    └── registry.json          # Widget catalog
```

### Exit Criteria
- Template engine can load and parse template definitions
- Widget renderer can instantiate widgets with config
- Basic code generator produces valid PHP files

---

## Phase 3: Template Library

### Objectives
- Implement all 16 core templates
- Create template CSS/JS assets
- Build preview system

### Template Implementation Order

| Priority | Templates | Rationale |
|----------|-----------|-----------|
| 1 | Dashboard, Data Table, Form Page | Most common use cases |
| 2 | Cards Grid, Split View, Blank | Flexible layouts |
| 3 | Profile, Auth, Landing | User-facing pages |
| 4 | Kanban, Calendar, Timeline | Interactive templates |
| 5 | Chat, Wizard, Gallery, Report | Specialized templates |

### Per-Template Deliverables
- `template.json` - Configuration schema
- `template.phtml` - PHP view with slots
- `template.css` - Scoped styles
- `template.js` - Interactivity (if needed)
- `preview.png` - Thumbnail for selection UI

### Exit Criteria
- All 16 templates implemented and tested
- Templates render correctly with placeholder widgets
- Responsive design verified on mobile/tablet/desktop

---

## Phase 4: Widget Library

### Objectives
- Implement 40+ core widgets
- Create widget documentation
- Build widget preview gallery

### Widget Implementation Order

| Priority | Category | Count | Widgets |
|----------|----------|-------|---------|
| 1 | Display | 8 | stats-card, chart-bar, chart-line, chart-pie, progress-bar, progress-ring, alert-box, info-card |
| 2 | Data | 5 | data-table, crud-table, pagination, search-bar, filter-panel |
| 3 | Input | 10 | text-input, textarea, select, checkbox, radio-group, date-picker, file-upload, toggle-switch, slider, rating |
| 4 | Layout | 8 | card, accordion, tabs, modal, drawer, breadcrumbs, page-header, divider |
| 5 | Display+ | 7 | timeline, activity-feed, notification-list, countdown, clock, chart-donut, chart-area |
| 6 | Input+ | 10 | multi-select, checkbox-group, time-picker, datetime-picker, date-range, image-upload, rich-text-editor, code-editor, color-picker, tags-input |
| 7 | Media | 8 | image, image-gallery, video-player, audio-player, webcam, screen-capture, file-manager, pdf-viewer |
| 8 | Layout+ | 4 | popover, tooltip, page-footer, spacer |

### Per-Widget Deliverables
- `widget.json` - Configuration schema with validation
- `widget.phtml` - PHP view template
- `widget.css` - Scoped styles
- `widget.js` - Client-side behavior
- `preview.png` - Thumbnail

### Exit Criteria
- All widgets render with sample configurations
- Widgets accept and validate configuration options
- Data binding works for query and API sources

---

## Phase 5: MCP Server

### Objectives
- Implement MCP protocol server
- Create tool handlers
- Build validation layer

### Technical Stack
- Runtime: Node.js 20+
- Language: TypeScript
- Protocol: MCP (Model Context Protocol)
- Transport: stdio (for Claude Desktop) + HTTP (for API access)

### Implementation Order

| Priority | Tools | Description |
|----------|-------|-------------|
| 1 | create_page, list_pages | Basic page management |
| 2 | add_widget, update_widget, remove_widget | Widget operations |
| 3 | create_form | Form generation |
| 4 | create_crud, query_database | Database operations |
| 5 | set_theme | Theming |
| 6 | create_api_endpoint | API creation |
| 7 | create_user_role | Authorization |

### Server Structure

```
phoenix/mcp-server/
├── src/
│   ├── server.ts           # MCP server entry point
│   ├── tools/
│   │   ├── pages.ts        # Page management tools
│   │   ├── widgets.ts      # Widget management tools
│   │   ├── forms.ts        # Form builder tools
│   │   ├── database.ts     # Database operation tools
│   │   ├── theme.ts        # Theming tools
│   │   └── auth.ts         # Authorization tools
│   ├── generators/
│   │   ├── route.ts        # Route generation
│   │   ├── model.ts        # Model generation
│   │   ├── view.ts         # View generation
│   │   └── gate.ts         # AJAX gate generation
│   └── validators/
│       ├── schema.ts       # JSON Schema validation
│       └── security.ts     # Security checks
├── package.json
└── tsconfig.json
```

### Exit Criteria
- MCP server responds to tool invocations
- Tools generate valid micro-MVC files
- Input validation prevents malformed requests
- Error handling provides useful feedback

---

## Phase 6: Database Connectors

### Objectives
- Implement MySQL connector
- Implement WordPress connector
- Implement WooCommerce connector

### Connector Interface

```php
interface PhoenixConnector {
    public function connect(array $config): bool;
    public function select(string $table, array $columns, array $where): array;
    public function insert(string $table, array $data): int;
    public function update(string $table, array $data, array $where): int;
    public function delete(string $table, array $where): int;
    public function count(string $table, array $where = []): int;
    public function sum(string $table, string $column, array $where = []): float;
}
```

### WordPress-Specific Methods

```php
interface WordPressConnector extends PhoenixConnector {
    public function get_posts(array $args): array;
    public function get_users(array $args): array;
    public function get_terms(string $taxonomy, array $args): array;
    public function get_options(array $keys): array;
}
```

### WooCommerce-Specific Methods

```php
interface WooCommerceConnector extends WordPressConnector {
    public function get_products(array $args): array;
    public function get_orders(array $args): array;
    public function get_customers(array $args): array;
    public function get_coupons(array $args): array;
    public function get_reports(string $type, array $args): array;
}
```

### Exit Criteria
- MySQL connector handles basic CRUD
- WordPress connector reads WP database with proper escaping
- WooCommerce connector accesses product/order data
- All queries use parameterized statements

---

## Phase 7: Security & Authentication

### Objectives
- Implement API key authentication
- Add rate limiting
- Create audit logging
- Build permission system

### Security Features

| Feature | Description | Priority |
|---------|-------------|----------|
| API Key Auth | Require valid key for MCP access | High |
| Rate Limiting | Prevent abuse (60 req/min default) | High |
| Input Sanitization | Sanitize all user inputs | High |
| SQL Injection Prevention | Parameterized queries only | High |
| XSS Prevention | Output escaping | High |
| Audit Logging | Log all operations | Medium |
| Permission System | Role-based access control | Medium |
| CSRF Protection | Token validation for forms | Medium |

### Exit Criteria
- All API endpoints require authentication
- Rate limiting prevents excessive requests
- All operations logged with timestamps
- Permissions enforced on tool execution

---

## Phase 8: Testing & Quality

### Objectives
- Unit tests for all components
- Integration tests for workflows
- End-to-end tests for AI interactions

### Test Coverage Targets

| Component | Coverage Target |
|-----------|-----------------|
| PHP Engine | 80% |
| MCP Server | 85% |
| Connectors | 90% |
| Generators | 75% |

### Testing Stack
- PHP: PHPUnit
- Node.js: Jest
- E2E: Playwright
- API: Postman/Newman

### Exit Criteria
- All tests passing
- Coverage targets met
- No critical security issues
- Performance benchmarks established

---

## Phase 9: Documentation & Examples

### Objectives
- Create user documentation
- Build example applications
- Record tutorial videos

### Documentation

| Document | Audience | Format |
|----------|----------|--------|
| Getting Started | All | Markdown + Video |
| Template Guide | Developers | Markdown |
| Widget Reference | Developers | Markdown |
| MCP Tool Reference | AI/Developers | Markdown |
| API Documentation | Developers | OpenAPI |
| Connector Guide | Developers | Markdown |

### Example Applications

| Example | Templates Used | Widgets Used |
|---------|----------------|--------------|
| Customer Dashboard | Dashboard | stats-card, chart-line, data-table |
| Product Catalog | Cards Grid, Split View | image-gallery, filter-panel, pagination |
| Order Management | Data Table, Form Page | crud-table, form fields |
| Support Ticket System | Kanban, Chat | cards, timeline, chat-window |
| Event Calendar | Calendar | calendar-grid, event-modal |

### Exit Criteria
- All documentation complete and reviewed
- At least 5 example applications built
- Video tutorials for common workflows

---

## Phase 10: Launch & Iteration

### Objectives
- Public release
- Community building
- Continuous improvement

### Launch Checklist

- [ ] All phases complete
- [ ] Security audit passed
- [ ] Performance testing complete
- [ ] Documentation published
- [ ] Examples deployed
- [ ] GitHub repository public
- [ ] npm package published (MCP server)
- [ ] Packagist package published (PHP engine)
- [ ] Discord/community channel created
- [ ] Issue templates created
- [ ] Contributing guidelines written

### Post-Launch

| Activity | Frequency |
|----------|-----------|
| Bug fixes | As needed |
| Security patches | Immediate |
| Minor releases | Monthly |
| Major releases | Quarterly |
| Community feedback review | Weekly |

---

## Milestones Summary

| Milestone | Phases | Key Deliverable |
|-----------|--------|-----------------|
| M1: Foundation | 1-2 | Working PHP engine |
| M2: Templates | 3 | 16 templates ready |
| M3: Widgets | 4 | 40+ widgets ready |
| M4: MCP Server | 5 | AI can build pages |
| M5: Data Layer | 6 | Database integration |
| M6: Security | 7 | Production-ready security |
| M7: Quality | 8 | Tested & documented |
| M8: Launch | 9-10 | Public release |

---

## Resource Requirements

### Development Team (Ideal)

| Role | Responsibility | Phases |
|------|----------------|--------|
| Lead Developer | Architecture, PHP engine | All |
| Frontend Developer | Templates, widgets, CSS | 3-4 |
| Node.js Developer | MCP server | 5-6 |
| DevOps | Docker, CI/CD, deployment | 1, 8-10 |
| Technical Writer | Documentation | 9 |

### Solo Developer Path

If building alone, prioritize:
1. Phase 1-2: Foundation (core engine)
2. Phase 5: MCP Server (MVP tools)
3. Phase 3: 4 essential templates
4. Phase 4: 10 essential widgets
5. Iterate with user feedback

---

## Risk Management

| Risk | Impact | Mitigation |
|------|--------|------------|
| Scope creep | High | Strict phase gates, MVP first |
| AI hallucinations | Medium | Strict validation, clear errors |
| Security vulnerabilities | High | Security audit, parameterized queries |
| Performance issues | Medium | Caching, lazy loading, profiling |
| Compatibility issues | Low | Test matrix, Docker standardization |

---

## Success Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| Page creation time | < 30 seconds | Automated testing |
| Widget add time | < 10 seconds | Automated testing |
| Error rate | < 1% | Logging/monitoring |
| AI tool success rate | > 95% | MCP server logs |
| Documentation coverage | 100% | Manual review |
| Test coverage | > 80% | CI/CD reports |

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 0.1 | 2025-12-21 | Claude + Human | Initial roadmap |
