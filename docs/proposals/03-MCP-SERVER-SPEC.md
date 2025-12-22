# MCP Server Specification

## Overview

The PHOENIX MCP Server is a Node.js application that implements Anthropic's Model Context Protocol (MCP), exposing a comprehensive set of tools for AI assistants to build web applications on the micro-MVC framework.

---

## Server Configuration

```typescript
// phoenix/mcp-server/config.ts
export const config = {
  server: {
    name: "phoenix-builder",
    version: "1.0.0",
    description: "AI-powered dashboard and application builder"
  },
  paths: {
    framework: "../framework",
    templates: "../phoenix/templates",
    widgets: "../phoenix/widgets",
    generated: "../generated"
  },
  security: {
    requireApiKey: true,
    rateLimitPerMinute: 60,
    allowedOrigins: ["*"]
  }
};
```

---

## Tool Definitions

### Category: Page Management

#### `create_page`
Creates a new page from a template.

```typescript
{
  name: "create_page",
  description: "Create a new page from a predefined template",
  inputSchema: {
    type: "object",
    required: ["template", "title", "route"],
    properties: {
      template: {
        type: "string",
        enum: ["dashboard", "data-table", "form-page", "kanban", "calendar",
               "gallery", "profile", "auth", "landing", "wizard", "chat",
               "timeline", "cards-grid", "split-view", "blank", "report"],
        description: "The template to use for the page"
      },
      title: {
        type: "string",
        description: "The page title displayed in header and browser"
      },
      route: {
        type: "string",
        pattern: "^[a-z0-9-]+$",
        description: "URL-safe route name (lowercase, hyphens only)"
      },
      description: {
        type: "string",
        description: "Optional page description for documentation"
      },
      theme: {
        type: "string",
        enum: ["dark", "light", "cyber", "matrix", "sunset", "ocean"],
        default: "dark"
      },
      layout: {
        type: "object",
        properties: {
          sidebar: { type: "boolean", default: false },
          header: { type: "boolean", default: true },
          footer: { type: "boolean", default: false }
        }
      }
    }
  }
}
```

**Returns:**
```json
{
  "success": true,
  "page": {
    "route": "customer-dashboard",
    "url": "/en/customer-dashboard",
    "files": {
      "model": "framework/mvc/models/customer-dashboard.php",
      "view": "framework/mvc/views/customer-dashboard.phtml"
    }
  }
}
```

---

#### `list_pages`
Lists all pages in the application.

```typescript
{
  name: "list_pages",
  description: "Get a list of all pages/routes in the application",
  inputSchema: {
    type: "object",
    properties: {
      filter: {
        type: "string",
        description: "Optional filter by template type"
      }
    }
  }
}
```

---

#### `delete_page`
Removes a page from the application.

```typescript
{
  name: "delete_page",
  description: "Delete a page and its associated files",
  inputSchema: {
    type: "object",
    required: ["route"],
    properties: {
      route: {
        type: "string",
        description: "The route name of the page to delete"
      }
    }
  }
}
```

---

### Category: Widget Management

#### `add_widget`
Adds a widget to an existing page.

```typescript
{
  name: "add_widget",
  description: "Add a widget to a page slot",
  inputSchema: {
    type: "object",
    required: ["page", "widget", "slot"],
    properties: {
      page: {
        type: "string",
        description: "The route name of the target page"
      },
      widget: {
        type: "string",
        enum: [
          // Display widgets
          "stats-card", "chart-bar", "chart-line", "chart-pie", "chart-donut",
          "progress-bar", "progress-ring", "alert-box", "info-card", "timeline",
          "activity-feed", "notification-list", "countdown", "clock",
          // Data widgets
          "data-table", "crud-table", "tree-view", "list-group", "pagination",
          "search-bar", "filter-panel", "sort-controls", "export-button",
          // Input widgets
          "text-input", "textarea", "select", "multi-select", "checkbox",
          "radio-group", "toggle-switch", "date-picker", "time-picker",
          "date-range", "file-upload", "image-upload", "rich-text-editor",
          "color-picker", "slider", "rating",
          // Media widgets
          "image", "image-gallery", "video-player", "audio-player",
          "webcam", "screen-capture", "file-manager",
          // Layout widgets
          "card", "accordion", "tabs", "modal", "drawer", "popover",
          "breadcrumbs", "page-header", "page-footer", "divider", "spacer"
        ],
        description: "The widget type to add"
      },
      slot: {
        type: "string",
        description: "The slot ID where the widget should be placed"
      },
      position: {
        type: "string",
        enum: ["start", "end", "before", "after"],
        default: "end",
        description: "Position within the slot"
      },
      config: {
        type: "object",
        description: "Widget-specific configuration (varies by widget type)"
      }
    }
  }
}
```

