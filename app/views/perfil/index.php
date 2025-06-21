<?php
// Definir variáveis para o template
$page_title = 'Meu Perfil';
$breadcrumbs = [
    'Perfil' => null
];

// Incluir o header
include ROOT_DIR . '/app/views/templates/header.php';
?>

<div class="row">
    <div class="col-lg-4">
        <!-- Card de perfil -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="profile-avatar mx-auto mb-3">
                    <?php
                    $user_name = $usuario['nome'] ?? 'Usuário';
                    $initials = strtoupper(substr($user_name, 0, 1));
                    if (strpos($user_name, ' ') !== false) {
                        $name_parts = explode(' ', $user_name);
                        $last_name = end($name_parts);
                        $initials .= strtoupper(substr($last_name, 0, 1));
                    }
                    ?>
                    <div class="profile-avatar-text"><?= $initials ?></div>
                </div>
                <h5 class="mb-1"><?= htmlspecialchars($usuario['nome']) ?></h5>
                <p class="text-muted mb-3"><?= htmlspecialchars($usuario['email']) ?></p>
                <p class="mb-2">
                    <span class="badge bg-primary"><?= htmlspecialchars($usuario['admin'] ? 'Administrador' : 'Usuário') ?></span>
                    <?php if ($usuario['admin'] && isset($usuario['admin_tipo'])): ?>
                        <span class="badge bg-info"><?= htmlspecialchars(ucfirst($usuario['admin_tipo'])) ?></span>
                    <?php endif; ?>
                </p>
                <div class="d-flex justify-content-center mt-3">
                    <a href="<?= base_url('perfil/editar') ?>" class="btn btn-sm btn-primary me-2">
                        <i class="fas fa-edit me-1"></i> Editar Perfil
                    </a>
                    <a href="<?= base_url('chamados/meus') ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-ticket-alt me-1"></i> Meus Chamados
                    </a>
                </div>
            </div>
        </div>

        <!-- Card de estatísticas -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Estatísticas de Chamados</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-4 text-center">
                        <h3 class="text-primary"><?= $estatisticas['abertos'] ?? 0 ?></h3>
                        <p class="text-muted mb-0">Abertos</p>
                    </div>
                    <div class="col-4 text-center">
                        <h3 class="text-warning"><?= $estatisticas['em_andamento'] ?? 0 ?></h3>
                        <p class="text-muted mb-0">Em Andamento</p>
                    </div>
                    <div class="col-4 text-center">
                        <h3 class="text-success"><?= $estatisticas['concluidos'] ?? 0 ?></h3>
                        <p class="text-muted mb-0">Concluídos</p>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="<?= base_url('chamados/meus') ?>" class="btn btn-sm btn-outline-primary">Ver Todos</a>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Card de informações -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Informações Pessoais</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Nome Completo</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <?= htmlspecialchars($usuario['nome']) ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">E-mail</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <?= htmlspecialchars($usuario['email']) ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Empresa</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <?= htmlspecialchars($usuario['empresa_nome'] ?? 'Não definida') ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Setor</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <?= htmlspecialchars($usuario['setor_nome'] ?? 'Não definido') ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Status</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <?php if ($usuario['ativo']): ?>
                            <span class="badge bg-success">Ativo</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inativo</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Último Acesso</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <?= $usuario['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) : 'Nunca' ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card de atividade recente -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Atividade Recente</h5>
                <a href="<?= base_url('perfil/atividade') ?>" class="btn btn-sm btn-outline-primary">Ver Tudo</a>
            </div>
            <div class="card-body">
                <?php if (empty($chamados_recentes)): ?>
                    <div class="text-center py-3">
                        <i class="fas fa-clipboard-list fa-2x text-muted mb-3"></i>
                        <p>Nenhuma atividade recente encontrada.</p>
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($chamados_recentes as $chamado): ?>
                            <div class="timeline-item">
                                <?php
                                $icone = 'fas fa-ticket-alt';
                                $cor = 'primary';

                                // Definir ícone e cor com base no status
                                switch ($chamado['status_id']) {
                                    case 1: // Aberto
                                        $icone = 'fas fa-ticket-alt';
                                        $cor = 'primary';
                                        break;
                                    case 2: // Em andamento
                                        $icone = 'fas fa-spinner';
                                        $cor = 'warning';
                                        break;
                                    case 3: // Pendente
                                        $icone = 'fas fa-clock';
                                        $cor = 'info';
                                        break;
                                    case 4: // Concluído
                                        $icone = 'fas fa-check-circle';
                                        $cor = 'success';
                                        break;
                                    case 5: // Cancelado
                                        $icone = 'fas fa-times-circle';
                                        $cor = 'danger';
                                        break;
                                }
                                ?>
                                <div class="timeline-marker bg-<?= $cor ?>">
                                    <i class="<?= $icone ?> fa-sm"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">
                                            <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>">
                                                Chamado #<?= $chamado['id'] ?>
                                            </a>
                                        </h6>
                                        <span class="badge bg-<?= $cor ?>"><?= htmlspecialchars($chamado['status_nome']) ?></span>
                                    </div>
                                    <p class="mb-1"><?= htmlspecialchars($chamado['titulo']) ?></p>
                                    <p class="text-muted mb-0 small">
                                        <?= date('d/m/Y H:i', strtotime($chamado['data_criacao'])) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-500) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }

    .timeline-marker {
        position: absolute;
        left: -1.5rem;
        top: 0;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        border: 2px solid #fff;
        z-index: 1;
    }

    .timeline-item:not(:last-child)::before {
        content: "";
        position: absolute;
        left: -1rem;
        top: 0.75rem;
        bottom: 0;
        width: 2px;
        background-color: #dee2e6;
    }

    .timeline-content {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 0.5rem;
    }
</style>

<?php
// Incluir o footer
include ROOT_DIR . '/app/views/templates/footer.php';
?>