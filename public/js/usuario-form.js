/**
 * Script para o formulário de usuário
 * Gerencia o toggle de senha, opções de administrador e validação
 */
document.addEventListener('DOMContentLoaded', function () {
    // Toggle de senha
    setupPasswordToggle();

    // Toggle de tipo de administrador
    setupAdminToggle();

    // Animações de entrada
    setupAnimations();

    // Validação de formulário
    setupFormValidation();
});

/**
 * Configura o toggle de visibilidade da senha
 */
function setupPasswordToggle() {
    const togglePassword = document.getElementById('togglePassword');
    const senhaInput = document.getElementById('senha');

    if (togglePassword && senhaInput) {
        togglePassword.addEventListener('click', function () {
            const type = senhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
            senhaInput.setAttribute('type', type);

            // Alterna o ícone
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }
}

/**
 * Configura o toggle de tipo de administrador
 */
function setupAdminToggle() {
    const adminCheckbox = document.getElementById('admin');
    const adminTipoContainer = document.getElementById('adminTipoContainer');

    if (adminCheckbox && adminTipoContainer) {
        // Configuração inicial
        updateAdminTipoVisibility();

        // Evento de mudança
        adminCheckbox.addEventListener('change', function () {
            updateAdminTipoVisibility();
        });

        // Configura os radio buttons para melhor feedback visual
        const radioButtons = document.querySelectorAll('.admin-tipo-card input[type="radio"]');
        radioButtons.forEach(radio => {
            radio.addEventListener('change', function () {
                updateSelectedAdminType();
            });
        });

        // Configura o estado inicial dos radio buttons
        updateSelectedAdminType();
    }

    /**
     * Atualiza a visibilidade do container de tipo de administrador
     */
    function updateAdminTipoVisibility() {
        if (adminCheckbox.checked) {
            adminTipoContainer.style.display = 'block';
            adminTipoContainer.classList.add('animate-fade');
        } else {
            adminTipoContainer.style.display = 'none';
            adminTipoContainer.classList.remove('animate-fade');
        }
    }

    /**
     * Atualiza o estado visual dos cards de tipo de administrador
     */
    function updateSelectedAdminType() {
        const radioButtons = document.querySelectorAll('.admin-tipo-card input[type="radio"]');
        radioButtons.forEach(radio => {
            const card = radio.closest('.admin-tipo-card');
            if (radio.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        });
    }
}

/**
 * Configura animações de entrada
 */
function setupAnimations() {
    // Animação para os grupos de formulário
    const formGroups = document.querySelectorAll('.form-group');
    formGroups.forEach((group, index) => {
        setTimeout(() => {
            group.classList.add('animate-in');
        }, 100 + (index * 50));
    });

    // Animação para a seção de permissões
    const permissionsSection = document.querySelector('.permissions-section');
    if (permissionsSection) {
        setTimeout(() => {
            permissionsSection.classList.add('animate-in');
        }, 300);
    }

    // Animação para os botões de ação
    const formActions = document.querySelector('.form-actions');
    if (formActions) {
        setTimeout(() => {
            formActions.classList.add('animate-in');
        }, 400);
    }
}

/**
 * Configura validação básica do formulário
 */
function setupFormValidation() {
    const form = document.querySelector('.user-form');

    if (form) {
        const inputs = form.querySelectorAll('input[required]');

        form.addEventListener('submit', function (e) {
            let isValid = true;

            // Verifica campos obrigatórios
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    highlightInvalidField(input);
                } else {
                    removeInvalidHighlight(input);
                }
            });

            // Verifica senha (se for criação)
            const senhaInput = document.getElementById('senha');
            if (senhaInput && senhaInput.hasAttribute('required') && senhaInput.value.length < 8) {
                isValid = false;
                highlightInvalidField(senhaInput);

                // Adiciona mensagem de erro
                const existingError = senhaInput.parentElement.parentElement.querySelector('.error-message');
                if (!existingError) {
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message';
                    errorMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> A senha deve ter pelo menos 8 caracteres.';
                    senhaInput.parentElement.parentElement.appendChild(errorMsg);
                }
            }

            // Impede o envio se o formulário for inválido
            if (!isValid) {
                e.preventDefault();

                // Rola até o primeiro campo inválido
                const firstInvalid = form.querySelector('.invalid-field');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
            }
        });

        // Remove destaque de erro quando o usuário começa a digitar
        inputs.forEach(input => {
            input.addEventListener('input', function () {
                removeInvalidHighlight(this);
            });
        });
    }

    /**
     * Destaca um campo inválido
     */
    function highlightInvalidField(input) {
        input.classList.add('invalid-field');
        input.parentElement.classList.add('invalid-group');

        // Adiciona animação de shake
        input.parentElement.classList.add('shake-animation');
        setTimeout(() => {
            input.parentElement.classList.remove('shake-animation');
        }, 500);
    }

    /**
     * Remove o destaque de um campo inválido
     */
    function removeInvalidHighlight(input) {
        input.classList.remove('invalid-field');
        input.parentElement.classList.remove('invalid-group');

        // Remove mensagem de erro se existir
        const errorMsg = input.parentElement.parentElement.querySelector('.error-message');
        if (errorMsg) {
            errorMsg.remove();
        }
    }
}

/**
 * Adiciona classes CSS para animação
 */
function addAnimationClass(element, className, delay = 0) {
    if (element) {
        setTimeout(() => {
            element.classList.add(className);
        }, delay);
    }
}