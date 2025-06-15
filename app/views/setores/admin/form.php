<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $titulo ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('setores/admin') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="<?= base_url('setores/' . ($acao == 'criar' ? 'store' : 'update/' . $setor['id'])) ?>" method="post">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nome" name="nome" value="<?= isset($setor['nome']) ? htmlspecialchars($setor['nome']) : '' ?>" required>
            </div>

            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição</label>
                <textarea class="form-control" id="descricao" name="descricao" rows="3"><?= isset($setor['descricao']) ? htmlspecialchars($setor['descricao']) : '' ?></textarea>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="ativo" name="ativo" <?= !isset($setor['ativo']) || $setor['ativo'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="ativo">Ativo</label>
            </div>

            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="<?= base_url('setores/admin') ?>" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>