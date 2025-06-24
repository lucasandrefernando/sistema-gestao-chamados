/**
 * setores.js - Funcionalidades específicas para a página de gestão de setores
 */

document.addEventListener('DOMContentLoaded', function () {
    // Inicializa os tooltips do Bootstrap (se estiver usando Bootstrap)
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
    }

    // Funcionalidade de busca na tabela
    const searchInput = document.getElementById('setoresSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('setoresTable');
            const rows = table.querySelectorAll('tbody tr:not(.empty-row)');

            let hasVisibleRows = false;

            rows.forEach(row => {
                const nome = row.querySelector('.setor-nome').textContent.toLowerCase();
                const descricao = row.querySelector('.col-descricao').textContent.toLowerCase();
                const id = row.querySelector('.col-id').textContent.toLowerCase();

                if (nome.includes(searchTerm) || descricao.includes(searchTerm) || id.includes(searchTerm)) {
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
        });
    }

    // Adiciona atributos data-label para responsividade em telas pequenas
    const table = document.getElementById('setoresTable');
    if (table) {
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
    }

    // Modal de remoção
    const removerModal = document.getElementById('removerModal');
    if (removerModal) {
        removerModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nome = button.getAttribute('data-nome');

            document.getElementById('removerNome').textContent = nome;
            document.getElementById('confirmarRemover').href = `${baseUrl}/setores/remover/${id}`;
        });
    }

    // Modal de restauração
    const restaurarModal = document.getElementById('restaurarModal');
    if (restaurarModal) {
        restaurarModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const nome = button.getAttribute('data-nome');
            const data = button.getAttribute('data-data');

            document.getElementById('restaurarNome').textContent = nome;
            document.getElementById('restaurarData').textContent = data;
            document.getElementById('confirmarRestaurar').href = `${baseUrl}/setores/restaurar/${id}`;
        });
    }

    // Animação de entrada para linhas da tabela
    const animateRows = () => {
        const rows = document.querySelectorAll('#setoresTable tbody tr');
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

    animateRows();

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
});