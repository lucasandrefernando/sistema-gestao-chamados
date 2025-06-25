/**
 * setores-form-v2.js
 * Script para o formulário de criação/edição de setores
 * 
 * @version 2.0
 * @author Desenvolvedor
 */

document.addEventListener('DOMContentLoaded', function () {
    // Verifica se estamos na página do formulário de setores
    const setorForm = document.querySelector('.setor-form-v2');
    if (!setorForm) return;

    // Inicializa os componentes do formulário
    initFormValidation();
    initStatusToggle();
    initAlertDismiss();
    initCancelButton();
    initMasks();

    console.log('Script setores-form-v2.js inicializado com sucesso.');
});

/**
 * Inicializa a validação do formulário
 */
function initFormValidation() {
    const form = document.getElementById('setorForm');
    if (!form) return;

    form.addEventListener('submit', function (event) {
        let isValid = true;

        // Validação do nome (obrigatório)
        const nomeInput = document.getElementById('nome');
        if (nomeInput && nomeInput.value.trim() === '') {
            showError(nomeInput, 'O nome do setor é obrigatório');
            isValid = false;
        } else if (nomeInput) {
            showSuccess(nomeInput);
        }

        // Validação do email (formato válido, se preenchido)
        const emailInput = document.getElementById('email');
        if (emailInput && emailInput.value.trim() !== '') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailInput.value.trim())) {
                showError(emailInput, 'Formato de email inválido');
                isValid = false;
            } else {
                showSuccess(emailInput);
            }
        }

        // Validação do telefone (formato válido, se preenchido)
        const telefoneInput = document.getElementById('telefone');
        if (telefoneInput && telefoneInput.value.trim() !== '') {
            const telefoneRegex = /^$\d{2}$ \d{4,5}-\d{4}$/;
            if (!telefoneRegex.test(telefoneInput.value.trim())) {
                showError(telefoneInput, 'Formato de telefone inválido. Use (00) 0000-0000');
                isValid = false;
            } else {
                showSuccess(telefoneInput);
            }
        }

        // Validação do código (sem caracteres especiais)
        const codigoInput = document.getElementById('codigo');
        if (codigoInput && codigoInput.value.trim() !== '') {
            const codigoRegex = /^[A-Za-z0-9\-]+$/;
            if (!codigoRegex.test(codigoInput.value.trim())) {
                showError(codigoInput, 'O código deve conter apenas letras, números e hífen');
                isValid = false;
            } else {
                showSuccess(codigoInput);
            }
        }

        // Se o formulário não for válido, impede o envio
        if (!isValid) {
            event.preventDefault();

            // Rola até o primeiro campo com erro
            const firstErrorField = document.querySelector('.form-control.is-invalid');
            if (firstErrorField) {
                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstErrorField.focus();
            }
        }
    });

    // Validação em tempo real para melhor experiência do usuário
    const formInputs = form.querySelectorAll('.form-control');
    formInputs.forEach(input => {
        input.addEventListener('blur', function () {
            validateField(this);
        });

        input.addEventListener('input', function () {
            // Remove as classes de erro quando o usuário começa a digitar
            this.classList.remove('is-invalid');
            this.classList.remove('is-valid');

            const feedbackElement = this.closest('.form-group').querySelector('.form-feedback');
            if (feedbackElement) {
                feedbackElement.innerHTML = '';
            }
        });
    });
}

/**
 * Valida um campo específico
 * @param {HTMLElement} field Campo a ser validado
 */
