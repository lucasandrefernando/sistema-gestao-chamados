<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $titulo ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('empresas') ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="<?= base_url('empresas/' . ($acao == 'criar' ? 'store' : 'update/' . $empresa['id'])) ?>" method="post">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?= $empresa['nome'] ?? '' ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="cnpj" class="form-label">CNPJ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="cnpj" name="cnpj" value="<?= $empresa['cnpj'] ?? '' ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= $empresa['email'] ?? '' ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" class="form-control" id="telefone" name="telefone" value="<?= $empresa['telefone'] ?? '' ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="endereco" class="form-label">Endereço</label>
                    <input type="text" class="form-control" id="endereco" name="endereco" value="<?= $empresa['endereco'] ?? '' ?>">
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