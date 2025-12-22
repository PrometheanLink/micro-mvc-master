/**
 * PHOENIX Catalog Template JavaScript
 */

class PhoenixCatalog {
    constructor(container) {
        this.container = container;
        this.filters = {};
        this.sort = 'featured';
        this.page = 1;
        this.init();
    }

    init() {
        this.bindEvents();
        this.initLazyLoad();
    }

    bindEvents() {
        // Filter changes
        this.container.querySelectorAll('.filter-option input').forEach(input => {
            input.addEventListener('change', () => this.updateFilters());
        });

        // Sort change
        const sortSelect = this.container.querySelector('.sort-select');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.sort = e.target.value;
                this.loadProducts();
            });
        }

        // Search
        const searchInput = this.container.querySelector('.search-box input');
        if (searchInput) {
            let timeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    this.filters.search = e.target.value;
                    this.loadProducts();
                }, 300);
            });
        }

        // Price range
        this.container.querySelectorAll('.price-inputs input').forEach((input, index) => {
            input.addEventListener('change', () => {
                const inputs = this.container.querySelectorAll('.price-inputs input');
                this.filters.minPrice = inputs[0].value || null;
                this.filters.maxPrice = inputs[1].value || null;
                this.loadProducts();
            });
        });
    }

    updateFilters() {
        const checkboxes = this.container.querySelectorAll('.filter-option input:checked');
        this.filters.categories = Array.from(checkboxes).map(cb => cb.value);
        this.page = 1;
        this.loadProducts();
    }

    async loadProducts() {
        const grid = this.container.querySelector('.catalog-grid');
        grid.classList.add('loading');

        try {
            const params = new URLSearchParams({
                ...this.filters,
                sort: this.sort,
                page: this.page
            });

            const response = await fetch(`/phoenix/api/products?${params}`);
            const data = await response.json();

            this.renderProducts(data.products);
            this.updatePagination(data.pagination);
        } catch (error) {
            console.error('Failed to load products:', error);
        } finally {
            grid.classList.remove('loading');
        }
    }

    renderProducts(products) {
        const grid = this.container.querySelector('.catalog-grid');
        // Implementation would render product cards
        console.log('Render products:', products);
    }

    updatePagination(pagination) {
        const resultsCount = this.container.querySelector('.results-count');
        if (resultsCount && pagination) {
            resultsCount.textContent = `${pagination.total} products`;
        }
    }

    initLazyLoad() {
        const images = this.container.querySelectorAll('.product-image img[data-src]');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => observer.observe(img));
    }
}

// Cart functionality
window.PhoenixCart = {
    items: [],

    add(productId, quantity = 1) {
        const existing = this.items.find(i => i.id === productId);
        if (existing) {
            existing.quantity += quantity;
        } else {
            this.items.push({ id: productId, quantity });
        }
        this.save();
        this.updateUI();
        this.showNotification('Added to cart');
    },

    remove(productId) {
        this.items = this.items.filter(i => i.id !== productId);
        this.save();
        this.updateUI();
    },

    save() {
        localStorage.setItem('phoenix_cart', JSON.stringify(this.items));
    },

    load() {
        const saved = localStorage.getItem('phoenix_cart');
        if (saved) {
            this.items = JSON.parse(saved);
        }
    },

    updateUI() {
        const count = this.items.reduce((sum, i) => sum + i.quantity, 0);
        document.querySelectorAll('.cart-count').forEach(el => {
            el.textContent = count;
            el.style.display = count > 0 ? 'block' : 'none';
        });
    },

    showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'cart-notification';
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }
};

// Initialize cart
document.addEventListener('DOMContentLoaded', () => {
    window.PhoenixCart.load();
    window.PhoenixCart.updateUI();

    // Initialize catalog if present
    const catalog = document.querySelector('.phoenix-catalog');
    if (catalog) {
        new PhoenixCatalog(catalog);
    }
});
