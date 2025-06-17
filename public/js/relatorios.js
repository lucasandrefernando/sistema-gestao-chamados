/**
 * Inicializa os relatórios
 */
function initRelatorios() {
    // Obtém os dados dos gráficos
    const relatorioData = document.getElementById('relatorio-data');

    if (!relatorioData) {
        console.error('Elemento relatorio-data não encontrado');
        return;
    }

    try {
        // Carrega os dados dos gráficos
        const statusData = JSON.parse(relatorioData.getAttribute('data-status') || '{}');
        const mensalData = JSON.parse(relatorioData.getAttribute('data-mensal') || '{}');
        const tempoData = JSON.parse(relatorioData.getAttribute('data-tempo') || '{}');
        const setorData = JSON.parse(relatorioData.getAttribute('data-setor') || '{}');
        const tipoData = JSON.parse(relatorioData.getAttribute('data-tipo') || '{}');
        const taxaResolucaoData = JSON.parse(relatorioData.getAttribute('data-taxa-resolucao') || '{}');
        const diaSemanaData = JSON.parse(relatorioData.getAttribute('data-dia-semana') || '{}');
        const evolucaoData = JSON.parse(relatorioData.getAttribute('data-evolucao') || '{}');

        // Log para depuração
        console.log('Dados carregados:', {
            status: statusData,
            mensal: mensalData,
            tempo: tempoData,
            setor: setorData,
            tipo: tipoData,
            taxaResolucao: taxaResolucaoData,
            diaSemana: diaSemanaData,
            evolucao: evolucaoData
        });

        // Cria os gráficos
        createStatusChart(statusData);
        createMensalChart(mensalData);
        createTempoChart(tempoData);
        createSetorChart(setorData);
        createTipoChart(tipoData);
        createTaxaResolucaoChart(taxaResolucaoData);
        createDiaSemanaChart(diaSemanaData);
        createEvolucaoChart(evolucaoData);

        // Inicializa os filtros
        initFiltros();
    } catch (e) {
        console.error('Erro ao inicializar relatórios:', e);
    }
}

/**
 * Função auxiliar para fazer parse seguro de JSON
 */
function safeParseJSON(jsonString) {
    try {
        if (!jsonString) return {};
        return JSON.parse(jsonString);
    } catch (e) {
        console.error('Erro ao fazer parse de JSON:', e);
        return {};
    }
}

/**
 * Cria o gráfico de taxa de resolução
 */
