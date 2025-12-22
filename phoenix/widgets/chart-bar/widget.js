/**
 * PHOENIX Bar Chart Widget
 */
(function() {
    'use strict';

    class PhoenixChartBar {
        constructor(element) {
            this.element = element;
            this.config = JSON.parse(element.dataset.config || '{}');
            this.canvas = element.querySelector('canvas');
            this.chart = null;

            this.init();
        }

        init() {
            if (typeof Chart !== 'undefined') {
                this.initChartJS();
            } else {
                this.initNativeChart();
            }
        }

        initChartJS() {
            const ctx = this.canvas.getContext('2d');
            const colors = ['#00d4ff', '#7b2cbf', '#ff006e', '#00ff88', '#ff6b35', '#ffd60a'];

            const datasets = (this.config.datasets || []).map((ds, i) => ({
                label: ds.label || `Dataset ${i + 1}`,
                data: ds.data || [],
                backgroundColor: ds.color || colors[i % colors.length],
                borderColor: ds.color || colors[i % colors.length],
                borderWidth: 0,
                borderRadius: 6,
            }));

            this.chart = new Chart(ctx, {
                type: this.config.horizontal ? 'bar' : 'bar',
                data: {
                    labels: this.config.labels || [],
                    datasets: datasets
                },
                options: {
                    indexAxis: this.config.horizontal ? 'y' : 'x',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            stacked: this.config.stacked,
                            grid: {
                                display: this.config.showGrid !== false,
                                color: 'rgba(255, 255, 255, 0.05)'
                            },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.5)'
                            }
                        },
                        y: {
                            stacked: this.config.stacked,
                            grid: {
                                display: this.config.showGrid !== false,
                                color: 'rgba(255, 255, 255, 0.05)'
                            },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.5)'
                            }
                        }
                    }
                }
            });
        }

        initNativeChart() {
            // Fallback: Create CSS-based bars
            const body = this.element.querySelector('.chart-body');
            if (!body) return;

            const labels = this.config.labels || [];
            const data = this.config.datasets?.[0]?.data || [];
            const maxValue = Math.max(...data);

            let html = '<div class="chart-bars">';
            labels.forEach((label, i) => {
                const value = data[i] || 0;
                const height = (value / maxValue) * 100;
                html += `
                    <div class="chart-bar-item">
                        <div class="chart-bar-fill" style="height: ${height}%" data-value="${value}"></div>
                        <div class="chart-bar-label">${label}</div>
                    </div>
                `;
            });
            html += '</div>';

            body.innerHTML = html;
        }

        updateData(newData) {
            if (this.chart) {
                this.chart.data.datasets.forEach((ds, i) => {
                    if (newData[i]) {
                        ds.data = newData[i];
                    }
                });
                this.chart.update();
            }
        }

        destroy() {
            if (this.chart) {
                this.chart.destroy();
            }
        }
    }

    // Initialize
    function initCharts() {
        document.querySelectorAll('.phoenix-chart-bar').forEach(el => {
            if (!el._phoenixWidget) {
                el._phoenixWidget = new PhoenixChartBar(el);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCharts);
    } else {
        initCharts();
    }

    window.PhoenixChartBar = PhoenixChartBar;
})();
