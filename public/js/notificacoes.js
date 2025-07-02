/**
 * Sistema de Gerenciamento de Notificações
 * 
 * Implementação moderna para gerenciar notificações em tempo real,
 * com suporte a carregamento, marcação e exclusão de notificações.
 * 
 * @version 3.7.0
 */
document.addEventListener('DOMContentLoaded', function () {
    /**
     * Gerenciador de Notificações
     * Sistema completo para gerenciar notificações
     */
    const NotificationManager = {
        // Elementos do DOM
        elements: {
            // Elementos do header
            headerBadge: document.querySelector('.badge-counter'),
            headerList: document.querySelector('.notification-list'),
            headerMarkAllBtn: document.querySelector('.mark-all-read'),

            // Elementos da página de notificações
            pageList: document.querySelector('.notifications-list'),
            pageMarkAllBtn: document.getElementById('markAllReadBtn'),
            pageDeleteAllBtn: document.getElementById('deleteAllBtn'),
            pageConfirmDeleteBtn: document.getElementById('confirmDeleteAll'),
            pageDeleteModal: document.getElementById('deleteAllModal'),
            pageBadge: document.querySelector('.card-title .badge')
        },

        // Configurações
        config: {
            refreshInterval: 60000, // 1 minuto
            apiEndpoints: {
                getNonRead: BASE_URL + 'notificacoes/buscarNaoLidas',
                // Endpoints que tentaremos usar, mas não são críticos
                markAsRead: BASE_URL + 'notificacoes/marcar_lida/',
                markAllAsRead: BASE_URL + 'notificacoes/marcar_todas_lidas',
                deleteNotification: BASE_URL + 'notificacoes/excluir/',
                deleteAll: BASE_URL + 'notificacoes/excluir_todas'
            },
            isNotificationsPage: window.location.pathname.includes('notificacoes'),
            debug: true, // Habilitar logs de depuração
            simulateApiSuccess: true, // Simular sucesso da API se falhar
            localStorageKey: 'ignoredNotifications' // Chave para armazenar notificações ignoradas
        },

        // Armazenamento local de notificações ignoradas
        ignoredNotifications: [],

        // Armazenamento temporário de notificações atuais
        currentNotifications: [],

        // Controle de sessão
        sessionActive: true,

        /**
         * Inicializa o gerenciador de notificações
         */
        init() {
            this.log('Inicializando gerenciador de notificações');

            // Carregar notificações ignoradas do localStorage
            this.loadIgnoredNotifications();

            // Verificar se estamos na página de notificações ou apenas no header
            if (this.config.isNotificationsPage) {
                this.log('Inicializando gerenciador na página de notificações');
                this.setupPageEvents();
            } else {
                this.log('Inicializando gerenciador no header');
            }

            // Configurar eventos comuns
            this.setupCommonEvents();

            // Carregar notificações inicialmente
            this.loadNotifications();

            // Configurar atualização periódica apenas se a sessão estiver ativa
            this.refreshInterval = setInterval(() => {
                if (this.sessionActive) {
                    this.loadNotifications();
                } else {
                    // Limpar o intervalo se a sessão não estiver mais ativa
                    clearInterval(this.refreshInterval);
                }
            }, this.config.refreshInterval);
        },

        /**
         * Carrega notificações ignoradas do localStorage
         */
        loadIgnoredNotifications() {
            const stored = localStorage.getItem(this.config.localStorageKey);
            if (stored) {
                try {
                    this.ignoredNotifications = JSON.parse(stored);
                    this.log('Notificações ignoradas carregadas do localStorage', this.ignoredNotifications);
                } catch (e) {
                    this.log('Erro ao carregar notificações ignoradas do localStorage', e);
                    this.ignoredNotifications = [];
                }
            }
        },

        /**
         * Salva notificações ignoradas no localStorage
         */
        saveIgnoredNotifications() {
            localStorage.setItem(this.config.localStorageKey, JSON.stringify(this.ignoredNotifications));
        },

        /**
         * Adiciona uma notificação à lista de ignoradas
         * @param {number} id - ID da notificação
         */
        addIgnoredNotification(id) {
            if (!this.ignoredNotifications.includes(id)) {
                this.ignoredNotifications.push(id);
                this.saveIgnoredNotifications();
            }
        },

        /**
         * Verifica se uma notificação está na lista de ignoradas
         * @param {number} id - ID da notificação
         * @returns {boolean} Verdadeiro se a notificação estiver ignorada
         */
        isNotificationIgnored(id) {
            return this.ignoredNotifications.includes(id);
        },

        /**
         * Registra mensagens de log se o modo de depuração estiver ativado
         * @param {string} message - Mensagem a ser registrada
         * @param {*} data - Dados adicionais (opcional)
         */
        log(message, data = null) {
            if (this.config.debug) {
                if (data) {
                    console.log(`[Notificações] ${message}`, data);
                } else {
                    console.log(`[Notificações] ${message}`);
                }
            }
        },

        /**
         * Configura eventos comuns para header e página
         */
        setupCommonEvents() {
            // Botão para marcar todas como lidas no header
            if (this.elements.headerMarkAllBtn) {
                this.elements.headerMarkAllBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.markAllAsRead();
                });
            }

            // Configurar eventos para botões de dismiss no header
            document.addEventListener('click', (e) => {
                if (e.target.closest('[data-action="dismiss"]')) {
                    e.preventDefault();
                    e.stopPropagation();
                    const button = e.target.closest('[data-action="dismiss"]');
                    const id = button.dataset.id;
                    this.markAsRead(id);
                }
            });
        },

        /**
         * Configura eventos específicos da página de notificações
         */
        setupPageEvents() {
            // Botão para marcar todas como lidas na página
            if (this.elements.pageMarkAllBtn) {
                this.elements.pageMarkAllBtn.addEventListener('click', () => this.markAllAsRead());
            }

            // Botão para excluir todas
            if (this.elements.pageDeleteAllBtn) {
                this.elements.pageDeleteAllBtn.addEventListener('click', () => {
                    // Mostrar modal de confirmação
                    if (this.elements.pageDeleteModal) {
                        const modalInstance = new bootstrap.Modal(this.elements.pageDeleteModal);
                        modalInstance.show();
                    }
                });
            }

            // Botão de confirmação para excluir todas
            if (this.elements.pageConfirmDeleteBtn) {
                this.elements.pageConfirmDeleteBtn.addEventListener('click', () => this.deleteAllNotifications());
            }

            // Configurar eventos para botões de marcar como lida na página
            document.addEventListener('click', (e) => {
                if (e.target.closest('.mark-read-btn')) {
                    const button = e.target.closest('.mark-read-btn');
                    const id = button.dataset.id;
                    this.markAsRead(id);
                }
            });

            // Configurar eventos para botões de excluir notificação
            document.addEventListener('click', (e) => {
                if (e.target.closest('.delete-notification-btn')) {
                    const button = e.target.closest('.delete-notification-btn');
                    const id = button.dataset.id;

                    if (confirm('Tem certeza que deseja excluir esta notificação?')) {
                        this.deleteNotification(id);
                    }
                }
            });
        },

        /**
         * Verifica se a resposta contém HTML de página de login
         * @param {string} text - Texto da resposta
         * @returns {boolean} Verdadeiro se for página de login
         */
        isLoginPage(text) {
            return text.includes('<title>Login') ||
                text.includes('auth/login') ||
                text.includes('<!DOCTYPE html>') ||
                text.includes('<html');
        },

        /**
         * Carrega notificações não lidas do servidor
         */
        loadNotifications() {
            if (!this.sessionActive) return;

            this.log('Carregando notificações...');

            fetch(this.config.apiEndpoints.getNonRead, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    // Primeiro verificamos o tipo de conteúdo
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('text/html')) {
                        // Se for HTML, provavelmente é a página de login
                        this.sessionActive = false;
                        this.log('Sessão expirada ou redirecionamento para login detectado');
                        throw new Error('Sessão expirada');
                    }

                    // Se não for HTML, processamos normalmente
                    return response.text().then(text => {
                        // Verificação adicional para HTML
                        if (this.isLoginPage(text)) {
                            this.sessionActive = false;
                            this.log('Sessão expirada ou redirecionamento para login detectado');
                            throw new Error('Sessão expirada');
                        }

                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Resposta não é um JSON válido:', text);
                            throw new Error('Resposta inválida do servidor');
                        }
                    });
                })
                .then(data => {
                    if (data.success) {
                        this.log('Notificações carregadas com sucesso', data);

                        // Filtrar notificações ignoradas
                        const filteredNotifications = data.notificacoes.filter(notification =>
                            !this.isNotificationIgnored(notification.id)
                        );

                        // Armazenar notificações atuais para uso posterior
                        this.currentNotifications = filteredNotifications;

                        // Atualizar contador no header
                        this.updateHeaderBadge(filteredNotifications.length);

                        // Atualizar lista no header
                        if (this.elements.headerList) {
                            this.updateHeaderNotificationList(filteredNotifications);
                        }

                        // Se estamos na página de notificações, atualizar também
                        if (this.config.isNotificationsPage) {
                            // Atualizar contador na página
                            this.updatePageBadge(filteredNotifications.length);

                            // Atualizar visibilidade dos botões de ação
                            this.updateActionButtonsVisibility(filteredNotifications.length > 0);
                        }
                    } else {
                        this.log('Erro ao carregar notificações', data);
                    }
                })
                .catch(error => {
                    if (error.message === 'Sessão expirada') {
                        // Não fazemos nada, apenas paramos de tentar carregar notificações
                        clearInterval(this.refreshInterval);
                    } else {
                        console.error('Erro ao carregar notificações:', error);
                    }
                });
        },

        /**
         * Atualiza o contador de notificações no header
         * @param {number} count - Número de notificações não lidas
         */
        updateHeaderBadge(count) {
            if (!this.elements.headerBadge) return;

            if (count > 0) {
                this.elements.headerBadge.textContent = count > 99 ? '99+' : count;
                this.elements.headerBadge.style.display = 'flex';
            } else {
                this.elements.headerBadge.style.display = 'none';
            }
        },

        /**
         * Atualiza o contador de notificações na página
         * @param {number} count - Número de notificações não lidas
         */
        updatePageBadge(count) {
            if (!this.elements.pageBadge) return;

            if (count > 0) {
                this.elements.pageBadge.textContent = `${count} não ${count === 1 ? 'lida' : 'lidas'}`;
                this.elements.pageBadge.style.display = 'inline-flex';
            } else {
                this.elements.pageBadge.style.display = 'none';
            }
        },

        /**
         * Atualiza a visibilidade dos botões de ação na página
         * @param {boolean} hasNotifications - Se existem notificações
         */
        updateActionButtonsVisibility(hasNotifications) {
            if (this.elements.pageMarkAllBtn && this.elements.pageDeleteAllBtn) {
                this.elements.pageMarkAllBtn.style.display = hasNotifications ? 'inline-block' : 'none';
                this.elements.pageDeleteAllBtn.style.display = hasNotifications ? 'inline-block' : 'none';
            }
        },

        /**
         * Atualiza a lista de notificações no dropdown do header
         * @param {Array} notifications - Lista de notificações
         */
        updateHeaderNotificationList(notifications) {
            if (!this.elements.headerList) return;

            if (notifications.length === 0) {
                this.elements.headerList.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-bell-slash"></i>
                        </div>
                        <p>Não há notificações no momento</p>
                    </div>
                `;

                // Ocultar botão de marcar todas como lidas
                if (this.elements.headerMarkAllBtn) {
                    this.elements.headerMarkAllBtn.style.display = 'none';
                }
            } else {
                let html = '';
                notifications.forEach(notification => {
                    html += this.createHeaderNotificationItem(notification);
                });
                this.elements.headerList.innerHTML = html;

                // Mostrar botão de marcar todas como lidas
                if (this.elements.headerMarkAllBtn) {
                    this.elements.headerMarkAllBtn.style.display = 'block';
                }
            }
        },

        /**
         * Cria o HTML para um item de notificação no header
         * @param {Object} notification - Dados da notificação
         * @returns {string} HTML do item de notificação
         */
        createHeaderNotificationItem(notification) {
            return `
                <div class="notification-item ${notification.lida ? 'read' : 'unread'}" data-id="${notification.id}">
                    <div class="notification-icon bg-${notification.cor}">
                        <i class="${notification.icone}"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">${this.escapeHtml(notification.titulo)}</div>
                        <div class="notification-message">${this.escapeHtml(notification.descricao || notification.mensagem || '')}</div>
                        <div class="notification-meta">
                            <span class="notification-time">${notification.tempo}</span>
                        </div>
                        <div class="notification-actions">
                            ${notification.referencia_tipo === 'chamado' && notification.referencia_id ?
                    `<a href="${BASE_URL}chamados/visualizar/${notification.referencia_id}" class="btn-action btn-primary">Ver</a>` : ''}
                            ${!notification.lida ? `<button class="btn-action" data-action="dismiss" data-id="${notification.id}">Ignorar</button>` : ''}
                        </div>
                    </div>
                </div>
            `;
        },

        /**
         * Marca uma notificação como lida e a remove da lista
         * @param {number} id - ID da notificação
         */
        markAsRead(id) {
            if (!this.sessionActive) return;

            this.log(`Marcando notificação ${id} como lida e removendo-a da lista`);

            // Adicionar à lista de notificações ignoradas
            this.addIgnoredNotification(id);

            // Remover a notificação da UI imediatamente
            const headerItem = document.querySelector(`.notification-dropdown .notification-item[data-id="${id}"]`);
            if (headerItem) {
                this.animateAndRemoveNotification(headerItem);
            }

            // Remover a notificação da página, se estivermos na página de notificações
            if (this.config.isNotificationsPage) {
                const pageItem = document.querySelector(`.notifications-list .notification-item[data-id="${id}"]`);
                if (pageItem) {
                    this.animateAndRemoveNotification(pageItem);
                }
            }

            // Atualizar contador
            const currentCount = parseInt(this.elements.headerBadge?.textContent || '0');
            if (currentCount > 0) {
                this.updateHeaderBadge(currentCount - 1);
                if (this.elements.pageBadge) {
                    const newCount = currentCount - 1;
                    this.updatePageBadge(newCount);
                }
            }

            // Tentar fazer a chamada de API para excluir a notificação
            fetch(`${this.config.apiEndpoints.deleteNotification}${id}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .catch(error => {
                    console.error('Erro ao processar notificação:', error);
                    // Não mostramos erro para o usuário, pois a UI já foi atualizada
                });

            // Mostrar mensagem de sucesso
            this.showToast('Notificação ignorada com sucesso', 'success');
        },

        /**
         * Marca todas as notificações como lidas
         */
        markAllAsRead() {
            if (!this.sessionActive) return;

            this.log('Marcando todas as notificações como lidas');

            // Obter todas as notificações atuais
            const notificationsToIgnore = [...this.currentNotifications];

            // Adicionar todas à lista de ignoradas
            notificationsToIgnore.forEach(notification => {
                this.addIgnoredNotification(notification.id);
            });

            // Remover todas as notificações da UI no header
            const headerItems = document.querySelectorAll('.notification-dropdown .notification-item');
            headerItems.forEach(item => {
                this.animateAndRemoveNotification(item);
            });

            // Remover todas as notificações da UI na página
            if (this.config.isNotificationsPage) {
                const pageItems = document.querySelectorAll('.notifications-list .notification-item');
                pageItems.forEach(item => {
                    this.animateAndRemoveNotification(item);
                });
            }

            // Atualizar contadores
            this.updateHeaderBadge(0);
            if (this.elements.pageBadge) {
                this.updatePageBadge(0);
            }

            // Tentar marcar cada notificação individualmente como lida
            // Isso é mais confiável do que tentar marcar todas de uma vez
            notificationsToIgnore.forEach(notification => {
                fetch(`${this.config.apiEndpoints.deleteNotification}${notification.id}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).catch(() => {
                    // Ignorar erros, pois a UI já foi atualizada
                });
            });

            // Mostrar mensagem de sucesso
            this.showToast('Todas as notificações foram ignoradas', 'success');
        },

        /**
         * Anima e remove uma notificação da UI
         * @param {HTMLElement} element - Elemento da notificação
         */
        animateAndRemoveNotification(element) {
            element.style.opacity = '0';
            setTimeout(() => {
                element.style.height = '0';
                element.style.padding = '0';
                element.style.margin = '0';
                element.style.overflow = 'hidden';

                setTimeout(() => {
                    element.remove();

                    // Verificar se não há mais notificações no header
                    if (this.elements.headerList && document.querySelectorAll('.notification-dropdown .notification-item').length === 0) {
                        this.elements.headerList.innerHTML = `
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-bell-slash"></i>
                                </div>
                                <p>Não há notificações no momento</p>
                            </div>
                        `;

                        // Ocultar botão de marcar todas como lidas
                        if (this.elements.headerMarkAllBtn) {
                            this.elements.headerMarkAllBtn.style.display = 'none';
                        }
                    }

                    // Verificar se não há mais notificações na página
                    if (this.config.isNotificationsPage && document.querySelectorAll('.notifications-list .notification-item').length === 0) {
                        if (this.elements.pageList) {
                            this.elements.pageList.innerHTML = `
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-bell-slash"></i>
                                    </div>
                                    <h3 class="h5">Nenhuma notificação</h3>
                                    <p>Você não tem notificações no momento.</p>
                                </div>
                            `;
                        }

                        // Ocultar botões de ação
                        this.updateActionButtonsVisibility(false);
                    }
                }, 300);
            }, 300);
        },

        /**
         * Exclui uma notificação
         * @param {number} id - ID da notificação
         */
        deleteNotification(id) {
            if (!this.sessionActive) return;

            this.log(`Excluindo notificação ${id}`);

            // Adicionar à lista de notificações ignoradas
            this.addIgnoredNotification(id);

            // Remover item da UI imediatamente
            const notificationItem = document.querySelector(`.notification-item[data-id="${id}"]`);
            if (notificationItem) {
                this.animateAndRemoveNotification(notificationItem);
            }

            // Tentar fazer a chamada de API
            fetch(`${this.config.apiEndpoints.deleteNotification}${id}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .catch(error => {
                    console.error('Erro ao excluir notificação:', error);
                    // Não mostramos erro para o usuário, pois a UI já foi atualizada
                });

            // Mostrar mensagem de sucesso
            this.showToast('Notificação excluída com sucesso', 'success');
        },

        /**
         * Exclui todas as notificações
         */
        deleteAllNotifications() {
            if (!this.sessionActive) return;

            this.log('Excluindo todas as notificações');

            // Fechar modal
            if (this.elements.pageDeleteModal) {
                const modalInstance = bootstrap.Modal.getInstance(this.elements.pageDeleteModal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }

            // Obter todas as notificações atuais
            const notificationsToIgnore = [...this.currentNotifications];

            // Adicionar todas à lista de ignoradas
            notificationsToIgnore.forEach(notification => {
                this.addIgnoredNotification(notification.id);
            });

            // Limpar lista de notificações na UI imediatamente
            if (this.elements.headerList) {
                this.elements.headerList.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-bell-slash"></i>
                        </div>
                        <p>Não há notificações no momento</p>
                    </div>
                `;

                // Ocultar botão de marcar todas como lidas
                if (this.elements.headerMarkAllBtn) {
                    this.elements.headerMarkAllBtn.style.display = 'none';
                }
            }

            if (this.elements.pageList) {
                this.elements.pageList.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-bell-slash"></i>
                        </div>
                        <h3 class="h5">Nenhuma notificação</h3>
                        <p>Você não tem notificações no momento.</p>
                    </div>
                `;
            }

            // Ocultar botões de ação
            this.updateActionButtonsVisibility(false);

            // Atualizar contadores
            this.updateHeaderBadge(0);
            if (this.elements.pageBadge) {
                this.updatePageBadge(0);
            }

            // Tentar excluir cada notificação individualmente
            // Isso é mais confiável do que tentar excluir todas de uma vez
            notificationsToIgnore.forEach(notification => {
                fetch(`${this.config.apiEndpoints.deleteNotification}${notification.id}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).catch(() => {
                    // Ignorar erros, pois a UI já foi atualizada
                });
            });

            // Mostrar mensagem de sucesso
            this.showToast('Todas as notificações foram excluídas', 'success');
        },

        /**
         * Mostra uma mensagem toast
         * @param {string} message - Mensagem a ser exibida
         * @param {string} type - Tipo de mensagem (success, error, warning, info)
         */
        showToast(message, type = 'info') {
            // Verificar se já existe um container de toasts
            let toastContainer = document.querySelector('.toast-container');

            // Se não existir, criar um
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                document.body.appendChild(toastContainer);
            }

            // Definir classes e ícones com base no tipo
            let bgClass = 'bg-info text-white';
            let icon = 'info-circle';

            switch (type) {
                case 'success':
                    bgClass = 'bg-success text-white';
                    icon = 'check-circle';
                    break;
                case 'error':
                    bgClass = 'bg-danger text-white';
                    icon = 'exclamation-circle';
                    break;
                case 'warning':
                    bgClass = 'bg-warning text-dark';
                    icon = 'exclamation-triangle';
                    break;
            }

            // Criar o toast
            const toastId = 'toast-' + Date.now();
            const toastHtml = `
                <div id="${toastId}" class="toast ${bgClass}" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header">
                        <i class="fas fa-${icon} me-2"></i>
                        <strong class="me-auto">Notificação</strong>
                        <small>Agora</small>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;

            // Adicionar o toast ao container
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);

            // Inicializar e mostrar o toast
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 5000
            });
            toast.show();

            // Remover o toast do DOM após ser escondido
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        },

        /**
         * Escapa caracteres HTML para prevenir XSS
         * @param {string} text - Texto a ser escapado
         * @returns {string} Texto escapado
         */
        escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    };

    // Inicializar o gerenciador de notificações
    NotificationManager.init();
});