function createTaxaResolucaoChart(data) {
    if (!document.getElementById('taxaResolucaoChart')) {
        console.error('Elemento taxaResolucaoChart não encontrado');
        return;
    }

    // Log para depuração
    console.log('Dados do gráfico de taxa de resolução:', data);

    // Verifica se há dados válidos
    if (!data.labels || !data.data || data.labels.length === 0 || data.data.length === 0) {
        const container = document.getElementById('taxaResolucaoChart').parentNode;
        container.innerHTML = '<div class="text-center py-5 text-muted">Nenhum dado disponível para o período selecionado.</div>';
        return;
    }

    const ctx = document.getElementById('taxaResolucaoChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.data,
                backgroundColor: data.backgroundColor,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            return `${label}: ${value.toFixed(1)}%`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Cria o gráfico de chamados por status
 */
function createStatusChart(data) {
    if (!document.getElementById('statusChart')) {
        console.error('Elemento statusChart não encontrado');
        return;
    }

    // Verifica se há dados válidos
    if (!data.labels || !data.data || data.labels.length === 0 || data.data.length === 0) {
        console.log('Sem dados para o gráfico de status, usando dados de exemplo');
        // ... (código existente para dados de exemplo)
    }

    const ctx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.data,
                backgroundColor: data.backgroundColor,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    onClick: function(e, legendItem, legend) {
                        // Obtém o índice do item clicado
                        const index = legendItem.index;
                        
                        // Obtém o status_id do item clicado (dos dados raw)
                        if (data.raw && data.raw[index] && data.raw[index].status_id) {
                            const statusId = data.raw[index].status_id;
                            
                            // Redireciona para a lista de chamados filtrada por status
                            window.location.href = gerarUrlFiltro({
                                status: statusId
                            });
                        } else {
                            // Comportamento padrão se não houver dados raw
                            Chart.defaults.plugins.legend.onClick(e, legendItem, legend);
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            onClick: function(event, elements) {
                if (elements && elements.length > 0) {
                    const index = elements[0].index;
                    
                    // Obtém o status_id do item clicado (dos dados raw)
                    if (data.raw && data.raw[index] && data.raw[index].status_id) {
                        const statusId = data.raw[index].status_id;
                        
                        // Redireciona para a lista de chamados filtrada por status
                        window.location.href = gerarUrlFiltro({
                            status: statusId
                        });
                    }
                }
            }
        }
    });
    
    // Adiciona um botão "Ver todos" abaixo do gráfico
    const container = document.getElementById('statusChart').parentNode;
    const verTodosBtn = document.createElement('div');
    verTodosBtn.className = 'text-center mt-3';
    verTodosBtn.innerHTML = '<a href="' + gerarUrlFiltro({}) + '" class="btn btn-sm btn-outline-primary">Ver todos os chamados</a>';
    container.appendChild(verTodosBtn);
}

/**
 * Cria o gráfico de chamados por mês
 */
function createMensalChart(data) {
    if (!document.getElementById('mensalChart')) {
        console.error('Elemento mensalChart não encontrado');
        return;
    }

    // Dados de exemplo para quando não houver dados reais
    const dadosExemplo = {
        labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        data: [4, 6, 8, 5, 7, 9, 10, 8, 6, 7, 5, 4]
    };

    // Use dados reais se disponíveis, caso contrário use dados de exemplo
    const chartData = (data.labels && data.labels.length > 0) ? data : dadosExemplo;

    const ctx = document.getElementById('mensalChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Chamados',
                data: chartData.data,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
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
                    }
                }
            }
        }
    });
}

/**
 * Cria o gráfico de tempo médio de atendimento
 */
function createTempoChart(data) {
    if (!document.getElementById('tempoChart')) {
        console.error('Elemento tempoChart não encontrado');
        return;
    }

    // Log para depuração
    console.log('Dados do gráfico de tempo médio:', data);

    // Verifica se há dados válidos
    if (!data.labels || !data.data || data.labels.length === 0 || data.data.length === 0) {
        const container = document.getElementById('tempoChart').parentNode;
        container.innerHTML = '<div class="text-center py-5 text-muted">Nenhum dado disponível para o período selecionado.</div>';
        return;
    }

    const ctx = document.getElementById('tempoChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Tempo Médio (horas)',
                data: data.data,
                backgroundColor: data.backgroundColor,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Horas'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const value = parseFloat(context.raw) || 0;
                            if (value < 24) {
                                return `Tempo médio: ${value.toFixed(1)} horas`;
                            } else {
                                const dias = Math.floor(value / 24);
                                const horas = (value % 24).toFixed(1);
                                return `Tempo médio: ${dias} dias e ${horas} horas`;
                            }
                        }
                    }
                }
            }
        }
    });
}

/**
 * Cria o gráfico de chamados por setor
 */
