<?php
// Definir variáveis para o template
$page_title = 'Histórico de Atividades';
$breadcrumbs = [
    'Perfil' => base_url('perfil'),
    'Atividades' => null
];

// Incluir o header
include ROOT_DIR . '/app/views/templates/header.php';
?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Histórico de Atividades</h5>
    </div>
    <div class="card-body">
        <?php if (empty($atividades)): ?>
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5>Nenhuma atividade encontrada</h5>
                <p class="text-muted">Você ainda não possui atividades registradas no sistema.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Chamado</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($atividades as $atividade): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($atividade['data'])) ?></td>
                                <td>
                                    <?php
                                    $icone = 'fas fa-clipboard-list';
                                    $texto = 'Atividade';

                                    switch ($atividade['tipo']) {
                                        case 'chamado_criado':
                                            $icone = 'fas fa-plus-circle';
                                            $texto = 'Chamado criado';
                                            break;
                                        case 'comentario_adicionado':
                                            $icone = 'fas fa-comment';
                                            $texto = 'Comentário adicionado';
                                            break;
                                        case 'chamado_atualizado':
                                            $icone = 'fas fa-sync-alt';
                                            $texto = 'Chamado atualizado';
                                            break;
                                    }
                                    ?>
                                    <span><i class="<?= $icone ?> me-1"></i> <?= $texto ?></span>
                                </td>
                                <td>
                                    <a href="<?= base_url('chamados/visualizar/' . $atividade['id']) ?>">
                                        #<?= $atividade['id'] ?> - <?= htmlspecialchars($atividade['titulo']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php
                                    $cor = 'primary';

                                    switch ($atividade['status_id']) {
                                        case 1: // Aberto
                                            $cor = 'primary';
                                            break;
                                        case 2: // Em andamento
                                            $cor = 'warning';
                                            break;
                                        case 3: // Pendente
                                            $cor = 'info';
                                            break;
                                        case 4: // Concluído
                                            $cor = 'success';
                                            break;
                                        case 5: // Cancelado
                                            $cor = 'danger';
                                            break;
                                    }
                                    ?>
                                    <span class="badge bg-<?= $cor ?>"><?= htmlspecialchars($atividade['status_nome']) ?></span>
                                </td>
                                <td>
                                    <a href="<?= base_url('chamados/visualizar/' . $atividade['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Incluir o footer
include ROOT_DIR . '/app/views/templates/footer.php';
?>