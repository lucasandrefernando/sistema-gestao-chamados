/**
 * chamados-form.js - Script para a página de formulário de chamados hospitalares
 * Versão 4.2 - Com tipos de serviço baseados nos setores
 */

/**
 * Inicializa o formulário de chamados
 * @param {Array} sugestoesDescricao - Lista de sugestões de descrição
 * @param {Object} tiposServicoPorSetor - Mapeamento de tipos de serviço por setor
 */
function initChamadosForm(sugestoesDescricao, tiposServicoPorSetor) {
    // Inicializa os componentes
    setupSetorTipoServico(tiposServicoPorSetor);
    setupOutroTipoServico();
    setupFormValidation();
    setupCharacterCounter();
    setupCardHoverEffects();
    setupFormBehavior();
    setupHospitalTips(tiposServicoPorSetor);
    setupAlertClosers();

    // Adiciona sugestões de descrição
    if (sugestoesDescricao && Array.isArray(sugestoesDescricao)) {
        adicionarSugestoesDescricao(sugestoesDescricao);
    }

    /**
     * Configura a relação entre setor e tipo de serviço
     * @param {Object} tiposServicoPorSetor - Mapeamento de tipos de serviço por setor
     */
    function setupSetorTipoServico(tiposServicoPorSetor) {
        const setorSelect = document.getElementById('setor_id');
        const tipoServicoSelect = document.getElementById('tipo_servico');

        if (!setorSelect || !tipoServicoSelect) return;

        // Adiciona evento de mudança ao select de setor
        setorSelect.addEventListener('change', function () {
            // Obtém o nome do setor selecionado
            const setorNome = this.options[this.selectedIndex].getAttribute('data-nome');

            if (this.value) {
                // Habilita o select de tipo de serviço
                tipoServicoSelect.disabled = false;

                // Preenche as opções com base no setor selecionado
                preencherTiposServico(setorNome, tipoServicoSelect, tiposServicoPorSetor);

                // Atualiza as dicas contextuais com base no setor selecionado
                updateContextualTipsBySetor(setorNome);
            } else {
                // Se nenhum setor for selecionado, desabilita o select de tipo de serviço
                tipoServicoSelect.disabled = true;
                tipoServicoSelect.innerHTML = '<option value="">Selecione primeiro o setor</option>';

                // Esconde o campo de outro tipo de serviço
                const outroTipoServico = document.getElementById('outroTipoServico');
                if (outroTipoServico) {
                    outroTipoServico.style.display = 'none';
                }
            }

            // Valida o campo
            validateField(setorSelect);
        });

        // Valida o campo de tipo de serviço quando ele muda
        tipoServicoSelect.addEventListener('change', function () {
            validateField(this);

            // Verifica se a opção "Outro" foi selecionada
            const outroTipoServico = document.getElementById('outroTipoServico');
            const outroTipoServicoInput = document.getElementById('outro_tipo_servico');

            if (this.value === 'outro' && outroTipoServico && outroTipoServicoInput) {
                // Mostra o campo com animação
                outroTipoServico.style.display = 'block';
                outroTipoServico.style.animation = 'fadeIn 0.3s ease';
                outroTipoServicoInput.setAttribute('required', 'required');

                // Foca no campo
                setTimeout(() => {
                    outroTipoServicoInput.focus();
                }, 300);
            } else if (outroTipoServico && outroTipoServicoInput) {
                // Esconde o campo
                outroTipoServico.style.display = 'none';
                outroTipoServicoInput.removeAttribute('required');
                outroTipoServicoInput.value = '';
            }

            // Atualiza as dicas contextuais com base no tipo de serviço selecionado
            updateContextualTips(this.options[this.selectedIndex].textContent);
        });
    }

    /**
     * Preenche o select de tipos de serviço com base no setor selecionado
     * @param {string} setorNome - Nome do setor selecionado
     * @param {HTMLElement} tipoServicoSelect - Elemento select para tipos de serviço
     * @param {Object} tiposServicoPorSetor - Mapeamento de tipos de serviço por setor
     */
    function preencherTiposServico(setorNome, tipoServicoSelect, tiposServicoPorSetor) {
        // Limpa as opções atuais
        tipoServicoSelect.innerHTML = '';

        // Adiciona a opção padrão
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Selecione o tipo de serviço';
        tipoServicoSelect.appendChild(defaultOption);

        // Obtém os tipos de serviço para o setor selecionado ou usa os tipos padrão
        const tiposServico = tiposServicoPorSetor[setorNome] || tiposServicoPorSetor['default'];

        // Adiciona as opções de tipos de serviço
        for (const [valor, descricao] of Object.entries(tiposServico)) {
            const option = document.createElement('option');
            option.value = valor;
            option.textContent = descricao;
            tipoServicoSelect.appendChild(option);
        }

        // Adiciona a opção "Outro"
        const outroOption = document.createElement('option');
        outroOption.value = 'outro';
        outroOption.textContent = 'Outro (especificar)';
        tipoServicoSelect.appendChild(outroOption);
    }

    /**
     * Configura o campo de outro tipo de serviço
     */
    function setupOutroTipoServico() {
        const outroTipoServico = document.getElementById('outroTipoServico');
        const outroTipoServicoInput = document.getElementById('outro_tipo_servico');

        if (!outroTipoServico || !outroTipoServicoInput) return;

        // Inicialmente esconde o campo
        outroTipoServico.style.display = 'none';
        outroTipoServicoInput.removeAttribute('required');

        // Adiciona validação ao campo de outro tipo de serviço
        outroTipoServicoInput.addEventListener('input', function () {
            validateField(outroTipoServicoInput);
        });
    }

    /**
     * Configura os botões de fechar dos alertas
     */
    function setupAlertClosers() {
        const alertCloseButtons = document.querySelectorAll('.chamados-form-alert-close');

        alertCloseButtons.forEach(button => {
            button.addEventListener('click', function () {
                const alert = this.closest('.chamados-form-alert');
                if (alert) {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';

                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                }
            });
        });
    }

    /**
     * Adiciona validação aos campos do formulário
     */
    function setupFormValidation() {
        const form = document.querySelector('.chamados-form-formulario');
        if (!form) return;

        // Obtém todos os campos obrigatórios
        const requiredFields = form.querySelectorAll('[required]');

        // Adiciona validação a cada campo
        requiredFields.forEach(field => {
            field.addEventListener('blur', function () {
                validateField(this);
            });

            field.addEventListener('input', function () {
                // Remove a classe de erro quando o usuário começa a digitar
                this.classList.remove('is-invalid');

                const feedbackElement = this.nextElementSibling;
                if (feedbackElement && feedbackElement.classList.contains('chamados-form-feedback')) {
                    feedbackElement.textContent = '';
                    feedbackElement.classList.remove('invalid-feedback');
                }
            });
        });

        // Adiciona validação ao enviar o formulário
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // Impede o envio padrão

            let isValid = true;

            // Valida todos os campos obrigatórios
            requiredFields.forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });

            // Verifica o campo de outro tipo de serviço
            const tipoServicoSelect = document.getElementById('tipo_servico');
            const outroTipoServicoInput = document.getElementById('outro_tipo_servico');

            if (tipoServicoSelect && outroTipoServicoInput && tipoServicoSelect.value === 'outro') {
                if (!validateField(outroTipoServicoInput)) {
                    isValid = false;
                }
            }

            // Verifica se há setores disponíveis
            const setorSelect = document.getElementById('setor_id');
            if (setorSelect && setorSelect.options.length <= 1) {
                isValid = false;
                showToast('Não é possível abrir chamados sem setores ativos', 'error');
                return;
            }

            // Impede o envio se o formulário for inválido
            if (!isValid) {
                // Rola até o primeiro campo inválido
                const firstInvalidField = form.querySelector('.is-invalid');
                if (firstInvalidField) {
                    firstInvalidField.focus();
                    firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                // Mostra uma mensagem de erro
                showToast('Por favor, corrija os campos destacados antes de enviar.', 'error');
                return;
            }

            // Prepara os dados para envio
            const formData = new FormData(form);

            // Se o tipo de serviço for "outro", substitui pelo valor digitado
            if (tipoServicoSelect && tipoServicoSelect.value === 'outro' && outroTipoServicoInput) {
                formData.set('tipo_servico', outroTipoServicoInput.value.trim());
            }

            // Adiciona efeito de loading ao botão de salvar
            const submitBtn = document.getElementById('btnSubmit');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
                submitBtn.disabled = true;

                // Envia o formulário normalmente
                form.removeEventListener('submit', arguments.callee);
                form.submit();
            } else {
                // Se não encontrar o botão, envia o formulário normalmente
                form.removeEventListener('submit', arguments.callee);
                form.submit();
            }
        });
    }

    /**
     * Exibe um toast de notificação
     * @param {string} message - Mensagem a ser exibida
     * @param {string} type - Tipo de toast (success ou error)
     */
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `chamados-form-toast chamados-form-toast-${type}`;
        toast.innerHTML = `
            <div class="chamados-form-toast-icon">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            </div>
            <div class="chamados-form-toast-content">
                <p>${message}</p>
            </div>
        `;
        document.body.appendChild(toast);

        // Remove o toast após 3 segundos
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }

    /**
     * Valida um campo específico
     * @param {HTMLElement} field - O campo a ser validado
     * @returns {boolean} - Verdadeiro se o campo for válido, falso caso contrário
     */
    function validateField(field) {
        // Remove classes de validação existentes
        field.classList.remove('is-valid', 'is-invalid');

        // Obtém o feedback do campo
        const feedbackElement = field.nextElementSibling;
        if (feedbackElement && feedbackElement.classList.contains('chamados-form-feedback')) {
            feedbackElement.textContent = '';
            feedbackElement.classList.remove('valid-feedback', 'invalid-feedback');
        }

        // Verifica se o campo é obrigatório
        const isRequired = field.hasAttribute('required');

        // Valida o campo
        let isValid = true;
        let errorMessage = '';

        // Verifica se o campo está vazio
        if (isRequired && field.value.trim() === '') {
            isValid = false;
            errorMessage = 'Este campo é obrigatório.';
        }

        // Validações específicas por tipo de campo
        switch (field.id) {
            case 'setor_id':
                if (field.options.length <= 1) {
                    isValid = false;
                    errorMessage = 'Não há setores ativos disponíveis.';
                }
                break;

            case 'tipo_servico':
                if (field.disabled) {
                    isValid = false;
                    errorMessage = 'Selecione primeiro o setor.';
                }
                break;

            case 'solicitante':
                if (field.value.trim().length < 3) {
                    isValid = false;
                    errorMessage = 'O nome do solicitante deve ter pelo menos 3 caracteres.';
                }
                break;

            case 'descricao':
                if (field.value.trim().length < 10) {
                    isValid = false;
                    errorMessage = 'A descrição deve ter pelo menos 10 caracteres.';
                }
                break;

            case 'outro_tipo_servico':
                if (field.value.trim() === '' && field.hasAttribute('required')) {
                    isValid = false;
                    errorMessage = 'Por favor, especifique o tipo de serviço.';
                }
                break;

            case 'email_origem':
                if (field.value.trim() !== '') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(field.value.trim())) {
                        isValid = false;
                        errorMessage = 'Por favor, informe um email válido.';
                    }
                }
                break;
        }

        // Atualiza as classes e o feedback
        if (isValid) {
            if (field.value.trim() !== '') {
                field.classList.add('is-valid');

                if (feedbackElement && feedbackElement.classList.contains('chamados-form-feedback')) {
                    feedbackElement.textContent = 'Informações registradas corretamente';
                    feedbackElement.classList.add('valid-feedback');
                }
            }
        } else {
            field.classList.add('is-invalid');

            if (feedbackElement && feedbackElement.classList.contains('chamados-form-feedback')) {
                feedbackElement.textContent = errorMessage;
                feedbackElement.classList.add('invalid-feedback');
            }
        }

        return isValid;
    }

    /**
     * Configura o contador de caracteres para a descrição
     */
    function setupCharacterCounter() {
        const descricaoTextarea = document.getElementById('descricao');
        const contadorElement = document.querySelector('.chamados-form-contador');

        if (!descricaoTextarea || !contadorElement) return;

        // Atualiza o contador inicialmente
        updateCharacterCount(descricaoTextarea, contadorElement);

        // Adiciona evento para atualizar o contador
        descricaoTextarea.addEventListener('input', function () {
            updateCharacterCount(this, contadorElement);
        });
    }

    /**
     * Atualiza o contador de caracteres
     * @param {HTMLElement} textarea - O textarea
     * @param {HTMLElement} counter - O elemento contador
     */
    function updateCharacterCount(textarea, counter) {
        const count = textarea.value.length;
        counter.textContent = count + ' caractere' + (count !== 1 ? 's' : '');

        // Muda a cor do contador conforme o tamanho do texto
        if (count < 10) {
            counter.style.color = 'var(--chamado-danger)';
        } else if (count > 500) {
            counter.style.color = 'var(--chamado-warning)';
        } else {
            counter.style.color = 'var(--chamado-gray)';
        }
    }

    /**
     * Adiciona efeitos de hover ao card
     */
    function setupCardHoverEffects() {
        const card = document.querySelector('.chamados-form-card');

        if (card) {
            card.addEventListener('mouseenter', function () {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 12px 30px rgba(0, 0, 0, 0.1)';
            });

            card.addEventListener('mouseleave', function () {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'var(--chamado-box-shadow)';
            });
        }
    }

    /**
     * Configura dicas contextuais para ambiente hospitalar
     * @param {Object} tiposServicoPorSetor - Mapeamento de tipos de serviço por setor
     */
    function setupHospitalTips(tiposServicoPorSetor) {
        const tipContainer = document.querySelector('.chamados-form-tip-container');

        if (!tipContainer) return;

        // Dicas específicas para cada setor
        const setorTips = {
            'default': 'Forneça informações detalhadas sobre sua solicitação para que possamos atendê-lo mais rapidamente. Quanto mais específico for, melhor será o atendimento.',
            'Enfermagem': 'Informe detalhes sobre a necessidade de assistência de enfermagem, incluindo sintomas, urgência e qualquer informação relevante sobre o paciente.',
            'Limpeza': 'Especifique o que precisa ser limpo, a localização exata e se há materiais ou substâncias que exijam cuidados especiais.',
            'Manutenção': 'Descreva o problema com detalhes: tipo de falha, localização, quando começou e qualquer informação que possa ajudar na resolução.',
            'Nutrição': 'Informe detalhes sobre a dieta, restrições alimentares, preferências e horários desejados para as refeições.',
            'TI': 'Especifique qual equipamento apresenta problema, os sintomas específicos, quando começou a falhar e o que já foi tentado.',
            'Hotelaria': 'Descreva suas necessidades de conforto, incluindo itens específicos, quantidade e urgência da solicitação.',
            'Farmácia': 'Forneça informações sobre medicamentos, incluindo nome, dosagem e qualquer detalhe relevante sobre a prescrição.',
            'Segurança': 'Descreva a situação de segurança com detalhes, incluindo localização, pessoas envolvidas e nível de urgência.'
        };

        // Dicas específicas para cada tipo de serviço
        const servicoTips = {
            'default': 'Forneça informações detalhadas sobre sua solicitação para que possamos atendê-lo mais rapidamente.',
            'Assistência ao Paciente': 'Descreva o tipo de assistência necessária, incluindo detalhes sobre o estado do paciente e urgência.',
            'Monitoramento': 'Especifique quais sinais vitais precisam ser monitorados e com qual frequência.',
            'Troca de Curativos': 'Informe a localização do curativo, quando foi feito pela última vez e qualquer orientação médica específica.',
            'Administração de Medicamentos': 'Mencione o nome do medicamento, dosagem, via de administração e horário prescrito.',
            'Limpeza de Quarto': 'Especifique quais áreas precisam de atenção especial e se há algum material que requer cuidados específicos.',
            'Limpeza Emergencial': 'Descreva o que foi derramado, a localização exata e se há risco para o paciente ou equipe.',
            'Troca de Roupas de Cama': 'Informe se é uma troca de rotina ou devido a alguma necessidade específica.',
            'Manutenção Elétrica': 'Descreva o problema elétrico, se há equipamentos afetados e se representa algum risco.',
            'Manutenção Hidráulica': 'Especifique se há vazamento, entupimento ou outro problema, e a localização exata.',
            'Ar Condicionado': 'Informe se o problema é relacionado à temperatura, ruído ou se o equipamento não está funcionando.',
            'Refeição Regular': 'Confirme o horário desejado para a refeição e qualquer preferência alimentar.',
            'Dieta Especial': 'Detalhe as restrições alimentares, alergias ou necessidades dietéticas específicas.',
            'Suporte a Computadores': 'Descreva o problema com o computador, incluindo mensagens de erro e o que já foi tentado.',
            'Problemas com TV': 'Especifique se a TV não liga, não tem imagem, não tem som ou apresenta outro problema.',
            'Internet/WiFi': 'Informe se o problema é de conexão, velocidade ou se o sinal não está disponível.',
            'Itens de Conforto': 'Especifique quais itens são necessários (travesseiros, cobertores, etc.) e a quantidade.'
        };

        // Cria a dica inicial
        createTip(setorTips['default']);

        /**
         * Atualiza as dicas contextuais com base no setor selecionado
         * @param {string} setorNome - Nome do setor selecionado
         */
        window.updateContextualTipsBySetor = function (setorNome) {
            // Limpa as dicas existentes
            tipContainer.innerHTML = '';

            // Obtém a dica para o setor selecionado ou usa a dica padrão
            let tipText = setorTips[setorNome] || setorTips['default'];

            // Cria a nova dica
            createTip(tipText);

            // Atualiza o placeholder da descrição
            const descricaoTextarea = document.getElementById('descricao');
            if (descricaoTextarea) {
                descricaoTextarea.placeholder = tipText;
            }
        };

        /**
         * Atualiza as dicas contextuais com base no tipo de serviço selecionado
         * @param {string} tipoServico - Tipo de serviço selecionado
         */
        window.updateContextualTips = function (tipoServico) {
            // Limpa as dicas existentes
            tipContainer.innerHTML = '';

            // Extrai o valor real do tipo de serviço (remove a descrição entre parênteses)
            const tipoServicoBase = tipoServico.split('(')[0].trim();

            // Obtém a dica para o tipo de serviço selecionado ou usa a dica padrão
            let tipText = servicoTips[tipoServicoBase] || servicoTips['default'];

            // Cria a nova dica
            createTip(tipText);

            // Atualiza o placeholder da descrição
            const descricaoTextarea = document.getElementById('descricao');
            if (descricaoTextarea) {
                descricaoTextarea.placeholder = tipText;
            }
        };

        /**
         * Cria um elemento de dica
         * @param {string} text - O texto da dica
         */
        function createTip(text) {
            const tip = document.createElement('div');
            tip.className = 'chamados-form-tip';
            tip.innerHTML = `
                <i class="fas fa-lightbulb"></i>
                <div class="chamados-form-tip-content">${text}</div>
            `;

            // Adiciona a dica ao container
            tipContainer.appendChild(tip);

            // Adiciona animação de entrada
            tip.style.opacity = '0';
            tip.style.transform = 'translateY(-10px)';

            setTimeout(() => {
                tip.style.transition = 'all 0.3s ease';
                tip.style.opacity = '1';
                tip.style.transform = 'translateY(0)';
            }, 10);
        }
    }

    /**
     * Configura o comportamento do formulário
     */
    function setupFormBehavior() {
        const form = document.querySelector('.chamados-form-formulario');
        if (!form) return;

        // Adiciona máscara para o campo de quarto/leito
        const quartoLeitoInput = document.getElementById('quarto_leito');
        if (quartoLeitoInput) {
            quartoLeitoInput.addEventListener('input', function () {
                // Formata o texto para o padrão de quarto/leito (ex: 101/A)
                let value = this.value.replace(/[^0-9A-Za-z\/]/g, '');

                // Adiciona a barra se não existir e houver números
                if (value.length > 0 && !value.includes('/') && /\d/.test(value)) {
                    const numeros = value.match(/\d+/)[0];
                    const letras = value.replace(numeros, '');

                    if (letras) {
                        value = numeros + '/' + letras;
                    }
                }

                this.value = value;
            });
        }

        // Adiciona confirmação antes de cancelar o formulário
        const cancelarBtn = document.querySelector('.chamados-form-btn-cancelar');
        if (cancelarBtn) {
            cancelarBtn.addEventListener('click', function (e) {
                // Verifica se algum campo foi preenchido
                const inputs = form.querySelectorAll('input, select, textarea');
                let formPreenchido = false;

                inputs.forEach(input => {
                    if (input.type === 'select-one') {
                        if (input.selectedIndex > 0) {
                            formPreenchido = true;
                        }
                    } else if (input.value.trim() !== '') {
                        formPreenchido = true;
                    }
                });

                // Se o formulário foi preenchido, pede confirmação
                if (formPreenchido) {
                    if (!confirm('Tem certeza que deseja cancelar? Todas as informações não salvas serão perdidas.')) {
                        e.preventDefault();
                    }
                }
            });
        }
    }

    /**
     * Função para adicionar sugestões de descrição
     * @param {Array} sugestoes - Lista de sugestões de descrição
     */
    function adicionarSugestoesDescricao(sugestoes) {
        if (!sugestoes || !Array.isArray(sugestoes) || sugestoes.length === 0) {
            return;
        }

        const container = document.getElementById('sugestoesDescricao');
        if (!container) return;

        // Cria o container para as sugestões
        const wrapper = document.createElement('div');
        wrapper.className = 'chamados-form-sugestoes-descricao';

        // Adiciona o título
        const titulo = document.createElement('small');
        titulo.innerHTML = '<i class="fas fa-lightbulb"></i> Exemplos de solicitações comuns:';
        titulo.style.display = 'block';
        titulo.style.marginBottom = '0.75rem';
        titulo.style.color = 'var(--chamado-primary)';
        titulo.style.fontWeight = '500';
        wrapper.appendChild(titulo);

        // Adiciona as sugestões
        const select = document.createElement('select');
        select.className = 'chamados-form-select-sugestao';

        // Adiciona a opção padrão
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Selecione um exemplo para preencher automaticamente...';
        select.appendChild(defaultOption);

        // Adiciona as opções de sugestão
        sugestoes.forEach((sugestao, index) => {
            const option = document.createElement('option');
            option.value = index.toString();
            option.textContent = sugestao.substring(0, 50) + (sugestao.length > 50 ? '...' : '');
            select.appendChild(option);
        });

        // Adiciona o evento de mudança
        select.addEventListener('change', function () {
            if (this.value !== '') {
                const index = parseInt(this.value);
                const descricaoTextarea = document.getElementById('descricao');
                if (descricaoTextarea) {
                    descricaoTextarea.value = sugestoes[index];

                    // Atualiza o contador de caracteres
                    const contadorElement = document.querySelector('.chamados-form-contador');
                    if (contadorElement) {
                        updateCharacterCount(descricaoTextarea, contadorElement);
                    }

                    // Valida o campo
                    validateField(descricaoTextarea);

                    // Mostra uma mensagem de sucesso
                    showToast('Exemplo aplicado com sucesso!', 'success');

                    this.selectedIndex = 0; // Reseta o select

                    // Foca no campo de descrição
                    descricaoTextarea.focus();
                }
            }
        });

        wrapper.appendChild(select);

        // Adiciona o wrapper ao container
        container.appendChild(wrapper);
    }
}

// Exporta a função de inicialização para uso global
window.initChamadosForm = initChamadosForm;