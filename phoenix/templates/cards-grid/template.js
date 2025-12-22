/**
 * PHOENIX Cards Grid Template
 * Responsive grid display with search, sort, and filters
 */

class PhoenixCardsGridTemplate {
    constructor(options = {}) {
        this.options = {
            infiniteScroll: false,
            itemsPerPage: 12,
            columns: 4,
            showParticles: true,
            ...options
        };

        this.currentPage = 1;
        this.totalPages = 1;
        this.isLoading = false;
        this.layoutMode = 'grid';

        this.init();
    }

    init() {
        this.bindElements();
        this.bindEvents();
        this.initParticles();
        this.initInfiniteScroll();

        console.log('📦 PHOENIX Cards Grid Template initialized');
    }

    bindElements() {
        this.container = document.getElementById('cards-container');
        this.searchInput = document.getElementById('cards-search');
        this.sortSelect = document.getElementById('cards-sort');
        this.loadingEl = document.getElementById('cards-loading');
        this.prevBtn = document.getElementById('prev-page');
        this.nextBtn = document.getElementById('next-page');
        this.currentPageEl = document.getElementById('current-page');
        this.totalPagesEl = document.getElementById('total-pages');
        this.viewBtns = document.querySelectorAll('.view-btn');
        this.categoryItems = document.querySelectorAll('.category-item');
    }

    bindEvents() {
        // Search
        if (this.searchInput) {
            this.searchInput.addEventListener('input', Phoenix.utils.debounce(() => {
                this.handleSearch(this.searchInput.value);
            }, 300));
        }

        // Sort
        if (this.sortSelect) {
            this.sortSelect.addEventListener('change', (e) => {
                this.handleSort(e.target.value);
            });
        }

        // View toggle
        this.viewBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                this.setLayoutMode(btn.dataset.view);
            });
        });

        // Category filter
        this.categoryItems.forEach(item => {
            item.addEventListener('click', () => {
                this.handleCategoryClick(item);
            });
        });

        // Pagination
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.goToPage(this.currentPage - 1));
        }

        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.goToPage(this.currentPage + 1));
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                if (this.searchInput) {
                    e.preventDefault();
                    this.searchInput.focus();
                }
            }
        });

        // Listen for card events
        Phoenix.on('card:click', (data) => this.handleCardClick(data));
        Phoenix.on('card:action', (data) => this.handleCardAction(data));
    }

    initParticles() {
        if (!this.options.showParticles) return;

        const container = document.getElementById('particles');
        if (!container) return;

        const particleCount = 40;

        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.style.cssText = `
                position: absolute;
                width: ${Math.random() * 4 + 1}px;
                height: ${Math.random() * 4 + 1}px;
                background: var(--phoenix-accent-primary);
                border-radius: 50%;
                left: ${Math.random() * 100}%;
                top: ${Math.random() * 100}%;
                opacity: ${Math.random() * 0.4 + 0.1};
                animation: float ${Math.random() * 10 + 10}s linear infinite;
                animation-delay: -${Math.random() * 10}s;
            `;
            container.appendChild(particle);
        }

        // Add float animation if not exists
        if (!document.querySelector('#phoenix-float-style')) {
            const style = document.createElement('style');
            style.id = 'phoenix-float-style';
            style.textContent = `
                @keyframes float {
                    0%, 100% { transform: translateY(0) translateX(0); }
                    25% { transform: translateY(-30px) translateX(10px); }
                    50% { transform: translateY(-60px) translateX(-10px); }
                    75% { transform: translateY(-30px) translateX(20px); }
                }
            `;
            document.head.appendChild(style);
        }
    }

    initInfiniteScroll() {
        if (!this.options.infiniteScroll) return;

        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !this.isLoading) {
                this.loadMore();
            }
        }, { threshold: 0.1 });

        if (this.loadingEl) {
            observer.observe(this.loadingEl);
        }
    }

    setLayoutMode(mode) {
        this.layoutMode = mode;

        // Update container class
        if (this.container) {
            this.container.classList.remove('layout-grid', 'layout-list', 'layout-masonry');
            this.container.classList.add('layout-' + mode);
        }

        // Update button states
        this.viewBtns.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.view === mode);
        });

        Phoenix.emit('cards:layoutChange', { mode });
    }

    handleSearch(query) {
        this.currentPage = 1;
        Phoenix.emit('cards:search', { query });
        this.refresh();
    }

    handleSort(sortBy) {
        Phoenix.emit('cards:sort', { sortBy });
        this.refresh();
    }

    handleCategoryClick(item) {
        // Update active state
        this.categoryItems.forEach(i => i.classList.remove('active'));
        item.classList.add('active');

        const category = item.querySelector('span')?.textContent;
        Phoenix.emit('cards:categoryFilter', { category });
        this.refresh();
    }

    handleCardClick(data) {
        console.log('Card clicked:', data);
        // Can be overridden or listened to via Phoenix.on('card:click')
    }

    handleCardAction(data) {
        console.log('Card action:', data);
        // Handle edit, delete, etc.
    }

    goToPage(page) {
        if (page < 1 || page > this.totalPages || this.isLoading) return;

        this.currentPage = page;
        this.updatePagination();
        this.refresh();
    }

    updatePagination() {
        if (this.currentPageEl) {
            this.currentPageEl.textContent = this.currentPage;
        }

        if (this.totalPagesEl) {
            this.totalPagesEl.textContent = this.totalPages;
        }

        if (this.prevBtn) {
            this.prevBtn.disabled = this.currentPage === 1;
        }

        if (this.nextBtn) {
            this.nextBtn.disabled = this.currentPage === this.totalPages;
        }
    }

    setLoading(loading) {
        this.isLoading = loading;

        if (this.loadingEl) {
            this.loadingEl.style.display = loading ? 'flex' : 'none';
        }
    }

    async loadMore() {
        if (this.currentPage >= this.totalPages) return;

        this.setLoading(true);
        this.currentPage++;

        try {
            // Emit event for data fetching
            Phoenix.emit('cards:loadMore', {
                page: this.currentPage,
                perPage: this.options.itemsPerPage
            });

        } finally {
            this.setLoading(false);
        }
    }

    async refresh() {
        this.setLoading(true);

        try {
            Phoenix.emit('cards:refresh', {
                page: this.currentPage,
                perPage: this.options.itemsPerPage,
                search: this.searchInput?.value || '',
                sort: this.sortSelect?.value || 'newest'
            });

        } finally {
            this.setLoading(false);
        }
    }

    addCard(cardHtml, prepend = false) {
        if (!this.container) return;

        const wrapper = document.createElement('div');
        wrapper.innerHTML = cardHtml.trim();
        const card = wrapper.firstChild;

        if (card) {
            card.style.animation = 'fade-in 0.3s ease';

            if (prepend) {
                this.container.insertBefore(card, this.container.firstChild);
            } else {
                this.container.appendChild(card);
            }
        }
    }

    removeCard(cardId) {
        const card = this.container?.querySelector(`[data-card-id="${cardId}"]`);
        if (card) {
            card.style.animation = 'fade-out 0.3s ease';
            setTimeout(() => card.remove(), 300);
        }
    }

    clearCards() {
        if (this.container) {
            this.container.innerHTML = '';
        }
    }

    setTotalPages(total) {
        this.totalPages = total;
        this.updatePagination();
    }
}

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
    window.phoenixCardsGrid = new PhoenixCardsGridTemplate();
});
