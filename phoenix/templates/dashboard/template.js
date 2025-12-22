/**
 * PHOENIX Dashboard Template JavaScript
 *
 * Handles template-level interactions, particle effects,
 * theme switching, and widget coordination.
 */

(function() {
    'use strict';

    // Dashboard Template Controller
    class PhoenixDashboard {
        constructor(element) {
            this.element = element;
            this.config = JSON.parse(element.dataset.config || '{}');
            this.widgets = new Map();
            this.theme = this.config.theme || 'dark';

            this.init();
        }

        init() {
            // Initialize particles if enabled
            if (this.config.features?.particles !== false) {
                this.initParticles();
            }

            // Initialize widgets
            this.initWidgets();

            // Set up theme switcher
            this.setupThemeSwitcher();

            // Set up auto-refresh if enabled
            if (this.config.data?.refresh?.enabled) {
                this.startAutoRefresh();
            }

            console.log('PHOENIX Dashboard initialized');
        }

        // Initialize particle background
        initParticles() {
            const container = this.element.querySelector('#phoenix-particles');
            if (!container) return;

            // Create particles
            const particleCount = 50;
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'phoenix-particle';
                particle.style.cssText = `
                    position: absolute;
                    width: ${Math.random() * 4 + 1}px;
                    height: ${Math.random() * 4 + 1}px;
                    background: var(--accent-primary);
                    border-radius: 50%;
                    left: ${Math.random() * 100}%;
                    top: ${Math.random() * 100}%;
                    opacity: ${Math.random() * 0.5 + 0.1};
                    animation: phoenix-particle-float ${Math.random() * 10 + 10}s linear infinite;
                    animation-delay: -${Math.random() * 10}s;
                `;
                container.appendChild(particle);
            }

            // Add particle animation
            if (!document.querySelector('#phoenix-particle-styles')) {
                const style = document.createElement('style');
                style.id = 'phoenix-particle-styles';
                style.textContent = `
                    @keyframes phoenix-particle-float {
                        0%, 100% {
                            transform: translateY(0) translateX(0);
                        }
                        25% {
                            transform: translateY(-30px) translateX(10px);
                        }
                        50% {
                            transform: translateY(-60px) translateX(-10px);
                        }
                        75% {
                            transform: translateY(-30px) translateX(20px);
                        }
                    }
                `;
                document.head.appendChild(style);
            }
        }

        // Initialize all widgets in the dashboard
        initWidgets() {
            const widgetElements = this.element.querySelectorAll('[data-widget]');
            widgetElements.forEach(el => {
                const widgetType = el.dataset.widget;
                const widgetId = el.id;

                // Store reference
                this.widgets.set(widgetId, {
                    element: el,
                    type: widgetType,
                    config: JSON.parse(el.dataset.config || '{}')
                });

                // Trigger widget init event
                el.dispatchEvent(new CustomEvent('phoenix:widget:init', {
                    bubbles: true,
                    detail: { type: widgetType, id: widgetId }
                }));
            });
        }

        // Theme switching
        setupThemeSwitcher() {
            // Listen for theme change events
            document.addEventListener('phoenix:theme:change', (e) => {
                this.setTheme(e.detail.theme);
            });

            // Expose global function
            window.phoenixSetTheme = (theme) => this.setTheme(theme);
        }

        setTheme(theme) {
            // Remove old theme class
            const themeClasses = ['theme-dark', 'theme-light', 'theme-cyber', 'theme-matrix', 'theme-sunset', 'theme-ocean'];
            themeClasses.forEach(cls => this.element.classList.remove(cls));

            // Add new theme class
            this.element.classList.add(`theme-${theme}`);
            this.theme = theme;

            // Notify widgets
            this.widgets.forEach((widget, id) => {
                widget.element.dispatchEvent(new CustomEvent('phoenix:theme:changed', {
                    detail: { theme }
                }));
            });

            console.log(`PHOENIX theme changed to: ${theme}`);
        }

        // Auto-refresh functionality
        startAutoRefresh() {
            const interval = (this.config.data.refresh.interval || 30) * 1000;

            setInterval(() => {
                this.refreshWidgets();
            }, interval);
        }

        async refreshWidgets() {
            // Notify all widgets to refresh
            this.widgets.forEach((widget, id) => {
                widget.element.dispatchEvent(new CustomEvent('phoenix:widget:refresh'));
            });
        }

        // Get widget by ID
        getWidget(id) {
            return this.widgets.get(id);
        }

        // Update widget data
        updateWidget(id, data) {
            const widget = this.widgets.get(id);
            if (widget) {
                widget.element.dispatchEvent(new CustomEvent('phoenix:widget:update', {
                    detail: { data }
                }));
            }
        }
    }

    // Initialize dashboard when DOM is ready
    function initDashboards() {
        const dashboards = document.querySelectorAll('.phoenix-dashboard');
        dashboards.forEach(el => {
            if (!el._phoenixDashboard) {
                el._phoenixDashboard = new PhoenixDashboard(el);
            }
        });
    }

    // Initialize on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboards);
    } else {
        initDashboards();
    }

    // Expose for external use
    window.PhoenixDashboard = PhoenixDashboard;

})();
