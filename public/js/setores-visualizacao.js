/**
 * setores-visualizacao-v2.js
 * Script modernizado para a página de visualização de setores
 * 
 * @version 2.0
 * @author Desenvolvedor
 */

document.addEventListener('DOMContentLoaded', function () {
    // Inicializa tooltips
    initTooltips();

    // Inicializa a busca de setores
    initSearch();

    // Inicializa a alternância de visualização (grid/tabela)
    initViewToggle();

    // Gera cores para os avatares
    generateAvatarColors();

    // Inicializa a funcionalidade de alteração de status
    initStatusToggle();

    // Corrige as legendas dos status
    fixStatusLegends();

    // Log de inicialização bem-sucedida
    console.log('Script setores-visualizacao-v2.js inicializado com sucesso.');
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

    if (!searchInput) return;

    // Função para realizar a busca
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();

        // Mostra/esconde o botão de limpar
        if (clearSearch) {
            clearSearch.style.display = searchTerm ? 'block' : 'none';
        }

        // Filtra os cards
        const setorCards = document.querySelectorAll('.setor-card');
        const setorRows = document.querySelectorAll('.setor-row');
        let visibleCount = 0;

        // Filtra os cards
        setorCards.forEach(card => {
            const nome = card.getAttribute('data-nome').toLowerCase();
            const status = card.getAttribute('data-status').toLowerCase();
            const chamados = card.getAttribute('data-chamados');
            const tempo = card.getAttribute('data-tempo');

            if (nome.includes(searchTerm) ||
                status.includes(searchTerm) ||
                chamados.includes(searchTerm) ||
                tempo.includes(searchTerm)) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Filtra as linhas da tabela
        setorRows.forEach(row => {
            const nome = row.getAttribute('data-nome').toLowerCase();
            const status = row.getAttribute('data-status').toLowerCase();
            const chamados = row.getAttribute('data-chamados');
            const tempo = row.getAttribute('data-tempo');

            if (nome.includes(searchTerm) ||
                status.includes(searchTerm) ||
                chamados.includes(searchTerm) ||
                tempo.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Mostra/esconde a mensagem de nenhum resultado
        const noResults = document.getElementById('noResults');
        const cardsView = document.getElementById('cardsView');
        const tableView = document.getElementById('tableView');

        if (noResults) {
            if (visibleCount === 0 && searchTerm) {
                noResults.style.display = 'flex';
                cardsView.style.display = 'none';
                tableView.style.display = 'none';
            } else {
                noResults.style.display = 'none';

                // Restaura a visualização atual
                const gridViewBtn = document.getElementById('gridViewBtn');
                if (gridViewBtn && gridViewBtn.classList.contains('active')) {
                    cardsView.style.display = 'grid';
                    tableView.style.display = 'none';
                } else {
                    cardsView.style.display = 'none';
                    tableView.style.display = 'block';
                }
            }
        }
    }

    // Evento de input para busca em tempo real
    searchInput.addEventListener('input', performSearch);

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
            performSearch();
        });
    }
}

/**
 * Inicializa a alternância de visualização (grid/tabela)
 */
function initViewToggle() {
    const gridViewBtn = document.getElementById('gridViewBtn');
    const tableViewBtn = document.getElementById('tableViewBtn');
    const cardsView = document.getElementById('cardsView');
    const tableView = document.getElementById('tableView');

    if (!gridViewBtn || !tableViewBtn || !cardsView || !tableView) return;

    // Verifica se há uma preferência salva
    const savedView = localStorage.getItem('setoresViewPreference');
    if (savedView === 'table') {
        gridViewBtn.classList.remove('active');
        tableViewBtn.classList.add('active');
        cardsView.style.display = 'none';
        tableView.style.display = 'block';
    }

    // Evento para visualização em grid
    gridViewBtn.addEventListener('click', function () {
        gridViewBtn.classList.add('active');
        tableViewBtn.classList.remove('active');
        cardsView.style.display = 'grid';
        tableView.style.display = 'none';
        localStorage.setItem('setoresViewPreference', 'grid');
    });

    // Evento para visualização em tabela
    tableViewBtn.addEventListener('click', function () {
        gridViewBtn.classList.remove('active');
        tableViewBtn.classList.add('active');
        cardsView.style.display = 'none';
        tableView.style.display = 'block';
        localStorage.setItem('setoresViewPreference', 'table');
    });
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
    if (!str) return '#6366f1';

    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        hash = str.charCodeAt(i) + ((hash << 5) - hash);
    }

    const colors = [
        '#6366f1', '#4f46e5', '#7c3aed', '#8b5cf6',
        '#10b981', '#3b82f6', '#6d28d9', '#ec4899',
        '#ef4444', '#f59e0b', '#fbbf24', '#0f766e'
    ];

    const index = Math.abs(hash) % colors.length;
    return colors[index];
}

/**
 * Inicializa a funcionalidade de alteração de status
 */
function initStatusToggle() {
    const toggleButtons = document.querySelectorAll('.toggle-status');
    const statusModal = document.getElementById('statusModal');
    const confirmButton = document.getElementById('confirmStatusChange');
    const modalMessage = document.getElementById('statusModalMessage');

    if (!toggleButtons.length) return;

    let currentSetorId = null;
    let currentStatus = null;

    // Inicializa o modal se existir
    let modal = null;
    if (statusModal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        modal = new bootstrap.Modal(statusModal);
    }

    // Evento para os botões de alteração de status
    toggleButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();

            currentSetorId = this.getAttribute('data-setor-id');
            currentStatus = this.getAttribute('data-status');

            // Se temos um modal, abrimos ele
            if (modal && modalMessage) {
                modalMessage.textContent = currentStatus === '1'
                    ? 'Tem certeza que deseja ativar este setor?'
                    : 'Tem certeza que deseja desativar este setor?';
                modal.show();
            } else {
                // Caso contrário, confirmamos diretamente
                if (confirm('Tem certeza que deseja alterar o status deste setor?')) {
                    alterarStatusSetor(currentSetorId, currentStatus);
                }
            }
        });
    });

    // Evento para o botão de confirmação no modal
    if (confirmButton) {
        confirmButton.addEventListener('click', function () {
            if (!currentSetorId || !currentStatus) {
                if (modal) modal.hide();
                return;
            }

            alterarStatusSetor(currentSetorId, currentStatus);

            if (modal) modal.hide();
        });
    }
}