function createSetorChart(data) {
    if (!document.getElementById('setorChart')) {
        console.error('Elemento setorChart não encontrado');
        return;
    }

    // Dados de exemplo para quando não houver dados reais
    const dadosExemplo = {
        labels: ['TI', 'RH', 'Financeiro', 'Comercial', 'Operacional'],
        data: [12, 8, 5, 7, 10],
        backgroundColor: [
            'rgba(54, 162, 235, 0.7)',   // Azul
            'rgba(255, 99, 132, 0.7)',   // Vermelho
            'rgba(255, 206, 86, 0.7)',   // Amarelo
            'rgba(75, 192, 192, 0.7)',   // Verde
            'rgba(153, 102, 255, 0.7)'   // Roxo
        ]
    };

    // Use dados reais se disponíveis, caso contrário use dados de exemplo
    const chartData = (data.labels && data.labels.length > 0) ? data : dadosExemplo;

    const ctx = document.getElementById('setorChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: chartData.labels,
            datasets: [{
                data: chartData.data,
                backgroundColor: chartData.backgroundColor,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Cria o gráfico de chamados por tipo de serviço
 */
function createTipoChart(data) {
    if (!document.getElementById('tipoChart')) {
        console.error('Elemento tipoChart não encontrado');
        return;
    }

    // Log para depuração
    console.log('Dados do gráfico de tipos de serviço:', data);

    // Verifica se há dados válidos
    if (!data.labels || !data.data || data.labels.length === 0 || data.data.length === 0) {
        const container = document.getElementById('tipoChart').parentNode;
        container.innerHTML = '<div class="text-center py-5 text-muted">Nenhum dado disponível para o período selecionado.</div>';
        return;
    }

    const ctx = document.getElementById('tipoChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Chamados',
                data: data.data,
                backgroundColor: data.backgroundColor,
                borderWidth: 1
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
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `Chamados: ${value} (${percentage}% do total)`;
                        }
                    }
                }
            }
        }
    });
}



/**
 * Cria o gráfico de chamados por dia da semana
 */
function createDiaSemanaChart(data) {
    if (!document.getElementById('diaSemanaChart')) return;

    // Verifica se há dados válidos
    if (!data.labels || !data.data || data.labels.length === 0 || data.data.length === 0) {
        const container = document.getElementById('diaSemanaChart').parentNode;
        container.innerHTML = '<div class="text-center py-5 text-muted">Nenhum dado disponível para o período selecionado.</div>';
        return;
    }

    // Log para depuração
    console.log('Dados do gráfico de dias da semana:', data);

    const ctx = document.getElementById('diaSemanaChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Chamados',
                data: data.data || [],
                backgroundColor: data.backgroundColor || [],
                borderWidth: 1
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
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `Chamados: ${value} (${percentage}% do total)`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Cria o gráfico de evolução mensal por status
 */
function createEvolucaoChart(data) {
    if (!document.getElementById('evolucaoChart')) {
        console.error('Elemento evolucaoChart não encontrado');
        return;
    }

    // Dados de exemplo para quando não houver dados reais
    const dadosExemplo = {
        labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        datasets: [
            {
                label: 'Aberto',
                data: [5, 7, 6, 8, 9, 7, 8, 10, 8, 7, 6, 5],
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2,
                tension: 0.4
            },
            {
                label: 'Em Andamento',
                data: [3, 4, 5, 6, 7, 8, 7, 6, 5, 4, 3, 2],
                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 2,
                tension: 0.4
            },
            {
                label: 'Concluído',
                data: [8, 7, 9, 10, 12, 11, 13, 14, 12, 10, 9, 8],
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                tension: 0.4
            }
        ],
        ano: new Date().getFullYear()
    };

    // Use dados reais se disponíveis, caso contrário use dados de exemplo
    const chartData = (data.labels && data.labels.length > 0 && data.datasets && data.datasets.length > 0) ? data : dadosExemplo;

    const ctx = document.getElementById('evolucaoChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: chartData.datasets
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
                    title: {
                        display: true,
                        text: 'Quantidade de Chamados'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Mês'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Evolução Mensal de Chamados por Status - ' + chartData.ano,
                    font: {
                        size: 16
                    }
                },
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

/**
 * Inicializa os filtros
 */
function initFiltros() {
    // Submete o formulário quando o filtro mudar
    const filtros = document.querySelectorAll('#ano, #mes, #setor');
    filtros.forEach(function (filtro) {
        filtro.addEventListener('change', function () {
            this.closest('form').submit();
        });
    });
}

/**
 * Gera URL para filtrar chamados com base nos parâmetros
 */
function gerarUrlFiltro(params) {
    const baseUrl = window.location.origin + '/chamados/listar';
    const queryParams = new URLSearchParams();

    // Adiciona os parâmetros à URL
    for (const key in params) {
        if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
            queryParams.append(key, params[key]);
        }
    }

    return baseUrl + '?' + queryParams.toString();
}