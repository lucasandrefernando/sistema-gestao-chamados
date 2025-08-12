/**
 * chamados-index.js - Script específico para a página de índice de chamados
 */

document.addEventListener('DOMContentLoaded', function () {
    // Inicializa tooltips do Bootstrap
    initTooltips();

    // Adiciona funcionalidades à tabela
    enhanceTable();

    // Configura o formulário de filtro
    setupFilterForm();

    // Adiciona efeitos de hover aos cards
    setupCardHoverEffects();

    // Configura a responsividade
    setupResponsiveBehavior();
});

/**
 * Inicializa os tooltips do Bootstrap
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            boundary: document.body,
            placement: 'top'
        });
    });
}

/**
 * Adiciona funcionalidades à tabela de chamados
 */
function enhanceTable() {
    const table = document.querySelector('.chamados-index-tabela');
    if (!table) return;

    // Adiciona efeito de hover nas linhas
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        // Adiciona clique na linha para visualizar o chamado
        row.addEventListener('click', function (e) {
            // Ignora se o clique foi em um botão de ação
            if (e.target.closest('.chamados-index-acao-btn')) {
                return;
            }

            // Obtém o ID do chamado
            const idCell = this.querySelector('.chamados-index-id');
            if (idCell) {
                const chamadoId = idCell.textContent;
                const viewLink = this.querySelector('.chamados-index-acao-visualizar');
                if (viewLink) {
                    window.location.href = viewLink.href;
                }
            }
        });

        // Adiciona cursor de ponteiro para indicar que a linha é clicável
        row.style.cursor = 'pointer';
    });

    // Adiciona ordenação nas colunas (exemplo básico)
    const headers = table.querySelectorAll('thead th');
    headers.forEach((header, index) => {
        if (index === 6) return; // Ignora a coluna de ações

        header.style.cursor = 'pointer';
        header.title = 'Clique para ordenar';

        // Adiciona ícone de ordenação
        const sortIcon = document.createElement('span');
        sortIcon.innerHTML = ' <i class="fas fa-sort"></i>';
        sortIcon.style.opacity = '0.5';
        sortIcon.style.marginLeft = '0.25rem';
        header.appendChild(sortIcon);

        // Adiciona evento de clique para ordenação
        header.addEventListener('click', function () {
            // Esta é uma implementação básica para demonstração
            // Em produção, isso seria tratado pelo servidor ou por uma biblioteca de ordenação

            // Atualiza os ícones
            headers.forEach(h => {
                const icon = h.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-sort';
                    icon.style.opacity = '0.5';
                }
            });

            const icon = this.querySelector('i');
            if (icon.classList.contains('fa-sort')) {
                icon.className = 'fas fa-sort-up';
                icon.style.opacity = '1';
            } else if (icon.classList.contains('fa-sort-up')) {
                icon.className = 'fas fa-sort-down';
                icon.style.opacity = '1';
            } else {
                icon.className = 'fas fa-sort';
                icon.style.opacity = '0.5';
            }

            // Aqui você implementaria a lógica real de ordenação
            // Por exemplo, enviando uma requisição AJAX para o servidor
            // ou ordenando os dados no cliente
        });
    });

    // Adiciona zebra-striping às linhas da tabela
    rows.forEach((row, index) => {
        if (index % 2 === 1) {
            row.classList.add('chamados-index-row-striped');
        }
    });
}

/**
 * Configura o formulário de filtro com funcionalidades adicionais
 */
function setupFilterForm() {
    const filterForm = document.querySelector('.chamados-index-filtros-form');
    if (!filterForm) return;

    // Adiciona validação visual aos campos
    const filterSelects = filterForm.querySelectorAll('.chamados-index-filtro-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function () {
            if (this.value) {
                this.style.borderColor = 'var(--chamados-index-primary)';
            } else {
                this.style.borderColor = 'var(--chamados-index-light-gray)';
            }
        });
    });

    // Adiciona efeito de loading ao botão de aplicar filtros
    filterForm.addEventListener('submit', function () {
        const submitBtn = this.querySelector('.chamados-index-filtro-btn-aplicar');
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Aplicando...';
            submitBtn.disabled = true;

            // Restaura o botão após 2 segundos (para demonstração)
            // Em produção, isso seria tratado pelo servidor
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
        }
    });

    // Adiciona funcionalidade ao botão de limpar
    const clearBtn = filterForm.querySelector('.chamados-index-filtro-btn-limpar');
    if (clearBtn) {
        clearBtn.addEventListener('click', function (e) {
            // Limpa todos os campos do formulário
            const inputs = filterForm.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.value = '';
                input.style.borderColor = 'var(--chamados-index-light-gray)';
            });

            // Adiciona efeito visual ao botão
            this.innerHTML = '<i class="fas fa-check"></i> Limpo!';
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-times"></i> Limpar';
            }, 1000);
        });
    }

    // Adiciona autocompletar ao campo de busca
    const searchInput = document.getElementById('chamados-index-busca');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            // Esta é uma implementação básica para demonstração
            // Em produção, você implementaria uma busca AJAX real

            // Destaca o campo quando tem conteúdo
            if (this.value.trim() !== '') {
                this.style.borderColor = 'var(--chamados-index-primary)';
                this.style.backgroundColor = 'var(--chamados-index-primary-light)';
            } else {
                this.style.borderColor = 'var(--chamados-index-light-gray)';
                this.style.backgroundColor = 'var(--chamados-index-white)';
            }
        });
    }
}

