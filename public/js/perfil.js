/**
 * Perfil do Usuário - JavaScript Profissional
 */
class PerfilManager {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.initPasswordStrength();
        this.initFormValidation();
        this.autoHideAlerts();
    }

    bindEvents() {
        // Formulário de dados
        const formDados = document.getElementById('formDados');
        if (formDados) {
            formDados.addEventListener('submit', this.handleFormDados.bind(this));
        }

        // Formulário de senha
        const formSenha = document.getElementById('formSenha');
        if (formSenha) {
            formSenha.addEventListener('submit', this.handleFormSenha.bind(this));
        }

        // Formulário de desativação
        const formDesativar = document.getElementById('formDesativar');
        if (formDesativar) {
            formDesativar.addEventListener('submit', this.handleFormDesativar.bind(this));
        }

        // Botões de toggle de senha
        document.querySelectorAll('.password-toggle').forEach(btn => {
            btn.addEventListener('click', this.togglePassword.bind(this));
        });
    }

    initPasswordStrength() {
        const novaSenhaInput = document.getElementById('nova_senha');
        if (!novaSenhaInput) return;

        novaSenhaInput.addEventListener('input', (e) => {
            this.checkPasswordStrength(e.target.value);
        });
    }

    checkPasswordStrength(password) {
        const strengthFill = document.querySelector('.strength-fill');
        const strengthText = document.querySelector('.strength-text');

        if (!strengthFill || !strengthText) return;

        let score = 0;
        let feedback = '';
        let className = '';

        // Critérios de força
        if (password.length >= 8) score++;
        if (password.match(/[a-z]/)) score++;
        if (password.match(/[A-Z]/)) score++;
        if (password.match(/[0-9]/)) score++;
        if (password.match(/[^a-zA-Z0-9]/)) score++;

        // Determinar força
        if (password.length === 0) {
            feedback = 'Digite sua senha';
            className = '';
        } else if (score <= 2) {
            feedback = 'Fraca';
            className = 'strength-weak';
        } else if (score === 3) {
            feedback = 'Razoável';
            className = 'strength-fair';
        } else if (score === 4) {
            feedback = 'Boa';
            className = 'strength-good';
        } else {
            feedback = 'Forte';
            className = 'strength-strong';
        }

        // Atualizar visual
        strengthFill.className = `strength-fill ${className}`;
        strengthText.innerHTML = `<i class="fas fa-shield-alt"></i> Força: ${feedback}`;
    }

    initFormValidation() {
        // Validação de confirmação de senha
        const novaSenha = document.getElementById('nova_senha');
        const confirmarSenha = document.getElementById('confirmar_senha');

        if (novaSenha && confirmarSenha) {
            confirmarSenha.addEventListener('input', () => {
                if (confirmarSenha.value !== novaSenha.value) {
                    confirmarSenha.setCustomValidity('As senhas não coincidem');
                } else {
                    confirmarSenha.setCustomValidity('');
                }
            });
        }
    }

    handleFormDados(event) {
        const submitBtn = event.target.querySelector('button[type="submit"]');
        this.setLoadingState(submitBtn, 'Salvando...');
    }

    handleFormSenha(event) {
        event.preventDefault();

        const novaSenha = document.getElementById('nova_senha').value;
        const confirmarSenha = document.getElementById('confirmar_senha').value;

        if (novaSenha !== confirmarSenha) {
            this.showAlert('danger', 'As senhas não coincidem!');
            return false;
        }

        if (novaSenha.length < 8) {
            this.showAlert('danger', 'A senha deve ter pelo menos 8 caracteres!');
            return false;
        }

        // Confirmar alteração
        if (confirm('Tem certeza que deseja alterar sua senha? Você será desconectado após a alteração.')) {
            const submitBtn = event.target.querySelector('button[type="submit"]');
            this.setLoadingState(submitBtn, 'Alterando senha...');
            event.target.submit();
        }
    }

    handleFormDesativar(event) {
        event.preventDefault();

        const confirmacao = document.getElementById('confirmacao_desativar').value;

        if (confirmacao !== 'DESATIVAR') {
            this.showAlert('danger', 'Digite "DESATIVAR" para confirmar!');
            return false;
        }

        if (confirm('ATENÇÃO: Esta ação desativará sua conta permanentemente. Apenas um administrador poderá reativá-la. Confirma?')) {
            const submitBtn = event.target.querySelector('button[type="submit"]');
            this.setLoadingState(submitBtn, 'Desativando...');
            event.target.submit();
        }
    }

    togglePassword(event) {
        const button = event.currentTarget;
        const input = button.parentElement.querySelector('input');
        const icon = button.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }

    setLoadingState(button, text) {
        button.disabled = true;
        button.classList.add('btn-loading');
        button.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${text}`;
    }

    showAlert(type, message) {
        const alertContainer = document.querySelector('.perfil-container');
        const alertClass = `alert-${type}-perfil`;

        const alert = document.createElement('div');
        alert.className = `alert-perfil ${alertClass}`;
        alert.innerHTML = `
            <i class="fas fa-${type === 'danger' ? 'exclamation-circle' : 'check-circle'}"></i>
            <span>${message}</span>
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        `;

        alertContainer.insertBefore(alert, alertContainer.firstChild);

        // Auto-remover após 5 segundos
        setTimeout(() => {
            if (alert.parentElement) {
                alert.remove();
            }
        }, 5000);
    }

    autoHideAlerts() {
        const alerts = document.querySelectorAll('.alert-perfil');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });
    }
}

// Inicializar
document.addEventListener('DOMContentLoaded', function () {
    new PerfilManager();
});

// Utilitários
window.PerfilUtils = {
    validatePassword: function (password) {
        const criteria = {
            length: password.length >= 8,
            lowercase: /[a-z]/.test(password),
            uppercase: /[A-Z]/.test(password),
            number: /\d/.test(password),
            special: /[^a-zA-Z\d]/.test(password)
        };

        const score = Object.values(criteria).filter(Boolean).length;
        return { criteria, score, isValid: score >= 3 };
    }
};