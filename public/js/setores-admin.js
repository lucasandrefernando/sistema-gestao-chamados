/**
 * setores-admin-v4.js
 * Script moderno para a página de administração de setores
 * 
 * @version 4.1
 * @author Desenvolvedor
 */

// Módulo de Administração de Setores
const SetoresAdmin = (function () {
    'use strict';

    // Configurações globais parametrizadas
    const CONFIG = {
        animationDuration: 300,
        selectors: {
            container: '.setores-admin-v4',
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
            avatars: '[data-name]'
        },
        colors: [
            '#4361ee', '#3a0ca3', '#7209b7', '#f72585',
            '#4cc9f0', '#4895ef', '#560bad', '#f3722c',
            '#f8961e', '#f9c74f', '#90be6d', '#43aa8b',
            '#577590', '#277da1', '#9d4edd', '#ff9e00'
        ],
        storage: {
            sortOrder: 'setoresAdminSortOrder',
            statusFilter: 'setoresAdminStatusFilter',
            filtersCollapsed: 'setoresAdminFiltersCollapsed'
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
            `
        }
    };

    // Estado da aplicação
    const state = {
        selectedSourceId: null,
        selectedTargetIds: [],
        sourceUsers: [],
        baseUrl: ''
    };

    /**
     * Inicializa a aplicação
     */
    function init() {
        // Verifica se estamos na página correta
        const container = getElement(CONFIG.selectors.container);
        if (!container) {
            console.log('Página de administração de setores não encontrada.');
            return;
        }

        // Define a URL base
        state.baseUrl = getBaseUrl();

        try {
            // Inicializa componentes
            initDataTable();
            initSearch();
            initFilters();
            initBatchActions();
            initActionMenus();
            initModals();
            initReplication();

            // Aplica cores aos avatares
            applyAvatarColors();

            console.log('Setores Admin v4.1 inicializado com sucesso!');
        } catch (error) {
            console.error('Erro ao inicializar Setores Admin:', error);
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
 * Inicializa o toggle de filtros
 */
    function initFilterToggle() {
        const toggleFiltersBtn = getElement('#toggleFilters');
        const filtersBody = getElement('.filters-body');

        if (!toggleFiltersBtn || !filtersBody) return;

        // Verifica se o estado está salvo no localStorage
        const filtersCollapsed = localStorage.getItem('setoresAdminFiltersCollapsed') === 'true';

        // Aplica o estado inicial
        if (filtersCollapsed) {
            filtersBody.style.display = 'none';
            toggleFiltersBtn.classList.add('collapsed');
        }

        // Adiciona o evento de toggle
        addEvent(toggleFiltersBtn, 'click', () => {
            const isVisible = filtersBody.style.display !== 'none';

            if (isVisible) {
                filtersBody.style.display = 'none';
                toggleFiltersBtn.classList.add('collapsed');
                localStorage.setItem('setoresAdminFiltersCollapsed', 'true');
            } else {
                filtersBody.style.display = 'block';
                toggleFiltersBtn.classList.remove('collapsed');
                localStorage.setItem('setoresAdminFiltersCollapsed', 'false');
            }
        });
    }

    // Adicione esta função à inicialização
    function init() {
        // Verifica se estamos na página correta
        const container = getElement(CONFIG.selectors.container);
        if (!container) {
            console.log('Página de administração de setores não encontrada.');
            return;
        }

        // Define a URL base
        state.baseUrl = getBaseUrl();

        try {
            // Inicializa componentes
            initDataTable();
            initSearch();
            initFilters();
            initFilterToggle(); // Nova função
            initBatchActions();
            initActionMenus();
            initModals();
            initReplication();

            // Aplica cores aos avatares
            applyAvatarColors();

            console.log('Setores Admin v4.1 inicializado com sucesso!');
        } catch (error) {
            console.error('Erro ao inicializar Setores Admin:', error);
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
                        valueA = parseInt(a.getAttribute('data-id'), 10);
                        valueB = parseInt(b.getAttribute('data-id'), 10);
                        break;
                    case 'nome':
                        valueA = a.getAttribute('data-nome').toLowerCase();
                        valueB = b.getAttribute('data-nome').toLowerCase();
                        break;
                    case 'usuarios':
                        valueA = parseInt(a.getAttribute('data-usuarios'), 10);
                        valueB = parseInt(b.getAttribute('data-usuarios'), 10);
                        break;
                    case 'status':
                        valueA = a.getAttribute('data-status').toLowerCase();
                        valueB = b.getAttribute('data-status').toLowerCase();
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
  * Inicializa a busca
  */
    function initSearch() {
        const searchInput = getElement(CONFIG.selectors.search.input);
        const clearSearchBtn = getElement(CONFIG.selectors.search.clearBtn);
        const tableRows = getElements(CONFIG.selectors.table.rows);
        const noResults = getElement(CONFIG.selectors.search.noResults);
        const tableContainer = getElement(CONFIG.selectors.search.tableContainer);

        // Verifica se o elemento de busca existe
        if (!searchInput) return;

        // Função para realizar a busca
        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const statusFilter = getElement(CONFIG.selectors.filters.status);
            const statusValue = statusFilter ? statusFilter.value.toLowerCase() : '';

            // Mostra/esconde o botão de limpar
            if (clearSearchBtn) {
                clearSearchBtn.style.display = searchTerm ? 'block' : 'none';
            }

            // Filtra as linhas
            let visibleCount = 0;

            if (tableRows && tableRows.length) {
                tableRows.forEach(row => {
                    const nome = row.getAttribute('data-nome').toLowerCase();
                    const status = row.getAttribute('data-status').toLowerCase();

                    const matchesSearch = nome.includes(searchTerm);
                    const matchesStatus = statusValue === '' || status === statusValue;

                    if (matchesSearch && matchesStatus) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            // Mostra/esconde mensagem de nenhum resultado
            if (noResults && tableContainer) {
                if (visibleCount === 0 && (searchTerm || statusValue)) {
                    noResults.style.display = 'flex';
                    tableContainer.style.display = 'none';
                } else {
                    noResults.style.display = 'none';
                    tableContainer.style.display = 'block';
                }
            }

            // Atualiza a contagem de selecionados, se a função existir
            if (typeof updateSelectedCount === 'function') {
                updateSelectedCount();
            }
        }

        // Evento de input para busca em tempo real
        addEvent(searchInput, 'input', performSearch);

        // Botão para limpar a busca
        if (clearSearchBtn) {
            addEvent(clearSearchBtn, 'click', () => {
                searchInput.value = '';
                searchInput.focus();
                performSearch();
            });
        }

        // Botões para limpar todos os filtros
        const clearFiltersBtn = getElement(CONFIG.selectors.search.clearFiltersBtn);
        const clearFilters = getElement(CONFIG.selectors.search.clearFilters);

        [clearFiltersBtn, clearFilters].forEach(btn => {
            if (btn) {
                addEvent(btn, 'click', () => {
                    searchInput.value = '';

                    const statusFilter = getElement(CONFIG.selectors.filters.status);
                    if (statusFilter) statusFilter.value = '';

                    performSearch();
                });
            }
        });
    }

    /**
 * Verifica se um elemento existe e executa uma função nele
 * @param {HTMLElement|null} element - Elemento a ser verificado
 * @param {Function} callback - Função a ser executada se o elemento existir
 * @returns {any} - Resultado da função ou undefined
 */
    function safeElementOperation(element, callback) {
        if (element) {
            return callback(element);
        }
        return undefined;
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

            // Dispara o evento para aplicar o filtro
            statusFilter.dispatchEvent(new Event('change'));
        }

        // Evento para o filtro de status
        addEvent(statusFilter, 'change', () => {
            // Salva a seleção
            localStorage.setItem(CONFIG.storage.statusFilter, statusFilter.value);

            // Aplica o filtro
            const searchInput = getElement(CONFIG.selectors.search.input);
            if (searchInput) {
                searchInput.dispatchEvent(new Event('input'));
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
            batchActionsPanel.style.display = isVisible ? 'none' : 'block';

            if (!isVisible) {
                // Anima a entrada do painel
                batchActionsPanel.style.opacity = '0';
                batchActionsPanel.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    batchActionsPanel.style.opacity = '1';
                    batchActionsPanel.style.transform = 'translateY(0)';
                }, 10);
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

        // Atualiza o estado inicial dos botões
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

        // Habilita/desabilita os botões
        if (batchActivate) batchActivate.disabled = !hasSelection;
        if (batchDeactivate) batchDeactivate.disabled = !hasSelection;
        if (batchRemove) batchRemove.disabled = !hasSelection;

        // Verifica se todos os setores selecionados podem ser removidos
        if (batchRemove && hasSelection) {
            const canRemoveAll = Array.from(checkboxes).every(checkbox => {
                const row = checkbox.closest('tr');
                if (row) {
                    const usuarios = parseInt(row.getAttribute('data-usuarios'), 10);
                    return usuarios === 0;
                }
                return false;
            });

            batchRemove.disabled = !canRemoveAll;
        }
    }

    /**
     * Inicializa os menus de ações
     */
    function initActionMenus() {
        const actionMenuBtns = getElements(CONFIG.selectors.actionMenu.btn);

        addEventToAll(actionMenuBtns, 'click', function (e) {
            e.stopPropagation();

            const menu = this.closest(CONFIG.selectors.actionMenu.container);

            // Fecha todos os outros menus
            getElements(CONFIG.selectors.actionMenu.active).forEach(m => {
                if (m !== menu) m.classList.remove('active');
            });

            // Abre/fecha o menu atual
            menu.classList.toggle('active');
        });

        // Fecha os menus ao clicar fora
        addEvent(document, 'click', () => {
            getElements(CONFIG.selectors.actionMenu.active).forEach(menu => {
                menu.classList.remove('active');
            });
        });

        // Inicializa os botões de remoção
        const removeButtons = getElements(CONFIG.selectors.actionMenu.removeBtn);

        addEventToAll(removeButtons, 'click', function () {
            const id = this.getAttribute('data-id');
            const nome = this.getAttribute('data-nome');

            openRemoveModal(id, nome);
        });
    }

    /**
     * Inicializa os modais
     */
    function initModals() {
        // Modal de remoção
        initModal(
            CONFIG.selectors.modals.remove.container,
            [CONFIG.selectors.modals.remove.closeBtn, CONFIG.selectors.modals.remove.cancelBtn]
        );

        // Modal de ações em lote
        const batchModal = initModal(
            CONFIG.selectors.modals.batch.container,
            [CONFIG.selectors.modals.batch.closeBtn, CONFIG.selectors.modals.batch.cancelBtn]
        );

        // Confirma a ação em lote
        const confirmBatchAction = getElement(CONFIG.selectors.modals.batch.confirmBtn);
        if (confirmBatchAction) {
            addEvent(confirmBatchAction, 'click', executeBatchAction);
        }

        // Modal de replicação
        initModal(
            CONFIG.selectors.modals.replicate.container,
            [CONFIG.selectors.modals.replicate.closeBtn, CONFIG.selectors.modals.replicate.cancelBtn]
        );

        // Botão para abrir o modal de replicação
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

        // Botões para fechar o modal
        closeButtonSelectors.forEach(selector => {
            const btn = getElement(selector);
            if (btn) {
                addEvent(btn, 'click', () => closeModal(modal));
            }
        });

        // Fecha o modal ao clicar fora
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

        modalElement.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Fecha um modal
     * @param {HTMLElement} modal - Elemento do modal
     */
    function closeModal(modal) {
        if (!modal) return;

        modal.classList.remove('active');

        setTimeout(() => {
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

        // Armazena a ação atual
        batchModal.dataset.action = action;

        // Configura o modal de acordo com a ação
        const modalConfig = {
            activate: {
                title: 'Ativar Setores',
                message: 'Tem certeza que deseja ativar os seguintes setores?',
                btnClass: 'btn-primary',
                btnHtml: '<i class="fas fa-check me-2"></i> Ativar',
                showWarning: false,
                iconClass: 'modal-icon success',
                iconHtml: '<i class="fas fa-check-circle"></i>'
            },
            deactivate: {
                title: 'Desativar Setores',
                message: 'Tem certeza que deseja desativar os seguintes setores?',
                btnClass: 'btn-primary',
                btnHtml: '<i class="fas fa-times me-2"></i> Desativar',
                showWarning: false,
                iconClass: 'modal-icon info',
                iconHtml: '<i class="fas fa-info-circle"></i>'
            },
            remove: {
                title: 'Remover Setores',
                message: 'Tem certeza que deseja remover os seguintes setores?',
                btnClass: 'btn-danger',
                btnHtml: '<i class="fas fa-trash me-2"></i> Remover',
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

        // Preenche a lista de setores selecionados
        if (batchModalList) {
            batchModalList.innerHTML = '';
            const selectedRows = getElements(`${CONFIG.selectors.table.rowCheckbox}:checked:not(${CONFIG.selectors.table.selectAll})`);

            selectedRows.forEach(checkbox => {
                const row = checkbox.closest('tr');
                if (row) {
                    const id = row.getAttribute('data-id');
                    const nome = row.getAttribute('data-nome');

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
            .map(checkbox => checkbox.closest('tr').getAttribute('data-id'));

        if (selectedIds.length === 0) {
            closeModal(batchModal);
            return;
        }

        // Define a URL com base na ação
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

        // Cria um formulário para enviar os IDs
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `${state.baseUrl}${url}`;
        form.style.display = 'none';

        // Adiciona os IDs como campos ocultos
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]'; // Importante: use 'ids[]' para criar um array no PHP
            input.value = id;
            form.appendChild(input);
        });

        // Adiciona o token CSRF se necessário
        const csrfToken = getElement('meta[name="csrf-token"]');
        if (csrfToken) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'csrf_token';
            input.value = csrfToken.content;
            form.appendChild(input);
        }

        // Adiciona o formulário ao documento e o envia
        document.body.appendChild(form);
        form.submit();
    }

    /**
   * Inicializa a funcionalidade de replicação
   */
    function initReplication() {
        // Cards de setor de origem
        initSourceSectorCards();

        // Checkboxes de setores de destino
        initTargetSectorCheckboxes();

        // Busca nos setores
        initSectorSearch();

        // Botão de confirmar replicação
        const confirmReplicate = getElement(CONFIG.selectors.modals.replicate.confirmBtn);
        if (confirmReplicate) {
            confirmReplicate.disabled = true;
        }

        // Botão para abrir o modal de replicação
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
            // Remove a seleção de todos os cards
            sourceCards.forEach(c => c.classList.remove('selected'));

            // Seleciona o card atual
            this.classList.add('selected');

            // Marca o radio correspondente
            const radio = this.querySelector(CONFIG.selectors.modals.replicate.sourceRadios);
            if (radio) {
                radio.checked = true;

                // Atualiza o estado
                state.selectedSourceId = radio.value;

                // Carrega os usuários do setor
                loadSourceUsers(radio.value);

                // Desabilita o setor de origem na lista de destinos
                updateTargetSectors(radio.value);

                // Verifica se pode habilitar o botão de confirmar
                updateReplicateButton();
            }
        });

        // Botão de selecionar
        const selectBtns = getElements(CONFIG.selectors.modals.replicate.selectSourceBtn);
        addEventToAll(selectBtns, 'click', function (e) {
            e.stopPropagation(); // Evita duplo clique no card
            const card = this.closest(CONFIG.selectors.modals.replicate.sourceCards);
            if (card) card.click();
        });

        // Evento para os radios (caso sejam clicados diretamente)
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

            // Atualiza o estado
            state.selectedTargetIds = Array.from(getElements(`${CONFIG.selectors.modals.replicate.targetCheckboxes}:checked`))
                .map(cb => cb.value);

            // Atualiza o contador de setores selecionados
            if (targetSelectedCount) {
                const count = state.selectedTargetIds.length;
                targetSelectedCount.textContent = `${count} ${count === 1 ? 'setor de destino selecionado' : 'setores de destino selecionados'}`;
            }

            // Verifica se pode habilitar o botão de confirmar
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

        // Busca nos setores de origem
        if (sourceSearch) {
            addEvent(sourceSearch, 'input', () => {
                const searchTerm = sourceSearch.value.toLowerCase().trim();
                let visibleCount = 0;

                sourceCards.forEach(card => {
                    const nome = card.getAttribute('data-nome').toLowerCase();
                    const matches = nome.includes(searchTerm);

                    card.style.display = matches ? '' : 'none';
                    if (matches) visibleCount++;
                });

                if (noSourceResults) {
                    noSourceResults.style.display = visibleCount === 0 ? 'flex' : 'none';
                }
            });
        }

        // Busca nos setores de destino
        if (targetSearch) {
            addEvent(targetSearch, 'input', () => {
                const searchTerm = targetSearch.value.toLowerCase().trim();
                let visibleCount = 0;

                targetCards.forEach(card => {
                    if (card.classList.contains('disabled')) return;

                    const nome = card.getAttribute('data-nome').toLowerCase();
                    const matches = nome.includes(searchTerm);

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
     * Carrega os usuários do setor de origem
     * @param {string} setorId - ID do setor
     */
    function loadSourceUsers(setorId) {
        const sourceUsersContainer = getElement(CONFIG.selectors.modals.replicate.usersContainer);
        const selectedUsersCount = getElement('#selectedUsersCount');

        if (!sourceUsersContainer) return;

        // Mostra o loading
        sourceUsersContainer.innerHTML = CONFIG.templates.loadingUsers;

        // Faz a requisição AJAX
        fetch(`${state.baseUrl}${CONFIG.endpoints.getUsuarios}${setorId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    sourceUsersContainer.innerHTML = CONFIG.templates.errorUsers.replace('Erro ao carregar usuários.', `Erro ao carregar usuários: ${data.error}`);
                    return;
                }

                const usuarios = data.usuarios || [];
                state.sourceUsers = usuarios;

                // Atualiza o contador de usuários
                if (selectedUsersCount) {
                    selectedUsersCount.querySelector('span').textContent = usuarios.length;
                }

                if (usuarios.length === 0) {
                    sourceUsersContainer.innerHTML = CONFIG.templates.noUsers;
                    return;
                }

                // Renderiza a lista de usuários
                let html = `<div class="users-list">`;

                usuarios.forEach(usuario => {
                    const iniciais = usuario.nome.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
                    const isPrincipal = usuario.principal == 1;

                    html += `
                    <div class="user-item">
                        <div class="user-avatar" style="background-color: ${generateColorFromString(usuario.nome)}">
                            ${iniciais}
                        </div>
                        <div class="user-info">
                            <p class="user-name">
                                ${usuario.nome}
                                ${isPrincipal ? '<span class="user-principal">Principal</span>' : ''}
                            </p>
                            <p class="user-email">${usuario.email || ''}</p>
                        </div>
                    </div>
                `;
                });

                html += `</div>`;

                sourceUsersContainer.innerHTML = html;
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
        const targetSearch = getElement('#targetSearch');

        targetCards.forEach(card => {
            const cardId = card.getAttribute('data-id');
            const checkbox = card.querySelector(CONFIG.selectors.modals.replicate.targetCheckboxes);

            if (cardId === sourceId) {
                // Desabilita o setor de origem na lista de destinos
                card.classList.add('disabled');
                if (checkbox) {
                    checkbox.disabled = true;
                    checkbox.checked = false;
                }
            } else {
                // Habilita os demais setores
                card.classList.remove('disabled');
                if (checkbox) {
                    checkbox.disabled = false;
                }
            }
        });

        // Atualiza os IDs de destino selecionados
        state.selectedTargetIds = Array.from(getElements(`${CONFIG.selectors.modals.replicate.targetCheckboxes}:checked:not(:disabled)`))
            .map(cb => cb.value);

        // Atualiza o contador de setores selecionados
        const targetSelectedCount = getElement('#targetSelectedCount');
        if (targetSelectedCount) {
            const count = state.selectedTargetIds.length;
            targetSelectedCount.textContent = `${count} ${count === 1 ? 'setor de destino selecionado' : 'setores de destino selecionados'}`;
        }

        // Reaplica a busca atual, se houver
        if (targetSearch && targetSearch.value.trim() !== '') {
            targetSearch.dispatchEvent(new Event('input'));
        }
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
        // Reseta o estado
        state.selectedSourceId = null;
        state.selectedTargetIds = [];
        state.sourceUsers = [];

        // Reseta os cards de origem
        const sourceCards = getElements(CONFIG.selectors.modals.replicate.sourceCards);
        const sourceRadios = getElements(CONFIG.selectors.modals.replicate.sourceRadios);

        sourceCards.forEach(card => card.classList.remove('selected'));
        sourceRadios.forEach(radio => radio.checked = false);

        // Reseta os checkboxes de destino
        const targetCards = getElements(CONFIG.selectors.modals.replicate.targetCards);
        const targetCheckboxes = getElements(CONFIG.selectors.modals.replicate.targetCheckboxes);

        targetCards.forEach(card => {
            card.classList.remove('selected', 'disabled');
        });

        targetCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
            checkbox.disabled = false;
        });

        // Reseta o container de usuários
        const sourceUsersContainer = getElement(CONFIG.selectors.modals.replicate.usersContainer);
        if (sourceUsersContainer) {
            sourceUsersContainer.innerHTML = CONFIG.templates.defaultUsers;
        }

        // Reseta os campos de busca
        const sourceSearch = getElement('#sourceSearch');
        const targetSearch = getElement('#targetSearch');

        if (sourceSearch) sourceSearch.value = '';
        if (targetSearch) targetSearch.value = '';

        // Mostra todos os cards
        sourceCards.forEach(card => card.style.display = '');
        targetCards.forEach(card => card.style.display = '');

        // Esconde as mensagens de "nenhum resultado"
        const noSourceResults = getElement('#noSourceResults');
        const noTargetResults = getElement('#noTargetResults');

        if (noSourceResults) noSourceResults.style.display = 'none';
        if (noTargetResults) noTargetResults.style.display = 'none';

        // Reseta os contadores
        const selectedUsersCount = getElement('#selectedUsersCount');
        const targetSelectedCount = getElement('#targetSelectedCount');

        if (selectedUsersCount) selectedUsersCount.querySelector('span').textContent = '0';
        if (targetSelectedCount) targetSelectedCount.textContent = '0 setores de destino selecionados';

        // Desabilita o botão de confirmar
        const confirmReplicate = getElement(CONFIG.selectors.modals.replicate.confirmBtn);
        if (confirmReplicate) {
            confirmReplicate.disabled = true;
        }
    }


    /**
     * Aplica cores aos avatares
     */
    function applyAvatarColors() {
        const avatars = getElements(CONFIG.selectors.avatars);

        avatars.forEach(avatar => {
            const name = avatar.getAttribute('data-name');
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
  * @returns {string} - URL base
  */
    function getBaseUrl() {
        // Tenta obter a URL base a partir de um link existente
        const baseUrlElement = getElement('a[href*="setores/admin"]');
        if (baseUrlElement) {
            const href = baseUrlElement.getAttribute('href');
            if (href.includes('setores/admin')) {
                return href.split('setores/admin')[0];
            }
        }

        // Se não encontrar, tenta obter a partir da URL atual
        const currentUrl = window.location.href;
        const urlParts = currentUrl.split('/');

        // Remove o último segmento da URL (que deve ser 'admin')
        if (urlParts[urlParts.length - 1] === 'admin' ||
            urlParts[urlParts.length - 1].includes('admin?')) {
            urlParts.pop();
        }

        // Remove 'setores' também, se for o último segmento
        if (urlParts[urlParts.length - 1] === 'setores') {
            urlParts.pop();
        }

        // Retorna a URL base
        return urlParts.join('/') + '/';
    }

    // API pública
    return {
        init: init
    };
})();

// Inicializa o módulo quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', SetoresAdmin.init);

/**
 * Executa a ação em lote
 */
function executeBatchAction() {
    const batchModal = getElement(CONFIG.selectors.modals.batch.container);
    if (!batchModal) return;

    const action = batchModal.dataset.action;
    const selectedIds = Array.from(getElements(`${CONFIG.selectors.table.rowCheckbox}:checked:not(${CONFIG.selectors.table.selectAll})`))
        .map(checkbox => checkbox.closest('tr').getAttribute('data-id'));

    if (selectedIds.length === 0) {
        closeModal(batchModal);
        return;
    }

    // Define a URL com base na ação
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

    // Obtém a URL base
    const baseUrl = getBaseUrl();
    const fullUrl = `${baseUrl}${url}`;

    // Depuração
    console.log('Base URL:', baseUrl);
    console.log('Endpoint:', url);
    console.log('Full URL:', fullUrl);
    console.log('Selected IDs:', selectedIds);

    // Cria um formulário para enviar os IDs
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = fullUrl;
    form.style.display = 'none';

    // Adiciona os IDs como campos ocultos
    selectedIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
    });

    // Adiciona o token CSRF se necessário
    const csrfToken = getElement('meta[name="csrf-token"]');
    if (csrfToken) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'csrf_token';
        input.value = csrfToken.content;
        form.appendChild(input);
    }

    // Adiciona o formulário ao documento e o envia
    document.body.appendChild(form);
    form.submit();
}

/**
 * Obtém um elemento do DOM pelo seletor
 * @param {string} selector - Seletor CSS
 * @param {HTMLElement} [parent=document] - Elemento pai para busca
 * @returns {HTMLElement|null} - Elemento encontrado ou null
 */
function getElement(selector, parent = document) {
    try {
        return parent.querySelector(selector);
    } catch (error) {
        console.warn(`Erro ao buscar elemento com seletor "${selector}":`, error);
        return null;
    }
}

/**
 * Obtém múltiplos elementos do DOM pelo seletor
 * @param {string} selector - Seletor CSS
 * @param {HTMLElement} [parent=document] - Elemento pai para busca
 * @returns {NodeList|[]} - Lista de elementos encontrados ou array vazio
 */
function getElements(selector, parent = document) {
    try {
        return parent.querySelectorAll(selector);
    } catch (error) {
        console.warn(`Erro ao buscar elementos com seletor "${selector}":`, error);
        return [];
    }
}