/**
 * Módulo de Licenças - Script Isolado
 * Este script é específico para o módulo de licenças e usa um namespace para evitar conflitos
 */
const LicencasModule = (function () {
    // Variáveis privadas do módulo
    let baseUrl;

    // Inicialização do módulo
    function init(config) {
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
        console.log('Módulo de Licenças inicializado');
        initTooltips();
        setupFormValidation();
        setupViewToggle();
        setupFilters();
        animateElements();
    }

    // Inicialização de tooltips
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

    // Configuração da validação de formulário
    function setupFormValidation() {
        const licencaForm = document.getElementById('licenca-form');
        if (licencaForm) {
            licencaForm.addEventListener('submit', function (event) {
                if (!validarFormulario()) {
                    event.preventDefault();
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
                btn.addEventListener('click', function () {
                    console.log('Botão clicado:', this.dataset.view);
                    // Remover classe ativa de todos os botões
                    viewToggleBtns.forEach(b => b.classList.remove('active'));

                    // Adicionar classe ativa ao botão clicado
                    this.classList.add('active');

                    // Alternar visualização
                    if (this.dataset.view === 'list') {
                        licencasList.classList.remove('d-none');
                        licencasCards.classList.add('d-none');
                        localStorage.setItem('licencasViewMode', 'list');
                        console.log('Modo lista ativado');
                    } else {
                        licencasList.classList.add('d-none');
                        licencasCards.classList.remove('d-none');
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
                    viewToggleBtns[0].click();
                }
            } catch (e) {
                console.warn('Erro ao carregar preferência de visualização:', e);
                // Usar o primeiro botão como padrão
                viewToggleBtns[0].click();
            }
        }
    }

    // Configuração dos filtros
    function setupFilters() {
        const filtroStatus = document.getElementById('filtro-status');
        if (filtroStatus) {
            filtroStatus.addEventListener('change', function () {
                const status = this.value;
                const licencaItems = document.querySelectorAll('.licenca-item');

                licencaItems.forEach(item => {
                    if (status === 'todos' || item.dataset.status === status) {
                        item.classList.remove('d-none');

                        // Adicionar animação de fade-in
                        setTimeout(() => {
                            item.style.opacity = '1';
                        }, 10);
                    } else {
                        // Adicionar animação de fade-out
                        item.style.opacity = '0';

                        setTimeout(() => {
                            item.classList.add('d-none');
                        }, 300);
                    }
                });
            });
        }
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
        const formGroups = document.querySelectorAll('.licenca-form-group');
        formGroups.forEach((group, index) => {
            group.style.opacity = '0';
            group.style.transform = 'translateY(10px)';

            setTimeout(() => {
                group.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                group.style.opacity = '1';
                group.style.transform = 'translateY(0)';
            }, 100 + (index * 50));
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
        if (!empresa.value) {
            mostrarErro(empresa, 'Selecione uma empresa');
            isValid = false;
        }

        // Validar quantidade
        if (!quantidade.value || quantidade.value < 1) {
            mostrarErro(quantidade, 'A quantidade deve ser maior que zero');
            isValid = false;
        }

        // Validar datas
        if (!dataInicio.value) {
            mostrarErro(dataInicio, 'Informe a data de início');
            isValid = false;
        }

        if (!dataFim.value) {
            mostrarErro(dataFim, 'Informe a data de fim');
            isValid = false;
        }

        if (dataInicio.value && dataFim.value) {
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

        if (dataInicio && dataFim && dataInicio.value && dataFim.value) {
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
                    } else {
                        duracaoBadge.style.backgroundColor = '';
                        duracaoBadge.style.color = '';
                    }
                }

                // Adicionar animação de pulso
                duracaoEl.classList.add('licenca-pulse');
                setTimeout(() => {
                    duracaoEl.classList.remove('licenca-pulse');
                }, 1500);
            }
        }

        return true;
    }

    // Exibição de mensagem de erro
    function mostrarErro(elemento, mensagem) {
        elemento.classList.add('is-invalid');

        const feedbackDiv = document.createElement('div');
        feedbackDiv.classList.add('invalid-feedback');
        feedbackDiv.textContent = mensagem;

        elemento.parentNode.appendChild(feedbackDiv);

        // Adicionar animação de shake
        elemento.classList.add('animate__animated', 'animate__shakeX');
        setTimeout(() => {
            elemento.classList.remove('animate__animated', 'animate__shakeX');
        }, 1000);
    }

    // Confirmação de desativação
    function confirmarDesativacao(id, nome) {
        // Usar SweetAlert2 se disponível, caso contrário usar confirm padrão
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Desativar licença?',
                html: `Tem certeza que deseja desativar a licença para <strong>${nome}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d92550',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sim, desativar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `${baseUrl}licencas/toggle/${id}`;
                }
            });
        } else {
            if (confirm(`Tem certeza que deseja desativar a licença para ${nome}?`)) {
                window.location.href = `${baseUrl}licencas/toggle/${id}`;
            }
        }
    }

    // API pública do módulo
    return {
        init: init,
        confirmarDesativacao: confirmarDesativacao
    };
})();

// Função global para desativação (para uso em atributos onclick)
function confirmarDesativacao(id, nome) {
    LicencasModule.confirmarDesativacao(id, nome);
}

// Inicializar o módulo com a URL base
LicencasModule.init({
    baseUrl: document.currentScript.getAttribute('data-base-url') || ''
});