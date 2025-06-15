<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Confirmar Exclusão de Empresa</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= base_url('empresas') ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="card border-danger mb-4">
    <div class="card-header bg-danger text-white">
        <i class="fas fa-exclamation-triangle me-2"></i> Atenção: Esta ação não pode ser desfeita!
    </div>
    <div class="card-body">
        <h5 class="card-title">Você está prestes a excluir a empresa:</h5>
        <div class="alert alert-warning">
            <h4><?= $empresa['nome'] ?></h4>
            <p><strong>CNPJ:</strong> <?= $empresa['cnpj'] ?></p>
            <p><strong>E-mail:</strong> <?= $empresa['email'] ?></p>
        </div>

        <h5 class="mt-4">Os seguintes dados serão excluídos permanentemente:</h5>

        <div class="row mt-3">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h5 class="card-title"><?= count($dependencias['usuarios']) ?> Usuários</h5>
                        <?php if (!empty($dependencias['usuarios'])): ?>
                            <ul class="list-group list-group-flush text-start mt-3">
                                <?php foreach (array_slice($dependencias['usuarios'], 0, 5) as $usuario): ?>
                                    <li class="list-group-item"><?= $usuario['nome'] ?></li>
                                <?php endforeach; ?>
                                <?php if (count($dependencias['usuarios']) > 5): ?>
                                    <li class="list-group-item text-muted">E mais <?= count($dependencias['usuarios']) - 5 ?> usuário(s)...</li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-key fa-3x text-success mb-3"></i>
                        <h5 class="card-title"><?= count($dependencias['licencas']) ?> Licenças</h5>
                        <?php if (!empty($dependencias['licencas'])): ?>
                            <ul class="list-group list-group-flush text-start mt-3">
                                <?php foreach (array_slice($dependencias['licencas'], 0, 5) as $licenca): ?>
                                    <li class="list-group-item">
                                        <?= $licenca['quantidade'] ?> licença(s) -
                                        Válida até: <?= date('d/m/Y', strtotime($licenca['data_fim'])) ?>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (count($dependencias['licencas']) > 5): ?>
                                    <li class="list-group-item text-muted">E mais <?= count($dependencias['licencas']) - 5 ?> licença(s)...</li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-ticket-alt fa-3x text-warning mb-3"></i>
                        <h5 class="card-title"><?= count($dependencias['chamados']) ?> Chamados</h5>
                        <?php if (!empty($dependencias['chamados'])): ?>
                            <ul class="list-group list-group-flush text-start mt-3">
                                <?php foreach (array_slice($dependencias['chamados'], 0, 5) as $chamado): ?>
                                    <li class="list-group-item">
                                        #<?= $chamado['id'] ?> -
                                        <?= date('d/m/Y', strtotime($chamado['data_solicitacao'])) ?>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (count($dependencias['chamados']) > 5): ?>
                                    <li class="list-group-item text-muted">E mais <?= count($dependencias['chamados']) - 5 ?> chamado(s)...</li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <form action="<?= base_url('empresas/excluir/' . $empresa['id']) ?>" method="post" class="mt-4" id="form-exclusao">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

            <div class="alert alert-danger">
                <p class="mb-0"><strong>Para confirmar a exclusão, digite "SIM" no campo abaixo:</strong></p>
            </div>

            <div class="mb-3">
                <input type="text" class="form-control" name="confirmar" id="confirmar" placeholder="Digite SIM para confirmar" required>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="<?= base_url('empresas') ?>" class="btn btn-secondary me-md-2">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-danger" id="btn-excluir" disabled>
                    <i class="fas fa-trash-alt me-1"></i> Excluir Permanentemente
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const confirmarInput = document.getElementById('confirmar');
        const btnExcluir = document.getElementById('btn-excluir');
        const formExclusao = document.getElementById('form-exclusao');

        // Habilita/desabilita o botão de exclusão com base no valor do campo de confirmação
        confirmarInput.addEventListener('input', function() {
            btnExcluir.disabled = this.value !== 'SIM';
        });

        // Confirmação adicional ao enviar o formulário
        formExclusao.addEventListener('submit', function(e) {
            if (!confirm('ATENÇÃO: Você está prestes a excluir permanentemente esta empresa e todos os seus dados relacionados. Esta ação NÃO PODE ser desfeita. Deseja continuar?')) {
                e.preventDefault();
            }
        });
    });
</script>