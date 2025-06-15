<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $titulo ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('setores') ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Informações do Setor</h5>
    </div>
    <div class="card-body">
        <form action="<?= base_url('setores/' . ($acao == 'criar' ? 'store' : 'update/' . $setor['id'])) ?>" method="post">
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-sitemap"></i></span>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?= $setor['nome'] ?? '' ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="empresa_nome" class="form-label">Empresa</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                        <input type="text" class="form-control bg-light" id="empresa_nome" value="<?= $_SESSION['empresa_nome'] ?>" readonly>
                        <input type="hidden" name="empresa_id" value="<?= get_empresa_id() ?>">
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <label for="descricao" class="form-label">Descrição</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-align-left"></i></span>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3"><?= $setor['descricao'] ?? '' ?></textarea>
                    </div>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1 text-info"></i> Uma breve descrição sobre o setor e suas responsabilidades.
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-light bg-light">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1" <?= (isset($setor['ativo']) && $setor['ativo']) || !isset($setor['ativo']) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold" for="ativo">
                                    Setor Ativo
                                </label>
                            </div>
                            <div class="form-text mt-2">
                                <i class="fas fa-info-circle me-1 text-info"></i> Setores inativos não aparecem nas listas de seleção.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="<?= base_url('setores') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> <?= $acao == 'criar' ? 'Criar Setor' : 'Salvar Alterações' ?>
                </button>
            </div>
        </form>
    </div>
</div>