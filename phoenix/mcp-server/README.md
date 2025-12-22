# PHOENIX MCP Server

AI-powered dashboard builder using the Model Context Protocol (MCP).

## Quick Start

### 1. Install Dependencies

```bash
cd phoenix/mcp-server
npm install
```

### 2. Build the Server

```bash
npm run build
```

### 3. Configure Claude Desktop

Add to your Claude Desktop configuration file:

**Windows:** `%APPDATA%\Claude\claude_desktop_config.json`
**macOS:** `~/Library/Application Support/Claude/claude_desktop_config.json`

```json
{
  "mcpServers": {
    "phoenix-builder": {
      "command": "node",
      "args": ["C:/path/to/micro-MVC-master/phoenix/mcp-server/dist/server.js"]
    }
  }
}
```

### 4. Restart Claude Desktop

After adding the configuration, restart Claude Desktop to load the PHOENIX tools.

## Available Tools

### Page Management

| Tool | Description |
|------|-------------|
| `create_page` | Create a new page from a template |
| `list_pages` | List all pages in the application |
| `delete_page` | Delete a page and its files |

### Widget Management

| Tool | Description |
|------|-------------|
| `add_widget` | Add a widget to a page slot |
| `list_widgets` | List available widgets |
| `get_widget_info` | Get widget configuration schema |

### Template Management

| Tool | Description |
|------|-------------|
| `list_templates` | List available templates |
| `get_template_info` | Get template details and slots |

## Usage Examples

### Create a Dashboard

```
"Create a new dashboard page called 'sales-dashboard' with the title 'Sales Dashboard'"
```

Claude will use:
```json
{
  "tool": "create_page",
  "arguments": {
    "template": "dashboard",
    "title": "Sales Dashboard",
    "route": "sales-dashboard",
    "theme": "dark"
  }
}
```

### Add Widgets

```
"Add a stats card showing total revenue to the stats slot"
```

Claude will use:
```json
{
  "tool": "add_widget",
  "arguments": {
    "page": "sales-dashboard",
    "widget": "stats-card",
    "slot": "stats",
    "config": {
      "title": "Total Revenue",
      "value": "$125,000",
      "icon": "money",
      "color": "green",
      "trend": {
        "direction": "up",
        "value": "+12%",
        "period": "vs last month"
      }
    }
  }
}
```

## Templates Available

- `dashboard` - Stats and metrics overview
- `data-table` - Tabular data with CRUD
- `form-page` - Data entry forms
- `kanban` - Drag-and-drop board
- `calendar` - Event scheduling
- `gallery` - Media grid
- `profile` - User profile
- `auth` - Login/register
- `landing` - Marketing page
- `wizard` - Multi-step form
- `chat` - Messaging interface
- `timeline` - Activity history
- `cards-grid` - Card display
- `split-view` - Master-detail
- `blank` - Empty canvas
- `report` - Print-ready reports

## Widgets Available

### Display
- `stats-card` - Single statistic with trend
- `chart-bar` - Bar chart
- `chart-line` - Line chart
- `chart-pie` - Pie chart
- `progress-bar` - Horizontal progress
- `progress-ring` - Circular progress
- `activity-feed` - Activity list
- `info-card` - Information card
- `alert-box` - Alert/notice

### Data
- `data-table` - Sortable table

### Layout
- `page-header` - Page title bar

## Development

### Run in Development Mode

```bash
npm run dev
```

### Test Tools

```bash
echo '{"jsonrpc": "2.0", "id": 1, "method": "tools/list"}' | node dist/server.js
```

## Troubleshooting

### Server Not Connecting

1. Check the path in `claude_desktop_config.json` is correct
2. Ensure the server is built (`npm run build`)
3. Check Claude Desktop logs for errors
4. Restart Claude Desktop

### Tools Not Appearing

1. The server must be running for tools to appear
2. Check that PHP is installed and in PATH
3. Verify the framework path is correct

## License

MIT
