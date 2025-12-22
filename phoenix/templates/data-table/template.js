/**
 * PHOENIX Data Table Template
 * Full-page data management with CRUD operations
 */

class PhoenixDataTableTemplate {
    constructor(options = {}) {
        this.options = {
            rowsPerPage: 25,
            enableSearch: true,
            enableBulkActions: true,
            ...options
        };

        this.currentPage = 1;
        this.totalRows = 0;
        this.selectedRows = new Set();

        this.init();
    }

    init() {
        this.bindElements();
        this.bindEvents();
        this.initKeyboardShortcuts();

        console.log('📊 PHOENIX Data Table Template initialized');
    }

    bindElements() {
        this.searchInput = document.getElementById('table-search');
        this.bulkActionsEl = document.getElementById('bulk-actions');
        this.selectedCountEl = document.getElementById('selected-count');
        this.prevBtn = document.getElementById('prev-page');
        this.nextBtn = document.getElementById('next-page');
        this.pageNumbersEl = document.getElementById('page-numbers');
        this.rowsPerPageSelect = document.getElementById('rows-per-page');
        this.showingStartEl = document.getElementById('showing-start');
        this.showingEndEl = document.getElementById('showing-end');
        this.totalRowsEl = document.getElementById('total-rows');
    }

    bindEvents() {
        // Search
        if (this.searchInput) {
            this.searchInput.addEventListener('input', Phoenix.utils.debounce(() => {
                this.handleSearch(this.searchInput.value);
            }, 300));
        }

        // Pagination
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.goToPage(this.currentPage - 1));
        }

        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.goToPage(this.currentPage + 1));
        }

        // Rows per page
        if (this.rowsPerPageSelect) {
            this.rowsPerPageSelect.addEventListener('change', (e) => {
                this.options.rowsPerPage = parseInt(e.target.value);
                this.currentPage = 1;
                this.refresh();
            });
        }

        // Create button
        const createBtn = document.getElementById('btn-create');
        if (createBtn) {
            createBtn.addEventListener('click', () => this.handleCreate());
        }

        // Export button
        const exportBtn = document.getElementById('btn-export');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.handleExport());
        }

        // Filters button
        const filtersBtn = document.getElementById('btn-filters');
        if (filtersBtn) {
            filtersBtn.addEventListener('click', () => this.toggleFilters());
        }

        // Listen for row selection events from data-table widget
        Phoenix.on('table:selection', (data) => {
            this.updateSelection(data.selected);
        });

        // Listen for data updates
        Phoenix.on('table:data', (data) => {
            this.totalRows = data.total || 0;
            this.updatePagination();
        });
    }

    initKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + F for search focus
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                if (this.searchInput) {
                    e.preventDefault();
                    this.searchInput.focus();
                }
            }

            // Escape to clear selection
            if (e.key === 'Escape') {
                this.clearSelection();
            }

            // Ctrl/Cmd + A to select all
            if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
                if (document.activeElement !== this.searchInput) {
                    e.preventDefault();
                    this.selectAll();
                }
            }
        });
    }

    handleSearch(query) {
        this.currentPage = 1;
        Phoenix.emit('table:search', { query });
    }

    handleCreate() {
        Phoenix.emit('table:create');
    }

    handleExport() {
        const formats = ['CSV', 'Excel', 'JSON', 'PDF'];

        // Simple export dialog
        const format = prompt('Export format: ' + formats.join(', '));
        if (format && formats.includes(format.toUpperCase())) {
            Phoenix.emit('table:export', { format: format.toUpperCase() });
        }
    }

    toggleFilters() {
        Phoenix.emit('filters:toggle');
    }

    updateSelection(selected) {
        this.selectedRows = new Set(selected);
        const count = this.selectedRows.size;

        if (this.selectedCountEl) {
            this.selectedCountEl.textContent = count;
        }

        if (this.bulkActionsEl) {
            this.bulkActionsEl.style.display = count > 0 ? 'flex' : 'none';
        }
    }

    clearSelection() {
        this.selectedRows.clear();
        this.updateSelection([]);
        Phoenix.emit('table:clearSelection');
    }

    selectAll() {
        Phoenix.emit('table:selectAll');
    }

    goToPage(page) {
        const totalPages = Math.ceil(this.totalRows / this.options.rowsPerPage);

        if (page < 1 || page > totalPages) return;

        this.currentPage = page;
        this.updatePagination();
        Phoenix.emit('table:page', { page });
    }

    updatePagination() {
        const totalPages = Math.ceil(this.totalRows / this.options.rowsPerPage);
        const start = ((this.currentPage - 1) * this.options.rowsPerPage) + 1;
        const end = Math.min(this.currentPage * this.options.rowsPerPage, this.totalRows);

        // Update summary
        if (this.showingStartEl) this.showingStartEl.textContent = start;
        if (this.showingEndEl) this.showingEndEl.textContent = end;
        if (this.totalRowsEl) this.totalRowsEl.textContent = this.totalRows;

        // Update buttons
        if (this.prevBtn) this.prevBtn.disabled = this.currentPage === 1;
        if (this.nextBtn) this.nextBtn.disabled = this.currentPage === totalPages;

        // Update page numbers
        if (this.pageNumbersEl) {
            this.pageNumbersEl.innerHTML = '';

            const maxVisible = 5;
            let startPage = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);

            if (endPage - startPage < maxVisible - 1) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                const btn = document.createElement('button');
                btn.className = 'dt-page-btn' + (i === this.currentPage ? ' active' : '');
                btn.textContent = i;
                btn.addEventListener('click', () => this.goToPage(i));
                this.pageNumbersEl.appendChild(btn);
            }
        }
    }

    refresh() {
        Phoenix.emit('table:refresh', {
            page: this.currentPage,
            perPage: this.options.rowsPerPage
        });
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    window.phoenixDataTable = new PhoenixDataTableTemplate();
});
