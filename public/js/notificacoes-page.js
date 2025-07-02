/**
 * Funcionalidades para gerenciamento de notificações
 * 
 * Manipula ações como marcar como lida, excluir e outras interações
 * com as notificações do sistema.
 * 
 * @version 3.1.0
 */

document.addEventListener('DOMContentLoaded', function () {
    // Botões de marcar como lida
    const markReadButtons = document.querySelectorAll('.mark-read-btn');
    markReadButtons.forEach(button => {
        button.addEventListener('click', function () {
            const notificationId = this.getAttribute('data-id');
            markAsRead(notificationId, this);
        });
    });

    // Botões de excluir notificação
    const deleteButtons = document.querySelectorAll('.delete-notification-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const notificationId = this.getAttribute('data-id');
            deleteNotification(notificationId, this);
        });
    });

    // Botão de marcar todas como lidas
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', markAllAsRead);
    }

    // Botão de excluir todas
    const deleteAllBtn = document.getElementById('deleteAllBtn');
    if (deleteAllBtn) {
        deleteAllBtn.addEventListener('click', function () {
            const modal = new bootstrap.Modal(document.getElementById('deleteAllModal'));
            modal.show();
        });
    }

    // Botão de confirmar exclusão de todas
    const confirmDeleteAllBtn = document.getElementById('confirmDeleteAll');
    if (confirmDeleteAllBtn) {
        confirmDeleteAllBtn.addEventListener('click', deleteAllNotifications);
    }

    /**
     * Marca uma notificação como lida
     * @param {string} id - ID da notificação
     * @param {HTMLElement} button - Botão que foi clicado
     */
    function markAsRead(id, button) {
        // Mostrar indicador de carregamento
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;

        // Fazer requisição para marcar como lida
        fetch(`${BASE_URL}notificacoes/marcar-lida/${id}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao marcar notificação como lida');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Atualizar UI
                    const notificationItem = button.closest('.notification-item');
                    notificationItem.classList.remove('unread');
                    notificationItem.classList.add('read');
                    button.remove();

                    // Atualizar contador
                    updateNotificationCounter();

                    // Mostrar mensagem de sucesso
                    showToast('Notificação marcada como lida', 'success');
                } else {
                    throw new Error(data.message || 'Erro ao marcar notificação como lida');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                button.innerHTML = '<i class="fas fa-check"></i>';
                button.disabled = false;
                showToast(error.message, 'error');
            });
    }

    /**
     * Exclui uma notificação
     * @param {string} id - ID da notificação
     * @param {HTMLElement} button - Botão que foi clicado
     */
    function deleteNotification(id, button) {
        // Mostrar indicador de carregamento
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;

        // Fazer requisição para excluir
        fetch(`${BASE_URL}notificacoes/excluir/${id}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao excluir notificação');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Remover item da UI com animação
                    const notificationItem = button.closest('.notification-item');
                    notificationItem.style.opacity = '0';
                    notificationItem.style.height = '0';
                    notificationItem.style.margin = '0';
                    notificationItem.style.padding = '0';
                    notificationItem.style.overflow = 'hidden';

                    setTimeout(() => {
                        notificationItem.remove();

                        // Verificar se não há mais notificações
                        const remainingItems = document.querySelectorAll('.notification-item');
                        if (remainingItems.length === 0) {
                            const notifList = document.querySelector('.notif-list');
                            if (notifList) {
                                notifList.innerHTML = `
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-bell-slash"></i>
                                    </div>
                                    <h3 class="h5">Nenhuma notificação</h3>
                                    <p>Você não tem notificações no momento.</p>
                                </div>
                            `;
                            }

                            // Esconder botões de ação globais
                            const actionButtons = document.querySelectorAll('.notif-actions-global button');
                            actionButtons.forEach(btn => btn.style.display = 'none');
                        }

                        // Atualizar contador
                        updateNotificationCounter();
                    }, 300);

                    // Mostrar mensagem de sucesso
                    showToast('Notificação excluída com sucesso', 'success');
                } else {
                    throw new Error(data.message || 'Erro ao excluir notificação');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                button.innerHTML = '<i class="fas fa-trash"></i>';
                button.disabled = false;
                showToast(error.message, 'error');
            });
    }

    /**
     * Marca todas as notificações como lidas
     */
    function markAllAsRead() {
        // Mostrar indicador de carregamento
        markAllReadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        markAllReadBtn.disabled = true;

        // Fazer requisição para marcar todas como lidas
        fetch(`${BASE_URL}notificacoes/marcar-todas-lidas`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao marcar notificações como lidas');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Atualizar UI
                    const unreadItems = document.querySelectorAll('.notification-item.unread');
                    unreadItems.forEach(item => {
                        item.classList.remove('unread');
                        item.classList.add('read');
                        const readBtn = item.querySelector('.mark-read-btn');
                        if (readBtn) {
                            readBtn.remove();
                        }
                    });

                    // Atualizar contador
                    updateNotificationCounter();

                    // Restaurar botão
                    markAllReadBtn.innerHTML = '<i class="fas fa-check-double me-1"></i> Marcar todas';
                    markAllReadBtn.disabled = false;

                    // Mostrar mensagem de sucesso
                    showToast('Todas as notificações foram marcadas como lidas', 'success');
                } else {
                    throw new Error(data.message || 'Erro ao marcar notificações como lidas');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                markAllReadBtn.innerHTML = '<i class="fas fa-check-double me-1"></i> Marcar todas';
                markAllReadBtn.disabled = false;
                showToast(error.message, 'error');
            });
    }

    /**
     * Exclui todas as notificações
     */
    function deleteAllNotifications() {
        // Mostrar indicador de carregamento
        confirmDeleteAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processando...';
        confirmDeleteAllBtn.disabled = true;

        // Fazer requisição para excluir todas
        fetch(`${BASE_URL}notificacoes/excluir-todas`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao excluir notificações');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Fechar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteAllModal'));
                    modal.hide();

                    // Limpar lista de notificações
                    const notifList = document.querySelector('.notif-list');
                    if (notifList) {
                        notifList.innerHTML = `
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-bell-slash"></i>
                            </div>
                            <h3 class="h5">Nenhuma notificação</h3>
                            <p>Você não tem notificações no momento.</p>
                        </div>
                    `;
                    }

                    // Esconder botões de ação globais
                    const actionButtons = document.querySelectorAll('.notif-actions-global button');
                    actionButtons.forEach(btn => btn.style.display = 'none');

                    // Atualizar contador
                    updateNotificationCounter();

                    // Mostrar mensagem de sucesso
                    showToast('Todas as notificações foram excluídas', 'success');
                } else {
                    throw new Error(data.message || 'Erro ao excluir notificações');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                confirmDeleteAllBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Excluir todas';
                confirmDeleteAllBtn.disabled = false;
                showToast(error.message, 'error');
            });
    }

    /**
     * Atualiza o contador de notificações
     */
    function updateNotificationCounter() {
        // Atualizar contador no header
        const headerCounter = document.querySelector('.notification-counter');
        if (headerCounter) {
            const unreadCount = document.querySelectorAll('.notification-item.unread').length;
            headerCounter.textContent = unreadCount;

            if (unreadCount === 0) {
                headerCounter.style.display = 'none';
            } else {
                headerCounter.style.display = 'flex';
            }
        }

        // Atualizar badge na página
        const badge = document.querySelector('.notif-card-title .badge');
        const unreadCount = document.querySelectorAll('.notification-item.unread').length;

        if (badge) {
            if (unreadCount === 0) {
                badge.remove();
            } else {
                badge.textContent = `${unreadCount} não ${unreadCount === 1 ? 'lida' : 'lidas'}`;
            }
        } else if (unreadCount > 0) {
            const cardTitle = document.querySelector('.notif-card-title');
            if (cardTitle) {
                const newBadge = document.createElement('span');
                newBadge.className = 'badge bg-primary rounded-pill';
                newBadge.textContent = `${unreadCount} não ${unreadCount === 1 ? 'lida' : 'lidas'}`;
                cardTitle.appendChild(newBadge);
            }
        }
    }

    /**
     * Exibe uma mensagem toast
     * @param {string} message - Mensagem a ser exibida
     * @param {string} type - Tipo da mensagem (success, error, warning, info)
     */
    function showToast(message, type = 'info') {
        // Verificar se o sistema tem uma função de toast
        if (typeof Toastify === 'function') {
            Toastify({
                text: message,
                duration: 3000,
                close: true,
                gravity: 'top',
                position: 'right',
                backgroundColor: type === 'success' ? '#4cc9a0' :
                    type === 'error' ? '#e63946' :
                        type === 'warning' ? '#ffa62b' : '#4895ef'
            }).showToast();
        } else {
            // Fallback simples
            alert(message);
        }
    }
});