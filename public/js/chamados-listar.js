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

    // Configura a alternância de visualização (tabela/cards)
    setupViewToggle();

    // Configura os cards de estatísticas clicáveis
    setupClickableStatCards();

    // Configura o modal de exportação
    setupExportModal();

    // Configura a paginação
    setupPagination();

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
 * Configura os cards de estatísticas para serem clicáveis e aplicarem filtros
 */
function setupClickableStatCards() {
    const statCards = document.querySelectorAll('.chamados-listar-card-estatistica');

    statCards.forEach(card => {
        card.addEventListener('click', function () {
            // Obtém o tipo de filtro do atributo data-filter do card
            const filterType = this.getAttribute('data-filter');

            if (filterType === 'todos') {
                // Limpa todos os filtros
                window.location.href = window.location.pathname;
                return;
            }

            if (filterType === 'status') {
                // Obtém o ID do status do atributo data-status do card
                const statusId = this.getAttribute('data-status');
                // Redireciona para a página com o filtro de status aplicado
                window.location.href = `${window.location.pathname}?status=${statusId}`;
            }
        });

        // Adiciona cursor de ponteiro e efeito de hover
        card.style.cursor = 'pointer';

        // Adiciona tooltip
        const label = card.querySelector('.chamados-listar-label-estatistica').textContent;
        card.setAttribute('title', `Filtrar por ${label}`);
        card.setAttribute('data-bs-toggle', 'tooltip');
        card.setAttribute('data-bs-placement', 'top');

        // Verifica se o card está ativo (corresponde ao filtro atual)
        const urlParams = new URLSearchParams(window.location.search);
        const statusFilter = urlParams.get('status');

        // Obtém o tipo de filtro e o status do card
        const filterType = card.getAttribute('data-filter');
        const statusId = card.getAttribute('data-status');

        if (filterType === 'status' && statusFilter === statusId) {
            card.classList.add('active');
        } else if (filterType === 'todos' && !statusFilter) {
            card.classList.add('active');
        }
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
        // Verifica se há filtros ativos
        const urlParams = new URLSearchParams(window.location.search);
        const hasActiveFilters = urlParams.toString() !== '' &&
            (urlParams.has('status') ||
                urlParams.has('setor') ||
                urlParams.has('busca') ||
                urlParams.has('data_inicio') ||
                urlParams.has('data_fim') ||
                urlParams.has('solicitante') ||
                urlParams.has('tipo_servico'));

        // Expande o filtro apenas se houver filtros ativos
        if (hasActiveFilters) {
            const bsCollapse = new bootstrap.Collapse(filterCollapse, {
                toggle: true
            });

            // Altera o ícone do botão
            const icon = filterToggle.querySelector('i');
            if (icon) {
                icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
            }
        } else {
            // Garante que o filtro esteja recolhido por padrão
            const bsCollapse = new bootstrap.Collapse(filterCollapse, {
                toggle: false
            });
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

            // Aplica estilo inicial se já tiver valor
            if (select.value) {
                select.style.borderColor = 'var(--chamados-listar-primary)';
            }
        });

        // Adiciona efeito de loading ao botão de aplicar filtros
        filterForm.addEventListener('submit', function () {
            const submitBtn = this.querySelector('.chamados-listar-filtros-btn-aplicar');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Aplicando...';
                submitBtn.disabled = true;
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

            // Aplica estilo visual
            if (this.value) {
                this.style.borderColor = 'var(--chamados-listar-primary)';
            } else {
                this.style.borderColor = 'var(--chamados-listar-light-gray)';
            }
        });

        // Atualiza a data máxima do campo de data inicial quando a data final mudar
        dataFim.addEventListener('change', function () {
            dataInicio.setAttribute('max', this.value);

            // Se a data inicial for posterior à final, ajusta
            if (dataInicio.value && dataInicio.value > this.value) {
                dataInicio.value = this.value;
            }

            // Aplica estilo visual
            if (this.value) {
                this.style.borderColor = 'var(--chamados-listar-primary)';
            } else {
                this.style.borderColor = 'var(--chamados-listar-light-gray)';
            }
        });

        // Aplica estilo inicial se já tiver valor
        if (dataInicio.value) {
            dataInicio.style.borderColor = 'var(--chamados-listar-primary)';
        }

        if (dataFim.value) {
            dataFim.style.borderColor = 'var(--chamados-listar-primary)';
        }
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

    // Adiciona ordenação nas colunas
    const headers = table.querySelectorAll('thead th');
    headers.forEach((header, index) => {
        if (index === 7) return; // Ignora a coluna de ações

        header.style.cursor = 'pointer';
        header.title = 'Clique para ordenar';

        // Adiciona evento de clique para ordenação
        header.addEventListener('click', function () {
            // Remove classes de ordenação de todos os cabeçalhos
            headers.forEach(h => {
                h.classList.remove('asc', 'desc');
            });

            // Determina a direção da ordenação
            let direction = 'asc';
            if (this.classList.contains('asc')) {
                direction = 'desc';
            }

            // Adiciona classe de ordenação ao cabeçalho clicado
            this.classList.add(direction);

            // Ordena as linhas da tabela
            sortTable(table, index, direction);
        });
    });
}

/**
 * Ordena a tabela com base na coluna e direção especificadas
 * @param {HTMLElement} table - Elemento da tabela
 * @param {number} columnIndex - Índice da coluna a ser ordenada
 * @param {string} direction - Direção da ordenação ('asc' ou 'desc')
 */
function sortTable(table, columnIndex, direction) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    // Função para obter o valor da célula
    const getCellValue = (row, index) => {
        const cell = row.querySelector(`td:nth-child(${index + 1})`);

        // Verifica se é uma coluna de data
        if (index === 6) { // Coluna de data
            // Converte a data para timestamp para ordenação
            const dateText = cell.textContent.trim();
            const dateParts = dateText.split('/');
            if (dateParts.length === 3) {
                const day = parseInt(dateParts[0]);
                const month = parseInt(dateParts[1]) - 1;
                const yearTimeParts = dateParts[2].split(' ');
                const year = parseInt(yearTimeParts[0]);

                // Se tiver hora
                if (yearTimeParts.length > 1) {
                    const timeParts = yearTimeParts[1].split(':');
                    const hour = parseInt(timeParts[0]);
                    const minute = parseInt(timeParts[1]);
                    return new Date(year, month, day, hour, minute).getTime();
                }

                return new Date(year, month, day).getTime();
            }
        }

        // Para outras colunas, retorna o texto
        return cell.textContent.trim().toLowerCase();
    };

    // Ordena as linhas
    rows.sort((a, b) => {
        const aValue = getCellValue(a, columnIndex);
        const bValue = getCellValue(b, columnIndex);

        if (aValue < bValue) {
            return direction === 'asc' ? -1 : 1;
        }
        if (aValue > bValue) {
            return direction === 'asc' ? 1 : -1;
        }
        return 0;
    });

    // Adiciona efeito de animação
    rows.forEach(row => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(10px)';
    });

    // Reordena as linhas no DOM
    rows.forEach(row => tbody.appendChild(row));

    // Anima as linhas reordenadas
    setTimeout(() => {
        rows.forEach((row, index) => {
            setTimeout(() => {
                row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, index * 30);
        });
    }, 50);
}

