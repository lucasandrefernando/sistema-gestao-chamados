/**
 * setores-admin-index-v2.js
 * Script para a página de administração de setores
 * 
 * @version 2.0
 * @author Desenvolvedor
 */

document.addEventListener('DOMContentLoaded', function () {
    // Verifica se estamos na página de admin de setores
    const adminPage = document.querySelector('.setores-admin-index-v2');
    if (!adminPage) return;

    // Inicializa os componentes da página
    initTooltips();
    initSearch();
    initStatusFilter();
    initSorting();
    initRemoveModal();
    initBatchActions();
    generateAvatarColors();

    console.log('Script setores-admin-index-v2.js inicializado com sucesso.');
});

/**
 * Inicializa tooltips do Bootstrap
 */
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltips.length > 0 && typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
    }
}

/**
 * Inicializa a busca de setores
 */
function initSearch() {
    const searchInput = document.getElementById('searchInput');
    const clearSearch = document.getElementById('clearSearch');
    const clearFilters = document.getElementById('clearFilters');
    const statusFilter = document.getElementById('statusFilter');

    if (!searchInput) return;

    // Função para realizar a busca
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const statusValue = statusFilter ? statusFilter.value.toLowerCase() : '';

        // Mostra/esconde o botão de limpar
        if (clearSearch) {
            clearSearch.style.display = searchTerm ? 'block' : 'none';
        }

        // Filtra as linhas da tabela
        const tableRows = document.querySelectorAll('.admin-table tbody tr:not(.empty-row)');
        let visibleCount = 0;

        tableRows.forEach(row => {
            const nome = row.getAttribute('data-nome').toLowerCase();
            const status = row.getAttribute('data-status').toLowerCase();

            // Verifica se atende aos critérios de busca e filtro
            const matchesSearch = nome.includes(searchTerm);
            const matchesStatus = statusValue === '' || status === statusValue;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Mostra/esconde a mensagem de nenhum resultado
        const noResults = document.getElementById('noResults');
        const tableContainer = document.querySelector('.table-container');

        if (noResults && tableContainer) {
            if (visibleCount === 0 && (searchTerm || statusValue)) {
                noResults.style.display = 'flex';
                tableContainer.style.display = 'none';
            } else {
                noResults.style.display = 'none';
                tableContainer.style.display = 'block';
            }
        }

        // Atualiza a contagem de selecionados
        updateSelectedCount();
    }

    // Evento de input para busca em tempo real
    searchInput.addEventListener('input', performSearch);

    // Evento para o filtro de status
    if (statusFilter) {
        statusFilter.addEventListener('change', performSearch);
    }

    // Botão para limpar a busca
    if (clearSearch) {
        clearSearch.addEventListener('click', function () {
            searchInput.value = '';
            searchInput.focus();
            performSearch();
        });
    }

    // Botão para limpar filtros na mensagem de nenhum resultado
    if (clearFilters) {
        clearFilters.addEventListener('click', function () {
            searchInput.value = '';
            if (statusFilter) {
                statusFilter.value = '';
            }
            performSearch();
        });
    }
}

/**
 * Inicializa o filtro de status
 */
function initStatusFilter() {
    const statusFilter = document.getElementById('statusFilter');

    if (!statusFilter) return;

    // Verifica se há um valor salvo no localStorage
    const savedStatus = localStorage.getItem('setoresAdminStatusFilter');
    if (savedStatus) {
        statusFilter.value = savedStatus;
        // Dispara o evento change para aplicar o filtro
        const event = new Event('change');
        statusFilter.dispatchEvent(event);
    }

    // Salva a seleção no localStorage
    statusFilter.addEventListener('change', function () {
        localStorage.setItem('setoresAdminStatusFilter', this.value);
    });
}

/**
 * Inicializa a ordenação da tabela
 */
function initSorting() {
    const sortableHeaders = document.querySelectorAll('.sortable');
    const sortOrder = document.getElementById('sortOrder');

    if (!sortableHeaders.length) return;

    // Função para ordenar as linhas da tabela
    function sortTable(column, direction) {
        const tbody = document.querySelector('.admin-table tbody');
        const rows = Array.from(tbody.querySelectorAll('tr:not(.empty-row)'));

        // Remove classes de ordenação de todos os cabeçalhos
        sortableHeaders.forEach(header => {
            header.classList.remove('sort-asc', 'sort-desc');
        });

        // Adiciona classe de ordenação ao cabeçalho atual
        const currentHeader = document.querySelector(`.sortable[data-sort="${column}"]`);
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
                case 'chamados':
                    valueA = parseInt(a.getAttribute('data-chamados'), 10);
                    valueB = parseInt(b.getAttribute('data-chamados'), 10);
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
            tbody.appendChild(row);
        });
    }

    // Evento para os cabeçalhos ordenáveis
    sortableHeaders.forEach(header => {
        header.addEventListener('click', function () {
            const column = this.getAttribute('data-sort');
            const currentDirection = this.classList.contains('sort-asc') ? 'desc' : 'asc';

            sortTable(column, currentDirection);

            // Atualiza o select de ordenação
            if (sortOrder) {
                sortOrder.value = `${column}_${currentDirection}`;
            }
        });
    });

    // Evento para o select de ordenação
    if (sortOrder) {
        // Verifica se há um valor salvo no localStorage
        const savedSort = localStorage.getItem('setoresAdminSortOrder');
        if (savedSort) {
            sortOrder.value = savedSort;
            const [column, direction] = savedSort.split('_');
            sortTable(column, direction);
        } else {
            // Ordenação padrão por ID ascendente
            sortTable('id', 'asc');
        }

        sortOrder.addEventListener('change', function () {
            const [column, direction] = this.value.split('_');
            sortTable(column, direction);
            localStorage.setItem('setoresAdminSortOrder', this.value);
        });
    }
}

