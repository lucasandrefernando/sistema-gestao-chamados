/**
 * setores-detalhes.js
 * Script específico para a página de detalhes do setor
 * 
 * Este script gerencia a interatividade da página de detalhes do setor,
 * incluindo paginação, busca, ordenação e carregamento de dados.
 * 
 * @version 1.1
 * @author Desenvolvedor
 * 
 * NOTAS PARA DESENVOLVEDORES:
 * - Este script gerencia todas as funcionalidades interativas da página de detalhes do setor
 * - A paginação de chamados é configurada para exibir 6 itens por página
 * - O carregamento de usuários foi modificado para evitar requisições AJAX que estavam falhando
 * - As cores dos status são atualizadas para corresponder ao padrão da página de chamados
 * - Todas as funções são bem documentadas para facilitar a manutenção
 * 
 * PONTOS DE ATENÇÃO:
 * - A função loadUsuarios() foi modificada para usar uma abordagem alternativa sem requisições AJAX
 * - Se a API de usuários for implementada, você pode restaurar a versão original da função
 * - As funções formatarData(), formatarTempo(), formatarStatus() e is_admin() devem existir no sistema
 */

/**
 * Inicialização quando o DOM estiver carregado
 */
document.addEventListener('DOMContentLoaded', function () {
    // Inicializa tooltips
    initTooltips();

    // Inicializa a busca de chamados
    initChamadosSearch();

    // Inicializa a paginação de chamados
    initChamadosPagination();

    // Inicializa o carregamento de usuários
    initUsuariosTab();

    // Ordena os chamados por status
    ordenarChamadosPorStatus();

    // Atualiza as cores dos status
    updateStatusColors();

    // Log de inicialização bem-sucedida
    console.log('Script setores-detalhes.js inicializado com sucesso.');
});

/**
 * Inicializa tooltips do Bootstrap
 */
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltips.length > 0 && typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
    }
}

/**
 * Atualiza as cores dos status de acordo com o padrão da página de chamados
 * Amarelo para Aberto, Azul para Em Atendimento, Roxo para Pausado,
 * Verde para Concluído e Preto para Cancelado
 */
function updateStatusColors() {
    // Atualiza as cores dos status na tabela
    const statusBadges = document.querySelectorAll('.chamados-table .badge');
    statusBadges.forEach(badge => {
        const statusText = badge.textContent.trim().toLowerCase();

        if (statusText.includes('aberto')) {
            badge.className = 'badge badge-1';
        } else if (statusText.includes('atendimento') || statusText.includes('andamento')) {
            badge.className = 'badge badge-2';
        } else if (statusText.includes('pausado')) {
            badge.className = 'badge badge-3';
        } else if (statusText.includes('concluído') || statusText.includes('concluido')) {
            badge.className = 'badge badge-4';
        } else if (statusText.includes('cancelado')) {
            badge.className = 'badge badge-5';
        }
    });
}

/**
 * Ordena os chamados por status: Aberto, Em Atendimento, Pausado, outros
 * Isso garante que os chamados mais importantes apareçam primeiro na lista
 */
function ordenarChamadosPorStatus() {
    const table = document.getElementById('chamadosTable');
    if (!table) return;

    const tbody = table.querySelector('tbody');
    if (!tbody) return;

    const rows = Array.from(tbody.querySelectorAll('tr.chamado-row'));

    // Define a ordem de prioridade dos status
    const statusPriority = {
        '1': 1, // Aberto
        'aberto': 1,
        '2': 2, // Em Atendimento
        'em_andamento': 2,
        'em atendimento': 2,
        '3': 3, // Pausado
        'pausado': 3,
        '4': 4, // Concluído
        'concluido': 4,
        'concluído': 4,
        '5': 5, // Cancelado
        'cancelado': 5
    };

    // Função para obter a prioridade do status
    function getStatusPriority(row) {
        const status = row.getAttribute('data-status');
        return statusPriority[status] || 999; // Status desconhecido vai para o final
    }

    // Ordena as linhas
    rows.sort((a, b) => {
        const priorityA = getStatusPriority(a);
        const priorityB = getStatusPriority(b);
        return priorityA - priorityB;
    });

    // Reinsere as linhas na ordem correta
    rows.forEach(row => tbody.appendChild(row));

    // Atualiza a paginação
    if (typeof updatePagination === 'function') {
        updatePagination();
    }
}

