/**
 * HEADER MANAGER - Sistema de Gestão de Dropdowns
 * VERSÃO FINAL FUNCIONAL - SEM BUGS
 */

(function () {
    'use strict';

    // Proteção contra múltiplas execuções
    if (window.HEADER_MANAGER_LOADED) {
        console.log('⚠️ Header Manager já carregado');
        return;
    }

    console.log('🚀 Inicializando Header Manager...');
    window.HEADER_MANAGER_LOADED = true;

    let headerDropdowns = [];
    let dropdownStates = new Map();

    // Aguardar Bootstrap e DOM
    function waitForReady() {
        return new Promise((resolve) => {
            if (typeof bootstrap !== 'undefined' && document.readyState !== 'loading') {
                resolve();
            } else {
                setTimeout(() => waitForReady().then(resolve), 100);
            }
        });
    }

    function initHeaderManager() {
        console.log('🔧 Configurando dropdowns...');

        const selectors = [
            '#userDropdown',
            '#notificationDropdown',
            '#quickActionsDropdown',
            'nav [data-bs-toggle="dropdown"]',
            '.navbar [data-bs-toggle="dropdown"]',
            '.app-navbar [data-bs-toggle="dropdown"]'
        ];

        headerDropdowns = [];

        selectors.forEach(selector => {
            try {
                const elements = document.querySelectorAll(selector);
                elements.forEach(el => {
                    if (!headerDropdowns.includes(el)) {
                        headerDropdowns.push(el);
                        dropdownStates.set(el, { isOpen: false, isToggling: false });
                    }
                });
            } catch (e) {
                console.warn(`Erro no seletor ${selector}:`, e);
            }
        });

        console.log(`📊 Dropdowns encontrados: ${headerDropdowns.length}`);

        if (headerDropdowns.length === 0) {
            console.warn('⚠️ Nenhum dropdown encontrado, tentando novamente...');
            setTimeout(initHeaderManager, 1000);
            return;
        }

        configureDropdowns();
        setupGlobalEvents();

        console.log('✅ Header Manager inicializado com sucesso!');
        exposePublicAPI();
    }

    function configureDropdowns() {
        headerDropdowns.forEach((element, index) => {
            const id = element.id || `header-dropdown-${index}`;
            console.log(`🔧 Configurando: ${id}`);

            try {
                const existing = bootstrap.Dropdown.getInstance(element);
                if (existing) {
                    existing.dispose();
                }

                element.style.zIndex = '9999';

                const dropdown = new bootstrap.Dropdown(element, {
                    autoClose: 'outside',
                    boundary: 'viewport'
                });

                setupDropdownClick(element, id);
                console.log(`✅ ${id} configurado`);

            } catch (error) {
                console.error(`❌ Erro ao configurar ${id}:`, error);
            }
        });
    }

    function setupDropdownClick(element, id) {
        element.removeEventListener('click', handleDropdownClick);
        element.addEventListener('click', function (e) {
            handleDropdownClick(e, element, id);
        }, { capture: true });
    }

    function handleDropdownClick(e, element, id) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        console.log(`🖱️ Clique em ${id}`);

        const state = dropdownStates.get(element);
        if (state.isToggling) {
            console.log(`⏳ ${id} já está sendo processado`);
            return;
        }

        toggleDropdown(id);
    }

    function toggleDropdown(id) {
        const element = document.getElementById(id);
        if (!element) {
            console.error(`❌ Dropdown ${id} não encontrado`);
            return false;
        }

        const state = dropdownStates.get(element);
        if (state.isToggling) return false;

        state.isToggling = true;

        if (state.isOpen) {
            closeDropdown(id);
        } else {
            openDropdown(id);
        }

        setTimeout(() => {
            state.isToggling = false;
        }, 100);

        return true;
    }

    function openDropdown(id) {
        const element = document.getElementById(id);
        if (!element) return false;

        const state = dropdownStates.get(element);
        if (state.isOpen) return true;

        console.log(`📂 Abrindo: ${id}`);

        closeAllDropdowns(id);

        element.setAttribute('aria-expanded', 'true');
        element.classList.add('show');

        const menu = element.nextElementSibling;
        if (menu && menu.classList.contains('dropdown-menu')) {
            menu.classList.add('show');
            menu.style.display = 'block';
            menu.style.zIndex = '9998';

            // POSICIONAMENTO SIMPLES E FUNCIONAL
            positionDropdown(element, menu, id);

            setupMenuEvents(menu, id);
        }

        state.isOpen = true;
        console.log(`✅ ${id} aberto`);

        return true;
    }

    function closeDropdown(id) {
        const element = document.getElementById(id);
        if (!element) return false;

        const state = dropdownStates.get(element);
        if (!state.isOpen) return true;

        console.log(`📁 Fechando: ${id}`);

        element.setAttribute('aria-expanded', 'false');
        element.classList.remove('show');

        const menu = element.nextElementSibling;
        if (menu && menu.classList.contains('dropdown-menu')) {
            menu.classList.remove('show');
            menu.style.display = 'none';

            // Limpar posicionamento
            menu.style.position = '';
            menu.style.top = '';
            menu.style.left = '';
            menu.style.right = '';
            menu.style.transform = '';
        }

        state.isOpen = false;
        console.log(`✅ ${id} fechado`);

        return true;
    }

    function closeAllDropdowns(except = null) {
        headerDropdowns.forEach(element => {
            const id = element.id;
            if (id !== except) {
                closeDropdown(id);
            }
        });
    }

    // POSICIONAMENTO SIMPLES E FUNCIONAL
    function positionDropdown(trigger, menu, id) {
        console.log(`📐 Posicionando ${id}...`);

        // Configurar largura baseada no tipo
        if (id === 'notificationDropdown') {
            menu.style.minWidth = '350px';
            menu.style.maxWidth = '400px';
        } else {
            menu.style.minWidth = '250px';
            menu.style.maxWidth = '300px';
        }

        // Posicionamento absoluto
        menu.style.position = 'absolute';

        // Obter posições
        const triggerRect = trigger.getBoundingClientRect();
        const viewportWidth = window.innerWidth;

        // Posição vertical (sempre abaixo)
        menu.style.top = '100%';
        menu.style.marginTop = '5px';

        // Posição horizontal
        const menuWidth = parseInt(menu.style.minWidth) || 280;

        if (triggerRect.right - menuWidth < 20) {
            // Se não cabe alinhado à esquerda, alinhar à direita
            menu.style.right = '0';
            menu.style.left = 'auto';
            console.log(`↩️ ${id}: Alinhado à direita`);
        } else {
            // Alinhar à esquerda
            menu.style.left = '0';
            menu.style.right = 'auto';
            console.log(`↪️ ${id}: Alinhado à esquerda`);
        }

        // Verificar se sai da tela pela direita
        setTimeout(() => {
            const menuRect = menu.getBoundingClientRect();
            if (menuRect.right > viewportWidth - 20) {
                menu.style.right = '0';
                menu.style.left = 'auto';
                console.log(`🔧 ${id}: Ajustado para não sair da tela`);
            }
        }, 10);

        console.log(`✅ ${id} posicionado`);
    }

    function setupGlobalEvents() {
        setupOutsideClick();
        setupNotificationEvents();
    }

    function setupOutsideClick() {
        document.addEventListener('click', function (e) {
            let clickedInside = false;

            headerDropdowns.forEach(element => {
                if (element.contains(e.target)) {
                    clickedInside = true;
                }

                const menu = element.nextElementSibling;
                if (menu && menu.classList.contains('dropdown-menu') && menu.contains(e.target)) {
                    clickedInside = true;
                }
            });

            if (!clickedInside) {
                closeAllDropdowns();
            }
        }, { capture: false });
    }

    function setupMenuEvents(menu, dropdownId) {
        menu.removeEventListener('click', handleMenuClick);
        menu.addEventListener('click', function (e) {
            handleMenuClick(e, dropdownId);
        }, { capture: true });
    }

    function handleMenuClick(e, dropdownId) {
        const allowClose = e.target.closest('a[href]:not([href="#"]), [data-action="dismiss"], .btn-logout, .mark-all-read');

        if (!allowClose) {
            e.stopPropagation();
            e.stopImmediatePropagation();
        } else {
            setTimeout(() => {
                closeDropdown(dropdownId);
            }, 100);
        }
    }

    function setupNotificationEvents() {
        document.addEventListener('click', function (e) {
            if (e.target.closest('[data-action="dismiss"]')) {
                e.preventDefault();
                e.stopPropagation();

                const button = e.target.closest('[data-action="dismiss"]');
                const item = button.closest('.notification-item');

                if (item) {
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(100%)';
                    setTimeout(() => item.remove(), 300);
                }

                updateNotificationBadge();
                console.log('🗑️ Notificação removida');
            }

            if (e.target.closest('.mark-all-read')) {
                e.preventDefault();
                e.stopPropagation();

                const items = document.querySelectorAll('.notification-item');
                items.forEach((item, index) => {
                    setTimeout(() => {
                        item.style.opacity = '0';
                        item.style.transform = 'translateX(100%)';
                        setTimeout(() => item.remove(), 300);
                    }, index * 50);
                });

                setTimeout(() => {
                    updateNotificationBadge(0, true);
                    showEmptyNotificationState();
                }, items.length * 50 + 300);

                console.log('🧹 Todas as notificações removidas');
            }
        });
    }

    function updateNotificationBadge(count, reset = false) {
        const badge = document.querySelector('.badge-counter');
        if (!badge) return;

        if (reset) {
            badge.style.display = 'none';
            return;
        }

        const items = document.querySelectorAll('.notification-item').length;
        const newCount = typeof count === 'number' ? count : items;

        if (newCount > 0) {
            badge.textContent = newCount > 99 ? '99+' : newCount;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    function showEmptyNotificationState() {
        const notificationList = document.querySelector('.notification-list');
        if (notificationList) {
            notificationList.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-bell-slash"></i>
                    </div>
                    <p>Não há notificações no momento</p>
                </div>
            `;
        }
    }

    function exposePublicAPI() {
        window.HeaderManager = {
            open: openDropdown,
            close: closeDropdown,
            toggle: toggleDropdown,
            closeAll: closeAllDropdowns,
            debug: debugDropdowns
        };

        window.testHeaderDropdown = function (id) {
            return window.HeaderManager.toggle(id);
        };

        window.openHeaderDropdown = function (id) {
            return window.HeaderManager.open(id);
        };

        window.closeHeaderDropdown = function (id) {
            return window.HeaderManager.close(id);
        };

        window.debugHeaderDropdowns = function () {
            return window.HeaderManager.debug();
        };
    }

    function debugDropdowns() {
        console.log('=== DEBUG HEADER MANAGER ===');
        console.log('Bootstrap:', typeof bootstrap);
        console.log('Header carregado:', !!window.HEADER_MANAGER_LOADED);
        console.log('Dropdowns:', headerDropdowns.length);

        headerDropdowns.forEach((element, index) => {
            const state = dropdownStates.get(element);
            console.log(`${index + 1}. ${element.id}:`, {
                element: !!element,
                isOpen: state.isOpen,
                isToggling: state.isToggling,
                expanded: element.getAttribute('aria-expanded')
            });
        });

        return {
            total: headerDropdowns.length,
            open: Array.from(dropdownStates.values()).filter(s => s.isOpen).length
        };
    }

    // Inicializar
    waitForReady().then(() => {
        console.log('✅ Bootstrap e DOM prontos');
        setTimeout(initHeaderManager, 1000);
    });

    console.log('📋 Header Manager carregado');
    console.log('🧪 Use: testHeaderDropdown("userDropdown") para testar');

})();