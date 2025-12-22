#!/usr/bin/env node
/**
 * PHOENIX MCP Server
 * AI-Powered Platform Builder for micro-MVC
 *
 * This server implements the Model Context Protocol (MCP) to enable
 * Claude Code to create and manage pages, templates, and widgets
 * through natural language conversation.
 */

import { Server } from '@modelcontextprotocol/sdk/server/index.js';
import { StdioServerTransport } from '@modelcontextprotocol/sdk/server/stdio.js';
import {
  CallToolRequestSchema,
  ListToolsRequestSchema,
} from '@modelcontextprotocol/sdk/types.js';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Framework path (parent of phoenix directory)
const FRAMEWORK_PATH = process.env.FRAMEWORK_PATH || path.resolve(__dirname, '../..');

// Load tool schemas
const schemasPath = path.join(__dirname, 'schemas', 'tools.json');
let toolSchemas = {};
if (fs.existsSync(schemasPath)) {
  toolSchemas = JSON.parse(fs.readFileSync(schemasPath, 'utf8'));
}

// ============================================
// MCP Server Setup
// ============================================

const server = new Server(
  {
    name: 'phoenix',
    version: '1.0.0',
  },
  {
    capabilities: {
      tools: {},
    },
  }
);

// ============================================
// Tool Definitions
// ============================================

server.setRequestHandler(ListToolsRequestSchema, async () => {
  return {
    tools: [
      {
        name: 'phoenix_create_page',
        description: 'Create a new page with a template. Generates route, PHP model, and PHTML view files. Use this when the user wants to create a new dashboard, catalog, blog, gallery, or any other page type.',
        inputSchema: {
          type: 'object',
          properties: {
            name: {
              type: 'string',
              description: 'The display name/title of the page (e.g., "Sales Dashboard", "Product Catalog")'
            },
            route: {
              type: 'string',
              description: 'URL path for the page, lowercase with hyphens (e.g., "sales-dashboard", "my-products")'
            },
            template: {
              type: 'string',
              enum: ['dashboard', 'catalog', 'article', 'gallery', 'kanban', 'landing'],
              description: 'The template type to use for the page layout'
            },
            description: {
              type: 'string',
              description: 'Brief description of what this page is for'
            },
            theme: {
              type: 'string',
              enum: ['cyber', 'dark', 'industrial'],
              default: 'cyber',
              description: 'Visual theme for the page'
            },
            widgets: {
              type: 'array',
              items: {
                type: 'object',
                properties: {
                  type: { type: 'string' },
                  config: { type: 'object' }
                }
              },
              description: 'Array of widgets to include on the page'
            }
          },
          required: ['name', 'route', 'template']
        }
      },
      {
        name: 'phoenix_update_page',
        description: 'Update an existing page configuration, add widgets, or modify content.',
        inputSchema: {
          type: 'object',
          properties: {
            route: {
              type: 'string',
              description: 'The route/path of the page to update'
            },
            updates: {
              type: 'object',
              properties: {
                title: { type: 'string' },
                theme: { type: 'string' },
                addWidgets: { type: 'array' },
                removeWidgets: { type: 'array' },
                config: { type: 'object' }
              },
              description: 'The updates to apply to the page'
            }
          },
          required: ['route', 'updates']
        }
      },
      {
        name: 'phoenix_list_pages',
        description: 'List all pages in the PHOENIX system with their routes, types, and status.',
        inputSchema: {
          type: 'object',
          properties: {},
          required: []
        }
      },
      {
        name: 'phoenix_get_page',
        description: 'Get detailed information about a specific page including its model, view, and configuration.',
        inputSchema: {
          type: 'object',
          properties: {
            route: {
              type: 'string',
              description: 'The route/path of the page to retrieve'
            }
          },
          required: ['route']
        }
      },
      {
        name: 'phoenix_delete_page',
        description: 'Delete a page and its associated files (model, view, gate).',
        inputSchema: {
          type: 'object',
          properties: {
            route: {
              type: 'string',
              description: 'The route/path of the page to delete'
            }
          },
          required: ['route']
        }
      },
      {
        name: 'phoenix_list_templates',
        description: 'List all available page templates with their slot configurations.',
        inputSchema: {
          type: 'object',
          properties: {},
          required: []
        }
      },
      {
        name: 'phoenix_list_widgets',
        description: 'List all available widgets with their configuration schemas.',
        inputSchema: {
          type: 'object',
          properties: {
            category: {
              type: 'string',
              description: 'Filter widgets by category (e.g., "data", "content", "ecommerce")'
            }
          },
          required: []
        }
      },
      {
        name: 'phoenix_update_dashboard_config',
        description: 'Update the main dashboard configuration (title, subtitle, stats, chart, etc.)',
        inputSchema: {
          type: 'object',
          properties: {
            title: { type: 'string' },
            subtitle: { type: 'string' },
            theme: { type: 'string' },
            show_particles: { type: 'boolean' },
            stats: {
              type: 'array',
              items: {
                type: 'object',
                properties: {
                  title: { type: 'string' },
                  value: { type: 'number' },
                  icon: { type: 'string' },
                  color: { type: 'string' },
                  prefix: { type: 'string' },
                  suffix: { type: 'string' },
                  trend: { type: 'object' }
                }
              }
            },
            chart: { type: 'object' },
            progress: { type: 'array' },
            activities: { type: 'array' }
          },
          required: []
        }
      }
    ]
  };
});

