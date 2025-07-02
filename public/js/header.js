/**
 * Script moderno para gerenciar o header e suas funcionalidades
 * 
 * Implementação eficiente para controlar notificações e 
 * outros elementos interativos do header.
 * 
 * @version 3.0.0
 */
document.addEventListener('DOMContentLoaded', function () {
    /**
     * Gerenciador de Notificações
     * Controla a exibição e interação com notificações
     */
    const NotificationManager = {
        // Elementos do DOM
        elements: {
            badge: document.querySelector('.badge-counter'),
            list: document.querySelector('.notification-list'),
            markAllReadBtn: document.querySelector('.mark-all-read'),
            dismissButtons: document.querySelectorAll('[data-action="dismiss"]')
        },

        // Configurações
        config: {
            refreshInterval: 60000, // 1 minuto
            apiEndpoints: {
                getNonRead: BASE_URL + 'notificacoes/buscarNaoLidas',
                markAsRead: BASE_URL + 'notificacoes/marcar_lida/',
                deleteNotification: BASE_URL + 'notificacoes/excluir/'
            },
            debug: true, // Habilitar logs de depuração
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
            this.log('Inicializando gerenciador de notificações do header');

            // Carregar notificações ignoradas do localStorage
            this.loadIgnoredNotifications();

            // Verificar se os elementos necessários existem
            if (this.elements.badge || this.elements.list) {
                // Configurar eventos
                this.setupEventListeners();

                // Carregar notificações inicialmente
                this.loadNotifications();

                // Configurar atualização periódica
                this.refreshInterval = setInterval(() => {
                    if (this.sessionActive) {
                        this.loadNotifications();
                    } else {
                        // Limpar o intervalo se a sessão não estiver mais ativa
                        clearInterval(this.refreshInterval);
                    }
                }, this.config.refreshInterval);
            }
        },

        /**
         * Registra mensagens de log se o modo de depuração estiver ativado
         * @param {string} message - Mensagem a ser registrada
         * @param {*} data - Dados adicionais (opcional)
         */
        log(message, data = null) {
            if (this.config.debug) {
                if (data) {
                    console.log(`[Header] ${message}`, data);
                } else {
                    console.log(`[Header] ${message}`);
                }
            }
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
         * Configura os listeners de eventos
         */
        setupEventListeners() {
            // Botão para marcar todas como lidas
            if (this.elements.markAllReadBtn) {
                this.elements.markAllReadBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.markAllAsRead();
                });
            }

            // Botões para ignorar notificação
            this.elements.dismissButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const id = e.currentTarget.dataset.id;
                    this.markAsRead(id);
                });
            });

            // Configurar eventos para botões de dismiss adicionados dinamicamente
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

                        // Atualizar contador
                        this.updateBadge(filteredNotifications.length);

                        // Atualizar lista
                        if (this.elements.list) {
                            this.updateNotificationList(filteredNotifications);
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
         * Processa a resposta HTTP e trata erros
         * @param {Response} response - Resposta HTTP
         * @returns {Promise} Promise com os dados JSON ou erro
         */
        handleResponse(response) {
            if (!response.ok) {
                throw new Error(`Erro HTTP: ${response.status}`);
            }

            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Resposta não é um JSON válido:', text);
                    throw new Error('Resposta inválida do servidor');
                }
            });
        },

        /**
         * Atualiza o contador de notificações
         * @param {number} count - Número de notificações não lidas
         */
        updateBadge(count) {
            if (!this.elements.badge) return;

            if (count > 0) {
                this.elements.badge.textContent = count > 99 ? '99+' : count;
                this.elements.badge.style.display = 'flex';
            } else {
                this.elements.badge.style.display = 'none';
            }
        },

        /**
         * Atualiza a lista de notificações no dropdown
         * @param {Array} notifications - Lista de notificações
         */
        updateNotificationList(notifications) {
            if (!this.elements.list) return;

            if (notifications.length === 0) {
                this.elements.list.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-bell-slash"></i>
                        </div>
                        <p>Não há notificações no momento</p>
                    </div>
                `;

                // Ocultar botão de marcar todas como lidas
                if (this.elements.markAllReadBtn) {
                    this.elements.markAllReadBtn.style.display = 'none';
                }
            } else {
                let html = '';
                notifications.forEach(notification => {
                    html += this.createNotificationItem(notification);
                });
                this.elements.list.innerHTML = html;

                // Mostrar botão de marcar todas como lidas
                if (this.elements.markAllReadBtn) {
                    this.elements.markAllReadBtn.style.display = 'block';
                }
            }
        },

        /**
         * Cria o HTML para um item de notificação
         * @param {Object} notification - Dados da notificação
         * @returns {string} HTML do item de notificação
         */
        createNotificationItem(notification) {
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
            const item = document.querySelector(`.notification-item[data-id="${id}"]`);
            if (item) {
                this.animateAndRemoveNotification(item);
            }

            // Atualizar contador
            const currentCount = parseInt(this.elements.badge?.textContent || '0');
            if (currentCount > 0) {
                this.updateBadge(currentCount - 1);
            }

            // Tentar fazer a chamada de API para excluir a notificação
            fetch(`${this.config.apiEndpoints.deleteNotification}${id}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .catch(error => {
                    // Tentar marcar como lida como fallback
                    return fetch(`${this.config.apiEndpoints.markAsRead}${id}`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).catch(error => {
                        console.error('Erro ao processar notificação:', error);
                        // Não mostramos erro para o usuário, pois a UI já foi atualizada
                    });
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

            // Remover todas as notificações da UI
            const items = document.querySelectorAll('.notification-item');
            items.forEach(item => {
                this.animateAndRemoveNotification(item);
            });

            // Atualizar contador
            this.updateBadge(0);

            // Processar cada notificação individualmente
            const processNotifications = async () => {
                for (const notification of notificationsToIgnore) {
                    try {
                        await fetch(`${this.config.apiEndpoints.deleteNotification}${notification.id}`, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        this.log(`Notificação ${notification.id} processada com sucesso`);
                    } catch (error) {
                        // Tentar marcar como lida como fallback
                        try {
                            await fetch(`${this.config.apiEndpoints.markAsRead}${notification.id}`, {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            this.log(`Notificação ${notification.id} marcada como lida com sucesso`);
                        } catch (error) {
                            this.log(`Erro ao processar notificação ${notification.id}, continuando com as próximas`);
                        }
                    }
                }
            };

            // Iniciar processamento em segundo plano
            processNotifications();

            // Mostrar mensagem de sucesso imediatamente
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

                    // Verificar se não há mais notificações
                    if (this.elements.list && document.querySelectorAll('.notification-item').length === 0) {
                        this.elements.list.innerHTML = `
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-bell-slash"></i>
                                </div>
                                <p>Não há notificações no momento</p>
                            </div>
                        `;

                        // Ocultar botão de marcar todas como lidas
                        if (this.elements.markAllReadBtn) {
                            this.elements.markAllReadBtn.style.display = 'none';
                        }
                    }
                }, 300);
            }, 300);
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

    /**
     * Gerenciador de Dropdowns
     * Melhora a experiência com dropdowns em dispositivos móveis
     */
    const DropdownManager = {
        /**
         * Inicializa o gerenciador de dropdowns
         */
        init() {
            // Fechar dropdowns ao clicar fora
            document.addEventListener('click', (event) => {
                if (!event.target.closest('.dropdown-menu') &&
                    !event.target.closest('[data-bs-toggle="dropdown"]')) {
                    document.querySelectorAll('.dropdown-menu.show').forEach(dropdown => {
                        const toggle = document.querySelector(`[data-bs-toggle="dropdown"][aria-expanded="true"]`);
                        if (toggle) {
                            const instance = bootstrap.Dropdown.getInstance(toggle);
                            if (instance) {
                                instance.hide();
                            }
                        }
                    });
                }
            });

            // Melhorar experiência em dispositivos móveis
            if (window.innerWidth < 768) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.addEventListener('click', (e) => {
                        // Evitar que o dropdown feche ao clicar dentro dele em dispositivos móveis
                        if (!e.target.closest('a[href]:not([href="#"])') &&
                            !e.target.closest('button:not([data-bs-toggle])')) {
                            e.stopPropagation();
                        }
                    });
                });
            }
        }
    };

    // Inicializar todos os gerenciadores
    NotificationManager.init();
    DropdownManager.init();
});