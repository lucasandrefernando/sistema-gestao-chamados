/**
 * setores-detalhes.js - Funcionalidades para a página de detalhes do setor
 * Este arquivo contém todas as funções necessárias para o funcionamento
 * da página de detalhes do setor, incluindo gráficos, filtros e navegação.
 */

// Variáveis globais para armazenar as instâncias dos gráficos
let statusChart = null;
let monthlyChart = null;

/**
 * Inicializa todas as funcionalidades quando o DOM estiver carregado
 */
document.addEventListener('DOMContentLoaded', function () {
    console.log('Script setores-detalhes.js inicializado com sucesso.');

    // Inicializa as abas do Bootstrap manualmente para garantir funcionamento
    initTabs();

    // Inicializa a busca na tabela de chamados
    initChamadosSearch();

    // Inicializa o filtro de chamados (mostrar todos/apenas ativos)
    initChamadosFilter();

    // Gera cores para os avatares de usuários baseado no nome
    generateAvatarColors();

    // Inicializa os gráficos se Chart.js estiver disponível
    if (typeof Chart !== 'undefined') {
        initCharts();
    } else {
        console.error('Chart.js não está disponível');
        // Tenta carregar Chart.js dinamicamente
        loadChartJs();
    }

    // Adiciona efeitos de hover aos cards para melhorar a experiência do usuário
    initCardHoverEffects();

    // Inicializa contadores em tempo real (atualização periódica)
    initRealTimeCounters();
});

/**
 * Inicializa as abas do Bootstrap manualmente
 * Isso garante que as abas funcionem mesmo se houver problemas com o Bootstrap
 */
function initTabs() {
    // Seleciona todos os elementos que têm o atributo data-bs-toggle="tab"
    const tabEls = document.querySelectorAll('[data-bs-toggle="tab"]');

    // Para cada elemento de aba, adiciona o evento de clique
    tabEls.forEach(tabEl => {
        // Verifica se o Bootstrap está disponível para usar a classe Tab
        if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
            // Cria uma nova instância de Tab para cada elemento
            new bootstrap.Tab(tabEl);
        }

        // Adiciona evento de clique para garantir que a aba seja mostrada
        tabEl.addEventListener('click', function (event) {
            event.preventDefault();

            // Obtém o alvo da aba (o conteúdo que deve ser mostrado)
            const tabTarget = this.getAttribute('data-bs-target');
            const tabContent = document.querySelector(tabTarget);

            if (!tabContent) {
                console.error('Conteúdo da aba não encontrado:', tabTarget);
                return;
            }

            // Remove a classe active de todos os links de abas
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
                link.setAttribute('aria-selected', 'false');
            });

            // Esconde todos os painéis de conteúdo
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });

            // Ativa a aba atual
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');

            // Mostra o conteúdo da aba atual
            tabContent.classList.add('show', 'active');

            console.log('Aba ativada:', tabTarget);
        });
    });

    // Ativa a primeira aba por padrão
    const firstTab = document.querySelector('.nav-link');
    if (firstTab) {
        console.log('Ativando a primeira aba por padrão');
        firstTab.click();
    }
}

/**
 * Inicializa efeitos de hover para os cards
 * Adiciona animações suaves quando o usuário passa o mouse sobre os cards
 */
function initCardHoverEffects() {
    // Seleciona todos os cards que devem ter o efeito de hover
    const cards = document.querySelectorAll('.stat-card, .content-card, .user-card, .info-card, .card');

    cards.forEach(card => {
        // Quando o mouse entra no card
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 1rem 2rem rgba(0, 0, 0, 0.1)';
            this.style.transition = 'all 0.3s ease';
        });

        // Quando o mouse sai do card
        card.addEventListener('mouseleave', function () {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });
}

/**
 * Inicializa contadores em tempo real
 * Isso permite que os contadores sejam atualizados quando um chamado muda de status
 */
