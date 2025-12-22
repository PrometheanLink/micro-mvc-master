/**
 * PHOENIX Progress Bar Widget
 */
(function() {
    'use strict';

    class PhoenixProgressBar {
        constructor(element) {
            this.element = element;
            this.config = JSON.parse(element.dataset.config || '{}');
            this.fill = element.querySelector('.progress-fill');
            this.percentageEl = element.querySelector('.progress-percentage');
            this.valueEl = element.querySelector('.progress-value');

            this.init();
        }

        init() {
            // Animate initial fill
            if (this.fill) {
                const targetWidth = this.fill.style.width;
                this.fill.style.width = '0%';
                requestAnimationFrame(() => {
                    this.fill.style.width = targetWidth;
                });
            }

            // Listen for updates
            this.element.addEventListener('phoenix:widget:update', (e) => {
                if (e.detail?.data?.value !== undefined) {
                    this.updateValue(e.detail.data.value);
                }
            });
        }

        updateValue(value) {
            const max = this.config.max || 100;
            const percentage = Math.min(100, Math.max(0, (value / max) * 100));

            if (this.fill) {
                this.fill.style.width = percentage + '%';
                this.fill.dataset.value = percentage;
            }

            if (this.percentageEl) {
                this.animateNumber(this.percentageEl, Math.round(percentage), '%');
            }

            if (this.valueEl) {
                this.valueEl.textContent = `${value} / ${max}`;
            }
        }

        animateNumber(element, target, suffix = '') {
            const current = parseInt(element.textContent) || 0;
            const duration = 500;
            const start = performance.now();

            const animate = (now) => {
                const elapsed = now - start;
                const progress = Math.min(elapsed / duration, 1);
                const value = Math.round(current + (target - current) * progress);
                element.textContent = value + suffix;

                if (progress < 1) {
                    requestAnimationFrame(animate);
                }
            };

            requestAnimationFrame(animate);
        }
    }

    // Initialize
    function initProgressBars() {
        document.querySelectorAll('.phoenix-progress-bar').forEach(el => {
            if (!el._phoenixWidget) {
                el._phoenixWidget = new PhoenixProgressBar(el);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProgressBars);
    } else {
        initProgressBars();
    }

    window.PhoenixProgressBar = PhoenixProgressBar;
})();
