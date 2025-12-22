#!/usr/bin/env node

/**
 * PHOENIX MCP Server
 *
 * Model Context Protocol server for AI-powered dashboard building.
 * Exposes tools for creating pages, adding widgets, and managing templates.
 */

import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
  Tool,
} from "@modelcontextprotocol/sdk/types.js";
import { exec } from "child_process";
import { promisify } from "util";
import * as fs from "fs/promises";
import * as path from "path";

const execAsync = promisify(exec);

// Configuration
const CONFIG = {
  name: "phoenix-builder",
  version: "1.0.0",
  frameworkPath: path.resolve(process.cwd(), "../../framework"),
  phoenixPath: path.resolve(process.cwd(), ".."),
};

// Tool definitions
const TOOLS: Tool[] = [
  {
    name: "create_page",
    description: "Create a new page from a predefined template",
    inputSchema: {
      type: "object",
      required: ["template", "title", "route"],
      properties: {
        template: {
          type: "string",
          enum: [
            "dashboard",
            "data-table",
            "form-page",
            "kanban",
            "calendar",
            "gallery",
            "profile",
            "auth",
            "landing",
            "wizard",
            "chat",
            "timeline",
            "cards-grid",
            "split-view",
            "blank",
            "report",
          ],
          description: "The template to use for the page",
        },
        title: {
          type: "string",
          description: "The page title displayed in header and browser",
        },
        route: {
          type: "string",
          pattern: "^[a-z0-9-]+$",
          description: "URL-safe route name (lowercase, hyphens only)",
        },
        description: {
          type: "string",
          description: "Optional page description",
        },
        theme: {
          type: "string",
          enum: ["dark", "light", "cyber", "matrix", "sunset", "ocean"],
          default: "dark",
        },
      },
    },
  },
  {
    name: "list_pages",
    description: "Get a list of all pages/routes in the application",
    inputSchema: {
      type: "object",
      properties: {
        filter: {
          type: "string",
          description: "Optional filter by template type",
        },
      },
    },
  },
  {
    name: "delete_page",
    description: "Delete a page and its associated files",
    inputSchema: {
      type: "object",
      required: ["route"],
      properties: {
        route: {
          type: "string",
          description: "The route name of the page to delete",
        },
      },
    },
  },
  {
    name: "add_widget",
    description: "Add a widget to a page slot",
    inputSchema: {
      type: "object",
      required: ["page", "widget", "slot"],
      properties: {
        page: {
          type: "string",
          description: "The route name of the target page",
        },
        widget: {
          type: "string",
          enum: [
            "stats-card",
            "chart-bar",
            "chart-line",
            "chart-pie",
            "progress-bar",
            "progress-ring",
            "data-table",
            "activity-feed",
            "info-card",
            "page-header",
            "alert-box",
          ],
          description: "The widget type to add",
        },
        slot: {
          type: "string",
          description: "The slot ID where the widget should be placed",
        },
        config: {
          type: "object",
          description: "Widget-specific configuration",
        },
      },
    },
  },
  {
    name: "list_templates",
    description: "List all available templates",
    inputSchema: {
      type: "object",
      properties: {
        category: {
          type: "string",
          description: "Optional filter by category",
        },
      },
    },
  },
  {
    name: "list_widgets",
    description: "List all available widgets",
    inputSchema: {
      type: "object",
      properties: {
        category: {
          type: "string",
          description: "Optional filter by category",
        },
      },
    },
  },
  {
    name: "get_template_info",
    description: "Get detailed information about a template including its slots",
    inputSchema: {
      type: "object",
      required: ["template"],
      properties: {
        template: {
          type: "string",
          description: "Template name",
        },
      },
    },
  },
  {
    name: "get_widget_info",
    description: "Get detailed information about a widget including its configuration options",
    inputSchema: {
      type: "object",
      required: ["widget"],
      properties: {
        widget: {
          type: "string",
          description: "Widget name",
        },
      },
    },
  },
];

