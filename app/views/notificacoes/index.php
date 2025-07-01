<?php

/**
 * View para exibição e gerenciamento de notificações
 * 
 * Esta página exibe todas as notificações do usuário logado,
 * permitindo marcar como lidas e excluir notificações.
 * 
 * @package Sistema de Gestão de Chamados
 * @version 2.0.0
 */
?>

<!-- Container principal da página de notificações -->
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Cabeçalho da página -->
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title">Notificações</h1>
                    <p class="page-subtitle">Gerencie suas notificações do sistema</p>
                </div>

                <!-- Botões de ação para todas as notificações -->
                <div class="notification-actions-global">
                    <?php if (!empty($notificacoes)): ?>
                        <button id="markAllReadBtn" class="btn btn-sm btn-outline-primary me-2">
                            <i class="fas fa-check-double me-1"></i> Marcar todas como lidas
                        </button>
                        <button id="deleteAllBtn" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-trash-alt me-1"></i> Excluir todas
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cartão contendo a lista de notificações -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Título do cartão -->
                    <div class="card-title d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0">Suas notificações</h2>

                        <!-- Contador de notificações não lidas -->
                        <?php
                        // Conta quantas notificações não foram lidas
                        $naoLidas = 0;
                        foreach ($notificacoes as $notificacao) {
                            if (!isset($notificacao['lida']) || !$notificacao['lida']) {
                                $naoLidas++;
                            }
                        }
                        ?>
                        <span class="badge bg-primary"><?= $naoLidas ?> não <?= $naoLidas == 1 ? 'lida' : 'lidas' ?></span>
                    </div>

                    <!-- Separador -->
                    <hr>

                    <!-- Lista de notificações -->
                    <div class="notifications-list">
                        <?php if (empty($notificacoes)): ?>
                            <!-- Mensagem exibida quando não há notificações -->
                            <div class="empty-state text-center py-5">
                                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                <h3 class="h5">Nenhuma notificação</h3>
                                <p class="text-muted">Você não tem notificações no momento.</p>
                            </div>
                        <?php else: ?>
                            <!-- Loop através das notificações -->
                            <?php foreach ($notificacoes as $notificacao): ?>
                                <!-- Item de notificação individual -->
                                <div class="notification-item <?= isset($notificacao['lida']) && $notificacao['lida'] ? 'read' : 'unread' ?>"
                                    data-id="<?= $notificacao['id'] ?? 0 ?>">
                                    <!-- Conteúdo da notificação -->
                                    <div class="notification-content">
                                        <!-- Título da notificação -->
                                        <div class="notification-title">
                                            <?= htmlspecialchars($notificacao['titulo'] ?? 'Notificação') ?>
                                        </div>

                                        <!-- Tempo decorrido desde a criação da notificação -->
                                        <div class="notification-time">
                                            <?php if (isset($notificacao['data_criacao']) && !empty($notificacao['data_criacao'])): ?>
                                                <?= isset($notificacao['tempo']) ? $notificacao['tempo'] : formatarTempoDecorrido($notificacao['data_criacao']) ?>
                                            <?php else: ?>
                                                Data desconhecida
                                            <?php endif; ?>
                                        </div>

                                        <!-- Mensagem da notificação -->
                                        <div class="notification-message">
                                            <?= htmlspecialchars($notificacao['mensagem'] ?? $notificacao['descricao'] ?? '') ?>
                                        </div>

                                        <!-- Link da notificação, se existir -->
                                        <?php if (isset($notificacao['referencia_id']) && isset($notificacao['referencia_tipo']) && $notificacao['referencia_tipo'] == 'chamado'): ?>
                                            <div class="notification-link mt-2">
                                                <a href="<?= base_url("chamados/visualizar/{$notificacao['referencia_id']}") ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-external-link-alt me-1"></i> Ver chamado
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Botões de ação para a notificação individual -->
                                    <div class="notification-actions">
                                        <!-- Botão para marcar como lida (apenas para notificações não lidas) -->
                                        <?php if (!isset($notificacao['lida']) || !$notificacao['lida']): ?>
                                            <button class="btn btn-sm btn-outline-primary mark-read-btn"
                                                data-id="<?= $notificacao['id'] ?? 0 ?>"
                                                title="Marcar como lida">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>

                                        <!-- Botão para excluir a notificação -->
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
<div class="modal fade" id="deleteAllModal" tabindex="-1" aria-labelledby="deleteAllModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAllModalLabel">Confirmar exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir todas as suas notificações? Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteAll">Excluir todas</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para gerenciar as notificações -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Definição da URL base para as requisições AJAX
        const BASE_URL = '<?= base_url() ?>/';

        // Referências aos elementos da página
        const notificationsList = document.querySelector('.notifications-list');
        const markAllReadBtn = document.getElementById('markAllReadBtn');
        const deleteAllBtn = document.getElementById('deleteAllBtn');
        const confirmDeleteAllBtn = document.getElementById('confirmDeleteAll');
        const deleteAllModal = new bootstrap.Modal(document.getElementById('deleteAllModal'));

        // Função para atualizar o contador de notificações não lidas
        function updateUnreadCount() {
            const unreadItems = document.querySelectorAll('.notification-item.unread');
            const badge = document.querySelector('.badge');

            if (badge) {
                badge.textContent = unreadItems.length + ' não ' + (unreadItems.length === 1 ? 'lida' : 'lidas');
            }

            // Atualiza o contador global de notificações (se existir)
            const globalCounter = document.querySelector('.notification-counter');
            if (globalCounter) {
                if (unreadItems.length > 0) {
                    globalCounter.textContent = unreadItems.length;
                    globalCounter.style.display = 'flex';
                } else {
                    globalCounter.style.display = 'none';
                }
            }
        }

        // Função para marcar uma notificação como lida
        function markAsRead(id) {
            fetch(BASE_URL + 'notificacoes/marcarComoLida/' + id, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const item = document.querySelector(`.notification-item[data-id="${id}"]`);
                        if (item) {
                            item.classList.remove('unread');
                            item.classList.add('read');

                            // Remove o botão de marcar como lida
                            const markReadBtn = item.querySelector('.mark-read-btn');
                            if (markReadBtn) {
                                markReadBtn.remove();
                            }

                            // Atualiza o contador
                            updateUnreadCount();
                        }
                    } else {
                        alert('Erro ao marcar notificação como lida: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Ocorreu um erro ao processar sua solicitação.');
                });
        }

        // Função para excluir uma notificação
        function deleteNotification(id) {
            fetch(BASE_URL + 'notificacoes/excluir/' + id, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const item = document.querySelector(`.notification-item[data-id="${id}"]`);
                        if (item) {
                            // Animação de fade out antes de remover
                            item.style.opacity = '0';
                            item.style.height = '0';
                            item.style.margin = '0';
                            item.style.padding = '0';
                            item.style.overflow = 'hidden';

                            // Remove o item após a animação
                            setTimeout(() => {
                                item.remove();

                                // Verifica se não há mais notificações
                                if (document.querySelectorAll('.notification-item').length === 0) {
                                    // Exibe mensagem de "nenhuma notificação"
                                    const emptyState = `
                                <div class="empty-state text-center py-5">
                                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                    <h3 class="h5">Nenhuma notificação</h3>
                                    <p class="text-muted">Você não tem notificações no momento.</p>
                                </div>
                            `;
                                    notificationsList.innerHTML = emptyState;

                                    // Esconde os botões de ação global
                                    if (markAllReadBtn) markAllReadBtn.style.display = 'none';
                                    if (deleteAllBtn) deleteAllBtn.style.display = 'none';
                                }

                                // Atualiza o contador
                                updateUnreadCount();
                            }, 300);
                        }
                    } else {
                        alert('Erro ao excluir notificação: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Ocorreu um erro ao processar sua solicitação.');
                });
        }

        // Evento para marcar uma notificação como lida
        notificationsList.addEventListener('click', function(e) {
            // Verifica se o clique foi no botão de marcar como lida
            if (e.target.closest('.mark-read-btn')) {
                const btn = e.target.closest('.mark-read-btn');
                const id = btn.getAttribute('data-id');
                markAsRead(id);
            }

            // Verifica se o clique foi no botão de excluir
            if (e.target.closest('.delete-notification-btn')) {
                const btn = e.target.closest('.delete-notification-btn');
                const id = btn.getAttribute('data-id');

                if (confirm('Tem certeza que deseja excluir esta notificação?')) {
                    deleteNotification(id);
                }
            }
        });

        // Evento para marcar todas as notificações como lidas
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function() {
                fetch(BASE_URL + 'notificacoes/marcarTodasComoLidas', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Marca todas as notificações como lidas na interface
                            document.querySelectorAll('.notification-item.unread').forEach(item => {
                                item.classList.remove('unread');
                                item.classList.add('read');

                                // Remove o botão de marcar como lida
                                const markReadBtn = item.querySelector('.mark-read-btn');
                                if (markReadBtn) {
                                    markReadBtn.remove();
                                }
                            });

                            // Atualiza o contador
                            updateUnreadCount();

                            // Feedback ao usuário
                            alert('Todas as notificações foram marcadas como lidas.');
                        } else {
                            alert('Erro ao marcar notificações como lidas: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Ocorreu um erro ao processar sua solicitação.');
                    });
            });
        }

        // Evento para abrir o modal de confirmação de exclusão de todas as notificações
        if (deleteAllBtn) {
            deleteAllBtn.addEventListener('click', function() {
                deleteAllModal.show();
            });
        }

        // Evento para confirmar a exclusão de todas as notificações
        if (confirmDeleteAllBtn) {
            confirmDeleteAllBtn.addEventListener('click', function() {
                fetch(BASE_URL + 'notificacoes/excluirTodas', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Fecha o modal
                            deleteAllModal.hide();

                            // Limpa a lista de notificações
                            const emptyState = `
                        <div class="empty-state text-center py-5">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <h3 class="h5">Nenhuma notificação</h3>
                            <p class="text-muted">Você não tem notificações no momento.</p>
                        </div>
                    `;
                            notificationsList.innerHTML = emptyState;

                            // Esconde os botões de ação global
                            if (markAllReadBtn) markAllReadBtn.style.display = 'none';
                            if (deleteAllBtn) deleteAllBtn.style.display = 'none';

                            // Atualiza o contador
                            updateUnreadCount();

                            // Feedback ao usuário
                            alert('Todas as notificações foram excluídas.');
                        } else {
                            alert('Erro ao excluir notificações: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Ocorreu um erro ao processar sua solicitação.');
                    });
            });
        }

        // Inicializa o contador de notificações não lidas
        updateUnreadCount();

        // Adiciona estilos de transição para animações
        document.head.insertAdjacentHTML('beforeend', `
        <style>
            .notification-item {
                transition: opacity 0.3s ease, height 0.3s ease, margin 0.3s ease, padding 0.3s ease;
            }
        </style>
    `);
    });