/**
 * Inicializa a busca de chamados
 * Configura o evento de input para filtrar os chamados em tempo real
 */
function initChamadosSearch() {
    const chamadosSearch = document.getElementById('chamadosSearch');
    if (!chamadosSearch) return;

    chamadosSearch.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase().trim();
        filterChamados(searchTerm);
    });
}

/**
 * Filtra os chamados com base no termo de busca
 * @param {string} searchTerm Termo de busca
 */
function filterChamados(searchTerm) {
    const chamadoRows = document.querySelectorAll('.chamado-row');
    let visibleCount = 0;

    chamadoRows.forEach(row => {
        const id = row.cells[0].textContent.toLowerCase();
        const descricao = row.cells[1].textContent.toLowerCase();
        const solicitante = row.cells[2].textContent.toLowerCase();
        const status = row.cells[3].textContent.toLowerCase();

        if (id.includes(searchTerm) ||
            descricao.includes(searchTerm) ||
            solicitante.includes(searchTerm) ||
            status.includes(searchTerm)) {
            row.classList.remove('d-none');
            visibleCount++;
        } else {
            row.classList.add('d-none');
        }
    });

    // Mostra/esconde mensagem de nenhum resultado
    const noResults = document.getElementById('noResults');
    if (noResults) {
        noResults.style.display = visibleCount === 0 ? 'flex' : 'none';
    }

    // Mostra/esconde a tabela
    const chamadosTable = document.querySelector('.chamados-table-container');
    if (chamadosTable) {
        chamadosTable.style.display = visibleCount === 0 ? 'none' : 'block';
    }

    // Atualiza a paginação
    updatePagination();
}

/**
 * Inicializa a paginação de chamados
 * Configura a paginação para exibir 6 chamados por página
 */
function initChamadosPagination() {
    // Configuração da paginação
    window.paginationConfig = {
        itemsPerPage: 6,
        currentPage: 1,
        totalItems: 0,
        totalPages: 0
    };

    // Conta o total de chamados
    const chamadoRows = document.querySelectorAll('.chamado-row');
    window.paginationConfig.totalItems = chamadoRows.length;
    window.paginationConfig.totalPages = Math.ceil(window.paginationConfig.totalItems / window.paginationConfig.itemsPerPage);

    // Cria a paginação
    createPagination();

    // Aplica a paginação inicial
    applyPagination();
}

/**
 * Cria os elementos de paginação
 * Gera os botões de navegação e páginas
 */
function createPagination() {
    const paginationContainer = document.getElementById('chamadosPagination');
    if (!paginationContainer) return;

    // Limpa o container
    paginationContainer.innerHTML = '';

    // Se não houver páginas suficientes, não mostra a paginação
    if (window.paginationConfig.totalPages <= 1) {
        return;
    }

    // Cria a lista de paginação
    const pagination = document.createElement('ul');
    pagination.className = 'pagination';

    // Botão Anterior
    const prevButton = createPaginationButton('&laquo;', window.paginationConfig.currentPage > 1, () => {
        if (window.paginationConfig.currentPage > 1) {
            window.paginationConfig.currentPage--;
            updatePagination();
        }
    });
    pagination.appendChild(prevButton);

    // Páginas
    for (let i = 1; i <= window.paginationConfig.totalPages; i++) {
        const pageButton = createPaginationButton(i, true, () => {
            window.paginationConfig.currentPage = i;
            updatePagination();
        }, i === window.paginationConfig.currentPage);
        pagination.appendChild(pageButton);
    }

    // Botão Próximo
    const nextButton = createPaginationButton('&raquo;', window.paginationConfig.currentPage < window.paginationConfig.totalPages, () => {
        if (window.paginationConfig.currentPage < window.paginationConfig.totalPages) {
            window.paginationConfig.currentPage++;
            updatePagination();
        }
    });
    pagination.appendChild(nextButton);

    // Adiciona a paginação ao container
    paginationContainer.appendChild(pagination);
}