// ============================================
// Tool Implementations
// ============================================

server.setRequestHandler(CallToolRequestSchema, async (request) => {
  const { name, arguments: args } = request.params;

  try {
    switch (name) {
      case 'phoenix_create_page':
        return await createPage(args);

      case 'phoenix_update_page':
        return await updatePage(args);

      case 'phoenix_list_pages':
        return await listPages();

      case 'phoenix_get_page':
        return await getPage(args);

      case 'phoenix_delete_page':
        return await deletePage(args);

      case 'phoenix_list_templates':
        return await listTemplates();

      case 'phoenix_list_widgets':
        return await listWidgets(args);

      case 'phoenix_update_dashboard_config':
        return await updateDashboardConfig(args);

      default:
        return {
          content: [{ type: 'text', text: `Unknown tool: ${name}` }],
          isError: true
        };
    }
  } catch (error) {
    return {
      content: [{ type: 'text', text: `Error: ${error.message}` }],
      isError: true
    };
  }
});

// ============================================
// Tool Functions
// ============================================

async function createPage({ name, route, template, description, theme = 'cyber', widgets = [] }) {
  const routesPath = path.join(FRAMEWORK_PATH, 'framework/config/routes.cfg');
  const modelPath = path.join(FRAMEWORK_PATH, `framework/mvc/models/${route}.php`);
  const viewPath = path.join(FRAMEWORK_PATH, `framework/mvc/views/${route}.phtml`);

  // Check if route already exists
  const existingRoutes = fs.readFileSync(routesPath, 'utf8').split(',').map(r => r.trim());
  if (existingRoutes.includes(route)) {
    return {
      content: [{ type: 'text', text: `Page "${route}" already exists. Use phoenix_update_page to modify it.` }],
      isError: true
    };
  }

  // Add route
  existingRoutes.push(route);
  fs.writeFileSync(routesPath, existingRoutes.join(','));

  // Generate model
  const className = route.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join('') + '_Model';
  const modelContent = generateModel(className, name, description, template, widgets);
  fs.writeFileSync(modelPath, modelContent);

  // Generate view
  const viewContent = generateView(name, template, theme);
  fs.writeFileSync(viewPath, viewContent);

  // Add to phoenix routes in site/index.phtml
  updatePhoenixRoutes(route);

  return {
    content: [{
      type: 'text',
      text: `✅ Page created successfully!

📄 **${name}**
🔗 URL: http://localhost:8888/en/${route}/
📐 Template: ${template}
🎨 Theme: ${theme}

Files created:
- framework/mvc/models/${route}.php
- framework/mvc/views/${route}.phtml
- Route added to routes.cfg

The page is now live and ready to view!`
    }]
  };
}

