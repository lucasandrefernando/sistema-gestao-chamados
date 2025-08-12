/**
 * Módulo de Licenças - Script Isolado (VERSÃO SEGURA)
 * Este script é específico para o módulo de licenças e usa um namespace para evitar conflitos
 */

// Verificar se já existe e remover
if (window.LicencasModule) {
    delete window.LicencasModule;
}

// Usar IIFE para evitar conflitos globais
(function () {
    'use strict';

    const LicencasModule = (function () {
        // Variáveis privadas do módulo
        let baseUrl;
        let isInitialized = false; // Flag para evitar múltiplas inicializações

        // Inicialização do módulo
        function init(config) {
            if (isInitialized) {
                console.warn('Módulo de Licenças já foi inicializado');
                return;
            }

            // Armazenar a URL base de forma segura
            baseUrl = config.baseUrl || '';

            // Inicializar quando o DOM estiver pronto
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', onDOMReady);
            } else {
                // DOM já está pronto
                onDOMReady();
            }
        }

        // Função executada quando o DOM estiver pronto
        function onDOMReady() {
            if (isInitialized) return;

            console.log('Módulo de Licenças inicializado');
            isInitialized = true;

            try {
                initTooltips();
                setupFormValidation();
                setupViewToggle();
                setupFilters();
                animateElements();
            } catch (error) {
                console.error('Erro ao inicializar módulo de licenças:', error);
            }
        }

        // Inicialização de tooltips
        function initTooltips() {
            try {
                // Verificar se Bootstrap está disponível
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

        // Configuração da validação de formulário
        function setupFormValidation() {
            const licencaForm = document.getElementById('licenca-form');
            if (!licencaForm) return;

            licencaForm.addEventListener('submit', function (event) {
                if (!validarFormulario()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            });

            // Configurar validação de datas
            const dataInicio = document.getElementById('data_inicio');
            const dataFim = document.getElementById('data_fim');

            if (dataInicio && dataFim) {
                dataInicio.addEventListener('change', function () {
                    validarDatas();
                });

                dataFim.addEventListener('change', function () {
                    validarDatas();
                });

                // Inicializar cálculo de duração
                setTimeout(validarDatas, 100);
            }

            // Validação em tempo real para quantidade
            const quantidade = document.getElementById('quantidade');
            if (quantidade) {
                quantidade.addEventListener('input', function () {
                    const value = parseInt(this.value);
                    if (value && value > 0) {
                        this.classList.remove('is-invalid');
                        const feedback = this.nextElementSibling;
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.remove();
                        }
                    }
                });
            }
        }

        // Configuração da alternância de visualização
        function setupViewToggle() {
            console.log('Configurando alternância de visualização');

            // Verificar ambos os tipos de botões (para compatibilidade)
            const viewToggleBtns = document.querySelectorAll('.licenca-view-btn, .view-toggle-btn');
            const licencasList = document.getElementById('licencas-list');
            const licencasCards = document.getElementById('licencas-cards');

            console.log('Botões encontrados:', viewToggleBtns.length);
            console.log('Lista encontrada:', !!licencasList);
            console.log('Cards encontrados:', !!licencasCards);

            if (viewToggleBtns.length && licencasList && licencasCards) {
                viewToggleBtns.forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        console.log('Botão clicado:', this.dataset.view);

                        // Remover classe ativa de todos os botões
                        viewToggleBtns.forEach(b => b.classList.remove('active'));

                        // Adicionar classe ativa ao botão clicado
                        this.classList.add('active');

                        // Alternar visualização com animação
                        if (this.dataset.view === 'list') {
                            // Fade out cards, fade in list
                            licencasCards.style.opacity = '0';
                            setTimeout(() => {
                                licencasCards.classList.add('d-none');
                                licencasList.classList.remove('d-none');
                                licencasList.style.opacity = '0';
                                setTimeout(() => {
                                    licencasList.style.opacity = '1';
                                }, 10);
                            }, 200);

                            localStorage.setItem('licencasViewMode', 'list');
                            console.log('Modo lista ativado');
                        } else {
                            // Fade out list, fade in cards
                            licencasList.style.opacity = '0';
                            setTimeout(() => {
                                licencasList.classList.add('d-none');
                                licencasCards.classList.remove('d-none');
                                licencasCards.style.opacity = '0';
                                setTimeout(() => {
                                    licencasCards.style.opacity = '1';
                                }, 10);
                            }, 200);

                            localStorage.setItem('licencasViewMode', 'cards');
                            console.log('Modo cards ativado');
                        }
                    });
                });

                // Carregar preferência salva
                try {
                    const savedViewMode = localStorage.getItem('licencasViewMode') || 'list';
                    console.log('Modo salvo:', savedViewMode);

                    const selector = `.licenca-view-btn[data-view="${savedViewMode}"], .view-toggle-btn[data-view="${savedViewMode}"]`;
                    const savedViewBtn = document.querySelector(selector);

                    if (savedViewBtn) {
                        console.log('Botão encontrado, ativando...');
                        savedViewBtn.click();
                    } else {
                        console.log('Botão não encontrado, usando padrão');
                        // Usar o primeiro botão como padrão
                        if (viewToggleBtns[0]) {
                            viewToggleBtns[0].click();
                        }
                    }
                } catch (e) {
                    console.warn('Erro ao carregar preferência de visualização:', e);
                    // Usar o primeiro botão como padrão
                    if (viewToggleBtns[0]) {
                        viewToggleBtns[0].click();
                    }
                }
            }
        }

        // Configuração dos filtros
        function setupFilters() {
            const filtroStatus = document.getElementById('filtro-status');
            if (!filtroStatus) return;

            filtroStatus.addEventListener('change', function () {
                const status = this.value;
                const licencaItems = document.querySelectorAll('.licenca-item, .licenca-card, .licenca-table tbody tr');

                licencaItems.forEach((item, index) => {
                    const itemStatus = item.dataset.status;

                    if (status === 'todos' || itemStatus === status) {
                        // Mostrar item com animação
                        item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        item.classList.remove('d-none');

                        setTimeout(() => {
                            item.style.opacity = '1';
                            item.style.transform = 'translateY(0)';
                        }, index * 50);
                    } else {
                        // Ocultar item com animação
                        item.style.opacity = '0';
                        item.style.transform = 'translateY(-10px)';

                        setTimeout(() => {
                            item.classList.add('d-none');
                        }, 300);
                    }
                });

                // Atualizar contador de resultados
                updateResultsCounter();
            });

            // Filtro de busca por texto
            const searchInput = document.getElementById('search-licencas');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function () {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        filterByText(this.value.toLowerCase());
                    }, 300);
                });
            }
        }

        // Filtrar por texto
        function filterByText(searchTerm) {
            const licencaItems = document.querySelectorAll('.licenca-item, .licenca-card, .licenca-table tbody tr');

            licencaItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                const shouldShow = !searchTerm || text.includes(searchTerm);

                if (shouldShow) {
                    item.classList.remove('d-none');
                    item.style.opacity = '1';
                } else {
                    item.style.opacity = '0';
                    setTimeout(() => {
                        item.classList.add('d-none');
                    }, 200);
                }
            });

            updateResultsCounter();
        }

        // Atualizar contador de resultados
        function updateResultsCounter() {
            const counter = document.getElementById('results-counter');
            if (!counter) return;

            const visibleItems = document.querySelectorAll('.licenca-item:not(.d-none), .licenca-card:not(.d-none), .licenca-table tbody tr:not(.d-none)');
            const total = document.querySelectorAll('.licenca-item, .licenca-card, .licenca-table tbody tr').length;

            counter.textContent = `${visibleItems.length} de ${total} licenças`;
        }

        // Animação de elementos
        function animateElements() {
            // Animar cards com delay progressivo
            const cards = document.querySelectorAll('.licenca-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100 + (index * 50));
            });

            // Animar linhas da tabela com delay progressivo
            const rows = document.querySelectorAll('.licenca-table tbody tr');
            rows.forEach((row, index) => {
                row.style.opacity = '0';

                setTimeout(() => {
                    row.style.transition = 'opacity 0.3s ease';
                    row.style.opacity = '1';
                }, 50 + (index * 30));
            });

            // Animar elementos do formulário
            const formGroups = document.querySelectorAll('.licenca-form-group, .form-group');
            formGroups.forEach((group, index) => {
                group.style.opacity = '0';
                group.style.transform = 'translateY(10px)';

                setTimeout(() => {
                    group.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    group.style.opacity = '1';
                    group.style.transform = 'translateY(0)';
                }, 100 + (index * 50));
            });

            // Animar estatísticas se existirem
            const statCards = document.querySelectorAll('.stat-card, .stats-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';

                setTimeout(() => {
                    card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'scale(1)';
                }, 200 + (index * 100));
            });
        }

        // Validação do formulário
        function validarFormulario() {
            const empresa = document.getElementById('empresa_id');
            const quantidade = document.getElementById('quantidade');
            const dataInicio = document.getElementById('data_inicio');
            const dataFim = document.getElementById('data_fim');

            let isValid = true;

            // Limpar mensagens de erro anteriores
            document.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
            document.querySelectorAll('.invalid-feedback').forEach(el => {
                el.remove();
            });

            // Validar empresa
            if (empresa && !empresa.value) {
                mostrarErro(empresa, 'Selecione uma empresa');
                isValid = false;
            }

            // Validar quantidade
            if (quantidade && (!quantidade.value || quantidade.value < 1)) {
                mostrarErro(quantidade, 'A quantidade deve ser maior que zero');
                isValid = false;
            }

            // Validar datas
            if (dataInicio && !dataInicio.value) {
                mostrarErro(dataInicio, 'Informe a data de início');
                isValid = false;
            }

            if (dataFim && !dataFim.value) {
                mostrarErro(dataFim, 'Informe a data de fim');
                isValid = false;
            }

            if (dataInicio && dataFim && dataInicio.value && dataFim.value) {
                const inicio = new Date(dataInicio.value);
                const fim = new Date(dataFim.value);

                if (fim <= inicio) {
                    mostrarErro(dataFim, 'A data de fim deve ser posterior à data de início');
                    isValid = false;
                }
            }

            return isValid;
        }

        // Validação das datas e cálculo da duração
        function validarDatas() {
            const dataInicio = document.getElementById('data_inicio');
            const dataFim = document.getElementById('data_fim');

            if (!dataInicio || !dataFim || !dataInicio.value || !dataFim.value) {
                return true;
            }

            const inicio = new Date(dataInicio.value);
            const fim = new Date(dataFim.value);

            // Remover feedback anterior
            const feedbackEl = dataFim.nextElementSibling;
            if (feedbackEl && feedbackEl.classList.contains('invalid-feedback')) {
                feedbackEl.remove();
            }
            dataFim.classList.remove('is-invalid');

            if (fim <= inicio) {
                mostrarErro(dataFim, 'A data de fim deve ser posterior à data de início');
                return false;
            }

            // Calcular e mostrar a duração
            const duracaoEl = document.getElementById('duracao-licenca');
            if (duracaoEl) {
                const diffTime = Math.abs(fim - inicio);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                const years = Math.floor(diffDays / 365);
                const months = Math.floor((diffDays % 365) / 30);
                const days = diffDays % 30;

                let duracaoText = '';
                if (years > 0) {
                    duracaoText += years + (years === 1 ? ' ano' : ' anos');
                }
                if (months > 0) {
                    duracaoText += (duracaoText ? ', ' : '') + months + (months === 1 ? ' mês' : ' meses');
                }
                if (days > 0 || (!years && !months)) {
                    duracaoText += (duracaoText ? ' e ' : '') + days + (days === 1 ? ' dia' : ' dias');
                }

                duracaoEl.textContent = duracaoText;

                // Adicionar classe de destaque se a duração for menor que 30 dias
                const duracaoBadge = duracaoEl.closest('.licenca-form-duration');
                if (duracaoBadge) {
                    if (diffDays < 30) {
                        duracaoBadge.style.backgroundColor = 'rgba(247, 37, 133, 0.1)';
                        duracaoBadge.style.color = '#f72585';
                        duracaoBadge.style.border = '1px solid rgba(247, 37, 133, 0.3)';
                    } else if (diffDays > 365) {
                        duracaoBadge.style.backgroundColor = 'rgba(16, 185, 129, 0.1)';
                        duracaoBadge.style.color = '#10b981';
                        duracaoBadge.style.border = '1px solid rgba(16, 185, 129, 0.3)';
                    } else {
                        duracaoBadge.style.backgroundColor = 'rgba(59, 130, 246, 0.1)';
                        duracaoBadge.style.color = '#3b82f6';
                        duracaoBadge.style.border = '1px solid rgba(59, 130, 246, 0.3)';
                    }
                }

                // Adicionar animação de pulso
                duracaoEl.classList.add('licenca-pulse');
                setTimeout(() => {
                    duracaoEl.classList.remove('licenca-pulse');
                }, 1500);
            }

            return true;
        }

        // Exibição de mensagem de erro
        function mostrarErro(elemento, mensagem) {
            if (!elemento) return;

            elemento.classList.add('is-invalid');

            // Remover feedback anterior se existir
            const existingFeedback = elemento.nextElementSibling;
            if (existingFeedback && existingFeedback.classList.contains('invalid-feedback')) {
                existingFeedback.remove();
            }

            const feedbackDiv = document.createElement('div');
            feedbackDiv.classList.add('invalid-feedback');
            feedbackDiv.textContent = mensagem;

            elemento.parentNode.appendChild(feedbackDiv);

            // Adicionar animação de shake se animate.css estiver disponível
            if (elemento.classList.contains('animate__animated')) {
                elemento.classList.add('animate__shakeX');
                setTimeout(() => {
                    elemento.classList.remove('animate__shakeX');
                }, 1000);
            } else {
                // Animação CSS simples
                elemento.style.animation = 'shake 0.5s ease-in-out';
                setTimeout(() => {
                    elemento.style.animation = '';
                }, 500);
            }

            // Focar no elemento com erro
            elemento.focus();
        }

        // Confirmação de desativação
        function confirmarDesativacao(id, nome) {
            if (!id || !nome) {
                console.error('ID ou nome não fornecido para desativação');
                return;
            }

            // Usar SweetAlert2 se disponível, caso contrário usar confirm padrão
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Desativar licença?',
                    html: `Tem certeza que deseja desativar a licença para <strong>${escapeHtml(nome)}</strong>?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d92550',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sim, desativar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Mostrar loading
                        Swal.fire({
                            title: 'Processando...',
                            text: 'Desativando licença...',
                            icon: 'info',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            willOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Redirecionar após um pequeno delay
                        setTimeout(() => {
                            window.location.href = `${baseUrl}licencas/toggle/${id}`;
                        }, 500);
                    }
                });
            } else {
                if (confirm(`Tem certeza que deseja desativar a licença para ${nome}?`)) {
                    window.location.href = `${baseUrl}licencas/toggle/${id}`;
                }
            }
        }

        // Função para escapar HTML
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Função para mostrar notificações
        function showNotification(message, type = 'success') {
            // Usar SweetAlert2 se disponível
            if (typeof Swal !== 'undefined') {
                const icon = type === 'success' ? 'success' : type === 'error' ? 'error' : 'info';
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: icon,
                    title: message,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            } else {
                // Fallback para alert simples
                alert(message);
            }
        }

        // API pública do módulo
        return {
            init: init,
            confirmarDesativacao: confirmarDesativacao,
            showNotification: showNotification,
            validarFormulario: validarFormulario
        };
    })();

    // Instância única do módulo
    let licencasInstance = null;

    function initLicencas(config) {
        if (licencasInstance) {
            console.warn('Módulo de Licenças já foi inicializado');
            return licencasInstance;
        }

        licencasInstance = LicencasModule;
        licencasInstance.init(config);

        // Expor globalmente se necessário
        window.LicencasModule = LicencasModule;
        window.licencasInstance = licencasInstance;

        return licencasInstance;
    }

    // Função global para desativação (para uso em atributos onclick)
    window.confirmarDesativacao = function (id, nome) {
        if (licencasInstance) {
            licencasInstance.confirmarDesativacao(id, nome);
        } else {
            console.error('Módulo de Licenças não foi inicializado');
        }
    };

    // Inicializar automaticamente se houver configuração no script
    const currentScript = document.currentScript;
    if (currentScript) {
        const baseUrl = currentScript.getAttribute('data-base-url') || '';
        initLicencas({ baseUrl: baseUrl });
    } else {
        // Fallback para inicialização manual
        window.initLicencas = initLicencas;
    }

})(); // Fim da IIFE

// Adicionar estilos CSS para animações se não existirem
if (!document.getElementById('licencas-animations-css')) {
    const style = document.createElement('style');
    style.id = 'licencas-animations-css';
    style.textContent = `
        .licenca-pulse {
            animation: licenca-pulse 1.5s ease-in-out;
        }
        
        @keyframes licenca-pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .licenca-form-duration {
            transition: all 0.3s ease;
            border-radius: 6px;
            padding: 8px 12px;
            margin-top: 8px;
        }
    `;
    document.head.appendChild(style);
}