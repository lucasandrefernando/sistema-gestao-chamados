<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $titulo ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('licencas') ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= base_url('licencas/' . ($acao == 'criar' ? 'store' : 'update/' . $licenca['id'])) ?>" method="post">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="empresa_id" class="form-label">Empresa <span class="text-danger">*</span></label>
                    <select class="form-select" id="empresa_id" name="empresa_id" required>
                        <option value="">Selecione uma empresa</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa['id'] ?>" <?= (isset($licenca['empresa_id']) && $licenca['empresa_id'] == $empresa['id']) ? 'selected' : '' ?>>
                                <?= $empresa['nome'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="quantidade" class="form-label">Quantidade <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="quantidade" name="quantidade" value="<?= $licenca['quantidade'] ?? '1' ?>" min="1" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="data_inicio" class="form-label">Data de Início <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?= isset($licenca['data_inicio']) ? date('Y-m-d', strtotime($licenca['data_inicio'])) : date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="data_fim" class="form-label">Data de Fim <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?= isset($licenca['data_fim']) ? date('Y-m-d', strtotime($licenca['data_fim'])) : date('Y-m-d', strtotime('+1 year')) ?>" required>
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