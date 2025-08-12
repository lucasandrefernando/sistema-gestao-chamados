/**
 * Perfil Manager - Versão Profissional
 * @version 2.0.0 - Solução completa e funcional
 */

if (window.location.pathname.includes('/perfil') || document.title.includes('Perfil')) {
    console.log('🚀 Inicializando Perfil Manager...');

    // Proteção contra múltiplas execuções
    if (window.PerfilManager) {
        delete window.PerfilManager;
    }

    (function () {
        'use strict';

        class PerfilManager {
            constructor() {
                this.passwordStrengthScore = 0;
                this.init();
            }

            init() {
                console.log('✅ PerfilManager inicializado');
                this.bindEvents();
                this.initPasswordStrength();
                this.initFormValidation();
                this.autoHideAlerts();
                this.protectHeaderDropdowns();
            }

            // ============================================================================
            // PROTEÇÃO DO HEADER
            // ============================================================================

            protectHeaderDropdowns() {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    modal.addEventListener('show.bs.modal', () => {
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

            // ============================================================================
            // EVENTOS
            // ============================================================================

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

                // Fechar notificações
                document.querySelectorAll('.close-notification').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.target.closest('.notification').remove();
                    });
                });
            }

            // ============================================================================
            // SISTEMA DE SENHAS
            // ============================================================================

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
                    // Critérios de força
                    if (password.length >= 8) score += 25;
                    if (password.match(/[a-z]/)) score += 15;
                    if (password.match(/[A-Z]/)) score += 15;
                    if (password.match(/[0-9]/)) score += 15;
                    if (password.match(/[^a-zA-Z0-9]/)) score += 15;
                    if (password.length >= 12) score += 15;

                    // Determinar força
                    if (score < 40) {
                        feedback = '🔴 Fraca - Adicione mais caracteres';
                        className = 'strength-weak';
                    } else if (score < 60) {
                        feedback = '🟡 Razoável - Pode melhorar';
                        className = 'strength-fair';
                    } else if (score < 80) {
                        feedback = '🔵 Boa - Quase lá';
                        className = 'strength-good';
                    } else {
                        feedback = '🟢 Forte - Excelente!';
                        className = 'strength-strong';
                    }
                }

                this.passwordStrengthScore = score;

                // Atualizar visual
                strengthFill.className = `strength-fill ${className}`;
                strengthFill.style.width = `${Math.min(score, 100)}%`;
                strengthText.textContent = feedback;
            }

            validatePasswordMatch() {
                const novaSenha = document.getElementById('nova_senha');
                const confirmarSenha = document.getElementById('confirmar_senha');

                if (!novaSenha || !confirmarSenha) return;

                if (confirmarSenha.value && confirmarSenha.value !== novaSenha.value) {
                    confirmarSenha.setCustomValidity('As senhas não coincidem');
                    confirmarSenha.classList.add('is-invalid');
                } else {
                    confirmarSenha.setCustomValidity('');
                    confirmarSenha.classList.remove('is-invalid');
                }
            }

            // ============================================================================
            // VALIDAÇÃO DE FORMULÁRIOS
            // ============================================================================

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

            // ============================================================================
            // MANIPULADORES DE FORMULÁRIOS
            // ============================================================================

            handleFormDados(event) {
                event.preventDefault();

                const form = event.target;
                const submitBtn = form.querySelector('button[type="submit"]');

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return false;
                }

                this.setLoadingState(submitBtn, 'Salvando alterações...');

                setTimeout(() => {
                    form.submit();
                }, 500);
            }

            handleFormSenha(event) {
                event.preventDefault();

                const form = event.target;
                const novaSenha = document.getElementById('nova_senha').value;
                const confirmarSenha = document.getElementById('confirmar_senha').value;
                const senhaAtual = form.querySelector('input[name="senha_atual"]').value;

                if (!senhaAtual) {
                    this.showAlert('error', 'Digite sua senha atual!');
                    return false;
                }

                if (novaSenha.length < 8) {
                    this.showAlert('error', 'A nova senha deve ter pelo menos 8 caracteres!');
                    return false;
                }

                if (novaSenha !== confirmarSenha) {
                    this.showAlert('error', 'As senhas não coincidem!');
                    return false;
                }

                if (novaSenha === senhaAtual) {
                    this.showAlert('error', 'A nova senha deve ser diferente da atual!');
                    return false;
                }

                if (this.passwordStrengthScore < 60) {
                    this.showAlert('error', 'A senha é muito fraca. Escolha uma senha mais forte!');
                    return false;
                }

                if (confirm('⚠️ Tem certeza que deseja alterar sua senha?\n\nVocê será desconectado após a alteração e receberá um e-mail de confirmação.')) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    this.setLoadingState(submitBtn, 'Alterando senha...');

                    setTimeout(() => {
                        form.submit();
                    }, 500);
                }
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

            // ============================================================================
            // UTILITÁRIOS
            // ============================================================================

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

            showAlert(type, message) {
                // Remover alertas existentes
                document.querySelectorAll('.notification.auto-generated').forEach(alert => {
                    alert.remove();
                });

                const alertContainer = document.querySelector('.profile-content');
                if (!alertContainer) return;

                const iconClass = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';

                const alert = document.createElement('div');
                alert.className = `notification ${type} auto-generated`;
                alert.innerHTML = `
                    <i class="fas ${iconClass}"></i>
                    <span>${message}</span>
                    <button class="close-notification" type="button">&times;</button>
                `;

                alertContainer.insertBefore(alert, alertContainer.firstChild);

                const closeBtn = alert.querySelector('.close-notification');
                closeBtn.addEventListener('click', () => alert.remove());

                setTimeout(() => {
                    if (alert.parentElement) {
                        alert.style.opacity = '0';
                        alert.style.transform = 'translateY(-10px)';
                        setTimeout(() => alert.remove(), 300);
                    }
                }, 5000);

                alertContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
        }

        // Inicializar apenas uma vez
        let perfilManagerInstance = null;

        function initPerfil() {
            if (perfilManagerInstance) {
                console.log('⚠️ PerfilManager já inicializado');
                return;
            }

            perfilManagerInstance = new PerfilManager();
            window.PerfilManager = PerfilManager;
            window.perfilManager = perfilManagerInstance;
        }

        // Inicializar quando DOM estiver pronto
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPerfil);
        } else {
            initPerfil();
        }

    })();

} else {
    console.log('⚠️ perfil.js não executado - não estamos na página de perfil');
}