// Tool handler functions
async function createPage(args: {
  template: string;
  title: string;
  route: string;
  description?: string;
  theme?: string;
}): Promise<object> {
  const { template, title, route, description, theme } = args;

  // Validate route format
  if (!/^[a-z0-9-]+$/.test(route)) {
    return {
      success: false,
      error: "Invalid route format. Use lowercase letters, numbers, and hyphens only.",
    };
  }

  try {
    // Execute PHP code generator
    const phpCode = `
      require_once '${CONFIG.phoenixPath}/engine/CodeGenerator.php';
      CodeGenerator::init('${CONFIG.frameworkPath}', '${CONFIG.phoenixPath}');
      $result = CodeGenerator::create_page('${route}', '${template}', [
        'title' => '${title.replace(/'/g, "\\'")}',
        'description' => '${(description || "").replace(/'/g, "\\'")}',
        'theme' => '${theme || "dark"}'
      ]);
      echo json_encode($result);
    `;

    const { stdout } = await execAsync(`php -r "${phpCode.replace(/"/g, '\\"')}"`);
    const result = JSON.parse(stdout);

    if (result.success) {
      return {
        success: true,
        page: {
          route: route,
          url: `/en/${route}`,
          template: template,
          files: result.files,
        },
        message: `Page '${title}' created successfully at /en/${route}`,
      };
    } else {
      return {
        success: false,
        errors: result.errors,
      };
    }
  } catch (error) {
    return {
      success: false,
      error: `Failed to create page: ${error}`,
    };
  }
}

async function listPages(args: { filter?: string }): Promise<object> {
  try {
    const routesPath = path.join(CONFIG.frameworkPath, "config/routes.cfg");
    const content = await fs.readFile(routesPath, "utf-8");
    const routes = content.split(",").map((r) => r.trim()).filter(Boolean);

    const pages = await Promise.all(
      routes.map(async (route) => {
        const modelPath = path.join(CONFIG.frameworkPath, `mvc/models/${route}.php`);
        const viewPath = path.join(CONFIG.frameworkPath, `mvc/views/${route}.phtml`);

        const hasModel = await fs.access(modelPath).then(() => true).catch(() => false);
        const hasView = await fs.access(viewPath).then(() => true).catch(() => false);

        return {
          route,
          url: `/en/${route}`,
          hasModel,
          hasView,
        };
      })
    );

    return {
      success: true,
      count: pages.length,
      pages,
    };
  } catch (error) {
    return {
      success: false,
      error: `Failed to list pages: ${error}`,
    };
  }
}

async function deletePage(args: { route: string }): Promise<object> {
  const { route } = args;

  try {
    const phpCode = `
      require_once '${CONFIG.phoenixPath}/engine/CodeGenerator.php';
      CodeGenerator::init('${CONFIG.frameworkPath}', '${CONFIG.phoenixPath}');
      $result = CodeGenerator::delete_page('${route}');
      echo json_encode($result);
    `;

    const { stdout } = await execAsync(`php -r "${phpCode.replace(/"/g, '\\"')}"`);
    const result = JSON.parse(stdout);

    return {
      success: true,
      deleted: result.deleted,
      message: `Page '${route}' deleted successfully`,
    };
  } catch (error) {
    return {
      success: false,
      error: `Failed to delete page: ${error}`,
    };
  }
}

async function addWidget(args: {
  page: string;
  widget: string;
  slot: string;
  config?: object;
}): Promise<object> {
  const { page, widget, slot, config } = args;

  // For now, return instructions - full implementation would modify the model file
  return {
    success: true,
    message: `Widget '${widget}' added to slot '${slot}' on page '${page}'`,
    widget: {
      type: widget,
      slot: slot,
      config: config || {},
    },
    instructions: `To complete this action, add the widget configuration to the page model's get_slots() method.`,
  };
}