function initRealTimeCounters() {
    // Verifica se há um elemento para atualizar os contadores
    const updateCountersBtn = document.getElementById('updateCounters');
    if (updateCountersBtn) {
        updateCountersBtn.addEventListener('click', function () {
            // Faz uma requisição AJAX para obter os dados atualizados
            fetch(window.location.href + '?ajax=1')
                .then(response => response.json())
                .then(data => {
                    // Atualiza os contadores
                    if (data.estatisticas) {
                        updateStatCards(data.estatisticas);
                        // Reinicializa os gráficos
                        initCharts();
                    }
                })
                .catch(error => console.error('Erro ao atualizar contadores:', error));
        });
    }

    // Adiciona um listener para atualizar os contadores a cada 5 minutos
    setInterval(function () {
        if (updateCountersBtn) {
            updateCountersBtn.click();
        }
    }, 300000); // 5 minutos
}

/**
 * Atualiza os cards de estatísticas com novos dados
 * @param {Object} estatisticas - Objeto contendo as estatísticas atualizadas
 */
function updateStatCards(estatisticas) {
    // Atualiza o contador de chamados abertos
    const abertosEl = document.querySelector('.stat-card:nth-child(1) .stat-card-title');
    if (abertosEl && estatisticas.chamados_abertos !== undefined) {
        abertosEl.textContent = estatisticas.chamados_abertos;
    }

    // Atualiza o contador de chamados em atendimento
    const emAtendimentoEl = document.querySelector('.stat-card:nth-child(2) .stat-card-title');
    if (emAtendimentoEl && estatisticas.chamados_em_atendimento !== undefined) {
        emAtendimentoEl.textContent = estatisticas.chamados_em_atendimento;
    }

    // Atualiza o contador de chamados concluídos
    const concluidosEl = document.querySelector('.stat-card:nth-child(3) .stat-card-title');
    if (concluidosEl && estatisticas.chamados_concluidos !== undefined) {
        concluidosEl.textContent = estatisticas.chamados_concluidos;
    }

    // Atualiza o contador de total de chamados
    const totalEl = document.querySelector('.stat-card:nth-child(4) .stat-card-title');
    if (totalEl && estatisticas.total_chamados !== undefined) {
        totalEl.textContent = estatisticas.total_chamados;
    }

    // Atualiza as barras de progresso
    updateProgressBars(estatisticas);
}

/**
 * Atualiza as barras de progresso nos cards de estatísticas
 * @param {Object} estatisticas - Objeto contendo as estatísticas atualizadas
 */
function updateProgressBars(estatisticas) {
    const total = estatisticas.total_chamados || 0;

    // Atualiza a barra de progresso de chamados abertos
    const abertosBar = document.querySelector('.stat-card:nth-child(1) .progress-bar');
    if (abertosBar && estatisticas.chamados_abertos !== undefined) {
        const percentAbertos = total > 0 ? (estatisticas.chamados_abertos / total) * 100 : 0;
        abertosBar.style.width = percentAbertos + '%';
        abertosBar.setAttribute('aria-valuenow', percentAbertos);
    }

    // Atualiza a barra de progresso de chamados em atendimento
    const emAtendimentoBar = document.querySelector('.stat-card:nth-child(2) .progress-bar');
    if (emAtendimentoBar && estatisticas.chamados_em_atendimento !== undefined) {
        const percentEmAtendimento = total > 0 ? (estatisticas.chamados_em_atendimento / total) * 100 : 0;
        emAtendimentoBar.style.width = percentEmAtendimento + '%';
        emAtendimentoBar.setAttribute('aria-valuenow', percentEmAtendimento);
    }

    // Atualiza a barra de progresso de chamados concluídos
    const concluidosBar = document.querySelector('.stat-card:nth-child(3) .progress-bar');
    if (concluidosBar && estatisticas.chamados_concluidos !== undefined) {
        const percentConcluidos = total > 0 ? (estatisticas.chamados_concluidos / total) * 100 : 0;
        concluidosBar.style.width = percentConcluidos + '%';
        concluidosBar.setAttribute('aria-valuenow', percentConcluidos);
    }
}

/**
 * Carrega Chart.js dinamicamente se não estiver disponível
 * Isso garante que os gráficos funcionem mesmo se Chart.js não estiver carregado
 */
function loadChartJs() {
    console.log('Tentando carregar Chart.js dinamicamente');

    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/chart.js';

    script.onload = function () {
        console.log('Chart.js carregado dinamicamente com sucesso');
        initCharts();
    };

    script.onerror = function () {
        console.error('Falha ao carregar Chart.js dinamicamente');
        // Tenta carregar de um CDN alternativo
        const alternativeScript = document.createElement('script');
        alternativeScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js';

        alternativeScript.onload = function () {
            console.log('Chart.js carregado do CDN alternativo');
            initCharts();
        };

        alternativeScript.onerror = function () {
            console.error('Falha ao carregar Chart.js de ambos os CDNs');
        };

        document.head.appendChild(alternativeScript);
    };

    document.head.appendChild(script);
}