---

#### `remove_widget`
Removes a widget from a page.

```typescript
{
  name: "remove_widget",
  description: "Remove a widget from a page",
  inputSchema: {
    type: "object",
    required: ["page", "widget_id"],
    properties: {
      page: { type: "string" },
      widget_id: { type: "string" }
    }
  }
}
```

---

#### `update_widget`
Updates a widget's configuration.

```typescript
{
  name: "update_widget",
  description: "Update the configuration of an existing widget",
  inputSchema: {
    type: "object",
    required: ["page", "widget_id", "config"],
    properties: {
      page: { type: "string" },
      widget_id: { type: "string" },
      config: { type: "object" }
    }
  }
}
```

---

### Category: Form Builder

#### `create_form`
Creates a form with fields and validation.

```typescript
{
  name: "create_form",
  description: "Create a form with fields, validation, and database binding",
  inputSchema: {
    type: "object",
    required: ["name", "fields"],
    properties: {
      name: {
        type: "string",
        description: "Form identifier"
      },
      page: {
        type: "string",
        description: "Page to add the form to (optional)"
      },
      fields: {
        type: "array",
        items: {
          type: "object",
          required: ["name", "type", "label"],
          properties: {
            name: { type: "string" },
            type: {
              type: "string",
              enum: ["text", "email", "password", "number", "tel", "url",
                     "textarea", "select", "multi-select", "checkbox",
                     "radio", "date", "time", "datetime", "file", "hidden"]
            },
            label: { type: "string" },
            placeholder: { type: "string" },
            required: { type: "boolean", default: false },
            validation: {
              type: "object",
              properties: {
                min: { type: "number" },
                max: { type: "number" },
                minLength: { type: "number" },
                maxLength: { type: "number" },
                pattern: { type: "string" },
                custom: { type: "string" }
              }
            },
            options: {
              type: "array",
              items: {
                type: "object",
                properties: {
                  value: { type: "string" },
                  label: { type: "string" }
                }
              }
            },
            default: { type: "any" }
          }
        }
      },
      submit: {
        type: "object",
        properties: {
          text: { type: "string", default: "Submit" },
          action: { type: "string", enum: ["insert", "update", "api", "custom"] },
          table: { type: "string" },
          endpoint: { type: "string" },
          redirect: { type: "string" }
        }
      },
      layout: {
        type: "string",
        enum: ["vertical", "horizontal", "inline", "grid"],
        default: "vertical"
      }
    }
  }
}
```

---

### Category: Database Operations

#### `create_crud`
Creates a full CRUD interface for a database table.

```typescript
{
  name: "create_crud",
  description: "Generate a complete CRUD interface for a database table",
  inputSchema: {
    type: "object",
    required: ["table", "columns"],
    properties: {
      table: {
        type: "string",
        description: "Database table name"
      },
      connector: {
        type: "string",
        enum: ["mysql", "wordpress", "woocommerce"],
        default: "mysql"
      },
      columns: {
        type: "array",
        items: {
          type: "object",
          required: ["name", "type"],
          properties: {
            name: { type: "string" },
            type: { type: "string" },
            label: { type: "string" },
            sortable: { type: "boolean", default: true },
            searchable: { type: "boolean", default: true },
            editable: { type: "boolean", default: true },
            visible: { type: "boolean", default: true }
          }
        }
      },
      features: {
        type: "object",
        properties: {
          create: { type: "boolean", default: true },
          read: { type: "boolean", default: true },
          update: { type: "boolean", default: true },
          delete: { type: "boolean", default: true },
          export: { type: "boolean", default: true },
          import: { type: "boolean", default: false },
          bulkActions: { type: "boolean", default: true }
        }
      },
      pagination: {
        type: "object",
        properties: {
          enabled: { type: "boolean", default: true },
          perPage: { type: "number", default: 25 },
          options: { type: "array", items: { type: "number" } }
        }
      }
    }
  }
}
```

---

#### `query_database`
Executes a read-only database query.

