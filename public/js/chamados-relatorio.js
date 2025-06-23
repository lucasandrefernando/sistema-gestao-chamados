/**
 * chamados-relatorio.js - Script específico para a página de relatório de chamados
 */

document.addEventListener('DOMContentLoaded', function () {
    // Inicializa a página
    initPage();

    // Configura os filtros
    setupFilters();

    // Configura os gráficos
    setupCharts();

    // Configura o modal de visualização expandida
    setupChartModal();

    // Configura os botões de download de gráficos
    setupChartDownload();
});

/**
 * Inicializa a página com configurações básicas
 */
function initPage() {
    // Adiciona efeito de hover aos cards
    const cards = document.querySelectorAll('.chamados-relatorio-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-5px)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
        });
    });

    // Configura tooltips do Bootstrap (se disponível)
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * Configura os filtros
 */
function setupFilters() {
    // Destaca visualmente os filtros ativos
    destacarFiltrosAtivos();

    // Submete o formulário quando certos filtros mudarem
    const filtrosAutoSubmit = document.querySelectorAll('#ano, #mes, #setor');
    filtrosAutoSubmit.forEach(function (filtro) {
        filtro.addEventListener('change', function () {
            // Desativa o período predefinido ao mudar manualmente
            const periodoSelect = document.getElementById('periodo');
            if (periodoSelect) {
                periodoSelect.value = '';
            }

            // Submete o formulário
            this.closest('form').submit();
        });
    });

    // Sincroniza datas
    const dataInicio = document.getElementById('data_inicio');
    const dataFim = document.getElementById('data_fim');

    if (dataInicio && dataFim) {
        dataInicio.addEventListener('change', function () {
            if (dataFim.value && this.value > dataFim.value) {
                dataFim.value = this.value;
            }
        });

        dataFim.addEventListener('change', function () {
            if (dataInicio.value && this.value < dataInicio.value) {
                dataInicio.value = this.value;
            }
        });
    }
}

/**
 * Destaca visualmente os filtros ativos
 */
function destacarFiltrosAtivos() {
    const formControls = document.querySelectorAll('.chamados-relatorio-filtros-select, .chamados-relatorio-filtros-input');
    formControls.forEach(function (control) {
        if (control.value && control.value !== '' && control.id !== 'ano') {
            control.classList.add('chamados-relatorio-filtro-ativo');

            // Adiciona um ícone de filtro ativo
            const formGroup = control.closest('.chamados-relatorio-filtros-grupo');
            if (formGroup) {
                const label = formGroup.querySelector('.chamados-relatorio-filtros-label');
                if (label && !label.querySelector('.chamados-relatorio-filtro-ativo-icon')) {
                    const icon = document.createElement('i');
                    icon.className = 'fas fa-filter chamados-relatorio-filtro-ativo-icon';
                    label.appendChild(icon);
                }
            }
        } else if (control.id === 'ano' && control.value !== '' && control.value != new Date().getFullYear()) {
            // Destaca o ano apenas se for diferente do ano atual
            control.classList.add('chamados-relatorio-filtro-ativo');

            const formGroup = control.closest('.chamados-relatorio-filtros-grupo');
            if (formGroup) {
                const label = formGroup.querySelector('.chamados-relatorio-filtros-label');
                if (label && !label.querySelector('.chamados-relatorio-filtro-ativo-icon')) {
                    const icon = document.createElement('i');
                    icon.className = 'fas fa-filter chamados-relatorio-filtro-ativo-icon';
                    label.appendChild(icon);
                }
            }
        }
    });
}

/**
 * Aplica um período predefinido aos campos de data
 */
