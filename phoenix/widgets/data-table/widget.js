/**
 * PHOENIX Data Table Widget
 */
(function() {
    'use strict';

    class PhoenixDataTable {
        constructor(element) {
            this.element = element;
            this.config = JSON.parse(element.dataset.config || '{}');
            this.table = element.querySelector('.data-table');
            this.tbody = this.table?.querySelector('tbody');
            this.searchInput = element.querySelector('.search-input');
            this.selectAllCheckbox = element.querySelector('.select-all');

            this.data = this.config.data || [];
            this.filteredData = [...this.data];
            this.sortColumn = null;
            this.sortDirection = 'asc';
            this.currentPage = 1;
            this.perPage = this.config.pagination?.perPage || 10;

            this.init();
        }

        init() {
            this.bindEvents();
        }

        bindEvents() {
            // Search
            if (this.searchInput) {
                this.searchInput.addEventListener('input', (e) => {
                    this.search(e.target.value);
                });
            }

            // Sort headers
            this.element.querySelectorAll('th.sortable').forEach(th => {
                th.addEventListener('click', () => {
                    this.sort(th.dataset.key);
                });
            });

            // Select all
            if (this.selectAllCheckbox) {
                this.selectAllCheckbox.addEventListener('change', (e) => {
                    this.selectAll(e.target.checked);
                });
            }

            // Pagination
            this.element.querySelectorAll('.page-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const page = btn.dataset.page;
                    if (page === 'prev') {
                        this.goToPage(this.currentPage - 1);
                    } else if (page === 'next') {
                        this.goToPage(this.currentPage + 1);
                    } else {
                        this.goToPage(parseInt(page));
                    }
                });
            });

            // Row actions
            this.element.querySelectorAll('.row-action-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const row = e.target.closest('tr');
                    const action = btn.title.toLowerCase();
                    this.element.dispatchEvent(new CustomEvent('phoenix:table:action', {
                        detail: { action, row }
                    }));
                });
            });
        }

        search(query) {
            query = query.toLowerCase().trim();

            if (!query) {
                this.filteredData = [...this.data];
            } else {
                const searchableColumns = this.config.columns
                    .filter(c => c.searchable !== false)
                    .map(c => c.key);

                this.filteredData = this.data.filter(row => {
                    return searchableColumns.some(key => {
                        const value = String(row[key] || '').toLowerCase();
                        return value.includes(query);
                    });
                });
            }

            this.currentPage = 1;
            this.render();
        }

        sort(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }

            this.filteredData.sort((a, b) => {
                const aVal = a[column];
                const bVal = b[column];

                if (aVal === bVal) return 0;

                let result;
                if (typeof aVal === 'number' && typeof bVal === 'number') {
                    result = aVal - bVal;
                } else {
                    result = String(aVal).localeCompare(String(bVal));
                }

                return this.sortDirection === 'desc' ? -result : result;
            });

            // Update sort icons
            this.element.querySelectorAll('th.sortable').forEach(th => {
                th.classList.remove('sorted', 'sorted-asc', 'sorted-desc');
                if (th.dataset.key === column) {
                    th.classList.add('sorted', `sorted-${this.sortDirection}`);
                    th.querySelector('.sort-icon').textContent =
                        this.sortDirection === 'asc' ? '↑' : '↓';
                } else {
                    th.querySelector('.sort-icon').textContent = '↕';
                }
            });

            this.render();
        }

        selectAll(checked) {
            this.element.querySelectorAll('.select-row').forEach(cb => {
                cb.checked = checked;
            });
        }

        getSelectedRows() {
            const selected = [];
            this.element.querySelectorAll('.select-row:checked').forEach(cb => {
                selected.push(cb.closest('tr'));
            });
            return selected;
        }

        goToPage(page) {
            const totalPages = Math.ceil(this.filteredData.length / this.perPage);
            this.currentPage = Math.max(1, Math.min(page, totalPages));
            this.render();
        }

        render() {
            // Pagination calculations
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            const pageData = this.filteredData.slice(start, end);

            // Update pagination info
            const pageStart = this.element.querySelector('.page-start');
            const pageEnd = this.element.querySelector('.page-end');
            const totalRows = this.element.querySelector('.total-rows');

            if (pageStart) pageStart.textContent = start + 1;
            if (pageEnd) pageEnd.textContent = Math.min(end, this.filteredData.length);
            if (totalRows) totalRows.textContent = this.filteredData.length;

            // Re-render is complex - for now just update counts
            // Full re-render would replace tbody content
        }

        updateData(newData) {
            this.data = newData;
            this.filteredData = [...newData];
            this.currentPage = 1;
            this.render();
        }
    }

    // Initialize
    function initTables() {
        document.querySelectorAll('.phoenix-data-table').forEach(el => {
            if (!el._phoenixWidget) {
                el._phoenixWidget = new PhoenixDataTable(el);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTables);
    } else {
        initTables();
    }

    window.PhoenixDataTable = PhoenixDataTable;
})();
