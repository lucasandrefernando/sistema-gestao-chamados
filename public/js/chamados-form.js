/**
 * chamados-form.js - Script específico para a página de formulário de chamados
 */

document.addEventListener('DOMContentLoaded', function () {
    // Configura o campo de outro tipo de serviço
    setupOutroTipoServico();

    // Adiciona validação aos campos do formulário
    setupFormValidation();

    // Configura o contador de caracteres para a descrição
    setupCharacterCounter();

    // Adiciona efeitos de hover ao card
    setupCardHoverEffects();

    // Configura o comportamento do formulário
    setupFormBehavior();
});

/**
 * Configura o campo de outro tipo de serviço
 */
function setupOutroTipoServico() {
    const tipoServicoSelect = document.getElementById('tipo_servico');
    const outroTipoServico = document.getElementById('outroTipoServico');
    const outroTipoServicoInput = document.getElementById('outro_tipo_servico');

    if (!tipoServicoSelect || !outroTipoServico || !outroTipoServicoInput) return;

    // Verifica o valor inicial
    if (tipoServicoSelect.value === 'outro') {
        outroTipoServico.style.display = 'block';
        outroTipoServicoInput.setAttribute('required', 'required');
    } else {
        outroTipoServico.style.display = 'none';
        outroTipoServicoInput.removeAttribute('required');
    }

    // Adiciona o evento de mudança
    tipoServicoSelect.addEventListener('change', function () {
        if (this.value === 'outro') {
            outroTipoServico.style.display = 'block';
            outroTipoServicoInput.setAttribute('required', 'required');

            // Adiciona animação
            outroTipoServico.style.animation = 'fadeIn 0.3s ease';

            // Foca no campo
            setTimeout(() => {
                outroTipoServicoInput.focus();
            }, 300);
        } else {
            outroTipoServico.style.display = 'none';
            outroTipoServicoInput.removeAttribute('required');
            outroTipoServicoInput.value = '';
        }

        // Valida o campo
        validateField(tipoServicoSelect);
    });

    // Adiciona validação ao campo de outro tipo de serviço
    outroTipoServicoInput.addEventListener('input', function () {
        if (tipoServicoSelect.value === 'outro') {
            validateField(outroTipoServicoInput);
        }
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
            validateField(this);
        });
    });

    // Adiciona validação ao enviar o formulário
    form.addEventListener('submit', function (e) {
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

        // Impede o envio se o formulário for inválido
        if (!isValid) {
            e.preventDefault();

            // Rola até o primeiro campo inválido
            const firstInvalidField = form.querySelector('.is-invalid');
            if (firstInvalidField) {
                firstInvalidField.focus();
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } else {
            // Adiciona efeito de loading ao botão de salvar
            const submitBtn = form.querySelector('.chamados-form-btn-salvar');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
                submitBtn.disabled = true;

                // Restaura o botão após 2 segundos (para demonstração)
                // Em produção, isso seria tratado pelo servidor
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 2000);
            }
        }
    });
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
    }

    // Atualiza as classes e o feedback
    if (isValid) {
        if (field.value.trim() !== '') {
            field.classList.add('is-valid');

            if (feedbackElement && feedbackElement.classList.contains('chamados-form-feedback')) {
                feedbackElement.textContent = 'Parece bom!';
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
        counter.style.color = 'var(--chamados-form-danger)';
    } else if (count > 500) {
        counter.style.color = 'var(--chamados-form-warning)';
    } else {
        counter.style.color = 'var(--chamados-form-gray)';
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
            this.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.1)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'var(--chamados-form-box-shadow)';
        });
    }
}

/**
 * Configura o comportamento do formulário
 */
