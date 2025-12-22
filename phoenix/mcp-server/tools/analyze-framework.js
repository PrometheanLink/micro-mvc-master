/**
 * Analyze the micro-MVC framework structure
 * Shows available routes, models, views, and PHOENIX components
 */

import fs from 'fs';
import path from 'path';

const FRAMEWORK_PATH = process.env.FRAMEWORK_PATH || path.resolve(process.cwd(), '../..');

async function analyzeFramework() {
  console.log('🔥 PHOENIX Framework Analysis');
  console.log('═'.repeat(60));

  try {
    // Routes
    console.log('\n📍 ROUTES');
    console.log('─'.repeat(40));

    const routesPath = path.join(FRAMEWORK_PATH, 'framework/config/routes.cfg');
    const routes = fs.readFileSync(routesPath, 'utf8').split(',').map(r => r.trim()).filter(r => r);

    routes.forEach(route => {
      const modelExists = fs.existsSync(path.join(FRAMEWORK_PATH, `framework/mvc/models/${route}.php`));
      const viewExists = fs.existsSync(path.join(FRAMEWORK_PATH, `framework/mvc/views/${route}.phtml`));
      const isPhoenix = route.includes('phoenix') || route.includes('dashboard');

      const status = modelExists && viewExists ? '✅' : '⚠️';
      const phoenix = isPhoenix ? ' 🔥' : '';

      console.log(`   ${status} /${route}${phoenix}`);
    });

    console.log(`\n   Total: ${routes.length} routes`);

    // Templates
    console.log('\n📐 PHOENIX TEMPLATES');
    console.log('─'.repeat(40));

    const templatesPath = path.join(FRAMEWORK_PATH, 'phoenix/templates');
    if (fs.existsSync(templatesPath)) {
      const templates = fs.readdirSync(templatesPath).filter(f =>
        fs.existsSync(path.join(templatesPath, f, 'template.json'))
      );

      templates.forEach(template => {
        const configPath = path.join(templatesPath, template, 'template.json');
        const config = JSON.parse(fs.readFileSync(configPath, 'utf8'));
        const slots = Object.keys(config.slots || {}).length;

        console.log(`   📄 ${template}`);
        console.log(`      ${config.description || 'No description'}`);
        console.log(`      Slots: ${slots}`);
      });

      console.log(`\n   Total: ${templates.length} templates`);
    } else {
      console.log('   No templates found');
    }

    // Widgets
    console.log('\n🧩 PHOENIX WIDGETS');
    console.log('─'.repeat(40));

    const widgetsPath = path.join(FRAMEWORK_PATH, 'phoenix/widgets');
    if (fs.existsSync(widgetsPath)) {
      const widgets = fs.readdirSync(widgetsPath).filter(f =>
        fs.existsSync(path.join(widgetsPath, f, 'widget.json'))
      );

      widgets.forEach(widget => {
        const configPath = path.join(widgetsPath, widget, 'widget.json');
        const config = JSON.parse(fs.readFileSync(configPath, 'utf8'));

        console.log(`   🔹 ${widget}`);
        console.log(`      ${config.description || 'No description'}`);
      });

      console.log(`\n   Total: ${widgets.length} widgets`);
    } else {
      console.log('   No widgets found');
    }

    // Assets
    console.log('\n📦 PHOENIX ASSETS');
    console.log('─'.repeat(40));

    const assetsPath = path.join(FRAMEWORK_PATH, 'phoenix/assets');
    if (fs.existsSync(assetsPath)) {
      const cssPath = path.join(assetsPath, 'css');
      const jsPath = path.join(assetsPath, 'js');

      if (fs.existsSync(cssPath)) {
        const cssFiles = fs.readdirSync(cssPath);
        cssFiles.forEach(f => console.log(`   🎨 css/${f}`));
      }

      if (fs.existsSync(jsPath)) {
        const jsFiles = fs.readdirSync(jsPath);
        jsFiles.forEach(f => console.log(`   ⚡ js/${f}`));
      }
    }

    // Summary
    console.log('\n' + '═'.repeat(60));
    console.log('📊 SUMMARY');
    console.log(`   Framework: micro-MVC`);
    console.log(`   Path: ${FRAMEWORK_PATH}`);
    console.log(`   PHOENIX: Ready ✅`);
    console.log('');

  } catch (error) {
    console.log(`❌ Error: ${error.message}`);
    process.exit(1);
  }
}

analyzeFramework();
