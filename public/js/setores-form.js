/**
 * setores-form.js - Funcionalidades para o formulário de setores
 */

document.addEventListener('DOMContentLoaded', function () {
    // Verifica se estamos na página do formulário
    const form = document.querySelector('.setor-form');

    if (!form) {
        console.log('Não estamos na página de formulário de setores. Script setores-form.js não será executado.');
        return; // Sai da função se não estiver na página correta
    }

    console.log('Script setores-form.js inicializado com sucesso.');

    // Inicializa os tooltips (se estiver usando Bootstrap)
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        if (tooltips && tooltips.length > 0) {
            tooltips.forEach(tooltip => {
                if (tooltip) {
                    new bootstrap.Tooltip(tooltip);
                }
            });
        }
    }

    // Validação do formulário
    form.addEventListener('submit', function (event) {
        let isValid = true;

        // Validação do campo nome
        const nomeInput = document.getElementById('nome');
        if (nomeInput && !nomeInput.value.trim()) {
            isValid = false;
            showError(nomeInput, 'O nome do setor é obrigatório');
        } else if (nomeInput) {
            clearError(nomeInput);
        }

        // Se o formulário não for válido, impede o envio
        if (!isValid) {
            event.preventDefault();
        }
    });

    // Limpa erros quando o usuário digita
    const inputs = form.querySelectorAll('input, textarea');
    if (inputs && inputs.length > 0) {
        inputs.forEach(input => {
            if (input) {
                input.addEventListener('input', function () {
                    clearError(this);
                });
            }
        });
    }

    // Função para mostrar mensagem de erro
    function showError(input, message) {
        if (!input) return;

        // Remove qualquer mensagem de erro existente
        clearError(input);

        // Adiciona a classe de erro ao input
        input.classList.add('input-error');

        // Cria e adiciona a mensagem de erro
        const errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        errorElement.textContent = message;

        // Insere após o input ou seu container
        const parent = input.closest('.input-with-icon') || input.parentNode;
        if (parent) {
            parent.insertAdjacentElement('afterend', errorElement);
        }
    }

    // Função para limpar mensagem de erro
    function clearError(input) {
        if (!input) return;

        // Remove a classe de erro
        input.classList.remove('input-error');

        // Remove a mensagem de erro se existir
        const parent = input.closest('.input-with-icon') || input.parentNode;
        if (parent && parent.nextElementSibling) {
            const errorElement = parent.nextElementSibling;
            if (errorElement && errorElement.classList.contains('error-message')) {
                errorElement.remove();
            }
        }
    }

    // Contador de caracteres para o campo de descrição
    const descricaoTextarea = document.getElementById('descricao');
    if (descricaoTextarea) {
        const maxLength = 500; // Defina o limite máximo de caracteres

        // Cria o elemento contador
        const counterElement = document.createElement('div');
        counterElement.className = 'character-counter';
        counterElement.textContent = `0/${maxLength} caracteres`;

        // Adiciona após a dica do formulário
        const formGroup = descricaoTextarea.closest('.form-group');
        if (formGroup) {
            const formHint = formGroup.querySelector('.form-hint');
            if (formHint) {
                formHint.insertAdjacentElement('afterend', counterElement);
            } else {
                formGroup.appendChild(counterElement);
            }

            // Atualiza o contador quando o usuário digita
            descricaoTextarea.addEventListener('input', function () {
                const currentLength = this.value.length;
                counterElement.textContent = `${currentLength}/${maxLength} caracteres`;

                // Adiciona classe de aviso quando se aproxima do limite
                if (currentLength > maxLength * 0.8 && currentLength <= maxLength) {
                    counterElement.className = 'character-counter warning';
                }
                // Adiciona classe de erro quando ultrapassa o limite
                else if (currentLength > maxLength) {
                    counterElement.className = 'character-counter error';
                }
                // Remove classes quando está dentro do limite
                else {
                    counterElement.className = 'character-counter';
                }
            });

            // Dispara o evento input para atualizar o contador inicialmente
            const event = new Event('input');
            descricaoTextarea.dispatchEvent(event);
        }
    }

    // Melhoria na experiência do toggle switch
    const toggleSwitch = document.getElementById('ativo');
    if (toggleSwitch) {
        const toggleLabel = toggleSwitch.nextElementSibling;
        if (toggleLabel && toggleLabel.classList.contains('toggle-label')) {
            toggleLabel.addEventListener('keydown', function (e) {
                // Permite ativar/desativar o toggle com a tecla espaço ou enter
                if (e.key === ' ' || e.key === 'Enter') {
                    e.preventDefault();
                    toggleSwitch.checked = !toggleSwitch.checked;

                    // Dispara o evento change para qualquer listener
                    const event = new Event('change');
                    toggleSwitch.dispatchEvent(event);
                }
            });

            // Torna o label focável
            toggleLabel.setAttribute('tabindex', '0');
            toggleLabel.setAttribute('role', 'switch');
            toggleLabel.setAttribute('aria-checked', toggleSwitch.checked);

            // Atualiza o atributo aria-checked quando o estado muda
            toggleSwitch.addEventListener('change', function () {
                toggleLabel.setAttribute('aria-checked', this.checked);
            });
        }
    }
});