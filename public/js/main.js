// Função para verificar se um elemento está no viewport
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

// Funções principais quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function () {
    // Auto-close alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            }
        }, 5000);
    });

    // Inicializa tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Inicializa popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Toggle sidebar em dispositivos móveis
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');

    if (sidebarToggle && sidebar && sidebarOverlay) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
        });

        sidebarOverlay.addEventListener('click', function () {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        });
    }

    // Toggle modo escuro
    const themeToggle = document.getElementById('theme-toggle');
    const htmlElement = document.documentElement;

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            htmlElement.classList.toggle('dark-mode');

            // Salvar preferência em cookie
            const isDarkMode = htmlElement.classList.contains('dark-mode');
            document.cookie = `dark_mode=${isDarkMode}; path=/; max-age=31536000`; // 1 ano

            // Atualizar ícone
            const icon = themeToggle.querySelector('i');
            if (icon) {
                if (isDarkMode) {
                    icon.classList.remove('fa-moon');
                    icon.classList.add('fa-sun');
                } else {
                    icon.classList.remove('fa-sun');
                    icon.classList.add('fa-moon');
                }
            }
        });

        // Definir ícone correto ao carregar
        const isDarkMode = htmlElement.classList.contains('dark-mode');
        const icon = themeToggle.querySelector('i');
        if (icon && isDarkMode) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        }
    }

    // NOVAS FUNCIONALIDADES PARA GRÁFICOS E DASHBOARD

    // Inicializa o filtro de ano para gráficos
    const anoFiltro = document.getElementById('anoFiltro');
    if (anoFiltro) {
        anoFiltro.addEventListener('change', function () {
            const form = document.getElementById('formAnoFiltro');
            if (form) {
                form.submit();
            }
        });
    }

    // Inicializa gráficos se existirem no dashboard
    initCharts();

    // Trunca textos longos e adiciona tooltips
    const textTruncate = document.querySelectorAll('.text-truncate[data-bs-toggle="tooltip"]');
    textTruncate.forEach(function (element) {
        new bootstrap.Tooltip(element);
    });

    // Inicializa filtros avançados
    const filtrosCollapse = document.getElementById('filtrosCollapse');
    if (filtrosCollapse) {
        const filtrosAtivos = document.querySelectorAll('.filtro-ativo');
        if (filtrosAtivos.length > 0) {
            const bsCollapse = new bootstrap.Collapse(filtrosCollapse, {
                toggle: false
            });
            bsCollapse.show();
        }
    }

    // Atualiza contadores de filtros ativos
    updateFilterCount();
});

// Função para inicializar gráficos
function initCharts() {
    // Gráfico de Status
    const statusChart = document.getElementById('statusChart');
    if (statusChart) {
        const ctx = statusChart.getContext('2d');
        const dashboardData = document.getElementById('dashboard-data');

        if (dashboardData) {
            try {
                const statusData = JSON.parse(dashboardData.getAttribute('data-status') || '{}');

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: statusData.labels || [],
                        datasets: [{
                            data: statusData.data || [],
                            backgroundColor: statusData.backgroundColor || [],
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
            } catch (e) {
                console.error('Erro ao inicializar gráfico de status:', e);
            }
        }
    }

    // Gráfico de Chamados por Mês
    const monthlyChart = document.getElementById('monthlyChart');
    if (monthlyChart) {
        const ctx = monthlyChart.getContext('2d');
        const dashboardData = document.getElementById('dashboard-data');

        if (dashboardData) {
            try {
                const monthlyData = JSON.parse(dashboardData.getAttribute('data-monthly') || '{}');
                const anoFiltro = dashboardData.getAttribute('data-ano') || new Date().getFullYear();

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: monthlyData.labels || [],
                        datasets: [{
                            label: 'Chamados em ' + anoFiltro,
                            data: monthlyData.data || [],
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
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            tooltip: {
                                callbacks: {
                                    title: function (tooltipItems) {
                                        return tooltipItems[0].label + ' de ' + anoFiltro;
                                    }
                                }
                            }
                        }
                    }
                });
            } catch (e) {
                console.error('Erro ao inicializar gráfico mensal:', e);
            }
        }
    }
}

// Função para atualizar contadores de filtros ativos
function updateFilterCount() {
    const filterBadge = document.getElementById('filterBadge');
    if (filterBadge) {
        const filterCount = document.querySelectorAll('.filtro-ativo').length;
        filterBadge.textContent = filterCount;
        filterBadge.style.display = filterCount > 0 ? 'inline-block' : 'none';
    }
}