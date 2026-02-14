/**
 * AJAX Search, Filter, and Sort functionality
 * Each action (search, sort, filter) works independently with separate buttons
 */

class AjaxSearchManager {
    constructor(options) {
        this.apiUrl = options.apiUrl; // e.g., '/projets/api/search'
        this.containerSelector = options.containerSelector; // e.g., '.results-container'
        this.searchInputId = options.searchInputId; // e.g., 'search'
        this.sortSelectId = options.sortSelectId; // e.g., 'sort'
        this.dateFromId = options.dateFromId; // e.g., 'date-from'
        this.dateToId = options.dateToId; // e.g., 'date-to'
        this.searchBtnId = options.searchBtnId; // e.g., 'search-btn'
        this.sortBtnId = options.sortBtnId; // e.g., 'sort-btn'
        this.filterBtnId = options.filterBtnId; // e.g., 'filter-btn'
        this.resetBtnId = options.resetBtnId; // e.g., 'reset-btn'
        this.renderCallback = options.renderCallback; // Custom render function
        this.entityType = options.entityType || 'projets'; // 'projets' or 'taches'
        
        // Store current state
        this.currentSearch = '';
        this.currentSort = 'date';
        this.currentDateFrom = '';
        this.currentDateTo = '';
        
        this.init();
    }

