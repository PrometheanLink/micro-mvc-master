/**
 * PHOENIX Kanban Template JavaScript
 * Drag-and-drop project management board
 */

class PhoenixKanban {
    constructor(container) {
        this.container = container;
        this.board = container.querySelector('#kanbanBoard');
        this.draggedCard = null;
        this.draggedColumn = null;
        this.init();
    }

    init() {
        this.setupCardDrag();
        this.setupSearch();
        this.setupFilters();
    }

    setupCardDrag() {
        const cards = this.container.querySelectorAll('.kanban-card');
        const columns = this.container.querySelectorAll('.column-cards');

        // Card drag events
        cards.forEach(card => {
            card.addEventListener('dragstart', (e) => this.handleDragStart(e, card));
            card.addEventListener('dragend', (e) => this.handleDragEnd(e, card));
            card.addEventListener('click', (e) => this.openCardModal(card));
        });

        // Column drop zones
        columns.forEach(column => {
            column.addEventListener('dragover', (e) => this.handleDragOver(e, column));
            column.addEventListener('dragleave', (e) => this.handleDragLeave(e, column));
            column.addEventListener('drop', (e) => this.handleDrop(e, column));
        });
    }

    handleDragStart(e, card) {
        this.draggedCard = card;
        card.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', card.dataset.cardId);

        // Create ghost image
        const ghost = card.cloneNode(true);
        ghost.classList.add('drag-ghost');
        ghost.style.width = card.offsetWidth + 'px';
        document.body.appendChild(ghost);
        e.dataTransfer.setDragImage(ghost, 20, 20);
        setTimeout(() => ghost.remove(), 0);
    }

    handleDragEnd(e, card) {
        card.classList.remove('dragging');
        this.draggedCard = null;

        // Remove all drag-over states
        this.container.querySelectorAll('.column-cards').forEach(col => {
            col.classList.remove('drag-over');
        });
    }

    handleDragOver(e, column) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        column.classList.add('drag-over');

        // Calculate drop position
        const afterCard = this.getDragAfterCard(column, e.clientY);
        const indicator = column.querySelector('.drop-zone-indicator');

        if (afterCard) {
            column.insertBefore(indicator, afterCard);
        } else {
            column.appendChild(indicator);
        }
    }

    handleDragLeave(e, column) {
        if (!column.contains(e.relatedTarget)) {
            column.classList.remove('drag-over');
        }
    }

    handleDrop(e, column) {
        e.preventDefault();
        column.classList.remove('drag-over');

        if (!this.draggedCard) return;

        const afterCard = this.getDragAfterCard(column, e.clientY);
        const indicator = column.querySelector('.drop-zone-indicator');

        if (afterCard) {
            column.insertBefore(this.draggedCard, afterCard);
        } else {
            column.insertBefore(this.draggedCard, indicator);
        }

        // Update card counts
        this.updateCardCounts();

        // Emit event for backend sync
        this.emitCardMoved(
            this.draggedCard.dataset.cardId,
            column.dataset.column,
            afterCard?.dataset.cardId
        );
    }

    getDragAfterCard(column, y) {
        const cards = [...column.querySelectorAll('.kanban-card:not(.dragging)')];

        return cards.reduce((closest, card) => {
            const box = card.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;

            if (offset < 0 && offset > closest.offset) {
                return { offset, element: card };
            }
            return closest;
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    updateCardCounts() {
        this.container.querySelectorAll('.kanban-column').forEach(column => {
            const count = column.querySelectorAll('.kanban-card').length;
            column.querySelector('.column-title .card-count').textContent = count;
        });

        // Update total count
        const total = this.container.querySelectorAll('.kanban-card').length;
        const countEl = this.container.querySelector('.board-meta .card-count');
        if (countEl) countEl.textContent = total + ' cards';
    }

    emitCardMoved(cardId, columnId, afterCardId) {
        const event = new CustomEvent('phoenix:card-moved', {
            detail: { cardId, columnId, afterCardId }
        });
        this.container.dispatchEvent(event);

        // API call would go here
        console.log('Card moved:', { cardId, columnId, afterCardId });
    }

    setupSearch() {
        const searchInput = this.container.querySelector('#cardSearch');
        if (!searchInput) return;

        let timeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => this.filterCards(e.target.value), 200);
        });
    }

    filterCards(query) {
        const cards = this.container.querySelectorAll('.kanban-card');
        const lowerQuery = query.toLowerCase();

        cards.forEach(card => {
            const title = card.querySelector('.card-title')?.textContent.toLowerCase() || '';
            const desc = card.querySelector('.card-description')?.textContent.toLowerCase() || '';
            const match = title.includes(lowerQuery) || desc.includes(lowerQuery);
            card.style.display = match || !query ? '' : 'none';
        });
    }

    setupFilters() {
        // Assignee filter
        const assigneeFilter = this.container.querySelector('#assigneeFilter');
        if (assigneeFilter) {
            assigneeFilter.addEventListener('change', () => this.applyFilters());
        }

        // Label filter
        const labelFilter = this.container.querySelector('#labelFilter');
        if (labelFilter) {
            labelFilter.addEventListener('change', () => this.applyFilters());
        }
    }

    applyFilters() {
        // Filter implementation
        console.log('Applying filters...');
    }

    openCardModal(card) {
        const modal = document.getElementById('cardModal');
        const titleInput = document.getElementById('modalCardTitle');
        const descInput = document.getElementById('modalCardDescription');

        titleInput.value = card.querySelector('.card-title')?.textContent || '';
        descInput.value = card.querySelector('.card-description')?.textContent || '';

        modal.hidden = false;
        modal.dataset.cardId = card.dataset.cardId;
        titleInput.focus();
    }
}

// Modal functions
function openNewCardModal() {
    const modal = document.getElementById('cardModal');
    document.getElementById('modalCardTitle').value = '';
    document.getElementById('modalCardDescription').value = '';
    document.getElementById('modalCardDueDate').value = '';
    modal.hidden = false;
    modal.dataset.cardId = '';
    document.getElementById('modalCardTitle').focus();
}

function closeCardModal() {
    document.getElementById('cardModal').hidden = true;
}

function saveCard() {
    const modal = document.getElementById('cardModal');
    const cardId = modal.dataset.cardId;
    const title = document.getElementById('modalCardTitle').value;
    const description = document.getElementById('modalCardDescription').value;
    const dueDate = document.getElementById('modalCardDueDate').value;

    console.log('Saving card:', { cardId, title, description, dueDate });

    // API call would go here

    closeCardModal();
}

function addCard(columnId) {
    openNewCardModal();
    document.getElementById('cardModal').dataset.columnId = columnId;
}

function addColumn() {
    const name = prompt('Column name:');
    if (!name) return;

    console.log('Adding column:', name);
    // API call would go here
}

function toggleColumnMenu(btn) {
    // Column menu implementation
    console.log('Toggle column menu');
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    const kanban = document.querySelector('.phoenix-kanban');
    if (kanban) {
        new PhoenixKanban(kanban);
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeCardModal();
    }
});