/**
 * Configura a alternância de visualização (tabela/cards)
 */
function setupViewToggle() {
    const btnTabela = document.getElementById('visualizacaoTabela');
    const btnCards = document.getElementById('visualizacaoCards');
    const containerTabela = document.getElementById('visualizacaoTabelaContainer');
    const containerCards = document.getElementById('visualizacaoCardsContainer');

    if (btnTabela && btnCards && containerTabela && containerCards) {
        btnTabela.addEventListener('click', function () {
            containerTabela.style.display = 'block';
            containerCards.style.display = 'none';

            btnTabela.classList.add('chamados-listar-tabela-btn-ativo');
            btnCards.classList.remove('chamados-listar-tabela-btn-ativo');

            // Salva a preferência do usuário
            localStorage.setItem('chamados-visualizacao', 'tabela');
        });

        btnCards.addEventListener('click', function () {
            containerTabela.style.display = 'none';
            containerCards.style.display = 'block';

            btnTabela.classList.remove('chamados-listar-tabela-btn-ativo');
            btnCards.classList.add('chamados-listar-tabela-btn-ativo');

            // Salva a preferência do usuário
            localStorage.setItem('chamados-visualizacao', 'cards');
        });

        // Verifica se há uma preferência salva
        const visualizacaoSalva = localStorage.getItem('chamados-visualizacao');
        if (visualizacaoSalva === 'cards') {
            btnCards.click();
        }
    }
}

/**
 * Configura o modal de exportação
 */
