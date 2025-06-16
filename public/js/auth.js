document.addEventListener('DOMContentLoaded', function () {
    // Toggle de visibilidade da senha
    const toggleButtons = document.querySelectorAll('.password-toggle');
    if (toggleButtons) {
        toggleButtons.forEach(button => {
            button.addEventListener('click', function () {
                const input = this.closest('.input-group').querySelector('input');
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                    this.setAttribute('aria-label', 'Ocultar senha');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                    this.setAttribute('aria-label', 'Mostrar senha');
                }
            });
        });
    }

    // Efeito de foco nos inputs
    const inputs = document.querySelectorAll('.input-group input, .input-group select');
    inputs.forEach(input => {
        // Verificar se já tem valor ao carregar
        if (input.value) {
            input.classList.add('has-value');
        }

        // Adicionar classe quando tiver valor
        input.addEventListener('input', function () {
            if (this.value) {
                this.classList.add('has-value');
            } else {
                this.classList.remove('has-value');
            }
        });
    });

    // Validação do formulário
    const loginForm = document.getElementById('loginForm');
    const loginButton = document.getElementById('loginButton');

    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            // Desabilita o botão para evitar múltiplos envios
            loginButton.disabled = true;
            loginButton.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Entrando...';

            // Continua com o envio do formulário
            return true;
        });
    }

    // Validação do formulário de recuperação
    const recoveryForm = document.getElementById('recoveryForm');
    const recoveryButton = document.getElementById('recoveryButton');

    if (recoveryForm) {
        recoveryForm.addEventListener('submit', function (e) {
            // Desabilita o botão para evitar múltiplos envios
            recoveryButton.disabled = true;
            recoveryButton.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Enviando...';

            // Continua com o envio do formulário
            return true;
        });
    }

    // Foco automático no primeiro campo
    const emailInput = document.getElementById('email');
    if (emailInput) {
        setTimeout(() => {
            emailInput.focus();
        }, 500); // Pequeno atraso para garantir que as animações terminem
    }
});