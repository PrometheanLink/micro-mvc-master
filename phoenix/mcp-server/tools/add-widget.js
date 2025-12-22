/**
 * Add a widget to a PHOENIX dashboard
 * Usage: Receives args via TOOL_ARGS environment variable
 */

import dashboardManager from '../lib/dashboard-manager.js';
import fs from 'fs';
import path from 'path';

const FRAMEWORK_PATH = process.env.FRAMEWORK_PATH || path.resolve(process.cwd(), '../..');

async function addWidget() {
  console.log('🔥 PHOENIX Add Widget');
  console.log('═'.repeat(50));

  // Parse args from environment
  const args = JSON.parse(process.env.TOOL_ARGS || '{}');

  const {
    dashboard,  // Dashboard route or ID
    widget,     // Widget type (e.g., 'stats-card')
    slot,       // Slot name (e.g., 'stats')
    config = {} // Widget configuration
  } = args;

  if (!dashboard || !widget || !slot) {
    console.log('❌ Error: dashboard, widget, and slot are required');
    console.log('');
    console.log('Example:');
    console.log('  {');
    console.log('    "dashboard": "my-dashboard",');
    console.log('    "widget": "stats-card",');
    console.log('    "slot": "stats",');
    console.log('    "config": { "title": "Total Users", "value": 1234 }');
    console.log('  }');
    process.exit(1);
  }

  try {
    await dashboardManager.load();

    // Find dashboard by route or ID
    let db = dashboardManager.getByRoute(dashboard) || dashboardManager.get(dashboard);

    if (!db) {
      console.log(`❌ Dashboard not found: ${dashboard}`);
      process.exit(1);
    }

    // Validate widget exists
    const widgetPath = path.join(FRAMEWORK_PATH, `phoenix/widgets/${widget}/widget.json`);
    if (!fs.existsSync(widgetPath)) {
      console.log(`❌ Widget not found: ${widget}`);
      console.log('');
      console.log('Available widgets:');

      const widgetsDir = path.join(FRAMEWORK_PATH, 'phoenix/widgets');
      const widgets = fs.readdirSync(widgetsDir).filter(f =>
        fs.existsSync(path.join(widgetsDir, f, 'widget.json'))
      );
      widgets.forEach(w => console.log(`   • ${w}`));
      process.exit(1);
    }

    // Load widget schema for validation
    const widgetSchema = JSON.parse(fs.readFileSync(widgetPath, 'utf8'));

    // Add widget to dashboard
    const addedWidget = await dashboardManager.addWidget(db.id, {
      widgetType: widget,
      slot,
      config
    });

    console.log('');
    console.log(`✅ Widget added successfully!`);
    console.log(`   📊 Widget: ${widget}`);
    console.log(`   📍 Slot: ${slot}`);
    console.log(`   🆔 Widget ID: ${addedWidget.id}`);
    console.log('');

    // Show widget config
    console.log('Configuration:');
    Object.entries(config).forEach(([key, value]) => {
      console.log(`   ${key}: ${JSON.stringify(value)}`);
    });

    console.log('');
    console.log('═'.repeat(50));
    console.log(`Dashboard now has ${db.widgets.length} widget(s)`);

  } catch (error) {
    console.log(`❌ Error: ${error.message}`);
    process.exit(1);
  }
}

addWidget();