/**
 * Altera o status de um setor
 * @param {string} setorId ID do setor
 * @param {string} status Novo status (0 ou 1)
 */
function alterarStatusSetor(setorId, status) {
    // Faz a requisição para alterar o status
    fetch(`/api/setores/status/${setorId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ status: status })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recarrega a página para mostrar as alterações
                window.location.reload();
            } else {
                alert('Erro ao alterar o status do setor: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro ao alterar status:', error);
            alert('Erro ao alterar o status do setor. Tente novamente mais tarde.');
        });
}

/**
 * Corrige as legendas dos status dos chamados
 */
function fixStatusLegends() {
    // Corrige as classes das legendas de status
    const legendItems = document.querySelectorAll('.legend-item');

    legendItems.forEach(item => {
        const legendText = item.querySelector('.legend-text');
        if (!legendText) return;

        const text = legendText.textContent.trim().toLowerCase();

        // Verifica se o texto contém o status e aplica a classe correta
        if (text.includes('aberto')) {
            const colorSpan = item.querySelector('.legend-color');
            if (colorSpan) {
                colorSpan.className = 'legend-color bg-warning';
            }
        } else if (text.includes('atendimento') || text.includes('andamento')) {
            const colorSpan = item.querySelector('.legend-color');
            if (colorSpan) {
                colorSpan.className = 'legend-color bg-primary';
            }
        } else if (text.includes('pausado')) {
            const colorSpan = item.querySelector('.legend-color');
            if (colorSpan) {
                colorSpan.className = 'legend-color bg-info';
            }
        } else if (text.includes('concluído') || text.includes('concluido')) {
            const colorSpan = item.querySelector('.legend-color');
            if (colorSpan) {
                colorSpan.className = 'legend-color bg-success';
            }
        } else if (text.includes('cancelado')) {
            const colorSpan = item.querySelector('.legend-color');
            if (colorSpan) {
                colorSpan.className = 'legend-color bg-secondary';
            }
        }
    });

    // Corrige as cores dos segmentos da barra de progresso
    const progressSegments = document.querySelectorAll('.progress-segment');

    progressSegments.forEach(segment => {
        const title = segment.getAttribute('title');
        if (!title) return;

        const titleLower = title.toLowerCase();

        if (titleLower.includes('aberto')) {
            segment.className = 'progress-segment bg-warning';
        } else if (titleLower.includes('atendimento') || titleLower.includes('andamento')) {
            segment.className = 'progress-segment bg-primary';
        } else if (titleLower.includes('pausado')) {
            segment.className = 'progress-segment bg-info';
        } else if (titleLower.includes('concluído') || titleLower.includes('concluido')) {
            segment.className = 'progress-segment bg-success';
        } else if (titleLower.includes('cancelado')) {
            segment.className = 'progress-segment bg-secondary';
        }
    });
}