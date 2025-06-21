/**
 * Funções para criação e manipulação de gráficos
 * Este script utiliza a biblioteca Chart.js para renderizar gráficos no sistema
 */

// Verifica se o script já foi carregado para evitar duplicação
if (!window.chartsScriptLoaded) {
    // Marca o script como carregado
    window.chartsScriptLoaded = true;

    // Configurações padrão para todos os gráficos
    window.chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
            }
        }
    };

    /**
     * Cria um gráfico de rosca para status de chamados
     * @param {string} elementId - ID do elemento canvas
     * @param {Array} labels - Rótulos para o gráfico (ex: "Aberto", "Em andamento", etc)
     * @param {Array} data - Dados para o gráfico (ex: [10, 5, 3, 7])
     * @param {Array} backgroundColor - Cores de fundo para cada segmento
     * @returns {Chart} Instância do gráfico criado
     */
    window.createStatusChart = function (elementId, labels, data, backgroundColor) {
        // Verifica se o elemento existe
        const element = document.getElementById(elementId);
        if (!element) {
            console.error(`Elemento com ID "${elementId}" não encontrado`);
            return null;
        }

        const ctx = element.getContext('2d');

        // Cria o gráfico de rosca
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColor,
                    borderWidth: 1
                }]
            },
            options: {
                // Usa as configurações padrão e adiciona configurações específicas
                responsive: window.chartDefaults.responsive,
                maintainAspectRatio: window.chartDefaults.maintainAspectRatio,
                plugins: {
                    legend: window.chartDefaults.plugins.legend,
                    title: {
                        display: true,
                        text: 'Distribuição de Chamados por Status',
                        font: {
                            size: 14
                        }
                    }
                }
            }
        });
    };

    /**
     * Cria um gráfico de barras para chamados por mês
     * @param {string} elementId - ID do elemento canvas
     * @param {Array} labels - Rótulos para o gráfico (ex: meses do ano)
     * @param {Array} data - Dados para o gráfico (ex: [10, 5, 3, 7, ...])
     * @param {string} ano - Ano selecionado para exibição
     * @returns {Chart} Instância do gráfico criado
     */
    window.createMonthlyChart = function (elementId, labels, data, ano) {
        // Verifica se o elemento existe
        const element = document.getElementById(elementId);
        if (!element) {
            console.error(`Elemento com ID "${elementId}" não encontrado`);
            return null;
        }

        const ctx = element.getContext('2d');

        // Cria o gráfico de barras
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Chamados em ' + ano,
                    data: data,
                    backgroundColor: 'rgba(74, 137, 220, 0.5)',
                    borderColor: 'rgba(74, 137, 220, 1)',
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
                            precision: 0 // Garante que os valores sejam inteiros
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            title: function (tooltipItems) {
                                return tooltipItems[0].label + ' de ' + ano;
                            }
                        }
                    }
                }
            }
        });
    };

    /**
     * Cria um gráfico de linha para tendência de chamados
     * @param {string} elementId - ID do elemento canvas
     * @param {Array} labels - Rótulos para o gráfico (ex: datas)
     * @param {Array} data - Dados para o gráfico
     * @param {string} label - Rótulo da série de dados
     * @returns {Chart} Instância do gráfico criado
     */
    window.createTrendChart = function (elementId, labels, data, label) {
        // Verifica se o elemento existe
        const element = document.getElementById(elementId);
        if (!element) {
            console.error(`Elemento com ID "${elementId}" não encontrado`);
            return null;
        }

        const ctx = element.getContext('2d');

        // Cria o gráfico de linha
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: label || 'Tendência de Chamados',
                    data: data,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    tension: 0.4, // Suaviza a linha
                    fill: true
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
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    };

    /**
     * Cria um gráfico de barras horizontais para comparação
     * @param {string} elementId - ID do elemento canvas
     * @param {Array} labels - Rótulos para o gráfico
     * @param {Array} data - Dados para o gráfico
     * @param {string} label - Rótulo da série de dados
     * @returns {Chart} Instância do gráfico criado
     */
    window.createHorizontalBarChart = function (elementId, labels, data, label) {
        // Verifica se o elemento existe
        const element = document.getElementById(elementId);
        if (!element) {
            console.error(`Elemento com ID "${elementId}" não encontrado`);
            return null;
        }

        const ctx = element.getContext('2d');

        // Cria o gráfico de barras horizontais
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: label || 'Dados',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y', // Torna o gráfico horizontal
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    };

    /**
     * Atualiza os dados de um gráfico existente
     * @param {Chart} chart - Instância do gráfico a ser atualizado
     * @param {Array} labels - Novos rótulos
     * @param {Array} data - Novos dados
     */
    window.updateChartData = function (chart, labels, data) {
        if (!chart) {
            console.error('Gráfico não fornecido para atualização');
            return;
        }

        chart.data.labels = labels;
        chart.data.datasets[0].data = data;
        chart.update();
    };

    /**
     * Destrói um gráfico para liberar recursos
     * @param {Chart} chart - Instância do gráfico a ser destruído
     */
    window.destroyChart = function (chart) {
        if (chart) {
            chart.destroy();
        }
    };

    // Detecta o tema atual e aplica cores apropriadas aos gráficos
    window.applyChartTheme = function () {
        const isDarkMode = document.documentElement.classList.contains('dark-mode') ||
            document.body.classList.contains('dark-mode');

        // Define cores baseadas no tema
        if (isDarkMode) {
            Chart.defaults.color = '#e0e0e0';
            Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';
        } else {
            Chart.defaults.color = '#666';
            Chart.defaults.borderColor = 'rgba(0, 0, 0, 0.1)';
        }
    };

    // Aplica o tema inicial
    window.applyChartTheme();

    // Observa mudanças no tema
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.attributeName === 'class') {
                window.applyChartTheme();
                // Atualiza todos os gráficos ativos
                Chart.instances.forEach(chart => chart.update());
            }
        });
    });

    // Observa mudanças na classe do documento e do body
    observer.observe(document.documentElement, { attributes: true });
    observer.observe(document.body, { attributes: true });

    // Log para confirmar que o script foi carregado corretamente
    console.log('Charts.js inicializado com sucesso');
}