async function updatePage({ route, updates }) {
  const modelPath = path.join(FRAMEWORK_PATH, `framework/mvc/models/${route}.php`);

  if (!fs.existsSync(modelPath)) {
    return {
      content: [{ type: 'text', text: `Page "${route}" not found.` }],
      isError: true
    };
  }

  // For now, return info about what would be updated
  // Full implementation would parse and modify the PHP files
  return {
    content: [{
      type: 'text',
      text: `📝 Page update request for "${route}":
${JSON.stringify(updates, null, 2)}

To fully update this page, edit the model file at:
framework/mvc/models/${route}.php`
    }]
  };
}

async function listPages() {
  const routesPath = path.join(FRAMEWORK_PATH, 'framework/config/routes.cfg');
  const routesContent = fs.readFileSync(routesPath, 'utf8');
  const routes = routesContent.split(',').map(r => r.trim()).filter(r => r);

  const pages = routes.map(route => {
    const modelPath = path.join(FRAMEWORK_PATH, `framework/mvc/models/${route}.php`);
    const viewPath = path.join(FRAMEWORK_PATH, `framework/mvc/views/${route}.phtml`);

    let isPhoenix = route.includes('phoenix') ||
                    route.includes('dashboard') ||
                    route === 'root' ||
                    route === 'admin';

    // Check if view contains phoenix-page class
    if (fs.existsSync(viewPath)) {
      const viewContent = fs.readFileSync(viewPath, 'utf8');
      if (viewContent.includes('phoenix-page')) {
        isPhoenix = true;
      }
    }

    return {
      route,
      url: route === 'root' ? '/en/' : `/en/${route}/`,
      hasModel: fs.existsSync(modelPath),
      hasView: fs.existsSync(viewPath),
      isPhoenix
    };
  });

  const phoenixPages = pages.filter(p => p.isPhoenix);
  const otherPages = pages.filter(p => !p.isPhoenix);

  let output = `📄 **PHOENIX Pages** (${phoenixPages.length})\n`;
  phoenixPages.forEach(p => {
    output += `  • ${p.route} → ${p.url}\n`;
  });

  if (otherPages.length > 0) {
    output += `\n📁 **Other Pages** (${otherPages.length})\n`;
    otherPages.forEach(p => {
      output += `  • ${p.route}\n`;
    });
  }

  return {
    content: [{ type: 'text', text: output }]
  };
}

async function getPage({ route }) {
  const modelPath = path.join(FRAMEWORK_PATH, `framework/mvc/models/${route}.php`);
  const viewPath = path.join(FRAMEWORK_PATH, `framework/mvc/views/${route}.phtml`);

  if (!fs.existsSync(modelPath) && !fs.existsSync(viewPath)) {
    return {
      content: [{ type: 'text', text: `Page "${route}" not found.` }],
      isError: true
    };
  }

  let output = `📄 **Page: ${route}**\n\n`;

  if (fs.existsSync(modelPath)) {
    const modelContent = fs.readFileSync(modelPath, 'utf8');
    output += `**Model** (${route}.php):\n\`\`\`php\n${modelContent.substring(0, 1500)}${modelContent.length > 1500 ? '\n...(truncated)' : ''}\n\`\`\`\n\n`;
  }

  if (fs.existsSync(viewPath)) {
    const viewContent = fs.readFileSync(viewPath, 'utf8');
    output += `**View** (${route}.phtml):\n\`\`\`html\n${viewContent.substring(0, 1500)}${viewContent.length > 1500 ? '\n...(truncated)' : ''}\n\`\`\``;
  }

  return {
    content: [{ type: 'text', text: output }]
  };
}

