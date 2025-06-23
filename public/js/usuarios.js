/**
 * Gerenciamento de Usuários - JavaScript Específico
 * Versão: 1.0.0
 */

// Função auto-executável para evitar conflitos com outros scripts
(function () {
    'use strict';

    // Variável para armazenar a URL base
    var BASE_URL;

    // Aguarda o carregamento completo do DOM
    document.addEventListener('DOMContentLoaded', function () {
        // Obtém a URL base do sistema
        BASE_URL = document.querySelector('meta[name="base-url"]')?.content || '';

        // Verifica se estamos em uma página de usuários
        if (document.querySelector('.usuarios-container')) {
            // Inicializa todos os componentes
            initTooltips();
            setupModals();
            setupFilters();
            setupViewToggle();
            setupPagination();
            setupVisualEffects();
            setupFormControls();
            setupSessionCheck();
        }
    });

    /**
     * Inicializa os tooltips em toda a página
     */
    function initTooltips() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    boundary: document.body
                });
            });
        }
    }

    /**
     * Configura os modais da página
     */
    function setupModals() {
        // Modal de remoção
        setupRemoverModal();

        // Modal de restauração
        setupRestaurarModal();

        // Modal de encerrar sessão
        setupEncerrarSessaoModal();
    }

    /**
     * Configura o modal de remoção de usuário
     */
    function setupRemoverModal() {
        var removerModal = document.getElementById('removerModal');
        if (removerModal) {
            removerModal.addEventListener('show.bs.modal', function (event) {
                // Obtém o botão que acionou o modal
                var button = event.relatedTarget;

                // Extrai informações do botão
                var id = button.getAttribute('data-id');
                var nome = button.getAttribute('data-nome');
                var email = button.getAttribute('data-email');

                // Preenche os campos do modal com as informações do usuário
                document.getElementById('removerNome').textContent = nome;
                document.getElementById('removerEmail').textContent = email;
                document.getElementById('confirmarRemover').href = BASE_URL + 'usuarios/remover/' + id;
            });
        }
    }

    /**
     * Configura o modal de restauração de usuário
     */
    function setupRestaurarModal() {
        var restaurarModal = document.getElementById('restaurarModal');
        if (restaurarModal) {
            restaurarModal.addEventListener('show.bs.modal', function (event) {
                // Obtém o botão que acionou o modal
                var button = event.relatedTarget;

                // Extrai informações do botão
                var id = button.getAttribute('data-id');
                var nome = button.getAttribute('data-nome');
                var email = button.getAttribute('data-email');
                var data = button.getAttribute('data-data');

                // Preenche os campos do modal com as informações do usuário
                document.getElementById('restaurarNome').textContent = nome;
                document.getElementById('restaurarEmail').textContent = email;
                document.getElementById('restaurarData').textContent = data;
                document.getElementById('confirmarRestaurar').href = BASE_URL + 'usuarios/restaurar/' + id;
            });
        }
    }

    /**
     * Configura o modal de encerrar sessão
     */
    function setupEncerrarSessaoModal() {
        var encerrarSessaoModal = document.getElementById('encerrarSessaoModal');
        if (encerrarSessaoModal) {
            encerrarSessaoModal.addEventListener('show.bs.modal', function (event) {
                // Obtém o botão que acionou o modal
                var button = event.relatedTarget;

                // Extrai informações do botão
                var id = button.getAttribute('data-id');
                var nome = button.getAttribute('data-nome');
                var email = button.getAttribute('data-email');
                var ip = button.getAttribute('data-ip');
                var data = button.getAttribute('data-data');

                // Preenche os campos do modal com as informações do usuário e da sessão
                document.getElementById('encerrarNome').textContent = nome;
                document.getElementById('encerrarEmail').textContent = email;
                document.getElementById('encerrarIP').textContent = ip || 'Não disponível';
                document.getElementById('encerrarData').textContent = data;
                document.getElementById('confirmarEncerrarSessao').href = BASE_URL + 'usuarios/forcarLogout/' + id;
            });
        }
    }

    /**
     * Configura os filtros de pesquisa com debounce para melhor performance
     */
    function setupFilters() {
        // Filtro de pesquisa por texto
        var searchInput = document.getElementById('userSearch');
        if (searchInput) {
            // Usa debounce para evitar muitas chamadas durante a digitação
            let debounceTimeout;
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(function () {
                    filterUsers();
                    updatePagination();
                }, 300); // Espera 300ms após o usuário parar de digitar
            });
        }

        // Filtro por status
        var statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', function () {
                filterUsers();
                updatePagination();
            });
        }

        // Filtro por tipo de usuário
        var adminFilter = document.getElementById('adminFilter');
        if (adminFilter) {
            adminFilter.addEventListener('change', function () {
                filterUsers();
                updatePagination();
            });
        }
    }

    /**
     * Configura a alternância entre visualização de cards e lista
     */
    function setupViewToggle() {
        var cardViewBtn = document.getElementById('cardViewBtn');
        var listViewBtn = document.getElementById('listViewBtn');
        var cardView = document.getElementById('cardView');
        var listView = document.getElementById('listView');

        // Verifica se todos os elementos necessários existem
        if (!cardViewBtn || !listViewBtn || !cardView || !listView) {
            console.warn('Elementos de alternância de visualização não encontrados.');
            return; // Sai da função se algum elemento não existir
        }

        // Salva a preferência do usuário no localStorage
        var savedView = localStorage.getItem('userViewPreference');
        if (savedView === 'list') {
            showListView();
        } else {
            showCardView();
        }

        // Configura os botões de alternância
        cardViewBtn.addEventListener('click', function () {
            showCardView();
            localStorage.setItem('userViewPreference', 'card');
            updatePagination();
        });

        listViewBtn.addEventListener('click', function () {
            showListView();
            localStorage.setItem('userViewPreference', 'list');
            updatePagination();
        });

        function showCardView() {
            cardView.style.display = 'block';
            listView.style.display = 'none';
            cardViewBtn.classList.add('active');
            listViewBtn.classList.remove('active');
        }

        function showListView() {
            cardView.style.display = 'none';
            listView.style.display = 'block';
            cardViewBtn.classList.remove('active');
            listViewBtn.classList.add('active');
        }
    }

    /**
     * Configura a paginação com melhor desempenho
     */
    function setupPagination() {
        // Verifica se estamos em uma página que usa paginação
        var cardView = document.getElementById('cardView');
        var listView = document.getElementById('listView');

        if (!cardView && !listView) {
            console.warn('Elementos de visualização não encontrados. Paginação não inicializada.');
            return; // Sai da função se os elementos não existirem
        }

        // Configurações iniciais de paginação
        window.paginationState = {
            currentPage: 1,
            itemsPerPage: 12, // Número de itens por página
            totalPages: 1
        };

        // Cria o container de paginação se não existir
        createPaginationContainer();

        // Inicializa a paginação
        updatePagination();

        // Ajusta itens por página com base no tamanho da tela
        function adjustItemsPerPage() {
            var width = window.innerWidth;
            if (width < 768) {
                window.paginationState.itemsPerPage = 6;
            } else if (width < 1200) {
                window.paginationState.itemsPerPage = 9;
            } else {
                window.paginationState.itemsPerPage = 12;
            }

            // Só atualiza a paginação se a página estiver visível
            if (document.visibilityState === 'visible') {
                updatePagination();
            }
        }

        // Adiciona evento para ajustar paginação quando a janela for redimensionada
        window.addEventListener('resize', debounce(adjustItemsPerPage, 250));

        // Ajusta a paginação quando a página se torna visível
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') {
                adjustItemsPerPage();
            }
        });

        // Dispara o evento de resize para configurar corretamente
        adjustItemsPerPage();
    }

    /**
     * Função de debounce para melhorar performance
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Cria o container de paginação
     */
    function createPaginationContainer() {
        var cardView = document.getElementById('cardView');
        var listView = document.getElementById('listView');

        // Verifica se os elementos existem
        if (!cardView && !listView) {
            return;
        }

        // Cria container de paginação para visualização em cards
        if (cardView && !document.querySelector('#cardView .pagination-container')) {
            var paginationContainer = document.createElement('div');
            paginationContainer.className = 'pagination-container';
            paginationContainer.innerHTML = `
                <ul class="pagination">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" aria-label="Anterior">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <li class="page-item active">
                        <a class="page-link" href="#">1</a>
                    </li>
                    <li class="page-item disabled">
                        <a class="page-link" href="#" aria-label="Próximo">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
                <div class="page-info">
                    <span>Exibindo 0-0 de 0 itens</span>
                </div>
            `;
            cardView.appendChild(paginationContainer);
        }

        // Cria container de paginação para visualização em lista
        if (listView && !document.querySelector('#listView .pagination-container')) {
            var paginationContainer = document.createElement('div');
            paginationContainer.className = 'pagination-container';
            paginationContainer.innerHTML = `
                <ul class="pagination">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" aria-label="Anterior">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <li class="page-item active">
                        <a class="page-link" href="#">1</a>
                    </li>
                    <li class="page-item disabled">
                        <a class="page-link" href="#" aria-label="Próximo">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
                <div class="page-info">
                    <span>Exibindo 0-0 de 0 itens</span>
                </div>
            `;
            listView.appendChild(paginationContainer);
        }
    }

    /**
     * Atualiza a paginação com base nos filtros atuais
     */
    function updatePagination() {
        // Verifica se os elementos existem antes de acessá-los
        var cardViewElement = document.getElementById('cardView');
        var listViewElement = document.getElementById('listView');

        if (!cardViewElement || !listViewElement) {
            console.warn('Elementos de visualização não encontrados. Paginação não aplicada.');
            return; // Sai da função se os elementos não existirem
        }

        // Determina qual visualização está ativa
        var isCardView = cardViewElement.style.display !== 'none';
        var selector = isCardView ? '.user-card' : '.user-list-item';
        var container = isCardView ? '.users-cards' : '.users-list';
        var paginationContainer = isCardView ?
            document.querySelector('#cardView .pagination-container') :
            document.querySelector('#listView .pagination-container');

        if (!paginationContainer) {
            console.warn('Container de paginação não encontrado.');
            return; // Sai da função se o container de paginação não existir
        }

        // Obtém todos os itens visíveis (não filtrados)
        var allItems = Array.from(document.querySelectorAll(selector));
        var visibleItems = allItems.filter(function (item) {
            return window.getComputedStyle(item).display !== 'none';
        });

        // Calcula o total de páginas
        var totalItems = visibleItems.length;
        var itemsPerPage = window.paginationState.itemsPerPage;
        var totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));

        // Atualiza o estado da paginação
        window.paginationState.totalPages = totalPages;
        if (window.paginationState.currentPage > totalPages) {
            window.paginationState.currentPage = 1;
        }

        // Atualiza a UI da paginação
        updatePaginationUI(paginationContainer, totalItems);

        // Aplica a paginação aos itens
        applyPagination(visibleItems, container);
    }

    /**
     * Atualiza a interface da paginação
     */
    function updatePaginationUI(paginationContainer, totalItems) {
        if (!paginationContainer) return;

        var currentPage = window.paginationState.currentPage;
        var totalPages = window.paginationState.totalPages;
        var itemsPerPage = window.paginationState.itemsPerPage;

        // Atualiza a informação da página
        var pageInfo = paginationContainer.querySelector('.page-info span');
        if (pageInfo) {
            var startItem = totalItems === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
            var endItem = Math.min(currentPage * itemsPerPage, totalItems);

            if (totalItems === 0) {
                pageInfo.textContent = 'Nenhum item';
            } else {
                pageInfo.textContent = `Exibindo ${startItem}-${endItem} de ${totalItems} itens`;
            }
        }

        // Atualiza os links de paginação
        var pagination = paginationContainer.querySelector('.pagination');
        if (pagination) {
            // Limpa a paginação atual
            pagination.innerHTML = '';

            // Adiciona o botão "Anterior"
            var prevButton = document.createElement('li');
            prevButton.className = 'page-item' + (currentPage === 1 ? ' disabled' : '');
            prevButton.innerHTML = `<a class="page-link" href="#" aria-label="Anterior"><i class="fas fa-chevron-left"></i></a>`;
            if (currentPage > 1) {
                prevButton.querySelector('a').addEventListener('click', function (e) {
                    e.preventDefault();
                    goToPage(currentPage - 1);
                });
            }
            pagination.appendChild(prevButton);

            // Determina quais páginas mostrar
            var pagesToShow = getPagesToShow(currentPage, totalPages);

            // Adiciona os números de página
            pagesToShow.forEach(function (page) {
                if (page === '...') {
                    // Adiciona ellipsis
                    var ellipsis = document.createElement('li');
                    ellipsis.className = 'page-item disabled';
                    ellipsis.innerHTML = `<span class="page-link">...</span>`;
                    pagination.appendChild(ellipsis);
                } else {
                    // Adiciona número de página
                    var pageItem = document.createElement('li');
                    pageItem.className = 'page-item' + (page === currentPage ? ' active' : '');
                    pageItem.innerHTML = `<a class="page-link" href="#">${page}</a>`;
                    if (page !== currentPage) {
                        pageItem.querySelector('a').addEventListener('click', function (e) {
                            e.preventDefault();
                            goToPage(page);
                        });
                    }
                    pagination.appendChild(pageItem);
                }
            });

            // Adiciona o botão "Próximo"
            var nextButton = document.createElement('li');
            nextButton.className = 'page-item' + (currentPage === totalPages ? ' disabled' : '');
            nextButton.innerHTML = `<a class="page-link" href="#" aria-label="Próximo"><i class="fas fa-chevron-right"></i></a>`;
            if (currentPage < totalPages) {
                nextButton.querySelector('a').addEventListener('click', function (e) {
                    e.preventDefault();
                    goToPage(currentPage + 1);
                });
            }
            pagination.appendChild(nextButton);
        }

        // Mostra ou esconde a paginação com base no número de páginas
        paginationContainer.style.display = totalPages > 1 ? 'flex' : 'none';
    }

    /**
     * Determina quais números de página mostrar na paginação
     */
    function getPagesToShow(currentPage, totalPages) {
        var pages = [];

        if (totalPages <= 7) {
            // Se houver 7 ou menos páginas, mostra todas
            for (var i = 1; i <= totalPages; i++) {
                pages.push(i);
            }
        } else {
            // Sempre mostra a primeira página
            pages.push(1);

            // Determina onde começar e terminar
            if (currentPage <= 3) {
                // Se estiver nas primeiras páginas
                pages.push(2, 3, 4, 5);
                pages.push('...');
            } else if (currentPage >= totalPages - 2) {
                // Se estiver nas últimas páginas
                pages.push('...');
                pages.push(totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1);
            } else {
                // Se estiver no meio
                pages.push('...');
                pages.push(currentPage - 1, currentPage, currentPage + 1);
                pages.push('...');
            }

            // Sempre mostra a última página
            pages.push(totalPages);
        }

        return pages;
    }

    /**
     * Navega para uma página específica
     */
    function goToPage(page) {
        window.paginationState.currentPage = page;
        updatePagination();

        // Rola para o topo da lista
        var container = document.querySelector('.users-main-section');
        if (container) {
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    /**
     * Aplica a paginação aos itens
     */
    function applyPagination(items, containerSelector) {
        var currentPage = window.paginationState.currentPage;
        var itemsPerPage = window.paginationState.itemsPerPage;
        var startIndex = (currentPage - 1) * itemsPerPage;
        var endIndex = startIndex + itemsPerPage;

        // Esconde todos os itens primeiro
        items.forEach(function (item) {
            item.classList.remove('visible-item');
            item.style.display = 'none';
        });

        // Mostra apenas os itens da página atual
        for (var i = startIndex; i < endIndex && i < items.length; i++) {
            items[i].classList.add('visible-item');
            items[i].style.display = '';

            // Adiciona animação de entrada
            setTimeout(function (item, index) {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';

                setTimeout(function () {
                    item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 50);
            }, 0, items[i], i - startIndex);
        }

        // Verifica se há resultados visíveis
        var containerElement = document.querySelector(containerSelector);
        if (containerElement && items.length === 0) {
            showNoResults(containerSelector);
        } else if (containerElement) {
            hideNoResults(containerSelector);
        }
    }

    /**
     * Filtra os usuários com base nos critérios de pesquisa
     */
    function filterUsers() {
        var searchInput = document.getElementById('userSearch');
        var statusFilter = document.getElementById('statusFilter');
        var adminFilter = document.getElementById('adminFilter');

        if (!searchInput || !statusFilter || !adminFilter) {
            console.warn('Elementos de filtro não encontrados.');
            return;
        }

        var searchValue = searchInput.value.toLowerCase();
        var statusValue = statusFilter.value;
        var adminValue = adminFilter.value;

        // Filtra os cards de usuários
        filterElements('.user-card', searchValue, statusValue, adminValue);

        // Filtra os itens da lista
        filterElements('.user-list-item', searchValue, statusValue, adminValue);
    }

    /**
     * Filtra elementos com base nos critérios fornecidos
     */
    function filterElements(selector, searchValue, statusValue, adminValue) {
        var elements = document.querySelectorAll(selector);
        var visibleCount = 0;

        elements.forEach(function (element) {
            // Adapta para funcionar com o novo design de cards
            var userName, userEmail;

            if (selector === '.user-card') {
                var nameElement = element.querySelector('.user-name');
                var emailElement = element.querySelector('.user-email');

                if (!nameElement || !emailElement) {
                    element.style.display = 'none';
                    return;
                }

                userName = nameElement.textContent.toLowerCase();
                userEmail = emailElement.textContent.toLowerCase();
            } else {
                var nameElement = element.querySelector('.user-list-name');
                var emailElement = element.querySelector('.user-list-email');

                if (!nameElement || !emailElement) {
                    element.style.display = 'none';
                    return;
                }

                userName = nameElement.textContent.toLowerCase();
                userEmail = emailElement.textContent.toLowerCase();
            }

            var userStatus = element.getAttribute('data-status');
            var userType = element.getAttribute('data-type');

            var matchesSearch = userName.includes(searchValue) || userEmail.includes(searchValue);
            var matchesStatus = statusValue === 'all' || userStatus === statusValue;
            var matchesAdmin = adminValue === 'all' ||
                (adminValue === 'admin' && userType === 'admin') ||
                (adminValue === 'regular' && userType === 'regular');

            if (matchesSearch && matchesStatus && matchesAdmin) {
                element.style.display = '';
                visibleCount++;
            } else {
                element.style.display = 'none';
            }
        });
    }

    /**
     * Mostra mensagem de "nenhum resultado encontrado"
     */
    function showNoResults(containerSelector) {
        var container = document.querySelector(containerSelector);
        if (!container) return;

        var parentContainer = containerSelector === '.users-cards' ?
            document.querySelector('.users-cards-view') :
            document.querySelector('.users-list-view');

        if (!parentContainer) return;

        var existingNoResults = parentContainer.querySelector('.no-results');

        if (!existingNoResults) {
            var noResults = document.createElement('div');
            noResults.className = 'no-results';
            noResults.innerHTML = '<i class="fas fa-search"></i><p>Nenhum usuário encontrado com os filtros selecionados.</p>';
            container.appendChild(noResults);
        }
    }

    /**
     * Esconde mensagem de "nenhum resultado encontrado"
     */
    function hideNoResults(containerSelector) {
        var parentContainer = containerSelector === '.users-cards' ?
            document.querySelector('.users-cards-view') :
            document.querySelector('.users-list-view');

        if (!parentContainer) return;

        var existingNoResults = parentContainer.querySelector('.no-results');

        if (existingNoResults) {
            existingNoResults.remove();
        }
    }

    /**
     * Configura controles específicos do formulário
     */
    function setupFormControls() {
        // Toggle de senha
        var togglePassword = document.getElementById('togglePassword');
        var senhaInput = document.getElementById('senha');

        if (togglePassword && senhaInput) {
            togglePassword.addEventListener('click', function () {
                var type = senhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
                senhaInput.setAttribute('type', type);

                // Alterna o ícone
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }

        // Toggle de administrador
        var adminCheckbox = document.getElementById('admin');
        var adminTipoContainer = document.getElementById('adminTipoContainer');

        if (adminCheckbox && adminTipoContainer) {
            // Configuração inicial
            adminTipoContainer.style.display = adminCheckbox.checked ? 'block' : 'none';

            // Evento de mudança
            adminCheckbox.addEventListener('change', function () {
                adminTipoContainer.style.display = this.checked ? 'block' : 'none';

                if (this.checked) {
                    adminTipoContainer.classList.add('animate-fade');
                } else {
                    adminTipoContainer.classList.remove('animate-fade');
                }
            });
        }
    }

    /**
     * Configura a verificação periódica de sessão
     */
    function setupSessionCheck() {
        // Verifica se o usuário está autenticado
        if (document.body.classList.contains('authenticated')) {
            // Inicia a verificação periódica
            setInterval(verificarSessao, 5 * 60 * 1000); // A cada 5 minutos
        }
    }

    /**
     * Verifica se a sessão do usuário ainda é válida
     */
    function verificarSessao() {
        fetch(BASE_URL + 'auth/verificar_sessao', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (!data.valid) {
                    // Sessão inválida, redireciona para o login
                    window.location.href = BASE_URL + 'auth/logout?sessao_encerrada=1';
                }
            })
            .catch(error => console.error('Erro ao verificar sessão:', error));
    }

    /**
     * Configura efeitos visuais e animações
     */
    function setupVisualEffects() {
        // Animação para os cards de estatísticas
        var statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(function (card, index) {
            // Adiciona um pequeno atraso para cada card, criando um efeito cascata
            setTimeout(function () {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';

                setTimeout(function () {
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            }, 0);
        });

        // Animações para os elementos do formulário
        var formGroups = document.querySelectorAll('.form-group');
        formGroups.forEach(function (group, index) {
            group.style.opacity = '0';
            group.style.transform = 'translateY(20px)';

            setTimeout(function () {
                group.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                group.style.opacity = '1';
                group.style.transform = 'translateY(0)';
            }, 100 + (index * 50));
        });

        var permissionsSection = document.querySelector('.permissions-section');
        if (permissionsSection) {
            permissionsSection.style.opacity = '0';
            permissionsSection.style.transform = 'translateY(20px)';

            setTimeout(function () {
                permissionsSection.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                permissionsSection.style.opacity = '1';
                permissionsSection.style.transform = 'translateY(0)';
            }, 300);
        }
    }
})();