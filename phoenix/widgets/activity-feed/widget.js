/**
 * PHOENIX Activity Feed Widget
 */
(function() {
    'use strict';

    class PhoenixActivityFeed {
        constructor(element) {
            this.element = element;
            this.config = JSON.parse(element.dataset.config || '{}');
            this.list = element.querySelector('.feed-list');
            this.refreshInterval = null;

            this.init();
        }

        init() {
            // Start refresh if configured
            if (this.config.refresh?.enabled) {
                this.startRefresh();
            }

            // Listen for refresh events
            this.element.addEventListener('phoenix:widget:refresh', () => {
                this.loadData();
            });
        }

        async loadData() {
            try {
                const widgetId = this.element.id;
                const response = await fetch(`/phoenix/api/widget/${widgetId}/data`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.config.data || {})
                });

                const result = await response.json();
                if (result.success && result.data?.items) {
                    this.updateItems(result.data.items);
                }
            } catch (error) {
                console.error('Activity feed load failed:', error);
            }
        }

        updateItems(items) {
            if (!this.list) return;

            // Prepend new items with animation
            items.forEach((item, index) => {
                const existingItem = this.list.querySelector(`[data-item-id="${item.id}"]`);
                if (!existingItem) {
                    const itemHtml = this.renderItem(item);
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = itemHtml;
                    const newItem = tempDiv.firstElementChild;

                    newItem.style.opacity = '0';
                    newItem.style.transform = 'translateX(-20px)';

                    this.list.insertBefore(newItem, this.list.firstChild);

                    setTimeout(() => {
                        newItem.style.transition = 'all 0.3s ease';
                        newItem.style.opacity = '1';
                        newItem.style.transform = 'translateX(0)';
                    }, index * 100);
                }
            });

            // Remove excess items
            const maxItems = this.config.maxItems || 10;
            while (this.list.children.length > maxItems) {
                this.list.lastElementChild.remove();
            }
        }

        renderItem(item) {
            const showAvatars = this.config.showAvatars !== false;
            const showTimestamps = this.config.showTimestamps !== false;

            return `
                <div class="feed-item" data-item-id="${item.id || ''}">
                    ${showAvatars ? `
                    <div class="feed-avatar">
                        ${item.avatar
                            ? `<img src="${item.avatar}" alt="">`
                            : `<span class="avatar-placeholder">${(item.user || '?')[0].toUpperCase()}</span>`
                        }
                    </div>
                    ` : ''}
                    <div class="feed-content">
                        <div class="feed-text">
                            <span class="feed-user">${item.user || 'Someone'}</span>
                            <span class="feed-action">${item.action || 'did something'}</span>
                            ${item.target ? `<span class="feed-target">${item.target}</span>` : ''}
                        </div>
                        ${showTimestamps && item.time ? `<div class="feed-time">${item.time}</div>` : ''}
                    </div>
                    <div class="feed-icon">${item.icon || '📌'}</div>
                </div>
            `;
        }

        startRefresh() {
            const interval = (this.config.refresh.interval || 30) * 1000;
            this.refreshInterval = setInterval(() => this.loadData(), interval);
        }

        stopRefresh() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
        }

        destroy() {
            this.stopRefresh();
        }
    }

    // Initialize
    function initFeeds() {
        document.querySelectorAll('.phoenix-activity-feed').forEach(el => {
            if (!el._phoenixWidget) {
                el._phoenixWidget = new PhoenixActivityFeed(el);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFeeds);
    } else {
        initFeeds();
    }

    window.PhoenixActivityFeed = PhoenixActivityFeed;
})();
