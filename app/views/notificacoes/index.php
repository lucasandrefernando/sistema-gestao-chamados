<?php

/**
 * View para exibição e gerenciamento de notificações
 * 
 * Esta página exibe todas as notificações do usuário logado,
 * permitindo marcar como lidas e excluir notificações.
 * 
 * @package Sistema de Gestão de Chamados
 * @version 3.1.0
 */

// Função auxiliar para formatar o tempo decorrido
if (!function_exists('formatarTempoDecorrido')) {
    function formatarTempoDecorrido($data)
    {
        if (empty($data)) return 'Data desconhecida';

        $timestamp = strtotime($data);
        $agora = time();
        $diferenca = $agora - $timestamp;

        if ($diferenca < 60) {
            return 'Agora mesmo';
        } elseif ($diferenca < 3600) {
            $minutos = floor($diferenca / 60);
            return $minutos . ' ' . ($minutos == 1 ? 'minuto' : 'minutos') . ' atrás';
        } elseif ($diferenca < 86400) {
            $horas = floor($diferenca / 3600);
            return $horas . ' ' . ($horas == 1 ? 'hora' : 'horas') . ' atrás';
        } elseif ($diferenca < 604800) {
            $dias = floor($diferenca / 86400);
            return $dias . ' ' . ($dias == 1 ? 'dia' : 'dias') . ' atrás';
        } elseif ($diferenca < 2592000) {
            $semanas = floor($diferenca / 604800);
            return $semanas . ' ' . ($semanas == 1 ? 'semana' : 'semanas') . ' atrás';
        } else {
            return date('d/m/Y H:i', $timestamp);
        }
    }
}

// Conta notificações não lidas
$naoLidas = 0;
if (!empty($notificacoes)) {
    foreach ($notificacoes as $notificacao) {
        if (!isset($notificacao['lida']) || !$notificacao['lida']) {
            $naoLidas++;
        }
    }
}

// Definir título da página e breadcrumbs
$page_title = 'Notificações';
$breadcrumbs = [
    'Dashboard' => base_url('dashboard'),
    'Notificações' => null
];
?>

<!-- Container principal da página de notificações -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Cabeçalho da página -->
            <div class="notif-page-header">
                <div>
                    <h1 class="notif-page-title">
                        <i class="fas fa-bell notif-icon"></i>
                        Notificações
                    </h1>
                    <p class="notif-page-subtitle">Gerencie suas notificações do sistema</p>
                </div>

                <div class="d-flex align-items-center">
                    <!-- Botão voltar para a página anterior -->
                    <a href="javascript:history.back();" class="notif-btn-back">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>

                    <!-- Botões de ação para todas as notificações -->
                    <div class="notif-actions-global">
                        <?php if (!empty($notificacoes)): ?>
                            <button id="markAllReadBtn" class="btn btn-outline-primary">
                                <i class="fas fa-check-double me-1"></i> Marcar todas
                            </button>
                            <button id="deleteAllBtn" class="btn btn-outline-danger">
                                <i class="fas fa-trash-alt me-1"></i> Excluir todas
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Cartão contendo a lista de notificações -->
            <div class="notif-card">
                <div class="notif-card-header">
                    <h2 class="notif-card-title">Suas notificações
                        <?php if ($naoLidas > 0): ?>
                            <span class="badge bg-primary rounded-pill"><?= $naoLidas ?> não <?= $naoLidas == 1 ? 'lida' : 'lidas' ?></span>
                        <?php endif; ?>
                    </h2>
                </div>

                <div class="notif-card-body">
                    <!-- Lista de notificações -->
                    <div class="notif-list">
                        <?php if (empty($notificacoes)): ?>
                            <!-- Estado vazio - sem notificações -->
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-bell-slash"></i>
                                </div>
                                <h3 class="h5">Nenhuma notificação</h3>
                                <p>Você não tem notificações no momento.</p>
                            </div>
                        <?php else: ?>
                            <!-- Loop de notificações -->
                            <?php foreach ($notificacoes as $index => $notificacao): ?>
                                <div class="notification-item <?= isset($notificacao['lida']) && $notificacao['lida'] ? 'read' : 'unread' ?>"
                                    data-id="<?= $notificacao['id'] ?? 0 ?>"
                                    style="--animation-order: <?= $index ?>">
                                    <!-- Conteúdo da notificação -->
                                    <div class="notification-content">
                                        <!-- Ícone da notificação -->
                                        <div class="notification-icon bg-<?= $notificacao['cor'] ?? 'primary' ?>">
                                            <i class="<?= $notificacao['icone'] ?? 'fas fa-bell' ?>"></i>
                                        </div>

                                        <div class="notification-text">
                                            <!-- Título da notificação -->
                                            <div class="notification-title">
                                                <?= htmlspecialchars($notificacao['titulo'] ?? 'Notificação') ?>
                                            </div>

                                            <!-- Tempo decorrido -->
                                            <div class="notification-time">
                                                <i class="far fa-clock me-1"></i>
                                                <?php if (isset($notificacao['data_criacao']) && !empty($notificacao['data_criacao'])): ?>
                                                    <?= isset($notificacao['tempo']) ? $notificacao['tempo'] : formatarTempoDecorrido($notificacao['data_criacao']) ?>
                                                <?php else: ?>
                                                    Data desconhecida
                                                <?php endif; ?>
                                            </div>

                                            <!-- Mensagem -->
                                            <div class="notification-message">
                                                <?= htmlspecialchars($notificacao['mensagem'] ?? $notificacao['descricao'] ?? '') ?>
                                            </div>

                                            <!-- Link (se existir) -->
                                            <?php if (isset($notificacao['referencia_id']) && isset($notificacao['referencia_tipo']) && $notificacao['referencia_tipo'] == 'chamado'): ?>
                                                <div class="notification-link">
                                                    <a href="<?= base_url("chamados/visualizar/{$notificacao['referencia_id']}") ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-external-link-alt"></i> Ver chamado
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Botões de ação -->
                                    <div class="notification-actions">
                                        <?php if (!isset($notificacao['lida']) || !$notificacao['lida']): ?>
                                            <button class="btn btn-sm btn-outline-primary mark-read-btn"
                                                data-id="<?= $notificacao['id'] ?? 0 ?>"
                                                title="Marcar como lida">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>

                                        <button class="btn btn-sm btn-outline-danger delete-notification-btn"
                                            data-id="<?= $notificacao['id'] ?? 0 ?>"
                                            title="Excluir notificação">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmação para excluir todas as notificações -->
<div class="modal fade notif-modal" id="deleteAllModal" tabindex="-1" aria-labelledby="deleteAllModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAllModalLabel">Confirmar exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                    <h4>Tem certeza?</h4>
                </div>
                <p>Você está prestes a excluir todas as suas notificações. Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteAll">
                    <i class="fas fa-trash-alt me-1"></i> Excluir todas
                </button>
            </div>
        </div>
    </div>
</div>