function aplicarPeriodo() {
    const periodo = document.getElementById('periodo').value;
    const dataInicio = document.getElementById('data_inicio');
    const dataFim = document.getElementById('data_fim');
    const ano = document.getElementById('ano');
    const mes = document.getElementById('mes');

    // Limpa as datas
    if (periodo === '') {
        dataInicio.value = '';
        dataFim.value = '';
        return;
    }

    // Data atual
    const hoje = new Date();
    const formatoData = (data) => {
        const ano = data.getFullYear();
        const mes = String(data.getMonth() + 1).padStart(2, '0');
        const dia = String(data.getDate()).padStart(2, '0');
        return `${ano}-${mes}-${dia}`;
    };

    // Aplica o período selecionado
    switch (periodo) {
        case 'hoje':
            dataInicio.value = formatoData(hoje);
            dataFim.value = formatoData(hoje);
            break;

        case 'ontem':
            const ontem = new Date(hoje);
            ontem.setDate(hoje.getDate() - 1);
            dataInicio.value = formatoData(ontem);
            dataFim.value = formatoData(ontem);
            break;

        case '7dias':
            const seteDiasAtras = new Date(hoje);
            seteDiasAtras.setDate(hoje.getDate() - 7);
            dataInicio.value = formatoData(seteDiasAtras);
            dataFim.value = formatoData(hoje);
            break;

        case '30dias':
            const trintaDiasAtras = new Date(hoje);
            trintaDiasAtras.setDate(hoje.getDate() - 30);
            dataInicio.value = formatoData(trintaDiasAtras);
            dataFim.value = formatoData(hoje);
            break;

        case 'este_mes':
            const primeiroDiaMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
            dataInicio.value = formatoData(primeiroDiaMes);
            dataFim.value = formatoData(hoje);

            // Seleciona o mês atual no dropdown
            if (mes) mes.value = hoje.getMonth() + 1;
            if (ano) ano.value = hoje.getFullYear();
            break;

        case 'mes_anterior':
            const primeiroDiaMesAnterior = new Date(hoje.getFullYear(), hoje.getMonth() - 1, 1);
            const ultimoDiaMesAnterior = new Date(hoje.getFullYear(), hoje.getMonth(), 0);
            dataInicio.value = formatoData(primeiroDiaMesAnterior);
            dataFim.value = formatoData(ultimoDiaMesAnterior);

            // Seleciona o mês anterior no dropdown
            if (mes) mes.value = hoje.getMonth() === 0 ? 12 : hoje.getMonth();
            if (ano) ano.value = hoje.getMonth() === 0 ? hoje.getFullYear() - 1 : hoje.getFullYear();
            break;

        case 'este_ano':
            const primeiroDiaAno = new Date(hoje.getFullYear(), 0, 1);
            dataInicio.value = formatoData(primeiroDiaAno);
            dataFim.value = formatoData(hoje);

            // Seleciona o ano atual no dropdown e limpa o mês
            if (ano) ano.value = hoje.getFullYear();
            if (mes) mes.value = '';
            break;
    }

    // Destaca os campos preenchidos
    if (dataInicio.value) dataInicio.classList.add('chamados-relatorio-filtro-ativo');
    if (dataFim.value) dataFim.classList.add('chamados-relatorio-filtro-ativo');

    // Adiciona ícones aos labels
    adicionarIconesFiltroAtivo();
}

/**
 * Adiciona ícones aos labels dos filtros ativos
 */
function adicionarIconesFiltroAtivo() {
    const formControls = document.querySelectorAll('.chamados-relatorio-filtro-ativo');
    formControls.forEach(function (control) {
        const formGroup = control.closest('.chamados-relatorio-filtros-grupo');
        if (formGroup) {
            const label = formGroup.querySelector('.chamados-relatorio-filtros-label');
            if (label && !label.querySelector('.chamados-relatorio-filtro-ativo-icon')) {
                const icon = document.createElement('i');
                icon.className = 'fas fa-filter chamados-relatorio-filtro-ativo-icon';
                label.appendChild(icon);
            }
        }
    });
}

/**
 * Configura os gráficos
 */
