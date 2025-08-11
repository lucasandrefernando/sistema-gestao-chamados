/**
 * Gerenciamento de Usuários - JavaScript Específico
 * Versão: 3.3.0 - MODAIS MODERNOS COM CLASSES CSS EXISTENTES
 */

// ✅ ISOLAMENTO: Verifica se estamos na página de usuários antes de executar
if (document.querySelector('.usuarios-container')) {

    document.addEventListener('DOMContentLoaded', function () {
        // Inicializa componentes básicos
        initTooltips();
        setupModals();
        setupViewToggle();
        setupFormControls();
        setupSessionCheck();
        setupServerSideFilters();
        setupStatCards();
        setupClearFiltersButton();
        setupModalScrollEffects();

        // ✅ AGUARDA UM POUCO PARA GARANTIR QUE O DOM ESTÁ PRONTO
        setTimeout(function () {
            setupAvatarInitials();
        }, 100);
    });

    /**
     * ✅ NOVO: Configura filtros server-side sem botão filtrar
     */
    function setupServerSideFilters() {
        const form = document.querySelector('.filters-form');
        const searchInput = document.getElementById('userSearch');

        if (form && searchInput) {
            // Auto-submit no campo de busca com debounce
            let debounceTimeout;
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(() => {
                    form.submit();
                }, 500); // Espera 500ms após parar de digitar
            });

            // Auto-submit nos selects
            const selects = form.querySelectorAll('select');
            selects.forEach(select => {
                select.addEventListener('change', () => {
                    form.submit();
                });
            });
        }
    }

    /**
     * ✅ CORRIGIDO: Configura cards de estatísticas clicáveis
     */
    function setupStatCards() {
        const statCards = document.querySelectorAll('.usuarios-container .stat-card');

        console.log('Cards encontrados:', statCards.length); // DEBUG

        statCards.forEach((card, index) => {
            card.style.cursor = 'pointer';
            card.classList.add('clickable');

            // ✅ ADICIONA VISUAL DE HOVER
            card.addEventListener('mouseenter', function () {
                this.style.transform = 'translateY(-3px)';
                this.style.boxShadow = '0 4px 16px rgba(0,0,0,0.15)';
            });

            card.addEventListener('mouseleave', function () {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
            });

            card.addEventListener('click', function () {
                console.log('Card clicado:', index); // DEBUG

                switch (index) {
                    case 0: // Total de Licenças
                        console.log('Redirecionando para licenças...');
                        window.location.href = getBaseUrl() + 'licencas';
                        break;
                    case 1: // Licenças Utilizadas
                        console.log('Abrindo modal licenças utilizadas...');
                        mostrarModalLicencasUtilizadas();
                        break;
                    case 2: // Usuários Online
                        console.log('Abrindo modal usuários online...');
                        mostrarModalUsuariosOnline();
                        break;
                }
            });
        });
    }

    /**
     * ✅ CORRIGIDO: Configura avatares com iniciais corretas
     */
    function setupAvatarInitials() {
        const avatares = document.querySelectorAll('.usuarios-container .user-avatar');

        console.log('Avatares encontrados:', avatares.length); // DEBUG

        avatares.forEach(avatar => {
            const card = avatar.closest('.user-card, .user-list-item');
            if (card) {
                const nomeElement = card.querySelector('.user-name, .user-list-name');
                if (nomeElement) {
                    const nomeCompleto = nomeElement.textContent.trim();
                    const iniciais = gerarIniciais(nomeCompleto);

                    console.log('Nome:', nomeCompleto, 'Iniciais:', iniciais); // DEBUG

                    // Preserva o indicador online se existir
                    const onlineIndicator = avatar.querySelector('.online-indicator');
                    const onlineHTML = onlineIndicator ? onlineIndicator.outerHTML : '';

                    // Atualiza o conteúdo do avatar
                    avatar.innerHTML = iniciais + onlineHTML;
                }
            }
        });
    }

    /**
     * ✅ FUNÇÃO: Gera iniciais corretas do nome
     */
    function gerarIniciais(nomeCompleto) {
        if (!nomeCompleto) return '?';

        const nomes = nomeCompleto.trim().split(' ').filter(nome => nome.length > 0);

        if (nomes.length === 0) return '?';

        if (nomes.length === 1) {
            // Se só tem um nome, pega as duas primeiras letras
            return nomes[0].substring(0, 2).toUpperCase();
        } else {
            // Se tem mais de um nome, pega primeira letra do primeiro e último nome
            const primeiroNome = nomes[0];
            const ultimoNome = nomes[nomes.length - 1];
            return (primeiroNome.charAt(0) + ultimoNome.charAt(0)).toUpperCase();
        }
    }

    /**
     * ✅ FUNÇÃO: Obtém a base URL do sistema
     */
    function getBaseUrl() {
        // Tenta obter da meta tag
        const metaBaseUrl = document.querySelector('meta[name="base-url"]');
        if (metaBaseUrl) {
            return metaBaseUrl.content;
        }

        // Fallback: detecta automaticamente
        const currentUrl = window.location.href;
        if (currentUrl.includes('/sistema-gestao-chamados/')) {
            return currentUrl.split('/sistema-gestao-chamados/')[0] + '/sistema-gestao-chamados/';
        }

        return window.location.origin + '/';
    }

    /**
     * ✅ MODAL LICENÇAS UTILIZADAS - USANDO CLASSES CSS EXISTENTES
     */
    function mostrarModalLicencasUtilizadas() {
        console.log('Iniciando busca de licenças utilizadas...'); // DEBUG

        // ✅ CRIA MODAL USANDO AS CLASSES CSS EXISTENTES
        const modalHtml = `
            <div class="modal fade" id="licencasUtilizadasModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-user-check"></i>
                                Licenças Utilizadas
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="loading-state-modal">
                                <div class="spinner-modal"></div>
                                <p class="loading-text-modal">Carregando usuários...</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Fechar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove modal anterior se existir
        const existingModal = document.getElementById('licencasUtilizadasModal');
        if (existingModal) existingModal.remove();

        // Adiciona e mostra modal
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('licencasUtilizadasModal'));
        modal.show();

        // ✅ CARREGA DADOS VIA AJAX
        const ajaxUrl = window.location.href + (window.location.href.includes('?') ? '&' : '?') + 'ajax=licencas_utilizadas';

        fetch(ajaxUrl)
            .then(response => response.json())
            .then(data => {
                const modalBody = document.querySelector('#licencasUtilizadasModal .modal-body');
                const modalTitle = document.querySelector('#licencasUtilizadasModal .modal-title');

                // Atualiza título
                modalTitle.innerHTML = `
                    <i class="fas fa-user-check"></i>
                    Licenças Utilizadas (${data.usuarios ? data.usuarios.length : 0})
                `;

                if (data.usuarios && data.usuarios.length > 0) {
                    // ✅ USA AS CLASSES CSS EXISTENTES
                    let listHtml = '<ul class="user-list-modal">';

                    data.usuarios.forEach(usuario => {
                        const iniciais = gerarIniciais(usuario.nome);
                        const tipoClass = usuario.admin ? (usuario.admin_tipo === 'master' ? 'admin-master' : 'admin') : 'user';

                        listHtml += `
                            <li class="user-item-modal">
                                <div class="user-avatar-modal ${tipoClass}">
                                    ${iniciais}
                                    ${usuario.session_id ? '<span class="online-dot"></span>' : ''}
                                </div>
                                <div class="user-info-modal">
                                    <h6 class="user-name-modal">${usuario.nome}</h6>
                                    <p class="user-email-modal">${usuario.email}</p>
                                    <div class="user-details-modal">
                                        <span class="badge-modal ${usuario.ativo ? 'status-active' : 'status-inactive'}">
                                            ${usuario.ativo ? 'Ativo' : 'Inativo'}
                                        </span>
                                        ${usuario.admin ? `<span class="badge-modal ${usuario.admin_tipo === 'master' ? 'role-master' : 'role-admin'}">${usuario.admin_tipo === 'master' ? 'Admin Master' : 'Admin'}</span>` : ''}
                                        <span class="badge-modal ${usuario.session_id ? 'online' : 'offline'}">
                                            <i class="fas fa-circle"></i>
                                            ${usuario.session_id ? 'Online' : 'Offline'}
                                        </span>
                                    </div>
                                </div>
                            </li>
                        `;
                    });

                    listHtml += '</ul>';
                    modalBody.innerHTML = listHtml;
                } else {
                    modalBody.innerHTML = `
                        <div class="empty-state-modal">
                            <i class="fas fa-users"></i>
                            <p>Nenhum usuário utilizando licenças</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                const modalBody = document.querySelector('#licencasUtilizadasModal .modal-body');
                modalBody.innerHTML = `
                    <div class="empty-state-modal">
                        <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
                        <p style="color: #ef4444;">Erro ao carregar dados</p>
                    </div>
                `;
            });

        // Remove modal ao fechar
        document.getElementById('licencasUtilizadasModal').addEventListener('hidden.bs.modal', function () {
            this.remove();
        });
    }

    /**
     * ✅ MODAL USUÁRIOS ONLINE - USANDO CLASSES CSS EXISTENTES
     */
    function mostrarModalUsuariosOnline() {
        console.log('Iniciando busca de usuários online...'); // DEBUG

        // ✅ CRIA MODAL USANDO AS CLASSES CSS EXISTENTES
        const modalHtml = `
            <div class="modal fade" id="usuariosOnlineModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-signal"></i>
                                Usuários Online
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="loading-state-modal">
                                <div class="spinner-modal"></div>
                                <p class="loading-text-modal">Carregando usuários online...</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Fechar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove modal anterior se existir
        const existingModal = document.getElementById('usuariosOnlineModal');
        if (existingModal) existingModal.remove();

        // Adiciona e mostra modal
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('usuariosOnlineModal'));
        modal.show();

        // ✅ CARREGA DADOS VIA AJAX
        const ajaxUrl = window.location.href + (window.location.href.includes('?') ? '&' : '?') + 'ajax=usuarios_online';

        fetch(ajaxUrl)
            .then(response => response.json())
            .then(data => {
                const modalBody = document.querySelector('#usuariosOnlineModal .modal-body');
                const modalTitle = document.querySelector('#usuariosOnlineModal .modal-title');

                // Atualiza título
                modalTitle.innerHTML = `
                    <i class="fas fa-signal"></i>
                    Usuários Online (${data.total || 0})
                `;

                if (data.usuarios && data.usuarios.length > 0) {
                    // ✅ USA AS CLASSES CSS EXISTENTES
                    let listHtml = '<ul class="user-list-modal">';

                    data.usuarios.forEach(usuario => {
                        const iniciais = gerarIniciais(usuario.nome);
                        const tipoClass = usuario.admin ? (usuario.admin_tipo === 'master' ? 'admin-master' : 'admin') : 'user';

                        listHtml += `
                            <li class="user-item-modal">
                                <div class="user-avatar-modal ${tipoClass}">
                                    ${iniciais}
                                    <span class="online-dot"></span>
                                </div>
                                <div class="user-info-modal">
                                    <h6 class="user-name-modal">${usuario.nome}</h6>
                                    <p class="user-email-modal">${usuario.email}</p>
                                    <div class="user-details-modal">
                                        <span class="badge-modal online">
                                            <i class="fas fa-circle"></i>
                                            Online
                                        </span>
                                        ${usuario.admin ? `<span class="badge-modal ${usuario.admin_tipo === 'master' ? 'role-master' : 'role-admin'}">${usuario.admin_tipo === 'master' ? 'Admin Master' : 'Admin'}</span>` : ''}
                                        ${usuario.session_ip ? `<span class="badge-modal offline" style="background: #e5e7eb; color: #6b7280;">IP: ${usuario.session_ip}</span>` : ''}
                                    </div>
                                </div>
                            </li>
                        `;
                    });

                    listHtml += '</ul>';
                    modalBody.innerHTML = listHtml;
                } else {
                    modalBody.innerHTML = `
                        <div class="empty-state-modal">
                            <i class="fas fa-wifi"></i>
                            <p>Nenhum usuário online no momento</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                const modalBody = document.querySelector('#usuariosOnlineModal .modal-body');
                modalBody.innerHTML = `
                    <div class="empty-state-modal">
                        <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
                        <p style="color: #ef4444;">Erro ao carregar dados</p>
                    </div>
                `;
            });

        // Remove modal ao fechar
        document.getElementById('usuariosOnlineModal').addEventListener('hidden.bs.modal', function () {
            this.remove();
        });
    }

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
     * ✅ CORRIGIDO: Configura o modal de remoção de usuário
     */
    function setupRemoverModal() {
        var removerModal = document.getElementById('removerModal');
        if (removerModal) {
            removerModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var nome = button.getAttribute('data-nome');
                var email = button.getAttribute('data-email');

                document.getElementById('removerNome').textContent = nome;
                document.getElementById('removerEmail').textContent = email;

                // ✅ SOLUÇÃO: Remove o href e adiciona onclick
                var confirmarBtn = document.getElementById('confirmarRemover');
                confirmarBtn.removeAttribute('href');
                confirmarBtn.onclick = function (e) {
                    e.preventDefault();
                    window.location.href = '/sistema-gestao-chamados/usuarios/remover/' + id;
                };
            });
        }
    }

    /**
     * ✅ CORRIGIDO: Configura o modal de restauração de usuário
     */
    function setupRestaurarModal() {
        var restaurarModal = document.getElementById('restaurarModal');
        if (restaurarModal) {
            restaurarModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var nome = button.getAttribute('data-nome');
                var email = button.getAttribute('data-email');
                var data = button.getAttribute('data-data');

                document.getElementById('restaurarNome').textContent = nome;
                document.getElementById('restaurarEmail').textContent = email;
                document.getElementById('restaurarData').textContent = data;

                // ✅ SOLUÇÃO: Remove o href e adiciona onclick
                var confirmarBtn = document.getElementById('confirmarRestaurar');
                confirmarBtn.removeAttribute('href');
                confirmarBtn.onclick = function (e) {
                    e.preventDefault();
                    window.location.href = '/sistema-gestao-chamados/usuarios/restaurar/' + id;
                };
            });
        }
    }

    /**
     * ✅ CORRIGIDO: Configura o modal de encerrar sessão
     */
    function setupEncerrarSessaoModal() {
        var encerrarSessaoModal = document.getElementById('encerrarSessaoModal');
        if (encerrarSessaoModal) {
            encerrarSessaoModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var nome = button.getAttribute('data-nome');
                var email = button.getAttribute('data-email');
                var ip = button.getAttribute('data-ip');
                var data = button.getAttribute('data-data');

                document.getElementById('encerrarNome').textContent = nome;
                document.getElementById('encerrarEmail').textContent = email;
                document.getElementById('encerrarIP').textContent = ip || 'Não disponível';
                document.getElementById('encerrarData').textContent = data;

                // ✅ SOLUÇÃO: Remove o href e adiciona onclick
                var confirmarBtn = document.getElementById('confirmarEncerrarSessao');
                confirmarBtn.removeAttribute('href');
                confirmarBtn.onclick = function (e) {
                    e.preventDefault();
                    window.location.href = '/sistema-gestao-chamados/usuarios/forcarLogout/' + id;
                };
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

        if (!cardViewBtn || !listViewBtn || !cardView || !listView) {
            console.warn('Elementos de alternância de visualização não encontrados.');
            return;
        }

        var savedView = localStorage.getItem('userViewPreference');
        if (savedView === 'list') {
            showListView();
        } else {
            showCardView();
        }

        cardViewBtn.addEventListener('click', function () {
            showCardView();
            localStorage.setItem('userViewPreference', 'card');
        });

        listViewBtn.addEventListener('click', function () {
            showListView();
            localStorage.setItem('userViewPreference', 'list');
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
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }

        // Toggle de administrador
        var adminCheckbox = document.getElementById('admin');
        var adminTipoContainer = document.getElementById('adminTipoContainer');

        if (adminCheckbox && adminTipoContainer) {
            adminTipoContainer.style.display = adminCheckbox.checked ? 'block' : 'none';

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
        if (document.body.classList.contains('authenticated')) {
            setInterval(verificarSessao, 5 * 60 * 1000); // A cada 5 minutos
        }
    }

    /**
     * Verifica se a sessão do usuário ainda é válida
     */
    function verificarSessao() {
        fetch('/sistema-gestao-chamados/auth/verificar_sessao', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (!data.valid) {
                    window.location.href = '/sistema-gestao-chamados/auth/logout?sessao_encerrada=1';
                }
            })
            .catch(error => console.error('Erro ao verificar sessão:', error));
    }

    /**
     * ✅ NOVO: Configura o botão limpar com indicação de filtros ativos
     */
    function setupClearFiltersButton() {
        const clearButton = document.querySelector('.usuarios-container .filter-actions .btn-secondary');
        const searchInput = document.getElementById('userSearch');
        const statusSelect = document.getElementById('statusFilter');
        const tipoSelect = document.getElementById('adminFilter');

        if (!clearButton) return;

        function checkActiveFilters() {
            let hasFilters = false;

            // Verifica se há busca
            if (searchInput && searchInput.value.trim() !== '') {
                hasFilters = true;
            }

            // Verifica se status não é "all"
            if (statusSelect && statusSelect.value !== 'all') {
                hasFilters = true;
            }

            // Verifica se tipo não é "all"
            if (tipoSelect && tipoSelect.value !== 'all') {
                hasFilters = true;
            }

            // Adiciona ou remove classe visual
            if (hasFilters) {
                clearButton.classList.add('has-filters');
                clearButton.innerHTML = '<i class="fas fa-times-circle"></i> Limpar Filtros';
                clearButton.title = 'Clique para limpar todos os filtros ativos';
            } else {
                clearButton.classList.remove('has-filters');
                clearButton.innerHTML = '<i class="fas fa-times"></i> Limpar';
                clearButton.title = 'Limpar filtros';
            }
        }

        // Verifica filtros ativos na inicialização
        checkActiveFilters();

        // Monitora mudanças nos filtros
        if (searchInput) {
            searchInput.addEventListener('input', checkActiveFilters);
        }

        if (statusSelect) {
            statusSelect.addEventListener('change', checkActiveFilters);
        }

        if (tipoSelect) {
            tipoSelect.addEventListener('change', checkActiveFilters);
        }
    }

    /**
     * ✅ NOVO: Configura efeitos de scroll nos modais
     */
    function setupModalScrollEffects() {
        // Observa quando modais são abertos
        document.addEventListener('shown.bs.modal', function (event) {
            const modal = event.target;
            const modalBody = modal.querySelector('.modal-body');

            if (modalBody) {
                // Verifica se tem scroll
                function checkScroll() {
                    if (modalBody.scrollHeight > modalBody.clientHeight) {
                        modalBody.classList.add('has-scroll');
                    } else {
                        modalBody.classList.remove('has-scroll');
                    }
                }

                // Verifica inicialmente
                setTimeout(checkScroll, 100);

                // Verifica quando redimensiona
                window.addEventListener('resize', checkScroll);

                // Efeito de scroll suave
                modalBody.addEventListener('scroll', function () {
                    const scrollPercentage = (this.scrollTop / (this.scrollHeight - this.clientHeight)) * 100;

                    // Adiciona efeito visual baseado na posição do scroll
                    if (scrollPercentage > 90) {
                        this.style.background = 'linear-gradient(to bottom, transparent 0%, rgba(67, 97, 238, 0.02) 100%)';
                    } else {
                        this.style.background = '';
                    }
                });
            }
        });
    }

} // ✅ FIM da verificação de isolamento