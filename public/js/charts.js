/**
 * Funções para criação e manipulação de gráficos
 */

// Configurações padrão para gráficos
const chartDefaults = {
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
 * @param {Array} labels - Rótulos para o gráfico
 * @param {Array} data - Dados para o gráfico
 * @param {Array} backgroundColor - Cores de fundo
 */
function createStatusChart(elementId, labels, data, backgroundColor) {
    const ctx = document.getElementById(elementId).getContext('2d');
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
            ...chartDefaults,
            plugins: {
                ...chartDefaults.plugins,
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
}

/**
 * Cria um gráfico de barras para chamados por mês
 * @param {string} elementId - ID do elemento canvas
 * @param {Array} labels - Rótulos para o gráfico
 * @param {Array} data - Dados para o gráfico
 * @param {string} ano - Ano selecionado
 */
function createMonthlyChart(elementId, labels, data, ano) {
    const ctx = document.getElementById(elementId).getContext('2d');
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
                        precision: 0
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
}