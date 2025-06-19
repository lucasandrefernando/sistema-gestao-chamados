/**
 * auth.js - Script para funcionalidades da página de autenticação
 * 
 * Este arquivo contém funcionalidades para as páginas de autenticação,
 * incluindo mostrar/ocultar senha, validação de formulários, e
 * detecção automática de tipo de usuário e empresas associadas.
 */
document.addEventListener('DOMContentLoaded', function () {
    // ======================================================
    // FUNCIONALIDADES EXISTENTES
    // ======================================================

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

    // ======================================================
    // NOVAS FUNCIONALIDADES PARA MÚLTIPLAS EMPRESAS
    // ======================================================

    // Elementos do DOM para funcionalidades de múltiplas empresas
    const empresaSelect = document.getElementById('empresa_id');
    const empresaGroup = document.getElementById('empresaGroup');
    const adminMasterMessage = document.getElementById('adminMasterMessage');
    const multiEmpresaMessage = document.getElementById('multiEmpresaMessage');
    const multiEmpresaText = document.getElementById('multiEmpresaText');
    const isAdminMasterInput = document.getElementById('isAdminMaster');
    const loadingIndicator = document.getElementById('emailLoadingIndicator');

    // Só continua se estiver na página de login com esses elementos
    if (emailInput && empresaSelect && empresaGroup) {
        // Variáveis para controle de digitação
        let typingTimer;
        const doneTypingInterval = 800; // tempo em ms para aguardar após o usuário parar de digitar

        // Armazena todas as opções originais do select de empresas para restauração posterior
        const todasEmpresas = Array.from(empresaSelect.options).map(option => {
            return {
                value: option.value,
                text: option.text
            };
        });

        /**
         * Verifica o usuário e suas empresas quando o email é informado
         * Esta função faz uma requisição AJAX para buscar informações do usuário
         * e determinar se é admin master ou está em múltiplas empresas
         */
        function verificarUsuario() {
            const email = emailInput.value.trim();

            // Verifica se o email tem formato válido
            if (email.length < 5 || !email.includes('@')) {
                resetarFormulario();
                return; // Email muito curto ou inválido
            }

            console.log('Verificando usuário para o email:', email);

            // Exibe indicador de carregamento
            if (loadingIndicator) {
                loadingIndicator.classList.add('active');
            }

            // Alternativa: mudar o ícone do email para um spinner
            const emailIcon = emailInput.parentNode.querySelector('.input-icon');
            if (emailIcon) {
                const originalIcon = emailIcon.innerHTML;
                emailIcon.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';
            }

            empresaSelect.disabled = true;

            // Obtém a URL base para as requisições AJAX
            let baseUrl = getBaseUrl();

            // Cria uma nova requisição AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('GET', baseUrl + 'buscarEmpresasDoUsuario?email=' + encodeURIComponent(email), true);

            xhr.onload = function () {
                // Oculta o indicador de carregamento
                if (loadingIndicator) {
                    loadingIndicator.classList.remove('active');
                }

                // Restaura o ícone original
                if (emailIcon) {
                    emailIcon.innerHTML = '<i class="fas fa-envelope"></i>';
                }

                if (xhr.status === 200) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        console.log('Resposta recebida:', data);

                        if (data.success) {
                            if (data.isAdminMaster) {
                                // É admin master, oculta o campo de empresa
                                empresaGroup.classList.add('hidden');
                                adminMasterMessage.style.display = 'block';
                                multiEmpresaMessage.style.display = 'none';
                                isAdminMasterInput.value = 'true';

                                // Remove o atributo required do select de empresa
                                empresaSelect.removeAttribute('required');
                            } else if (data.empresas && data.empresas.length > 0) {
                                // Usuário encontrado em uma ou mais empresas
                                empresaGroup.classList.remove('hidden');
                                adminMasterMessage.style.display = 'none';
                                isAdminMasterInput.value = 'false';

                                // Adiciona o atributo required de volta
                                empresaSelect.setAttribute('required', 'required');

                                // Filtra o select para mostrar apenas as empresas do usuário
                                filtrarEmpresasDoUsuario(data.empresas, data.totalEmpresas, data.isAdminRegular);
                            } else {
                                // Usuário não encontrado em nenhuma empresa
                                resetarFormulario();
                            }
                        } else {
                            // Resposta de erro
                            resetarFormulario();
                        }
                    } catch (e) {
                        console.error('Erro ao processar resposta:', e);
                        resetarFormulario();
                    }
                } else {
                    console.error('Erro na requisição AJAX:', xhr.status);
                    resetarFormulario();
                }

                empresaSelect.disabled = false;
            };

            xhr.onerror = function () {
                console.error('Erro na requisição AJAX');

                // Oculta o indicador de carregamento
                if (loadingIndicator) {
                    loadingIndicator.classList.remove('active');
                }

                // Restaura o ícone original
                if (emailIcon) {
                    emailIcon.innerHTML = '<i class="fas fa-envelope"></i>';
                }

                empresaSelect.disabled = false;
                resetarFormulario();
            };

            xhr.send();
        }

        /**
         * Filtra as empresas do usuário no select
         * @param {Array} empresasDoUsuario - Lista de empresas do usuário
         * @param {Number} totalEmpresas - Total de empresas do usuário
         * @param {Boolean} isAdminRegular - Se o usuário é admin regular
         */
        function filtrarEmpresasDoUsuario(empresasDoUsuario, totalEmpresas, isAdminRegular) {
            // Limpa o select
            empresaSelect.innerHTML = '';

            // Adiciona a opção padrão
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.text = 'Selecione sua empresa';
            empresaSelect.add(defaultOption);

            // Adiciona apenas as empresas do usuário
            empresasDoUsuario.forEach(empresa => {
                const option = document.createElement('option');
                option.value = empresa.id;
                option.text = empresa.nome;
                empresaSelect.add(option);
            });

            // Aplica estilo visual para indicar que o select está filtrado
            empresaSelect.classList.add('filtered-select');

            // Exibe mensagem informativa se houver múltiplas empresas
            if (totalEmpresas > 1) {
                multiEmpresaMessage.style.display = 'block';
                let mensagem = `Você está cadastrado em ${totalEmpresas} empresas. Selecione uma para continuar.`;

                if (isAdminRegular) {
                    mensagem += ' (Admin Regular)';
                }

                multiEmpresaText.textContent = mensagem;

                // Se houver apenas uma empresa, seleciona automaticamente
            } else if (totalEmpresas === 1) {
                empresaSelect.value = empresasDoUsuario[0].id;
                empresaSelect.classList.add('auto-selected');
                empresaSelect.classList.add('has-value'); // Adiciona classe para efeito de foco

                // Remove a classe após 2 segundos
                setTimeout(() => {
                    empresaSelect.classList.remove('auto-selected');
                }, 2000);

                multiEmpresaMessage.style.display = 'none';
            } else {
                multiEmpresaMessage.style.display = 'none';
            }
        }

        /**
         * Reseta o formulário para o estado original
         * Restaura todas as opções de empresa e remove mensagens especiais
         */
        function resetarFormulario() {
            // Restaura todas as opções originais do select
            empresaSelect.innerHTML = '';

            todasEmpresas.forEach(empresa => {
                const option = document.createElement('option');
                option.value = empresa.value;
                option.text = empresa.text;
                empresaSelect.add(option);
            });

            // Remove classes e mensagens
            empresaSelect.classList.remove('filtered-select');
            empresaSelect.classList.remove('has-value');
            empresaGroup.classList.remove('hidden');
            adminMasterMessage.style.display = 'none';
            multiEmpresaMessage.style.display = 'none';
            isAdminMasterInput.value = 'false';

            // Garante que o campo seja required
            empresaSelect.setAttribute('required', 'required');
        }

        /**
         * Obtém a URL base para as requisições AJAX
         * @returns {string} URL base para as requisições AJAX
         */
        function getBaseUrl() {
            // Tenta obter do link de recuperação de senha
            const forgotLink = document.querySelector('.forgot-link');
            if (forgotLink) {
                const href = forgotLink.getAttribute('href');
                const authIndex = href.indexOf('auth/');
                if (authIndex !== -1) {
                    return href.substring(0, authIndex + 5);
                }
            }

            // Tenta obter da URL atual
            const currentUrl = window.location.href;
            const authIndex = currentUrl.indexOf('auth/');
            if (authIndex !== -1) {
                return currentUrl.substring(0, authIndex + 5);
            }

            // Fallback - usa a URL atual
            return window.location.origin + '/auth/';
        }

        // Configura o evento para detectar quando o usuário para de digitar
        emailInput.addEventListener('keyup', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(verificarUsuario, doneTypingInterval);
        });

        // Limpa o timer se o usuário continuar digitando
        emailInput.addEventListener('keydown', function () {
            clearTimeout(typingTimer);
        });

        // Também verifica quando o campo perde o foco
        emailInput.addEventListener('blur', verificarUsuario);
    }
});