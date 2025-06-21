<?php
// Definir variáveis para o template
$page_title = 'Notificações';
$breadcrumbs = [
    'Notificações' => null
];

// Incluir o header
include ROOT_DIR . '/app/views/templates/header.php';
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Minhas Notificações</h5>
        <?php if (!empty($notificacoes) && $total_nao_lidas > 0): ?>
            <a href="<?= base_url('notificacoes/marcar-todas-lidas') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-check-double me-1"></i> Marcar todas como lidas
            </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (empty($notificacoes)): ?>
            <div class="text-center py-5">
                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                <h5>Não há notificações</h5>
                <p class="text-muted">Você não tem nenhuma notificação no momento.</p>
            </div>
        <?php else: ?>
            <div class="list-group notification-list-full">
                <?php foreach ($notificacoes as $notificacao): ?>
                    <div class="list-group-item list-group-item-action notification-item <?= $notificacao['lida'] ? 'notification-read' : '' ?>" data-id="<?= $notificacao['id'] ?>">
                        <div class="d-flex w-100 align-items-start">
                            <div class="notification-icon bg-<?= $notificacao['cor'] ?> me-3">
                                <i class="<?= $notificacao['icone'] ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($notificacao['titulo']) ?></h6>
                                    <small class="text-muted"><?= $notificacao['tempo'] ?></small>
                                </div>
                                <p class="mb-1"><?= htmlspecialchars($notificacao['descricao']) ?></p>
                                <div class="notification-actions mt-2">
                                    <?php if ($notificacao['referencia_tipo'] == 'chamado' && $notificacao['referencia_id']): ?>
                                        <a href="<?= base_url('chamados/visualizar/' . $notificacao['referencia_id']) ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye me-1"></i> Ver Chamado
                                        </a>
                                    <?php endif; ?>

                                    <?php if (!$notificacao['lida']): ?>
                                        <button class="btn btn-sm btn-outline-secondary mark-read-btn" data-id="<?= $notificacao['id'] ?>">
                                            <i class="fas fa-check me-1"></i> Marcar como lida
                                        </button>
                                    <?php endif; ?>

                                    <button class="btn btn-sm btn-outline-danger delete-notification-btn" data-id="<?= $notificacao['id'] ?>">
                                        <i class="fas fa-trash-alt me-1"></i> Excluir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .notification-list-full {
        max-height: none;
    }

    .notification-read {
        background-color: #f8f9fa;
        opacity: 0.8;
    }

    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Marcar como lida
        document.querySelectorAll('.mark-read-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const item = document.querySelector(`.notification-item[data-id="${id}"]`);

                // Efeito visual
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processando...';

                // Enviar requisição AJAX
                fetch(`<?= base_url('notificacoes/marcar-lida') ?>/${id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Atualizar interface
                            item.classList.add('notification-read');
                            this.remove();
                        } else {
                            alert('Erro ao marcar notificação como lida');
                            this.disabled = false;
                            this.innerHTML = '<i class="fas fa-check me-1"></i> Marcar como lida';
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao processar a solicitação');
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-check me-1"></i> Marcar como lida';
                    });
            });
        });

        // Excluir notificação
        document.querySelectorAll('.delete-notification-btn').forEach(button => {
            button.addEventListener('click', function() {
                if (!confirm('Tem certeza que deseja excluir esta notificação?')) {
                    return;
                }

                const id = this.getAttribute('data-id');
                const item = document.querySelector(`.notification-item[data-id="${id}"]`);

                // Efeito visual
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processando...';

                // Enviar requisição AJAX
                fetch(`<?= base_url('notificacoes/excluir') ?>/${id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remover item da lista
                            item.style.height = '0';
                            item.style.padding = '0';
                            item.style.margin = '0';
                            item.style.overflow = 'hidden';

                            setTimeout(() => {
                                item.remove();

                                // Verificar se a lista está vazia
                                if (document.querySelectorAll('.notification-item').length === 0) {
                                    document.querySelector('.card-body').innerHTML = `
                                <div class="text-center py-5">
                                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                    <h5>Não há notificações</h5>
                                    <p class="text-muted">Você não tem nenhuma notificação no momento.</p>
                                </div>
                            `;
                                }
                            }, 300);
                        } else {
                            alert('Erro ao excluir notificação');
                            this.disabled = false;
                            this.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Excluir';
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao processar a solicitação');
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Excluir';
                    });
            });
        });
    });
</script>

<?php
// Incluir o footer
include ROOT_DIR . '/app/views/templates/footer.php';
?>