function validateField(field) {
    if (!field) return;

    const fieldId = field.id;
    const fieldValue = field.value.trim();

    // Validação específica para cada campo
    switch (fieldId) {
        case 'nome':
            if (fieldValue === '') {
                showError(field, 'O nome do setor é obrigatório');
            } else {
                showSuccess(field);
            }
            break;

        case 'email':
            if (fieldValue !== '') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(fieldValue)) {
                    showError(field, 'Formato de email inválido');
                } else {
                    showSuccess(field);
                }
            } else {
                // Campo não obrigatório, remove qualquer feedback
                field.classList.remove('is-invalid');
                field.classList.remove('is-valid');

                const feedbackElement = field.closest('.form-group').querySelector('.form-feedback');
                if (feedbackElement) {
                    feedbackElement.innerHTML = '';
                }
            }
            break;

        case 'telefone':
            if (fieldValue !== '') {
                const telefoneRegex = /^\(\d{2}\) \d{4,5}-\d{4}$/;
                if (!telefoneRegex.test(fieldValue)) {
                    showError(field, 'Formato de telefone inválido. Use (00) 0000-0000');
                } else {
                    showSuccess(field);
                }
            } else {
                // Campo não obrigatório, remove qualquer feedback
                field.classList.remove('is-invalid');
                field.classList.remove('is-valid');

                const feedbackElement = field.closest('.form-group').querySelector('.form-feedback');
                if (feedbackElement) {
                    feedbackElement.innerHTML = '';
                }
            }
            break;

        case 'codigo':
            if (fieldValue !== '') {
                const codigoRegex = /^[A-Za-z0-9\-]+$/;
                if (!codigoRegex.test(fieldValue)) {
                    showError(field, 'O código deve conter apenas letras, números e hífen');
                } else {
                    showSuccess(field);
                }
            } else {
                // Campo não obrigatório, remove qualquer feedback
                field.classList.remove('is-invalid');
                field.classList.remove('is-valid');

                const feedbackElement = field.closest('.form-group').querySelector('.form-feedback');
                if (feedbackElement) {
                    feedbackElement.innerHTML = '';
                }
            }
            break;
    }
}

/**
 * Mostra mensagem de erro para um campo
 * @param {HTMLElement} field Campo com erro
 * @param {string} message Mensagem de erro
 */
function showError(field, message) {
    field.classList.add('is-invalid');
    field.classList.remove('is-valid');

    const feedbackElement = field.closest('.form-group').querySelector('.form-feedback');
    if (feedbackElement) {
        feedbackElement.innerHTML = `<div class="invalid-feedback">${message}</div>`;
    }
}

/**
 * Mostra mensagem de sucesso para um campo
 * @param {HTMLElement} field Campo válido
 */
function showSuccess(field) {
    field.classList.add('is-valid');
    field.classList.remove('is-invalid');

    const feedbackElement = field.closest('.form-group').querySelector('.form-feedback');
    if (feedbackElement) {
        feedbackElement.innerHTML = `<div class="valid-feedback">Campo válido</div>`;
    }
}

/**
 * Inicializa o toggle de status
 */
function initStatusToggle() {
    const statusToggle = document.getElementById('ativo');
    const statusText = document.getElementById('statusText');

    if (!statusToggle || !statusText) return;

    // Atualiza o texto com base no estado inicial
    updateStatusText();

    // Adiciona evento para atualizar o texto quando o toggle mudar
    statusToggle.addEventListener('change', updateStatusText);

    function updateStatusText() {
        statusText.textContent = statusToggle.checked ? 'Ativo' : 'Inativo';
        statusText.style.color = statusToggle.checked ? 'var(--success-color)' : 'var(--secondary-color)';
    }
}

/**
 * Inicializa a funcionalidade de fechar alertas
 */
function initAlertDismiss() {
    const alertCloseButtons = document.querySelectorAll('.alert-close');

    alertCloseButtons.forEach(button => {
        button.addEventListener('click', function () {
            const alert = this.closest('.alert-error, .alert-success');
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            }
        });
    });
}

/**
 * Inicializa o botão de cancelar
 */
function initCancelButton() {
    const cancelButton = document.getElementById('cancelarBtn');

    if (!cancelButton) return;

    cancelButton.addEventListener('click', function () {
        // Pergunta se o usuário realmente deseja cancelar
        if (confirm('Tem certeza que deseja cancelar? Todas as alterações serão perdidas.')) {
            window.location.href = document.querySelector('a[href*="setores"]').getAttribute('href');
        }
    });
}

/**
 * Inicializa máscaras para campos específicos
 */
function initMasks() {
    const telefoneInput = document.getElementById('telefone');

    if (telefoneInput) {
        // Máscara para telefone
        telefoneInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');

            if (value.length <= 10) {
                // Formato (00) 0000-0000
                value = value.replace(/^(\d{2})(\d{4})(\d{4}).*/, '($1) $2-$3');
            } else {
                // Formato (00) 00000-0000
                value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            }

            e.target.value = value;
        });
    }
}