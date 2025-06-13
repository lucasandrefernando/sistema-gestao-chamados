<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $titulo ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('usuarios') ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= base_url('usuarios/' . ($acao == 'criar' ? 'store' : 'update/' . $usuario['id'])) ?>" method="post">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?= $usuario['nome'] ?? '' ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= $usuario['email'] ?? '' ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="senha" class="form-label"><?= $acao == 'criar' ? 'Senha <span class="text-danger">*</span>' : 'Nova Senha (deixe em branco para manter a atual)' ?></label>
                    <input type="password" class="form-control" id="senha" name="senha" <?= $acao == 'criar' ? 'required' : '' ?>>
                </div>
                <div class="col-md-6">
                    <label for="cargo" class="form-label">Cargo</label>
                    <input type="text" class="form-control" id="cargo" name="cargo" value="<?= $usuario['cargo'] ?? '' ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="empresa_id" class="form-label">Empresa <span class="text-danger">*</span></label>
                    <select class="form-select" id="empresa_id" name="empresa_id" required>
                        <option value="">Selecione uma empresa</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa['id'] ?>" <?= (isset($usuario['empresa_id']) && $usuario['empresa_id'] == $empresa['id']) ? 'selected' : '' ?>>
                                <?= $empresa['nome'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="admin" name="admin" value="1" <?= (isset($usuario['admin']) && $usuario['admin']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="admin">
                            Usuário Administrador
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Salvar
                </button>
            </div>
        </form>
    </div>
</div>