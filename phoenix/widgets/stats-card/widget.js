/**
 * PHOENIX Stats Card Widget JavaScript
 *
 * Handles value animations, data loading, and refresh functionality.
 */

(function() {
    'use strict';

    class PhoenixStatsCard {
        constructor(element) {
            this.element = element;
            this.config = JSON.parse(element.dataset.config || '{}');
            this.valueElement = element.querySelector('.value-number');
            this.animate = element.dataset.animate === 'true';
            this.refreshInterval = null;

            this.init();
        }

        init() {
            // Load data if configured
            if (this.config.data?.type === 'query' || this.config.data?.type === 'api') {
                this.loadData();
            }

            // Start refresh if configured
            if (this.config.refresh?.enabled) {
                this.startRefresh();
            }

            // Listen for refresh events
            this.element.addEventListener('phoenix:widget:refresh', () => {
                this.loadData();
            });

            // Listen for update events
            this.element.addEventListener('phoenix:widget:update', (e) => {
                if (e.detail?.data?.value !== undefined) {
                    this.updateValue(e.detail.data.value);
                }
            });
        }

        // Load data from configured source
        async loadData() {
            if (!this.config.data) return;

            try {
                const widgetId = this.element.id;
                const response = await fetch(`/phoenix/api/widget/${widgetId}/data`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.config.data)
                });

                const result = await response.json();
                if (result.success && result.data?.value !== undefined) {
                    this.updateValue(result.data.value);
                }
            } catch (error) {
                console.error('Stats card data load failed:', error);
            }
        }

        // Update displayed value with animation
        updateValue(newValue) {
            if (!this.valueElement) return;

            const currentValue = this.parseValue(this.valueElement.textContent);
            const targetValue = this.parseValue(newValue);

            if (this.animate && !isNaN(currentValue) && !isNaN(targetValue)) {
                this.animateValue(currentValue, targetValue);
            } else {
                this.valueElement.textContent = this.formatValue(newValue);
            }

            this.valueElement.dataset.value = newValue;
        }

        // Parse value to number
        parseValue(value) {
            if (typeof value === 'number') return value;
            // Remove commas and parse
            return parseFloat(String(value).replace(/,/g, ''));
        }

        // Format value for display
        formatValue(value) {
            if (typeof value === 'number') {
                return value.toLocaleString();
            }
            return value;
        }

        // Animate value change
        animateValue(start, end, duration = 500) {
            const startTime = performance.now();
            const valueEl = this.valueElement;

            // Add updating class
            valueEl.classList.add('updating');

            const animate = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                // Ease out cubic
                const easeOut = 1 - Math.pow(1 - progress, 3);
                const current = Math.round(start + (end - start) * easeOut);

                valueEl.textContent = current.toLocaleString();

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    valueEl.textContent = end.toLocaleString();
                    valueEl.classList.remove('updating');
                }
            };

            requestAnimationFrame(animate);
        }

        // Start auto-refresh
        startRefresh() {
            const interval = (this.config.refresh.interval || 30) * 1000;

            this.refreshInterval = setInterval(() => {
                this.loadData();
            }, interval);
        }

        // Stop auto-refresh
        stopRefresh() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
                this.refreshInterval = null;
            }
        }

        // Cleanup
        destroy() {
            this.stopRefresh();
        }
    }

    // Initialize all stats cards
    function initStatsCards() {
        const cards = document.querySelectorAll('.phoenix-stats-card');
        cards.forEach(el => {
            if (!el._phoenixWidget) {
                el._phoenixWidget = new PhoenixStatsCard(el);
            }
        });
    }

    // Initialize on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initStatsCards);
    } else {
        initStatsCards();
    }

    // Re-initialize on widget:init event
    document.addEventListener('phoenix:widget:init', (e) => {
        if (e.detail?.type === 'stats-card') {
            const el = document.getElementById(e.detail.id);
            if (el && !el._phoenixWidget) {
                el._phoenixWidget = new PhoenixStatsCard(el);
            }
        }
    });

    // Expose for external use
    window.PhoenixStatsCard = PhoenixStatsCard;

})();
