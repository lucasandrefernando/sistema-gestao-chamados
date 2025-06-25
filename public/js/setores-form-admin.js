/**
 * setores-admin-v2.js
 * Script para o formulário de administração de setores
 * 
 * @version 2.0
 * @author Desenvolvedor
 */

document.addEventListener('DOMContentLoaded', function () {
    // Verifica se estamos na página do formulário de admin de setores
    const adminForm = document.querySelector('.setores-admin-v2');
    if (!adminForm) return;

    // Inicializa os componentes do formulário
    initFormValidation();
    initStatusToggle();

    console.log('Script setores-admin-v2.js inicializado com sucesso.');
});

/**
 * Inicializa a validação do formulário
 */
function initFormValidation() {
    const form = document.getElementById('adminSetorForm');
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