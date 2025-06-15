<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $titulo ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('usuarios') ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Informações do Usuário</h5>
    </div>
    <div class="card-body">
        <form action="<?= base_url('usuarios/' . ($acao == 'criar' ? 'store' : 'update/' . $usuario['id'])) ?>" method="post">
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?= $usuario['nome'] ?? '' ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" value="<?= $usuario['email'] ?? '' ?>" required>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="senha" class="form-label"><?= $acao == 'criar' ? 'Senha <span class="text-danger">*</span>' : 'Nova Senha <small class="text-muted">(deixe em branco para manter a atual)</small>' ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="senha" name="senha" <?= $acao == 'criar' ? 'required' : '' ?>>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" title="Mostrar/Ocultar Senha">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <?php if ($acao == 'criar'): ?>
                        <div class="form-text mt-1">
                            <i class="fas fa-info-circle me-1 text-info"></i> A senha deve ter pelo menos 8 caracteres.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="cargo" class="form-label">Cargo</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                        <input type="text" class="form-control" id="cargo" name="cargo" value="<?= $usuario['cargo'] ?? '' ?>" placeholder="Ex: Analista de Suporte">
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="empresa_nome" class="form-label">Empresa</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                        <input type="text" class="form-control bg-light" id="empresa_nome" value="<?= $_SESSION['empresa_nome'] ?>" readonly>
                        <input type="hidden" name="empresa_id" value="<?= get_empresa_id() ?>">
                    </div>
                    <div class="form-text mt-1">
                        <i class="fas fa-info-circle me-1 text-info"></i> Os usuários são criados na empresa atual.
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-light bg-light mt-4">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="admin" name="admin" value="1" <?= (isset($usuario['admin']) && $usuario['admin']) ? 'checked' : '' ?> onchange="toggleAdminTipo()">
                                <label class="form-check-label fw-bold" for="admin">
                                    Usuário Administrador
                                </label>
                            </div>

                            <div id="adminTipoContainer" class="mt-3" style="display: <?= (isset($usuario['admin']) && $usuario['admin']) ? 'block' : 'none' ?>;">
                                <?php if (is_admin_master()): ?>
                                    <label class="form-label">Tipo de Administrador</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="admin_tipo" id="adminRegular" value="regular" <?= (!isset($usuario['admin_tipo']) || $usuario['admin_tipo'] == 'regular') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="adminRegular">
                                            <span class="badge bg-info me-1">Regular</span>
                                            Pode gerenciar usuários comuns
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="admin_tipo" id="adminMaster" value="master" <?= (isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="adminMaster">
                                            <span class="badge bg-danger me-1">Master</span>
                                            Pode gerenciar licenças, empresas e acessar todas as empresas
                                        </label>
                                    </div>
                                <?php else: ?>
                                    <!-- Para administradores regulares, apenas mostra o tipo sem opção de alterar -->
                                    <?php if (isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master'): ?>
                                        <input type="hidden" name="admin_tipo" value="master">
                                        <div class="alert alert-info py-2">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-info-circle text-info me-2"></i>
                                                <div>Este usuário é um administrador master.</div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <input type="hidden" name="admin_tipo" value="regular">
                                        <div class="alert alert-info py-2">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-info-circle text-info me-2"></i>
                                                <div>Este usuário será um administrador regular (pode gerenciar usuários).</div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="<?= base_url('usuarios') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> <?= $acao == 'criar' ? 'Criar Usuário' : 'Salvar Alterações' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleAdminTipo() {
        var adminCheckbox = document.getElementById('admin');
        var adminTipoContainer = document.getElementById('adminTipoContainer');

        if (adminTipoContainer) {
            adminTipoContainer.style.display = adminCheckbox.checked ? 'block' : 'none';
        }
    }

    // Mostrar/ocultar senha
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('togglePassword');
        const senhaInput = document.getElementById('senha');

        togglePassword.addEventListener('click', function() {
            const type = senhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
            senhaInput.setAttribute('type', type);

            // Alterna o ícone
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    });
</script>