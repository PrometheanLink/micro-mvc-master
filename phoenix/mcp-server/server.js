/**
 * PHOENIX MCP Control Panel Server
 * AI-powered dashboard builder for micro-MVC
 */

import express from 'express';
import cors from 'cors';
import path from 'path';
import dotenv from 'dotenv';
import fs from 'fs';
import { fileURLToPath } from 'url';
import { dirname } from 'path';
import { spawn } from 'child_process';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

dotenv.config();

const app = express();
const PORT = process.env.PHOENIX_PORT || 5678;

// Get framework path (parent of phoenix directory)
const FRAMEWORK_PATH = process.env.FRAMEWORK_PATH || path.resolve(__dirname, '../..');

app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, 'control-panel')));

// Serve phoenix assets
app.use('/phoenix', express.static(path.join(FRAMEWORK_PATH, 'phoenix')));

// ==========================================
// API Routes
// ==========================================

// Get configuration
app.get('/api/config', (req, res) => {
  res.json({
    framework_path: FRAMEWORK_PATH,
    phoenix_path: path.join(FRAMEWORK_PATH, 'phoenix'),
    templates_path: path.join(FRAMEWORK_PATH, 'phoenix/templates'),
    widgets_path: path.join(FRAMEWORK_PATH, 'phoenix/widgets'),
    port: PORT
  });
});

