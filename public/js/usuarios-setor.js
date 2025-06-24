/**
* usuarios-setor.js - Funcionalidades específicas para a página de usuários do setor
*/

document.addEventListener('DOMContentLoaded', function () {
    // Inicializa os tooltips do Bootstrap (se estiver usando Bootstrap)
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
    }

    // Funcionalidade de busca na tabela principal
    const searchInput = document.getElementById('usuariosSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            filterTable('usuariosTable', this.value);
        });
    }

    // Funcionalidade de busca na tabela do modal
    const modalSearchInput = document.getElementById('modalUsuariosSearch');
    if (modalSearchInput) {
        modalSearchInput.addEventListener('keyup', function () {
            filterTable('usuariosDisponiveis', this.value);
        });
    }

    // Função para filtrar tabelas
    function filterTable(tableId, searchTerm) {
        const table = document.getElementById(tableId);
        if (!table) return;

        searchTerm = searchTerm.toLowerCase();
        const rows = table.querySelectorAll('tbody tr:not(.empty-row)');

        let hasVisibleRows = false;

        rows.forEach(row => {
            const nome = row.querySelector('.usuario-nome')?.textContent.toLowerCase() || '';
            const email = row.querySelector('.email-text')?.textContent.toLowerCase() || '';
            const cargo = row.querySelector('.col-cargo')?.textContent.toLowerCase() || '';
            const id = row.querySelector('.col-id')?.textContent.toLowerCase() || '';

            if (nome.includes(searchTerm) || email.includes(searchTerm) ||
                cargo.includes(searchTerm) || id.includes(searchTerm)) {
                row.style.display = '';
                hasVisibleRows = true;
            } else {
                row.style.display = 'none';
            }
        });

        // Mostra mensagem de "nenhum resultado" se não houver linhas visíveis
        const emptyRow = table.querySelector('.empty-row');
        if (emptyRow) {
            emptyRow.style.display = hasVisibleRows ? 'none' : '';
        } else if (!hasVisibleRows) {
            const tbody = table.querySelector('tbody');
            const colSpan = table.querySelector('thead tr').children.length;

            const newEmptyRow = document.createElement('tr');
            newEmptyRow.className = 'empty-row';
            newEmptyRow.innerHTML = `
                <td colspan="${colSpan}">
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <p>Nenhum resultado encontrado para "${searchTerm}"</p>
                    </div>
                </td>
            `;

            tbody.appendChild(newEmptyRow);
        }
    }

    // Adiciona atributos data-label para responsividade em telas pequenas
    const tables = document.querySelectorAll('.usuarios-table');
    tables.forEach(table => {
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach((cell, index) => {
                if (headers[index]) {
                    cell.setAttribute('data-label', headers[index]);
                }
            });
        });
    });

    // Modal para editar associação
    const editarAssociacaoModal = document.getElementById('editarAssociacaoModal');
    if (editarAssociacaoModal) {
        editarAssociacaoModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const usuarioId = button.getAttribute('data-usuario-id');
            const usuarioNome = button.getAttribute('data-usuario-nome');
            const principal = button.getAttribute('data-principal') === '1';

            document.getElementById('editarUsuarioId').value = usuarioId;
            document.getElementById('editarUsuarioNome').textContent = usuarioNome;
            document.getElementById('editarPrincipal').checked = principal;
        });
    }

    // Modal para desassociar usuário
    const desassociarUsuarioModal = document.getElementById('desassociarUsuarioModal');
    if (desassociarUsuarioModal) {
        desassociarUsuarioModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const usuarioId = button.getAttribute('data-usuario-id');
            const usuarioNome = button.getAttribute('data-usuario-nome');

            document.getElementById('desassociarUsuarioId').value = usuarioId;
            document.getElementById('desassociarUsuarioNome').textContent = usuarioNome;
        });
    }

    // Animação de entrada para linhas da tabela
    const animateRows = (tableId) => {
        const rows = document.querySelectorAll(`#${tableId} tbody tr`);
        rows.forEach((row, index) => {
            row.style.opacity = '0';
            row.style.transform = 'translateY(10px)';
            row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';

            setTimeout(() => {
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, 50 * index);
        });
    };

    // Anima as tabelas
    animateRows('usuariosTable');

    // Anima a tabela do modal quando ele é aberto
    const associarUsuarioModal = document.getElementById('associarUsuarioModal');
    if (associarUsuarioModal) {
        associarUsuarioModal.addEventListener('shown.bs.modal', function () {
            animateRows('usuariosDisponiveis');
        });
    }

    // Função para obter a base URL do sistema
    function getBaseUrl() {
        // Tenta obter a base URL de uma variável global definida no template
        if (typeof baseUrl !== 'undefined') {
            return baseUrl;
        }

        // Caso contrário, tenta extrair do atributo base href
        const baseElement = document.querySelector('base');
        if (baseElement && baseElement.href) {
            return baseElement.href.replace(/\/$/, '');
        }

        // Fallback: usa o caminho atual até a raiz do aplicativo
        return window.location.origin;
    }

    // Define a variável baseUrl se ainda não estiver definida
    if (typeof baseUrl === 'undefined') {
        window.baseUrl = getBaseUrl();
    }

    // Destaca visualmente o usuário principal
    const destacarPrincipal = () => {
        const principalRows = document.querySelectorAll('#usuariosTable tbody tr');
        principalRows.forEach(row => {
            const isPrincipal = row.querySelector('.principal-badge.yes');
            if (isPrincipal) {
                row.classList.add('row-principal');
            }
        });
    };

    destacarPrincipal();
});