/**
 * Script para o formulário de usuário
 * Gerencia o toggle de senha, opções de administrador e validação
 * VERSÃO OTIMIZADA E INTEGRADA COM SUAS FUNCIONALIDADES BASE - CORRIGIDA PARA CONTEXTO
 */
document.addEventListener('DOMContentLoaded', function () {
    console.log('🚀 Inicializando formulário de usuário (Versão Integrada e Corrigida para Contexto)...');

    // Inicializa o sistema de toasts
    setupToastSystem();

    // Toggle de senha (APRIMORADO PARA MÚLTIPLOS CAMPOS)
    setupPasswordToggle();

    // Verificação de força da senha (AGORA VERIFICA SE ESTÁ NO CONTEXTO CORRETO)
    setupPasswordStrength();

    // Separação de nome e sobrenome (AGORA VERIFICA SE ESTÁ NO CONTEXTO CORRETO)
    setupNameSeparation();

    // Atualização de avatar (AGORA VERIFICA SE ESTÁ NO CONTEXTO CORRETO)
    setupAvatarUpdates();

    // Toggle de tipo de administrador
    setupAdminToggle();

    // Animações de entrada
    setupAnimations();

    // Validação de formulário
    setupFormValidation();

    console.log('✅ Formulário inicializado com sucesso!');
});

/**
 * Sistema de Toast Notifications
 */
