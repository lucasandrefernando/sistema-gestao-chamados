<?php
// Definir variáveis para o template
$page_title = 'Gerenciar Minha Conta';
$breadcrumbs = [
    'Perfil' => base_url('perfil'),
    'Gerenciar' => null
];

// Incluir o header
include ROOT_DIR . '/app/views/templates/header.php';
?>

<div class="perfil-form-container">
    <div class="perfil-form-card">
        <div class="perfil-form-header">
            <h2><?= $titulo ?></h2>
        </div>
        <div class="perfil-form-body">

            <!-- Seção: Informações Pessoais -->
            <section class="form-section">
                <h3 class="form-section-title"><i class="fas fa-user-edit"></i> Dados Pessoais</h3>
                <form action="<?= base_url('perfil/atualizarDados') ?>" method="post" class="perfil-update-form" id="dadosPessoaisForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nome_primeiro">Nome <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-user"></i></span>
                                <input type="text" id="nome_primeiro" name="nome_primeiro" value="<?= htmlspecialchars($usuario['nome_primeiro'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="sobrenome">Sobrenome <span class="optional">(opcional)</span></label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-user"></i></span>
                                <input type="text" id="sobrenome" name="sobrenome" value="<?= htmlspecialchars($usuario['sobrenome'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cargo">Cargo <span class="optional">(opcional)</span></label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-briefcase"></i></span>
                                <input type="text" id="cargo" name="cargo" value="<?= htmlspecialchars($usuario['cargo'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-actions-small">
                        <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Salvar Dados</button>
                    </div>
                </form>
            </section>

            <!-- Seção: Alterar Senha -->
            <section class="form-section">
                <h3 class="form-section-title"><i class="fas fa-lock"></i> Alterar Senha</h3>
                <form action="<?= base_url('perfil/alterarSenha') ?>" method="post" class="perfil-update-form" id="alterarSenhaForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="senha_atual">Senha Atual <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-key"></i></span>
                                <input type="password" id="senha_atual" name="senha_atual" required>
                                <button type="button" class="toggle-password" data-target="senha_atual">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="nova_senha">Nova Senha <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" id="nova_senha" name="nova_senha" required>
                                <button type="button" class="toggle-password" data-target="nova_senha">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirmar_nova_senha">Confirmar Nova Senha <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" id="confirmar_nova_senha" name="confirmar_nova_senha" required>
                                <button type="button" class="toggle-password" data-target="confirmar_nova_senha">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="password-strength" id="passwordStrengthContainer"></div> <!-- Container para o medidor de força -->
                    <div class="form-actions-small">
                        <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Alterar Senha</button>
                    </div>
                </form>
            </section>

            <!-- Seção: Alterar E-mail -->
            <section class="form-section">
                <h3 class="form-section-title"><i class="fas fa-envelope"></i> Alterar E-mail</h3>
                <form action="<?= base_url('perfil/iniciarTrocaEmail') ?>" method="post" class="perfil-update-form" id="alterarEmailForm">
                    <p class="form-hint">
                        <i class="fas fa-info-circle"></i> Ao alterar seu e-mail, um link de confirmação será enviado para o novo endereço. Você será deslogado e precisará fazer login com o novo e-mail após a confirmação.
                    </p>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="email_atual">E-mail Atual</label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-envelope"></i></span>
                                <input type="email" id="email_atual" value="<?= htmlspecialchars($usuario['email']) ?>" readonly disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="novo_email">Novo E-mail <span class="required">*</span></label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-envelope"></i></span>
                                <input type="email" id="novo_email" name="novo_email" required placeholder="Digite seu novo e-mail">
                            </div>
                        </div>
                    </div>
                    <div class="form-actions-small">
                        <button type="submit" class="btn-primary"><i class="fas fa-paper-plane"></i> Enviar Link de Confirmação</button>
                    </div>
                </form>
            </section>

            <!-- Seção: Desativar Conta -->
            <section class="form-section danger-zone">
                <h3 class="form-section-title"><i class="fas fa-exclamation-triangle"></i> Desativar Conta</h3>
                <form action="<?= base_url('perfil/desativarConta') ?>" method="post" class="perfil-update-form" id="desativarContaForm">
                    <p class="form-hint danger-text">
                        <i class="fas fa-exclamation-circle"></i> Desativar sua conta a tornará inativa e você será deslogado. Apenas um administrador poderá reativá-la. Esta ação é irreversível por você.
                    </p>
                    <div class="form-group">
                        <label for="confirmacao_desativar">Para confirmar, digite "desativar minha conta" no campo abaixo:</label>
                        <div class="input-group">
                            <span class="input-icon"><i class="fas fa-keyboard"></i></span>
                            <input type="text" id="confirmacao_desativar" name="confirmacao_desativar" required placeholder="desativar minha conta">
                        </div>
                    </div>
                    <div class="form-actions-small">
                        <button type="submit" class="btn-danger"><i class="fas fa-user-slash"></i> Desativar Minha Conta</button>
                    </div>
                </form>
            </section>

            <div class="form-actions-bottom">
                <a href="<?= base_url('perfil') ?>" class="btn-outline">
                    <i class="fas fa-arrow-left"></i> Voltar para o Perfil
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir o footer
include ROOT_DIR . '/app/views/templates/footer.php';
?>