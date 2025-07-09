/**
 * Dashboard - Sistema de Gestão de Chamados
 * Script para inicialização e gerenciamento do dashboard
 * 
 * Este módulo gerencia todos os aspectos do dashboard, incluindo:
 * - Inicialização e atualização de gráficos
 * - Animação de contadores
 * - Atualização automática de dados
 * - Tratamento de erros e exibição de notificações
 * - Alternância de tema claro/escuro
 */
const DashboardModule = (function () {
    // Variáveis privadas
    let charts = {};                // Armazena referências aos gráficos
    let baseUrl = '';               // URL base para requisições AJAX
    let refreshInterval = 300000;   // Intervalo de atualização (5 minutos)
    let isRefreshing = false;       // Flag para controlar requisições simultâneas
    let darkMode = false;           // Estado do tema (claro/escuro)

    /**
     * Inicializa o módulo
     * @param {Object} config Configurações do módulo
     */
    function init(config) {
        baseUrl = config.baseUrl || '';

        // Inicializa quando o DOM estiver pronto
        document.addEventListener('DOMContentLoaded', function () {
            console.log('Dashboard inicializado');

            // Inicializa o tema
            initTheme();

            // Inicializa tooltips
            initTooltips();

            // Anima os cards de estatísticas
            animateStatCards();

            // Anima os contadores
            animateCounters();

            // Inicializa os gráficos
            initCharts();

            // Melhora os indicadores de carregamento
            enhanceLoadingIndicators();

            // Oculta os indicadores de carregamento após inicialização
            setTimeout(() => {
                showLoadingIndicators(false);
            }, 500);

            // Configura atualização automática
            setupAutoRefresh();

            // Configura o botão de alternância de tema
            setupThemeToggle();

            // Adiciona efeitos de hover nos cards
            setupCardHoverEffects();
        });
    }

    /**
     * Inicializa o tema com base na preferência do usuário
     */
    function initTheme() {
        // Verifica se há uma preferência salva
        const savedTheme = localStorage.getItem('dashboard-theme');
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            darkMode = true;
        } else {
            // Verifica a preferência do sistema
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-theme', 'dark');
                darkMode = true;
            }
        }

        // Atualiza o ícone do botão de tema
        updateThemeIcon();
    }

    /**
     * Configura o botão de alternância de tema
     */
    function setupThemeToggle() {
        const themeToggle = document.getElementById('theme-toggle');
        if (!themeToggle) return;

        themeToggle.addEventListener('click', function () {
            darkMode = !darkMode;
            document.documentElement.setAttribute('data-theme', darkMode ? 'dark' : 'light');
            localStorage.setItem('dashboard-theme', darkMode ? 'dark' : 'light');
            updateThemeIcon();

            // Atualiza os gráficos para o novo tema
            updateChartsTheme();
        });
    }

    /**
     * Atualiza o ícone do botão de tema
     */
    function updateThemeIcon() {
        const themeToggle = document.getElementById('theme-toggle');
        if (!themeToggle) return;

        themeToggle.innerHTML = darkMode ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        themeToggle.setAttribute('title', darkMode ? 'Mudar para tema claro' : 'Mudar para tema escuro');
    }

    /**
     * Atualiza os gráficos para o tema atual
     */
    function updateChartsTheme() {
        // Atualiza as cores dos gráficos com base no tema
        const textColor = darkMode ? '#e2e8f0' : '#6c757d';
        const gridColor = darkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';

        // Atualiza as configurações globais do Chart.js
        Chart.defaults.color = textColor;

        // Atualiza cada gráfico individualmente
        for (const chartName in charts) {
            if (charts.hasOwnProperty(chartName)) {
                const chart = charts[chartName];

                // Atualiza as cores do texto
                if (chart.options.scales && chart.options.scales.x) {
                    chart.options.scales.x.ticks.color = textColor;
                    chart.options.scales.x.grid.color = gridColor;
                }

                if (chart.options.scales && chart.options.scales.y) {
                    chart.options.scales.y.ticks.color = textColor;
                    chart.options.scales.y.grid.color = gridColor;
                }

                // Atualiza as cores da legenda
                if (chart.options.plugins && chart.options.plugins.legend) {
                    chart.options.plugins.legend.labels.color = textColor;
                }

                // Atualiza o gráfico
                chart.update();
            }
        }
    }

    /**
     * Configura efeitos de hover nos cards
     */
    function setupCardHoverEffects() {
        const cards = document.querySelectorAll('.chart-card, .recent-tickets-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function () {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = darkMode ?
                    '0 8px 20px rgba(0, 0, 0, 0.3)' :
                    '0 8px 20px rgba(0, 0, 0, 0.12)';
            });

            card.addEventListener('mouseleave', function () {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = darkMode ?
                    '0 4px 15px rgba(0, 0, 0, 0.2)' :
                    '0 4px 15px rgba(0, 0, 0, 0.08)';
            });
        });
    }

    /**
     * Inicializa tooltips do Bootstrap
     */
    function initTooltips() {
        try {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        } catch (e) {
            console.warn('Erro ao inicializar tooltips:', e);
        }
    }

    /**
     * Anima os cards de estatísticas com efeito de entrada
     */
    function animateStatCards() {
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(function (card, index) {
            setTimeout(function () {
                card.classList.add('animate-in');
            }, index * 100);
        });
    }

    /**
     * Anima os contadores com efeito de contagem
     */
    function animateCounters() {
        const counters = document.querySelectorAll('.counter-number');

        counters.forEach(counter => {
            const target = parseInt(counter.textContent);
            const duration = 1500; // 1.5 segundos
            const step = Math.ceil(target / (duration / 16)); // 60fps

            let current = 0;
            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    counter.textContent = target;
                    clearInterval(timer);
                } else {
                    counter.textContent = current;
                }
            }, 16);
        });
    }

    /**
     * Melhora os indicadores de carregamento
     */
    function enhanceLoadingIndicators() {
        const loadingIndicators = document.querySelectorAll('.chart-loading');

        loadingIndicators.forEach(indicator => {
            // Adiciona um texto de "Carregando..."
            const loadingText = document.createElement('span');
            loadingText.textContent = 'Carregando...';
            loadingText.className = 'chart-loading-text';

            indicator.appendChild(loadingText);
        });
    }

    /**
     * Inicializa todos os gráficos do dashboard
     */
    function initCharts() {
        // Configura o Chart.js para usar cores e fontes consistentes
        Chart.defaults.font.family = "'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.color = darkMode ? '#e2e8f0' : '#6c757d';
        Chart.defaults.plugins.tooltip.backgroundColor = darkMode ? 'rgba(26, 32, 44, 0.9)' : 'rgba(44, 62, 80, 0.9)';
        Chart.defaults.plugins.tooltip.titleFont = { weight: 'bold' };
        Chart.defaults.plugins.tooltip.bodyFont = { size: 12 };
        Chart.defaults.plugins.tooltip.padding = 10;
        Chart.defaults.plugins.tooltip.cornerRadius = 6;
        Chart.defaults.plugins.tooltip.displayColors = true;
        Chart.defaults.plugins.tooltip.boxWidth = 10;
        Chart.defaults.plugins.tooltip.boxHeight = 10;
        Chart.defaults.plugins.tooltip.boxPadding = 3;

        // Inicializa cada gráfico individualmente
        initStatusChart();
        initSectorChart();
        initMonthChart();
        initAvgTimeChart();
        initServiceTypeChart();
        initWeekdayChart();
    }

    /**
     * Inicializa o gráfico de chamados por status
     */
    function initStatusChart() {
        const ctx = document.getElementById('chamadosPorStatusChart');
        if (!ctx) return;

        try {
            const chartData = JSON.parse(ctx.getAttribute('data-chart') || '{"labels":[],"data":[],"backgroundColor":[]}');

            charts.chamadosPorStatusChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Chamados por Status',
                        data: chartData.data,
                        backgroundColor: chartData.backgroundColor,
                        borderWidth: 1,
                        borderColor: darkMode ? '#2d3748' : '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 15,
                                padding: 15,
                                font: {
                                    size: 12
                                },
                                usePointStyle: true,
                                pointStyle: 'circle'
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
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 1000
                    }
                }
            });
        } catch (e) {
            console.error('Erro ao inicializar gráfico de status:', e);
            hideChartLoading('chamadosPorStatusChart');
        }
    }

    /**
     * Inicializa o gráfico de chamados por setor
     */
    function initSectorChart() {
        const ctx = document.getElementById('chamadosPorSetorChart');
        if (!ctx) return;

        try {
            const chartData = JSON.parse(ctx.getAttribute('data-chart') || '{"labels":[],"data":[],"backgroundColor":[]}');

            charts.chamadosPorSetorChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Chamados por Setor',
                        data: chartData.data,
                        backgroundColor: chartData.backgroundColor,
                        borderWidth: 0,
                        borderRadius: 6,
                        maxBarThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                color: darkMode ? '#e2e8f0' : '#6c757d'
                            },
                            grid: {
                                display: false
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45,
                                color: darkMode ? '#e2e8f0' : '#6c757d'
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    animation: {
                        delay: function (context) {
                            return context.dataIndex * 100;
                        },
                        duration: 1000
                    }
                }
            });
        } catch (e) {
            console.error('Erro ao inicializar gráfico de setores:', e);
            hideChartLoading('chamadosPorSetorChart');
        }
    }

    /**
     * Inicializa o gráfico de chamados por mês
     */
    function initMonthChart() {
        const ctx = document.getElementById('chamadosPorMesChart');
        if (!ctx) return;

        try {
            const chartData = JSON.parse(ctx.getAttribute('data-chart') || '{"labels":[],"data":[]}');

            charts.chamadosPorMesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Chamados por Mês',
                        data: chartData.data,
                        backgroundColor: 'rgba(67, 97, 238, 0.2)',
                        borderColor: '#4361ee',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: darkMode ? '#2d3748' : '#fff',
                        pointBorderColor: '#4361ee',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                color: darkMode ? '#e2e8f0' : '#6c757d'
                            },
                            grid: {
                                color: darkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            ticks: {
                                color: darkMode ? '#e2e8f0' : '#6c757d'
                            },
                            grid: {
                                color: darkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)'
                            }
                        }
                    },
                    animation: {
                        tension: {
                            duration: 1000,
                            easing: 'linear'
                        }
                    }
                }
            });
        } catch (e) {
            console.error('Erro ao inicializar gráfico de meses:', e);
            hideChartLoading('chamadosPorMesChart');
        }
    }

    /**
     * Inicializa o gráfico de tempo médio por setor
     */
    function initAvgTimeChart() {
        const ctx = document.getElementById('tempoMedioPorSetorChart');
        if (!ctx) return;

        try {
            const chartData = JSON.parse(ctx.getAttribute('data-chart') || '{"labels":[],"data":[],"backgroundColor":[]}');

            // Verifica se há dados válidos
            if (!chartData.data || chartData.data.length === 0 || (chartData.data.length === 1 && chartData.data[0] === 0)) {
                hideChartLoading('tempoMedioPorSetorChart');
                return;
            }

            charts.tempoMedioPorSetorChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Tempo Médio (horas)',
                        data: chartData.data,
                        backgroundColor: chartData.backgroundColor,
                        borderWidth: 0,
                        borderRadius: 6,
                        maxBarThickness: 20
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const value = context.raw || 0;
                                    return `${value} horas`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Horas',
                                color: darkMode ? '#e2e8f0' : '#6c757d'
                            },
                            ticks: {
                                color: darkMode ? '#e2e8f0' : '#6c757d'
                            },
                            grid: {
                                color: darkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        y: {
                            ticks: {
                                color: darkMode ? '#e2e8f0' : '#6c757d'
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    animation: {
                        delay: function (context) {
                            return context.dataIndex * 100;
                        },
                        duration: 1000
                    }
                }
            });
        } catch (e) {
            console.error('Erro ao inicializar gráfico de tempo médio:', e);
            hideChartLoading('tempoMedioPorSetorChart');
        }
    }

    /**
     * Inicializa o gráfico de chamados por tipo de serviço
     */
    function initServiceTypeChart() {
        const ctx = document.getElementById('chamadosPorTipoServicoChart');
        if (!ctx) return;

        try {
            const chartData = JSON.parse(ctx.getAttribute('data-chart') || '{"labels":[],"data":[],"backgroundColor":[]}');

            // Verifica se há dados válidos
            if (!chartData.data || chartData.data.length === 0 || (chartData.data.length === 1 && chartData.data[0] === 0)) {
                hideChartLoading('chamadosPorTipoServicoChart');
                return;
            }

            charts.chamadosPorTipoServicoChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Chamados por Tipo de Serviço',
                        data: chartData.data,
                        backgroundColor: chartData.backgroundColor,
                        borderWidth: 1,
                        borderColor: darkMode ? '#2d3748' : '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 15,
                                padding: 15,
                                font: {
                                    size: 11
                                },
                                color: darkMode ? '#e2e8f0' : '#6c757d',
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        }
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 1000
                    }
                }
            });
        } catch (e) {
            console.error('Erro ao inicializar gráfico de tipos de serviço:', e);
            hideChartLoading('chamadosPorTipoServicoChart');
        }
    }

    /**
     * Inicializa o gráfico de chamados por dia da semana
     */
    function initWeekdayChart() {
        const ctx = document.getElementById('chamadosPorDiaSemanaChart');
        if (!ctx) return;

        try {
            const chartData = JSON.parse(ctx.getAttribute('data-chart') || '{"labels":[],"data":[],"backgroundColor":[]}');

            // Verifica se há dados válidos
            if (!chartData.data || chartData.data.length === 0 || array_sum(chartData.data) === 0) {
                hideChartLoading('chamadosPorDiaSemanaChart');
                return;
            }

            charts.chamadosPorDiaSemanaChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Chamados por Dia da Semana',
                        data: chartData.data,
                        backgroundColor: chartData.backgroundColor,
                        borderWidth: 0,
                        borderRadius: 6,
                        maxBarThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                color: darkMode ? '#e2e8f0' : '#6c757d'
                            },
                            grid: {
                                color: darkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            ticks: {
                                color: darkMode ? '#e2e8f0' : '#6c757d'
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    animation: {
                        delay: function (context) {
                            return context.dataIndex * 100;
                        },
                        duration: 1000
                    }
                }
            });
        } catch (e) {
            console.error('Erro ao inicializar gráfico de dias da semana:', e);
            hideChartLoading('chamadosPorDiaSemanaChart');
        }
    }

    /**
     * Oculta o indicador de carregamento de um gráfico específico
     * @param {string} chartId ID do elemento canvas do gráfico
     */
    function hideChartLoading(chartId) {
        const canvas = document.getElementById(chartId);
        if (!canvas) return;

        const chartBody = canvas.closest('.chart-body');
        if (!chartBody) return;

        const loadingIndicator = chartBody.querySelector('.chart-loading');
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
        }
    }

    /**
     * Configura a atualização automática dos dados
     */
    function setupAutoRefresh() {
        // Atualiza os dados a cada 5 minutos
        setInterval(refreshDashboardData, refreshInterval);

        // Adiciona botão de atualização manual
        const refreshBtn = document.getElementById('refresh-dashboard');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (isRefreshing) return;

                refreshDashboardData();
            });
        }
    }

    /**
     * Atualiza os dados do dashboard via AJAX
     */
    function refreshDashboardData() {
        if (isRefreshing) return;

        isRefreshing = true;
        showLoadingIndicators(true);

        fetch(`${baseUrl}dashboard/getChartData`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na requisição: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Dados atualizados:', data);

                // Atualiza os contadores
                updateStatistics(data.estatisticas);

                // Atualiza os gráficos
                updateCharts(data);

                // Exibe notificação de sucesso
                showUpdateNotification('Dashboard atualizado com sucesso!', 'success');
            })
            .catch(error => {
                console.error('Erro ao atualizar dashboard:', error);
                showUpdateNotification('Erro ao atualizar dados. Tente novamente.', 'error');
            })
            .finally(() => {
                isRefreshing = false;
                showLoadingIndicators(false);
            });
    }

    /**
     * Exibe ou oculta indicadores de carregamento
     * @param {boolean} show Indica se deve mostrar ou ocultar os indicadores
     */
    function showLoadingIndicators(show) {
        // Atualiza o botão de refresh
        const refreshBtn = document.getElementById('refresh-dashboard');
        if (refreshBtn) {
            if (show) {
                refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                refreshBtn.classList.add('loading');
                refreshBtn.disabled = true;
            } else {
                refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i>';
                refreshBtn.classList.remove('loading');
                refreshBtn.disabled = false;
            }
        }

        // Exibe/oculta indicadores de carregamento nos gráficos
        const loadingIndicators = document.querySelectorAll('.chart-loading');
        loadingIndicators.forEach(indicator => {
            indicator.style.display = show ? 'flex' : 'none';
        });
    }

    /**
     * Atualiza os contadores de estatísticas
     * @param {Object} estatisticas Dados de estatísticas
     */
    function updateStatistics(estatisticas) {
        if (!estatisticas) return;

        // Atualiza os contadores com animação
        updateCounterWithAnimation('total-chamados', estatisticas.total);
        updateCounterWithAnimation('chamados-abertos', estatisticas.abertos);
        updateCounterWithAnimation('chamados-andamento', estatisticas.em_andamento);
        updateCounterWithAnimation('chamados-concluidos', estatisticas.concluidos);
        updateCounterWithAnimation('concluidos-hoje', estatisticas.concluidos_hoje);

        // Atualiza o tempo médio
        const tempoMedioEl = document.getElementById('tempo-medio');
        if (tempoMedioEl) {
            const tempoMedio = estatisticas.tempo_medio_atendimento || 0;

            // Atualiza o valor numérico
            const numberEl = tempoMedioEl.querySelector('.counter-number');
            if (numberEl) {
                numberEl.textContent = tempoMedio;
            }

            // Atualiza as barras de progresso
            updateProgressBars(estatisticas);
        }
    }

    /**
     * Atualiza um contador com animação
     * @param {string} elementId ID do elemento
     * @param {number} newValue Novo valor
     */
    function updateCounterWithAnimation(elementId, newValue) {
        const element = document.getElementById(elementId);
        if (!element) return;

        const numberEl = element.querySelector('.counter-number');
        if (!numberEl) return;

        const currentValue = parseInt(numberEl.textContent) || 0;
        const difference = newValue - currentValue;

        if (difference === 0) return;

        // Adiciona classe de destaque
        element.classList.add('pulse');

        // Anima o contador
        let startTime;
        const duration = 1000; // 1 segundo

        function animate(timestamp) {
            if (!startTime) startTime = timestamp;
            const progress = Math.min((timestamp - startTime) / duration, 1);
            const value = Math.floor(currentValue + difference * progress);
            numberEl.textContent = value;

            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                numberEl.textContent = newValue;

                // Remove a classe de destaque após a animação
                setTimeout(() => {
                    element.classList.remove('pulse');
                }, 500);
            }
        }

        requestAnimationFrame(animate);
    }

    /**
     * Atualiza as barras de progresso com base nas estatísticas
     * @param {Object} estatisticas Dados de estatísticas
     */
    function updateProgressBars(estatisticas) {
        const total = estatisticas.total || 0;

        // Calcula percentuais
        const percentAbertos = total > 0 ? Math.round((estatisticas.abertos / total) * 100) : 0;
        const percentAndamento = total > 0 ? Math.round((estatisticas.em_andamento / total) * 100) : 0;
        const percentConcluidos = total > 0 ? Math.round((estatisticas.concluidos / total) * 100) : 0;
        const percentHoje = estatisticas.concluidos > 0 ? Math.round((estatisticas.concluidos_hoje / estatisticas.concluidos) * 100) : 0;
        const tempoRelativo = Math.min(100, (estatisticas.tempo_medio_atendimento / 48) * 100);

        // Atualiza os percentuais exibidos
        updateTrendValue('chamados-abertos', percentAbertos + '%');
        updateTrendValue('chamados-andamento', percentAndamento + '%');
        updateTrendValue('chamados-concluidos', percentConcluidos + '%');
        updateTrendValue('concluidos-hoje', percentHoje + '%');

        // Atualiza as barras de progresso
        updateProgressBar('chamados-abertos', percentAbertos);
        updateProgressBar('chamados-andamento', percentAndamento);
        updateProgressBar('chamados-concluidos', percentConcluidos);
        updateProgressBar('concluidos-hoje', percentHoje);
        updateProgressBar('tempo-medio', tempoRelativo);
    }

    /**
     * Atualiza o valor de tendência de um contador
     * @param {string} elementId ID do elemento contador
     * @param {string} value Valor a ser exibido
     */
    function updateTrendValue(elementId, value) {
        const element = document.getElementById(elementId);
        if (!element) return;

        const trendEl = element.closest('.stat-content').querySelector('.stat-trend span');
        if (trendEl) {
            trendEl.textContent = value;
        }
    }

    /**
     * Atualiza a barra de progresso de um contador
     * @param {string} elementId ID do elemento contador
     * @param {number} percent Percentual de preenchimento
     */
    function updateProgressBar(elementId, percent) {
        const element = document.getElementById(elementId);
        if (!element) return;

        const progressBar = element.closest('.stat-content').querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.width = percent + '%';
        }
    }

    /**
     * Soma os valores de um array
     * @param {Array} arr Array de números
     * @return {number} Soma dos valores
     */
    function array_sum(arr) {
        if (!arr || !Array.isArray(arr)) return 0;
        return arr.reduce((a, b) => a + b, 0);
    }

    /**
     * Atualiza os gráficos com novos dados
     * @param {Object} data Dados para os gráficos
     */
    function updateCharts(data) {
        // Atualiza o gráfico de chamados por status
        if (data.chamadosPorStatus && charts.chamadosPorStatusChart) {
            updateChartData(charts.chamadosPorStatusChart, data.chamadosPorStatus);
        }

        // Atualiza o gráfico de chamados por setor
        if (data.chamadosPorSetor && charts.chamadosPorSetorChart) {
            updateChartData(charts.chamadosPorSetorChart, data.chamadosPorSetor);
        }

        // Atualiza o gráfico de chamados por mês
        if (data.chamadosPorMes && charts.chamadosPorMesChart) {
            updateChartData(charts.chamadosPorMesChart, data.chamadosPorMes);
        }

        // Atualiza o gráfico de tempo médio por setor
        if (data.tempoMedioPorSetor && charts.tempoMedioPorSetorChart) {
            // Verifica se há dados válidos
            if (data.tempoMedioPorSetor.data && data.tempoMedioPorSetor.data.length > 0 &&
                !(data.tempoMedioPorSetor.data.length === 1 && data.tempoMedioPorSetor.data[0] === 0)) {
                updateChartData(charts.tempoMedioPorSetorChart, data.tempoMedioPorSetor);
            }
        }

        // Atualiza o gráfico de chamados por tipo de serviço
        if (data.chamadosPorTipoServico && charts.chamadosPorTipoServicoChart) {
            // Verifica se há dados válidos
            if (data.chamadosPorTipoServico.data && data.chamadosPorTipoServico.data.length > 0 &&
                !(data.chamadosPorTipoServico.data.length === 1 && data.chamadosPorTipoServico.data[0] === 0)) {
                updateChartData(charts.chamadosPorTipoServicoChart, data.chamadosPorTipoServico);
            }
        }

        // Atualiza o gráfico de chamados por dia da semana
        if (data.chamadosPorDiaSemana && charts.chamadosPorDiaSemanaChart) {
            // Verifica se há dados válidos
            if (data.chamadosPorDiaSemana.data && data.chamadosPorDiaSemana.data.length > 0 &&
                array_sum(data.chamadosPorDiaSemana.data) > 0) {
                updateChartData(charts.chamadosPorDiaSemanaChart, data.chamadosPorDiaSemana);
            }
        }

        // Atualiza a tabela de chamados recentes
        if (data.recentes) {
            updateRecentTickets(data.recentes);
        }
    }

    /**
     * Atualiza os dados de um gráfico
     * @param {Chart} chart Objeto do gráfico
     * @param {Object} newData Novos dados
     */
    function updateChartData(chart, newData) {
        if (!chart || !newData) return;

        // Atualiza labels
        if (newData.labels) {
            chart.data.labels = newData.labels;
        }

        // Atualiza dados
        if (newData.data) {
            chart.data.datasets[0].data = newData.data;
        }

        // Atualiza cores
        if (newData.backgroundColor) {
            chart.data.datasets[0].backgroundColor = newData.backgroundColor;
        }

        // Atualiza o gráfico
        chart.update();
    }

    /**
     * Atualiza a tabela de chamados recentes
     * @param {Array} recentes Lista de chamados recentes
     */
    function updateRecentTickets(recentes) {
        const tableBody = document.querySelector('.recent-tickets-table tbody');
        if (!tableBody) return;

        // Se não houver chamados recentes, exibe mensagem
        if (!recentes || recentes.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="no-tickets">
                        <div class="no-data-message">
                            <i class="fas fa-ticket-alt"></i>
                            <p>Nenhum chamado encontrado.</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        // Constrói as linhas da tabela
        let html = '';
        recentes.forEach((chamado, index) => {
            html += `
                <tr class="fade-in-up" style="animation-delay: ${index * 0.1}s">
                    <td class="ticket-id">${chamado.id}</td>
                    <td class="ticket-user">
                        <i class="fas fa-user"></i>
                        ${escapeHtml(chamado.solicitante)}
                    </td>
                    <td>
                        <div class="ticket-desc" data-bs-toggle="tooltip" title="${escapeHtml(chamado.descricao)}">
                            ${escapeHtml(chamado.descricao.substring(0, 30))}${chamado.descricao.length > 30 ? '...' : ''}
                        </div>
                    </td>
                    <td>${escapeHtml(chamado.setor)}</td>
                    <td>
                        <span class="status-badge" style="background-color: ${chamado.status_cor || '#4361ee'}">
                            <i class="fas fa-circle"></i>
                            ${escapeHtml(chamado.status)}
                        </span>
                    </td>
                    <td class="ticket-date">
                        <i class="far fa-calendar-alt"></i>
                        ${formatDate(chamado.data_solicitacao)}
                    </td>
                    <td>
                        <div class="ticket-actions">
                            <a href="${baseUrl}chamados/visualizar/${chamado.id}" class="action-btn view" data-bs-toggle="tooltip" title="Visualizar">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            `;
        });

        tableBody.innerHTML = html;

        // Reinicializa os tooltips
        initTooltips();
    }

    /**
     * Escapa caracteres HTML para evitar XSS
     * @param {string} text Texto a ser escapado
     * @return {string} Texto escapado
     */
    function escapeHtml(text) {
        if (!text) return '';
        return text
            .toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Formata uma data para exibição
     * @param {string} dateStr String de data
     * @return {string} Data formatada
     */
    function formatDate(dateStr) {
        if (!dateStr) return '';
        try {
            const date = new Date(dateStr);
            return date.toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return dateStr;
        }
    }

    /**
     * Exibe uma notificação de atualização
     * @param {string} message Mensagem
     * @param {string} type Tipo (success, error)
     */
    function showUpdateNotification(message, type = 'success') {
        // Verifica se o elemento de notificação existe
        let notificationEl = document.getElementById('dashboard-notification');

        // Se não existir, cria um novo
        if (!notificationEl) {
            notificationEl = document.createElement('div');
            notificationEl.id = 'dashboard-notification';
            notificationEl.className = 'dashboard-notification';
            document.body.appendChild(notificationEl);
        }

        // Configura a notificação
        notificationEl.className = `dashboard-notification ${type}`;
        notificationEl.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            ${message}
        `;

        // Exibe a notificação
        setTimeout(() => {
            notificationEl.classList.add('show');
        }, 10);

        // Oculta após 3 segundos
        setTimeout(() => {
            notificationEl.classList.remove('show');
        }, 3000);
    }

    // API pública
    return {
        init: init,
        refreshData: refreshDashboardData,
        toggleTheme: function () {
            darkMode = !darkMode;
            document.documentElement.setAttribute('data-theme', darkMode ? 'dark' : 'light');
            localStorage.setItem('dashboard-theme', darkMode ? 'dark' : 'light');
            updateThemeIcon();
            updateChartsTheme();
        }
    }; 
})();

// Inicializa o módulo quando o script for carregado
DashboardModule.init({
    baseUrl: document.currentScript.getAttribute('data-base-url') || ''
});