// List templates
app.get('/api/templates', (req, res) => {
  const templatesPath = path.join(FRAMEWORK_PATH, 'phoenix/templates');
  const registryPath = path.join(templatesPath, 'registry.json');

  try {
    const registry = JSON.parse(fs.readFileSync(registryPath, 'utf8'));

    // Enrich with actual template configs
    const templates = Object.entries(registry.templates).map(([id, meta]) => {
      const configPath = path.join(templatesPath, meta.path, 'template.json');
      let config = {};

      if (fs.existsSync(configPath)) {
        config = JSON.parse(fs.readFileSync(configPath, 'utf8'));
      }

      return {
        id,
        ...meta,
        slots: config.slots || {},
        features: config.features || {},
        themes: config.themes || []
      };
    });

    res.json({ templates, categories: registry.categories });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// List widgets
app.get('/api/widgets', (req, res) => {
  const widgetsPath = path.join(FRAMEWORK_PATH, 'phoenix/widgets');
  const registryPath = path.join(widgetsPath, 'registry.json');

  try {
    const registry = JSON.parse(fs.readFileSync(registryPath, 'utf8'));

    // Enrich with actual widget configs
    const widgets = Object.entries(registry.widgets).map(([id, meta]) => {
      const configPath = path.join(widgetsPath, id, 'widget.json');
      let config = {};

      if (fs.existsSync(configPath)) {
        config = JSON.parse(fs.readFileSync(configPath, 'utf8'));
      }

      return {
        id,
        ...meta,
        schema: config.config || {},
        preview: config.preview || null
      };
    });

    res.json({ widgets, categories: registry.categories });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// List existing pages
app.get('/api/pages', (req, res) => {
  const routesPath = path.join(FRAMEWORK_PATH, 'framework/config/routes.cfg');

  try {
    const routesContent = fs.readFileSync(routesPath, 'utf8');
    const routes = routesContent.split(',').map(r => r.trim()).filter(r => r);

    const pages = routes.map(route => {
      const modelPath = path.join(FRAMEWORK_PATH, `framework/mvc/models/${route}.php`);
      const viewPath = path.join(FRAMEWORK_PATH, `framework/mvc/views/${route}.phtml`);

      return {
        route,
        url: `/en/${route}`,
        hasModel: fs.existsSync(modelPath),
        hasView: fs.existsSync(viewPath),
        isPhoenix: route.includes('phoenix') || route.startsWith('dashboard')
      };
    });

    res.json({ pages });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// Get page details
app.get('/api/pages/:route', (req, res) => {
  const { route } = req.params;
  const modelPath = path.join(FRAMEWORK_PATH, `framework/mvc/models/${route}.php`);
  const viewPath = path.join(FRAMEWORK_PATH, `framework/mvc/views/${route}.phtml`);

  try {
    const result = {
      route,
      url: `/en/${route}`,
      model: null,
      view: null
    };

    if (fs.existsSync(modelPath)) {
      result.model = fs.readFileSync(modelPath, 'utf8');
    }

    if (fs.existsSync(viewPath)) {
      result.view = fs.readFileSync(viewPath, 'utf8');
    }

    res.json(result);
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// List available tools
app.get('/api/tools', (req, res) => {
  const toolsDir = path.join(__dirname, 'tools');

  try {
    if (!fs.existsSync(toolsDir)) {
      return res.json({ tools: [] });
    }

    const tools = fs.readdirSync(toolsDir)
      .filter(file => file.endsWith('.js'))
      .map(file => {
        const toolPath = path.join(toolsDir, file);
        const content = fs.readFileSync(toolPath, 'utf8');

        // Extract description from first comment
        const descMatch = content.match(/\/\*\*[\s\S]*?\*\s*(.+)/);
        const description = descMatch ? descMatch[1].trim() : file.replace('.js', '');

        return {
          name: file.replace('.js', ''),
          file,
          description
        };
      });

    res.json({ tools });
  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// Execute a tool
app.post('/api/tools/:tool', (req, res) => {
  const { tool } = req.params;
  const { args = {} } = req.body;

  const toolPath = path.join(__dirname, 'tools', `${tool}.js`);

  if (!fs.existsSync(toolPath)) {
    return res.status(404).json({ error: 'Tool not found' });
  }

  // Pass args as environment variables
  const env = {
    ...process.env,
    FRAMEWORK_PATH,
    TOOL_ARGS: JSON.stringify(args)
  };

  const proc = spawn('node', [toolPath], { env });
  let output = '';
  let errorOutput = '';

  proc.stdout.on('data', (data) => {
    output += data.toString();
  });

  proc.stderr.on('data', (data) => {
    errorOutput += data.toString();
  });

  proc.on('close', (code) => {
    res.json({
      success: code === 0,
      output,
      error: errorOutput,
      exitCode: code
    });
  });
});

// Create a new page
app.post('/api/pages', (req, res) => {
  const { route, template, title, theme = 'dark', config = {} } = req.body;

  if (!route || !template) {
    return res.status(400).json({ error: 'Missing required fields: route, template' });
  }

  try {
    const routesPath = path.join(FRAMEWORK_PATH, 'framework/config/routes.cfg');
    const modelPath = path.join(FRAMEWORK_PATH, `framework/mvc/models/${route}.php`);
    const viewPath = path.join(FRAMEWORK_PATH, `framework/mvc/views/${route}.phtml`);

    // Check if route already exists
    const existingRoutes = fs.readFileSync(routesPath, 'utf8').split(',').map(r => r.trim());
    if (existingRoutes.includes(route)) {
      return res.status(400).json({ error: 'Route already exists' });
    }

    // Add route
    const newRoutes = [...existingRoutes, route].join(',');
    fs.writeFileSync(routesPath, newRoutes);

    // Generate model
    const modelContent = generateModel(route, title, config);
    fs.writeFileSync(modelPath, modelContent);

    // Generate view
    const viewContent = generateView(route, template, title, theme, config);
    fs.writeFileSync(viewPath, viewContent);

    res.json({
      success: true,
      message: `Page created: ${route}`,
      url: `/en/${route}`
    });

  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// Delete a page
app.delete('/api/pages/:route', (req, res) => {
  const { route } = req.params;

  try {
    const routesPath = path.join(FRAMEWORK_PATH, 'framework/config/routes.cfg');
    const modelPath = path.join(FRAMEWORK_PATH, `framework/mvc/models/${route}.php`);
    const viewPath = path.join(FRAMEWORK_PATH, `framework/mvc/views/${route}.phtml`);
    const gatePath = path.join(FRAMEWORK_PATH, `framework/mvc/gates/${route}.php`);

    // Remove from routes
    const routes = fs.readFileSync(routesPath, 'utf8').split(',').map(r => r.trim());
    const newRoutes = routes.filter(r => r !== route).join(',');
    fs.writeFileSync(routesPath, newRoutes);

    // Delete files
    if (fs.existsSync(modelPath)) fs.unlinkSync(modelPath);
    if (fs.existsSync(viewPath)) fs.unlinkSync(viewPath);
    if (fs.existsSync(gatePath)) fs.unlinkSync(gatePath);

    res.json({ success: true, message: `Page deleted: ${route}` });

  } catch (error) {
    res.status(500).json({ error: error.message });
  }
});

// ==========================================
// Code Generators
// ==========================================

function generateModel(route, title, config) {
  const className = route.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join('') + '_Model';

  return `<?php
/**
 * ${title || route} - Generated by PHOENIX
 */

class ${className} {

    public static function Get_Data() {
        return [
            'title' => '${title || route}',
            'config' => ${JSON.stringify(config, null, 12).replace(/"/g, "'")},

            // Add your data here
            'items' => []
        ];
    }
}
?>
`;
}

function generateView(route, template, title, theme, config) {
  const templatePath = `phoenix/templates/${template}`;

  return `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title']) ?> - PHOENIX</title>

    <!-- PHOENIX Core -->
    <link rel="stylesheet" href="/phoenix/assets/css/phoenix-core.css">
    <link rel="stylesheet" href="/${templatePath}/template.css">
</head>
<body class="phoenix-page theme-${theme}">

    <?php
    // Load template
    $config = $data['config'];
    $slots = [];
    include dirname(dirname(__DIR__)) . '/${templatePath}/template.phtml';
    ?>

    <!-- PHOENIX Core JS -->
    <script src="/phoenix/assets/js/phoenix-core.js"></script>
    <script src="/${templatePath}/template.js"></script>

</body>
</html>
`;
}

// ==========================================
// Start Server
// ==========================================

app.listen(PORT, () => {
  console.log('');
  console.log('🔥 PHOENIX Control Panel');
  console.log('═'.repeat(60));
  console.log('');
  console.log(`   🌐 Control Panel: http://localhost:${PORT}`);
  console.log(`   📁 Framework: ${FRAMEWORK_PATH}`);
  console.log('');
  console.log('═'.repeat(60));
  console.log('');
  console.log('   Ready to build dashboards!');
  console.log('');
});
