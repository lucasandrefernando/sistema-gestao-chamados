/**
 * chamados-listar.js - Script específico para a página de listagem de chamados
 * Versão: 2.0.0 - Redesign dos Cards de Estatísticas
 * 
 * FUNCIONALIDADES PRINCIPAIS:
 * - Cards de estatísticas interativos e modernos
 * - Animações suaves e profissionais
 * - Layout reorganizado (ícone + info lado a lado)
 * - Responsividade completa
 * - Acessibilidade melhorada
 */

// ✅ ISOLAMENTO: Só executa se NÃO estivermos na página de usuários
if (!document.querySelector('.usuarios-container')) {

    document.addEventListener('DOMContentLoaded', function () {
        // ===== INICIALIZAÇÃO PRINCIPAL =====
        console.log('🚀 Inicializando sistema de chamados...');

        // Inicializa tooltips do Bootstrap
        initTooltips();

        // ⭐ NOVA FUNÇÃO: Inicializa os cards redesenhados
        initModernStatCards();

        // Configura o comportamento do filtro avançado
        setupAdvancedFilter();

        // Configura os seletores de data
        setupDatePickers();

        // Adiciona funcionalidades à tabela
        enhanceTable();

        // Configura a alternância de visualização (tabela/cards)
        setupViewToggle();

        // Configura o modal de exportação
        setupExportModal();

        // Configura a paginação
        setupPagination();

        // Configura a responsividade
        setupResponsiveBehavior();


        // ✅ NOVA LINHA: Configura autocomplete do solicitante
        setupSolicitanteAutocomplete();

        console.log('✅ Sistema inicializado com sucesso!');
    });

    /**
     * ===== NOVA FUNÇÃO PRINCIPAL DOS CARDS =====
     * Inicializa os cards de estatísticas com o novo design
     * - Layout reorganizado
     * - Animações melhoradas
     * - Interatividade aprimorada
     */
    function initModernStatCards() {
        console.log('🎨 Inicializando cards modernos...');

        const statCards = document.querySelectorAll('.chamados-listar-card-estatistica');

        if (statCards.length === 0) {
            console.warn('⚠️ Nenhum card de estatística encontrado');
            return;
        }

        // ===== REORGANIZA O LAYOUT DOS CARDS =====
        statCards.forEach((card, index) => {
            reorganizeCardLayout(card);
            setupCardInteractivity(card);
            animateCardEntrance(card, index);
            checkActiveCard(card);
        });

        console.log(`✅ ${statCards.length} cards inicializados`);
    }

    /**
     * Reorganiza o layout interno do card para o novo design
     * ANTES: Ícone flutuando à esquerda + conteúdo embaixo
     * DEPOIS: Ícone + informações lado a lado no topo + descrição + barra embaixo
     * 
     * @param {HTMLElement} card - Elemento do card
     */
    function reorganizeCardLayout(card) {
        // Obtém os elementos existentes
        const icone = card.querySelector('.chamados-listar-icone-estatistica');
        const conteudo = card.querySelector('.chamados-listar-conteudo-estatistica');
        const valor = card.querySelector('.chamados-listar-valor-estatistica');
        const label = card.querySelector('.chamados-listar-label-estatistica');
        const descricao = card.querySelector('.chamados-listar-descricao-estatistica');
        const progresso = card.querySelector('.chamados-listar-estatistica-progresso');

        if (!icone || !conteudo || !valor || !label) {
            console.warn('⚠️ Elementos do card não encontrados:', card);
            return;
        }

        // Remove o float do ícone (compatibilidade com CSS antigo)
        icone.style.float = 'none';
        icone.style.marginRight = '0';

        // Cria a nova estrutura
        const novoConteudo = document.createElement('div');
        novoConteudo.className = 'chamados-listar-conteudo-estatistica';

        // Seção superior: Ícone + Informações lado a lado
        const headerInfo = document.createElement('div');
        headerInfo.className = 'chamados-listar-card-header-info';

        const infoNumerica = document.createElement('div');
        infoNumerica.className = 'chamados-listar-info-numerica';

        // Move os elementos para a nova estrutura
        infoNumerica.appendChild(valor);
        infoNumerica.appendChild(label);

        headerInfo.appendChild(icone);
        headerInfo.appendChild(infoNumerica);

        // Seção inferior: Descrição + Barra de progresso
        const bodyInfo = document.createElement('div');
        bodyInfo.className = 'chamados-listar-card-body-info';

        if (descricao) {
            bodyInfo.appendChild(descricao);
        }
        if (progresso) {
            bodyInfo.appendChild(progresso);
        }

        // Monta a estrutura final
        novoConteudo.appendChild(headerInfo);
        novoConteudo.appendChild(bodyInfo);

        // Substitui o conteúdo antigo
        card.innerHTML = '';
        card.appendChild(novoConteudo);

        console.log('🔄 Layout do card reorganizado:', card);
    }

    /**
     * Configura a interatividade do card
     * - Cliques para filtrar
     * - Efeitos de hover
     * - Navegação por teclado
     * - Tooltips informativos
     * 
     * @param {HTMLElement} card - Elemento do card
     */
    function setupCardInteractivity(card) {
        // ===== CONFIGURAÇÃO DE CLIQUE =====
        card.addEventListener('click', function () {
            handleCardClick(this);
        });

        // ===== NAVEGAÇÃO POR TECLADO =====
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'button');

        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                handleCardClick(this);
            }
        });

        // ===== TOOLTIPS INFORMATIVOS =====
        const label = card.querySelector('.chamados-listar-label-estatistica');
        if (label) {
            const filterType = card.getAttribute('data-filter');
            let tooltipText = '';

            if (filterType === 'todos') {
                tooltipText = `${label.textContent} - Clique para remover filtros`;
            } else {
                tooltipText = `${label.textContent} - Clique para filtrar`;
            }

            card.setAttribute('title', tooltipText);
            card.setAttribute('data-bs-toggle', 'tooltip');
            card.setAttribute('data-bs-placement', 'top');
            card.setAttribute('aria-label', tooltipText);
        }

        // ===== EFEITOS VISUAIS AVANÇADOS =====
        setupAdvancedCardEffects(card);

        console.log('🎯 Interatividade configurada para card:', card);
    }

    /**
     * Configura efeitos visuais avançados para o card
     * - Efeito de ripple no clique
     * - Animação de hover suave
     * - Feedback tátil
     * 
     * @param {HTMLElement} card - Elemento do card
     */
    function setupAdvancedCardEffects(card) {
        // ===== EFEITO DE RIPPLE NO CLIQUE =====
        card.addEventListener('mousedown', function (e) {
            createRippleEffect(this, e);
        });

        // ===== EFEITO DE HOVER SUAVE =====
        let hoverTimeout;

        card.addEventListener('mouseenter', function () {
            clearTimeout(hoverTimeout);
            this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        });

        card.addEventListener('mouseleave', function () {
            hoverTimeout = setTimeout(() => {
                this.style.transition = 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            }, 100);
        });

        // ===== FEEDBACK TÁTIL NO CLIQUE =====
        card.addEventListener('click', function () {
            // Efeito de "pressionar"
            this.style.transform = 'translateY(-6px) scale(0.98)';

            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    }

    /**
     * Cria efeito de ripple (ondulação) no clique
     * 
     * @param {HTMLElement} element - Elemento onde criar o ripple
     * @param {MouseEvent} event - Evento do mouse
     */
    function createRippleEffect(element, event) {
        const ripple = document.createElement('div');
        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.clientX - rect.left - size / 2;
        const y = event.clientY - rect.top - size / 2;

        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s ease-out;
            pointer-events: none;
            z-index: 1;
        `;

        // Adiciona a animação CSS se não existir
        if (!document.querySelector('#ripple-animation')) {
            const style = document.createElement('style');
            style.id = 'ripple-animation';
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(2);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        element.style.position = 'relative';
        element.style.overflow = 'hidden';
        element.appendChild(ripple);

        // Remove o ripple após a animação
        setTimeout(() => {
            if (ripple.parentNode) {
                ripple.parentNode.removeChild(ripple);
            }
        }, 600);
    }

    /**
     * Anima a entrada do card com delay escalonado
     * 
     * @param {HTMLElement} card - Elemento do card
     * @param {number} index - Índice do card para delay
     */
    function animateCardEntrance(card, index) {
        // Define o delay baseado no índice
        const delay = index * 100; // 100ms entre cada card

        // Inicia invisível
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';

        setTimeout(() => {
            card.classList.add('animate-in');
            card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';

            // Anima o contador após a entrada
            setTimeout(() => {
                animateCardCounter(card);
            }, 200);

        }, delay);

        console.log(`🎬 Card ${index + 1} animado com delay de ${delay}ms`);
    }

    /**
     * Anima o contador numérico do card
     * 
     * @param {HTMLElement} card - Elemento do card
     */
    function animateCardCounter(card) {
        const valueElement = card.querySelector('.chamados-listar-valor-estatistica');
        if (!valueElement) return;

        const targetValue = parseInt(valueElement.textContent) || 0;

        // Só anima se o valor for maior que 0
        if (targetValue === 0) return;

        let currentValue = 0;
        const duration = 1200; // 1.2 segundos
        const steps = 60; // 60 FPS
        const increment = targetValue / steps;
        const stepTime = duration / steps;

        valueElement.textContent = '0';

        const timer = setInterval(() => {
            currentValue += increment;

            if (currentValue >= targetValue) {
                currentValue = targetValue;
                clearInterval(timer);
            }

            valueElement.textContent = Math.floor(currentValue);
        }, stepTime);

        console.log(`🔢 Contador animado para valor: ${targetValue}`);
    }

    /**
     * Manipula o clique nos cards
     * 
     * @param {HTMLElement} card - Card clicado
     */
    function handleCardClick(card) {
        console.log('🖱️ Card clicado:', card);

        const filterType = card.getAttribute('data-filter');

        // Adiciona classe de loading temporária
        card.classList.add('chamados-listar-loading');

        setTimeout(() => {
            if (filterType === 'todos') {
                // Remove todos os filtros
                window.location.href = window.location.pathname;
            } else if (filterType === 'status') {
                // Aplica filtro de status
                const statusId = card.getAttribute('data-status');
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('status', statusId);
                urlParams.delete('pagina'); // Volta para a primeira página

                window.location.href = `${window.location.pathname}?${urlParams.toString()}`;
            }
        }, 200);
    }

    /**
     * Verifica e marca o card ativo baseado nos filtros da URL
     * 
     * @param {HTMLElement} card - Elemento do card
     */
    function checkActiveCard(card) {
        const urlParams = new URLSearchParams(window.location.search);
        const statusFilter = urlParams.get('status');
        const filterType = card.getAttribute('data-filter');
        const statusId = card.getAttribute('data-status');

        // Remove classe ativa de todos os cards
        card.classList.remove('active');

        // Verifica se este card deve estar ativo
        if (filterType === 'status' && statusFilter === statusId) {
            card.classList.add('active');
            console.log('✅ Card ativo identificado:', card);
        } else if (filterType === 'todos' && !statusFilter) {
            card.classList.add('active');
            console.log('✅ Card ativo identificado (todos):', card);
        }
    }

    /**
     * ===== FUNÇÃO PARA ATUALIZAR CARDS DINAMICAMENTE =====
     * Atualiza os valores dos cards com animação suave
     * Útil para atualizações via AJAX sem recarregar a página
     * 
     * @param {Object} newStats - Novas estatísticas
     * @param {number} newStats.total - Total de chamados
     * @param {number} newStats.abertos - Chamados abertos
     * @param {number} newStats.em_andamento - Chamados em andamento
     * @param {number} newStats.concluidos - Chamados concluídos
     */
    function updateModernStatCards(newStats) {
        if (!newStats) {
            console.warn('⚠️ Estatísticas não fornecidas para atualização');
            return;
        }

        console.log('🔄 Atualizando cards com novas estatísticas:', newStats);

        const cards = {
            total: document.querySelector('.chamados-listar-total'),
            abertos: document.querySelector('.chamados-listar-abertos'),
            andamento: document.querySelector('.chamados-listar-andamento'),
            concluidos: document.querySelector('.chamados-listar-concluidos')
        };

        // Atualiza cada card
        Object.keys(cards).forEach(key => {
            const card = cards[key];
            if (!card || newStats[key] === undefined) return;

            const valueElement = card.querySelector('.chamados-listar-valor-estatistica');
            const progressBar = card.querySelector('.chamados-listar-estatistica-barra');

            if (valueElement) {
                // Anima a mudança de valor
                animateValueChange(valueElement, newStats[key]);
            }

            // Atualiza a barra de progresso
            if (progressBar && key !== 'total' && newStats.total > 0) {
                const percentage = (newStats[key] / newStats.total) * 100;

                // Anima a barra de progresso
                setTimeout(() => {
                    progressBar.style.width = percentage + '%';
                }, 300);
            }

            // Adiciona efeito visual de atualização
            card.classList.add('chamados-listar-highlight');
            setTimeout(() => {
                card.classList.remove('chamados-listar-highlight');
            }, 1000);
        });

        console.log('✅ Cards atualizados com sucesso');
    }

    /**
     * Anima a mudança de valor em um elemento
     * 
     * @param {HTMLElement} element - Elemento do valor
     * @param {number} newValue - Novo valor
     */
    function animateValueChange(element, newValue) {
        const currentValue = parseInt(element.textContent) || 0;
        const difference = newValue - currentValue;

        if (difference === 0) return;

        const steps = 30;
        const stepValue = difference / steps;
        const stepTime = 40; // 40ms por step = 1.2s total

        let step = 0;
        const timer = setInterval(() => {
            step++;
            const value = Math.round(currentValue + (stepValue * step));
            element.textContent = value;

            if (step >= steps) {
                element.textContent = newValue;
                clearInterval(timer);
            }
        }, stepTime);
    }

    /**
     * ===== FUNÇÕES ORIGINAIS MANTIDAS =====
     * Mantém compatibilidade com o código existente
     */

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
        console.log('💡 Tooltips inicializados');
    }

    /**
     * ⚠️ FUNÇÃO DEPRECIADA - Mantida para compatibilidade
     * Use initModernStatCards() em vez desta
     */
    function animateStatCards() {
        console.warn('⚠️ animateStatCards() está depreciada. Use initModernStatCards()');
        // Chama a nova função para compatibilidade
        initModernStatCards();
    }

    /**
     * ⚠️ FUNÇÃO DEPRECIADA - Mantida para compatibilidade
     * Use initModernStatCards() em vez desta
     */
    function setupClickableStatCards() {
        console.warn('⚠️ setupClickableStatCards() está depreciada. Use initModernStatCards()');
        // A funcionalidade agora está integrada em initModernStatCards()
    }

    /**
     * Configura o comportamento do filtro avançado
     */
    function setupAdvancedFilter() {
        const filterHeader = document.querySelector('.chamados-listar-filtros-header');
        const filterToggle = document.querySelector('.chamados-listar-filtros-toggle');
        const filterCollapse = document.getElementById('filtrosCollapse');

        if (filterHeader && filterToggle && filterCollapse) {
            // ===== CORREÇÃO: Lógica do botão de expandir/recolher =====

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

            // Inicializa o Bootstrap Collapse
            let bsCollapse = new bootstrap.Collapse(filterCollapse, {
                toggle: false // Não alterna automaticamente
            });

            // ✅ CORREÇÃO: Função para atualizar o ícone baseado no estado
            function updateToggleIcon() {
                const icon = filterToggle.querySelector('i');
                if (!icon) return;

                if (filterCollapse.classList.contains('show')) {
                    // Filtro está expandido - mostrar ícone para recolher
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                } else {
                    // Filtro está recolhido - mostrar ícone para expandir
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            }

            // ✅ CORREÇÃO: Expande o filtro apenas se houver filtros ativos
            if (hasActiveFilters) {
                bsCollapse.show();
            }

            // Atualiza o ícone inicial
            updateToggleIcon();

            // ✅ CORREÇÃO: Evento de clique no cabeçalho - agora funciona corretamente
            filterHeader.addEventListener('click', function (e) {
                e.preventDefault();

                // Alterna o estado do collapse
                if (filterCollapse.classList.contains('show')) {
                    bsCollapse.hide();
                } else {
                    bsCollapse.show();
                }
            });

            // ✅ CORREÇÃO: Eventos do Bootstrap Collapse para atualizar o ícone
            filterCollapse.addEventListener('shown.bs.collapse', function () {
                updateToggleIcon();
                console.log('🔽 Filtro expandido');
            });

            filterCollapse.addEventListener('hidden.bs.collapse', function () {
                updateToggleIcon();
                console.log('🔼 Filtro recolhido');
            });

            // ===== RESTO DA FUNÇÃO MANTIDA =====

            // Configura o formulário de filtro
            const filterForm = document.querySelector('.chamados-listar-filtros-form');
            if (filterForm) {
                // Adiciona validação visual aos campos
                const filterInputs = filterForm.querySelectorAll('.chamados-listar-filtro-select, .chamados-listar-filtro-input');
                filterInputs.forEach(input => {
                    input.addEventListener('change', function () {
                        if (this.value) {
                            this.style.borderColor = 'var(--chamados-listar-primary)';
                        } else {
                            this.style.borderColor = 'var(--chamados-listar-light-gray)';
                        }
                    });

                    // ✅ NOVO: Evento para inputs de texto (solicitante)
                    if (input.type === 'text') {
                        input.addEventListener('input', function () {
                            if (this.value.trim()) {
                                this.style.borderColor = 'var(--chamados-listar-primary)';
                            } else {
                                this.style.borderColor = 'var(--chamados-listar-light-gray)';
                            }
                        });
                    }

                    // Aplica estilo inicial se já tiver valor
                    if (input.value) {
                        input.style.borderColor = 'var(--chamados-listar-primary)';
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

            console.log('🔍 Filtros avançados configurados (corrigidos)');
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

        console.log('📅 Seletores de data configurados');
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

        console.log('📊 Tabela aprimorada');
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

        console.log('🔄 Alternância de visualização configurada');
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

        console.log('📤 Modal de exportação configurado');
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

        console.log('📱 Comportamento responsivo configurado');
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
     * ⚠️ FUNÇÃO DEPRECIADA - Mantida para compatibilidade
     * Use updateModernStatCards() em vez desta
     */
    function updateStatCounters(stats) {
        console.warn('⚠️ updateStatCounters() está depreciada. Use updateModernStatCards()');
        updateModernStatCards(stats);
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
 * ✅ NOVA FUNÇÃO: Configura autocomplete inteligente para o campo solicitante
 */
    function setupSolicitanteAutocomplete() {
        const solicitanteInput = document.getElementById('chamados-listar-solicitante');
        const datalist = document.getElementById('solicitantes-datalist');

        if (!solicitanteInput) return;

        // ===== AUTOCOMPLETE INTELIGENTE =====
        let timeoutId;

        solicitanteInput.addEventListener('input', function () {
            const value = this.value.trim();

            // Limpa timeout anterior
            clearTimeout(timeoutId);

            // Adiciona delay para evitar muitas requisições
            timeoutId = setTimeout(() => {
                // Filtra as opções do datalist baseado no que foi digitado
                if (datalist && value.length >= 2) {
                    const options = datalist.querySelectorAll('option');
                    let hasMatch = false;

                    options.forEach(option => {
                        const optionValue = option.value.toLowerCase();
                        const inputValue = value.toLowerCase();

                        if (optionValue.includes(inputValue)) {
                            option.style.display = '';
                            hasMatch = true;
                        } else {
                            option.style.display = 'none';
                        }
                    });

                    // Feedback visual se não houver correspondências
                    if (!hasMatch && value.length >= 3) {
                        solicitanteInput.style.borderColor = 'var(--chamados-listar-warning)';
                        solicitanteInput.title = 'Nenhum solicitante encontrado com este nome';
                    } else {
                        solicitanteInput.style.borderColor = value ? 'var(--chamados-listar-primary)' : 'var(--chamados-listar-light-gray)';
                        solicitanteInput.title = '';
                    }
                }
            }, 300); // 300ms de delay
        });

        // ===== LIMPEZA DO CAMPO =====
        solicitanteInput.addEventListener('keydown', function (e) {
            // Limpa o campo com Escape
            if (e.key === 'Escape') {
                this.value = '';
                this.style.borderColor = 'var(--chamados-listar-light-gray)';
                this.blur();
            }
        });

        // ===== VALIDAÇÃO AO SAIR DO CAMPO =====
        solicitanteInput.addEventListener('blur', function () {
            const value = this.value.trim();

            if (value) {
                // Verifica se o valor digitado existe nas opções
                if (datalist) {
                    const options = Array.from(datalist.querySelectorAll('option'));
                    const exactMatch = options.some(option =>
                        option.value.toLowerCase() === value.toLowerCase()
                    );

                    if (exactMatch) {
                        this.style.borderColor = 'var(--chamados-listar-success)';
                    } else {
                        this.style.borderColor = 'var(--chamados-listar-primary)';
                    }
                }
            } else {
                this.style.borderColor = 'var(--chamados-listar-light-gray)';
            }
        });

        console.log('👤 Autocomplete do solicitante configurado');
    }

    /**
     * Configura a paginação da tabela de chamados
     */
    function setupPagination() {
        const paginationContainer = document.querySelector('.pagination-container');
        if (!paginationContainer) return;

        const paginationLinks = paginationContainer.querySelectorAll('.pagination-link');

        // Declara as variáveis necessárias
        let previousLink = null;
        let nextLink = null;
        let currentPage = 1;

        // Identifica os links de navegação
        paginationLinks.forEach(link => {
            const linkText = link.textContent.trim();
            const linkHref = link.getAttribute('href');

            // Identifica o link "Anterior"
            if (link.querySelector('i.fa-angle-left') || link.querySelector('i.fa-angle-double-left')) {
                previousLink = link;
            }

            // Identifica o link "Próximo"
            if (link.querySelector('i.fa-angle-right') || link.querySelector('i.fa-angle-double-right')) {
                nextLink = link;
            }

            // Identifica a página atual
            if (link.classList.contains('active') && !isNaN(parseInt(linkText))) {
                currentPage = parseInt(linkText);
            }
        });

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

        console.log('📄 Paginação configurada');
    }

    // ===== EXPÕE FUNÇÕES GLOBALMENTE =====
    // Mantém compatibilidade com código externo
    window.mudarRegistrosPorPagina = mudarRegistrosPorPagina;
    window.updateStatCounters = updateStatCounters; // Depreciada
    window.updateModernStatCards = updateModernStatCards; // Nova função principal

    console.log('🎉 Sistema de chamados carregado com sucesso!');

} // ✅ FIM da verificação de isolamento