/**
 * Configura efeitos de hover para os cards
 */
function setupCardHoverEffects() {
    const cards = document.querySelectorAll('.chamados-index-filtros-card, .chamados-index-lista-card');

    cards.forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.1)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'var(--chamados-index-box-shadow)';
        });
    });
}

/**
 * Configura comportamentos responsivos específicos
 */
function setupResponsiveBehavior() {
    // Ajusta a tabela em telas pequenas
    function adjustTableForSmallScreens() {
        const tableContainer = document.querySelector('.chamados-index-tabela-container');
        if (!tableContainer) return;

        if (window.innerWidth < 768) {
            // Adiciona indicador de rolagem horizontal se necessário
            if (tableContainer.scrollWidth > tableContainer.clientWidth) {
                if (!document.querySelector('.chamados-index-scroll-indicator')) {
                    const scrollIndicator = document.createElement('div');
                    scrollIndicator.className = 'chamados-index-scroll-indicator';
                    scrollIndicator.innerHTML = '<i class="fas fa-arrows-alt-h"></i> Deslize para ver mais';
                    scrollIndicator.style.textAlign = 'center';
                    scrollIndicator.style.padding = '0.5rem';
                    scrollIndicator.style.color = 'var(--chamados-index-gray)';
                    scrollIndicator.style.fontSize = '0.8rem';
                    tableContainer.parentNode.insertBefore(scrollIndicator, tableContainer);

                    // Esconde o indicador após alguns segundos
                    setTimeout(() => {
                        scrollIndicator.style.opacity = '0';
                        setTimeout(() => {
                            scrollIndicator.remove();
                        }, 500);
                    }, 3000);
                }
            }
        }
    }

    // Executa o ajuste inicial
    adjustTableForSmallScreens();

    // Adiciona listener para redimensionamento da janela
    window.addEventListener('resize', adjustTableForSmallScreens);

    // Ajusta o layout do formulário de filtro em telas pequenas
    function adjustFilterFormLayout() {
        const filterForm = document.querySelector('.chamados-index-filtros-form');
        if (!filterForm) return;

        const filterGrid = filterForm.querySelector('.chamados-index-filtros-grid');
        if (!filterGrid) return;

        if (window.innerWidth < 768) {
            filterGrid.style.gridTemplateColumns = '1fr';
        } else if (window.innerWidth < 1200) {
            filterGrid.style.gridTemplateColumns = 'repeat(2, 1fr)';
        } else {
            filterGrid.style.gridTemplateColumns = 'repeat(4, 1fr)';
        }
    }

    // Executa o ajuste inicial
    adjustFilterFormLayout();

    // Adiciona listener para redimensionamento da janela
    window.addEventListener('resize', adjustFilterFormLayout);
}

/**
 * Função para exportar os dados da tabela para CSV
 * Pode ser adicionada como funcionalidade extra
 */
function exportToCSV() {
    const table = document.querySelector('.chamados-index-tabela');
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');

        for (let j = 0; j < cols.length; j++) {
            // Ignora a coluna de ações
            if (j === 6) continue;

            // Obtém o texto da célula
            let text = cols[j].innerText;

            // Remove quebras de linha e aspas
            text = text.replace(/(\r\n|\n|\r)/gm, '').replace(/"/g, '""');

            // Adiciona aspas ao redor do texto
            row.push('"' + text + '"');
        }

        csv.push(row.join(','));
    }

    // Cria o arquivo CSV
    const csvString = csv.join('\n');
    const filename = 'chamados_' + new Date().toISOString().slice(0, 10) + '.csv';

    // Cria um link para download
    const link = document.createElement('a');
    link.style.display = 'none';
    link.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(csvString));
    link.setAttribute('download', filename);

    document.body.appendChild(link);
    link.click(); 
    document.body.removeChild(link);
}

/**
 * Função para adicionar um botão de exportação à página
 * Pode ser chamada para adicionar essa funcionalidade
 */
function addExportButton() {
    const headerActions = document.querySelector('.chamados-index-acoes');
    if (!headerActions) return;

    const exportButton = document.createElement('button');
    exportButton.className = 'chamados-index-btn-exportar';
    exportButton.innerHTML = '<i class="fas fa-file-export"></i> Exportar';
    exportButton.style.backgroundColor = 'var(--chamados-index-success)';
    exportButton.style.color = 'var(--chamados-index-white)';
    exportButton.style.border = 'none';
    exportButton.style.borderRadius = 'var(--chamados-index-border-radius)';
    exportButton.style.padding = '0.5rem 1rem';
    exportButton.style.display = 'flex';
    exportButton.style.alignItems = 'center';
    exportButton.style.gap = '0.5rem';
    exportButton.style.fontWeight = '500';
    exportButton.style.fontSize = '0.875rem';
    exportButton.style.cursor = 'pointer';
    exportButton.style.transition = 'var(--chamados-index-transition)';

    exportButton.addEventListener('mouseenter', function () {
        this.style.backgroundColor = '#27ae60';
        this.style.transform = 'translateY(-2px)';
        this.style.boxShadow = '0 4px 12px rgba(46, 204, 113, 0.3)';
    });

    exportButton.addEventListener('mouseleave', function () {
        this.style.backgroundColor = 'var(--chamados-index-success)';
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = 'none';
    });

    exportButton.addEventListener('click', exportToCSV);

    headerActions.appendChild(exportButton);
}