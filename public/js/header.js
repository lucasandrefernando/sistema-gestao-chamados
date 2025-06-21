// JavaScript para o header
document.addEventListener('DOMContentLoaded', function () {
    // Detectar scroll para reduzir o tamanho da navbar
    const navbar = document.querySelector('.custom-navbar');

    window.addEventListener('scroll', function () {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Tema escuro
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            document.documentElement.classList.toggle('dark-mode');

            // Salvar preferência em cookie
            const isDarkMode = document.documentElement.classList.contains('dark-mode');
            document.cookie = `dark_mode=${isDarkMode}; path=/; max-age=31536000`; // 1 ano

            // Alternar ícone
            const icon = themeToggle.querySelector('i');
            if (isDarkMode) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        });

        // Verificar tema atual ao carregar
        if (document.documentElement.classList.contains('dark-mode')) {
            const icon = themeToggle.querySelector('i');
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        }
    }

    // Posicionamento correto dos dropdowns em telas pequenas
    const adjustDropdownPosition = () => {
        if (window.innerWidth <= 576) {
            const dropdowns = document.querySelectorAll('.dropdown-menu');
            dropdowns.forEach(dropdown => {
                dropdown.style.maxHeight = `${window.innerHeight - 70}px`;
            });
        }
    };

    // Ajustar ao carregar e ao redimensionar
    adjustDropdownPosition();
    window.addEventListener('resize', adjustDropdownPosition);

    // Fechar dropdowns ao clicar fora
    document.addEventListener('click', function (event) {
        const dropdowns = document.querySelectorAll('.dropdown-menu.show');
        dropdowns.forEach(dropdown => {
            if (!dropdown.contains(event.target) && !event.target.closest('.dropdown')) {
                dropdown.classList.remove('show');
            }
        });
    });

    // Adicionar funcionalidade aos botões de notificação
    const notificationButtons = document.querySelectorAll('.notification-btn[data-action="dismiss"]');
    notificationButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const notificationItem = this.closest('.notification-item');
            const notificationId = this.getAttribute('data-id');

            // Efeito visual de remoção
            notificationItem.style.opacity = '0.5';
            setTimeout(() => {
                notificationItem.style.height = '0';
                notificationItem.style.padding = '0';
                notificationItem.style.margin = '0';
                notificationItem.style.overflow = 'hidden';

                setTimeout(() => {
                    notificationItem.remove();

                    // Atualizar contador de notificações
                    const badge = document.querySelector('#notificationDropdown .badge');
                    if (badge) {
                        const count = parseInt(badge.textContent);
                        if (count > 1) {
                            badge.textContent = count - 1;
                        } else {
                            badge.style.display = 'none';
                        }
                    }

                    // Verificar se não há mais notificações
                    const remainingNotifications = document.querySelectorAll('.notification-item').length;
                    if (remainingNotifications === 0) {
                        const notificationList = document.querySelector('.notification-list');
                        if (notificationList) {
                            notificationList.innerHTML = `
                                <div class="p-4 text-center text-muted">
                                    <i class="fas fa-bell-slash fa-2x mb-3"></i>
                                    <p>Não há notificações no momento</p>
                                </div>
                            `;
                        }
                    }

                    // Opcional: Enviar requisição AJAX para marcar como lida no servidor
                    if (notificationId) {
                        fetch(`${window.location.origin}/notificacoes/marcar-lida/${notificationId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                notification_id: notificationId
                            })
                        })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Notificação marcada como lida:', data);
                            })
                            .catch(error => {
                                console.error('Erro ao marcar notificação como lida:', error);
                            });
                    }
                }, 300);
            }, 200);
        });
    });

    // Adicionar funcionalidade para marcar todas as notificações como lidas
    const markAllAsReadLink = document.querySelector('a[href*="notificacoes/marcar-lidas"]');
    if (markAllAsReadLink) {
        markAllAsReadLink.addEventListener('click', function (e) {
            e.preventDefault();

            // Efeito visual
            const notificationItems = document.querySelectorAll('.notification-item');
            notificationItems.forEach(item => {
                item.style.opacity = '0.5';
            });

            setTimeout(() => {
                // Limpar a lista de notificações
                const notificationList = document.querySelector('.notification-list');
                if (notificationList) {
                    notificationList.innerHTML = `
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-bell-slash fa-2x mb-3"></i>
                            <p>Não há notificações no momento</p>
                        </div>
                    `;
                }

                // Esconder o badge
                const badge = document.querySelector('#notificationDropdown .badge');
                if (badge) {
                    badge.style.display = 'none';
                }

                // Opcional: Enviar requisição AJAX para marcar todas como lidas no servidor
                fetch(`${window.location.origin}/notificacoes/marcar-lidas`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Todas as notificações marcadas como lidas:', data);
                    })
                    .catch(error => {
                        console.error('Erro ao marcar todas as notificações como lidas:', error);
                    });
            }, 300);
        });
    }

    // Verificar se há links de notificação quebrados e corrigi-los
    document.querySelectorAll('.notification-btn.notification-btn-primary').forEach(btn => {
        btn.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (!href || href === '#') {
                e.preventDefault();

                // Tentar extrair o ID do chamado da descrição
                const descriptionEl = this.closest('.notification-text').querySelector('.notification-subtitle span:first-child');
                if (descriptionEl) {
                    const match = descriptionEl.textContent.match(/#(\d+)/);
                    if (match && match[1]) {
                        window.location.href = `${window.location.origin}/chamados/visualizar/${match[1]}`;
                    }
                }
            }
        });
    });

    // Adicionar tooltip aos botões da navbar
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.forEach(tooltipTriggerEl => {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            new bootstrap.Tooltip(tooltipTriggerEl, {
                placement: 'bottom',
                delay: { show: 500, hide: 100 }
            });
        }
    });

    // Detectar cliques em links de ações rápidas para análise
    document.querySelectorAll('.quick-action-item').forEach(item => {
        item.addEventListener('click', function () {
            const actionText = this.querySelector('.quick-action-text').textContent;
            console.log(`Ação rápida clicada: ${actionText}`);

            // Opcional: Enviar evento de analytics
            if (typeof gtag !== 'undefined') {
                gtag('event', 'quick_action_click', {
                    'action_name': actionText
                });
            }
        });
    });
});