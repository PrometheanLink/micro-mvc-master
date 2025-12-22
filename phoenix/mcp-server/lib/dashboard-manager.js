/**
 * Dashboard Manager - Multi-dashboard configuration management
 * Handles CRUD operations for PHOENIX dashboards
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { dirname } from 'path';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const DATA_DIR = path.join(__dirname, '..', 'data');
const DASHBOARDS_FILE = path.join(DATA_DIR, 'dashboards.json');

class DashboardManager {
  constructor() {
    this.dashboards = [];
    this.activeDashboardId = null;
    this._ensureDataDirs();
  }

  /**
   * Ensure data directories exist
   */
  _ensureDataDirs() {
    if (!fs.existsSync(DATA_DIR)) {
      fs.mkdirSync(DATA_DIR, { recursive: true });
    }
  }

  /**
   * Generate unique ID
   */
  _generateId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
  }

  /**
   * Load all dashboards from disk
   */
  async load() {
    if (fs.existsSync(DASHBOARDS_FILE)) {
      const data = JSON.parse(fs.readFileSync(DASHBOARDS_FILE, 'utf8'));
      this.dashboards = data.dashboards || [];
      this.activeDashboardId = data.activeDashboardId || null;
    }
    return this;
  }

  /**
   * Save dashboards to disk
   */
  async save() {
    const data = {
      version: '1.0.0',
      activeDashboardId: this.activeDashboardId,
      dashboards: this.dashboards
    };

    fs.writeFileSync(DASHBOARDS_FILE, JSON.stringify(data, null, 2));
    return this;
  }

  /**
   * Create a new dashboard
   */
  async create({
    name,
    route,
    template,
    theme = 'dark',
    description = '',
    config = {}
  }) {
    // Validate required fields
    if (!name || !route || !template) {
      throw new Error('Missing required fields: name, route, template');
    }

    // Check if route already exists
    if (this.dashboards.find(d => d.route === route)) {
      throw new Error(`Dashboard with route '${route}' already exists`);
    }

    const id = this._generateId();

    const dashboard = {
      id,
      name,
      route,
      template,
      theme,
      description,
      config,
      widgets: [],
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    };

    this.dashboards.push(dashboard);

    // If first dashboard, make it active
    if (this.dashboards.length === 1) {
      this.activeDashboardId = id;
    }

    await this.save();

    console.log(`✅ Dashboard created: ${name} (/${route})`);
    return dashboard;
  }

  /**
   * Get dashboard by ID
   */
  get(dashboardId) {
    return this.dashboards.find(d => d.id === dashboardId);
  }

  /**
   * Get dashboard by route
   */
  getByRoute(route) {
    return this.dashboards.find(d => d.route === route);
  }

  /**
   * Get all dashboards
   */
  getAll() {
    return this.dashboards;
  }

  /**
   * Get active dashboard
   */
  getActive() {
    if (!this.activeDashboardId) return null;
    return this.get(this.activeDashboardId);
  }

  /**
   * Activate a dashboard
   */
  async activate(dashboardId) {
    const dashboard = this.get(dashboardId);
    if (!dashboard) {
      throw new Error(`Dashboard not found: ${dashboardId}`);
    }

    this.activeDashboardId = dashboardId;
    await this.save();

    console.log(`✅ Activated dashboard: ${dashboard.name}`);
    return dashboard;
  }

  /**
   * Update dashboard
   */
  async update(dashboardId, updates) {
    const dashboard = this.get(dashboardId);
    if (!dashboard) {
      throw new Error(`Dashboard not found: ${dashboardId}`);
    }

    // Allowed updates
    const allowedFields = ['name', 'theme', 'description', 'config'];
    allowedFields.forEach(field => {
      if (updates[field] !== undefined) {
        dashboard[field] = updates[field];
      }
    });

    dashboard.updated_at = new Date().toISOString();
    await this.save();

    console.log(`✅ Dashboard updated: ${dashboard.name}`);
    return dashboard;
  }

  /**
   * Add widget to dashboard
   */
  async addWidget(dashboardId, {
    widgetType,
    slot,
    config = {},
    position = null
  }) {
    const dashboard = this.get(dashboardId);
    if (!dashboard) {
      throw new Error(`Dashboard not found: ${dashboardId}`);
    }

    const widget = {
      id: this._generateId(),
      type: widgetType,
      slot,
      config,
      position: position ?? dashboard.widgets.length,
      created_at: new Date().toISOString()
    };

    dashboard.widgets.push(widget);
    dashboard.updated_at = new Date().toISOString();
    await this.save();

    console.log(`✅ Widget added: ${widgetType} to ${slot}`);
    return widget;
  }

  /**
   * Remove widget from dashboard
   */
  async removeWidget(dashboardId, widgetId) {
    const dashboard = this.get(dashboardId);
    if (!dashboard) {
      throw new Error(`Dashboard not found: ${dashboardId}`);
    }

    const widgetIndex = dashboard.widgets.findIndex(w => w.id === widgetId);
    if (widgetIndex === -1) {
      throw new Error(`Widget not found: ${widgetId}`);
    }

    dashboard.widgets.splice(widgetIndex, 1);
    dashboard.updated_at = new Date().toISOString();
    await this.save();

    console.log(`✅ Widget removed: ${widgetId}`);
    return { success: true };
  }

  /**
   * Update widget configuration
   */
  async updateWidget(dashboardId, widgetId, config) {
    const dashboard = this.get(dashboardId);
    if (!dashboard) {
      throw new Error(`Dashboard not found: ${dashboardId}`);
    }

    const widget = dashboard.widgets.find(w => w.id === widgetId);
    if (!widget) {
      throw new Error(`Widget not found: ${widgetId}`);
    }

    widget.config = { ...widget.config, ...config };
    dashboard.updated_at = new Date().toISOString();
    await this.save();

    console.log(`✅ Widget updated: ${widgetId}`);
    return widget;
  }

  /**
   * Reorder widgets in a slot
   */
  async reorderWidgets(dashboardId, slot, widgetIds) {
    const dashboard = this.get(dashboardId);
    if (!dashboard) {
      throw new Error(`Dashboard not found: ${dashboardId}`);
    }

    widgetIds.forEach((widgetId, index) => {
      const widget = dashboard.widgets.find(w => w.id === widgetId && w.slot === slot);
      if (widget) {
        widget.position = index;
      }
    });

    dashboard.updated_at = new Date().toISOString();
    await this.save();

    console.log(`✅ Widgets reordered in ${slot}`);
    return dashboard.widgets.filter(w => w.slot === slot);
  }

  /**
   * Delete dashboard
   */
  async delete(dashboardId) {
    const dashboardIndex = this.dashboards.findIndex(d => d.id === dashboardId);
    if (dashboardIndex === -1) {
      throw new Error(`Dashboard not found: ${dashboardId}`);
    }

    const dashboard = this.dashboards[dashboardIndex];
    this.dashboards.splice(dashboardIndex, 1);

    // If this was active, set new active
    if (this.activeDashboardId === dashboardId) {
      this.activeDashboardId = this.dashboards.length > 0 ? this.dashboards[0].id : null;
    }

    await this.save();

    console.log(`✅ Dashboard deleted: ${dashboard.name}`);
    return { success: true, message: `Dashboard '${dashboard.name}' deleted` };
  }

  /**
   * Export dashboard as JSON
   */
  export(dashboardId) {
    const dashboard = this.get(dashboardId);
    if (!dashboard) {
      throw new Error(`Dashboard not found: ${dashboardId}`);
    }

    return JSON.stringify(dashboard, null, 2);
  }

  /**
   * Import dashboard from JSON
   */
  async import(jsonData, newRoute = null) {
    const data = typeof jsonData === 'string' ? JSON.parse(jsonData) : jsonData;

    // Generate new ID and optionally new route
    const dashboard = {
      ...data,
      id: this._generateId(),
      route: newRoute || `${data.route}-copy`,
      name: newRoute ? data.name : `${data.name} (Copy)`,
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString()
    };

    // Generate new IDs for widgets
    dashboard.widgets = dashboard.widgets.map(w => ({
      ...w,
      id: this._generateId()
    }));

    this.dashboards.push(dashboard);
    await this.save();

    console.log(`✅ Dashboard imported: ${dashboard.name}`);
    return dashboard;
  }
}

// Singleton instance
const dashboardManager = new DashboardManager();

export default dashboardManager;
