<div class="page-container">
    <!-- Cabeçalho da página -->
    <div class="page-header">
        <div class="page-header-content">
            <div class="page-title-wrapper">
                <h1 class="page-title">
                    <i class="fas fa-user-<?= $acao == 'criar' ? 'plus' : 'edit' ?> page-title-icon"></i>
                    <?= $titulo ?>
                </h1>
                <p class="page-subtitle">
                    <?= $acao == 'criar' ? 'Preencha os dados para criar um novo usuário' : 'Edite as informações do usuário' ?>
                </p>
            </div>
            <div class="page-actions">
                <a href="<?= base_url('usuarios') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <span>Voltar</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Formulário de usuário -->
    <div class="content-card">
        <div class="content-card-header">
            <div class="content-card-title">
                <h2 class="section-title">Informações do Usuário</h2>
            </div>
        </div>
        <div class="content-card-body">
            <form action="<?= base_url('usuarios/' . ($acao == 'criar' ? 'store' : 'update/' . $usuario['id'])) ?>" method="post" class="user-form">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="nome" class="form-label">Nome <span class="required-indicator">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?= $usuario['nome'] ?? '' ?>" required>
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="email" class="form-label">E-mail <span class="required-indicator">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" value="<?= $usuario['email'] ?? '' ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="senha" class="form-label">
                            <?= $acao == 'criar' ? 'Senha <span class="required-indicator">*</span>' : 'Nova Senha <span class="text-muted">(deixe em branco para manter a atual)</span>' ?>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="senha" name="senha" <?= $acao == 'criar' ? 'required' : '' ?>>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword" title="Mostrar/Ocultar Senha">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <?php if ($acao == 'criar'): ?>
                            <div class="form-text">
                                <i class="fas fa-info-circle text-info"></i> A senha deve ter pelo menos 8 caracteres.
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="cargo" class="form-label">Cargo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                            <input type="text" class="form-control" id="cargo" name="cargo" value="<?= $usuario['cargo'] ?? '' ?>" placeholder="Ex: Analista de Suporte">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="empresa_nome" class="form-label">Empresa</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                            <input type="text" class="form-control bg-light" id="empresa_nome" value="<?= $_SESSION['empresa_nome'] ?>" readonly>
                            <input type="hidden" name="empresa_id" value="<?= get_empresa_id() ?>">
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle text-info"></i> Os usuários são criados na empresa atual.
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <div class="admin-options-card">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="admin" name="admin" value="1" <?= (isset($usuario['admin']) && $usuario['admin']) ? 'checked' : '' ?> onchange="toggleAdminTipo()">
                                <label class="form-check-label fw-bold" for="admin">
                                    Usuário Administrador
                                </label>
                            </div>

                            <div id="adminTipoContainer" class="admin-tipo-options" style="display: <?= (isset($usuario['admin']) && $usuario['admin']) ? 'block' : 'none' ?>;">
                                <?php if (is_admin_master()): ?>
                                    <label class="form-label">Tipo de Administrador</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="admin_tipo" id="adminRegular" value="regular" <?= (!isset($usuario['admin_tipo']) || $usuario['admin_tipo'] == 'regular') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="adminRegular">
                                            <span class="badge bg-info">Regular</span>
                                            Pode gerenciar usuários comuns
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="admin_tipo" id="adminMaster" value="master" <?= (isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="adminMaster">
                                            <span class="badge bg-danger">Master</span>
                                            Pode gerenciar licenças, empresas e acessar todas as empresas
                                        </label>
                                    </div>
                                <?php else: ?>
                                    <!-- Para administradores regulares, apenas mostra o tipo sem opção de alterar -->
                                    <?php if (isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master'): ?>
                                        <input type="hidden" name="admin_tipo" value="master">
                                        <div class="alert alert-info">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-info-circle text-info me-2"></i>
                                                <div>Este usuário é um administrador master.</div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <input type="hidden" name="admin_tipo" value="regular">
                                        <div class="alert alert-info">
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

                <div class="form-actions">
                    <a href="<?= base_url('usuarios') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= $acao == 'criar' ? 'Criar Usuário' : 'Salvar Alterações' ?>
                    </button>
                </div>
            </form>
        </div>
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