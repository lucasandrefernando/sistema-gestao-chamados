<div class="setores-container">
    <div class="setores-header">
        <h1 class="setores-title">Gestão de Setores</h1>
        <div class="setores-actions">
            <div class="setores-filter-group">
                <button type="button" class="setores-btn filter-btn <?= !isset($mostrarRemovidos) || !$mostrarRemovidos ? 'active' : '' ?>"
                    onclick="window.location.href='<?= base_url('setores') ?>'">
                    <i class="fas fa-check-circle"></i> Ativos
                </button>
                <button type="button" class="setores-btn filter-btn <?= isset($mostrarRemovidos) && $mostrarRemovidos ? 'active-danger' : '' ?>"
                    onclick="window.location.href='<?= base_url('setores?mostrar_removidos=1') ?>'">
                    <i class="fas fa-trash"></i> Removidos
                </button>
            </div>
            <a href="<?= base_url('setores/criar') ?>" class="setores-btn create-btn">
                <i class="fas fa-plus"></i> Novo Setor
            </a>
        </div>
    </div>

    <?php if (isset($mostrarRemovidos) && $mostrarRemovidos): ?>
        <div class="setores-alert info-alert">
            <div class="alert-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="alert-content">
                <h5>Visualizando setores removidos</h5>
                <p>Estes setores foram removidos do sistema, mas seus dados ainda estão armazenados. Você pode restaurá-los se necessário.</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="setores-card">
        <div class="setores-card-header">
            <h5 class="setores-card-title">
                <?= isset($mostrarRemovidos) && $mostrarRemovidos ? 'Setores Removidos' : 'Setores Ativos' ?>
            </h5>
            <div class="setores-search">
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="setoresSearch" class="search-input" placeholder="Buscar setores...">
                </div>
            </div>
        </div>
        <div class="setores-card-body">
            <div class="setores-table-container">
                <table class="setores-table" id="setoresTable">
                    <thead>
                        <tr>
                            <th class="col-id">#</th>
                            <th class="col-nome">Nome</th>
                            <th class="col-descricao">Descrição</th>
                            <th class="col-status">Status</th>
                            <th class="col-chamados">Chamados</th>
                            <th class="col-usuarios">Usuários</th>
                            <?php if (isset($mostrarRemovidos) && $mostrarRemovidos): ?>
                                <th class="col-data">Removido em</th>
                            <?php endif; ?>
                            <th class="col-acoes">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($setores) && !empty($setores)): ?>
                            <?php foreach ($setores as $setor): ?>
                                <tr class="<?= isset($setor['removido']) && $setor['removido'] ? 'row-removed' : '' ?>">
                                    <td class="col-id"><?= $setor['id'] ?></td>
                                    <td class="col-nome">
                                        <div class="setor-info">
                                            <div class="setor-avatar" style="background-color: <?= gerarCorAvatar($setor['nome']) ?>">
                                                <?= strtoupper(substr($setor['nome'], 0, 1)) ?>
                                            </div>
                                            <span class="setor-nome"><?= $setor['nome'] ?></span>
                                        </div>
                                    </td>
                                    <td class="col-descricao"><?= $setor['descricao'] ?? '<span class="text-muted">Sem descrição</span>' ?></td>
                                    <td class="col-status">
                                        <?php if (isset($setor['removido']) && $setor['removido']): ?>
                                            <span class="status-badge removed">Removido</span>
                                        <?php elseif ($setor['ativo']): ?>
                                            <span class="status-badge active">Ativo</span>
                                        <?php else: ?>
                                            <span class="status-badge inactive">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-chamados">
                                        <div class="counter-badge info"><?= $setor['total_chamados'] ?></div>
                                    </td>
                                    <td class="col-usuarios">
                                        <div class="counter-badge secondary"><?= $setor['total_usuarios'] ?></div>
                                    </td>
                                    <?php if (isset($mostrarRemovidos) && $mostrarRemovidos): ?>
                                        <td class="col-data">
                                            <?php if (isset($setor['removido']) && $setor['removido'] && isset($setor['data_remocao'])): ?>
                                                <div class="data-remocao">
                                                    <i class="far fa-calendar-times"></i>
                                                    <?= date('d/m/Y H:i', strtotime($setor['data_remocao'])) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                    <td class="col-acoes">
                                        <div class="acoes-grupo">
                                            <?php if (isset($setor['removido']) && $setor['removido']): ?>
                                                <button type="button" class="acao-btn restaurar"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#restaurarModal"
                                                    data-id="<?= $setor['id'] ?>"
                                                    data-nome="<?= $setor['nome'] ?>"
                                                    data-data="<?= date('d/m/Y H:i', strtotime($setor['data_remocao'])) ?>">
                                                    <i class="fas fa-trash-restore"></i>
                                                    <span class="tooltip-text">Restaurar</span>
                                                </button>
                                            <?php else: ?>
                                                <a href="<?= base_url('setores/editar/' . $setor['id']) ?>" class="acao-btn editar">
                                                    <i class="fas fa-edit"></i>
                                                    <span class="tooltip-text">Editar</span>
                                                </a>
                                                <a href="<?= base_url('setores/toggle/' . $setor['id']) ?>" class="acao-btn <?= $setor['ativo'] ? 'desativar' : 'ativar' ?>">
                                                    <i class="fas <?= $setor['ativo'] ? 'fa-ban' : 'fa-check' ?>"></i>
                                                    <span class="tooltip-text"><?= $setor['ativo'] ? 'Desativar' : 'Ativar' ?></span>
                                                </a>
                                                <?php if ($setor['total_chamados'] == 0 && $setor['total_usuarios'] == 0): ?>
                                                    <button type="button" class="acao-btn remover"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#removerModal"
                                                        data-id="<?= $setor['id'] ?>"
                                                        data-nome="<?= $setor['nome'] ?>">
                                                        <i class="fas fa-trash"></i>
                                                        <span class="tooltip-text">Remover</span>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="acao-btn remover disabled"
                                                        title="Não é possível remover este setor pois existem chamados ou usuários associados a ele">
                                                        <i class="fas fa-trash"></i>
                                                        <span class="tooltip-text">Não pode remover</span>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="empty-row">
                                <td colspan="<?= isset($mostrarRemovidos) && $mostrarRemovidos ? '8' : '7' ?>">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-search"></i>
                                        </div>
                                        <p>Nenhum setor <?= isset($mostrarRemovidos) && $mostrarRemovidos ? 'removido' : '' ?> encontrado.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Remoção -->