/**
 * Inicializa a busca na tabela de chamados
 * Permite filtrar os chamados conforme o usuário digita na caixa de busca
 */
function initChamadosSearch() {
    const searchInput = document.getElementById('chamadosSearch');
    const table = document.getElementById('chamadosTable');
    const noResults = document.getElementById('noResults');

    if (!searchInput || !table) {
        console.log('Elementos de busca não encontrados');
        return;
    }

    const rows = table.querySelectorAll('tbody tr');

    searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase().trim();
        let visibleCount = 0;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Mostra/esconde a mensagem de "nenhum resultado"
        if (visibleCount === 0 && searchTerm !== '') {
            table.style.display = 'none';
            if (noResults) noResults.style.display = 'block';
        } else {
            table.style.display = '';
            if (noResults) noResults.style.display = 'none';
        }
    });
}

/**
 * Inicializa o filtro de chamados (mostrar todos/apenas ativos)
 * Permite mostrar ou ocultar chamados concluídos e cancelados
 */
function initChamadosFilter() {
    const checkbox = document.getElementById('mostrarTodosChamados');
    if (!checkbox) {
        console.log('Checkbox de filtro não encontrado');
        return;
    }

    // Por padrão, mostrar apenas chamados ativos (status 1 e 2)
    filterChamados(false);

    checkbox.addEventListener('change', function () {
        const showAll = this.checked;
        filterChamados(showAll);
    });
}

/**
 * Filtra os chamados na tabela com base no status
 * @param {boolean} showAll - Se true, mostra todos os chamados; se false, mostra apenas os ativos
 */
