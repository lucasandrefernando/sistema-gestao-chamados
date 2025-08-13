/**
 * Perfil Manager - Versão DEFINITIVA
 * @version 4.0.0 - Correção final dos problemas
 */

if (window.location.pathname.includes('/perfil') || document.title.includes('Perfil')) {
    console.log('🚀 Inicializando Perfil Manager v4.0.0...');

    // ✅ CORREÇÃO DEFINITIVA: Limpar qualquer instância anterior
    if (window.perfilManager) {
        delete window.perfilManager;
    }
    if (window.PerfilManager) {
        delete window.PerfilManager;
    }

    // ✅ CORREÇÃO: Aguardar DOM estar completamente pronto
    function initPerfilManager() {
        'use strict';

        class PerfilManager {
            constructor() {
                this.passwordStrengthScore = 0;
                this.senhaAtualValida = false;
                this.novaSenhaValida = false;
                this.senhasCoincidentes = false;
                this.baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '';
                this.alertShown = false; // ✅ CORREÇÃO: Flag para alerta único
                this.init();
            }

            init() {
                console.log('✅ PerfilManager inicializado');
                this.bindEvents();
                this.initPasswordStrength();
                this.initFormValidation();
                this.initRealTimeValidation();
                this.autoHideAlerts();
                this.protectHeaderDropdowns();
            }

            protectHeaderDropdowns() {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    modal.addEventListener('show.bs.modal', () => {
                        // ✅ CORREÇÃO: Resetar flag quando abrir modal
                        this.alertShown = false;

                        const navbarDropdowns = document.querySelectorAll('nav.app-navbar .dropdown-menu.show');
                        navbarDropdowns.forEach(menu => {
                            menu.style.zIndex = '9999';
                        });
                    });

                    modal.addEventListener('hidden.bs.modal', () => {
                        const navbarDropdowns = document.querySelectorAll('nav.app-navbar .dropdown-menu');
                        navbarDropdowns.forEach(menu => {
                            menu.style.zIndex = '1050';
                        });
                    });
                });
            }

            bindEvents() {
                // ✅ CORREÇÃO: Remover listeners existentes primeiro
                const formSenha = document.getElementById('formSenha');
                if (formSenha) {
                    // Clonar para remover todos os listeners
                    const newForm = formSenha.cloneNode(true);
                    formSenha.parentNode.replaceChild(newForm, formSenha);

                    // Adicionar listener único
                    newForm.addEventListener('submit', (e) => this.handleFormSenha(e));
                }

                // Formulário de dados
                const formDados = document.getElementById('formDados');
                if (formDados) {
                    formDados.addEventListener('submit', this.handleFormDados.bind(this));
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

                // Fechar notificações
                document.querySelectorAll('.close-notification').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.target.closest('.notification').remove();
                    });
                });
            }

            initRealTimeValidation() {
                const senhaAtualInput = document.getElementById('senha_atual');
                if (senhaAtualInput) {
                    senhaAtualInput.addEventListener('input', this.debounce((e) => {
                        this.validateSenhaAtual(e.target.value);
                    }, 500));

                    senhaAtualInput.addEventListener('blur', (e) => {
                        this.validateSenhaAtual(e.target.value);
                    });
                }

                const novaSenhaInput = document.getElementById('nova_senha');
                if (novaSenhaInput) {
                    novaSenhaInput.addEventListener('input', (e) => {
                        this.checkPasswordStrength(e.target.value);
                        this.validateNovaSenha(e.target.value);
                        this.validatePasswordMatch();
                        this.updateSubmitButton();
                    });
                }

                const confirmarSenhaInput = document.getElementById('confirmar_senha');
                if (confirmarSenhaInput) {
                    confirmarSenhaInput.addEventListener('input', () => {
                        this.validatePasswordMatch();
                        this.updateSubmitButton();
                    });
                }
            }

            async validateSenhaAtual(senha) {
                const feedback = document.getElementById('senha-atual-feedback');
                const input = document.getElementById('senha_atual');

                if (!senha || senha.length === 0) {
                    this.senhaAtualValida = false;
                    this.setFieldState(input, feedback, '', 'neutral');
                    this.updateSubmitButton();
                    return;
                }

                this.setFieldState(input, feedback, '⏳ Verificando...', 'loading');

                try {
                    const formData = new FormData();
                    formData.append('senha_atual', senha);

                    const response = await fetch(`${this.baseUrl}perfil/verificar-senha-atual`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('Resposta inválida do servidor');
                    }

                    const result = await response.json();

                    if (result.success) {
                        this.senhaAtualValida = true;
                        this.setFieldState(input, feedback, '✅ Senha correta', 'success');
                    } else {
                        this.senhaAtualValida = false;
                        this.setFieldState(input, feedback, '❌ ' + (result.message || 'Senha incorreta'), 'error');
                    }
                } catch (error) {
                    console.error('Erro ao verificar senha:', error);
                    this.senhaAtualValida = false;
                    this.setFieldState(input, feedback, '❌ Erro de conexão. Tente novamente.', 'error');
                }

                this.updateSubmitButton();
            }

            validateNovaSenha(senha) {
                if (!senha || senha.length === 0) {
                    this.novaSenhaValida = false;
                    return;
                }
                this.novaSenhaValida = this.passwordStrengthScore >= 60;
            }

            validatePasswordMatch() {
                const novaSenha = document.getElementById('nova_senha');
                const confirmarSenha = document.getElementById('confirmar_senha');
                const feedback = document.getElementById('confirmar-senha-feedback');

                if (!novaSenha || !confirmarSenha || !feedback) return;

                const novaSenhaValue = novaSenha.value;
                const confirmarSenhaValue = confirmarSenha.value;

                if (!confirmarSenhaValue) {
                    this.senhasCoincidentes = false;
                    this.setFieldState(confirmarSenha, feedback, '', 'neutral');
                    return;
                }

                if (confirmarSenhaValue === novaSenhaValue) {
                    this.senhasCoincidentes = true;
                    this.setFieldState(confirmarSenha, feedback, '✅ Senhas coincidem', 'success');
                } else {
                    this.senhasCoincidentes = false;
                    this.setFieldState(confirmarSenha, feedback, '❌ Senhas não coincidem', 'error');
                }
            }

            updateSubmitButton() {
                const submitBtn = document.getElementById('btn-alterar-senha');
                if (!submitBtn) return;

                const isValid = this.senhaAtualValida &&
                    this.novaSenhaValida &&
                    this.senhasCoincidentes;

                submitBtn.disabled = !isValid;

                if (isValid) {
                    submitBtn.classList.remove('btn-disabled');
                    submitBtn.innerHTML = '<i class="fas fa-key"></i> Alterar Senha';
                } else {
                    submitBtn.classList.add('btn-disabled');
                    submitBtn.innerHTML = '<i class="fas fa-lock"></i> Preencha todos os campos';
                }
            }

            setFieldState(input, feedback, message, state) {
                if (!input || !feedback) return;

                input.classList.remove('is-valid', 'is-invalid', 'is-loading');
                feedback.classList.remove('field-success', 'field-error', 'field-loading', 'field-neutral');

                switch (state) {
                    case 'success':
                        input.classList.add('is-valid');
                        feedback.classList.add('field-success');
                        break;
                    case 'error':
                        input.classList.add('is-invalid');
                        feedback.classList.add('field-error');
                        break;
                    case 'loading':
                        input.classList.add('is-loading');
                        feedback.classList.add('field-loading');
                        break;
                    case 'neutral':
                    default:
                        feedback.classList.add('field-neutral');
                        break;
                }

                feedback.textContent = message;
            }

            initPasswordStrength() {
                const novaSenhaInput = document.getElementById('nova_senha');
                if (!novaSenhaInput) return;

                novaSenhaInput.addEventListener('input', (e) => {
                    this.checkPasswordStrength(e.target.value);
                    this.validatePasswordMatch();
                });

                const confirmarSenhaInput = document.getElementById('confirmar_senha');
                if (confirmarSenhaInput) {
                    confirmarSenhaInput.addEventListener('input', () => {
                        this.validatePasswordMatch();
                    });
                }
            }

            checkPasswordStrength(password) {
                const strengthFill = document.querySelector('.strength-fill');
                const strengthText = document.querySelector('.strength-text');

                if (!strengthFill || !strengthText) return;

                let score = 0;
                let feedback = '';
                let className = '';

                if (password.length === 0) {
                    feedback = 'Digite sua senha para ver a força';
                    className = '';
                    score = 0;
                } else {
                    if (password.length >= 8) score += 25;
                    if (password.length >= 12) score += 15;
                    if (password.match(/[a-z]/)) score += 15;
                    if (password.match(/[A-Z]/)) score += 15;
                    if (password.match(/[0-9]/)) score += 15;
                    if (password.match(/[^a-zA-Z0-9]/)) score += 15;

                    if (score < 40) {
                        feedback = '🔴 Muito Fraca - Adicione mais caracteres e símbolos';
                        className = 'strength-weak';
                    } else if (score < 60) {
                        feedback = '🟡 Fraca - Adicione letras maiúsculas, números ou símbolos';
                        className = 'strength-fair';
                    } else if (score < 80) {
                        feedback = '🔵 Boa - Quase perfeita!';
                        className = 'strength-good';
                    } else {
                        feedback = '🟢 Muito Forte - Excelente segurança!';
                        className = 'strength-strong';
                    }
                }

                this.passwordStrengthScore = score;

                strengthFill.className = `strength-fill ${className}`;
                strengthFill.style.width = `${Math.min(score, 100)}%`;
                strengthText.textContent = feedback;

                this.validateNovaSenha(password);
            }

            initFormValidation() {
                const inputs = document.querySelectorAll('.form-control-modern');
                inputs.forEach(input => {
                    input.addEventListener('blur', () => {
                        if (input.checkValidity()) {
                            input.classList.remove('is-invalid');
                            input.classList.add('is-valid');
                        } else {
                            input.classList.remove('is-valid');
                            input.classList.add('is-invalid');
                        }
                    });
                });
            }


            // ✅ CORREÇÃO: Alertas no modal de dados também
            handleFormDados(event) {
                event.preventDefault();

                const form = event.target;
                const submitBtn = form.querySelector('button[type="submit"]');
                const nome = form.querySelector('input[name="nome"]').value.trim();
                const sobrenome = form.querySelector('input[name="sobrenome"]').value.trim();

                if (!nome || nome.length < 2) {
                    this.showAlert('error', 'O nome deve ter pelo menos 2 caracteres!', 'modalDados');
                    return false;
                }

                if (!sobrenome || sobrenome.length < 2) {
                    this.showAlert('error', 'O sobrenome deve ter pelo menos 2 caracteres!', 'modalDados');
                    return false;
                }

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return false;
                }

                this.setLoadingState(submitBtn, 'Salvando alterações...');

                setTimeout(() => {
                    form.submit();
                }, 500);
            }

            // ✅ CORREÇÃO: Alertas dentro do modal de senha
            handleFormSenha(event) {
                event.preventDefault();

                // Verificar se alerta já foi mostrado
                if (this.alertShown) {
                    console.log('Alerta já foi mostrado, ignorando...');
                    return false;
                }

                const form = event.target;
                const novaSenha = document.getElementById('nova_senha').value;
                const confirmarSenha = document.getElementById('confirmar_senha').value;
                const senhaAtual = form.querySelector('input[name="senha_atual"]').value;

                // ✅ CORREÇÃO: Validações com alertas no modal
                if (!this.senhaAtualValida) {
                    this.showAlert('error', 'Verifique sua senha atual!', 'modalSenha');
                    return false;
                }

                if (!this.novaSenhaValida) {
                    this.showAlert('error', 'A nova senha não atende aos critérios de segurança!', 'modalSenha');
                    return false;
                }

                if (!this.senhasCoincidentes) {
                    this.showAlert('error', 'As senhas não coincidem!', 'modalSenha');
                    return false;
                }

                if (novaSenha === senhaAtual) {
                    this.showAlert('error', 'A nova senha deve ser diferente da atual!', 'modalSenha');
                    return false;
                }

                if (this.passwordStrengthScore < 60) {
                    this.showAlert('error', 'A senha é muito fraca. Escolha uma senha mais forte!', 'modalSenha');
                    return false;
                }

                // Marcar alerta como mostrado
                this.alertShown = true;

                const confirmResult = confirm('⚠️ Tem certeza que deseja alterar sua senha?\n\nVocê será desconectado após a alteração e receberá um e-mail de confirmação.');

                if (confirmResult) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    this.setLoadingState(submitBtn, 'Alterando senha...');

                    setTimeout(() => {
                        form.submit();
                    }, 500);
                } else {
                    // Resetar flag se cancelar
                    this.alertShown = false;
                }

                return false;
            }

            handleFormDesativar(event) {
                event.preventDefault();

                const form = event.target;
                const confirmacao = document.getElementById('confirmacao_desativar').value;
                const senha = form.querySelector('input[name="senha"]').value;

                if (!senha) {
                    this.showAlert('error', 'Digite sua senha para confirmar!');
                    return false;
                }

                if (confirmacao !== 'DESATIVAR') {
                    this.showAlert('error', 'Digite exatamente "DESATIVAR" para confirmar!');
                    document.getElementById('confirmacao_desativar').focus();
                    return false;
                }

                if (confirm('⚠️ ATENÇÃO: Esta ação desativará sua conta permanentemente!\n\nApenas um administrador poderá reativá-la.\n\nTem CERTEZA que deseja continuar?')) {
                    if (confirm('🚨 ÚLTIMA CONFIRMAÇÃO:\n\nSua conta será DESATIVADA e você perderá acesso ao sistema.\n\nConfirma a desativação?')) {
                        const submitBtn = form.querySelector('button[type="submit"]');
                        this.setLoadingState(submitBtn, 'Desativando conta...');

                        setTimeout(() => {
                            form.submit();
                        }, 1000);
                    }
                }
            }

            togglePassword(event) {
                const button = event.currentTarget;
                const input = button.parentElement.querySelector('input');
                const icon = button.querySelector('i');

                if (!input || !icon) return;

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'fas fa-eye-slash';
                    button.setAttribute('title', 'Ocultar senha');
                } else {
                    input.type = 'password';
                    icon.className = 'fas fa-eye';
                    button.setAttribute('title', 'Mostrar senha');
                }
            }

            setLoadingState(button, text) {
                if (!button) return;

                button.disabled = true;
                button.classList.add('btn-loading');
                button.setAttribute('data-original-text', button.innerHTML);
                button.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${text}`;
            }

            // ✅ CORREÇÃO: Mostrar alertas dentro do modal
            showAlert(type, message, modalId = null) {
                // Remover alertas existentes
                document.querySelectorAll('.notification.auto-generated').forEach(alert => {
                    alert.remove();
                });

                let alertContainer;

                // ✅ CORREÇÃO: Se modalId for especificado, mostrar dentro do modal
                if (modalId) {
                    const modal = document.getElementById(modalId);
                    if (modal) {
                        alertContainer = modal.querySelector('.modal-body-modern');
                        if (!alertContainer) {
                            alertContainer = modal.querySelector('.modal-body');
                        }
                    }
                }

                // Fallback para container principal
                if (!alertContainer) {
                    alertContainer = document.querySelector('.profile-content');
                }

                if (!alertContainer) return;

                const iconClass = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';

                const alert = document.createElement('div');
                alert.className = `notification ${type} auto-generated`;
                alert.style.marginBottom = '1rem';
                alert.innerHTML = `
                <i class="fas ${iconClass}"></i>
                <span>${message}</span>
                <button class="close-notification" type="button">&times;</button>
                 `;

                // ✅ CORREÇÃO: Inserir no topo do container
                alertContainer.insertBefore(alert, alertContainer.firstChild);

                const closeBtn = alert.querySelector('.close-notification');
                closeBtn.addEventListener('click', () => alert.remove());

                // Auto-remover após 5 segundos
                setTimeout(() => {
                    if (alert.parentElement) {
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-10px)';
                        setTimeout(() => alert.remove(), 300);
                    }
                }, 5000);

                // ✅ CORREÇÃO: Scroll suave para o alerta
                alert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            autoHideAlerts() {
                const alerts = document.querySelectorAll('.profile-content .notification:not(.auto-generated)');
                alerts.forEach(alert => {
                    setTimeout(() => {
                        if (alert.parentElement) {
                            alert.style.opacity = '0';
                            alert.style.transform = 'translateY(-10px)';
                            setTimeout(() => alert.remove(), 300);
                        }
                    }, 7000);
                });
            }

            debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }
        }

        // ✅ CORREÇÃO: Instância única
        window.perfilManager = new PerfilManager();
        window.PerfilManager = PerfilManager;
    }

    // ✅ CORREÇÃO: Aguardar DOM estar completamente pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPerfilManager);
    } else {
        // Aguardar um pouco para garantir que tudo foi carregado
        setTimeout(initPerfilManager, 100);
    }

} else {
    console.log('⚠️ perfil.js não executado - não estamos na página de perfil');
}