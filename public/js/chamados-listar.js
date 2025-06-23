/**
 * chamados-listar.js - Script específico para a página de listagem de chamados
 */

document.addEventListener('DOMContentLoaded', function () {
    // Inicializa tooltips do Bootstrap
    initTooltips();

    // Anima os cards de estatísticas
    animateStatCards();

    // Configura o comportamento do filtro avançado
    setupAdvancedFilter();

    // Configura os seletores de data
    setupDatePickers();

    // Adiciona funcionalidades à tabela
    enhanceTable();

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
 * Anima os cards de estatísticas com efeito de entrada
 */
function animateStatCards() {
    const statCards = document.querySelectorAll('.chamados-listar-card-estatistica');
    statCards.forEach(function (card, index) {
        // Define um atraso crescente para cada card
        setTimeout(function () {
            card.classList.add('animate-in');
            card.style.animationDelay = (index * 0.1) + 's';
        }, 100);
    });
}

/**
 * Configura o comportamento do filtro avançado
 */
function setupAdvancedFilter() {
    const filterHeader = document.querySelector('.chamados-listar-filtros-header');
    const filterToggle = document.querySelector('.chamados-listar-filtros-toggle');
    const filterCollapse = document.getElementById('filtrosCollapse');

    if (filterHeader && filterToggle && filterCollapse) {
        // Verifica se há filtros ativos para expandir automaticamente
        const filterBadge = document.querySelector('.chamados-listar-filtros-badge');
        if (filterBadge) {
            const bsCollapse = new bootstrap.Collapse(filterCollapse, {
                toggle: true
            });

            // Altera o ícone do botão
            const icon = filterToggle.querySelector('i');
            if (icon) {
                icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
            }
        }

        // Adiciona evento de clique no cabeçalho
        filterHeader.addEventListener('click', function () {
            const bsCollapse = bootstrap.Collapse.getInstance(filterCollapse);
            if (!bsCollapse) {
                new bootstrap.Collapse(filterCollapse);
            } else {
                bsCollapse.toggle();
            }

            // Alterna o ícone do botão
            const icon = filterToggle.querySelector('i');
            if (icon) {
                if (icon.classList.contains('fa-chevron-down')) {
                    icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
                } else {
                    icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
                }
            }
        });

        // Adiciona evento para quando o collapse é mostrado/escondido
        filterCollapse.addEventListener('shown.bs.collapse', function () {
            const icon = filterToggle.querySelector('i');
            if (icon) {
                icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
            }
        });

        filterCollapse.addEventListener('hidden.bs.collapse', function () {
            const icon = filterToggle.querySelector('i');
            if (icon) {
                icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
            }
        });
    }

    // Configura o formulário de filtro
    const filterForm = document.querySelector('.chamados-listar-filtros-form');
    if (filterForm) {
        // Adiciona validação visual aos campos
        const filterSelects = filterForm.querySelectorAll('.chamados-listar-filtro-select');
        filterSelects.forEach(select => {
            select.addEventListener('change', function () {
                if (this.value) {
                    this.style.borderColor = 'var(--chamados-listar-primary)';
                } else {
                    this.style.borderColor = 'var(--chamados-listar-light-gray)';
                }
            });
        });

        // Adiciona efeito de loading ao botão de aplicar filtros
        filterForm.addEventListener('submit', function () {
            const submitBtn = this.querySelector('.chamados-listar-filtros-btn-aplicar');
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
    }
}

/**
 * Configura os seletores de data com funcionalidades adicionais
 */
function setupDatePickers() {
    // Obtém os elementos de data
    const dataInicio = document.getElementById('chamados-listar-data-inicio');
    const dataFim = document.getElementById('chamados-listar-data-fim');

    if (dataInicio && dataFim) {
        // Configura a data máxima como hoje
        const hoje = new Date().toISOString().split('T')[0];
        dataInicio.setAttribute('max', hoje);
        dataFim.setAttribute('max', hoje);

        // Atualiza a data mínima do campo de data final quando a data inicial mudar
        dataInicio.addEventListener('change', function () {
            dataFim.setAttribute('min', this.value);

            // Se a data final for anterior à inicial, ajusta
            if (dataFim.value && dataFim.value < this.value) {
                dataFim.value = this.value;
            }
        });

        // Atualiza a data máxima do campo de data inicial quando a data final mudar
        dataFim.addEventListener('change', function () {
            dataInicio.setAttribute('max', this.value);

            // Se a data inicial for posterior à final, ajusta
            if (dataInicio.value && dataInicio.value > this.value) {
                dataInicio.value = this.value;
            }
        });
    }
}

/**
 * Adiciona funcionalidades à tabela de chamados
 */
function enhanceTable() {
    const table = document.querySelector('.chamados-listar-tabela');
    if (!table) return;

    // Adiciona efeito de hover nas linhas
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        row.addEventListener('mouseenter', function () {
            this.style.backgroundColor = 'var(--chamados-listar-primary-light)';
            this.style.transition = 'background-color 0.3s ease';
        });

        row.addEventListener('mouseleave', function () {
            this.style.backgroundColor = '';
        });

        // Adiciona clique na linha para visualizar o chamado
        row.addEventListener('click', function (e) {
            // Ignora se o clique foi em um botão de ação
            if (e.target.closest('.chamados-listar-acao-btn')) {
                return;
            }

            // Obtém o ID do chamado
            const idCell = this.querySelector('.chamados-listar-id');
            if (idCell) {
                const chamadoId = idCell.textContent;
                const viewLink = this.querySelector('.chamados-listar-acao-visualizar');
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
        if (index === 7) return; // Ignora a coluna de ações

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
        });
    });
}

/**
 * Configura comportamentos responsivos específicos
 */
function setupResponsiveBehavior() {
    // Ajusta a tabela em telas pequenas
    function adjustTableForSmallScreens() {
        const tableContainer = document.querySelector('.chamados-listar-tabela-container');
        if (!tableContainer) return;

        if (window.innerWidth < 768) {
            // Adiciona indicador de rolagem horizontal se necessário
            if (tableContainer.scrollWidth > tableContainer.clientWidth) {
                if (!document.querySelector('.chamados-listar-scroll-indicator')) {
                    const scrollIndicator = document.createElement('div');
                    scrollIndicator.className = 'chamados-listar-scroll-indicator';
                    scrollIndicator.innerHTML = '<i class="fas fa-arrows-alt-h"></i> Deslize para ver mais';
                    scrollIndicator.style.textAlign = 'center';
                    scrollIndicator.style.padding = '0.5rem';
                    scrollIndicator.style.color = 'var(--chamados-listar-gray)';
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
}

/**
 * Função para atualizar os contadores de estatísticas com animação
 * Pode ser chamada após carregar novos dados via AJAX
 */
function updateStatCounters(stats) {
    if (!stats) return;

    const elements = {
        total: document.querySelector('.chamados-listar-total .chamados-listar-valor-estatistica'),
        abertos: document.querySelector('.chamados-listar-abertos .chamados-listar-valor-estatistica'),
        andamento: document.querySelector('.chamados-listar-andamento .chamados-listar-valor-estatistica'),
        concluidos: document.querySelector('.chamados-listar-concluidos .chamados-listar-valor-estatistica')
    };

    // Função para animar a contagem
    function animateCounter(element, targetValue) {
        if (!element) return;

        const startValue = parseInt(element.textContent) || 0;
        const duration = 1000; // ms
        const startTime = performance.now();

        function updateCounter(currentTime) {
            const elapsedTime = currentTime - startTime;

            if (elapsedTime < duration) {
                const progress = elapsedTime / duration;
                const currentValue = Math.floor(startValue + progress * (targetValue - startValue));
                element.textContent = currentValue;
                requestAnimationFrame(updateCounter);
            } else {
                element.textContent = targetValue;
            }
        }

        requestAnimationFrame(updateCounter);
    }

    // Atualiza cada contador com animação
    if (stats.total !== undefined && elements.total) {
        animateCounter(elements.total, stats.total);
    }

    if (stats.abertos !== undefined && elements.abertos) {
        animateCounter(elements.abertos, stats.abertos);
    }

    if (stats.em_andamento !== undefined && elements.andamento) {
        animateCounter(elements.andamento, stats.em_andamento);
    }

    if (stats.concluidos !== undefined && elements.concluidos) {
        animateCounter(elements.concluidos, stats.concluidos);
    }
}

/**
 * Função para exportar os dados da tabela para CSV
 * Pode ser adicionada como funcionalidade extra
 */
function exportToCSV() {
    const table = document.querySelector('.chamados-listar-tabela');
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');

        for (let j = 0; j < cols.length; j++) {
            // Ignora a coluna de ações
            if (j === 7) continue;

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