function setupToastSystem() {
    if (!document.querySelector('.toast-container')) {
        const container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
}

/**
 * Mostra toast notification
 * @param {string} message - Mensagem a ser exibida no toast.
 * @param {string} type - Tipo de toast ('success', 'error', 'warning', 'info').
 * @param {string} [title=null] - Título opcional para o toast.
 * @param {number} [duration=4000] - Duração do toast em milissegundos.
 */
function showToast(message, type = 'success', title = null, duration = 4000) {
    const container = document.querySelector('.toast-container');
    if (!container) {
        console.error('Container de toasts não encontrado. Chame setupToastSystem() primeiro.');
        return;
    }

    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;

    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };

    const defaultTitles = {
        success: 'Sucesso!',
        error: 'Erro!',
        warning: 'Atenção!',
        info: 'Informação'
    };

    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas ${icons[type]}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">${title || defaultTitles[type]}</div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close">
            <i class="fas fa-times"></i>
        </button>
    `;

    toast.querySelector('.toast-close').addEventListener('click', () => {
        removeToast(toast);
    });

    container.appendChild(toast);

    setTimeout(() => {
        removeToast(toast);
    }, duration);
}

/**
 * Remove toast com animação
 * @param {HTMLElement} toast - O elemento toast a ser removido.
 */
function removeToast(toast) {
    if (toast && toast.parentNode) {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }
}

/**
 * Configura o toggle de visibilidade da senha para múltiplos campos.
 */
function setupPasswordToggle() {
    const toggleButtons = document.querySelectorAll('.toggle-password');

    toggleButtons.forEach(button => {
        button.addEventListener('click', function () {
            const targetId = this.dataset.target;
            const targetInput = document.getElementById(targetId);

            if (targetInput) {
                const type = targetInput.getAttribute('type') === 'password' ? 'text' : 'password';
                targetInput.setAttribute('type', type);

                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            }
        });
    });
}

/**
 * Configura a força da senha em tempo real.
 */
function setupPasswordStrength() {
    // NOVO: Verifica se o formulário de usuário está presente na página
    const userForm = document.querySelector('.user-form');
    const senhaInput = document.getElementById('senha');
    if (!userForm || !senhaInput) { // Só executa se o userForm e o campo senha existirem
        return;
    }

    console.log('🔒 Configurando verificação de força da senha...');

    const strengthContainer = document.createElement('div');
    strengthContainer.className = 'password-strength';
    strengthContainer.innerHTML = `
        <div class="strength-label">Força da senha: <span class="strength-text">Muito fraca</span></div>
        <div class="strength-bar">
            <div class="strength-fill weak"></div>
        </div>
        <div class="strength-requirements">
            <ul>
                <li id="length-req">Pelo menos 8 caracteres</li>
                <li id="uppercase-req">Pelo menos 1 letra maiúscula</li>
                <li id="lowercase-req">Pelo menos 1 letra minúscula</li>
                <li id="number-req">Pelo menos 1 número</li>
                <li id="special-req">Pelo menos 1 caractere especial (!@#$%^&*)</li>
            </ul>
        </div>
    `;

    const senhaGroup = senhaInput.closest('.form-group');
    if (senhaGroup) {
        senhaGroup.appendChild(strengthContainer);
    } else {
        console.warn('Grupo do campo de senha não encontrado para inserir o medidor de força.');
        senhaInput.parentNode.insertBefore(strengthContainer, senhaInput.nextSibling);
    }

    senhaInput.addEventListener('input', function () {
        const password = this.value;

        if (password.length > 0) {
            strengthContainer.classList.add('show');
            checkPasswordStrength(password);
        } else {
            strengthContainer.classList.remove('show');
            senhaInput.classList.remove('valid-field', 'invalid-field');
            _removeFieldMessage(senhaInput);
        }
    });

    senhaInput.addEventListener('focus', function () {
        if (this.value.length > 0) {
            strengthContainer.classList.add('show');
        }
    });

    senhaInput.addEventListener('blur', function () {
        if (!senhaInput.classList.contains('invalid-field')) {
            strengthContainer.classList.remove('show');
        }
    });

    function checkPasswordStrength(password) {
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /\d/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };

        Object.keys(requirements).forEach(req => {
            const element = document.getElementById(`${req}-req`);
            if (element) {
                if (requirements[req]) {
                    element.classList.add('valid');
                } else {
                    element.classList.remove('valid');
                }
            }
        });

        const validCount = Object.values(requirements).filter(Boolean).length;
        const strengthFill = strengthContainer.querySelector('.strength-fill');
        const strengthText = strengthContainer.querySelector('.strength-text');

        strengthFill.className = 'strength-fill';

        if (validCount <= 1) {
            strengthFill.classList.add('weak');
            strengthText.textContent = 'Muito fraca';
            strengthText.style.color = 'var(--danger)';
        } else if (validCount === 2) {
            strengthFill.classList.add('weak');
            strengthText.textContent = 'Fraca';
            strengthText.style.color = 'var(--danger)';
        } else if (validCount === 3) {
            strengthFill.classList.add('fair');
            strengthText.textContent = 'Regular';
            strengthText.style.color = 'var(--warning)';
        } else if (validCount === 4) {
            strengthFill.classList.add('good');
            strengthText.textContent = 'Boa';
            strengthText.style.color = 'var(--info)';
        } else if (validCount === 5) {
            strengthFill.classList.add('strong');
            strengthText.textContent = 'Forte';
            strengthText.style.color = 'var(--success)';
        }

        _validatePasswordField(senhaInput, validCount >= 4);
    }
}

/**
 * Separação automática de nome e sobrenome.
 */
function setupNameSeparation() {
    // NOVO: Verifica se o formulário de usuário está presente na página
    const userForm = document.querySelector('.user-form');
    const nomeInput = document.getElementById('nome');
    const sobrenomeInput = document.getElementById('sobrenome');

    if (!userForm || !nomeInput) { // Só executa se o userForm e o campo nome existirem
        return;
    }

    console.log('👤 Configurando separação de nome e sobrenome...');

    let suggestionTimeout;

    function removeSuggestion() {
        const existingSuggestion = document.querySelector('.name-suggestion');
        if (existingSuggestion) {
            existingSuggestion.remove();
        }
    }

    function showNameSuggestion(firstName, lastName) {
        removeSuggestion();

        const suggestionDiv = document.createElement('div');
        suggestionDiv.className = 'name-suggestion';
        suggestionDiv.innerHTML = `
            <i class="fas fa-lightbulb"></i>
            <span class="suggestion-text">Sugestão: <strong>${firstName}</strong> como nome e <strong>${lastName}</strong> como sobrenome.</span>
            <button type="button" class="btn-apply">Aplicar</button>
            <button type="button" class="btn-dismiss"><i class="fas fa-times"></i></button>
        `;

        const nomeGroup = nomeInput.closest('.form-group');
        if (nomeGroup) {
            nomeGroup.appendChild(suggestionDiv);
        } else {
            console.warn('Grupo do campo nome não encontrado para inserir a sugestão.');
            nomeInput.parentNode.insertBefore(suggestionDiv, nomeInput.nextSibling);
        }

        suggestionDiv.querySelector('.btn-apply').addEventListener('click', () => {
            nomeInput.value = firstName;
            if (sobrenomeInput) {
                sobrenomeInput.value = lastName;
                _validateField(sobrenomeInput);
            }
            _validateField(nomeInput);
            removeSuggestion();
            showToast('Nomes separados com sucesso!', 'success', 'Nomes Aplicados');
        });

        suggestionDiv.querySelector('.btn-dismiss').addEventListener('click', () => {
            removeSuggestion();
        });

        setTimeout(() => {
            removeSuggestion();
        }, 10000);
    }

    nomeInput.addEventListener('input', function () {
        clearTimeout(suggestionTimeout);
        removeSuggestion();

        const fullName = this.value.trim();

        if (fullName.includes(' ') && sobrenomeInput && !sobrenomeInput.value.trim()) {
            suggestionTimeout = setTimeout(() => {
                const parts = fullName.split(' ');
                const firstName = parts[0];
                const lastName = parts.slice(1).join(' ');

                if (firstName && lastName) {
                    showNameSuggestion(firstName, lastName);
                }
            }, 1500);
        }
    });

    if (sobrenomeInput) {
        sobrenomeInput.addEventListener('input', removeSuggestion);
    }
}

/**
 * Atualiza o avatar de preview com as iniciais do nome.
 */
function setupAvatarUpdates() {
    // NOVO: Verifica se o formulário de usuário está presente na página
    const userForm = document.querySelector('.user-form');
    const nomeInput = document.getElementById('nome');
    const sobrenomeInput = document.getElementById('sobrenome');
    const avatarPreview = document.querySelector('.user-avatar-preview .avatar-circle');

    if (!userForm || !nomeInput || !avatarPreview) { // Só executa se o userForm e os elementos existirem
        return;
    }

    console.log('🖼️ Configurando atualização do avatar...');

    function updateAvatar() {
        const nome = nomeInput.value.trim();
        const sobrenome = sobrenomeInput ? sobrenomeInput.value.trim() : '';
        let initials = '?';

        if (nome) {
            const firstInitial = nome.charAt(0).toUpperCase();
            if (sobrenome) {
                const lastInitial = sobrenome.charAt(0).toUpperCase();
                initials = firstInitial + lastInitial;
            } else if (nome.includes(' ')) {
                const parts = nome.split(' ');
                const lastInitial = parts[parts.length - 1].charAt(0).toUpperCase();
                initials = firstInitial + lastInitial;
            } else {
                initials = firstInitial;
            }
        }
        avatarPreview.textContent = initials;
    }

    nomeInput.addEventListener('input', updateAvatar);
    if (sobrenomeInput) {
        sobrenomeInput.addEventListener('input', updateAvatar);
    }

    updateAvatar();
}

/**
 * Configura o toggle de tipo de administrador
 */
function setupAdminToggle() {
    const adminCheckbox = document.getElementById('admin');
    const adminTipoContainer = document.getElementById('adminTipoContainer');

    if (adminCheckbox && adminTipoContainer) {
        updateAdminTipoVisibility();

        adminCheckbox.addEventListener('change', function () {
            updateAdminTipoVisibility();
        });

        const radioButtons = document.querySelectorAll('.admin-tipo-card input[type="radio"]');
        radioButtons.forEach(radio => {
            radio.addEventListener('change', function () {
                updateSelectedAdminType();
            });
        });

        updateSelectedAdminType();
    }

    function updateAdminTipoVisibility() {
        if (adminCheckbox.checked) {
            adminTipoContainer.style.display = 'block';
            adminTipoContainer.classList.add('animate-fade');
        } else {
            adminTipoContainer.style.display = 'none';
            adminTipoContainer.classList.remove('animate-fade');

            const adminTipoSelect = document.querySelector('input[name="admin_tipo"][value="regular"]');
            if (adminTipoSelect) {
                adminTipoSelect.checked = true;
                updateSelectedAdminType();
            }
        }
    }

    function updateSelectedAdminType() {
        const radioButtons = document.querySelectorAll('.admin-tipo-card input[type="radio"]');
        radioButtons.forEach(radio => {
            const card = radio.closest('.admin-tipo-card');
            if (card) {
                if (radio.checked) {
                    card.classList.add('selected');
                } else {
                    card.classList.remove('selected');
                }
            }
        });
    }
}

/**
 * Configura animações de entrada
 */
function setupAnimations() {
    // NOVO: Verifica se o formulário de usuário está presente na página
    const userForm = document.querySelector('.user-form');
    if (!userForm) { // Só executa se o userForm existir
        return;
    }

    const formGroups = document.querySelectorAll('.form-group');
    formGroups.forEach((group, index) => {
        setTimeout(() => {
            group.classList.add('animate-in');
        }, 100 + (index * 50));
    });

    const formSections = document.querySelectorAll('.form-section');
    formSections.forEach((section, index) => {
        setTimeout(() => {
            section.classList.add('animate-in');
        }, 100 + (index * 150));
    });

    const formActions = document.querySelector('.form-actions');
    if (formActions) {
        setTimeout(() => {
            formActions.classList.add('animate-in');
        }, 400);
    }
}

/**
 * Configura validação do formulário
 */
function setupFormValidation() {
    const form = document.querySelector('.user-form');

    if (form) {
        const fieldsToValidate = form.querySelectorAll('input[required], input[type="email"], #senha, #confirmar_senha, #nome, #sobrenome, #cargo');

        fieldsToValidate.forEach(field => {
            field.addEventListener('blur', () => _validateField(field));
            field.addEventListener('input', () => {
                _removeFieldMessage(field);
                field.classList.remove('invalid-field', 'valid-field');
                field.closest('.form-group')?.classList.remove('invalid-group');
            });
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            let formIsValid = true;
            let invalidFieldIds = [];

            fieldsToValidate.forEach(field => {
                if (!_validateField(field)) {
                    formIsValid = false;
                    invalidFieldIds.push(field.id);
                }
            });

            const senhaInput = document.getElementById('senha');
            if (senhaInput && senhaInput.value.length > 0) {
                const strengthFill = document.querySelector('.password-strength .strength-fill');
                if (strengthFill && (!strengthFill.classList.contains('good') && !strengthFill.classList.contains('strong'))) {
                    formIsValid = false;
                    _showFieldError(senhaInput, 'A senha não atende aos critérios de segurança (mínimo "Boa").');
                    _applyInvalidHighlight(senhaInput);
                    invalidFieldIds.push(senhaInput.id);
                }
            } else if (senhaInput && senhaInput.hasAttribute('required') && senhaInput.value.length === 0) {
                formIsValid = false;
                invalidFieldIds.push(senhaInput.id);
            }

            const confirmarSenhaInput = document.getElementById('confirmar_senha');
            if (senhaInput && confirmarSenhaInput) {
                if (senhaInput.value.length > 0 && (confirmarSenhaInput.hasAttribute('required') || confirmarSenhaInput.value.length > 0)) {
                    if (senhaInput.value !== confirmarSenhaInput.value) {
                        formIsValid = false;
                        _showFieldError(confirmarSenhaInput, 'As senhas não coincidem.');
                        _applyInvalidHighlight(confirmarSenhaInput);
                        invalidFieldIds.push(confirmarSenhaInput.id);
                    }
                }
            }

            if (!formIsValid) {
                showToast('Por favor, corrija os campos destacados.', 'error', 'Erro de Validação');
                const firstInvalid = form.querySelector('.invalid-field');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
                return;
            }

            showToast('Formulário enviado com sucesso!', 'success', 'Sucesso!');

            const submitBtn = form.querySelector('.btn-primary[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
                submitBtn.disabled = true;
                submitBtn.classList.add('btn-disabled');

                setTimeout(() => {
                    form.removeEventListener('submit', arguments.callee);
                    form.submit();
                }, 500);
            } else {
                form.removeEventListener('submit', arguments.callee);
                form.submit();
            }
        });

        fieldsToValidate.forEach(field => {
            field.addEventListener('input', function () {
                _removeInvalidHighlight(this);
            });
        });
    }
}

/**
 * Valida um campo específico do formulário.
 * @param {HTMLElement} field - O elemento do campo a ser validado.
 * @returns {boolean} - True se o campo é válido, False caso contrário.
 */
function _validateField(field) {
    _removeInvalidHighlight(field);
    _removeFieldMessage(field);

    const value = field.value.trim();
    const isRequired = field.hasAttribute('required');
    let isValid = true;
    let errorMessage = '';

    if (isRequired && value === '') {
        isValid = false;
        errorMessage = 'Este campo é obrigatório.';
    }

    if (isValid && value !== '') {
        switch (field.id) {
            case 'nome':
                if (value.length < 2) {
                    isValid = false;
                    errorMessage = 'Nome deve ter pelo menos 2 caracteres.';
                } else if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(value)) {
                    isValid = false;
                    errorMessage = 'Nome deve conter apenas letras e espaços.';
                }
                break;
            case 'sobrenome':
                if (value.length < 2) {
                    isValid = false;
                    errorMessage = 'Sobrenome deve ter pelo menos 2 caracteres.';
                } else if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(value)) {
                    isValid = false;
                    errorMessage = 'Sobrenome deve conter apenas letras e espaços.';
                }
                break;
            case 'email':
                if (!_validateEmailFormat(value)) {
                    isValid = false;
                    errorMessage = 'Por favor, insira um email válido.';
                }
                break;
            case 'senha':
                break;
            case 'confirmar_senha':
                const senhaInput = document.getElementById('senha');
                if (senhaInput && senhaInput.value.length > 0 && value !== senhaInput.value) {
                    isValid = false;
                    errorMessage = 'As senhas não coincidem.';
                }
                break;
            case 'cargo':
                if (value.length < 3) {
                    isValid = false;
                    errorMessage = 'Cargo deve ter pelo menos 3 caracteres.';
                }
                break;
        }
    }

    if (isValid && value !== '') {
        field.classList.add('valid-field');
    } else if (!isValid) {
        _applyInvalidHighlight(field);
        _showFieldError(field, errorMessage);
    }

    return isValid;
}

/**
 * Valida o formato de um email.
 * @param {string} email - O email a ser validado.
 * @returns {boolean} - True se o email tem um formato válido, False caso contrário.
 */
function _validateEmailFormat(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Valida o campo de senha com base na força.
 * @param {HTMLElement} field - O campo de senha.
 * @param {boolean} isStrongEnough - True se a senha atende aos critérios mínimos de força.
 */
function _validatePasswordField(field, isStrongEnough) {
    _removeInvalidHighlight(field);
    _removeFieldMessage(field);

    if (field.value.trim() === '') {
        if (field.hasAttribute('required')) {
            _applyInvalidHighlight(field);
            _showFieldError(field, 'Este campo é obrigatório.');
        }
        return;
    }

    if (isStrongEnough) {
        field.classList.add('valid-field');
    } else {
        _applyInvalidHighlight(field);
        _showFieldError(field, 'A senha não atende aos critérios de segurança (mínimo "Boa").');
    }
}

/**
 * Destaca um campo inválido
 * @param {HTMLElement} input - O campo a ser destacado.
 */
function _applyInvalidHighlight(input) {
    input.classList.add('invalid-field');
    input.closest('.form-group')?.classList.add('invalid-group');

    input.closest('.form-group')?.classList.add('shake-animation');
    setTimeout(() => {
        input.closest('.form-group')?.classList.remove('shake-animation');
    }, 500);
}

/**
 * Remove o destaque de um campo inválido
 * @param {HTMLElement} input - O campo do qual remover o destaque.
 */
function _removeInvalidHighlight(input) {
    input.classList.remove('invalid-field');
    input.closest('.form-group')?.classList.remove('invalid-group');
    _removeFieldMessage(input);
}

/**
 * Exibe uma mensagem de erro abaixo de um campo.
 * @param {HTMLElement} field - O campo ao qual a mensagem se refere.
 * @param {string} message - A mensagem de erro a ser exibida.
 */
function _showFieldError(field, message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;

    const parentGroup = field.closest('.form-group');
    const inputGroup = field.closest('.input-group');
    if (parentGroup && inputGroup) {
        inputGroup.insertAdjacentElement('afterend', errorDiv);
    } else if (parentGroup) {
        parentGroup.appendChild(errorDiv);
    } else {
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    }
}

/**
 * Exibe uma mensagem de sucesso abaixo de um campo.
 * @param {HTMLElement} field - O campo ao qual a mensagem se refere.
 * @param {string} message - A mensagem de sucesso a ser exibida.
 */
function _showSuccessMessage(field, message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;

    const parentGroup = field.closest('.form-group');
    const inputGroup = field.closest('.input-group');
    if (parentGroup && inputGroup) {
        inputGroup.insertAdjacentElement('afterend', successDiv);
    } else if (parentGroup) {
        parentGroup.appendChild(successDiv);
    } else {
        field.parentNode.insertBefore(successDiv, field.nextSibling);
    }
}

/**
 * Remove qualquer mensagem de erro ou sucesso associada a um campo.
 * @param {HTMLElement} field - O campo do qual remover as mensagens.
 */
function _removeFieldMessage(field) {
    const parentGroup = field.closest('.form-group');
    if (parentGroup) {
        const existingError = parentGroup.querySelector('.error-message');
        if (existingError) existingError.remove();
        const existingSuccess = parentGroup.querySelector('.success-message');
        if (existingSuccess) existingSuccess.remove();
    } else {
        const nextSibling = field.nextElementSibling;
        if (nextSibling && (nextSibling.classList.contains('error-message') || nextSibling.classList.contains('success-message'))) {
            nextSibling.remove();
        }
    }
}