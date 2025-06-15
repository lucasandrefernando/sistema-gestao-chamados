<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        Chamado #<?= $chamado['id'] ?>
        <span class="badge bg-<?= getStatusColor(strtolower(str_replace(' ', '_', $status['nome']))) ?> ms-2">
            <?= htmlspecialchars($status['nome']) ?>
        </span>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?= base_url('chamados/listar') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
            <a href="<?= base_url('chamados/editar/' . $chamado['id']) ?>" class="btn btn-sm btn-outline-warning">
                <i class="fas fa-edit me-1"></i> Editar
            </a>
        </div>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#alterarStatusModal">
            <i class="fas fa-exchange-alt me-1"></i> Alterar Status
        </button>
    </div>
</div>

<div class="row mb-4">
    <!-- Informações do Chamado -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Informações do Chamado</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Informações Gerais</h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="150">Solicitante:</th>
                                <td><?= htmlspecialchars($chamado['solicitante']) ?></td>
                            </tr>
                            <tr>
                                <th>Setor:</th>
                                <td>
                                    <?= htmlspecialchars($setor['nome']) ?>
                                    <button type="button" class="btn btn-sm btn-link p-0 ms-2" data-bs-toggle="modal" data-bs-target="#transferirSetorModal">
                                        <i class="fas fa-exchange-alt"></i> Transferir
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge bg-<?= getStatusColor(strtolower(str_replace(' ', '_', $status['nome']))) ?>">
                                        <?= htmlspecialchars($status['nome']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Tipo de Serviço:</th>
                                <td><?= htmlspecialchars($chamado['tipo_servico'] ?? 'Não especificado') ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Datas</h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="150">Data de Solicitação:</th>
                                <td><?= formatarData($chamado['data_solicitacao']) ?></td>
                            </tr>
                            <?php if (!empty($chamado['data_conclusao'])): ?>
                                <tr>
                                    <th>Data de Conclusão:</th>
                                    <td><?= formatarData($chamado['data_conclusao']) ?></td>
                                </tr>
                                <tr>
                                    <th>Tempo de Atendimento:</th>
                                    <td>
                                        <?php
                                        $inicio = new DateTime($chamado['data_solicitacao']);
                                        $fim = new DateTime($chamado['data_conclusao']);
                                        $diff = $inicio->diff($fim);

                                        $tempoFormatado = '';
                                        if ($diff->d > 0) {
                                            $tempoFormatado .= $diff->d . ' dia(s), ';
                                        }
                                        $tempoFormatado .= sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
                                        echo $tempoFormatado;
                                        ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <th>Tempo em Aberto:</th>
                                    <td>
                                        <?php
                                        $inicio = new DateTime($chamado['data_solicitacao']);
                                        $agora = new DateTime();
                                        $diff = $inicio->diff($agora);

                                        $tempoFormatado = '';
                                        if ($diff->d > 0) {
                                            $tempoFormatado .= $diff->d . ' dia(s), ';
                                        }
                                        $tempoFormatado .= sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
                                        echo $tempoFormatado;
                                        ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <?php if (!empty($chamado['paciente']) || !empty($chamado['quarto_leito'])): ?>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="fw-bold">Informações do Paciente</h6>
                            <table class="table table-sm">
                                <?php if (!empty($chamado['paciente'])): ?>
                                    <tr>
                                        <th width="150">Paciente:</th>
                                        <td><?= htmlspecialchars($chamado['paciente']) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (!empty($chamado['quarto_leito'])): ?>
                                    <tr>
                                        <th>Quarto/Leito:</th>
                                        <td><?= htmlspecialchars($chamado['quarto_leito']) ?></td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <h6 class="fw-bold">Descrição</h6>
                        <div class="p-3 bg-light rounded">
                            <?= nl2br(htmlspecialchars($chamado['descricao'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comentários -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Comentários</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($comentarios)): ?>
                    <div class="comentarios-lista mb-4">
                        <?php foreach ($comentarios as $comentario): ?>
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <div class="avatar bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><?= htmlspecialchars($comentario['usuario_nome'] ?? 'Usuário') ?></h6>
                                        <small class="text-muted"><?= formatarData($comentario['data_criacao']) ?></small>
                                    </div>
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($comentario['comentario'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Nenhum comentário ainda.</p>
                <?php endif; ?>

                <form action="<?= base_url('chamados/adicionarComentario/' . $chamado['id']) ?>" method="post">
                    <div class="mb-3">
                        <label for="comentario" class="form-label">Adicionar Comentário</label>
                        <textarea class="form-control" id="comentario" name="comentario" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar Comentário</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Histórico e Ações Rápidas -->
    <div class="col-md-4">
        <!-- Ações Rápidas -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Ações Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#alterarStatusModal">
                        <i class="fas fa-exchange-alt me-1"></i> Alterar Status
                    </button>
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#transferirSetorModal">
                        <i class="fas fa-random me-1"></i> Transferir para Outro Setor
                    </button>
                    <a href="<?= base_url('chamados/editar/' . $chamado['id']) ?>" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i> Editar Chamado
                    </a>
                    <a href="<?= base_url('chamados/imprimir/' . $chamado['id']) ?>" class="btn btn-secondary">
                        <i class="fas fa-print me-1"></i> Imprimir Chamado
                    </a>
                </div>
            </div>
        </div>

        <!-- Histórico -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Histórico do Chamado</h5>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($historico)): ?>
                    <div class="timeline p-3">
                        <?php foreach ($historico as $index => $item): ?>
                            <div class="timeline-item pb-3">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1"><?= formatarData($item['data_criacao']) ?></h6>
                                    <p class="mb-1">
                                        <?php if ($item['status_id_anterior'] != $item['status_id_novo']): ?>
                                            Status alterado de
                                            <span class="badge bg-<?= getStatusColor(strtolower(str_replace(' ', '_', $item['status_anterior_nome']))) ?>">
                                                <?= htmlspecialchars($item['status_anterior_nome']) ?>
                                            </span>
                                            para
                                            <span class="badge bg-<?= getStatusColor(strtolower(str_replace(' ', '_', $item['status_novo_nome']))) ?>">
                                                <?= htmlspecialchars($item['status_novo_nome']) ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if ($item['setor_id_anterior'] != $item['setor_id_novo']): ?>
                                            <?= $item['status_id_anterior'] != $item['status_id_novo'] ? '<br>' : '' ?>
                                            Transferido do setor
                                            <strong><?= htmlspecialchars($item['setor_anterior_nome']) ?></strong>
                                            para
                                            <strong><?= htmlspecialchars($item['setor_novo_nome']) ?></strong>
                                        <?php endif; ?>
                                    </p>
                                    <?php if (!empty($item['observacao'])): ?>
                                        <p class="text-muted mb-0"><?= htmlspecialchars($item['observacao']) ?></p>
                                    <?php endif; ?>
                                    <small class="text-muted">Por: <?= htmlspecialchars($item['usuario_nome'] ?? 'Sistema') ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted p-3">Nenhum histórico disponível.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Alterar Status -->
<div class="modal fade" id="alterarStatusModal" tabindex="-1" aria-labelledby="alterarStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('chamados/alterarStatus/' . $chamado['id']) ?>" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="alterarStatusModalLabel">Alterar Status do Chamado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status_id" class="form-label">Novo Status</label>
                        <select class="form-select" id="status_id" name="status_id" required>
                            <option value="">Selecione um status</option>
                            <?php foreach ($statusDisponiveis as $statusItem): ?>
                                <option value="<?= $statusItem['id'] ?>" <?= $chamado['status_id'] == $statusItem['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($statusItem['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="observacao" class="form-label">Observação</label>
                        <textarea class="form-control" id="observacao" name="observacao" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alteração</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Transferir Setor -->
<div class="modal fade" id="transferirSetorModal" tabindex="-1" aria-labelledby="transferirSetorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('chamados/transferirSetor/' . $chamado['id']) ?>" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="transferirSetorModalLabel">Transferir Chamado para Outro Setor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="setor_id" class="form-label">Novo Setor</label>
                        <select class="form-select" id="setor_id" name="setor_id" required>
                            <option value="">Selecione um setor</option>
                            <?php foreach ($setoresDisponiveis as $setorItem): ?>
                                <option value="<?= $setorItem['id'] ?>" <?= $chamado['setor_id'] == $setorItem['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($setorItem['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="observacao" class="form-label">Motivo da Transferência</label>
                        <textarea class="form-control" id="observacao" name="observacao" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Transferir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Estilo para o histórico em timeline */
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline-item {
        position: relative;
    }

    .timeline-marker {
        position: absolute;
        left: -30px;
        top: 0;
        width: 15px;
        height: 15px;
        border-radius: 50%;
        background-color: #0d6efd;
        border: 2px solid #fff;
    }

    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: -23px;
        top: 15px;
        bottom: 0;
        width: 2px;
        background-color: #dee2e6;
    }
</style>