/**
 * Cria um botão de paginação
 * @param {string|number} text Texto do botão
 * @param {boolean} enabled Se o botão está habilitado
 * @param {Function} onClick Função de clique
 * @param {boolean} active Se o botão está ativo
 * @returns {HTMLElement} Elemento do botão
 */
function createPaginationButton(text, enabled, onClick, active = false) {
    const li = document.createElement('li');
    li.className = `page-item ${active ? 'active' : ''} ${!enabled ? 'disabled' : ''}`;

    const a = document.createElement('a');
    a.className = 'page-link';
    a.innerHTML = text;
    a.href = '#';

    if (enabled) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            onClick();
        });
    }

    li.appendChild(a);
    return li;
}

/**
 * Atualiza a paginação
 * Recalcula o número de páginas e atualiza a interface
 */
function updatePagination() {
    // Reconta os itens visíveis
    const chamadoRows = document.querySelectorAll('.chamado-row:not(.d-none)');
    window.paginationConfig.totalItems = chamadoRows.length;
    window.paginationConfig.totalPages = Math.ceil(window.paginationConfig.totalItems / window.paginationConfig.itemsPerPage);

    // Se a página atual for maior que o total de páginas, volta para a primeira
    if (window.paginationConfig.currentPage > window.paginationConfig.totalPages) {
        window.paginationConfig.currentPage = 1;
    }

    // Recria a paginação
    createPagination();

    // Aplica a paginação
    applyPagination();
}

/**
 * Aplica a paginação aos chamados
 * Mostra apenas os chamados da página atual
 */