async function deletePage({ route }) {
  // Protect critical pages
  const protectedRoutes = ['root', 'admin'];
  if (protectedRoutes.includes(route)) {
    return {
      content: [{ type: 'text', text: `Cannot delete protected page "${route}".` }],
      isError: true
    };
  }

  const routesPath = path.join(FRAMEWORK_PATH, 'framework/config/routes.cfg');
  const modelPath = path.join(FRAMEWORK_PATH, `framework/mvc/models/${route}.php`);
  const viewPath = path.join(FRAMEWORK_PATH, `framework/mvc/views/${route}.phtml`);
  const gatePath = path.join(FRAMEWORK_PATH, `framework/mvc/gates/${route}.php`);

  // Remove from routes
  const routes = fs.readFileSync(routesPath, 'utf8').split(',').map(r => r.trim());
  const newRoutes = routes.filter(r => r !== route);
  fs.writeFileSync(routesPath, newRoutes.join(','));

  // Delete files
  let deleted = [];
  if (fs.existsSync(modelPath)) { fs.unlinkSync(modelPath); deleted.push('model'); }
  if (fs.existsSync(viewPath)) { fs.unlinkSync(viewPath); deleted.push('view'); }
  if (fs.existsSync(gatePath)) { fs.unlinkSync(gatePath); deleted.push('gate'); }

  return {
    content: [{
      type: 'text',
      text: `🗑️ Page "${route}" deleted.\nRemoved: ${deleted.join(', ') || 'route only'}`
    }]
  };
}

async function listTemplates() {
  const templates = [
    { id: 'dashboard', name: 'Dashboard', description: 'Analytics, KPIs, charts, and metrics', slots: ['stats', 'chart', 'activity', 'progress'] },
    { id: 'catalog', name: 'Product Catalog', description: 'Grid layout for products or portfolios', slots: ['filters', 'products', 'pagination'] },
    { id: 'article', name: 'Article/Blog', description: 'Long-form content with sidebar', slots: ['header', 'content', 'sidebar', 'comments'] },
    { id: 'gallery', name: 'Media Gallery', description: 'Photo/video grid with lightbox', slots: ['album-header', 'media-grid'] },
    { id: 'kanban', name: 'Kanban Board', description: 'Project management columns', slots: ['board-header', 'columns'] },
    { id: 'landing', name: 'Landing Page', description: 'Marketing page with sections', slots: ['hero', 'features', 'testimonials', 'cta'] }
  ];

  let output = '📐 **Available Templates**\n\n';
  templates.forEach(t => {
    output += `**${t.name}** (\`${t.id}\`)\n`;
    output += `${t.description}\n`;
    output += `Slots: ${t.slots.join(', ')}\n\n`;
  });

  return {
    content: [{ type: 'text', text: output }]
  };
}

async function listWidgets({ category }) {
  const widgets = [
    { id: 'stat-card', category: 'data', name: 'Stat Card', description: 'Single metric with trend indicator' },
    { id: 'chart-bar', category: 'data', name: 'Bar Chart', description: 'Vertical or horizontal bar chart' },
    { id: 'chart-line', category: 'data', name: 'Line Chart', description: 'Time series line chart' },
    { id: 'data-table', category: 'data', name: 'Data Table', description: 'Sortable data table' },
    { id: 'progress-bar', category: 'data', name: 'Progress Bar', description: 'Progress indicator with label' },
    { id: 'activity-feed', category: 'social', name: 'Activity Feed', description: 'Recent activity list' },
    { id: 'product-card', category: 'ecommerce', name: 'Product Card', description: 'Product with image and price' },
    { id: 'text-block', category: 'content', name: 'Text Block', description: 'Rich text content' },
    { id: 'image-block', category: 'content', name: 'Image Block', description: 'Image with caption' }
  ];

  let filtered = category ? widgets.filter(w => w.category === category) : widgets;

  let output = '🧩 **Available Widgets**\n\n';
  filtered.forEach(w => {
    output += `• **${w.name}** (\`${w.id}\`) - ${w.description}\n`;
  });

  return {
    content: [{ type: 'text', text: output }]
  };
}

