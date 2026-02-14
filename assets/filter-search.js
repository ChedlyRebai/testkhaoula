/**
 * AJAX Filter, Search, and Sort functionality
 */

class FilterManager {
    constructor(options) {
        this.apiUrl = options.apiUrl;
        this.tableBodySelector = options.tableBodySelector;
        this.searchInputId = options.searchInputId;
        this.sortSelectId = options.sortSelectId;
        this.orderSelectId = options.orderSelectId;
        this.filterSelectIds = options.filterSelectIds || [];
        this.entityType = options.entityType || 'projets';
        this.renderAsGrid = options.renderAsGrid || false; // Add grid layout option
        
        this.init();
    }

    init() {
        // Search input - with debounce (300ms delay) - DEPENDENT ON FILTERS
        if (this.searchInputId) {
            const searchInput = document.getElementById(this.searchInputId);
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        console.log('🔍 Search triggered:', e.target.value);
                        console.log('🔗 Search is dependent on current filter state');
                        this.performSearch();
                    }, 300);
                });
            }
        }

        // Search button - explicit search trigger
        const searchBtn = document.getElementById('search-btn');
        if (searchBtn) {
            searchBtn.addEventListener('click', () => {
                clearTimeout(this.searchTimeout);
                console.log('🔍 Search button clicked');
                console.log('🔗 Search within filters:', this.getActiveFiltersInfo());
                this.performSearch();
            });
        }

        // Search clear button - clear search
        const searchClearBtn = document.getElementById('search-clear-btn');
        if (searchClearBtn) {
            searchClearBtn.addEventListener('click', () => {
                console.log('🔍 Search cleared - Filters remain active');
                if (this.searchInputId) {
                    const searchInput = document.getElementById(this.searchInputId);
                    if (searchInput) {
                        searchInput.value = '';
                        this.updateSearchDependencyStatus();
                        this.performSearch();
                    }
                }
            });
        }

        // Sort select - filter change only (NO auto-search)
        if (this.sortSelectId) {
            const sortSelect = document.getElementById(this.sortSelectId);
            if (sortSelect) {
                sortSelect.addEventListener('change', (e) => {
                    console.log('⚙️ Sort changed:', e.target.value);
                    console.log('🔗 Filter updated - Waiting for search button...');
                    this.updateSearchDependencyStatus();
                });
            }
        }

        // Order select - filter change only (NO auto-search)
        if (this.orderSelectId) {
            const orderSelect = document.getElementById(this.orderSelectId);
            if (orderSelect) {
                orderSelect.addEventListener('change', (e) => {
                    console.log('⚙️ Order changed:', e.target.value);
                    console.log('🔗 Filter updated - Waiting for search button...');
                    this.updateSearchDependencyStatus();
                });
            }
        }

        // Filter reset button - reset sort/order to defaults
        const filterResetBtn = document.getElementById('filter-reset-btn');
        if (filterResetBtn) {
            filterResetBtn.addEventListener('click', () => {
                console.log('⚙️ Filters reset to defaults - Waiting for search button...');
                if (this.sortSelectId) {
                    const sortSelect = document.getElementById(this.sortSelectId);
                    if (sortSelect) {
                        sortSelect.value = 'date';
                    }
                }
                if (this.orderSelectId) {
                    const orderSelect = document.getElementById(this.orderSelectId);
                    if (orderSelect) {
                        orderSelect.value = 'DESC';
                    }
                }
                // Clear all additional filters
                this.filterSelectIds.forEach(id => {
                    const element = document.getElementById(id);
                    if (element) {
                        element.value = '';
                    }
                });
                this.updateSearchDependencyStatus();
                console.log('🔗 Filters reset. Now click Search to apply new filters');
            });
        }

        // Additional filters - filter change only (NO auto-search)
        this.filterSelectIds.forEach(id => {
            const selectElement = document.getElementById(id);
            if (selectElement) {
                selectElement.addEventListener('change', (e) => {
                    console.log('⚙️ Filter changed:', id, e.target.value);
                    console.log('🔗 Filter updated - Waiting for search button...');
                    this.updateSearchDependencyStatus();
                });
            }
        });

        // Initial dependency status update
        this.updateSearchDependencyStatus();
    }

    /**
     * Update the search dependency status UI
     */
    updateSearchDependencyStatus() {
        const searchInput = document.getElementById(this.searchInputId);
        const filterStatus = this.getActiveFiltersInfo();
        
        if (searchInput) {
            // Update placeholder to show filter dependency
            if (filterStatus.count > 0) {
                searchInput.placeholder = `🔗 Chercher dans ${filterStatus.description}...`;
                searchInput.setAttribute('title', `Recherche active dans: ${filterStatus.description}`);
            } else {
                searchInput.placeholder = `Entrez le nom ou une partie de la description...`;
                searchInput.setAttribute('title', 'Appliquez des filtres pour affiner la recherche');
            }
        }

        // Update search results count display
        const searchResultsCount = document.getElementById('search-results-count');
        if (searchResultsCount && filterStatus.count > 0) {
            const searchValue = (searchInput?.value || '').trim();
            let statusHtml = `<strong>🔗 Filtres actifs:</strong> ${filterStatus.description}`;
            if (searchValue) {
                statusHtml += `<br><strong>🔍 Recherche:</strong> "${searchValue}"`;
            }
            searchResultsCount.innerHTML = statusHtml;
        }
    }

    /**
     * Get information about active filters
     */
    getActiveFiltersInfo() {
        const filters = [];
        
        // Check sort
        if (this.sortSelectId) {
            const sortValue = document.getElementById(this.sortSelectId)?.value;
            if (sortValue && sortValue !== 'date') {
                filters.push(`Tri: ${sortValue}`);
            }
        }

        // Check order
        if (this.orderSelectId) {
            const orderValue = document.getElementById(this.orderSelectId)?.value;
            if (orderValue && orderValue !== 'DESC') {
                filters.push(`Ordre: ${orderValue}`);
            }
        }

        // Check additional filters
        this.filterSelectIds.forEach(id => {
            const element = document.getElementById(id);
            if (element && element.value && element.value !== '') {
                const label = element.options[element.selectedIndex]?.text || element.value;
                filters.push(label);
            }
        });

        return {
            count: filters.length,
            filters: filters,
            description: filters.length > 0 ? filters.join(' + ') : 'tous les résultats'
        };
    }

    performSearch() {
        const params = new URLSearchParams();

        // ============================================
        // SEARCH DEPENDENT ON FILTERS
        // ============================================
        // Search always works within the context of active filters
        // Filters are ALWAYS included, search is optional

        // ==== MANDATORY: Filter Parameters ====
        
        // Sort parameter (always included, default 'date')
        if (this.sortSelectId) {
            const sortValue = document.getElementById(this.sortSelectId)?.value || 'date';
            params.append('sort', sortValue);
            console.log('[FILTER CONTEXT] Sort:', sortValue);
        }

        // Order parameter (always included, default 'DESC')
        if (this.orderSelectId) {
            const orderValue = document.getElementById(this.orderSelectId)?.value || 'DESC';
            params.append('order', orderValue);
            console.log('[FILTER CONTEXT] Order:', orderValue);
        }

        // Additional filters (optional but part of filter context)
        this.filterSelectIds.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                const value = element.value?.trim() || '';
                if (value && value !== '') {
                    const paramName = id.replace('filter-', '');
                    params.append(paramName, value);
                    console.log('[FILTER CONTEXT]', paramName, '=', value);
                }
            }
        });

        // ==== OPTIONAL: Search Parameter (depends on filters above) ====
        
        if (this.searchInputId) {
            const searchValue = document.getElementById(this.searchInputId)?.value?.trim() || '';
            if (searchValue) {
                params.append('search', searchValue);
                console.log('[SEARCH WITHIN FILTERS] Search term:', searchValue);
            }
        }

        const url = `${this.apiUrl}?${params.toString()}`;
        console.log('🔗 Final Search URL (Search dependent on Filters):', url);
        
        // Update browser history for persistence
        window.history.replaceState(
            { filters: params.toString() },
            document.title,
            `${window.location.pathname}?${params.toString()}`
        );

        // Show loading state
        const tableBody = document.querySelector(this.tableBodySelector);
        if (tableBody) {
            if (this.renderAsGrid) {
                tableBody.innerHTML = '<div class="col-lg-12 text-center py-5"><i class="fa fa-spinner fa-spin"></i> Chargement...</div>';
            } else {
                tableBody.innerHTML = '<tr><td colspan="10" class="text-center py-4"><i class="fa fa-spinner fa-spin"></i> Chargement...</td></tr>';
            }
        }

        // Fetch results
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('API Response:', data);
                if (data.success) {
                    this.renderResults(data);
                } else {
                    this.showError('Erreur lors de la récupération des données: ' + (data.error || 'Inconnu'));
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                this.showError('Une erreur est survenue: ' + error.message);
            });
    }

    renderResults(data) {
        const tableBody = document.querySelector(this.tableBodySelector);
        if (!tableBody) {
            console.error('Table body not found:', this.tableBodySelector);
            return;
        }

        if (data.count === 0) {
            if (this.renderAsGrid) {
                tableBody.innerHTML = `
                    <div class="col-lg-12">
                        <div class="alert alert-info text-center py-5">
                            <i class="fa fa-inbox text-muted" style="font-size: 2rem;"></i><br>
                            <p class="mt-3">Aucun résultat trouvé avec les critères sélectionnés.</p>
                        </div>
                    </div>
                `;
            } else {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <i class="fa fa-inbox text-muted"></i><br>
                            Aucun résultat trouvé.
                        </td>
                    </tr>
                `;
            }
            this.updateResultCount(data);
            return;
        }

        let html = '';

        if (this.entityType === 'projets') {
            if (this.renderAsGrid) {
                // Grid layout for projects
                html = data.projets.map(projet => {
                    const imageIndex = ((projet.id - 1) % 5 + 1);
                    return `
                    <div class="col-lg-6 col-md-6 mb-40">
                        <div class="course-card">
                            <div class="course-img">
                                <img src="/edubin-images/course/cu-${imageIndex}.jpg" alt="Project">
                                <div class="course-overlay">
                                    <a href="/projets/${projet.id}" class="main-btn">Afficher</a>
                                </div>
                            </div>
                            <div class="course-cont">
                                <div class="course-header">
                                    <h4><a href="/projets/${projet.id}">${this.escapeHtml(projet.nom)}</a></h4>
                                </div>
                                <p>${this.escapeHtml(projet.description)}</p>
                                <div class="course-footer">
                                    <div class="course-meta">
                                        <span class="meta-date"><i class="fa fa-calendar"></i> ${projet.dateCreation}</span>
                                    </div>
                                    <div class="course-action">
                                        <a href="/projets/${projet.id}" class="btn btn-sm btn-outline-primary">Voir</a>
                                        <a href="/taches?projet=${projet.id}" class="btn btn-sm btn-outline-info">Tâches</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                }).join('');
            } else {
                // Table layout for projects
                html = data.projets.map(projet => `
                    <tr>
                        <td><strong>#${projet.id}</strong></td>
                        <td>${this.escapeHtml(projet.nom)}</td>
                        <td>${this.escapeHtml(projet.description)}</td>
                        <td>${projet.dateCreation}</td>
                        <td>
                            <a href="/projets/${projet.id}" class="btn btn-sm btn-info">Voir</a>
                            <a href="/taches?projet=${projet.id}" class="btn btn-sm btn-secondary" title="Tâches du projet">Tâches</a>
                            <a href="/projets/${projet.id}/edit" class="btn btn-sm btn-warning">Éditer</a>
                            <form method="POST" action="/projets/${projet.id}/delete" style="display:inline;">
                                <input type="hidden" name="_token" value="${this.getCsrfToken()}">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer?')">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                `).join('');
            }
        } else if (this.entityType === 'taches') {
            html = data.taches.map(tache => `
                <tr>
                    <td><strong>#${tache.id}</strong></td>
                    <td>${this.escapeHtml(tache.titre)}</td>
                    <td>${tache.projet ? `<a href="/taches?projet=${tache.projet.id}">${this.escapeHtml(tache.projet.nom)}</a>` : '-'}</td>
                    <td><span class="badge ${this.getStatutBadgeClass(tache.statut)}">${tache.statut}</span></td>
                    <td><span class="badge ${this.getPrioriteBadgeClass(tache.priorite)}">${tache.priorite_label}</span></td>
                    <td>
                        <a href="/taches/${tache.id}" class="btn btn-sm btn-info">Voir</a>
                        <a href="/taches/${tache.id}/edit" class="btn btn-sm btn-warning">Éditer</a>
                        <form method="POST" action="/taches/${tache.id}/delete" style="display:inline;">
                            <input type="hidden" name="_token" value="${this.getCsrfToken()}">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr?')">Supprimer</button>
                        </form>
                    </td>
                </tr>
            `).join('');
        }

        tableBody.innerHTML = html;
        this.updateResultCount(data);
        console.log('Rendered', data.count, 'result(s)');
    }

    updateResultCount(data) {
        const countElement = document.getElementById('results-count');
        if (countElement) {
            if (data.total > 0) {
                countElement.textContent = `${data.count} résultat(s) sur ${data.total}`;
            } else {
                countElement.textContent = 'Aucun résultat';
            }
        }
    }

    getStatutBadgeClass(statut) {
        const classes = {
            'À faire': 'badge-secondary',
            'En cours': 'badge-info',
            'Terminée': 'badge-success'
        };
        return classes[statut] || 'badge-warning';
    }

    getPrioriteBadgeClass(priorite) {
        const classes = {
            'Basse': 'badge-success',
            'Normale': 'badge-warning',
            'Haute': 'badge-danger'
        };
        return classes[priorite] || 'badge-secondary';
    }

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

    getCsrfToken() {
        const element = document.querySelector('meta[name="csrf-token"]');
        const token = element ? element.getAttribute('content') : '';
        return token;
    }

    showError(message) {
        const tableBody = document.querySelector(this.tableBodySelector);
        if (tableBody) {
            if (this.renderAsGrid) {
                tableBody.innerHTML = `
                    <div class="col-lg-12">
                        <div class="alert alert-danger text-center py-5">
                            <i class="fa fa-exclamation-circle" style="font-size: 2rem;"></i><br>
                            <p class="mt-3">${this.escapeHtml(message)}</p>
                        </div>
                    </div>
                `;
            } else {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-4">
                            <i class="fa fa-exclamation-circle text-danger"></i><br>
                            ${this.escapeHtml(message)}
                        </td>
                    </tr>
                `;
            }
        }
    }
}

// Make available globally
window.FilterManager = FilterManager;