function filterChamados(showAll) {
    const rows = document.querySelectorAll('#chamadosTable tbody tr');
    let visibleCount = 0;

    rows.forEach(row => {
        const status = row.getAttribute('data-status');
        if (showAll || !status || status === '1' || status === '2' || status === 'aberto' || status === 'em_andamento') {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Atualizar a mensagem de "nenhum resultado"
    const table = document.getElementById('chamadosTable');
    const noResults = document.getElementById('noResults');
    if (visibleCount === 0) {
        if (table) table.style.display = 'none';
        if (noResults) noResults.style.display = 'block';
    } else {
        if (table) table.style.display = '';
        if (noResults) noResults.style.display = 'none';
    }
}

/**
 * Gera cores para os avatares de usuários baseado no nome
 * Isso garante que cada usuário tenha uma cor consistente
 */
function generateAvatarColors() {
    const avatars = document.querySelectorAll('.user-avatar');
    const colors = [
        '#4361ee', '#3a0ca3', '#7209b7', '#f72585',
        '#4cc9a0', '#4895ef', '#560bad', '#b5179e',
        '#e63946', '#fb8500', '#ffb703', '#023047'
    ];

    avatars.forEach((avatar) => {
        const name = avatar.getAttribute('data-name');
        if (name) {
            let hash = 0;
            for (let i = 0; i < name.length; i++) {
                hash = name.charCodeAt(i) + ((hash << 5) - hash);
            }
            const colorIndex = Math.abs(hash) % colors.length;
            avatar.style.backgroundColor = colors[colorIndex];
        }
    });
}

/**
 * Inicializa todos os gráficos da página
 */
function initCharts() {
    // Destroi gráficos existentes antes de criar novos
    destroyCharts();

    // Inicializa os gráficos
    initStatusChart();
    initMonthlyChart();
}

/**
 * Destroi gráficos existentes para evitar duplicação
 */
function destroyCharts() {
    // Destroi o gráfico de status se existir
    if (statusChart) {
        statusChart.destroy();
        statusChart = null;
    }

    // Destroi o gráfico mensal se existir
    if (monthlyChart) {
        monthlyChart.destroy();
        monthlyChart = null;
    }
}

/**
 * Inicializa o gráfico de status (pizza/donut)
 */
function initStatusChart() {
    const statusChartEl = document.getElementById('statusChart');
    if (!statusChartEl) {
        console.log('Elemento do gráfico de status não encontrado');
        return;
    }

    try {
        console.log('Iniciando criação do gráfico de status');

        // Primeiro, tenta obter dados do elemento JSON
        const statusDataEl = document.getElementById('statusChartData');
        if (statusDataEl) {
            try {
                console.log('Conteúdo do elemento statusChartData:', statusDataEl.textContent);
                const statusData = JSON.parse(statusDataEl.textContent);
                console.log('Dados parseados do statusChartData:', statusData);

                if (statusData.labels && statusData.labels.length > 0 &&
                    statusData.data && statusData.data.length > 0) {
                    console.log('Usando dados do elemento JSON para o gráfico de status');
                    createStatusChart(statusData.labels, statusData.data);
                    return;
                } else {
                    console.log('Dados do elemento JSON estão vazios ou incompletos');
                }
            } catch (e) {
                console.error('Erro ao parsear dados do elemento JSON:', e);
            }
        } else {
            console.log('Elemento statusChartData não encontrado');
        }

        // Se não conseguir obter dados do elemento JSON, tenta da legenda
        const labels = getLabelsFromLegend('statusChart');
        const data = getDataFromLegend('statusChart');

        console.log('Dados obtidos da legenda:', { labels, data });

        if (labels.length === 0 || data.length === 0) {
            console.log('Dados insuficientes para o gráfico de status');
            return;
        }

        createStatusChart(labels, data);
    } catch (e) {
        console.error('Erro ao criar gráfico de status:', e);
    }
}

/**
 * Cria o gráfico de status com os dados fornecidos
 * @param {Array} labels - Array de rótulos para o gráfico
 * @param {Array} data - Array de dados para o gráfico
 */
function createStatusChart(labels, data) {
    const statusChartEl = document.getElementById('statusChart');
    console.log('Criando gráfico de status com dados:', { labels, data });

    // Cores para o gráfico de status
    const colors = [
        'rgba(220, 53, 69, 0.8)',   // Vermelho (aberto)
        'rgba(255, 193, 7, 0.8)',   // Amarelo (em andamento)
        'rgba(23, 162, 184, 0.8)',  // Azul (pausado)
        'rgba(40, 167, 69, 0.8)',   // Verde (concluído)
        'rgba(108, 117, 125, 0.8)', // Cinza (cancelado)
        'rgba(0, 123, 255, 0.8)'    // Azul primário (outros)
    ];

    try {
        statusChart = new Chart(statusChartEl, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors.slice(0, data.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        display: false
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
                        },
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        bodySpacing: 6,
                        caretSize: 8,
                        cornerRadius: 6
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
        console.log('Gráfico de status criado com sucesso');
    } catch (e) {
        console.error('Erro ao criar instância do gráfico de status:', e);
    }
}

/**
 * Inicializa o gráfico de chamados por mês (barras)
 */
function initMonthlyChart() {
    const monthlyChartEl = document.getElementById('monthlyChart');
    if (!monthlyChartEl) {
        console.log('Elemento do gráfico mensal não encontrado');
        return;
    }

    try {
        // Tenta obter dados do elemento script
        const monthlyDataScript = document.getElementById('monthlyChartData');
        let labels = [];
        let data = [];

        if (monthlyDataScript) {
            try {
                const monthlyChartData = JSON.parse(monthlyDataScript.textContent);
                labels = monthlyChartData.labels;
                data = monthlyChartData.data;
                console.log('Dados do gráfico mensal:', { labels, data });
            } catch (e) {
                console.error('Erro ao parsear dados do gráfico mensal:', e);
            }
        }

        // Se não tiver dados, usa dados de exemplo
        if (labels.length === 0 || data.length === 0) {
            console.log('Usando dados de exemplo para o gráfico mensal');
            labels = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'];
            data = [5, 10, 15, 8, 12, 9];
        }

        monthlyChart = new Chart(monthlyChartEl, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Chamados',
                    data: data,
                    backgroundColor: 'rgba(67, 97, 238, 0.8)',
                    borderColor: 'rgba(67, 97, 238, 1)',
                    borderWidth: 1,
                    borderRadius: 6,
                    barThickness: 'flex',
                    maxBarThickness: 40,
                    hoverBackgroundColor: 'rgba(67, 97, 238, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
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
                                return `Chamados: ${value}`;
                            }
                        },
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        bodySpacing: 6,
                        caretSize: 8,
                        cornerRadius: 6
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        });
    } catch (e) {
        console.error('Erro ao criar gráfico mensal:', e);
    }
}

/**
 * Obtém labels da legenda para um gráfico
 * @param {string} chartId - ID do elemento canvas do gráfico
 * @return {Array} Array de labels extraídos da legenda
 */
function getLabelsFromLegend(chartId) {
    const labels = [];
    const chartContainer = document.getElementById(chartId).closest('.card-body');
    if (!chartContainer) return labels;

    const legendItems = chartContainer.querySelectorAll('.legend-item');

    console.log(`Encontrados ${legendItems.length} itens de legenda para ${chartId}`);

    legendItems.forEach(item => {
        const text = item.textContent.trim();
        console.log(`Texto da legenda: "${text}"`);
        const match = text.match(/(.+)\s+\((\d+)\)/);
        if (match) {
            labels.push(match[1]);
            console.log(`Label extraído: "${match[1]}"`);
        } else {
            console.log(`Não foi possível extrair label do texto: "${text}"`);
        }
    });

    console.log(`Labels extraídos para ${chartId}:`, labels);
    return labels;
}

/**
 * Obtém dados da legenda para um gráfico
 * @param {string} chartId - ID do elemento canvas do gráfico
 * @return {Array} Array de valores extraídos da legenda
 */
function getDataFromLegend(chartId) {
    const data = [];
    const chartContainer = document.getElementById(chartId).closest('.card-body');
    if (!chartContainer) return data;

    const legendItems = chartContainer.querySelectorAll('.legend-item');

    console.log(`Obtendo dados para ${chartId} de ${legendItems.length} itens`);

    legendItems.forEach(item => {
        const text = item.textContent.trim();
        console.log(`Texto para extração de dados: "${text}"`);
        const match = text.match(/(.+)\s+\((\d+)\)/);
        if (match) {
            data.push(parseInt(match[2]));
            console.log(`Valor extraído: ${match[2]}`);
        } else {
            console.log(`Não foi possível extrair valor do texto: "${text}"`);
        }
    });

    console.log(`Dados extraídos para ${chartId}:`, data);
    return data;
}

/**
 * Função auxiliar para verificar se o Bootstrap está disponível
 * @return {boolean} True se o Bootstrap estiver disponível, false caso contrário
 */
function isBootstrapAvailable() {
    return typeof bootstrap !== 'undefined';
}

/**
 * Função auxiliar para verificar se o jQuery está disponível
 * @return {boolean} True se o jQuery estiver disponível, false caso contrário
 */
function isjQueryAvailable() {
    return typeof jQuery !== 'undefined';
}

/**
 * Função para inicializar as abas usando jQuery se disponível
 * Esta é uma alternativa caso o Bootstrap nativo não funcione
 */
function initTabsWithjQuery() {
    if (isjQueryAvailable()) {
        jQuery(document).ready(function ($) {
            $('#setorTabs .nav-link').on('click', function (e) {
                e.preventDefault();
                $(this).tab('show');
            });

            // Ativa a primeira aba por padrão
            $('#setorTabs .nav-link:first').tab('show');
        });
    }
}

/**
 * Função para carregar o Bootstrap se não estiver disponível
 */
function loadBootstrap() {
    if (!isBootstrapAvailable()) {
        console.log('Bootstrap não encontrado, tentando carregar dinamicamente');

        // Carrega o CSS do Bootstrap
        const cssLink = document.createElement('link');
        cssLink.rel = 'stylesheet';
        cssLink.href = 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css';
        document.head.appendChild(cssLink);

        // Carrega o JS do Bootstrap
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js';

        script.onload = function () {
            console.log('Bootstrap carregado com sucesso');
            // Reinicializa as abas
            initTabs();
        };

        script.onerror = function () {
            console.error('Falha ao carregar Bootstrap');
            // Tenta inicializar com jQuery como fallback
            initTabsWithjQuery();
        };

        document.head.appendChild(script);
    }
}

// Verifica se o Bootstrap está disponível e carrega se necessário
if (!isBootstrapAvailable()) {
    loadBootstrap();
}