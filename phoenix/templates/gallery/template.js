/**
 * PHOENIX Gallery Template JavaScript
 */

class PhoenixGallery {
    constructor(container) {
        this.container = container;
        this.items = [];
        this.filtered = [];
        this.currentAlbum = null;
        this.init();
    }

    init() {
        this.items = Array.from(this.container.querySelectorAll('.gallery-item'));
        this.filtered = [...this.items];
        this.setupLazyLoad();
        this.setupAlbumFilter();
        this.setupTouchGestures();
    }

    setupLazyLoad() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    observer.unobserve(img);
                }
            });
        }, { rootMargin: '100px' });

        this.container.querySelectorAll('img[data-src]').forEach(img => {
            observer.observe(img);
        });
    }

    setupAlbumFilter() {
        const select = this.container.querySelector('.album-select');
        if (!select) return;

        select.addEventListener('change', (e) => {
            this.currentAlbum = e.target.value || null;
            this.filterItems();
        });
    }

    filterItems() {
        this.items.forEach(item => {
            const albumId = item.dataset.album;
            const show = !this.currentAlbum || albumId === this.currentAlbum;
            item.style.display = show ? '' : 'none';
        });
    }

    setupTouchGestures() {
        let touchStartX = 0;
        let touchEndX = 0;

        const lightbox = document.getElementById('lightbox');
        if (!lightbox) return;

        lightbox.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        lightbox.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            this.handleSwipe(touchStartX, touchEndX);
        }, { passive: true });
    }

    handleSwipe(startX, endX) {
        const diff = startX - endX;
        const threshold = 50;

        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                // Swipe left - next
                document.querySelector('.lightbox-next')?.click();
            } else {
                // Swipe right - prev
                document.querySelector('.lightbox-prev')?.click();
            }
        }
    }
}

// Masonry layout helper (optional enhancement)
class MasonryLayout {
    constructor(container, options = {}) {
        this.container = container;
        this.options = {
            columns: options.columns || 4,
            gap: options.gap || 15,
            ...options
        };
        this.init();
    }

    init() {
        if (typeof ResizeObserver !== 'undefined') {
            const observer = new ResizeObserver(() => this.layout());
            observer.observe(this.container);
        }
        this.layout();
    }

    layout() {
        // CSS column-count handles this, but we could use JS for more control
        const width = this.container.clientWidth;
        let columns = this.options.columns;

        if (width < 480) columns = 1;
        else if (width < 768) columns = 2;
        else if (width < 1200) columns = 3;

        this.container.style.columnCount = columns;
    }
}

// Initialize gallery on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    const gallery = document.querySelector('.phoenix-gallery');
    if (gallery) {
        new PhoenixGallery(gallery);
    }
});
