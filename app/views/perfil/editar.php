<?php
// Definir variáveis para o template
$page_title = 'Editar Perfil';
$breadcrumbs = [
    'Perfil' => base_url('perfil'),
    'Editar' => null
];

// Incluir o header
include ROOT_DIR . '/app/views/templates/header.php';
?>

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Editar Perfil</h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('perfil/atualizar') ?>" method="post">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($usuario['email']) ?>" readonly disabled>
                        <div class="form-text text-muted">O e-mail não pode ser alterado.</div>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3">Alterar Senha</h6>
                    <div class="mb-3">
                        <label for="senha_atual" class="form-label">Senha Atual</label>
                        <input type="password" class="form-control" id="senha_atual" name="senha_atual">
                        <div class="form-text text-muted">Deixe em branco se não deseja alterar a senha.</div>
                    </div>

                    <div class="mb-3">
                        <label for="nova_senha" class="form-label">Nova Senha</label>
                        <input type="password" class="form-control" id="nova_senha" name="nova_senha">
                    </div>

                    <div class="mb-3">
                        <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha">
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?= base_url('perfil') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Voltar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const senhaAtual = document.getElementById('senha_atual');
        const novaSenha = document.getElementById('nova_senha');
        const confirmarSenha = document.getElementById('confirmar_senha');

        form.addEventListener('submit', function(e) {
            // Verificar se está tentando alterar a senha
            if (novaSenha.value || confirmarSenha.value) {
                // Verificar se a senha atual foi informada
                if (!senhaAtual.value) {
                    e.preventDefault();
                    alert('Por favor, informe a senha atual para alterá-la.');
                    senhaAtual.focus();
                    return;
                }

                // Verificar se as senhas coincidem
                if (novaSenha.value !== confirmarSenha.value) {
                    e.preventDefault();
                    alert('A nova senha e a confirmação não coincidem.');
                    novaSenha.focus();
                    return;
                }

                // Verificar força da senha
                if (novaSenha.value.length < 6) {
                    e.preventDefault();
                    alert('A nova senha deve ter pelo menos 6 caracteres.');
                    novaSenha.focus();
                    return;
                }
            }
        });
    });
</script>

<?php
// Incluir o footer
include ROOT_DIR . '/app/views/templates/footer.php';
?>