```typescript
{
  name: "query_database",
  description: "Execute a safe, read-only database query",
  inputSchema: {
    type: "object",
    required: ["operation"],
    properties: {
      connector: {
        type: "string",
        enum: ["mysql", "wordpress", "woocommerce"],
        default: "mysql"
      },
      operation: {
        type: "string",
        enum: ["select", "count", "sum", "avg", "min", "max"]
      },
      table: { type: "string" },
      columns: { type: "array", items: { type: "string" } },
      where: { type: "object" },
      orderBy: { type: "string" },
      limit: { type: "number" },
      offset: { type: "number" }
    }
  }
}
```

---

### Category: Theme & Styling

#### `set_theme`
Applies a theme to a page or globally.

```typescript
{
  name: "set_theme",
  description: "Set the color theme for a page or the entire application",
  inputSchema: {
    type: "object",
    properties: {
      page: {
        type: "string",
        description: "Page route (omit for global)"
      },
      theme: {
        type: "string",
        enum: ["dark", "light", "cyber", "matrix", "sunset", "ocean"]
      },
      custom: {
        type: "object",
        properties: {
          primary: { type: "string" },
          secondary: { type: "string" },
          accent: { type: "string" },
          background: { type: "string" },
          surface: { type: "string" },
          text: { type: "string" }
        }
      }
    }
  }
}
```

---

### Category: API & Integration

#### `create_api_endpoint`
Creates an API endpoint for external access.

```typescript
{
  name: "create_api_endpoint",
  description: "Create a REST API endpoint",
  inputSchema: {
    type: "object",
    required: ["route", "method"],
    properties: {
      route: {
        type: "string",
        description: "API route path (e.g., 'products', 'orders/{id}')"
      },
      method: {
        type: "string",
        enum: ["GET", "POST", "PUT", "PATCH", "DELETE"]
      },
      auth: {
        type: "string",
        enum: ["none", "api_key", "jwt", "session"],
        default: "api_key"
      },
      handler: {
        type: "object",
        properties: {
          type: { type: "string", enum: ["crud", "query", "custom"] },
          table: { type: "string" },
          query: { type: "string" }
        }
      },
      response: {
        type: "object",
        properties: {
          format: { type: "string", enum: ["json", "xml", "csv"] },
          pagination: { type: "boolean" }
        }
      }
    }
  }
}
```

---

### Category: User & Auth

#### `create_user_role`
Creates a custom user role with permissions.

```typescript
{
  name: "create_user_role",
  description: "Create a new user role with specific permissions",
  inputSchema: {
    type: "object",
    required: ["name", "permissions"],
    properties: {
      name: { type: "string" },
      displayName: { type: "string" },
      description: { type: "string" },
      permissions: {
        type: "array",
        items: {
          type: "string",
          enum: [
            "pages.view", "pages.create", "pages.edit", "pages.delete",
            "widgets.manage", "forms.submit", "data.read", "data.write",
            "users.view", "users.manage", "settings.manage", "api.access"
          ]
        }
      },
      inherits: {
        type: "string",
        description: "Parent role to inherit from"
      }
    }
  }
}
```

---

## Response Format

All tools return responses in this format:

```typescript
interface ToolResponse {
  success: boolean;
  data?: any;           // Tool-specific response data
  error?: {
    code: string;       // Error code for programmatic handling
    message: string;    // Human-readable error message
    details?: any;      // Additional error details
  };
  meta?: {
    duration: number;   // Execution time in ms
    timestamp: string;  // ISO timestamp
  };
}
```

---

## Error Codes

| Code | Description |
|------|-------------|
| `INVALID_INPUT` | Input validation failed |
| `TEMPLATE_NOT_FOUND` | Specified template doesn't exist |
| `WIDGET_NOT_FOUND` | Specified widget doesn't exist |
| `PAGE_NOT_FOUND` | Specified page doesn't exist |
| `SLOT_NOT_FOUND` | Specified slot doesn't exist in template |
| `PERMISSION_DENIED` | User lacks required permission |
| `DATABASE_ERROR` | Database operation failed |
| `FILE_ERROR` | File system operation failed |
| `RATE_LIMITED` | Too many requests |
| `INTERNAL_ERROR` | Unexpected server error |

---

## Implementation Priority

### Phase 1 (MVP)
1. `create_page`
2. `add_widget`
3. `list_pages`
4. Basic widget configs

### Phase 2
1. `create_form`
2. `create_crud`
3. `query_database`
4. Form validation

### Phase 3
1. `set_theme`
2. `create_api_endpoint`
3. Custom themes

### Phase 4
1. `create_user_role`
2. Permission system
3. WordPress integration

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 0.1 | 2025-12-21 | Claude + Human | Initial MCP specification |
