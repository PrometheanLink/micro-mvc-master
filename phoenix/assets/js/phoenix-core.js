/**
 * PHOENIX Core JavaScript
 *
 * Core utilities, event system, and widget management for PHOENIX.
 */

(function() {
    'use strict';

    // ============================================
    // PHOENIX GLOBAL NAMESPACE
    // ============================================

    window.Phoenix = window.Phoenix || {};

    // ============================================
    // CONFIGURATION
    // ============================================

    Phoenix.config = {
        apiBase: '/phoenix/api',
        refreshInterval: 30000,
        animationDuration: 300,
        debug: false
    };

    // ============================================
    // LOGGING
    // ============================================

    Phoenix.log = function(message, ...args) {
        if (Phoenix.config.debug) {
            console.log(`[PHOENIX] ${message}`, ...args);
        }
    };

    Phoenix.error = function(message, ...args) {
        console.error(`[PHOENIX] ${message}`, ...args);
    };

    // ============================================
    // EVENT SYSTEM
    // ============================================

    Phoenix.events = {
        listeners: {},

        on(event, callback) {
            if (!this.listeners[event]) {
                this.listeners[event] = [];
            }
            this.listeners[event].push(callback);
            return () => this.off(event, callback);
        },

        off(event, callback) {
            if (this.listeners[event]) {
                this.listeners[event] = this.listeners[event].filter(cb => cb !== callback);
            }
        },

        emit(event, data) {
            Phoenix.log(`Event: ${event}`, data);
            if (this.listeners[event]) {
                this.listeners[event].forEach(callback => {
                    try {
                        callback(data);
                    } catch (e) {
                        Phoenix.error(`Event handler error: ${event}`, e);
                    }
                });
            }
        }
    };

    // ============================================
    // WIDGET REGISTRY
    // ============================================

    Phoenix.widgets = {
        registry: new Map(),
        instances: new Map(),

        register(name, WidgetClass) {
            this.registry.set(name, WidgetClass);
            Phoenix.log(`Widget registered: ${name}`);
        },

        create(element) {
            const widgetType = element.dataset.widget;
            if (!widgetType) return null;

            const WidgetClass = this.registry.get(widgetType);
            if (!WidgetClass) {
                Phoenix.log(`Widget class not found: ${widgetType}`);
                return null;
            }

            const instance = new WidgetClass(element);
            this.instances.set(element.id, instance);
            return instance;
        },

        get(id) {
            return this.instances.get(id);
        },

        destroy(id) {
            const instance = this.instances.get(id);
            if (instance && typeof instance.destroy === 'function') {
                instance.destroy();
            }
            this.instances.delete(id);
        },

        initAll() {
            document.querySelectorAll('[data-widget]').forEach(element => {
                if (!element._phoenixWidget) {
                    const instance = this.create(element);
                    if (instance) {
                        element._phoenixWidget = instance;
                    }
                }
            });
        }
    };

    // Alias
    Phoenix.registerWidget = (name, cls) => Phoenix.widgets.register(name, cls);

    // ============================================
    // API CLIENT
    // ============================================

    Phoenix.api = {
        async request(endpoint, options = {}) {
            const url = `${Phoenix.config.apiBase}${endpoint}`;
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json'
                }
            };

            try {
                const response = await fetch(url, { ...defaultOptions, ...options });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'API request failed');
                }

                return data;
            } catch (error) {
                Phoenix.error(`API error: ${endpoint}`, error);
                throw error;
            }
        },

        get(endpoint) {
            return this.request(endpoint, { method: 'GET' });
        },

        post(endpoint, data) {
            return this.request(endpoint, {
                method: 'POST',
                body: JSON.stringify(data)
            });
        }
    };

    // ============================================
    // WIDGET DATA FETCHING
    // ============================================

    Phoenix.fetchWidgetData = async function(widgetId, dataConfig) {
        return Phoenix.api.post(`/widget/${widgetId}/data`, dataConfig);
    };

    // ============================================
    // THEME MANAGEMENT
    // ============================================

    Phoenix.theme = {
        current: 'dark',

        set(theme) {
            const page = document.querySelector('.phoenix-page, .phoenix-template');
            if (!page) return;

            // Remove old theme classes
            const themes = ['dark', 'light', 'cyber', 'matrix', 'sunset', 'ocean'];
            themes.forEach(t => page.classList.remove(`theme-${t}`));

            // Add new theme class
            page.classList.add(`theme-${theme}`);
            this.current = theme;

            // Emit event
            Phoenix.events.emit('theme:changed', { theme });

            // Store preference
            localStorage.setItem('phoenix-theme', theme);

            Phoenix.log(`Theme changed to: ${theme}`);
        },

        get() {
            return this.current;
        },

        toggle() {
            const newTheme = this.current === 'dark' ? 'light' : 'dark';
            this.set(newTheme);
        },

        init() {
            const stored = localStorage.getItem('phoenix-theme');
            if (stored) {
                this.set(stored);
            }
        }
    };

    // ============================================
    // UTILITIES
    // ============================================

    Phoenix.utils = {
        // Generate unique ID
        uniqueId(prefix = 'phoenix') {
            return `${prefix}-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
        },

        // Debounce function
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // Throttle function
        throttle(func, limit) {
            let inThrottle;
            return function executedFunction(...args) {
                if (!inThrottle) {
                    func(...args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        },

        // Format number with commas
        formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },

        // Format currency
        formatCurrency(num, currency = 'USD') {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency
            }).format(num);
        },

        // Format date
        formatDate(date, format = 'short') {
            const d = new Date(date);
            if (format === 'short') {
                return d.toLocaleDateString();
            }
            if (format === 'long') {
                return d.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }
            if (format === 'relative') {
                return this.timeAgo(d);
            }
            return d.toISOString();
        },

        // Time ago
        timeAgo(date) {
            const seconds = Math.floor((new Date() - date) / 1000);

            const intervals = {
                year: 31536000,
                month: 2592000,
                week: 604800,
                day: 86400,
                hour: 3600,
                minute: 60
            };

            for (const [unit, secondsInUnit] of Object.entries(intervals)) {
                const interval = Math.floor(seconds / secondsInUnit);
                if (interval >= 1) {
                    return `${interval} ${unit}${interval > 1 ? 's' : ''} ago`;
                }
            }

            return 'just now';
        },

        // Animate number
        animateNumber(element, target, duration = 500) {
            const start = parseFloat(element.textContent.replace(/,/g, '')) || 0;
            const startTime = performance.now();

            const animate = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const easeOut = 1 - Math.pow(1 - progress, 3);
                const current = Math.round(start + (target - start) * easeOut);

                element.textContent = this.formatNumber(current);

                if (progress < 1) {
                    requestAnimationFrame(animate);
                }
            };

            requestAnimationFrame(animate);
        }
    };

    // ============================================
    // NOTIFICATIONS
    // ============================================

    Phoenix.notify = {
        container: null,

        init() {
            if (this.container) return;

            this.container = document.createElement('div');
            this.container.className = 'phoenix-notifications';
            this.container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: var(--phoenix-z-notification, 1200);
                display: flex;
                flex-direction: column;
                gap: 10px;
            `;
            document.body.appendChild(this.container);
        },

        show(message, type = 'info', duration = 5000) {
            this.init();

            const notification = document.createElement('div');
            notification.className = `phoenix-notification phoenix-notification-${type}`;
            notification.style.cssText = `
                padding: 16px 20px;
                border-radius: 12px;
                background: var(--phoenix-bg-card);
                border: 1px solid var(--phoenix-border-color);
                backdrop-filter: blur(20px);
                color: var(--phoenix-text-primary);
                font-size: 0.9rem;
                box-shadow: var(--phoenix-shadow);
                animation: phoenix-slide-in 0.3s ease;
                cursor: pointer;
            `;

            const colors = {
                success: 'var(--phoenix-success)',
                error: 'var(--phoenix-danger)',
                warning: 'var(--phoenix-warning)',
                info: 'var(--phoenix-info)'
            };

            notification.style.borderLeftColor = colors[type] || colors.info;
            notification.style.borderLeftWidth = '4px';
            notification.textContent = message;

            notification.addEventListener('click', () => {
                notification.remove();
            });

            this.container.appendChild(notification);

            if (duration > 0) {
                setTimeout(() => {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateX(20px)';
                    setTimeout(() => notification.remove(), 300);
                }, duration);
            }

            return notification;
        },

        success(message, duration) {
            return this.show(message, 'success', duration);
        },

        error(message, duration) {
            return this.show(message, 'error', duration);
        },

        warning(message, duration) {
            return this.show(message, 'warning', duration);
        },

        info(message, duration) {
            return this.show(message, 'info', duration);
        }
    };

    // ============================================
    // INITIALIZATION
    // ============================================

    Phoenix.init = function() {
        Phoenix.log('Initializing PHOENIX...');

        // Initialize theme
        Phoenix.theme.init();

        // Initialize all widgets
        Phoenix.widgets.initAll();

        // Emit ready event
        Phoenix.events.emit('ready');

        Phoenix.log('PHOENIX initialized');
    };

    // Auto-init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', Phoenix.init);
    } else {
        Phoenix.init();
    }

    // Re-init on dynamic content
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1 && node.dataset?.widget) {
                    Phoenix.widgets.create(node);
                }
            });
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });

})();