function setupExportModal() {
    const exportarCsv = document.getElementById('exportarCsv');
    const exportarModal = document.getElementById('exportarModal');
    const fecharModal = document.getElementById('fecharModal');
    const cancelarExportacao = document.getElementById('cancelarExportacao');
    const confirmarExportacao = document.getElementById('confirmarExportacao');
    const exportarCsvBtn = document.getElementById('exportarCsvBtn');
    const exportarExcelBtn = document.getElementById('exportarExcelBtn');
    const exportarPdfBtn = document.getElementById('exportarPdfBtn');

    if (exportarCsv && exportarModal) {
        // Abre o modal
        exportarCsv.addEventListener('click', function () {
            exportarModal.classList.add('ativo');
            document.body.style.overflow = 'hidden';
        });

        // Fecha o modal
        const fecharModalFn = function () {
            exportarModal.classList.remove('ativo');
            document.body.style.overflow = '';
        };

        if (fecharModal) {
            fecharModal.addEventListener('click', fecharModalFn);
        }

        if (cancelarExportacao) {
            cancelarExportacao.addEventListener('click', fecharModalFn);
        }

        // Clique fora do modal para fechar
        exportarModal.addEventListener('click', function (e) {
            if (e.target === exportarModal) {
                fecharModalFn();
            }
        });

        // Tecla ESC para fechar
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && exportarModal.classList.contains('ativo')) {
                fecharModalFn();
            }
        });

        // Seleciona o formato de exportação
        const opcoes = [exportarCsvBtn, exportarExcelBtn, exportarPdfBtn];
        let formatoSelecionado = 'csv';

        opcoes.forEach(opcao => {
            if (opcao) {
                opcao.addEventListener('click', function () {
                    // Remove a classe ativa de todas as opções
                    opcoes.forEach(op => op.classList.remove('ativo'));

                    // Adiciona a classe ativa à opção clicada
                    this.classList.add('ativo');

                    // Armazena o formato selecionado
                    formatoSelecionado = this.id.replace('exportar', '').replace('Btn', '').toLowerCase();
                });
            }
        });

        // Confirma a exportação
        if (confirmarExportacao) {
            confirmarExportacao.addEventListener('click', function () {
                // Adiciona efeito de loading
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exportando...';
                this.disabled = true;

                // Simula a exportação (em produção, isso seria uma chamada AJAX)
                setTimeout(() => {
                    // Exporta os dados
                    exportData(formatoSelecionado);

                    // Fecha o modal
                    fecharModalFn();

                    // Restaura o botão
                    this.innerHTML = '<i class="fas fa-download"></i> Exportar';
                    this.disabled = false;
                }, 1500);
            });
        }
    }
}

/**
 * Exporta os dados da tabela para o formato especificado
 * @param {string} formato - Formato de exportação ('csv', 'excel', 'pdf')
 */
function exportData(formato) {
    const table = document.querySelector('.chamados-listar-tabela');
    if (!table) return;

    // Obtém os dados da tabela
    const headers = [];
    const data = [];

    // Obtém os cabeçalhos
    table.querySelectorAll('thead th').forEach(th => {
        if (th.cellIndex !== 7) { // Ignora a coluna de ações
            headers.push(th.textContent.trim());
        }
    });

    // Obtém os dados das linhas
    table.querySelectorAll('tbody tr').forEach(tr => {
        const rowData = [];
        tr.querySelectorAll('td').forEach(td => {
            if (td.cellIndex !== 7) { // Ignora a coluna de ações
                rowData.push(td.textContent.trim());
            }
        });
        data.push(rowData);
    });

    // Exporta os dados no formato especificado
    switch (formato) {
        case 'csv':
            exportToCSV(headers, data);
            break;
        case 'excel':
            // Em produção, isso seria uma chamada para uma biblioteca de exportação para Excel
            alert('Exportação para Excel não implementada nesta demonstração.');
            break;
        case 'pdf':
            // Em produção, isso seria uma chamada para uma biblioteca de exportação para PDF
            alert('Exportação para PDF não implementada nesta demonstração.');
            break;
    }
}

/**
 * Exporta os dados para CSV
 * @param {Array} headers - Cabeçalhos da tabela
 * @param {Array} data - Dados da tabela
 */
