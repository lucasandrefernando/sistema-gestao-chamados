/**
 * Script para gerenciar notificações em tempo real
 */
document.addEventListener('DOMContentLoaded', function () {
    // Elementos do DOM
    const notificationBadge = document.querySelector('.position-absolute.badge.rounded-pill.bg-danger');
    const notificationList = document.querySelector('.notification-list');
    const notificationDropdown = document.getElementById('notificationDropdown');

    // Função para escapar HTML (prevenção de XSS)
    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Função para carregar notificações
    function carregarNotificacoes() {
        fetch(BASE_URL + 'notificacoes/buscarNaoLidas', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar o contador de notificações
                    if (notificationBadge) {
                        if (data.total > 0) {
                            notificationBadge.textContent = data.total > 99 ? '99+' : data.total;
                            notificationBadge.style.display = 'block';
                        } else {
                            notificationBadge.style.display = 'none';
                        }
                    }

                    // Atualizar a lista de notificações no dropdown
                    if (notificationList) {
                        if (data.notificacoes.length === 0) {
                            notificationList.innerHTML = `
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-bell-slash fa-2x mb-3"></i>
                                <p>Não há notificações no momento</p>
                            </div>
                        `;
                        } else {
                            let html = '';
                            data.notificacoes.forEach(notificacao => {
                                html += `
                                <div class="notification-item" data-id="${notificacao.id}">
                                    <div class="notification-content">
                                        <div class="notification-icon bg-${notificacao.cor}">
                                            <i class="${notificacao.icone}"></i>
                                        </div>
                                        <div class="notification-text">
                                            <div class="notification-title">${escapeHtml(notificacao.titulo)}</div>
                                            <div class="notification-subtitle">
                                                <span>${escapeHtml(notificacao.descricao)}</span>
                                                <span class="notification-time">• ${notificacao.tempo}</span>
                                            </div>
                                            <div class="notification-actions">
                                                ${notificacao.referencia_tipo === 'chamado' && notificacao.referencia_id ?
                                        `<a href="${BASE_URL}chamados/visualizar/${notificacao.referencia_id}" class="notification-btn notification-btn-primary">Ver</a>` : ''}
                                                <button class="notification-btn" data-action="dismiss" data-id="${notificacao.id}">Ignorar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            });
                            notificationList.innerHTML = html;

                            // Adicionar eventos aos botões de ignorar
                            document.querySelectorAll('.notification-btn[data-action="dismiss"]').forEach(button => {
                                button.addEventListener('click', function () {
                                    const id = this.getAttribute('data-id');
                                    marcarComoLida(id);
                                });
                            });
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Erro ao carregar notificações:', error);
            });
    }

    // Função para marcar notificação como lida
    function marcarComoLida(id) {
        fetch(`${BASE_URL}notificacoes/marcar-lida/${id}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Recarregar notificações
                    carregarNotificacoes();
                }
            })
            .catch(error => {
                console.error('Erro ao marcar notificação como lida:', error);
            });
    }

    // Carregar notificações inicialmente
    if (notificationList || notificationBadge) {
        carregarNotificacoes();

        // Configurar atualização periódica (a cada 1 minuto)
        setInterval(carregarNotificacoes, 60000);
    }
});