async function updateDashboardConfig(config) {
  const configPath = path.join(FRAMEWORK_PATH, 'phoenix/config/dashboard.json');

  // Load existing config
  let existingConfig = {};
  if (fs.existsSync(configPath)) {
    existingConfig = JSON.parse(fs.readFileSync(configPath, 'utf8'));
  }

  // Merge updates
  const newConfig = { ...existingConfig, ...config };

  // Save
  fs.writeFileSync(configPath, JSON.stringify(newConfig, null, 2));

  return {
    content: [{
      type: 'text',
      text: `✅ Dashboard configuration updated!

Changes applied:
${Object.keys(config).map(k => `• ${k}`).join('\n')}

View at: http://localhost:8888/en/`
    }]
  };
}

// ============================================
// Helper Functions
// ============================================

function generateModel(className, name, description, template, widgets) {
  const widgetsJson = JSON.stringify(widgets, null, 8).replace(/"/g, "'");

  return `<?php
/**
 * ${name}
 * ${description || 'Generated by PHOENIX'}
 * Template: ${template}
 */

if (!defined('micro_mvc'))
    exit();

class ${className}
{
    public static function Get_Data()
    {
        return [
            'phoenix' => true,
            'title' => '${name}',
            'template' => '${template}',
            'theme' => 'cyber',

            // Page data - customize as needed
            'stats' => [],
            'items' => [],
            'widgets' => ${widgetsJson}
        ];
    }
}
?>
`;
}

function generateView(name, template, theme) {
  return `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title']) ?> - PrometheanLink</title>
    <link rel="stylesheet" href="/phoenix/assets/css/phoenix-core.css">
</head>
<body class="phoenix-page theme-${theme}">

    <div class="phoenix-container" style="padding: 40px;">
        <header style="text-align: center; margin-bottom: 40px;">
            <h1 class="gradient-text" style="font-size: 2.5rem; font-weight: 800;">
                <?= htmlspecialchars($data['title']) ?>
            </h1>
            <p style="color: var(--brand-text-secondary);">
                Template: ${template} | Theme: ${theme}
            </p>
            <a href="/en/admin/" class="btn btn-primary" style="margin-top: 20px;">
                Open Control Panel
            </a>
        </header>

        <main>
            <!-- Add your ${template} content here -->
            <div class="card" style="padding: 40px; text-align: center;">
                <p style="font-size: 1.2rem; color: var(--brand-text-secondary);">
                    This page was generated by PHOENIX.
                </p>
                <p style="color: var(--brand-text-tertiary); margin-top: 10px;">
                    Edit the model and view files to customize this page.
                </p>
            </div>
        </main>
    </div>

    <script src="/phoenix/assets/js/phoenix-core.js"></script>
</body>
</html>
`;
}

function updatePhoenixRoutes(route) {
  const indexPath = path.join(FRAMEWORK_PATH, 'site/index.phtml');

  if (!fs.existsSync(indexPath)) return;

  let content = fs.readFileSync(indexPath, 'utf8');

  // Find the phoenix_routes array and add the new route
  const routesMatch = content.match(/\$phoenix_routes\s*=\s*\[([^\]]+)\]/);
  if (routesMatch) {
    const existingRoutes = routesMatch[1];
    if (!existingRoutes.includes(`'${route}'`)) {
      const newRoutes = existingRoutes.trim() + `, '${route}'`;
      content = content.replace(routesMatch[0], `$phoenix_routes = [${newRoutes}]`);
      fs.writeFileSync(indexPath, content);
    }
  }
}

// ============================================
// Start Server
// ============================================

async function main() {
  const transport = new StdioServerTransport();
  await server.connect(transport);
  console.error('🔥 PHOENIX MCP Server running');
}

main().catch(console.error);