function exportToCSV(headers, data) {
    // Cria o conteúdo CSV
    let csvContent = headers.join(',') + '\n';

    data.forEach(row => {
        // Processa cada célula para lidar com vírgulas e aspas
        const processedRow = row.map(cell => {
            // Se a célula contém vírgulas, aspas ou quebras de linha, coloca entre aspas
            if (cell.includes(',') || cell.includes('"') || cell.includes('\n')) {
                // Substitui aspas por aspas duplas
                return '"' + cell.replace(/"/g, '""') + '"';
            }
            return cell;
        });

        csvContent += processedRow.join(',') + '\n';
    });

    // Cria um blob com o conteúdo CSV
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });

    // Cria um link para download
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', 'chamados_' + new Date().toISOString().slice(0, 10) + '.csv');
    link.style.visibility = 'hidden';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
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
                        scrollIndicator.style.transition = 'opacity 0.5s ease';
                        setTimeout(() => {
                            if (scrollIndicator.parentNode) {
                                scrollIndicator.parentNode.removeChild(scrollIndicator);
                            }
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
 * Função para mudar a quantidade de registros por página
 */
function mudarRegistrosPorPagina(valor) {
    // Obtém os parâmetros atuais da URL
    const urlParams = new URLSearchParams(window.location.search);

    // Atualiza ou adiciona o parâmetro itens_por_pagina
    urlParams.set('itens_por_pagina', valor);

    // Volta para a primeira página ao mudar a quantidade de registros
    urlParams.set('pagina', '1');

    // Redireciona para a nova URL
    window.location.href = `${window.location.pathname}?${urlParams.toString()}`;
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

    // Atualiza as barras de progresso
    updateProgressBars(stats);
}

/**
 * Atualiza as barras de progresso das estatísticas
 * @param {Object} stats - Estatísticas atualizadas
 */
function updateProgressBars(stats) {
    if (!stats || !stats.total || stats.total === 0) return;

    const total = stats.total;

    // Barra de progresso de abertos
    if (stats.abertos !== undefined) {
        const percentualAbertos = (stats.abertos / total) * 100;
        const barraAbertos = document.querySelector('.chamados-listar-abertos .chamados-listar-estatistica-barra');
        if (barraAbertos) {
            barraAbertos.style.width = '0%';
            setTimeout(() => {
                barraAbertos.style.width = percentualAbertos + '%';
            }, 100);
        }
    }

    // Barra de progresso de em andamento
    if (stats.em_andamento !== undefined) {
        const percentualAndamento = (stats.em_andamento / total) * 100;
        const barraAndamento = document.querySelector('.chamados-listar-andamento .chamados-listar-estatistica-barra');
        if (barraAndamento) {
            barraAndamento.style.width = '0%';
            setTimeout(() => {
                barraAndamento.style.width = percentualAndamento + '%';
            }, 200);
        }
    }

    // Barra de progresso de concluídos
    if (stats.concluidos !== undefined) {
        const percentualConcluidos = (stats.concluidos / total) * 100;
        const barraConcluidos = document.querySelector('.chamados-listar-concluidos .chamados-listar-estatistica-barra');
        if (barraConcluidos) {
            barraConcluidos.style.width = '0%';
            setTimeout(() => {
                barraConcluidos.style.width = percentualConcluidos + '%';
            }, 300);
        }
    }
}

/**
 * Configura a paginação da tabela de chamados
 */
function setupPagination() {
    const paginationContainer = document.querySelector('.pagination-container');
    if (!paginationContainer) return;

    const paginationLinks = paginationContainer.querySelectorAll('.pagination-link');

    // Adiciona efeito de hover aos links de paginação
    paginationLinks.forEach(link => {
        if (!link.classList.contains('disabled') && !link.classList.contains('active')) {
            link.addEventListener('mouseenter', function () {
                this.style.backgroundColor = 'var(--chamados-listar-primary-light)';
                this.style.color = 'var(--chamados-listar-primary)';
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 3px 8px rgba(0, 0, 0, 0.1)';
            });

            link.addEventListener('mouseleave', function () {
                this.style.backgroundColor = '';
                this.style.color = '';
                this.style.transform = '';
                this.style.boxShadow = '';
            });
        }
    });

    // Adiciona comportamento responsivo
    function adjustPaginationForSmallScreens() {
        if (window.innerWidth < 576) {
            // Em telas pequenas, mostra menos links de página
            const pageItems = paginationContainer.querySelectorAll('.pagination li');
            const pagina_atual = parseInt(paginationContainer.querySelector('.pagination-link.active')?.textContent || '1');
            const total_paginas = pageItems.length - 4; // Subtrai os botões de navegação

            pageItems.forEach((item, index) => {
                // Mantém visíveis: primeira, última, atual, anterior e próxima
                const link = item.querySelector('.pagination-link');
                if (link) {
                    const pageNumber = parseInt(link.textContent);

                    // Esconde páginas que não são importantes em telas pequenas
                    if (!isNaN(pageNumber) &&
                        pageNumber !== 1 &&
                        pageNumber !== total_paginas &&
                        pageNumber !== pagina_atual &&
                        pageNumber !== pagina_atual - 1 &&
                        pageNumber !== pagina_atual + 1) {

                        item.style.display = 'none';
                    } else {
                        item.style.display = '';
                    }
                }
            });
        } else {
            // Em telas maiores, mostra todos os links
            const pageItems = paginationContainer.querySelectorAll('.pagination li');
            pageItems.forEach(item => {
                item.style.display = '';
            });
        }
    }

    // Executa o ajuste inicial
    adjustPaginationForSmallScreens();

    // Adiciona listener para redimensionamento da janela
    window.addEventListener('resize', function () {
        adjustPaginationForSmallScreens();
    });

    // Configura o link "Anterior"
    if (previousLink && !previousLink.classList.contains('chamados-listar-paginacao-link-desabilitado')) {
        previousLink.addEventListener('click', function (e) {
            e.preventDefault();
            navigateToPage(currentPage - 1);
        });
    }

    // Configura o link "Próximo"
    if (nextLink && !nextLink.classList.contains('chamados-listar-paginacao-link-desabilitado')) {
        nextLink.addEventListener('click', function (e) {
            e.preventDefault();
            navigateToPage(currentPage + 1);
        });
    }

    /**
    * Navega para a página especificada mantendo os filtros atuais
    * @param {number} page - Número da página
    */
    function navigateToPage(page) {
        // Obtém os parâmetros atuais da URL
        const params = new URLSearchParams(window.location.search);

        // Atualiza ou adiciona o parâmetro de página
        params.set('pagina', page);

        // Redireciona para a nova URL
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }

    // Adiciona efeito de hover aos links de paginação
    paginationLinks.forEach(link => {
        if (!link.classList.contains('chamados-listar-paginacao-link-desabilitado')) {
            link.addEventListener('mouseenter', function () {
                this.style.backgroundColor = 'var(--chamados-listar-primary)';
                this.style.color = 'var(--chamados-listar-white)';
                this.style.transform = 'translateY(-2px)';
            });

            link.addEventListener('mouseleave', function () {
                if (!this.classList.contains('chamados-listar-paginacao-link-ativo')) {
                    this.style.backgroundColor = '';
                    this.style.color = '';
                }
                this.style.transform = 'translateY(0)';
            });
        }
    });

    // Adiciona contador de resultados
    const totalItems = paginationContainer.dataset.totalItems;
    const itemsPerPage = paginationContainer.dataset.itemsPerPage;

    if (totalItems && itemsPerPage) {
        const startItem = (currentPage - 1) * parseInt(itemsPerPage) + 1;
        const endItem = Math.min(startItem + parseInt(itemsPerPage) - 1, parseInt(totalItems));

        const paginationInfo = document.createElement('div');
        paginationInfo.className = 'chamados-listar-paginacao-info';
        paginationInfo.textContent = `Mostrando ${startItem}-${endItem} de ${totalItems} chamados`;

        // Insere o contador antes da paginação
        paginationContainer.parentNode.insertBefore(paginationInfo, paginationContainer);
    }

    // Adiciona comportamento responsivo
    function adjustPaginationForSmallScreens() {
        if (window.innerWidth < 576) {
            // Em telas pequenas, mostra menos links de página
            const pageItems = paginationContainer.querySelectorAll('.chamados-listar-paginacao-item');

            pageItems.forEach(item => {
                const link = item.querySelector('.chamados-listar-paginacao-link');
                if (link) {
                    const pageNumber = parseInt(link.textContent);

                    // Esconde páginas que não são a atual, a primeira, a última ou adjacentes à atual
                    if (!isNaN(pageNumber) &&
                        pageNumber !== 1 &&
                        pageNumber !== parseInt(paginationContainer.dataset.totalPages) &&
                        pageNumber !== currentPage &&
                        pageNumber !== currentPage - 1 &&
                        pageNumber !== currentPage + 1) {

                        item.style.display = 'none';
                    }
                }
            });
        } else {
            // Em telas maiores, restaura a visibilidade
            const pageItems = paginationContainer.querySelectorAll('.chamados-listar-paginacao-item');
            pageItems.forEach(item => {
                item.style.display = '';
            });
        }
    }

    // Executa o ajuste inicial
    adjustPaginationForSmallScreens();

    // Adiciona listener para redimensionamento da janela
    window.addEventListener('resize', adjustPaginationForSmallScreens);
}