/**
 * Inicializa o modal de remoção
 */
function initRemoveModal() {
    const removeButtons = document.querySelectorAll('.action-remove');
    const modalOverlay = document.getElementById('removeModal');
    const closeModal = document.getElementById('closeModal');
    const cancelRemove = document.getElementById('cancelRemove');
    const confirmRemove = document.getElementById('confirmRemove');
    const modalMessage = document.getElementById('removeModalMessage');

    if (!removeButtons.length || !modalOverlay) return;

    // Função para abrir o modal
    function openModal(id, nome) {
        modalMessage.textContent = `Tem certeza que deseja remover o setor "${nome}"?`;
        confirmRemove.href = `${baseUrl}setores/remover/${id}`;
        modalOverlay.classList.add('active');
        modalOverlay.style.display = 'flex';
    }

    // Função para fechar o modal
    function closeModalFunc() {
        modalOverlay.classList.remove('active');
        setTimeout(() => {
            modalOverlay.style.display = 'none';
        }, 300);
    }

    // Evento para os botões de remoção
    removeButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            const nome = this.getAttribute('data-nome');
            openModal(id, nome);
        });
    });

    // Eventos para fechar o modal
    if (closeModal) {
        closeModal.addEventListener('click', closeModalFunc);
    }

    if (cancelRemove) {
        cancelRemove.addEventListener('click', closeModalFunc);
    }

    // Fecha o modal ao clicar fora dele
    modalOverlay.addEventListener('click', function (e) {
        if (e.target === modalOverlay) {
            closeModalFunc();
        }
    });

    // Fecha o modal com a tecla ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modalOverlay.classList.contains('active')) {
            closeModalFunc();
        }
    });
}

/**
 * Inicializa as ações em lote
 */
