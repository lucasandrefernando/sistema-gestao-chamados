/**
 * Script para gerenciar notificações em tempo real
 */
document.addEventListener('DOMContentLoaded', function () {
    // Elementos do DOM
    const notificationBadge = document.querySelector('.position-absolute.badge.rounded-pill.bg-danger');
    const notificationList = document.querySelector('.notification-list');

    console.log('Script de notificações carregado');
    console.log('Badge encontrado:', !!notificationBadge);
    console.log('Lista encontrada:', !!notificationList);

    // Função para carregar notificações
    function carregarNotificacoes() {
        console.log('Carregando notificações...');

        fetch(BASE_URL + 'notificacoes/buscarNaoLidas', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                console.log('Resposta recebida:', data);

                if (data.success) {
                    // Atualizar o contador de notificações
                    if (notificationBadge) {
                        if (data.total > 0) {
                            notificationBadge.textContent = data.total > 99 ? '99+' : data.total;
                            notificationBadge.style.display = 'inline-block';
                            console.log('Badge atualizado:', data.total);
                        } else {
                            notificationBadge.style.display = 'none';
                            console.log('Badge ocultado (sem notificações)');
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
                            console.log('Lista atualizada: sem notificações');
                        } else {
                            console.log('Atualizando lista com', data.notificacoes.length, 'notificações');

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
                                button.addEventListener('click', function (e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    const id = this.getAttribute('data-id');
                                    marcarComoLida(id);
                                });
                            });
                        }
                    }
                } else {
                    console.error('Erro na resposta:', data);
                }
            })
            .catch(error => {
                console.error('Erro ao carregar notificações:', error);
            });
    }

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

    // Função para marcar notificação como lida
    function marcarComoLida(id) {
        console.log('Marcando notificação como lida:', id);

        fetch(`${BASE_URL}notificacoes/marcar-lida/${id}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                console.log('Resposta ao marcar como lida:', data);

                if (data.success) {
                    // Recarregar notificações
                    carregarNotificacoes();
                }
            })
            .catch(error => {
                console.error('Erro ao marcar notificação como lida:', error);
            });
    }

    // Carregar notificações inicialmente se os elementos existirem
    if (notificationList || notificationBadge) {
        console.log('Iniciando carregamento inicial de notificações');
        carregarNotificacoes();

        // Configurar atualização periódica (a cada 1 minuto)
        console.log('Configurando atualização periódica');
        setInterval(carregarNotificacoes, 60000);
    } else {
        console.warn('Elementos de notificação não encontrados na página');
    }

    // Adicionar evento para marcar todas como lidas
    const markAllReadBtn = document.querySelector('.mark-all-read');
    if (markAllReadBtn) {
        console.log('Botão "Marcar todas como lidas" encontrado');

        markAllReadBtn.addEventListener('click', function (e) {
            e.preventDefault();
            console.log('Marcando todas as notificações como lidas');

            fetch(BASE_URL + 'notificacoes/marcar-todas-lidas', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Resposta ao marcar todas como lidas:', data);

                    if (data.success) {
                        // Recarregar notificações
                        carregarNotificacoes();
                    }
                })
                .catch(error => {
                    console.error('Erro ao marcar todas notificações como lidas:', error);
                });
        });
    } else {
        console.warn('Botão "Marcar todas como lidas" não encontrado');
    }
});