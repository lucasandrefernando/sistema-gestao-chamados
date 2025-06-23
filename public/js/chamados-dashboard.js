/**
 * chamados-dashboard.js - Script específico para a página de dashboard de chamados
 */

document.addEventListener('DOMContentLoaded', function () {
    // Inicializa tooltips do Bootstrap
    initTooltips();

    // Anima os cards de estatísticas
    animateStatCards();

    // Adiciona efeitos de hover aos cards
    setupCardHoverEffects();

    // Inicializa os datepickers com configurações específicas
    setupDatePickers();

    // Adiciona comportamento responsivo
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
    const statCards = document.querySelectorAll('.chamados-stat-card');
    statCards.forEach(function (card, index) {
        // Define um atraso crescente para cada card
        setTimeout(function () {
            card.classList.add('animate-in');
            card.style.animationDelay = (index * 0.1) + 's';
        }, 100);
    });
}

/**
 * Configura efeitos de hover para os cards
 */
function setupCardHoverEffects() {
    // Efeito de elevação para os cards de estatísticas
    const statCards = document.querySelectorAll('.chamados-stat-card');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-8px)';
            this.style.boxShadow = '0 12px 24px rgba(0, 0, 0, 0.15)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'var(--chamados-box-shadow)';
        });
    });

    // Efeito de elevação para os outros cards
    const otherCards = document.querySelectorAll('.chamados-filter-card, .chamados-recent-card');
    otherCards.forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.1)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'var(--chamados-box-shadow)';
        });
    });
}

/**
 * Configura os seletores de data com funcionalidades adicionais
 */
function setupDatePickers() {
    // Obtém os elementos de data
    const dataInicio = document.getElementById('chamados-data-inicio');
    const dataFim = document.getElementById('chamados-data-fim');

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
 * Configura comportamentos responsivos específicos
 */
function setupResponsiveBehavior() {
    // Ajusta a tabela em telas pequenas
    function adjustTableForSmallScreens() {
        const tableContainer = document.querySelector('.chamados-table-responsive');
        if (!tableContainer) return;

        if (window.innerWidth < 768) {
            // Adiciona indicador de rolagem horizontal se necessário
            if (tableContainer.scrollWidth > tableContainer.clientWidth) {
                if (!document.querySelector('.chamados-scroll-indicator')) {
                    const scrollIndicator = document.createElement('div');
                    scrollIndicator.className = 'chamados-scroll-indicator';
                    scrollIndicator.innerHTML = '<i class="fas fa-arrows-alt-h"></i> Deslize para ver mais';
                    scrollIndicator.style.textAlign = 'center';
                    scrollIndicator.style.padding = '0.5rem';
                    scrollIndicator.style.color = 'var(--chamados-gray)';
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

    // Ajusta o layout dos botões de ação em telas pequenas
    const actionButtons = document.querySelector('.chamados-action-buttons');
    const viewToggle = document.querySelector('.chamados-view-toggle');

    function adjustActionButtons() {
        if (window.innerWidth < 576) {
            actionButtons.style.flexDirection = 'column';
            viewToggle.style.width = '100%';
            viewToggle.style.marginBottom = '0.75rem';
        } else {
            actionButtons.style.flexDirection = 'row';
            viewToggle.style.width = 'auto';
            viewToggle.style.marginBottom = '0';
        }
    }

    // Executa o ajuste inicial
    if (actionButtons && viewToggle) {
        adjustActionButtons();

        // Adiciona listener para redimensionamento da janela
        window.addEventListener('resize', adjustActionButtons);
    }
}

/**
 * Função para atualizar os contadores de estatísticas com animação
 * Pode ser chamada após carregar novos dados via AJAX
 */
function updateStatCounters(stats) {
    if (!stats) return;

    const elements = {
        total: document.querySelector('.chamados-total .chamados-stat-value'),
        abertos: document.querySelector('.chamados-abertos .chamados-stat-value'),
        andamento: document.querySelector('.chamados-andamento .chamados-stat-value'),
        concluidos: document.querySelector('.chamados-concluidos .chamados-stat-value')
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
 * Função para atualizar a tabela de chamados recentes
 * Pode ser usada com AJAX para atualizar os dados sem recarregar a página
 */
function updateRecentTickets(chamados) {
    if (!chamados || !Array.isArray(chamados)) return;

    const tableBody = document.querySelector('.chamados-recent-table tbody');
    if (!tableBody) return;

    // Limpa a tabela atual
    tableBody.innerHTML = '';

    if (chamados.length === 0) {
        // Exibe mensagem de nenhum chamado
        const emptyRow = document.createElement('tr');
        emptyRow.innerHTML = `
            <td colspan="6" class="chamados-no-tickets">
                <div class="chamados-no-data">
                    <i class="fas fa-ticket-alt"></i>
                    <p>Nenhum chamado encontrado.</p>
                </div>
            </td>
        `;
        tableBody.appendChild(emptyRow);
        return;
    }

    // Adiciona os novos chamados
    chamados.forEach(chamado => {
        const row = document.createElement('tr');

        // Constrói a linha da tabela com os dados do chamado
        // Esta parte depende da estrutura exata dos seus dados
        // Adapte conforme necessário

        tableBody.appendChild(row);
    });

    // Reinicializa os tooltips
    initTooltips();
}