function setupCharts() {
    // Verifica se o Chart.js está carregado
    if (typeof Chart === 'undefined') {
        console.error('Chart.js não está carregado. Verifique se a biblioteca está incluída.');
        return;
    }

    // Obtém os dados dos gráficos
    const dataElement = document.getElementById('relatorio-data');
    if (!dataElement) {
        console.error('Elemento de dados não encontrado.');
        return;
    }

    // Cores para os gráficos
    const chartColors = [
        'rgba(67, 97, 238, 0.7)',   // Azul
        'rgba(46, 204, 113, 0.7)',  // Verde
        'rgba(243, 156, 18, 0.7)',  // Amarelo
        'rgba(231, 76, 60, 0.7)',   // Vermelho
        'rgba(155, 89, 182, 0.7)',  // Roxo
        'rgba(26, 188, 156, 0.7)',  // Teal
        'rgba(230, 126, 34, 0.7)',  // Laranja
        'rgba(232, 67, 147, 0.7)',  // Rosa
        'rgba(108, 92, 231, 0.7)',  // Índigo
        'rgba(149, 165, 166, 0.7)'  // Cinza
    ];

    // Configurações comuns para os gráficos
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#2c3e50';
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(44, 62, 80, 0.8)';
    Chart.defaults.plugins.tooltip.titleFont = { weight: 'bold' };
    Chart.defaults.plugins.tooltip.bodyFont = { size: 12 };
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 6;
    Chart.defaults.plugins.legend.position = 'top';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.padding = 15;

    // Inicializa os gráficos
    try {
        // Gráfico de Chamados por Status
        const statusData = JSON.parse(dataElement.dataset.status || '{}');
        if (statusData.labels && statusData.data) {
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                const statusChart = new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: statusData.labels,
                        datasets: [{
                            data: statusData.data,
                            backgroundColor: chartColors,
                            borderColor: 'rgba(255, 255, 255, 0.8)',
                            borderWidth: 2,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });

                // Armazena a referência do gráfico para uso posterior
                window.chartInstances = window.chartInstances || {};
                window.chartInstances.statusChart = statusChart;
            }
        }

        // Gráfico de Chamados por Mês
        const mensalData = JSON.parse(dataElement.dataset.mensal || '{}');
        if (mensalData.labels && mensalData.data) {
            const mensalCtx = document.getElementById('mensalChart');
            if (mensalCtx) {
                const mensalChart = new Chart(mensalCtx, {
                    type: 'bar',
                    data: {
                        labels: mensalData.labels,
                        datasets: [{
                            label: 'Chamados',
                            data: mensalData.data,
                            backgroundColor: 'rgba(67, 97, 238, 0.7)',
                            borderColor: 'rgba(67, 97, 238, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                            hoverBackgroundColor: 'rgba(67, 97, 238, 0.9)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });

                window.chartInstances.mensalChart = mensalChart;
            }
        }

        // Gráfico de Evolução Mensal por Status
        const evolucaoData = JSON.parse(dataElement.dataset.evolucao || '{}');
        if (evolucaoData.labels && evolucaoData.datasets) {
            const evolucaoCtx = document.getElementById('evolucaoChart');
            if (evolucaoCtx) {
                // Prepara os datasets com cores personalizadas
                const evolucaoDatasets = evolucaoData.datasets.map((dataset, index) => {
                    return {
                        label: dataset.label,
                        data: dataset.data,
                        backgroundColor: chartColors[index % chartColors.length],
                        borderColor: chartColors[index % chartColors.length].replace('0.7', '1'),
                        borderWidth: 2,
                        tension: 0.3,
                        fill: false,
                        pointBackgroundColor: chartColors[index % chartColors.length].replace('0.7', '1'),
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    };
                });

                const evolucaoChart = new Chart(evolucaoCtx, {
                    type: 'line',
                    data: {
                        labels: evolucaoData.labels,
                        datasets: evolucaoDatasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            }
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            tooltip: {
                                position: 'nearest'
                            }
                        }
                    }
                });

                window.chartInstances.evolucaoChart = evolucaoChart;
            }
        }

        // Gráfico de Chamados por Dia da Semana
        const diaSemanaData = JSON.parse(dataElement.dataset.diaSemana || '{}');
        if (diaSemanaData.labels && diaSemanaData.data) {
            const diaSemanaCtx = document.getElementById('diaSemanaChart');
            if (diaSemanaCtx) {
                const diaSemanaChart = new Chart(diaSemanaCtx, {
                    type: 'bar',
                    data: {
                        labels: diaSemanaData.labels,
                        datasets: [{
                            label: 'Chamados',
                            data: diaSemanaData.data,
                            backgroundColor: chartColors,
                            borderColor: chartColors.map(color => color.replace('0.7', '1')),
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });

                window.chartInstances.diaSemanaChart = diaSemanaChart;
            }
        }

        // Gráfico de Taxa de Resolução
        const taxaResolucaoData = JSON.parse(dataElement.dataset.taxaResolucao || '{}');
        if (taxaResolucaoData) {
            const taxaResolucaoCtx = document.getElementById('taxaResolucaoChart');
            if (taxaResolucaoCtx) {
                const taxaResolucaoChart = new Chart(taxaResolucaoCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Concluídos', 'Em Andamento/Abertos'],
                        datasets: [{
                            data: [
                                taxaResolucaoData.concluidos || 0,
                                (taxaResolucaoData.total || 0) - (taxaResolucaoData.concluidos || 0)
                            ],
                            backgroundColor: [
                                'rgba(46, 204, 113, 0.7)',
                                'rgba(231, 76, 60, 0.7)'
                            ],
                            borderColor: [
                                'rgba(46, 204, 113, 1)',
                                'rgba(231, 76, 60, 1)'
                            ],
                            borderWidth: 2,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });

                window.chartInstances.taxaResolucaoChart = taxaResolucaoChart;
            }
        }

        // Gráfico de Tempo Médio de Atendimento
        const tempoData = JSON.parse(dataElement.dataset.tempo || '{}');
        if (tempoData.labels && tempoData.data) {
            const tempoCtx = document.getElementById('tempoChart');
            if (tempoCtx) {
                const tempoChart = new Chart(tempoCtx, {
                    type: 'bar',
                    data: {
                        labels: tempoData.labels,
                        datasets: [{
                            label: 'Horas',
                            data: tempoData.data,
                            backgroundColor: 'rgba(52, 152, 219, 0.7)',
                            borderColor: 'rgba(52, 152, 219, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                            hoverBackgroundColor: 'rgba(52, 152, 219, 0.9)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const value = context.raw || 0;
                                        if (value < 24) {
                                            return `${value.toFixed(1)} horas`;
                                        } else {
                                            const dias = Math.floor(value / 24);
                                            const horas = (value % 24).toFixed(1);
                                            return `${dias} dia(s) e ${horas} hora(s)`;
                                        }
                                    }
                                }
                            }
                        }
                    }
                });

                window.chartInstances.tempoChart = tempoChart;
            }
        }

        // Gráfico de Chamados por Setor
        const setorData = JSON.parse(dataElement.dataset.setor || '{}');
        if (setorData.labels && setorData.data) {
            const setorCtx = document.getElementById('setorChart');
            if (setorCtx) {
                const setorChart = new Chart(setorCtx, {
                    type: 'pie',
                    data: {
                        labels: setorData.labels,
                        datasets: [{
                            data: setorData.data,
                            backgroundColor: chartColors,
                            borderColor: 'rgba(255, 255, 255, 0.8)',
                            borderWidth: 2,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });

                window.chartInstances.setorChart = setorChart;
            }
        }

        // Gráfico de Chamados por Tipo de Serviço
        const tipoData = JSON.parse(dataElement.dataset.tipo || '{}');
        if (tipoData.labels && tipoData.data) {
            const tipoCtx = document.getElementById('tipoChart');
            if (tipoCtx) {
                // Limita para os 10 principais tipos
                const maxItems = 10;
                let labels = tipoData.labels.slice(0, maxItems);
                let data = tipoData.data.slice(0, maxItems);

                // Se houver mais de 10 tipos, agrupa os restantes como "Outros"
                if (tipoData.labels.length > maxItems) {
                    const outrosData = tipoData.data.slice(maxItems).reduce((a, b) => a + b, 0);
                    labels.push('Outros');
                    data.push(outrosData);
                }

                const tipoChart = new Chart(tipoCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Chamados',
                            data: data,
                            backgroundColor: chartColors,
                            borderColor: chartColors.map(color => color.replace('0.7', '1')),
                            borderWidth: 1,
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });

                window.chartInstances.tipoChart = tipoChart;
            }
        }
    } catch (error) {
        console.error('Erro ao inicializar os gráficos:', error);
    }
}

/**
 * Configura o modal de visualização expandida
 */
function setupChartModal() {
    const modal = document.getElementById('chartModal');
    const modalTitle = document.getElementById('chartModalTitle');
    const modalChart = document.getElementById('modalChart');
    const closeBtn = document.getElementById('closeChartModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const downloadBtn = document.getElementById('downloadModalChart');

    if (!modal || !modalTitle || !modalChart) return;

    // Configura os botões de expandir
    const expandButtons = document.querySelectorAll('.chamados-relatorio-btn-expand');
    expandButtons.forEach(button => {
        button.addEventListener('click', function () {
            const chartId = this.dataset.chart;
            const chartInstance = window.chartInstances[chartId];

            if (!chartInstance) return;

            // Obtém o título do gráfico
            const chartCard = this.closest('.chamados-relatorio-grafico-card');
            const chartTitle = chartCard.querySelector('.chamados-relatorio-card-titulo').textContent;

            // Configura o modal
            modalTitle.textContent = chartTitle;
            modal.classList.add('ativo');
            document.body.style.overflow = 'hidden';

            // Cria uma cópia do gráfico no modal
            const modalChartInstance = new Chart(modalChart, {
                type: chartInstance.config.type,
                data: JSON.parse(JSON.stringify(chartInstance.data)),
                options: JSON.parse(JSON.stringify(chartInstance.options))
            });

            // Armazena a referência para uso posterior
            window.modalChartInstance = modalChartInstance;
            window.currentExpandedChart = chartId;
        });
    });

    // Configura o fechamento do modal
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    // Fecha o modal ao clicar fora do conteúdo
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    // Fecha o modal com a tecla ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('ativo')) {
            closeModal();
        }
    });

    // Configura o botão de download
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function () {
            if (window.modalChartInstance) {
                downloadChart(window.modalChartInstance, modalTitle.textContent);
            }
        });
    }

    function closeModal() {
        modal.classList.remove('ativo');
        document.body.style.overflow = '';

        // Destrói a instância do gráfico para evitar vazamento de memória
        if (window.modalChartInstance) {
            window.modalChartInstance.destroy();
            window.modalChartInstance = null;
        }
    }
}

/**
 * Configura os botões de download de gráficos
 */
function setupChartDownload() {
    const downloadButtons = document.querySelectorAll('.chamados-relatorio-btn-download');
    downloadButtons.forEach(button => {
        button.addEventListener('click', function () {
            const chartId = this.dataset.chart;
            const chartInstance = window.chartInstances[chartId];

            if (!chartInstance) return;

            // Obtém o título do gráfico
            const chartCard = this.closest('.chamados-relatorio-grafico-card');
            const chartTitle = chartCard.querySelector('.chamados-relatorio-card-titulo').textContent;

            // Faz o download do gráfico
            downloadChart(chartInstance, chartTitle);
        });
    });
}

/**
 * Faz o download de um gráfico como imagem
 * @param {Chart} chartInstance - Instância do gráfico
 * @param {string} title - Título do gráfico
 */
function downloadChart(chartInstance, title) {
    // Cria um link temporário
    const link = document.createElement('a');
    link.download = `${title.replace(/[^a-z0-9]/gi, '_').toLowerCase()}.png`;

    // Converte o gráfico para uma URL de dados
    link.href = chartInstance.toBase64Image('image/png', 1.0);

    // Simula um clique no link
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}