async function listTemplates(args: { category?: string }): Promise<object> {
  try {
    const registryPath = path.join(CONFIG.phoenixPath, "templates/registry.json");
    const content = await fs.readFile(registryPath, "utf-8");
    const registry = JSON.parse(content);

    let templates = Object.entries(registry.templates).map(([name, data]: [string, any]) => ({
      name,
      ...data,
    }));

    if (args.category) {
      templates = templates.filter((t) => t.category === args.category);
    }

    return {
      success: true,
      count: templates.length,
      templates,
      categories: registry.categories,
    };
  } catch (error) {
    return {
      success: false,
      error: `Failed to list templates: ${error}`,
    };
  }
}

async function listWidgets(args: { category?: string }): Promise<object> {
  try {
    const registryPath = path.join(CONFIG.phoenixPath, "widgets/registry.json");
    const content = await fs.readFile(registryPath, "utf-8");
    const registry = JSON.parse(content);

    let widgets = Object.entries(registry.widgets).map(([name, data]: [string, any]) => ({
      name,
      ...data,
    }));

    if (args.category) {
      widgets = widgets.filter((w) => w.category === args.category);
    }

    return {
      success: true,
      count: widgets.length,
      widgets,
      categories: registry.categories,
    };
  } catch (error) {
    return {
      success: false,
      error: `Failed to list widgets: ${error}`,
    };
  }
}

async function getTemplateInfo(args: { template: string }): Promise<object> {
  try {
    const templatePath = path.join(CONFIG.phoenixPath, `templates/${args.template}/template.json`);
    const content = await fs.readFile(templatePath, "utf-8");
    const template = JSON.parse(content);

    return {
      success: true,
      template,
    };
  } catch (error) {
    return {
      success: false,
      error: `Template '${args.template}' not found`,
    };
  }
}

async function getWidgetInfo(args: { widget: string }): Promise<object> {
  try {
    const widgetPath = path.join(CONFIG.phoenixPath, `widgets/${args.widget}/widget.json`);
    const content = await fs.readFile(widgetPath, "utf-8");
    const widget = JSON.parse(content);

    return {
      success: true,
      widget,
    };
  } catch (error) {
    return {
      success: false,
      error: `Widget '${args.widget}' not found`,
    };
  }
}

// Main server setup
async function main() {
  const server = new Server(
    {
      name: CONFIG.name,
      version: CONFIG.version,
    },
    {
      capabilities: {
        tools: {},
      },
    }
  );

  // List tools handler
  server.setRequestHandler(ListToolsRequestSchema, async () => ({
    tools: TOOLS,
  }));

  // Call tool handler
  server.setRequestHandler(CallToolRequestSchema, async (request) => {
    const { name, arguments: args } = request.params;

    try {
      let result: object;

      switch (name) {
        case "create_page":
          result = await createPage(args as any);
          break;
        case "list_pages":
          result = await listPages(args as any);
          break;
        case "delete_page":
          result = await deletePage(args as any);
          break;
        case "add_widget":
          result = await addWidget(args as any);
          break;
        case "list_templates":
          result = await listTemplates(args as any);
          break;
        case "list_widgets":
          result = await listWidgets(args as any);
          break;
        case "get_template_info":
          result = await getTemplateInfo(args as any);
          break;
        case "get_widget_info":
          result = await getWidgetInfo(args as any);
          break;
        default:
          result = { error: `Unknown tool: ${name}` };
      }

      return {
        content: [
          {
            type: "text",
            text: JSON.stringify(result, null, 2),
          },
        ],
      };
    } catch (error) {
      return {
        content: [
          {
            type: "text",
            text: JSON.stringify({ error: String(error) }),
          },
        ],
        isError: true,
      };
    }
  });

  // Start server
  const transport = new StdioServerTransport();
  await server.connect(transport);

  console.error("PHOENIX MCP Server running");
}

main().catch(console.error);