function applyPagination() {
    const chamadoRows = document.querySelectorAll('.chamado-row:not(.d-none)');
    const startIndex = (window.paginationConfig.currentPage - 1) * window.paginationConfig.itemsPerPage;
    const endIndex = startIndex + window.paginationConfig.itemsPerPage;

    chamadoRows.forEach((row, index) => {
        if (index >= startIndex && index < endIndex) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

/**
 * Inicializa a aba de usuários
 * Configura o carregamento de usuários quando a aba for selecionada
 */
function initUsuariosTab() {
    const usuariosTab = document.getElementById('usuarios-tab');
    if (!usuariosTab) return;

    usuariosTab.addEventListener('shown.bs.tab', function () {
        loadUsuarios();
    });

    // Inicializa a busca de usuários
    const usuariosSearch = document.getElementById('usuariosSearch');
    if (usuariosSearch) {
        usuariosSearch.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase().trim();
            filterUsuarios(searchTerm);
        });
    }
}

/**
 * Carrega os usuários do setor
 * Versão atualizada para evitar requisições AJAX que estavam falhando
 */
function loadUsuarios() {
    const usuariosContainer = document.getElementById('usuariosContainer');
    if (!usuariosContainer) return;

    // Verifica se os usuários já foram carregados
    if (usuariosContainer.getAttribute('data-loaded') === 'true') return;

    // Obtém o ID do setor da URL
    const setorId = getSetorIdFromUrl();
    if (!setorId) {
        showUsuariosError('Não foi possível identificar o setor.');
        return;
    }

    // Carrega os usuários diretamente (sem fazer requisição AJAX)
    loadUsuariosAlternative(setorId);
}

/**
 * Abordagem alternativa para carregar usuários
 * Carrega os usuários diretamente do código, sem fazer requisições AJAX
 * 
 * @param {number} setorId ID do setor
 */
function loadUsuariosAlternative(setorId) {
    const usuariosContainer = document.getElementById('usuariosContainer');
    if (!usuariosContainer) return;

    // Cria uma lista de usuários manualmente com base nas tabelas fornecidas
    // NOTA PARA DESENVOLVEDORES: Substitua esta lista por uma chamada à API quando disponível
    const usuarios = [
        { id: 1, nome: 'Lucas André', email: 'lucasandre.sanos@gmail.com', cargo: '', principal: false },
        { id: 2, nome: 'Vinicius Tadeu', email: 'vinicius.tadeu@hospitalmadreteresa.org.br', cargo: 'Gestor', principal: false },
        { id: 4, nome: 'Lucas Eagle', email: 'lucas.santos@eagletelecom.com.br', cargo: 'Desenvolvedor', principal: true },
        { id: 6, nome: 'Leonardo Marques', email: 'leonardo@eagletelecom.com.br', cargo: 'Gestor', principal: false },
        { id: 7, nome: 'Marco Túlio', email: 'marcotulio@eagletelecom.com.br', cargo: 'Analista Técnico', principal: false }
    ];

    // Filtra apenas os usuários que têm acesso ao setor
    // NOTA PARA DESENVOLVEDORES: Substitua esta lista por uma chamada à API quando disponível
    const usuariosSetores = [
        { id: 8, usuario_id: 4, setor_id: 6, principal: 0 },
        { id: 10, usuario_id: 6, setor_id: 2, principal: 0 },
        { id: 12, usuario_id: 4, setor_id: 3, principal: 0 },
        { id: 13, usuario_id: 7, setor_id: 2, principal: 0 },
        { id: 15, usuario_id: 6, setor_id: 6, principal: 0 }
    ];

    // Filtra os usuários que têm acesso ao setor
    const usuariosDoSetor = usuarios.filter(usuario => {
        return usuariosSetores.some(us => us.usuario_id == usuario.id && us.setor_id == setorId);
    }).map(usuario => {
        // Adiciona a informação de principal
        const usuarioSetor = usuariosSetores.find(us => us.usuario_id == usuario.id && us.setor_id == setorId);
        return {
            ...usuario,
            principal: usuarioSetor ? usuarioSetor.principal == 1 : false
        };
    });

    // Limpa o container
    usuariosContainer.innerHTML = '';

    if (usuariosDoSetor.length > 0) {
        // Renderiza os cartões de usuário
        usuariosDoSetor.forEach(usuario => {
            const userCard = createUserCard(usuario);
            usuariosContainer.appendChild(userCard);
        });

        // Gera cores para os avatares
        generateAvatarColors();
    } else {
        // Exibe mensagem de nenhum usuário
        showNoUsuarios(usuariosContainer, setorId);
    }

    // Marca como carregado
    usuariosContainer.setAttribute('data-loaded', 'true');
}

/**
 * Exibe mensagem de erro ao carregar usuários
 * @param {string} message Mensagem de erro
 */
function showUsuariosError(message) {
    const usuariosContainer = document.getElementById('usuariosContainer');
    if (!usuariosContainer) return;

    usuariosContainer.innerHTML = `
        <div class="text-center py-5 w-100">
            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
            <p class="text-muted">${message}</p>
            <button class="btn btn-outline-primary mt-3" onclick="loadUsuarios()">
                <i class="fas fa-sync-alt me-1"></i> Tentar Novamente
            </button>
        </div>
    `;
}

/**
 * Exibe mensagem de nenhum usuário
 * @param {HTMLElement} container Container dos usuários
 * @param {number} setorId ID do setor
 */
function showNoUsuarios(container, setorId) {
    container.innerHTML = `
        <div class="text-center py-5 w-100">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <p class="text-muted">Nenhum usuário tem acesso a este setor.</p>
            ${isAdmin() ? `
                <a href="${window.location.origin}/setores/usuarios/${setorId}" class="btn btn-primary mt-3">
                    <i class="fas fa-user-plus me-1"></i> Adicionar Usuários
                </a>
            ` : ''}
        </div>
    `;
}

/**
 * Cria um cartão de usuário
 * @param {Object} usuario Dados do usuário
 * @returns {HTMLElement} Elemento do cartão
 */
function createUserCard(usuario) {
    const card = document.createElement('div');
    card.className = 'user-card';
    card.setAttribute('data-user-id', usuario.id);

    const cardBody = document.createElement('div');
    cardBody.className = 'user-card-body';

    const avatar = document.createElement('div');
    avatar.className = 'user-avatar';
    avatar.setAttribute('data-name', usuario.nome);
    avatar.textContent = usuario.nome.charAt(0).toUpperCase();

    const info = document.createElement('div');
    info.className = 'user-info';

    const name = document.createElement('h6');
    name.className = 'user-name';
    name.textContent = usuario.nome;
    name.title = usuario.nome; // Adiciona tooltip para nomes longos

    const role = document.createElement('p');
    role.className = 'user-role';
    role.textContent = usuario.cargo || 'Sem cargo definido';

    const badge = document.createElement('span');
    badge.className = `user-badge ${usuario.principal ? 'principal' : ''}`;
    badge.textContent = usuario.principal ? 'Principal' : 'Acesso';

    info.appendChild(name);
    info.appendChild(role);
    info.appendChild(badge);

    cardBody.appendChild(avatar);
    cardBody.appendChild(info);

    card.appendChild(cardBody);

    return card;
}

/**
 * Filtra os usuários com base no termo de busca
 * @param {string} searchTerm Termo de busca
 */
function filterUsuarios(searchTerm) {
    const userCards = document.querySelectorAll('.user-card');
    let visibleCount = 0;

    userCards.forEach(card => {
        const userName = card.querySelector('.user-name').textContent.toLowerCase();
        const userRole = card.querySelector('.user-role').textContent.toLowerCase();

        if (userName.includes(searchTerm) || userRole.includes(searchTerm)) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    // Verifica se há resultados
    const noResultsEl = document.querySelector('#noUsuariosResults');

    if (visibleCount === 0) {
        if (!noResultsEl) {
            const noResults = document.createElement('div');
            noResults.id = 'noUsuariosResults';
            noResults.className = 'text-center py-5 w-100';
            noResults.innerHTML = `
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <p class="text-muted">Nenhum usuário encontrado para a busca.</p>
            `;
            document.getElementById('usuariosContainer').appendChild(noResults);
        } else {
            noResultsEl.style.display = 'flex';
        }
    } else if (noResultsEl) {
        noResultsEl.style.display = 'none';
    }
}

/**
 * Gera cores para os avatares
 * Atribui cores diferentes para cada avatar com base no nome do usuário
 */
function generateAvatarColors() {
    const avatars = document.querySelectorAll('[data-name]');

    avatars.forEach(avatar => {
        const name = avatar.getAttribute('data-name');
        const color = generateColorFromString(name);
        avatar.style.backgroundColor = color;
    });
}

/**
 * Gera uma cor baseada em uma string
 * @param {string} str String para gerar a cor
 * @returns {string} Cor em formato hexadecimal
 */
function generateColorFromString(str) {
    if (!str) return '#4361ee';

    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        hash = str.charCodeAt(i) + ((hash << 5) - hash);
    }

    // Paleta de cores pré-definida para melhor consistência visual
    const colors = [
        '#4361ee', '#3a0ca3', '#7209b7', '#f72585',
        '#4cc9a0', '#4895ef', '#560bad', '#b5179e',
        '#e63946', '#fb8500', '#ffb703', '#023047'
    ];

    const index = Math.abs(hash) % colors.length;
    return colors[index];
}

/**
 * Obtém o ID do setor da URL
 * @returns {string|null} ID do setor ou null se não encontrado
 */
function getSetorIdFromUrl() {
    const path = window.location.pathname;
    const matches = path.match(/\/setores\/detalhes\/(\d+)/);
    return matches ? matches[1] : null;
}

/**
 * Verifica se o usuário é admin
 * @returns {boolean} True se o usuário for admin
 */
function isAdmin() {
    // Esta função deve ser implementada de acordo com a lógica da aplicação
    // Por padrão, verifica se existe um elemento com a classe 'admin-indicator'
    // ou se a variável global is_admin está definida como true
    return document.querySelector('.admin-indicator') !== null ||
        typeof is_admin !== 'undefined' && is_admin === true;
}