function initBatchActions() {
    // Adiciona checkboxes a cada linha da tabela
    const tableRows = document.querySelectorAll('.admin-table tbody tr:not(.empty-row)');
    const tableHeader = document.querySelector('.admin-table thead tr');

    if (!tableRows.length || !tableHeader) return;

    // Adiciona coluna de checkbox no cabeçalho
    const thCheckbox = document.createElement('th');
    thCheckbox.className = 'col-checkbox';
    thCheckbox.style.width = '40px';

    const headerCheckbox = document.createElement('input');
    headerCheckbox.type = 'checkbox';
    headerCheckbox.className = 'row-checkbox';
    headerCheckbox.id = 'selectAllCheckbox';

    thCheckbox.appendChild(headerCheckbox);
    tableHeader.insertBefore(thCheckbox, tableHeader.firstChild);

    // Adiciona checkbox a cada linha
    tableRows.forEach(row => {
        const tdCheckbox = document.createElement('td');
        tdCheckbox.className = 'col-checkbox';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.className = 'row-checkbox';
        checkbox.dataset.id = row.getAttribute('data-id');

        // Não permite selecionar setores removidos
        if (row.classList.contains('row-removed')) {
            checkbox.disabled = true;
        }

        checkbox.addEventListener('change', function () {
            if (this.checked) {
                row.classList.add('row-selected');
            } else {
                row.classList.remove('row-selected');
            }
            updateSelectedCount();
            updateBatchButtons();
        });

        tdCheckbox.appendChild(checkbox);
        row.insertBefore(tdCheckbox, row.firstChild);
    });

    // Selecionar/Desselecionar todos
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const selectAllBtn = document.getElementById('selectAll');
    const deselectAllBtn = document.getElementById('deselectAll');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.row-checkbox:not(:disabled)');
            checkboxes.forEach(checkbox => {
                if (checkbox !== selectAllCheckbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                    const row = checkbox.closest('tr');
                    if (row) {
                        if (selectAllCheckbox.checked) {
                            row.classList.add('row-selected');
                        } else {
                            row.classList.remove('row-selected');
                        }
                    }
                }
            });
            updateSelectedCount();
            updateBatchButtons();

            // Atualiza os botões de selecionar/desselecionar todos
            if (selectAllBtn && deselectAllBtn) {
                if (selectAllCheckbox.checked) {
                    selectAllBtn.style.display = 'none';
                    deselectAllBtn.style.display = 'inline-block';
                } else {
                    selectAllBtn.style.display = 'inline-block';
                    deselectAllBtn.style.display = 'none';
                }
            }
        });
    }

    // Botões de selecionar/desselecionar todos
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function () {
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.dispatchEvent(new Event('change'));
            }
        });
    }

    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function () {
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.dispatchEvent(new Event('change'));
            }
        });
    }

    // Botões de ação em lote
    const batchActivate = document.getElementById('batchActivate');
    const batchDeactivate = document.getElementById('batchDeactivate');
    const batchRemove = document.getElementById('batchRemove');
    const batchModal = document.getElementById('batchModal');
    const batchModalTitle = document.getElementById('batchModalTitle');
    const batchModalMessage = document.getElementById('batchModalMessage');
    const batchModalList = document.getElementById('batchModalList');
    const batchModalWarning = document.getElementById('batchModalWarning');
    const confirmBatchAction = document.getElementById('confirmBatchAction');
    const cancelBatchAction = document.getElementById('cancelBatchAction');
    const closeBatchModal = document.getElementById('closeBatchModal');

    let currentBatchAction = '';

    // Função para abrir o modal de ação em lote
    function openBatchModal(action) {
        if (!batchModal) return;

        currentBatchAction = action;

        // Configura o modal de acordo com a ação
        switch (action) {
            case 'activate':
                batchModalTitle.textContent = 'Ativar Setores';
                batchModalMessage.textContent = 'Tem certeza que deseja ativar os seguintes setores?';
                confirmBatchAction.className = 'btn-primary';
                confirmBatchAction.innerHTML = '<i class="fas fa-check me-2"></i> Ativar';
                batchModalWarning.style.display = 'none';
                break;
            case 'deactivate':
                batchModalTitle.textContent = 'Desativar Setores';
                batchModalMessage.textContent = 'Tem certeza que deseja desativar os seguintes setores?';
                confirmBatchAction.className = 'btn-primary';
                confirmBatchAction.innerHTML = '<i class="fas fa-times me-2"></i> Desativar';
                batchModalWarning.style.display = 'none';
                break;
            case 'remove':
                batchModalTitle.textContent = 'Remover Setores';
                batchModalMessage.textContent = 'Tem certeza que deseja remover os seguintes setores?';
                confirmBatchAction.className = 'btn-danger';
                confirmBatchAction.innerHTML = '<i class="fas fa-trash me-2"></i> Remover';
                batchModalWarning.style.display = 'block';
                break;
        }

        // Preenche a lista de setores selecionados
        batchModalList.innerHTML = '';
        const selectedRows = document.querySelectorAll('.row-checkbox:checked:not(#selectAllCheckbox)');

        selectedRows.forEach(checkbox => {
            const row = checkbox.closest('tr');
            if (row) {
                const id = checkbox.dataset.id;
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

        // Exibe o modal
        batchModal.classList.add('active');
        batchModal.style.display = 'flex';
    }

    // Função para fechar o modal de ação em lote
    function closeBatchModalFunc() {
        if (!batchModal) return;

        batchModal.classList.remove('active');
        setTimeout(() => {
            batchModal.style.display = 'none';
        }, 300);
    }

    // Eventos para os botões de ação em lote
    if (batchActivate) {
        batchActivate.addEventListener('click', function () {
            openBatchModal('activate');
        });
    }

    if (batchDeactivate) {
        batchDeactivate.addEventListener('click', function () {
            openBatchModal('deactivate');
        });
    }

    if (batchRemove) {
        batchRemove.addEventListener('click', function () {
            openBatchModal('remove');
        });
    }

    // Eventos para fechar o modal de ação em lote
    if (closeBatchModal) {
        closeBatchModal.addEventListener('click', closeBatchModalFunc);
    }

    if (cancelBatchAction) {
        cancelBatchAction.addEventListener('click', closeBatchModalFunc);
    }

    // Fecha o modal ao clicar fora dele
    if (batchModal) {
        batchModal.addEventListener('click', function (e) {
            if (e.target === batchModal) {
                closeBatchModalFunc();
            }
        });
    }

    // Confirma a ação em lote
    if (confirmBatchAction) {
        confirmBatchAction.addEventListener('click', function () {
            const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked:not(#selectAllCheckbox)'))
                .map(checkbox => checkbox.dataset.id);

            if (selectedIds.length === 0) {
                closeBatchModalFunc();
                return;
            }

            // Executa a ação em lote
            let url = '';
            let method = 'POST';

            switch (currentBatchAction) {
                case 'activate':
                    url = `${baseUrl}setores/batch-activate`;
                    break;
                case 'deactivate':
                    url = `${baseUrl}setores/batch-deactivate`;
                    break;
                case 'remove':
                    url = `${baseUrl}setores/batch-remove`;
                    break;
            }

            // Cria um formulário para enviar os IDs
            const form = document.createElement('form');
            form.method = method;
            form.action = url;
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
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
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
        });
    }
}

/**
 * Atualiza a contagem de setores selecionados
 */
function updateSelectedCount() {
    const selectedCount = document.getElementById('selectedCount');
    const checkboxes = document.querySelectorAll('.row-checkbox:checked:not(#selectAllCheckbox)');

    if (selectedCount) {
        const count = checkboxes.length;
        selectedCount.textContent = `${count} ${count === 1 ? 'setor selecionado' : 'setores selecionados'}`;
    }
}

/**
 * Atualiza o estado dos botões de ação em lote
 */
function updateBatchButtons() {
    const batchActivate = document.getElementById('batchActivate');
    const batchDeactivate = document.getElementById('batchDeactivate');
    const batchRemove = document.getElementById('batchRemove');

    const checkboxes = document.querySelectorAll('.row-checkbox:checked:not(#selectAllCheckbox)');
    const hasSelection = checkboxes.length > 0;

    // Habilita/desabilita os botões com base na seleção
    if (batchActivate) batchActivate.disabled = !hasSelection;
    if (batchDeactivate) batchDeactivate.disabled = !hasSelection;
    if (batchRemove) batchRemove.disabled = !hasSelection;

    // Verifica se todos os setores selecionados podem ser removidos
    if (batchRemove && hasSelection) {
        const canRemoveAll = Array.from(checkboxes).every(checkbox => {
            const row = checkbox.closest('tr');
            if (row) {
                const chamados = parseInt(row.getAttribute('data-chamados'), 10);
                const usuarios = parseInt(row.getAttribute('data-usuarios'), 10);
                return chamados === 0 && usuarios === 0;
            }
            return false;
        });

        batchRemove.disabled = !canRemoveAll;
    }
}

/**
 * Gera cores para os avatares
 */
function generateAvatarColors() {
    const avatars = document.querySelectorAll('[data-name]');

    avatars.forEach(avatar => {
        const name = avatar.getAttribute('data-name');
        const color = generateColorFromString(name);
        avatar.style.backgroundColor = color;
    });
}

/**
 * Gera uma cor baseada em uma string
 * @param {string} str String para gerar a cor
 * @returns {string} Cor em formato hexadecimal
 */
function generateColorFromString(str) {
    if (!str) return '#4f46e5';

    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        hash = str.charCodeAt(i) + ((hash << 5) - hash);
    }

    const colors = [
        '#4f46e5', '#4338ca', '#6d28d9', '#8b5cf6',
        '#10b981', '#3b82f6', '#6d28d9', '#ec4899',
        '#ef4444', '#f59e0b', '#fbbf24', '#0f766e'
    ];

    const index = Math.abs(hash) % colors.length;
    return colors[index];
}

// Variável global para a URL base (deve ser definida no seu HTML)
const baseUrl = document.querySelector('a[href*="setores/admin"]')?.href.split('setores/admin')[0] || '/';