    init() {
        
        // Search button - triggers search
        if (this.searchBtnId) {
            const searchBtn = document.getElementById(this.searchBtnId);
            if (searchBtn) {
                searchBtn.addEventListener('click', () => {
                    this.performSearch();
                });
            }
        }

        // Sort button - triggers sort
        if (this.sortBtnId) {
            const sortBtn = document.getElementById(this.sortBtnId);
            if (sortBtn) {
                sortBtn.addEventListener('click', () => {
                    this.performSort();
                });
            }
        }

        // Filter button - triggers date filtering
        if (this.filterBtnId) {
            const filterBtn = document.getElementById(this.filterBtnId);
            if (filterBtn) {
                filterBtn.addEventListener('click', () => {
                    this.performDateFilter();
                });
            }
        }

        // Reset button - resets everything
        if (this.resetBtnId) {
            const resetBtn = document.getElementById(this.resetBtnId);
            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    this.resetAll();
                });
            }
        }

        // Optional: Allow Enter key in search input to trigger search
        if (this.searchInputId) {
            const searchInput = document.getElementById(this.searchInputId);
            if (searchInput) {
                searchInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        this.performSearch();
                    }
                });
            }
        }
    }

    /**
     * Perform search with current search value
     */
    performSearch() {
        const params = new URLSearchParams();

        // Add search parameter
        if (this.searchInputId) {
            const searchValue = document.getElementById(this.searchInputId)?.value?.trim() || '';
            this.currentSearch = searchValue;
            if (searchValue) {
                params.append('search', searchValue);
            }
        }

        // Include current sort
        params.append('sort', this.currentSort);

        // Include order based on sort type
        const order = (this.currentSort === 'nom' || this.currentSort === 'id') ? 'ASC' : 'DESC';
        params.append('order', order);

        // Include current date filters
        if (this.currentDateFrom) {
            params.append('dateFrom', this.currentDateFrom);
        }
        if (this.currentDateTo) {
            params.append('dateTo', this.currentDateTo);
        }

        const url = `${this.apiUrl}?${params.toString()}`;

        this.showLoading();
        this.makeRequest(url);
    }

    /**
     * Perform sort with current sort value
     */
    performSort() {
        const params = new URLSearchParams();

        // Add sort parameter
        let sortValue = 'date';
        if (this.sortSelectId) {
            sortValue = document.getElementById(this.sortSelectId)?.value || 'date';
            this.currentSort = sortValue;
        }
        params.append('sort', sortValue);

        // Determine order based on sort type
        // Alphabetical (nom, id) = ASC (A-Z)
        // Date = DESC (newest first)
        const order = (sortValue === 'nom' || sortValue === 'id') ? 'ASC' : 'DESC';
        params.append('order', order);

        // Include current search if it exists
        if (this.currentSearch) {
            params.append('search', this.currentSearch);
        }

        // Include current date filters
        if (this.currentDateFrom) {
            params.append('dateFrom', this.currentDateFrom);
        }
        if (this.currentDateTo) {
            params.append('dateTo', this.currentDateTo);
        }

        const url = `${this.apiUrl}?${params.toString()}`;

        this.showLoading();
        this.makeRequest(url);
    }

    /**
     * Perform date filtering
     */
    performDateFilter() {
        const params = new URLSearchParams();

        // Add date parameters
        if (this.dateFromId) {
            const dateFrom = document.getElementById(this.dateFromId)?.value || '';
            this.currentDateFrom = dateFrom;
            if (dateFrom) {
                params.append('dateFrom', dateFrom);
            }
        }

        if (this.dateToId) {
            const dateTo = document.getElementById(this.dateToId)?.value || '';
            this.currentDateTo = dateTo;
            if (dateTo) {
                params.append('dateTo', dateTo);
            }
        }

        // Include current sort
        params.append('sort', this.currentSort);

        // Include order based on sort type
        const order = (this.currentSort === 'nom' || this.currentSort === 'id') ? 'ASC' : 'DESC';
        params.append('order', order);

        // Include current search if it exists
        if (this.currentSearch) {
            params.append('search', this.currentSearch);
        }

        const url = `${this.apiUrl}?${params.toString()}`;

        this.showLoading();
        this.makeRequest(url);
    }

    /**
     * Reset all filters and search
     */
    resetAll() {
        // Reset search input
        if (this.searchInputId) {
            const searchInput = document.getElementById(this.searchInputId);
            if (searchInput) {
                searchInput.value = '';
                this.currentSearch = '';
            }
        }

        // Reset sort to default
        if (this.sortSelectId) {
            const sortSelect = document.getElementById(this.sortSelectId);
            if (sortSelect) {
                sortSelect.value = 'date';
                this.currentSort = 'date';
            }
        }

        // Reset date filters
        if (this.dateFromId) {
            const dateFrom = document.getElementById(this.dateFromId);
            if (dateFrom) {
                dateFrom.value = '';
                this.currentDateFrom = '';
            }
        }

        if (this.dateToId) {
            const dateTo = document.getElementById(this.dateToId);
            if (dateTo) {
                dateTo.value = '';
                this.currentDateTo = '';
            }
        }

        // Trigger search to show all results
        const params = new URLSearchParams();
        params.append('sort', 'date');
        params.append('order', 'DESC');

        const url = `${this.apiUrl}?${params.toString()}`;
        this.showLoading();
        this.makeRequest(url);
    }

    /**
     * Make AJAX request
     */
    makeRequest(url) {
        // Update browser history
        window.history.replaceState(
            {},
            document.title,
            `${window.location.pathname}${url.split('?')[1] ? '?' + url.split('?')[1] : ''}`
        );

        // Fetch results
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.renderResults(data);
                } else {
                    this.showError('Erreur lors de la récupération des données');
                }
            })
            .catch(error => {
                this.showError('Une erreur est survenue: ' + error.message);
            });
    }

    /**
     * Render results based on entity type
     */
    renderResults(data) {
        const container = document.querySelector(this.containerSelector);
        if (!container) {
            return;
        }

        // Use custom render callback if provided
        if (this.renderCallback) {
            this.renderCallback(container, data);
            return;
        }

        // Default rendering based on entity type
        if (data.count === 0) {
            container.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-4">
                        <i class="fa fa-inbox text-muted"></i><br>
                        Aucun résultat trouvé.
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';

        if (this.entityType === 'projets' && data.projets) {
            html = data.projets.map(projet => `
                <tr>
                    <td><strong>${this.escapeHtml(projet.nom)}</strong></td>
                    <td>${this.escapeHtml(projet.description.substring(0, 50))}${projet.description.length > 50 ? '...' : ''}</td>
                    <td><small class="text-muted">${projet.dateCreation}</small></td>
                    <td><span class="badge bg-info">${projet.tachesCount}</span></td>
                    <td>
                        <span class="badge ${projet.enabled ? 'bg-success' : 'bg-danger'}">
                            ${projet.enabled ? 'Actif' : 'Inactif'}
                        </span>
                    </td>
                    <td>
                        <a href="/admin/projets/${projet.id}" class="btn btn-sm btn-info" title="Voir">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="/admin/projets/${projet.id}/edit" class="btn btn-sm btn-warning" title="Éditer">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="/admin/projets/${projet.id}/delete" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr ?');">
                            <input type="hidden" name="_token" value="${this.getCsrfToken()}">
                            <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            `).join('');
        } else if (this.entityType === 'taches' && data.taches) {
            html = data.taches.map(tache => {
                const badgeClass = {
                    'À faire': 'badge-secondary',
                    'En cours': 'badge-warning',
                    'Terminée': 'badge-success'
                };
                const priorityBadgeClass = {
                    'Basse': 'badge-success',
                    'Normale': 'badge-warning',
                    'Haute': 'badge-danger'
                };
                return `
                    <tr>
                        <td><strong>${this.escapeHtml(tache.titre)}</strong></td>
                        <td>${tache.projet ? `<a href="/admin/taches?projet=${tache.projet.id}">${this.escapeHtml(tache.projet.nom)}</a>` : '-'}</td>
                        <td><span class="badge ${badgeClass[tache.statut] || 'badge-secondary'}">${tache.statut}</span></td>
                        <td><span class="badge ${priorityBadgeClass[tache.priorite_label] || 'badge-secondary'}">${tache.priorite_label}</span></td>
                        <td><small class="text-muted">${tache.dateCreation}</small></td>
                        <td>
                            <a href="/admin/taches/${tache.id}" class="btn btn-sm btn-info" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="/admin/taches/${tache.id}/edit" class="btn btn-sm btn-warning" title="Éditer">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="/admin/taches/${tache.id}/delete" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr?');">
                                <input type="hidden" name="_token" value="${this.getCsrfToken()}">
                                <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        container.innerHTML = html;
    }

    /**
     * Show loading state
     */
    showLoading() {
        const container = document.querySelector(this.containerSelector);
        if (container) {
            container.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-4">
                        <i class="fa fa-spinner fa-spin"></i> Chargement...
                    </td>
                </tr>
            `;
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        const container = document.querySelector(this.containerSelector);
        if (container) {
            container.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-4">
                        <i class="fa fa-exclamation-circle text-danger"></i><br>
                        ${this.escapeHtml(message)}
                    </td>
                </tr>
            `;
        }
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    /**
     * Get CSRF token from meta tag
     */
    getCsrfToken() {
        const element = document.querySelector('meta[name="csrf-token"]');
        return element ? element.getAttribute('content') : '';
    }
}

// Make available globally
window.AjaxSearchManager = AjaxSearchManager;
