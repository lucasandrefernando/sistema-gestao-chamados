/**
 * setores-admin-v5.js (VERSÃO SEGURA)
 * Script moderno para a página de administração de setores
 * 
 * @version 5.0
 * @author Desenvolvedor
 * @updated 2023-06-26
 */

// Verificar se já existe e remover
if (window.SetoresAdmin) {
    delete window.SetoresAdmin;
}

// Usar IIFE para evitar conflitos globais
(function () {
    'use strict';

    // Módulo de Administração de Setores
    const SetoresAdmin = (function () {
        // Configurações globais parametrizadas
        const CONFIG = {
            animationDuration: 300,
            rowsPerPage: 6, // Número de registros por página
            selectors: {
                container: '.setores-admin-v5', // Atualizado para v5
                table: {
                    container: '.data-table',
                    rows: '.data-table tbody tr:not(.empty-row)',
                    selectAll: '#selectAllCheckbox',
                    rowCheckbox: '.row-checkbox',
                    sortableHeaders: '.sortable',
                    sortOrder: '#sortOrder'
                },
                search: {
                    input: '#searchInput',
                    clearBtn: '#clearSearch',
                    noResults: '#noResults',
                    tableContainer: '.table-responsive',
                    clearFiltersBtn: '#clearFiltersBtn',
                    clearFilters: '#clearFilters'
                },
                filters: {
                    status: '#statusFilter',
                    toggle: '#toggleFilters',
                    body: '.filters-body'
                },
                batch: {
                    btn: '#batchActionsBtn',
                    panel: '#batchActionsPanel',
                    closeBtn: '#closeBatchPanel',
                    activateBtn: '#batchActivate',
                    deactivateBtn: '#batchDeactivate',
                    removeBtn: '#batchRemove',
                    selectAllBtn: '#selectAll',
                    deselectAllBtn: '#deselectAll',
                    selectedCount: '#selectedCount'
                },
                actionMenu: {
                    btn: '.action-menu-btn',
                    container: '.action-menu',
                    active: '.action-menu.active',
                    removeBtn: '.action-remove'
                },
                modals: {
                    remove: {
                        container: '#removeModal',
                        closeBtn: '#closeRemoveModal',
                        cancelBtn: '#cancelRemove',
                        message: '#removeModalMessage',
                        confirmBtn: '#confirmRemove'
                    },
                    batch: {
                        container: '#batchModal',
                        closeBtn: '#closeBatchModal',
                        cancelBtn: '#cancelBatchAction',
                        confirmBtn: '#confirmBatchAction',
                        title: '#batchModalTitle',
                        message: '#batchModalMessage',
                        list: '#batchModalList',
                        warning: '#batchModalWarning',
                        icon: '#batchModalIcon'
                    },
                    replicate: {
                        container: '#replicateModal',
                        closeBtn: '#closeReplicateModal',
                        cancelBtn: '#cancelReplicate',
                        confirmBtn: '#confirmReplicate',
                        card: '#replicateUsersCard',
                        sourceCards: '.source-setor-card',
                        sourceRadios: '.source-radio',
                        targetCards: '.target-setor-card',
                        targetCheckboxes: '.target-checkbox',
                        usersContainer: '#sourceUsersContainer',
                        selectSourceBtn: '.btn-select-source'
                    }
                },
                pagination: {
                    container: '.pagination-container',
                    list: '.pagination',
                    info: '.pagination-info'
                },
                avatars: '[data-name]'
            },
            colors: [
                '#4f46e5', '#4338ca', '#3730a3', '#312e81', // Tons de roxo/azul
                '#2563eb', '#1d4ed8', '#1e40af', '#1e3a8a', // Tons de azul
                '#0891b2', '#0e7490', '#155e75', '#164e63', // Tons de ciano
                '#059669', '#047857', '#065f46', '#064e3b', // Tons de verde
                '#7c3aed', '#6d28d9', '#5b21b6', '#4c1d95', // Tons de roxo
                '#db2777', '#be185d', '#9d174d', '#831843'  // Tons de rosa
            ],
            storage: {
                sortOrder: 'setoresAdminSortOrder',
                statusFilter: 'setoresAdminStatusFilter',
                filtersCollapsed: 'setoresAdminFiltersCollapsed',
                currentPage: 'setoresAdminCurrentPage'
            },
            endpoints: {
                getUsuarios: 'setores/getUsuariosSetor/',
                batchActivate: 'setores/batch/activate',
                batchDeactivate: 'setores/batch/deactivate',
                batchRemove: 'setores/batch/remove',
                remove: 'setores/remover/'
            },
            templates: {
                loadingUsers: `
                    <div class="placeholder-message">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Carregando usuários...</p>
                    </div>
                `,
                errorUsers: `
                    <div class="placeholder-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Erro ao carregar usuários. Tente novamente.</p>
                    </div>
                `,
                noUsers: `
                    <div class="placeholder-message">
                        <i class="fas fa-users-slash"></i>
                        <p>Nenhum usuário encontrado neste setor</p>
                    </div>
                `,
                defaultUsers: `
                    <div class="placeholder-message">
                        <i class="fas fa-arrow-left"></i>
                        <p>Selecione um setor de origem para ver os usuários</p>
                    </div>
                `,
                pagination: `
                    <div class="pagination-container">
                        <ul class="pagination"></ul>
                        <div class="pagination-info"></div>
                    </div>
                `
            },
            apiUrl: '' // Será definido dinamicamente
        };

        // Estado da aplicação
        const state = {
            selectedSourceId: null,
            selectedTargetIds: [],
            sourceUsers: [],
            baseUrl: '',
            currentPage: 1,
            totalPages: 1,
            visibleRows: [],
            isInitialized: false
        };

        /**
         * Inicializa a aplicação
         */
        function init() {
            if (state.isInitialized) {
                console.warn('SetoresAdmin já foi inicializado');
                return;
            }

            // Verifica se estamos na página correta
            const container = getElement(CONFIG.selectors.container);
            if (!container) {
                console.log('Página de administração de setores não encontrada.');
                // Tenta encontrar o container v4 e atualiza para v5
                const oldContainer = getElement('.setores-admin-v4');
                if (oldContainer) {
                    oldContainer.className = oldContainer.className.replace('setores-admin-v4', 'setores-admin-v5');
                    console.log('Container atualizado para v5.');
                } else {
                    return;
                }
            }

            state.isInitialized = true;

            // Define a URL base
            state.baseUrl = getBaseUrl();
            CONFIG.apiUrl = state.baseUrl;

            try {
                // Inicializa componentes
                initDataTable();
                initSearch();
                initFilters();
                initBatchActions();
                replaceDropdownWithButtons();
                initModals();
                initReplication();
                initPagination();

                // Aplica cores aos avatares
                applyAvatarColors();

                // Aplica animações e efeitos visuais
                applyAnimationsAndEffects();

                // Inicializa tooltips melhorados
                initEnhancedTooltips();

                console.log('Setores Admin v5.0 inicializado com sucesso!');
            } catch (error) {
                console.error('Erro ao inicializar Setores Admin:', error);
            }
        }

        /**
         * Inicializa tooltips melhorados para os botões de ação
         */
        function initEnhancedTooltips() {
            // Adiciona z-index elevado para os botões de ação
            const actionButtons = getElements('.btn-action');
            actionButtons.forEach(button => {
                button.style.position = 'relative';
                button.style.zIndex = '5';
            });
        }

        /**
         * Aplica animações e efeitos visuais
         */
        function applyAnimationsAndEffects() {
            // Adiciona efeito de hover nos cards de estatísticas
            const statCards = getElements('.stat-card');
            statCards.forEach(card => {
                card.classList.add('hover-lift');
            });

            // Adiciona animação de fade-in nas linhas da tabela
            const tableRows = getElements(CONFIG.selectors.table.rows);
            tableRows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.05}s`;
                row.classList.add('fade-in');
            });

            // Adiciona efeito de pulse para destacar elementos importantes
            const newSetorBtn = getElement('a[href*="setores/criar"]');
            if (newSetorBtn) {
                newSetorBtn.classList.add('pulse');
            }
        }

        /**
         * Obtém um elemento do DOM pelo seletor
         * @param {string} selector - Seletor CSS
         * @returns {HTMLElement|null} - Elemento encontrado ou null
         */
        function getElement(selector) {
            return document.querySelector(selector);
        }

        /**
         * Obtém múltiplos elementos do DOM pelo seletor
         * @param {string} selector - Seletor CSS
         * @returns {NodeList} - Lista de elementos encontrados
         */
        function getElements(selector) {
            return document.querySelectorAll(selector);
        }

        /**
         * Adiciona um evento a um elemento
         * @param {HTMLElement} element - Elemento alvo
         * @param {string} event - Nome do evento
         * @param {Function} callback - Função de callback
         */
        function addEvent(element, event, callback) {
            if (element) {
                element.addEventListener(event, callback);
            }
        }

        /**
         * Adiciona um evento a múltiplos elementos
         * @param {NodeList} elements - Lista de elementos
         * @param {string} event - Nome do evento
         * @param {Function} callback - Função de callback
         */
        function addEventToAll(elements, event, callback) {
            elements.forEach(element => {
                addEvent(element, event, callback);
            });
        }

        /**
         * Inicializa a busca
         */
        function initSearch() {
            const searchInput = getElement(CONFIG.selectors.search.input);
            if (!searchInput) return;

            // Remove event listeners existentes
            const newSearchInput = searchInput.cloneNode(true);
            if (searchInput.parentNode) {
                searchInput.parentNode.replaceChild(newSearchInput, searchInput);
            }

            // Adiciona novo event listener seguro
            addEvent(newSearchInput, 'input', function () {
                performSearch(this.value);
            });

            // Botão de limpar busca
            const clearSearchBtn = getElement(CONFIG.selectors.search.clearBtn);
            if (clearSearchBtn) {
                const newClearBtn = clearSearchBtn.cloneNode(true);
                if (clearSearchBtn.parentNode) {
                    clearSearchBtn.parentNode.replaceChild(newClearBtn, clearSearchBtn);
                }

                addEvent(newClearBtn, 'click', function () {
                    const searchInput = getElement(CONFIG.selectors.search.input);
                    if (searchInput) {
                        searchInput.value = '';
                        performSearch('');
                        searchInput.focus();
                    }
                });
            }
        }

        /**
         * Executa a busca de forma segura
         * @param {string} searchTerm - Termo de busca
         */
        function performSearch(searchTerm) {
            try {
                const term = searchTerm.toLowerCase().trim();
                const statusFilter = getElement(CONFIG.selectors.filters.status);
                const statusValue = statusFilter ? statusFilter.value.toLowerCase() : '';

                // Atualiza botão de limpar
                const clearSearchBtn = getElement(CONFIG.selectors.search.clearBtn);
                if (clearSearchBtn) {
                    clearSearchBtn.style.display = term ? 'block' : 'none';
                }

                // Filtra as linhas
                const tableRows = getElements(CONFIG.selectors.table.rows);
                const noResults = getElement(CONFIG.selectors.search.noResults);
                const tableContainer = getElement(CONFIG.selectors.search.tableContainer);

                let visibleCount = 0;

                tableRows.forEach(row => {
                    const nome = row.getAttribute('data-nome') || '';
                    const status = row.getAttribute('data-status') || '';

                    const matchesSearch = nome.toLowerCase().includes(term);
                    const matchesStatus = statusValue === '' || status.toLowerCase() === statusValue;

                    if (matchesSearch && matchesStatus) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Atualiza elementos de UI
                if (noResults) {
                    noResults.style.display = visibleCount === 0 && (term || statusValue) ? 'flex' : 'none';
                }

                if (tableContainer) {
                    tableContainer.style.display = visibleCount === 0 && (term || statusValue) ? 'none' : 'block';
                }

                // Atualiza paginação
                updatePagination();
                goToPage(1);

            } catch (error) {
                console.error('Erro na busca:', error);
            }
        }

        /**
         * Inicializa a tabela de dados
         */
        function initDataTable() {
            initCheckboxes();
            initSorting();
        }

        /**
         * Inicializa os checkboxes da tabela
         */
        function initCheckboxes() {
            const selectAllCheckbox = getElement(CONFIG.selectors.table.selectAll);
            const rowCheckboxes = getElements(CONFIG.selectors.table.rowCheckbox);

            if (!selectAllCheckbox) return;

            // Evento para selecionar/desselecionar todos
            addEvent(selectAllCheckbox, 'change', () => {
                rowCheckboxes.forEach(checkbox => {
                    if (!checkbox.disabled) {
                        checkbox.checked = selectAllCheckbox.checked;
                        const row = checkbox.closest('tr');
                        if (row) {
                            row.classList.toggle('selected', checkbox.checked);
                        }
                    }
                });

                updateSelectedCount();
                updateBatchButtons();
            });

            // Eventos para checkboxes individuais
            addEventToAll(rowCheckboxes, 'change', function () {
                const row = this.closest('tr');
                if (row) {
                    row.classList.toggle('selected', this.checked);
                }

                // Verifica se todos estão selecionados
                const allChecked = Array.from(rowCheckboxes)
                    .filter(cb => !cb.disabled)
                    .every(cb => cb.checked);

                selectAllCheckbox.checked = allChecked;

                updateSelectedCount();
                updateBatchButtons();
            });

            // Botões de selecionar/desselecionar todos
            const selectAllBtn = getElement(CONFIG.selectors.batch.selectAllBtn);
            const deselectAllBtn = getElement(CONFIG.selectors.batch.deselectAllBtn);

            if (selectAllBtn) {
                addEvent(selectAllBtn, 'click', () => {
                    selectAllCheckbox.checked = true;
                    selectAllCheckbox.dispatchEvent(new Event('change'));

                    selectAllBtn.style.display = 'none';
                    if (deselectAllBtn) deselectAllBtn.style.display = 'inline-block';
                });
            }

            if (deselectAllBtn) {
                addEvent(deselectAllBtn, 'click', () => {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.dispatchEvent(new Event('change'));

                    deselectAllBtn.style.display = 'none';
                    if (selectAllBtn) selectAllBtn.style.display = 'inline-block';
                });
            }
        }

        /**
         * Inicializa a ordenação da tabela
         */
        function initSorting() {
            const sortableHeaders = getElements(CONFIG.selectors.table.sortableHeaders);
            const sortOrderSelect = getElement(CONFIG.selectors.table.sortOrder);

            if (!sortableHeaders.length) return;

            // Função para ordenar a tabela
            function sortTable(column, direction) {
                const tableBody = getElement(`${CONFIG.selectors.table.container} tbody`);
                if (!tableBody) return;

                const rows = Array.from(tableBody.querySelectorAll('tr:not(.empty-row)'));

                // Remove classes de ordenação
                sortableHeaders.forEach(header => {
                    header.classList.remove('sort-asc', 'sort-desc');
                });

                // Adiciona classe ao cabeçalho atual
                const currentHeader = getElement(`${CONFIG.selectors.table.sortableHeaders}[data-sort="${column}"]`);
                if (currentHeader) {
                    currentHeader.classList.add(direction === 'asc' ? 'sort-asc' : 'sort-desc');
                }

                // Ordena as linhas
                rows.sort((a, b) => {
                    let valueA, valueB;

                    switch (column) {
                        case 'id':
                            valueA = parseInt(a.getAttribute('data-id') || '0', 10);
                            valueB = parseInt(b.getAttribute('data-id') || '0', 10);
                            break;
                        case 'nome':
                            valueA = (a.getAttribute('data-nome') || '').toLowerCase();
                            valueB = (b.getAttribute('data-nome') || '').toLowerCase();
                            break;
                        case 'usuarios':
                            valueA = parseInt(a.getAttribute('data-usuarios') || '0', 10);
                            valueB = parseInt(b.getAttribute('data-usuarios') || '0', 10);
                            break;
                        case 'status':
                            valueA = (a.getAttribute('data-status') || '').toLowerCase();
                            valueB = (b.getAttribute('data-status') || '').toLowerCase();
                            break;
                        default:
                            return 0;
                    }

                    if (valueA < valueB) {
                        return direction === 'asc' ? -1 : 1;
                    }
                    if (valueA > valueB) {
                        return direction === 'asc' ? 1 : -1;
                    }
                    return 0;
                });

                // Reordena as linhas no DOM
                rows.forEach(row => {
                    tableBody.appendChild(row);
                });

                // Atualiza a paginação após ordenar
                updatePagination();
            }

            // Eventos para cabeçalhos ordenáveis
            addEventToAll(sortableHeaders, 'click', function () {
                const column = this.getAttribute('data-sort');
                const currentDirection = this.classList.contains('sort-asc') ? 'desc' : 'asc';

                sortTable(column, currentDirection);

                // Atualiza o select de ordenação
                if (sortOrderSelect) {
                    sortOrderSelect.value = `${column}_${currentDirection}`;
                    localStorage.setItem(CONFIG.storage.sortOrder, sortOrderSelect.value);
                }
            });

            // Evento para o select de ordenação
            if (sortOrderSelect) {
                // Carrega a ordenação salva
                const savedSort = localStorage.getItem(CONFIG.storage.sortOrder);
                if (savedSort) {
                    sortOrderSelect.value = savedSort;
                    const [column, direction] = savedSort.split('_');
                    sortTable(column, direction);
                } else {
                    // Ordenação padrão
                    sortTable('id', 'asc');
                }

                addEvent(sortOrderSelect, 'change', () => {
                    const [column, direction] = sortOrderSelect.value.split('_');
                    sortTable(column, direction);
                    localStorage.setItem(CONFIG.storage.sortOrder, sortOrderSelect.value);
                });
            }
        }

        /**
         * Inicializa os filtros
         */
        function initFilters() {
            const statusFilter = getElement(CONFIG.selectors.filters.status);

            if (!statusFilter) return;

            // Carrega o filtro salvo
            const savedStatus = localStorage.getItem(CONFIG.storage.statusFilter);
            if (savedStatus) {
                statusFilter.value = savedStatus;
                statusFilter.dispatchEvent(new Event('change'));
            }

            // Evento para o filtro de status
            addEvent(statusFilter, 'change', () => {
                localStorage.setItem(CONFIG.storage.statusFilter, statusFilter.value);

                const searchInput = getElement(CONFIG.selectors.search.input);
                if (searchInput) {
                    performSearch(searchInput.value);
                }
            });
        }

        /**
         * Inicializa as ações em lote
         */
        function initBatchActions() {
            const batchActionsBtn = getElement(CONFIG.selectors.batch.btn);
            const batchActionsPanel = getElement(CONFIG.selectors.batch.panel);
            const closeBatchPanel = getElement(CONFIG.selectors.batch.closeBtn);

            if (!batchActionsBtn || !batchActionsPanel) return;

            // Botão para mostrar/esconder o painel
            addEvent(batchActionsBtn, 'click', () => {
                const isVisible = batchActionsPanel.style.display !== 'none';

                if (isVisible) {
                    batchActionsPanel.style.opacity = '0';
                    batchActionsPanel.style.transform = 'translateY(20px)';

                    setTimeout(() => {
                        batchActionsPanel.style.display = 'none';
                    }, CONFIG.animationDuration);
                } else {
                    batchActionsPanel.style.display = 'block';
                    batchActionsPanel.style.opacity = '0';
                    batchActionsPanel.style.transform = 'translateY(20px)';

                    setTimeout(() => {
                        batchActionsPanel.style.opacity = '1';
                        batchActionsPanel.style.transform = 'translateY(0)';
                    }, 10);

                    batchActionsPanel.classList.add('fade-in');
                }
            });

            // Botão para fechar o painel
            if (closeBatchPanel) {
                addEvent(closeBatchPanel, 'click', () => {
                    batchActionsPanel.style.opacity = '0';
                    batchActionsPanel.style.transform = 'translateY(20px)';

                    setTimeout(() => {
                        batchActionsPanel.style.display = 'none';
                    }, CONFIG.animationDuration);
                });
            }

            // Botões de ação em lote
            const batchActivate = getElement(CONFIG.selectors.batch.activateBtn);
            const batchDeactivate = getElement(CONFIG.selectors.batch.deactivateBtn);
            const batchRemove = getElement(CONFIG.selectors.batch.removeBtn);

            if (batchActivate) {
                addEvent(batchActivate, 'click', () => openBatchModal('activate'));
            }

            if (batchDeactivate) {
                addEvent(batchDeactivate, 'click', () => openBatchModal('deactivate'));
            }

            if (batchRemove) {
                addEvent(batchRemove, 'click', () => openBatchModal('remove'));
            }

            updateBatchButtons();
        }

        /**
         * Atualiza a contagem de setores selecionados
         */
        function updateSelectedCount() {
            const selectedCount = getElement(CONFIG.selectors.batch.selectedCount);
            const checkboxes = getElements(`${CONFIG.selectors.table.rowCheckbox}:checked:not(${CONFIG.selectors.table.selectAll})`);

            if (selectedCount) {
                const count = checkboxes.length;
                selectedCount.textContent = `${count} ${count === 1 ? 'setor selecionado' : 'setores selecionados'}`;
            }
        }

        /**
         * Atualiza o estado dos botões de ação em lote
         */
        function updateBatchButtons() {
            const batchActivate = getElement(CONFIG.selectors.batch.activateBtn);
            const batchDeactivate = getElement(CONFIG.selectors.batch.deactivateBtn);
            const batchRemove = getElement(CONFIG.selectors.batch.removeBtn);

            const checkboxes = getElements(`${CONFIG.selectors.table.rowCheckbox}:checked:not(${CONFIG.selectors.table.selectAll})`);
            const hasSelection = checkboxes.length > 0;

            if (batchActivate) batchActivate.disabled = !hasSelection;
            if (batchDeactivate) batchDeactivate.disabled = !hasSelection;
            if (batchRemove) batchRemove.disabled = !hasSelection;

            if (batchRemove && hasSelection) {
                const canRemoveAll = Array.from(checkboxes).every(checkbox => {
                    const row = checkbox.closest('tr');
                    if (row) {
                        const usuarios = parseInt(row.getAttribute('data-usuarios') || '0', 10);
                        return usuarios === 0;
                    }
                    return false;
                });

                batchRemove.disabled = !canRemoveAll;
            }
        }

        /**
         * Substitui o dropdown por botões diretos
         */
        function replaceDropdownWithButtons() {
            const actionMenus = getElements(CONFIG.selectors.actionMenu.container);

            actionMenus.forEach(menu => {
                const dropdownItems = menu.querySelectorAll('.dropdown-item');
                if (!dropdownItems.length) return;

                const buttonsContainer = document.createElement('div');
                buttonsContainer.className = 'action-buttons-inline';

                dropdownItems.forEach(item => {
                    const href = item.getAttribute('href') || '#';
                    const iconElement = item.querySelector('i');
                    const icon = iconElement ? iconElement.className : 'fas fa-cog';
                    const text = item.textContent.trim();

                    let buttonClass = 'edit';

                    if (text.includes('Remover')) {
                        buttonClass = 'remove';
                    } else if (text.includes('Ativar') || text.includes('Desativar')) {
                        buttonClass = 'toggle-active';
                    } else if (text.includes('Restaurar')) {
                        buttonClass = 'restore';
                    }

                    const button = document.createElement('a');
                    button.href = href;
                    button.className = `btn-action ${buttonClass}`;
                    button.innerHTML = `<i class="${icon}"></i>`;
                    button.setAttribute('data-tooltip', text);

                    if (text.includes('Remover')) {
                        button.href = 'javascript:void(0)';
                        button.setAttribute('data-id', item.getAttribute('data-id') || '');
                        button.setAttribute('data-nome', item.getAttribute('data-nome') || '');

                        button.addEventListener('click', function () {
                            const id = this.getAttribute('data-id');
                            const nome = this.getAttribute('data-nome');
                            openRemoveModal(id, nome);
                        });
                    }

                    buttonsContainer.appendChild(button);
                });

                if (menu.parentNode) {
                    menu.parentNode.replaceChild(buttonsContainer, menu);
                }
            });
        }

        /**
         * Inicializa os modais
         */
        function initModals() {
            initModal(
                CONFIG.selectors.modals.remove.container,
                [CONFIG.selectors.modals.remove.closeBtn, CONFIG.selectors.modals.remove.cancelBtn]
            );

            initModal(
                CONFIG.selectors.modals.batch.container,
                [CONFIG.selectors.modals.batch.closeBtn, CONFIG.selectors.modals.batch.cancelBtn]
            );

            const confirmBatchAction = getElement(CONFIG.selectors.modals.batch.confirmBtn);
            if (confirmBatchAction) {
                addEvent(confirmBatchAction, 'click', executeBatchAction);
            }

            initModal(
                CONFIG.selectors.modals.replicate.container,
                [CONFIG.selectors.modals.replicate.closeBtn, CONFIG.selectors.modals.replicate.cancelBtn]
            );

            const replicateUsersCard = getElement(CONFIG.selectors.modals.replicate.card);
            if (replicateUsersCard) {
                addEvent(replicateUsersCard, 'click', () => {
                    openModal(CONFIG.selectors.modals.replicate.container);
                    resetReplication();
                });
            }
        }

        /**
         * Inicializa um modal
         * @param {string} modalSelector - Seletor do modal
         * @param {Array<string>} closeButtonSelectors - Array de seletores dos botões de fechar
         * @returns {HTMLElement} - Elemento do modal
         */
        function initModal(modalSelector, closeButtonSelectors) {
            const modal = getElement(modalSelector);

            if (!modal) return null;

            closeButtonSelectors.forEach(selector => {
                const btn = getElement(selector);
                if (btn) {
                    addEvent(btn, 'click', () => closeModal(modal));
                }
            });

            addEvent(modal, 'click', function (e) {
                if (e.target === this) {
                    closeModal(this);
                }
            });

            return modal;
        }

        /**
         * Abre um modal
         * @param {string|HTMLElement} modal - Seletor ou elemento do modal
         */
        function openModal(modal) {
            const modalElement = typeof modal === 'string' ? getElement(modal) : modal;

            if (!modalElement) return;

            modalElement.classList.add('fade-in');
            modalElement.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        /**
         * Fecha um modal
         * @param {HTMLElement} modal - Elemento do modal
         */
        function closeModal(modal) {
            if (!modal) return;

            modal.style.opacity = '0';
            modal.style.transform = 'translateY(20px)';

            setTimeout(() => {
                modal.classList.remove('active');
                modal.style.opacity = '';
                modal.style.transform = '';
                document.body.style.overflow = '';
            }, CONFIG.animationDuration);
        }

        /**
         * Abre o modal de remoção
         * @param {string} id - ID do setor
         * @param {string} nome - Nome do setor
         */
        function openRemoveModal(id, nome) {
            const removeModal = getElement(CONFIG.selectors.modals.remove.container);
            const removeModalMessage = getElement(CONFIG.selectors.modals.remove.message);
            const confirmRemove = getElement(CONFIG.selectors.modals.remove.confirmBtn);

            if (!removeModal || !removeModalMessage || !confirmRemove) return;

            removeModalMessage.textContent = `Tem certeza que deseja remover o setor "${nome}"?`;
            confirmRemove.href = `${state.baseUrl}${CONFIG.endpoints.remove}${id}`;

            openModal(removeModal);
        }

        /**
         * Abre o modal de ação em lote
         * @param {string} action - Ação a ser executada (activate, deactivate, remove)
         */
        function openBatchModal(action) {
            const batchModal = getElement(CONFIG.selectors.modals.batch.container);
            const batchModalTitle = getElement(CONFIG.selectors.modals.batch.title);
            const batchModalMessage = getElement(CONFIG.selectors.modals.batch.message);
            const batchModalList = getElement(CONFIG.selectors.modals.batch.list);
            const batchModalWarning = getElement(CONFIG.selectors.modals.batch.warning);
            const confirmBatchAction = getElement(CONFIG.selectors.modals.batch.confirmBtn);
            const batchModalIcon = getElement(CONFIG.selectors.modals.batch.icon);

            if (!batchModal) return;

            batchModal.dataset.action = action;

            const modalConfig = {
                activate: {
                    title: 'Ativar Setores',
                    message: 'Tem certeza que deseja ativar os seguintes setores?',
                    btnClass: 'btn-primary',
                    btnHtml: '<i class="fas fa-check"></i> Ativar',
                    showWarning: false,
                    iconClass: 'modal-icon success',
                    iconHtml: '<i class="fas fa-check-circle"></i>'
                },
                deactivate: {
                    title: 'Desativar Setores',
                    message: 'Tem certeza que deseja desativar os seguintes setores?',
                    btnClass: 'btn-secondary',
                    btnHtml: '<i class="fas fa-times"></i> Desativar',
                    showWarning: false,
                    iconClass: 'modal-icon info',
                    iconHtml: '<i class="fas fa-info-circle"></i>'
                },
                remove: {
                    title: 'Remover Setores',
                    message: 'Tem certeza que deseja remover os seguintes setores?',
                    btnClass: 'btn-danger',
                    btnHtml: '<i class="fas fa-trash"></i> Remover',
                    showWarning: true,
                    iconClass: 'modal-icon danger',
                    iconHtml: '<i class="fas fa-exclamation-triangle"></i>'
                }
            };

            const config = modalConfig[action];

            if (batchModalTitle) batchModalTitle.textContent = config.title;
            if (batchModalMessage) batchModalMessage.textContent = config.message;
            if (confirmBatchAction) {
                confirmBatchAction.className = config.btnClass;
                confirmBatchAction.innerHTML = config.btnHtml;
            }
            if (batchModalWarning) batchModalWarning.style.display = config.showWarning ? 'block' : 'none';
            if (batchModalIcon) {
                batchModalIcon.className = config.iconClass;
                batchModalIcon.innerHTML = config.iconHtml;
            }

            if (batchModalList) {
                batchModalList.innerHTML = '';
                const selectedRows = getElements(`${CONFIG.selectors.table.rowCheckbox}:checked:not(${CONFIG.selectors.table.selectAll})`);

                selectedRows.forEach(checkbox => {
                    const row = checkbox.closest('tr');
                    if (row) {
                        const id = row.getAttribute('data-id') || '';
                        const nome = row.getAttribute('data-nome') || '';

                        const listItem = document.createElement('div');
                        listItem.className = 'modal-list-item';
                        listItem.innerHTML = `
                            <span class="setor-id">#${id}</span>
                            <span class="setor-name">${nome}</span>
                        `;

                        batchModalList.appendChild(listItem);
                    }
                });
            }

            openModal(batchModal);
        }

        /**
         * Executa a ação em lote
         */
        function executeBatchAction() {
            const batchModal = getElement(CONFIG.selectors.modals.batch.container);
            if (!batchModal) return;

            const action = batchModal.dataset.action;
            const selectedIds = Array.from(getElements(`${CONFIG.selectors.table.rowCheckbox}:checked:not(${CONFIG.selectors.table.selectAll})`))
                .map(checkbox => {
                    const row = checkbox.closest('tr');
                    return row ? row.getAttribute('data-id') || '' : '';
                })
                .filter(id => id !== '');

            if (selectedIds.length === 0) {
                closeModal(batchModal);
                return;
            }

            const endpoints = {
                activate: CONFIG.endpoints.batchActivate,
                deactivate: CONFIG.endpoints.batchDeactivate,
                remove: CONFIG.endpoints.batchRemove
            };

            const url = endpoints[action];

            if (!url) {
                console.error('Ação em lote desconhecida:', action);
                closeModal(batchModal);
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `${state.baseUrl}${url}`;
            form.style.display = 'none';

            selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });

            const csrfToken = getElement('meta[name="csrf-token"]');
            if (csrfToken) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'csrf_token';
                input.value = csrfToken.content;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        }

        /**
         * Inicializa a funcionalidade de replicação
         */
        function initReplication() {
            initSourceSectorCards();
            initTargetSectorCheckboxes();
            initSectorSearch();

            const confirmReplicate = getElement(CONFIG.selectors.modals.replicate.confirmBtn);
            if (confirmReplicate) {
                confirmReplicate.disabled = true;
            }

            const replicateUsersCard = getElement(CONFIG.selectors.modals.replicate.card);
            if (replicateUsersCard) {
                addEvent(replicateUsersCard, 'click', () => {
                    openModal(CONFIG.selectors.modals.replicate.container);
                    resetReplication();
                });
            }
        }

        /**
         * Inicializa os cards de setor de origem
         */
        function initSourceSectorCards() {
            const sourceCards = getElements(CONFIG.selectors.modals.replicate.sourceCards);
            const sourceRadios = getElements(CONFIG.selectors.modals.replicate.sourceRadios);

            addEventToAll(sourceCards, 'click', function () {
                sourceCards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');

                const radio = this.querySelector(CONFIG.selectors.modals.replicate.sourceRadios);
                if (radio) {
                    radio.checked = true;
                    state.selectedSourceId = radio.value;
                    loadSourceUsers(radio.value);
                    updateTargetSectors(radio.value);
                    updateReplicateButton();
                }
            });

            const selectBtns = getElements(CONFIG.selectors.modals.replicate.selectSourceBtn);
            addEventToAll(selectBtns, 'click', function (e) {
                e.stopPropagation();
                const card = this.closest(CONFIG.selectors.modals.replicate.sourceCards);
                if (card) card.click();
            });

            addEventToAll(sourceRadios, 'change', function () {
                if (this.checked) {
                    const card = this.closest(CONFIG.selectors.modals.replicate.sourceCards);
                    if (card) card.click();
                }
            });
        }

        /**
         * Inicializa os checkboxes de setores de destino
         */
        function initTargetSectorCheckboxes() {
            const targetCheckboxes = getElements(CONFIG.selectors.modals.replicate.targetCheckboxes);
            const targetSelectedCount = getElement('#targetSelectedCount');

            addEventToAll(targetCheckboxes, 'change', function () {
                const card = this.closest(CONFIG.selectors.modals.replicate.targetCards);
                if (card) {
                    card.classList.toggle('selected', this.checked);
                }

                state.selectedTargetIds = Array.from(getElements(`${CONFIG.selectors.modals.replicate.targetCheckboxes}:checked`))
                    .map(cb => cb.value);

                if (targetSelectedCount) {
                    const count = state.selectedTargetIds.length;
                    targetSelectedCount.textContent = `${count} ${count === 1 ? 'setor de destino selecionado' : 'setores de destino selecionados'}`;
                }

                updateReplicateButton();
            });
        }

        /**
         * Inicializa a busca nos setores
         */
        function initSectorSearch() {
            const sourceSearch = getElement('#sourceSearch');
            const targetSearch = getElement('#targetSearch');
            const sourceCards = getElements(CONFIG.selectors.modals.replicate.sourceCards);
            const targetCards = getElements(CONFIG.selectors.modals.replicate.targetCards);
            const noSourceResults = getElement('#noSourceResults');
            const noTargetResults = getElement('#noTargetResults');

            if (sourceSearch) {
                addEvent(sourceSearch, 'input', () => {
                    const searchTerm = sourceSearch.value.toLowerCase().trim();
                    let visibleCount = 0;

                    sourceCards.forEach(card => {
                        const nome = card.getAttribute('data-nome') || '';
                        const matches = nome.toLowerCase().includes(searchTerm);

                        card.style.display = matches ? '' : 'none';
                        if (matches) visibleCount++;
                    });

                    if (noSourceResults) {
                        noSourceResults.style.display = visibleCount === 0 ? 'flex' : 'none';
                    }
                });
            }

            if (targetSearch) {
                addEvent(targetSearch, 'input', () => {
                    const searchTerm = targetSearch.value.toLowerCase().trim();
                    let visibleCount = 0;

                    targetCards.forEach(card => {
                        if (card.classList.contains('disabled')) return;

                        const nome = card.getAttribute('data-nome') || '';
                        const matches = nome.toLowerCase().includes(searchTerm);

                        card.style.display = matches ? '' : 'none';
                        if (matches) visibleCount++;
                    });

                    if (noTargetResults) {
                        noTargetResults.style.display = visibleCount === 0 ? 'flex' : 'none';
                    }
                });
            }
        }

        /**
         * Carrega os usuários do setor selecionado
         * @param {string} sourceId - ID do setor de origem
         */
        function loadSourceUsers(sourceId) {
            const sourceUsersContainer = getElement(CONFIG.selectors.modals.replicate.usersContainer);

            if (!sourceUsersContainer) {
                console.error('Container de usuários não encontrado');
                return;
            }

            if (!sourceId) {
                sourceUsersContainer.innerHTML = CONFIG.templates.defaultUsers;
                return;
            }

            sourceUsersContainer.innerHTML = CONFIG.templates.loadingUsers;

            fetch(`${CONFIG.apiUrl}${CONFIG.endpoints.getUsuarios}${sourceId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro ao carregar usuários');
                    }
                    return response.json();
                })
                .then(responseData => {
                    const usuarios = responseData.usuarios || [];
                    state.sourceUsers = usuarios;

                    const selectedUsersCount = getElement('#selectedUsersCount');
                    if (selectedUsersCount) {
                        const countSpan = selectedUsersCount.querySelector('span');
                        if (countSpan) {
                            countSpan.textContent = usuarios.length;
                        }
                    }

                    if (!usuarios || usuarios.length === 0) {
                        sourceUsersContainer.innerHTML = CONFIG.templates.noUsers;
                        return;
                    }

                    let html = `<div class="users-list">`;

                    usuarios.forEach((usuario, index) => {
                        const iniciais = usuario.nome
                            ? usuario.nome.split(' ')
                                .map(n => n.charAt(0))
                                .slice(0, 2)
                                .join('')
                            : '?';

                        const isPrincipal = usuario.principal === 1 || usuario.principal === true;
                        const corUsuario = generateColorFromString(usuario.nome || '');

                        html += `
                        <div class="user-item fade-in" style="animation-delay: ${index * 0.05}s">
                            <div class="user-avatar" style="background-color: ${corUsuario}">
                                ${iniciais}
                            </div>
                            <div class="user-info">
                                                            <p class="user-name">
                                    ${usuario.nome || 'Sem nome'}
                                    ${isPrincipal ? '<span class="user-principal"><i class="fas fa-crown"></i> Principal</span>' : ''}
                                </p>
                                <p class="user-email">${usuario.email || ''}</p>
                            </div>
                        </div>
                    `;
                    });

                    html += `</div>`;

                    sourceUsersContainer.innerHTML = html;
                    updateReplicateButton();
                })
                .catch(error => {
                    console.error('Erro ao carregar usuários:', error);
                    sourceUsersContainer.innerHTML = CONFIG.templates.errorUsers;
                });
        }

        /**
         * Atualiza os setores de destino com base no setor de origem
         * @param {string} sourceId - ID do setor de origem
         */
        function updateTargetSectors(sourceId) {
            const targetCards = getElements(CONFIG.selectors.modals.replicate.targetCards);

            targetCards.forEach(card => {
                const cardId = card.getAttribute('data-id') || '';
                const checkbox = card.querySelector(CONFIG.selectors.modals.replicate.targetCheckboxes);

                if (cardId === sourceId) {
                    card.classList.add('disabled');
                    if (checkbox) {
                        checkbox.disabled = true;
                        checkbox.checked = false;
                    }
                } else {
                    card.classList.remove('disabled');
                    if (checkbox) {
                        checkbox.disabled = false;
                    }
                }
            });

            state.selectedTargetIds = Array.from(getElements(`${CONFIG.selectors.modals.replicate.targetCheckboxes}:checked:not(:disabled)`))
                .map(cb => cb.value);

            const targetSelectedCount = getElement('#targetSelectedCount');
            if (targetSelectedCount) {
                const count = state.selectedTargetIds.length;
                targetSelectedCount.textContent = `${count} ${count === 1 ? 'setor de destino selecionado' : 'setores de destino selecionados'}`;
            }

            updateReplicateButton();
        }

        /**
         * Atualiza o estado do botão de confirmar replicação
         */
        function updateReplicateButton() {
            const confirmReplicate = getElement(CONFIG.selectors.modals.replicate.confirmBtn);
            if (!confirmReplicate) return;

            const hasSourceSetor = state.selectedSourceId !== null;
            const hasTargetSetores = state.selectedTargetIds.length > 0;
            const hasSourceUsers = state.sourceUsers.length > 0;

            confirmReplicate.disabled = !(hasSourceSetor && hasTargetSetores && hasSourceUsers);
        }

        /**
         * Reseta o estado da replicação
         */
        function resetReplication() {
            state.selectedSourceId = null;
            state.selectedTargetIds = [];
            state.sourceUsers = [];

            const sourceCards = getElements(CONFIG.selectors.modals.replicate.sourceCards);
            const sourceRadios = getElements(CONFIG.selectors.modals.replicate.sourceRadios);

            sourceCards.forEach(card => card.classList.remove('selected'));
            sourceRadios.forEach(radio => radio.checked = false);

            const targetCards = getElements(CONFIG.selectors.modals.replicate.targetCards);
            const targetCheckboxes = getElements(CONFIG.selectors.modals.replicate.targetCheckboxes);

            targetCards.forEach(card => {
                card.classList.remove('selected', 'disabled');
            });

            targetCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
                checkbox.disabled = false;
            });

            const sourceUsersContainer = getElement(CONFIG.selectors.modals.replicate.usersContainer);
            if (sourceUsersContainer) {
                sourceUsersContainer.innerHTML = CONFIG.templates.defaultUsers;
            }

            const sourceSearch = getElement('#sourceSearch');
            const targetSearch = getElement('#targetSearch');

            if (sourceSearch) sourceSearch.value = '';
            if (targetSearch) targetSearch.value = '';

            sourceCards.forEach(card => card.style.display = '');
            targetCards.forEach(card => card.style.display = '');

            const noSourceResults = getElement('#noSourceResults');
            const noTargetResults = getElement('#noTargetResults');

            if (noSourceResults) noSourceResults.style.display = 'none';
            if (noTargetResults) noTargetResults.style.display = 'none';

            const selectedUsersCount = getElement('#selectedUsersCount');
            const targetSelectedCount = getElement('#targetSelectedCount');

            if (selectedUsersCount) {
                const countSpan = selectedUsersCount.querySelector('span');
                if (countSpan) {
                    countSpan.textContent = '0';
                }
            }

            if (targetSelectedCount) {
                targetSelectedCount.textContent = '0 setores de destino selecionados';
            }

            const confirmReplicate = getElement(CONFIG.selectors.modals.replicate.confirmBtn);
            if (confirmReplicate) {
                confirmReplicate.disabled = true;
            }
        }

        /**
         * Inicializa a paginação
         */
        function initPagination() {
            let paginationContainer = getElement(CONFIG.selectors.pagination.container);
            if (!paginationContainer) {
                paginationContainer = document.createElement('div');
                paginationContainer.className = 'pagination-container';

                const dataContainer = getElement('.data-container');
                if (dataContainer) {
                    dataContainer.appendChild(paginationContainer);
                }
            }

            const savedPage = localStorage.getItem(CONFIG.storage.currentPage);
            if (savedPage) {
                state.currentPage = parseInt(savedPage, 10);
            }

            updatePagination();
            goToPage(state.currentPage);
        }

        /**
         * Atualiza a paginação
         */
        function updatePagination() {
            const tableRows = getElements(CONFIG.selectors.table.rows);
            state.visibleRows = Array.from(tableRows).filter(row => row.style.display !== 'none');

            state.totalPages = Math.ceil(state.visibleRows.length / CONFIG.rowsPerPage);

            if (state.currentPage > state.totalPages) {
                state.currentPage = Math.max(1, state.totalPages);
            }

            renderPagination();
        }

        /**
         * Renderiza a paginação
         */
        function renderPagination() {
            const paginationContainer = getElement(CONFIG.selectors.pagination.container);
            if (!paginationContainer) return;

            paginationContainer.innerHTML = '';

            if (state.totalPages <= 1) {
                paginationContainer.style.display = 'none';
                return;
            } else {
                paginationContainer.style.display = 'flex';
            }

            const paginationList = document.createElement('ul');
            paginationList.className = 'pagination';

            // Botão anterior
            const prevItem = document.createElement('li');
            prevItem.className = 'pagination-item';
            const prevLink = document.createElement('a');
            prevLink.href = 'javascript:void(0)';
            prevLink.className = `pagination-link ${state.currentPage === 1 ? 'disabled' : ''}`;
            prevLink.innerHTML = '<i class="fas fa-chevron-left"></i>';
            prevLink.addEventListener('click', function () {
                if (state.currentPage > 1) {
                    goToPage(state.currentPage - 1);
                }
            });
            prevItem.appendChild(prevLink);
            paginationList.appendChild(prevItem);

            // Páginas
            for (let i = 1; i <= state.totalPages; i++) {
                const pageItem = document.createElement('li');
                pageItem.className = 'pagination-item';
                const pageLink = document.createElement('a');
                pageLink.href = 'javascript:void(0)';
                pageLink.className = `pagination-link ${i === state.currentPage ? 'active' : ''}`;
                pageLink.textContent = i;
                pageLink.addEventListener('click', function () {
                    goToPage(i);
                });
                pageItem.appendChild(pageLink);
                paginationList.appendChild(pageItem);
            }

            // Botão próximo
            const nextItem = document.createElement('li');
            nextItem.className = 'pagination-item';
            const nextLink = document.createElement('a');
            nextLink.href = 'javascript:void(0)';
            nextLink.className = `pagination-link ${state.currentPage === state.totalPages ? 'disabled' : ''}`;
            nextLink.innerHTML = '<i class="fas fa-chevron-right"></i>';
            nextLink.addEventListener('click', function () {
                if (state.currentPage < state.totalPages) {
                    goToPage(state.currentPage + 1);
                }
            });
            nextItem.appendChild(nextLink);
            paginationList.appendChild(nextItem);

            paginationContainer.appendChild(paginationList);

            // Adiciona informação de paginação
            const paginationInfo = document.createElement('div');
            paginationInfo.className = 'pagination-info';

            const startIndex = (state.currentPage - 1) * CONFIG.rowsPerPage + 1;
            const endIndex = Math.min(state.currentPage * CONFIG.rowsPerPage, state.visibleRows.length);

            paginationInfo.textContent = `Mostrando ${startIndex} a ${endIndex} de ${state.visibleRows.length} registros`;
            paginationContainer.appendChild(paginationInfo);
        }

        /**
         * Vai para uma página específica
         * @param {number} page - Número da página
         */
        function goToPage(page) {
            state.currentPage = page;
            localStorage.setItem(CONFIG.storage.currentPage, state.currentPage);

            const tableRows = getElements(CONFIG.selectors.table.rows);
            tableRows.forEach(row => {
                row.style.display = 'none';
            });

            const startIndex = (state.currentPage - 1) * CONFIG.rowsPerPage;
            const endIndex = Math.min(startIndex + CONFIG.rowsPerPage, state.visibleRows.length);

            for (let i = startIndex; i < endIndex; i++) {
                if (state.visibleRows[i]) {
                    state.visibleRows[i].style.display = '';
                }
            }

            renderPagination();
        }

        /**
         * Aplica cores aos avatares
         */
        function applyAvatarColors() {
            const avatars = getElements(CONFIG.selectors.avatars);

            avatars.forEach(avatar => {
                const name = avatar.getAttribute('data-name') || '';
                if (!avatar.style.backgroundColor) {
                    avatar.style.backgroundColor = generateColorFromString(name);
                }
            });
        }

        /**
         * Gera uma cor a partir de uma string
         * @param {string} str - String para gerar a cor
         * @returns {string} - Cor em formato hexadecimal
         */
        function generateColorFromString(str) {
            if (!str) return CONFIG.colors[0];

            let hash = 0;
            for (let i = 0; i < str.length; i++) {
                hash = str.charCodeAt(i) + ((hash << 5) - hash);
            }

            const index = Math.abs(hash) % CONFIG.colors.length;
            return CONFIG.colors[index];
        }

        /**
         * Obtém a URL base do sistema
         * @returns {string} URL base
         */
        function getBaseUrl() {
            const baseUrlElement = getElement('a[href*="setores/admin"]');
            if (baseUrlElement) {
                const href = baseUrlElement.getAttribute('href') || '';
                if (href.includes('setores/admin')) {
                    return href.split('setores/admin')[0];
                }
            }

            const currentUrl = window.location.href;
            const urlParts = currentUrl.split('/');

            if (urlParts[urlParts.length - 1] === 'admin' ||
                urlParts[urlParts.length - 1].includes('admin?')) {
                urlParts.pop();
            }

            if (urlParts[urlParts.length - 1] === 'setores') {
                urlParts.pop();
            }

            return urlParts.join('/') + '/';
        }

        // API pública
        return {
            init: init,
            closeModal: closeModal,
            goToPage: goToPage,
            updatePagination: updatePagination,
            loadSourceUsers: loadSourceUsers,
            performSearch: performSearch
        };
    })();

    // Instância única do módulo
    let setoresAdminInstance = null;

    function initSetoresAdmin() {
        if (setoresAdminInstance) {
            console.warn('SetoresAdmin já foi inicializado');
            return setoresAdminInstance;
        }

        setoresAdminInstance = SetoresAdmin;
        setoresAdminInstance.init();

        // Expor globalmente se necessário
        window.SetoresAdmin = SetoresAdmin;
        window.setoresAdminInstance = setoresAdminInstance;

        return setoresAdminInstance;
    }

    // Funções globais para compatibilidade
    window.closeModalFunction = function (modalId) {
        const modal = document.querySelector(modalId);
        if (modal && setoresAdminInstance) {
            setoresAdminInstance.closeModal(modal);
        }
    };

    window.loadSourceUsers = function (sourceId) {
        if (setoresAdminInstance) {
            setoresAdminInstance.loadSourceUsers(sourceId);
        }
    };

    window.performSearch = function (searchTerm) {
        if (setoresAdminInstance) {
            setoresAdminInstance.performSearch(searchTerm);
        }
    };

    // Funções auxiliares globais
    window.generateColorFromName = function (name) {
        const colors = [
            '#4f46e5', '#4338ca', '#3730a3', '#312e81',
            '#2563eb', '#1d4ed8', '#1e40af', '#1e3a8a',
            '#0891b2', '#0e7490', '#155e75', '#164e63',
            '#059669', '#047857', '#065f46', '#064e3b',
            '#7c3aed', '#6d28d9', '#5b21b6', '#4c1d95',
            '#db2777', '#be185d', '#9d174d', '#831843'
        ];

        let hash = 0;
        for (let i = 0; i < name.length; i++) {
            hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }

        const index = Math.abs(hash) % colors.length;
        return colors[index];
    };

    window.getBaseUrl = function () {
        const currentUrl = window.location.href;
        const urlParts = currentUrl.split('/');
        urlParts.pop();
        return urlParts.join('/') + '/';
    };

    window.updateConfirmButton = function () {
        const confirmButton = document.querySelector('#confirmReplicate');
        const sourceSelected = document.querySelector('.source-setor-card.selected');
        const targetSelected = document.querySelector('.target-checkbox:checked');

        if (confirmButton) {
            confirmButton.disabled = !(sourceSelected && targetSelected);
        }
    };

    // Inicializar quando DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initSetoresAdmin();
            fixAllSearchFunctionality();
            fixAllTooltips();
        });
    } else {
        initSetoresAdmin();
        fixAllSearchFunctionality();
        fixAllTooltips();
    }

    // Também executa as correções quando a janela terminar de carregar
    window.addEventListener('load', function () {
        setTimeout(function () {
            fixAllSearchFunctionality();
            fixAllTooltips();
        }, 500);
    });

    // Funções de correção
    function fixAllSearchFunctionality() {
        try {
            // Sobrescreve a função global performSearch
            window.performSearch = function (searchTerm) {
                if (setoresAdminInstance) {
                    setoresAdminInstance.performSearch(searchTerm);
                }
            };

            // Redefine todos os event listeners relacionados à pesquisa
            const searchInput = document.querySelector('#searchInput');
            if (searchInput) {
                const newSearchInput = searchInput.cloneNode(true);
                if (searchInput.parentNode) {
                    searchInput.parentNode.replaceChild(newSearchInput, searchInput);
                }

                newSearchInput.addEventListener('input', function (event) {
                    window.performSearch(event.target.value);
                });
            }

            // Redefine o botão de limpar pesquisa
            const clearSearchBtn = document.querySelector('#clearSearch');
            if (clearSearchBtn) {
                const newClearBtn = clearSearchBtn.cloneNode(true);
                if (clearSearchBtn.parentNode) {
                    clearSearchBtn.parentNode.replaceChild(newClearBtn, clearSearchBtn);
                }

                newClearBtn.addEventListener('click', function () {
                    const searchInput = document.querySelector('#searchInput');
                    if (searchInput) {
                        searchInput.value = '';
                        window.performSearch('');
                        searchInput.focus();
                    }
                });
            }

            // Redefine o botão de limpar filtros
            const clearFiltersBtn = document.querySelector('#clearFiltersBtn');
            if (clearFiltersBtn) {
                const newClearFiltersBtn = clearFiltersBtn.cloneNode(true);
                if (clearFiltersBtn.parentNode) {
                    clearFiltersBtn.parentNode.replaceChild(newClearFiltersBtn, clearFiltersBtn);
                }

                newClearFiltersBtn.addEventListener('click', function () {
                    const searchInput = document.querySelector('#searchInput');
                    const statusFilter = document.querySelector('#statusFilter');

                    if (searchInput) searchInput.value = '';
                    if (statusFilter) statusFilter.value = '';

                    if (searchInput) {
                        window.performSearch('');
                    }
                });
            }

        } catch (error) {
            console.error('Erro ao corrigir funcionalidade de busca:', error);
        }
    }

    function fixAllTooltips() {
        try {
            // Remove qualquer tooltip dinâmico existente
            document.querySelectorAll('.custom-tooltip').forEach(el => el.remove());

            // Adiciona os estilos para os tooltips dinâmicos se ainda não existirem
            if (!document.querySelector('#custom-tooltip-styles')) {
                const style = document.createElement('style');
                style.id = 'custom-tooltip-styles';
                style.textContent = `
                    .custom-tooltip {
                        position: fixed;
                        z-index: 9999;
                        background-color: #1f2937;
                        color: white;
                        padding: 6px 10px;
                        border-radius: 6px;
                        font-size: 12px;
                        font-weight: 500;
                        letter-spacing: 0.3px;
                        pointer-events: none;
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                        white-space: nowrap;
                        animation: fadeIn 0.2s ease-in-out;
                    }
                    
                    .custom-tooltip.tooltip-top::after {
                        content: '';
                        position: absolute;
                        bottom: -10px;
                        left: 50%;
                        transform: translateX(-50%);
                        border-width: 5px;
                        border-style: solid;
                        border-color: #1f2937 transparent transparent transparent;
                    }
                    
                    .custom-tooltip.tooltip-bottom::after {
                        content: '';
                        position: absolute;
                        top: -10px;
                        left: 50%;
                        transform: translateX(-50%);
                        border-width: 5px;
                        border-style: solid;
                        border-color: transparent transparent #1f2937 transparent;
                    }
                    
                    @keyframes fadeIn {
                        from {
                            opacity: 0;
                            transform: translateY(5px);
                        }
                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                    
                    .setores-admin-v5 .btn-action::after,
                    .setores-admin-v5 .btn-action::before {
                        display: none !important;
                    }
                `;

                document.head.appendChild(style);
            }

            // Processa todos os botões de ação
            const actionButtons = document.querySelectorAll('.btn-action');

            actionButtons.forEach(button => {
                if (button.hasAttribute('data-tooltip-processed')) {
                    return;
                }

                button.setAttribute('data-tooltip-processed', 'true');

                const tooltipText = button.getAttribute('data-tooltip');
                if (!tooltipText) return;

                const newButton = button.cloneNode(true);
                newButton.setAttribute('data-tooltip-processed', 'true');

                if (button.parentNode) {
                    button.parentNode.replaceChild(newButton, button);

                    newButton.addEventListener('mouseenter', function () {
                        if (this._tooltip) {
                            document.body.removeChild(this._tooltip);
                            this._tooltip = null;
                        }

                        const tooltip = document.createElement('div');
                        tooltip.className = 'custom-tooltip';
                        tooltip.textContent = tooltipText;
                        document.body.appendChild(tooltip);

                        const rect = this.getBoundingClientRect();
                        const isInFirstRows = this.closest('tr') &&
                            Array.from(this.closest('tbody').querySelectorAll('tr')).indexOf(this.closest('tr')) < 3;

                        if (isInFirstRows) {
                            tooltip.style.top = `${rect.bottom + 10}px`;
                            tooltip.classList.add('tooltip-bottom');
                        } else {
                            tooltip.style.top = `${rect.top - tooltip.offsetHeight - 10}px`;
                            tooltip.classList.add('tooltip-top');
                        }

                        tooltip.style.left = `${rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)}px`;
                        this._tooltip = tooltip;
                    });

                    newButton.addEventListener('mouseleave', function () {
                        if (this._tooltip) {
                            document.body.removeChild(this._tooltip);
                            this._tooltip = null;
                        }
                    });
                }
            });

        } catch (error) {
            console.error('Erro ao corrigir tooltips:', error);
        }
    }

})(); // Fim da IIFE