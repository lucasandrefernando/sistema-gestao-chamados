<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Sistema de Gestão de Chamados</title>
    <!-- Fontes e ícones -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Estilos da aplicação -->
    <link rel="stylesheet" href="<?= base_url('public/css/auth.css') ?>">
</head>

<body>
    <div class="auth-container">
        <!-- Fundo decorativo -->
        <div class="auth-background"></div>

        <div class="auth-wrapper">
            <!-- Container do logo -->
            <div class="auth-logo-wrapper">
                <div class="auth-logo-container">
                    <img src="<?= base_url('public/img/logo.png') ?>" alt="Logo" class="auth-logo">
                </div>
            </div>

            <!-- Card principal -->
            <div class="auth-card">
                <!-- Barra superior colorida -->
                <div class="auth-card-header"></div>

                <div class="auth-content">
                    <!-- Cabeçalho com título e subtítulo -->
                    <div class="auth-header">
                        <h1 class="auth-title">Bem-vindo ao Sistema</h1>
                        <p class="auth-subtitle">Faça login para acessar o painel de gestão de chamados</p>
                    </div>

                    <!-- Mensagens de alerta -->
                    <?php if (has_flash_message('error')): ?>
                        <div class="auth-alert auth-alert-error">
                            <i class="fas fa-exclamation-circle auth-alert-icon"></i>
                            <div><?= get_flash_message('error') ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (has_flash_message('success')): ?>
                        <div class="auth-alert auth-alert-success">
                            <i class="fas fa-check-circle auth-alert-icon"></i>
                            <div><?= get_flash_message('success') ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Mensagem para admin master (inicialmente oculta) -->
                    <div id="adminMasterMessage" class="admin-master-message">
                        <i class="fas fa-shield-alt"></i> Você foi identificado como Administrador Master. Não é necessário selecionar uma empresa.
                    </div>

                    <!-- Mensagem para usuário em múltiplas empresas (inicialmente oculta) -->
                    <div id="multiEmpresaMessage" class="multi-empresa-message">
                        <i class="fas fa-building"></i> <span id="multiEmpresaText">Você está cadastrado em múltiplas empresas. Selecione uma para continuar.</span>
                    </div>

                    <!-- Formulário de login -->
                    <form action="<?= base_url('auth/login') ?>" method="post" class="auth-form" id="loginForm">
                        <!-- Campo oculto para indicar se é admin master -->
                        <input type="hidden" id="isAdminMaster" name="is_admin_master" value="false">

                        <!-- Campo de email -->
                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-envelope"></i></span>
                                <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
                            </div>
                        </div>

                        <!-- Campo de senha -->
                        <div class="form-group">
                            <label for="senha">Senha</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                                <button type="button" class="password-toggle" tabindex="-1" aria-label="Mostrar/ocultar senha">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <!-- Link "Esqueceu a senha?" posicionado abaixo do campo de senha -->
                            <div class="forgot-password-link">
                                <a href="<?= base_url('auth/recuperarSenha') ?>" class="forgot-link">Esqueceu a senha?</a>
                            </div>
                        </div>

                        <!-- Campo de seleção de empresa -->
                        <div class="form-group" id="empresaGroup">
                            <label for="empresa_id">Empresa</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-building"></i></span>
                                <select id="empresa_id" name="empresa_id" required>
                                    <option value="">Selecione sua empresa</option>
                                    <?php if (isset($empresas) && !empty($empresas)): ?>
                                        <?php foreach ($empresas as $empresa): ?>
                                            <option value="<?= $empresa['id'] ?>"><?= $empresa['nome'] ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Botão de login -->
                        <button type="submit" class="auth-btn" id="loginButton">
                            <i class="fas fa-sign-in-alt"></i> Entrar no Sistema
                        </button>
                    </form>

                    <!-- Rodapé -->
                    <div class="auth-footer">
                        <div class="auth-support">
                            <i class="fas fa-headset"></i>
                            <span>Suporte: <a href="mailto:suporte@eagletelecom.com.br">suporte@eagletelecom.com.br</a></span>
                        </div>
                        <div class="auth-copyright">
                            &copy; <?= date('Y') ?> Eagle Telecom - Todos os direitos reservados
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script principal da autenticação -->
    <script src="<?= base_url('public/js/auth.js') ?>"></script>

    <!-- Script para automatizar a seleção da empresa e verificar admin master -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elementos do DOM
            const emailInput = document.getElementById('email');
            const empresaSelect = document.getElementById('empresa_id');
            const empresaGroup = document.getElementById('empresaGroup');
            const adminMasterMessage = document.getElementById('adminMasterMessage');
            const multiEmpresaMessage = document.getElementById('multiEmpresaMessage');
            const multiEmpresaText = document.getElementById('multiEmpresaText');
            const isAdminMasterInput = document.getElementById('isAdminMaster');

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
                empresaSelect.disabled = true;

                // Cria uma nova requisição AJAX
                const xhr = new XMLHttpRequest();
                xhr.open('GET', '<?= base_url('auth/buscarEmpresasDoUsuario') ?>?email=' + encodeURIComponent(email), true);

                xhr.onload = function() {
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

                xhr.onerror = function() {
                    console.error('Erro na requisição AJAX');
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
                empresaGroup.classList.remove('hidden');
                adminMasterMessage.style.display = 'none';
                multiEmpresaMessage.style.display = 'none';
                isAdminMasterInput.value = 'false';

                // Garante que o campo seja required
                empresaSelect.setAttribute('required', 'required');
            }

            // Configura o evento para detectar quando o usuário para de digitar
            emailInput.addEventListener('keyup', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(verificarUsuario, doneTypingInterval);
            });

            // Limpa o timer se o usuário continuar digitando
            emailInput.addEventListener('keydown', function() {
                clearTimeout(typingTimer);
            });

            // Também verifica quando o campo perde o foco
            emailInput.addEventListener('blur', verificarUsuario);

            // Função para alternar visibilidade da senha
            const passwordToggle = document.querySelector('.password-toggle');
            const senhaInput = document.getElementById('senha');

            if (passwordToggle) {
                passwordToggle.addEventListener('click', function() {
                    const type = senhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    senhaInput.setAttribute('type', type);

                    // Alterna o ícone
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            }
        });
    </script>
</body>

</html>