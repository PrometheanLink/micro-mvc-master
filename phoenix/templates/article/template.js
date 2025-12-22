/**
 * PHOENIX Article Template JavaScript
 */

class PhoenixArticle {
    constructor(container) {
        this.container = container;
        this.init();
    }

    init() {
        this.setupReadingProgress();
        this.generateTableOfContents();
        this.setupStickyTOC();
        this.setupSmoothScroll();
        this.highlightCodeBlocks();
    }

    setupReadingProgress() {
        const article = this.container.querySelector('.article-body');
        const progressBar = document.getElementById('readingProgress');

        if (!article || !progressBar) return;

        const updateProgress = () => {
            const rect = article.getBoundingClientRect();
            const scrolled = Math.max(0, -rect.top);
            const total = rect.height - window.innerHeight;
            const progress = Math.min(100, Math.max(0, (scrolled / total) * 100));
            progressBar.style.width = progress + '%';
        };

        window.addEventListener('scroll', updateProgress, { passive: true });
        updateProgress();
    }

    generateTableOfContents() {
        const article = this.container.querySelector('.article-body');
        const tocNav = this.container.querySelector('.toc-nav');

        if (!article || !tocNav) return;

        const headings = article.querySelectorAll('h2, h3');
        let html = '';

        headings.forEach((heading, index) => {
            const id = heading.id || `heading-${index}`;
            heading.id = id;

            const level = heading.tagName === 'H2' ? 'toc-h2' : 'toc-h3';
            html += `<a href="#${id}" class="${level}" data-target="${id}">${heading.textContent}</a>`;
        });

        tocNav.innerHTML = html;
    }

    setupStickyTOC() {
        const tocLinks = this.container.querySelectorAll('.toc-nav a');
        const headings = Array.from(this.container.querySelectorAll('.article-body h2, .article-body h3'));

        if (!tocLinks.length || !headings.length) return;

        const updateActiveLink = () => {
            let current = '';

            headings.forEach(heading => {
                const rect = heading.getBoundingClientRect();
                if (rect.top <= 100) {
                    current = heading.id;
                }
            });

            tocLinks.forEach(link => {
                link.classList.toggle('active', link.dataset.target === current);
            });
        };

        window.addEventListener('scroll', updateActiveLink, { passive: true });
        updateActiveLink();
    }

    setupSmoothScroll() {
        this.container.querySelectorAll('a[href^="#"]').forEach(link => {
            link.addEventListener('click', (e) => {
                const targetId = link.getAttribute('href').slice(1);
                const target = document.getElementById(targetId);

                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });

                    // Update URL without jumping
                    history.pushState(null, '', '#' + targetId);
                }
            });
        });
    }

    highlightCodeBlocks() {
        // If Prism.js or highlight.js is loaded, trigger highlighting
        if (typeof Prism !== 'undefined') {
            Prism.highlightAllUnder(this.container);
        } else if (typeof hljs !== 'undefined') {
            this.container.querySelectorAll('pre code').forEach(block => {
                hljs.highlightBlock(block);
            });
        }
    }
}

// Estimated reading time calculator
function calculateReadTime(text, wordsPerMinute = 200) {
    const words = text.trim().split(/\s+/).length;
    return Math.ceil(words / wordsPerMinute);
}

// Initialize article on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    const article = document.querySelector('.phoenix-article');
    if (article) {
        new PhoenixArticle(article);
    }
});
