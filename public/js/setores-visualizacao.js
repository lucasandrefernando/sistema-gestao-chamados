/**
 * setores-visualizacao.js - Funcionalidades para a visualização de setores
 * Versão modificada sem filtros de status e sem ordenação
 */

document.addEventListener('DOMContentLoaded', function () {
    // Elementos DOM
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearch');
    const gridViewBtn = document.getElementById('gridViewBtn');
    const tableViewBtn = document.getElementById('tableViewBtn');
    const cardsView = document.getElementById('cardsView');
    const tableView = document.getElementById('tableView');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const noResultsEl = document.getElementById('noResults');

    // Verifica se estamos na página correta (com os elementos necessários)
    if (!cardsView || !tableView) {
        console.log('Não estamos na página de visualização de setores. Script setores-visualizacao.js não será executado completamente.');
        return; // Sai da função se não estiver na página correta
    }

    // Elementos de setor
    const setorCards = document.querySelectorAll('.setor-card');
    const setorRows = document.querySelectorAll('.setor-row');

    // Verifica se há preferência salva no localStorage
    const preferredView = localStorage.getItem('setoresViewPreference') || 'grid';

    // Inicializa a visualização
    if (preferredView === 'table') {
        cardsView.style.display = 'none';
        tableView.style.display = 'block';
        if (gridViewBtn && tableViewBtn) {
            gridViewBtn.classList.remove('active');
            tableViewBtn.classList.add('active');
        }
    }

    // Alternar entre visualizações
    if (gridViewBtn) {
        gridViewBtn.addEventListener('click', function () {
            cardsView.style.display = 'grid';
            tableView.style.display = 'none';
            gridViewBtn.classList.add('active');
            if (tableViewBtn) tableViewBtn.classList.remove('active');
            localStorage.setItem('setoresViewPreference', 'grid');
        });
    }

    if (tableViewBtn) {
        tableViewBtn.addEventListener('click', function () {
            cardsView.style.display = 'none';
            tableView.style.display = 'block';
            if (gridViewBtn) gridViewBtn.classList.remove('active');
            tableViewBtn.classList.add('active');
            localStorage.setItem('setoresViewPreference', 'table');
        });
    }

    // Busca de setores
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase().trim();

            // Mostra/esconde o botão de limpar
            if (clearSearchBtn) {
                if (searchTerm.length > 0) {
                    clearSearchBtn.style.display = 'block';
                } else {
                    clearSearchBtn.style.display = 'none';
                }
            }

            applyFilters();
        });
    }

    // Limpar busca
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function () {
            if (searchInput) {
                searchInput.value = '';
                clearSearchBtn.style.display = 'none';
                applyFilters();
                searchInput.focus();
            }
        });
    }

    // Limpar filtros
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function () {
            if (searchInput) {
                searchInput.value = '';
                if (clearSearchBtn) {
                    clearSearchBtn.style.display = 'none';
                }
                applyFilters();
            }
        });
    }

    // Função para aplicar filtros
    function applyFilters() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';

        // Arrays para armazenar elementos filtrados
        let visibleCards = [];
        let visibleRows = [];

        // Filtra os cards
        if (setorCards && setorCards.length > 0) {
            setorCards.forEach(card => {
                if (card && card.dataset && card.dataset.nome) {
                    const nome = card.dataset.nome.toLowerCase();

                    // Verifica se atende aos critérios de busca
                    const matchesSearch = nome.includes(searchTerm);

                    if (matchesSearch) {
                        card.style.display = '';
                        visibleCards.push(card);
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        }

        // Filtra as linhas da tabela
        if (setorRows && setorRows.length > 0) {
            setorRows.forEach(row => {
                if (row && row.dataset && row.dataset.nome) {
                    const nome = row.dataset.nome.toLowerCase();

                    // Verifica se atende aos critérios de busca
                    const matchesSearch = nome.includes(searchTerm);

                    if (matchesSearch) {
                        row.style.display = '';
                        visibleRows.push(row);
                    } else {
                        row.style.display = 'none';
                    }
                }
            });
        }

        // Mostra mensagem de nenhum resultado se necessário
        if (noResultsEl) {
            if (visibleCards.length === 0 && visibleRows.length === 0) {
                noResultsEl.style.display = 'flex';
                if (cardsView) cardsView.style.display = 'none';
                if (tableView) tableView.style.display = 'none';
            } else {
                noResultsEl.style.display = 'none';
                if (preferredView === 'table') {
                    if (tableView) tableView.style.display = 'block';
                } else {
                    if (cardsView) cardsView.style.display = 'grid';
                }
            }
        }
    }

    // Gera cores para os avatares dos setores
    function generateAvatarColors() {
        const avatars = document.querySelectorAll('[data-name]');

        if (avatars && avatars.length > 0) {
            avatars.forEach(avatar => {
                if (avatar && avatar.dataset && avatar.dataset.name) {
                    const name = avatar.dataset.name;
                    const color = generateColorFromString(name);
                    avatar.style.backgroundColor = color;
                }
            });
        }
    }

    // Gera uma cor baseada em uma string
    function generateColorFromString(str) {
        if (!str) return '#4361ee'; // Cor padrão se a string for vazia

        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }

        const colors = [
            '#4361ee', '#3a0ca3', '#7209b7', '#f72585',
            '#4cc9a0', '#4895ef', '#560bad', '#b5179e',
            '#e63946', '#fb8500', '#ffb703', '#023047'
        ];

        // Usa o hash para selecionar uma cor do array
        const index = Math.abs(hash) % colors.length;
        return colors[index];
    }

    // Inicializa tooltips do Bootstrap
    function initTooltips() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            if (tooltips && tooltips.length > 0) {
                tooltips.forEach(tooltip => {
                    if (tooltip) {
                        new bootstrap.Tooltip(tooltip);
                    }
                });
            }
        }
    }

    // Adiciona atributos data-label para responsividade em telas pequenas
    function addDataLabels() {
        const tableHeadersElements = document.querySelectorAll('.setores-table thead th');
        if (!tableHeadersElements || tableHeadersElements.length === 0) return;

        const tableHeaders = Array.from(tableHeadersElements).map(th => th.textContent.trim());
        const tableRows = document.querySelectorAll('.setores-table tbody tr');

        if (tableRows && tableRows.length > 0 && tableHeaders.length > 0) {
            tableRows.forEach(row => {
                if (row) {
                    const cells = row.querySelectorAll('td');
                    if (cells && cells.length > 0) {
                        cells.forEach((cell, index) => {
                            if (cell && tableHeaders[index]) {
                                cell.setAttribute('data-label', tableHeaders[index]);
                            }
                        });
                    }
                }
            });
        }
    }

    // Inicializa as funções
    generateAvatarColors();
    initTooltips();
    addDataLabels();

    // Log de inicialização bem-sucedida
    console.log('Script setores-visualizacao.js inicializado com sucesso.');
});