<div class="modal fade" id="removerModal" tabindex="-1" aria-labelledby="removerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-danger">
                <h5 class="modal-title" id="removerModalLabel">Confirmar Remoção</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-icon-container">
                    <div class="modal-icon danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <h5 class="modal-message">Tem certeza que deseja remover este setor?</h5>
                <div class="modal-alert warning">
                    <i class="fas fa-info-circle"></i>
                    <span>O setor será marcado como removido, mas seus dados permanecerão no sistema. Você poderá restaurá-lo posteriormente se necessário.</span>
                </div>
                <div class="modal-info-card">
                    <div class="info-item">
                        <span class="info-label">Nome:</span>
                        <span class="info-value" id="removerNome"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn cancel" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmarRemover" class="modal-btn confirm-danger">Confirmar Remoção</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Restauração -->
<div class="modal fade" id="restaurarModal" tabindex="-1" aria-labelledby="restaurarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-success">
                <h5 class="modal-title" id="restaurarModalLabel">Confirmar Restauração</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-icon-container">
                    <div class="modal-icon success">
                        <i class="fas fa-trash-restore"></i>
                    </div>
                </div>
                <h5 class="modal-message">Tem certeza que deseja restaurar este setor?</h5>
                <div class="modal-alert info">
                    <i class="fas fa-info-circle"></i>
                    <span>O setor será restaurado e poderá ser utilizado novamente.</span>
                </div>
                <div class="modal-info-card">
                    <div class="info-item">
                        <span class="info-label">Nome:</span>
                        <span class="info-value" id="restaurarNome"></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Removido em:</span>
                        <span class="info-value" id="restaurarData"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn cancel" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmarRestaurar" class="modal-btn confirm-success">Confirmar Restauração</a>
            </div>
        </div>
    </div>
</div>

<!-- Adicione esta função PHP no arquivo apropriado -->
<?php
function gerarCorAvatar($nome)
{
    // Gera uma cor baseada no nome do setor
    $hash = md5($nome);
    $r = hexdec(substr($hash, 0, 2));
    $g = hexdec(substr($hash, 2, 2));
    $b = hexdec(substr($hash, 4, 2));

    // Ajusta para garantir cores mais vibrantes
    $r = min(max(intval($r * 0.7 + 80), 100), 220);
    $g = min(max(intval($g * 0.7 + 80), 100), 220);
    $b = min(max(intval($b * 0.7 + 80), 100), 220);

    return "rgb($r, $g, $b)";
}
?>