function setupFormBehavior() {
    const form = document.querySelector('.chamados-form-formulario');
    const tipoServicoSelect = document.getElementById('tipo_servico');
    const outroTipoServicoInput = document.getElementById('outro_tipo_servico');

    if (!form || !tipoServicoSelect || !outroTipoServicoInput) return;

    // Manipula o envio do formulário para o caso de "outro tipo de serviço"
    form.addEventListener('submit', function (e) {
        if (tipoServicoSelect.value === 'outro' && outroTipoServicoInput.value.trim() !== '') {
            e.preventDefault();

            // Cria uma nova opção com o valor personalizado
            const novaOpcao = document.createElement('option');
            novaOpcao.value = outroTipoServicoInput.value.trim();
            novaOpcao.textContent = outroTipoServicoInput.value.trim();
            novaOpcao.selected = true;

            // Adiciona a nova opção ao select
            tipoServicoSelect.appendChild(novaOpcao);

            // Envia o formulário
            setTimeout(() => {
                this.submit();
            }, 10);
        }
    });

    // Adiciona sugestões de autocompletar para o campo de solicitante
    const solicitanteInput = document.getElementById('solicitante');
    if (solicitanteInput) {
        // Esta é uma implementação básica
        // Em produção, você usaria uma biblioteca de autocompletar ou uma API
        solicitanteInput.addEventListener('focus', function () {
            // Adiciona uma classe para indicar o foco
            this.classList.add('chamados-form-input-focus');
        });

        solicitanteInput.addEventListener('blur', function () {
            // Remove a classe de foco
            this.classList.remove('chamados-form-input-focus');
        });
    }

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
 * Função para preencher automaticamente o campo de solicitante
 * Pode ser usada se houver uma lista de solicitantes frequentes
 */
function preencherSolicitanteFrequente(nome) {
    const solicitanteInput = document.getElementById('solicitante');
    if (solicitanteInput) {
        solicitanteInput.value = nome;
        validateField(solicitanteInput);
    }
}

/**
 * Função para adicionar botões de solicitantes frequentes
 * Pode ser implementada se necessário
 */
function adicionarBotoesSolicitantesFrequentes(solicitantesFrequentes) {
    if (!solicitantesFrequentes || !Array.isArray(solicitantesFrequentes) || solicitantesFrequentes.length === 0) {
        return;
    }

    const solicitanteInput = document.getElementById('solicitante');
    if (!solicitanteInput) return;

    // Cria o container para os botões
    const container = document.createElement('div');
    container.className = 'chamados-form-solicitantes-frequentes';
    container.style.display = 'flex';
    container.style.flexWrap = 'wrap';
    container.style.gap = '0.5rem';
    container.style.marginTop = '0.5rem';

    // Adiciona o título
    const titulo = document.createElement('small');
    titulo.textContent = 'Solicitantes frequentes:';
    titulo.style.width = '100%';
    titulo.style.color = 'var(--chamados-form-gray)';
    container.appendChild(titulo);

    // Adiciona os botões
    solicitantesFrequentes.forEach(nome => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'chamados-form-btn-solicitante';
        btn.textContent = nome;
        btn.style.padding = '0.25rem 0.5rem';
        btn.style.fontSize = '0.75rem';
        btn.style.backgroundColor = 'var(--chamados-form-primary-light)';
        btn.style.color = 'var(--chamados-form-primary)';
        btn.style.border = 'none';
        btn.style.borderRadius = 'var(--chamados-form-border-radius)';
        btn.style.cursor = 'pointer';
        btn.style.transition = 'var(--chamados-form-transition)';

        btn.addEventListener('mouseenter', function () {
            this.style.backgroundColor = 'var(--chamados-form-primary)';
            this.style.color = 'var(--chamados-form-white)';
        });

        btn.addEventListener('mouseleave', function () {
            this.style.backgroundColor = 'var(--chamados-form-primary-light)';
            this.style.color = 'var(--chamados-form-primary)';
        });

        btn.addEventListener('click', function () {
            preencherSolicitanteFrequente(nome);
        });

        container.appendChild(btn);
    });

    // Adiciona o container após o campo de solicitante
    const feedbackElement = solicitanteInput.nextElementSibling;
    if (feedbackElement) {
        solicitanteInput.parentNode.insertBefore(container, feedbackElement.nextSibling);
    } else {
        solicitanteInput.parentNode.appendChild(container);
    }
}

/**
 * Função para adicionar sugestões de descrição
 * Pode ser implementada se necessário
 */
function adicionarSugestoesDescricao(sugestoes) {
    if (!sugestoes || !Array.isArray(sugestoes) || sugestoes.length === 0) {
        return;
    }

    const descricaoTextarea = document.getElementById('descricao');
    if (!descricaoTextarea) return;

    // Cria o container para as sugestões
    const container = document.createElement('div');
    container.className = 'chamados-form-sugestoes-descricao';
    container.style.marginTop = '0.5rem';

    // Adiciona o título
    const titulo = document.createElement('small');
    titulo.textContent = 'Sugestões de descrição:';
    titulo.style.display = 'block';
    titulo.style.marginBottom = '0.5rem';
    titulo.style.color = 'var(--chamados-form-gray)';
    container.appendChild(titulo);

    // Adiciona as sugestões
    const select = document.createElement('select');
    select.className = 'chamados-form-select-sugestao';
    select.style.width = '100%';
    select.style.padding = '0.5rem';

    // Adiciona a opção padrão
    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = 'Selecione uma sugestão...';
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
            descricaoTextarea.value = sugestoes[index];
            updateCharacterCount(descricaoTextarea, document.querySelector('.chamados-form-contador'));
            validateField(descricaoTextarea);
            this.selectedIndex = 0; // Reseta o select
        }
    });

    container.appendChild(select);

    // Adiciona o container após o contador de caracteres
    const contadorElement = document.querySelector('.chamados-form-contador');
    if (contadorElement) {
        contadorElement.parentNode.insertBefore(container, contadorElement.nextSibling);
    } else {
        descricaoTextarea.parentNode.appendChild(container);
    }
}