</script>

<!-- Estilos específicos para a página de notificações -->
<style>
    /* Estilos para o container de notificações */
    .notifications-list {
        max-height: 600px;
        overflow-y: auto;
    }

    /* Estilos para cada item de notificação */
    .notification-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        position: relative;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    /* Estilos para notificações não lidas */
    .notification-item.unread {
        background-color: rgba(13, 110, 253, 0.05);
    }

    .notification-item.unread::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background-color: #0d6efd;
    }

    /* Estilos para o conteúdo da notificação */
    .notification-content {
        flex: 1;
        padding-right: 15px;
    }

    .notification-title {
        font-weight: 600;
        margin-bottom: 5px;
        color: #212529;
    }

    .notification-time {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 8px;
    }

    .notification-message {
        color: #495057;
        margin-bottom: 5px;
    }

    /* Estilos para os botões de ação */
    .notification-actions {
        display: flex;
        gap: 5px;
    }

    /* Estilos para o estado vazio */
    .empty-state {
        color: #6c757d;
    }

    /* Estilos para os botões de ação global */
    .notification-actions-global {
        display: flex;
    }

    /* Responsividade */
    @media (max-width: 768px) {
        .notification-item {
            flex-direction: column;
        }

        .notification-actions {
            margin-top: 10px;
            align-self: flex-end;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .notification-actions-global {
            margin-top: 10px;
        }
    }
</style>

<?php
/**
 * Função para formatar o tempo decorrido desde uma data
 * Definida aqui caso não exista no escopo global
 * 
 * @param string $data Data no formato MySQL
 * @return string Tempo decorrido formatado
 */
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
?>