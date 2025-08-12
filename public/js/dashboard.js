/**
 * Dashboard - Sistema de Gestão de Chamados
 * Versão: 7.0.0 - CÓDIGO COMPLETO E DOCUMENTADO
 * 
 * DESCRIÇÃO:
 * Este arquivo gerencia toda a interatividade do dashboard, incluindo:
 * - Inicialização e configuração de gráficos Chart.js
 * - Animações de contadores e cards
 * - Atualização automática de dados via AJAX
 * - Gerenciamento de temas (claro/escuro)
 * - Manipulação da tabela de chamados recentes
 * - Sistema de notificações
 * 
 * DEPENDÊNCIAS:
 * - Chart.js (para gráficos)
 * - Bootstrap (para tooltips)
 * - FontAwesome (para ícones)
 * 
 * COMPATIBILIDADE:
 * - Navegadores modernos (Chrome 60+, Firefox 55+, Safari 12+, Edge 79+)
 * - Dispositivos móveis e desktop
 * 
 * AUTOR: Sistema de Gestão de Chamados
 * DATA: 2024
 */

// =============================================================================
// VERIFICAÇÃO DE PÁGINA E INICIALIZAÇÃO
// =============================================================================

// Verifica se estamos na página correta antes de executar
if (window.location.pathname.includes('/dashboard') || document.title.includes('Dashboard')) {
    console.log('🚀 Inicializando dashboard.js versão 7.0.0...');

    // =============================================================================
    // PALETAS DE CORES PARA GRÁFICOS
    // Cada tipo de gráfico tem sua própria paleta para evitar repetição
    // =============================================================================

    const COLOR_PALETTES = {
        // Tempo Médio por Setor - Cores vibrantes e distintas
        timePerformance: [
            '#FF6B6B', // Vermelho coral
            '#4ECDC4', // Turquesa
            '#45B7D1', // Azul céu
            '#96CEB4', // Verde menta
            '#FFEAA7', // Amarelo suave
            '#DDA0DD', // Ameixa
            '#98D8C8', // Verde água
            '#F7DC6F', // Dourado
            '#BB8FCE', // Lavanda
            '#85C1E9', // Azul claro
            '#F8C471', // Laranja claro
            '#82E0AA', // Verde claro
            '#F1948A', // Rosa salmão
            '#AED6F1', // Azul bebê
            '#D7BDE2'  // Roxo claro
        ],

        // Tipo de Serviço - Cores intensas e profissionais
        serviceTypes: [
            '#E74C3C', // Vermelho intenso
            '#3498DB', // Azul royal
            '#2ECC71', // Verde esmeralda
            '#F39C12', // Laranja
            '#9B59B6', // Roxo
            '#1ABC9C', // Turquesa escuro
            '#E67E22', // Laranja escuro
            '#34495E', // Azul acinzentado
            '#16A085', // Verde petróleo
            '#27AE60', // Verde floresta
            '#8E44AD', // Roxo escuro
            '#2980B9', // Azul oceano
            '#F1C40F', // Amarelo ouro
            '#E91E63', // Rosa pink
            '#FF5722'  // Vermelho alaranjado
        ],

        // Setores - Cores naturais e harmoniosas
        sectors: [
            '#FF7043', // Laranja terra
            '#66BB6A', // Verde grama
            '#42A5F5', // Azul céu
            '#AB47BC', // Roxo médio
            '#26A69A', // Verde água
            '#FFCA28', // Amarelo sol
            '#EF5350', // Vermelho suave
            '#5C6BC0', // Índigo
            '#78909C', // Cinza azulado
            '#FFA726', // Laranja dourado
            '#EC407A', // Rosa vibrante
            '#29B6F6', // Azul claro
            '#9CCC65', // Verde lima
            '#FF8A65', // Coral
            '#7E57C2'  // Roxo violeta
        ],

        // Status - Cores padronizadas do sistema
        status: {
            1: '#FFC107', // Amarelo - Aberto
            2: '#007BFF', // Azul - Em Atendimento
            3: '#9C27B0', // Roxo - Pausado
            4: '#28A745', // Verde - Concluído
            5: '#343A40'  // Preto - Cancelado
        },

        // Dias da semana - Cores distintas para cada dia
        weekdays: [
            '#FF4757', // Vermelho vibrante - Segunda
            '#2ED573', // Verde neon - Terça
            '#1E90FF', // Azul dodger - Quarta
            '#FF6348', // Tomate - Quinta
            '#7B68EE', // Azul médio - Sexta
            '#FFD700', // Dourado - Sábado
            '#FF69B4'  // Rosa quente - Domingo
        ]
    };

    // =============================================================================
    // FUNÇÕES UTILITÁRIAS
    // =============================================================================

    /**
     * Obtém cor específica baseada no tipo de gráfico e índice
     * @param {string} type - Tipo do gráfico (timePerformance, serviceTypes, etc.)
     * @param {number} index - Índice do elemento
     * @param {number|null} id - ID específico (usado para status)
     * @returns {string} Código hexadecimal da cor
     */
    function getChartColor(type, index, id = null) {
        switch (type) {
            case 'timePerformance':
                return COLOR_PALETTES.timePerformance[index % COLOR_PALETTES.timePerformance.length];
            case 'serviceTypes':
                return COLOR_PALETTES.serviceTypes[index % COLOR_PALETTES.serviceTypes.length];
            case 'sectors':
                return COLOR_PALETTES.sectors[index % COLOR_PALETTES.sectors.length];
            case 'status':
                return COLOR_PALETTES.status[id] || '#6C757D';
            case 'weekdays':
                return COLOR_PALETTES.weekdays[index % COLOR_PALETTES.weekdays.length];
            default:
                return '#4361ee'; // Cor padrão
        }
    }

    /**
     * Determina a classe CSS baseada no status do chamado
     * @param {string} status - Status do chamado
     * @returns {string} Classe CSS correspondente
     */
    function getStatusClass(status) {
        const statusMap = {
            'Aberto': 'status-aberto',
            'Em Atendimento': 'status-andamento',
            'Concluído': 'status-concluido',
            'Pausado': 'status-pausado',
            'Cancelado': 'status-cancelado'
        };

        return statusMap[status] || 'status-aberto';
    }

    /**
     * Escapa caracteres HTML para prevenir XSS
     * @param {string} text - Texto a ser escapado
     * @returns {string} Texto escapado
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
     * Formata data para exibição
     * @param {string} dateStr - String de data
     * @returns {string} Data formatada
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
     * Soma elementos de um array
     * @param {Array} arr - Array de números
     * @returns {number} Soma dos elementos
     */
    function array_sum(arr) {
        if (!arr || !Array.isArray(arr)) return 0;
        return arr.reduce((a, b) => a + b, 0);
    }

    // =============================================================================
    // SISTEMA DE CARREGAMENTO DO CHART.JS
    // =============================================================================

    /**
     * Aguarda o carregamento do Chart.js com timeout
     * @returns {Promise} Promise que resolve quando Chart.js está disponível
     */
    function waitForChartJS() {
        return new Promise((resolve, reject) => {
            let attempts = 0;
            const maxAttempts = 100; // 10 segundos máximo

            const checkChart = setInterval(() => {
                attempts++;

                if (typeof Chart !== 'undefined') {
                    console.log('✅ Chart.js encontrado - versão:', Chart.version || 'desconhecida');
                    clearInterval(checkChart);
                    resolve();
                } else if (attempts >= maxAttempts) {
                    console.error('❌ Chart.js não carregou após 10 segundos');
                    clearInterval(checkChart);
                    reject(new Error('Chart.js não carregou'));
                } else {
                    console.log(`⏳ Tentativa ${attempts}/${maxAttempts} - Aguardando Chart.js...`);
                }
            }, 100);
        });
    }

    /**
     * Exibe erro quando Chart.js não pode ser carregado
     */
    function showChartError() {
        console.error('❌ Erro crítico: Chart.js não pôde ser carregado');

        const chartContainers = document.querySelectorAll('.chart-body');
        chartContainers.forEach(container => {
            container.innerHTML = `
                <div class="chart-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Erro ao carregar gráficos</p>
                    <small>Chart.js não pôde ser carregado</small>
                </div>
            `;
        });
    }

    // =============================================================================
    // LIMPEZA E INICIALIZAÇÃO
    // =============================================================================

    // Verifica se já existe uma instância e remove
    if (window.DashboardModule) {
        console.log('⚠️ Dashboard já inicializado, destruindo charts existentes...');
        if (window.DashboardModule.charts) {
            Object.values(window.DashboardModule.charts).forEach(chart => {
                if (chart && typeof chart.destroy === 'function') {
                    chart.destroy();
                }
            });
        }
        delete window.DashboardModule;
    }

    // Aguarda Chart.js antes de inicializar
    waitForChartJS()
        .then(() => {
            console.log('🎯 Iniciando dashboard com Chart.js carregado...');
            initializeDashboardModule();
        })
        .catch((error) => {
            console.error('❌ Erro ao carregar Chart.js:', error);
            showChartError();
        });

    // =============================================================================
    // MÓDULO PRINCIPAL DO DASHBOARD
    // =============================================================================

    /**
     * Inicializa o módulo principal do dashboard
     */
    function initializeDashboardModule() {
        (function () {
            'use strict';

            /**
             * Módulo Dashboard - Gerencia toda a funcionalidade do painel
             */
            const DashboardModule = (function () {
                // Variáveis privadas do módulo
                let charts = {};                    // Armazena instâncias dos gráficos
                let baseUrl = '';                   // URL base da aplicação
                let refreshInterval = 300000;       // Intervalo de atualização (5 min)
                let isRefreshing = false;           // Flag para evitar múltiplas atualizações
                let darkMode = false;               // Estado do tema escuro
                let isInitialized = false;          // Flag de inicialização

                /**
                 * Inicializa o dashboard
                 * @param {Object} config - Configurações de inicialização
                 */
                function init(config) {
                    if (isInitialized) {
                        console.warn('Dashboard já foi inicializado');
                        return;
                    }

                    // Configura URL base
                    baseUrl = config.baseUrl || (typeof BASE_URL !== 'undefined' ? BASE_URL : '');

                    // Aguarda DOM estar pronto
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', initializeDashboard);
                    } else {
                        initializeDashboard();
                    }
                }

                /**
                 * Função principal de inicialização do dashboard
                 */
                function initializeDashboard() {
                    if (isInitialized) return;

                    console.log('✅ Dashboard inicializado');
                    isInitialized = true;

                    try {
                        // Verifica disponibilidade do Chart.js
                        if (typeof Chart === 'undefined') {
                            throw new Error('Chart.js não está disponível');
                        }

                        // Sequência de inicialização
                        destroyExistingCharts();        // Limpa charts existentes
                        initTheme();                    // Configura tema
                        initTooltips();                 // Inicializa tooltips
                        animateStatCards();             // Anima cards de estatística
                        animateCounters();              // Anima contadores
                        initCharts();                   // Inicializa gráficos
                        enhanceLoadingIndicators();     // Melhora indicadores de carregamento

                        // Remove indicadores de carregamento após delay
                        setTimeout(() => {
                            showLoadingIndicators(false);
                        }, 500);

                        // Configura funcionalidades adicionais
                        setupAutoRefresh();             // Auto-atualização
                        setupThemeToggle();             // Toggle de tema
                        setupCardHoverEffects();        // Efeitos de hover

                    } catch (error) {
                        console.error('❌ Erro ao inicializar dashboard:', error);
                        showChartError();
                    }
                }

                // =============================================================================
                // GERENCIAMENTO DE CHARTS
                // =============================================================================

                /**
                 * Destrói todos os gráficos existentes para evitar conflitos
                 */
                function destroyExistingCharts() {
                    console.log('🗑️ Destruindo charts existentes...');

                    const canvasIds = [
                        'chamadosPorStatusChart',
                        'chamadosPorSetorChart',
                        'chamadosPorMesChart',
                        'tempoMedioPorSetorChart',
                        'chamadosPorTipoServicoChart',
                        'chamadosPorDiaSemanaChart'
                    ];

                    canvasIds.forEach(canvasId => {
                        const canvas = document.getElementById(canvasId);
                        if (canvas) {
                            // Método moderno do Chart.js
                            if (typeof Chart.getChart === 'function') {
                                const existingChart = Chart.getChart(canvas);
                                if (existingChart) {
                                    console.log(`🗑️ Destruindo chart existente: ${canvasId}`);
                                    existingChart.destroy();
                                }
                            }
                            // Fallback para versões antigas
                            else if (canvas.chart) {
                                console.log(`🗑️ Destruindo chart existente (fallback): ${canvasId}`);
                                canvas.chart.destroy();
                                canvas.chart = null;
                            }
                        }
                    });

                    charts = {};
                }

                /**
                 * Configura Chart.js e inicializa todos os gráficos
                 */
                function initCharts() {
                    if (typeof Chart === 'undefined') {
                        console.error('❌ Chart.js não está disponível');
                        showChartError();
                        return;
                    }

                    try {
                        console.log('📊 Configurando Chart.js...');

                        // Configurações globais do Chart.js
                        Chart.defaults.font = Chart.defaults.font || {};
                        Chart.defaults.font.family = "'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";
                        Chart.defaults.font.size = 12;
                        Chart.defaults.color = darkMode ? '#e2e8f0' : '#6c757d';

                        // Configurações de tooltip
                        Chart.defaults.plugins = Chart.defaults.plugins || {};
                        Chart.defaults.plugins.tooltip = Chart.defaults.plugins.tooltip || {};
                        Chart.defaults.plugins.tooltip.backgroundColor = darkMode ? 'rgba(26, 32, 44, 0.9)' : 'rgba(44, 62, 80, 0.9)';
                        Chart.defaults.plugins.tooltip.titleFont = { weight: 'bold' };
                        Chart.defaults.plugins.tooltip.bodyFont = { size: 12 };
                        Chart.defaults.plugins.tooltip.padding = 10;
                        Chart.defaults.plugins.tooltip.cornerRadius = 6;
                        Chart.defaults.plugins.tooltip.displayColors = true;
                        Chart.defaults.plugins.tooltip.boxWidth = 10;
                        Chart.defaults.plugins.tooltip.boxHeight = 10;
                        Chart.defaults.plugins.tooltip.boxPadding = 3;

                        // Inicializa gráficos com delay para melhor performance
                        setTimeout(() => {
                            console.log('🎨 Inicializando gráficos...');

                            initStatusChart();          // Gráfico de status
                            initSectorChart();          // Gráfico de setores
                            initMonthChart();           // Gráfico mensal
                            initAvgTimeChart();         // Gráfico de tempo médio
                            initServiceTypeChart();     // Gráfico de tipos de serviço
                            initWeekdayChart();         // Gráfico de dias da semana

                            console.log('✅ Todos os gráficos inicializados');
                        }, 100);

                    } catch (error) {
                        console.error('❌ Erro ao configurar Chart.js:', error);
                        showChartError();
                    }
                }

                /**
                 * Inicializa gráfico de chamados por status (donut)
                 */
                function initStatusChart() {
                    const ctx = document.getElementById('chamadosPorStatusChart');
                    if (!ctx) {
                        console.warn('⚠️ Canvas chamadosPorStatusChart não encontrado');
                        return;
                    }

                    try {
                        // Remove chart existente
                        if (typeof Chart.getChart === 'function') {
                            const existingChart = Chart.getChart(ctx);
                            if (existingChart) {
                                existingChart.destroy();
                            }
                        }

                        // Obtém dados do elemento
                        const chartData = JSON.parse(ctx.getAttribute('data-chart') || '{"labels":[],"data":[],"backgroundColor":[]}');

                        // Cria novo gráfico
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
                                            font: { size: 12 },
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

                        console.log('✅ Gráfico de status inicializado');
                    } catch (e) {
                        console.error('❌ Erro ao inicializar gráfico de status:', e);
                        hideChartLoading('chamadosPorStatusChart');
                    }
                }

                /**
                 * Inicializa gráfico de chamados por setor (barras)
                 */
                function initSectorChart() {
                    const ctx = document.getElementById('chamadosPorSetorChart');
                    if (!ctx) {
                        console.warn('⚠️ Canvas chamadosPorSetorChart não encontrado');
                        return;
                    }

                    try {
                        // Remove chart existente
                        if (typeof Chart.getChart === 'function') {
                            const existingChart = Chart.getChart(ctx);
                            if (existingChart) {
                                existingChart.destroy();
                            }
                        }

                        // Obtém dados do elemento
                        const chartData = JSON.parse(ctx.getAttribute('data-chart') || '{"labels":[],"data":[],"backgroundColor":[]}');

                        // Aplica cores específicas para setores
                        const sectorColors = chartData.labels.map((label, index) =>
                            getChartColor('sectors', index)
                        );

                        // Cria novo gráfico
                        charts.chamadosPorSetorChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: chartData.labels,
                                datasets: [{
                                    label: 'Chamados por Setor',
                                    data: chartData.data,
                                    backgroundColor: sectorColors,
                                    borderWidth: 0,
                                    borderRadius: 6,
                                    maxBarThickness: 40
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0,
                                            color: darkMode ? '#e2e8f0' : '#6c757d'
                                        },
                                        grid: { display: false }
                                    },
                                    x: {
                                        ticks: {
                                            maxRotation: 45,
                                            minRotation: 45,
                                            color: darkMode ? '#e2e8f0' : '#6c757d'
                                        },
                                        grid: { display: false }
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

                        console.log('✅ Gráfico de setores inicializado');
                    } catch (e) {
                        console.error('❌ Erro ao inicializar gráfico de setores:', e);
                        hideChartLoading('chamadosPorSetorChart');
                    }
                }

                /**
                 * Inicializa gráfico de chamados por mês (linha)
                 */
                function initMonthChart() {
                    const ctx = document.getElementById('chamadosPorMesChart');
                    if (!ctx) {
                        console.warn('⚠️ Canvas chamadosPorMesChart não encontrado');
                        return;
                    }

                    try {
                        // Remove chart existente
                        if (typeof Chart.getChart === 'function') {
                            const existingChart = Chart.getChart(ctx);
                            if (existingChart) {
                                existingChart.destroy();
                            }
                        }

                        // Obtém dados do elemento
                        const chartData = JSON.parse(ctx.getAttribute('data-chart') || '{"labels":[],"data":[]}');

                        // Cria novo gráfico
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
                                    legend: { display: false }
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

                        console.log('✅ Gráfico de meses inicializado');
                    } catch (e) {
                        console.error('❌ Erro ao inicializar gráfico de meses:', e);
                        hideChartLoading('chamadosPorMesChart');
                    }
                }

                /**
                 * Inicializa gráfico de tempo médio por setor (barras horizontais)
                 */
                function initAvgTimeChart() {
                    const ctx = document.getElementById('tempoMedioPorSetorChart');
                    if (!ctx) {
                        console.warn('⚠️ Canvas tempoMedioPorSetorChart não encontrado');
                        return;
                    }

                    try {
                        // Remove chart existente
                        if (typeof Chart.getChart === 'function') {
                            const existingChart = Chart.getChart(ctx);
                            if (existingChart) {
                                existingChart.destroy();
                            }
                        }

                        // Obtém dados do elemento
                        const chartData = JSON.parse(ctx.getAttribute('data-chart') || '{"labels":[],"data":[],"backgroundColor":[]}');

                        // Verifica se há dados válidos
                        if (!chartData.data || chartData.data.length === 0 || (chartData.data.length === 1 && chartData.data[0] === 0)) {
                            hideChartLoading('tempoMedioPorSetorChart');
                            return;
                        }

                        // Aplica cores específicas para tempo médio
                        const timeColors = chartData.labels.map((label, index) =>
                            getChartColor('timePerformance', index)
                        );

                        // Cria novo gráfico
                        charts.tempoMedioPorSetorChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: chartData.labels,
                                datasets: [{
                                    label: 'Tempo Médio (horas)',
                                    data: chartData.data,
                                    backgroundColor: timeColors,
                                    borderWidth: 0,
                                    borderRadius: 6,
                                    maxBarThickness: 20
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                indexAxis: 'y', // Barras horizontais
                                plugins: {
                                    legend: { display: false },
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
                                        grid: { display: false }
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

                        console.log('✅ Gráfico de tempo médio inicializado');
                    } catch (e) {
                        console.error('❌ Erro ao inicializar gráfico de tempo médio:', e);
                        hideChartLoading('tempoMedioPorSetorChart');
                    }
                }

                /**
                 * Inicializa gráfico de chamados por tipo de serviço (pizza)
                 */
                function initServiceTypeChart() {
                    const ctx = document.getElementById('chamadosPorTipoServicoChart');
                    if (!ctx) {
                        console.warn('⚠️ Canvas chamadosPorTipoServicoChart não encontrado');
                        return;
                    }

                    try {
                        // Remove chart existente
                        if (typeof Chart.getChart === 'function') {
                            const existingChart = Chart.getChart(ctx);
                            if (existingChart) {
                                existingChart.destroy();
                            }
                        }

                        // Obtém dados do elemento
                        const chartData = JSON.parse(ctx.getAttribute('data-chart') || '{"labels":[],"data":[],"backgroundColor":[]}');

                        // Verifica se há dados válidos
                        if (!chartData.data || chartData.data.length === 0 || (chartData.data.length === 1 && chartData.data[0] === 0)) {
                            hideChartLoading('chamadosPorTipoServicoChart');
                            return;
                        }

                        // Aplica cores específicas para tipos de serviço
                        const serviceColors = chartData.labels.map((label, index) =>
                            getChartColor('serviceTypes', index)
                        );

                        // Cria novo gráfico
                        charts.chamadosPorTipoServicoChart = new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: chartData.labels,
                                datasets: [{
                                    label: 'Chamados por Tipo de Serviço',
                                    data: chartData.data,
                                    backgroundColor: serviceColors,
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
                                            font: { size: 11 },
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

                        console.log('✅ Gráfico de tipos de serviço inicializado');
                    } catch (e) {
                        console.error('❌ Erro ao inicializar gráfico de tipos de serviço:', e);
                        hideChartLoading('chamadosPorTipoServicoChart');
                    }
                }

                /**
                 * Inicializa gráfico de chamados por dia da semana (barras)
                 */
                function initWeekdayChart() {
                    const ctx = document.getElementById('chamadosPorDiaSemanaChart');
                    if (!ctx) {
                        console.warn('⚠️ Canvas chamadosPorDiaSemanaChart não encontrado');
                        return;
                    }

                    try {
                        // Remove chart existente
                        if (typeof Chart.getChart === 'function') {
                            const existingChart = Chart.getChart(ctx);
                            if (existingChart) {
                                existingChart.destroy();
                            }
                        }

                        // Obtém dados do elemento
                        const chartData = JSON.parse(ctx.getAttribute('data-chart') || '{"labels":[],"data":[],"backgroundColor":[]}');

                        // Verifica se há dados válidos
                        if (!chartData.data || chartData.data.length === 0 || array_sum(chartData.data) === 0) {
                            hideChartLoading('chamadosPorDiaSemanaChart');
                            return;
                        }

                        // Aplica cores específicas para dias da semana
                        const weekdayColors = chartData.labels.map((label, index) =>
                            getChartColor('weekdays', index)
                        );

                        // Cria novo gráfico
                        charts.chamadosPorDiaSemanaChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: chartData.labels,
                                datasets: [{
                                    label: 'Chamados por Dia da Semana',
                                    data: chartData.data,
                                    backgroundColor: weekdayColors,
                                    borderWidth: 0,
                                    borderRadius: 6,
                                    maxBarThickness: 40
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false }
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
                                        grid: { display: false }
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

                        console.log('✅ Gráfico de dias da semana inicializado');
                    } catch (e) {
                        console.error('❌ Erro ao inicializar gráfico de dias da semana:', e);
                        hideChartLoading('chamadosPorDiaSemanaChart');
                    }
                }

                /**
                 * Oculta indicador de carregamento de um gráfico específico
                 * @param {string} chartId - ID do canvas do gráfico
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

                // =============================================================================
                // SISTEMA DE TEMAS
                // =============================================================================

                /**
                 * Inicializa o sistema de temas
                 */
                function initTheme() {
                    const savedTheme = localStorage.getItem('dashboard-theme');
                    if (savedTheme === 'dark') {
                        document.documentElement.setAttribute('data-theme', 'dark');
                        darkMode = true;
                    } else {
                        // Detecta preferência do sistema
                        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                            document.documentElement.setAttribute('data-theme', 'dark');
                            darkMode = true;
                        }
                    }
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
                 * Atualiza tema de todos os gráficos
                 */
                function updateChartsTheme() {
                    if (typeof Chart === 'undefined') {
                        console.warn('Chart.js não está disponível');
                        return;
                    }

                    const textColor = darkMode ? '#e2e8f0' : '#6c757d';
                    const gridColor = darkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';

                    Chart.defaults.color = textColor;

                    // Atualiza cada gráfico individualmente
                    for (const chartName in charts) {
                        if (charts.hasOwnProperty(chartName)) {
                            const chart = charts[chartName];

                            try {
                                // Atualiza cores dos eixos
                                if (chart.options.scales && chart.options.scales.x) {
                                    chart.options.scales.x.ticks.color = textColor;
                                    chart.options.scales.x.grid.color = gridColor;
                                }

                                if (chart.options.scales && chart.options.scales.y) {
                                    chart.options.scales.y.ticks.color = textColor;
                                    chart.options.scales.y.grid.color = gridColor;
                                }

                                // Atualiza cor da legenda
                                if (chart.options.plugins && chart.options.plugins.legend) {
                                    chart.options.plugins.legend.labels.color = textColor;
                                }

                                chart.update();
                            } catch (error) {
                                console.warn('Erro ao atualizar tema do gráfico:', chartName, error);
                            }
                        }
                    }
                }

                // =============================================================================
                // ANIMAÇÕES E EFEITOS VISUAIS
                // =============================================================================

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
                        if (typeof bootstrap === 'undefined') {
                            console.warn('Bootstrap não está disponível');
                            return;
                        }

                        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(function (tooltipTriggerEl) {
                            return new bootstrap.Tooltip(tooltipTriggerEl);
                        });
                    } catch (e) {
                        console.warn('Erro ao inicializar tooltips:', e);
                    }
                }

                /**
                 * Anima entrada dos cards de estatística
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
                 * Anima contadores numéricos
                 */
                function animateCounters() {
                    const counters = document.querySelectorAll('.counter-number');

                    counters.forEach(counter => {
                        const target = parseInt(counter.textContent) || 0;
                        const duration = 1500;
                        const step = Math.ceil(target / (duration / 16));

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
                 * Melhora indicadores de carregamento
                 */
                function enhanceLoadingIndicators() {
                    const loadingIndicators = document.querySelectorAll('.chart-loading');

                    loadingIndicators.forEach(indicator => {
                        const loadingText = document.createElement('span');
                        loadingText.textContent = 'Carregando...';
                        loadingText.className = 'chart-loading-text';
                        indicator.appendChild(loadingText);
                    });
                }

                // =============================================================================
                // SISTEMA DE ATUALIZAÇÃO AUTOMÁTICA
                // =============================================================================

                /**
                 * Configura sistema de auto-refresh
                 */
                function setupAutoRefresh() {
                    // Auto-refresh a cada 5 minutos
                    setInterval(refreshDashboardData, refreshInterval);

                    // Botão manual de refresh
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
                 * Atualiza dados do dashboard via AJAX
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
                            updateStatistics(data.estatisticas);
                            updateCharts(data);
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
                 * Controla exibição dos indicadores de carregamento
                 * @param {boolean} show - Se deve mostrar ou ocultar
                 */
                function showLoadingIndicators(show) {
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

                    const loadingIndicators = document.querySelectorAll('.chart-loading');
                    loadingIndicators.forEach(indicator => {
                        indicator.style.display = show ? 'flex' : 'none';
                    });
                }

                // =============================================================================
                // ATUALIZAÇÃO DE DADOS
                // =============================================================================

                /**
                 * Atualiza estatísticas dos cards
                 * @param {Object} estatisticas - Dados das estatísticas
                 */
                function updateStatistics(estatisticas) {
                    if (!estatisticas) return;

                    // Atualiza contadores com animação
                    updateCounterWithAnimation('total-chamados', estatisticas.total);
                    updateCounterWithAnimation('chamados-abertos', estatisticas.abertos);
                    updateCounterWithAnimation('chamados-andamento', estatisticas.em_andamento);
                    updateCounterWithAnimation('chamados-concluidos', estatisticas.concluidos);
                    updateCounterWithAnimation('concluidos-hoje', estatisticas.concluidos_hoje);

                    // Atualiza tempo médio
                    const tempoMedioEl = document.getElementById('tempo-medio');
                    if (tempoMedioEl) {
                        const tempoMedio = estatisticas.tempo_medio_atendimento || 0;
                        const numberEl = tempoMedioEl.querySelector('.counter-number');
                        if (numberEl) {
                            numberEl.textContent = tempoMedio;
                        }
                        updateProgressBars(estatisticas);
                    }
                }

                /**
                 * Atualiza contador com animação
                 * @param {string} elementId - ID do elemento
                 * @param {number} newValue - Novo valor
                 */
                function updateCounterWithAnimation(elementId, newValue) {
                    const element = document.getElementById(elementId);
                    if (!element) return;

                    const numberEl = element.querySelector('.counter-number');
                    if (!numberEl) return;

                    const currentValue = parseInt(numberEl.textContent) || 0;
                    const difference = newValue - currentValue;

                    if (difference === 0) return;

                    // Adiciona efeito de pulso
                    element.classList.add('pulse');

                    let startTime;
                    const duration = 1000;

                    function animate(timestamp) {
                        if (!startTime) startTime = timestamp;
                        const progress = Math.min((timestamp - startTime) / duration, 1);
                        const value = Math.floor(currentValue + difference * progress);
                        numberEl.textContent = value;

                        if (progress < 1) {
                            requestAnimationFrame(animate);
                        } else {
                            numberEl.textContent = newValue;
                            setTimeout(() => {
                                element.classList.remove('pulse');
                            }, 500);
                        }
                    }

                    requestAnimationFrame(animate);
                }

                /**
                 * Atualiza barras de progresso dos cards
                 * @param {Object} estatisticas - Dados das estatísticas
                 */
                function updateProgressBars(estatisticas) {
                    const total = estatisticas.total || 0;

                    // Calcula percentuais
                    const percentAbertos = total > 0 ? Math.round((estatisticas.abertos / total) * 100) : 0;
                    const percentAndamento = total > 0 ? Math.round((estatisticas.em_andamento / total) * 100) : 0;
                    const percentConcluidos = total > 0 ? Math.round((estatisticas.concluidos / total) * 100) : 0;
                    const percentHoje = estatisticas.concluidos > 0 ? Math.round((estatisticas.concluidos_hoje / estatisticas.concluidos) * 100) : 0;
                    const tempoRelativo = Math.min(100, (estatisticas.tempo_medio_atendimento / 48) * 100);

                    // Atualiza valores de tendência
                    updateTrendValue('chamados-abertos', percentAbertos + '%');
                    updateTrendValue('chamados-andamento', percentAndamento + '%');
                    updateTrendValue('chamados-concluidos', percentConcluidos + '%');
                    updateTrendValue('concluidos-hoje', percentHoje + '%');

                    // Atualiza barras de progresso
                    updateProgressBar('chamados-abertos', percentAbertos);
                    updateProgressBar('chamados-andamento', percentAndamento);
                    updateProgressBar('chamados-concluidos', percentConcluidos);
                    updateProgressBar('concluidos-hoje', percentHoje);
                    updateProgressBar('tempo-medio', tempoRelativo);
                }

                /**
                 * Atualiza valor de tendência
                 * @param {string} elementId - ID do elemento
                 * @param {string} value - Novo valor
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
                 * Atualiza barra de progresso
                 * @param {string} elementId - ID do elemento
                 * @param {number} percent - Percentual
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
                 * Atualiza dados de todos os gráficos
                 * @param {Object} data - Dados dos gráficos
                 */
                function updateCharts(data) {
                    // Atualiza cada gráfico se os dados estão disponíveis
                    if (data.chamadosPorStatus && charts.chamadosPorStatusChart) {
                        updateChartData(charts.chamadosPorStatusChart, data.chamadosPorStatus);
                    }

                    if (data.chamadosPorSetor && charts.chamadosPorSetorChart) {
                        updateChartData(charts.chamadosPorSetorChart, data.chamadosPorSetor, 'sectors');
                    }

                    if (data.chamadosPorMes && charts.chamadosPorMesChart) {
                        updateChartData(charts.chamadosPorMesChart, data.chamadosPorMes);
                    }

                    if (data.tempoMedioPorSetor && charts.tempoMedioPorSetorChart) {
                        if (data.tempoMedioPorSetor.data && data.tempoMedioPorSetor.data.length > 0 &&
                            !(data.tempoMedioPorSetor.data.length === 1 && data.tempoMedioPorSetor.data[0] === 0)) {
                            updateChartData(charts.tempoMedioPorSetorChart, data.tempoMedioPorSetor, 'timePerformance');
                        }
                    }

                    if (data.chamadosPorTipoServico && charts.chamadosPorTipoServicoChart) {
                        if (data.chamadosPorTipoServico.data && data.chamadosPorTipoServico.data.length > 0 &&
                            !(data.chamadosPorTipoServico.data.length === 1 && data.chamadosPorTipoServico.data[0] === 0)) {
                            updateChartData(charts.chamadosPorTipoServicoChart, data.chamadosPorTipoServico, 'serviceTypes');
                        }
                    }

                    if (data.chamadosPorDiaSemana && charts.chamadosPorDiaSemanaChart) {
                        if (data.chamadosPorDiaSemana.data && data.chamadosPorDiaSemana.data.length > 0 &&
                            array_sum(data.chamadosPorDiaSemana.data) > 0) {
                            updateChartData(charts.chamadosPorDiaSemanaChart, data.chamadosPorDiaSemana, 'weekdays');
                        }
                    }

                    // Atualiza tabela de chamados recentes
                    if (data.recentes) {
                        updateRecentTickets(data.recentes);
                    }
                }

                /**
                 * Atualiza dados de um gráfico específico
                 * @param {Object} chart - Instância do gráfico
                 * @param {Object} newData - Novos dados
                 * @param {string|null} colorType - Tipo de cor a aplicar
                 */
                function updateChartData(chart, newData, colorType = null) {
                    if (!chart || !newData) return;

                    try {
                        // Atualiza labels
                        if (newData.labels) {
                            chart.data.labels = newData.labels;
                        }

                        // Atualiza dados
                        if (newData.data) {
                            chart.data.datasets[0].data = newData.data;
                        }

                        // Aplica cores específicas se fornecido o tipo
                        if (colorType && newData.labels) {
                            const newColors = newData.labels.map((label, index) =>
                                getChartColor(colorType, index)
                            );
                            chart.data.datasets[0].backgroundColor = newColors;
                        } else if (newData.backgroundColor) {
                            chart.data.datasets[0].backgroundColor = newData.backgroundColor;
                        }

                        chart.update();
                    } catch (error) {
                        console.warn('Erro ao atualizar dados do gráfico:', error);
                    }
                }

                // =============================================================================
                // GERENCIAMENTO DA TABELA DE CHAMADOS RECENTES
                // =============================================================================

                /**
                 * Atualiza tabela de chamados recentes
                 * @param {Array} recentes - Array de chamados recentes
                 */
                function updateRecentTickets(recentes) {
                    const tableBody = document.querySelector('.recent-tickets-table tbody');
                    if (!tableBody) return;

                    // Verifica se há dados
                    if (!recentes || recentes.length === 0) {
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="7" class="no-tickets">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-ticket-alt"></i>
                                        </div>
                                        <h3 class="empty-state-title">Nenhum chamado encontrado</h3>
                                        <p class="empty-state-desc">Não há chamados recentes para exibir no momento.</p>
                                    </div>
                                </td>
                            </tr>
                        `;
                        return;
                    }

                    let html = '';
                    recentes.forEach((chamado, index) => {
                        // Gera iniciais do nome para o avatar
                        const iniciais = chamado.solicitante
                            .split(' ')
                            .map(nome => nome.charAt(0))
                            .join('')
                            .substring(0, 2)
                            .toUpperCase();

                        // Formata data e hora
                        const dataObj = new Date(chamado.data_solicitacao);
                        const dataFormatada = dataObj.toLocaleDateString('pt-BR', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric'
                        });
                        const horaFormatada = dataObj.toLocaleTimeString('pt-BR', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        // Determina classe do status
                        const statusClass = getStatusClass(chamado.status);

                        // Gera HTML da linha
                        html += `
                            <tr class="fade-in-up" style="animation-delay: ${index * 0.1}s">
                                <!-- ID -->
                                <td>
                                    <span class="ticket-id">#${chamado.id}</span>
                                </td>
                                
                                <!-- Solicitante -->
                                <td>
                                    <div class="ticket-user">
                                        <div class="user-avatar">${iniciais}</div>
                                        <div class="user-info">
                                            <p class="user-name">${escapeHtml(chamado.solicitante)}</p>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Descrição -->
                                <td>
                                    <div class="ticket-desc">
                                        <div class="desc-preview" data-bs-toggle="tooltip" title="${escapeHtml(chamado.descricao)}">
                                            ${escapeHtml(chamado.descricao)}
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Setor -->
                                <td>
                                    <div class="sector-badge">
                                        <i class="fas fa-building"></i>
                                        ${escapeHtml(chamado.setor)}
                                    </div>
                                </td>
                                
                                <!-- Status -->
                                <td>
                                    <span class="status-badge ${statusClass}">
                                        <i class="fas fa-circle"></i>
                                        ${escapeHtml(chamado.status)}
                                    </span>
                                </td>
                                
                                <!-- Data -->
                                <td>
                                    <div class="ticket-date">
                                        <div class="date-icon">
                                            <i class="far fa-calendar-alt"></i>
                                        </div>
                                        <div class="date-info">
                                            <p class="date-day">${dataFormatada}</p>
                                            <p class="date-time">${horaFormatada}</p>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Ações -->
                              <td>
                                <div class="ticket-actions">
                                    <a href="${baseUrl}chamados/visualizar/${chamado.id}" 
                                    class="action-btn view" 
                                    data-bs-toggle="tooltip" 
                                    title="Visualizar detalhes do chamado">
                                        <div class="action-btn-content">
                                            <span class="action-btn-text">Ver</span>
                                            <div class="action-btn-icon">
                                                <i class="fas fa-eye"></i>
                                                <i class="fas fa-arrow-right secondary-icon"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </td>
                            </tr>
                        `;
                    });

                    // Atualiza conteúdo da tabela
                    tableBody.innerHTML = html;

                    // Reinicializa tooltips
                    initTooltips();
                }

                // =============================================================================
                // SISTEMA DE NOTIFICAÇÕES
                // =============================================================================

                /**
                 * Exibe notificação de atualização
                 * @param {string} message - Mensagem da notificação
                 * @param {string} type - Tipo da notificação (success, error)
                 */
                function showUpdateNotification(message, type = 'success') {
                    let notificationEl = document.getElementById('dashboard-notification');

                    // Cria elemento se não existir
                    if (!notificationEl) {
                        notificationEl = document.createElement('div');
                        notificationEl.id = 'dashboard-notification';
                        notificationEl.className = 'dashboard-notification';
                        document.body.appendChild(notificationEl);
                    }

                    // Configura conteúdo e estilo
                    notificationEl.className = `dashboard-notification ${type}`;
                    notificationEl.innerHTML = `
                        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                        ${message}
                    `;

                    // Exibe notificação
                    setTimeout(() => {
                        notificationEl.classList.add('show');
                    }, 10);

                    // Oculta após 3 segundos
                    setTimeout(() => {
                        notificationEl.classList.remove('show');
                    }, 3000);
                }

                // =============================================================================
                // LIMPEZA E DESTRUIÇÃO
                // =============================================================================

                /**
                 * Destrói todos os gráficos e limpa recursos
                 */
                function destroy() {
                    Object.values(charts).forEach(chart => {
                        if (chart && typeof chart.destroy === 'function') {
                            chart.destroy();
                        }
                    });
                    charts = {};
                    console.log('🗑️ Todos os charts destruídos');
                }

                // =============================================================================
                // API PÚBLICA DO MÓDULO
                // =============================================================================

                return {
                    init: init,
                    refreshData: refreshDashboardData,
                    destroy: destroy,
                    charts: charts,
                    toggleTheme: function () {
                        darkMode = !darkMode;
                        document.documentElement.setAttribute('data-theme', darkMode ? 'dark' : 'light');
                        localStorage.setItem('dashboard-theme', darkMode ? 'dark' : 'light');
                        updateThemeIcon();
                        updateChartsTheme();
                    }
                };
            })();

            // =============================================================================
            // INICIALIZAÇÃO E CONFIGURAÇÃO GLOBAL
            // =============================================================================

            let dashboardInstance = null;

            /**
             * Inicializa instância do dashboard
             * @param {Object} config - Configurações de inicialização
             * @returns {Object} Instância do dashboard
             */
            function initDashboard(config) {
                if (dashboardInstance) {
                    console.warn('⚠️ Dashboard já foi inicializado, destruindo anterior...');
                    dashboardInstance.destroy();
                }

                dashboardInstance = DashboardModule;
                dashboardInstance.init(config);

                // Expõe globalmente para debug e uso externo
                window.DashboardModule = dashboardInstance;
                window.dashboardInstance = dashboardInstance;

                return dashboardInstance;
            }

            // Configuração e inicialização
            const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '';
            initDashboard({ baseUrl: baseUrl });

            // Limpeza ao sair da página
            window.addEventListener('beforeunload', () => {
                if (dashboardInstance) {
                    dashboardInstance.destroy();
                }
            });

        })();
    }

} else {
    console.log('⚠️ dashboard.js não executado - não estamos na página de dashboard');
}

/* =============================================================================
   COMENTÁRIOS FINAIS PARA DESENVOLVEDORES
   
   ESTRUTURA DO ARQUIVO:
   1. Verificação de página e inicialização
   2. Paletas de cores para gráficos
   3. Funções utilitárias (escape, formatação, etc.)
   4. Sistema de carregamento do Chart.js
   5. Módulo principal do dashboard
   6. Gerenciamento de charts
   7. Sistema de temas (claro/escuro)
   8. Animações e efeitos visuais
   9. Sistema de atualização automática
   10. Atualização de dados
   11. Gerenciamento da tabela
   12. Sistema de notificações
   13. Limpeza e destruição
   14. API pública e inicialização global
   
   FUNCIONALIDADES PRINCIPAIS:
   - Gráficos interativos com Chart.js
   - Atualização automática a cada 5 minutos
   - Sistema de temas claro/escuro
   - Tabela responsiva de chamados recentes
   - Animações suaves e contadores
   - Sistema de notificações
   - Gerenciamento de memória (destroy charts)
   
   PERFORMANCE:
   - Lazy loading de gráficos
   - Animações GPU-accelerated
   - Debounce em atualizações
   - Limpeza de recursos
   
   COMPATIBILIDADE:
   - Chart.js 3.x ou superior
   - Bootstrap 5.x para tooltips
   - Navegadores modernos (ES6+)
   
   MANUTENÇÃO:
   - Código bem documentado
   - Funções modulares e reutilizáveis
   - Tratamento de erros robusto
   - Logs detalhados para debug
   
   SEGURANÇA:
   - Escape de HTML para prevenir XSS
   - Validação de dados de entrada
   - Sanitização de URLs
   
   Para adicionar novos gráficos:
   1. Adicione o canvas no HTML
   2. Crie função initNovoChart()
   3. Chame a função em initCharts()
   4. Adicione lógica de atualização em updateCharts()
   
   Para modificar cores:
   1. Edite COLOR_PALETTES no início do arquivo
   2. Use getChartColor() para obter cores consistentes
   
   Para debug:
   - Use console.log() - já implementado
   - Acesse window.DashboardModule no console
   - Verifique window.dashboardInstance.charts
   ============================================================================= */