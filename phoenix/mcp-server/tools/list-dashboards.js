/**
 * List all PHOENIX dashboards
 */

import dashboardManager from '../lib/dashboard-manager.js';

async function listDashboards() {
  console.log('🔥 PHOENIX Dashboards');
  console.log('═'.repeat(50));

  try {
    await dashboardManager.load();

    const dashboards = dashboardManager.getAll();
    const active = dashboardManager.getActive();

    if (dashboards.length === 0) {
      console.log('');
      console.log('   No dashboards created yet.');
      console.log('   Use create-dashboard to create one!');
      console.log('');
      return;
    }

    console.log('');
    dashboards.forEach(dashboard => {
      const isActive = active && active.id === dashboard.id;
      const status = isActive ? '🟢' : '⚪';

      console.log(`${status} ${dashboard.name}`);
      console.log(`   Route: /en/${dashboard.route}`);
      console.log(`   Template: ${dashboard.template}`);
      console.log(`   Theme: ${dashboard.theme}`);
      console.log(`   Widgets: ${dashboard.widgets.length}`);
      console.log(`   Created: ${dashboard.created_at}`);
      console.log('');
    });

    console.log('═'.repeat(50));
    console.log(`Total: ${dashboards.length} dashboard(s)`);

  } catch (error) {
    console.log(`❌ Error: ${error.message}`);
    process.exit(1);
  }
}

listDashboards();
