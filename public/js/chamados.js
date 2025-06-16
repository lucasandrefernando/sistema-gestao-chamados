/**
 * Funções específicas para o módulo de chamados
 */

/**
 * Inicializa os filtros e eventos da página de chamados
 */
function initChamadosPage() {
    // Inicializa o filtro de ano
    const anoFiltro = document.getElementById('anoFiltro');
    if (anoFiltro) {
        anoFiltro.addEventListener('change', function () {
            document.getElementById('formAnoFiltro').submit();
        });
    }

    // Inicializa tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Inicializa filtros avançados
    const filtrosCollapse = document.getElementById('filtrosCollapse');
    if (filtrosCollapse) {
        const bsCollapse = new bootstrap.Collapse(filtrosCollapse, {
            toggle: false
        });

        // Verifica se há filtros ativos
        const filtrosAtivos = document.querySelectorAll('.filtro-ativo');
        if (filtrosAtivos.length > 0) {
            bsCollapse.show();
        }
    }
}

/**
 * Atualiza o contador de filtros ativos
 */
function updateFilterCount() {
    const filterCount = document.querySelectorAll('.filtro-ativo').length;
    const filterBadge = document.getElementById('filterBadge');

    if (filterBadge) {
        filterBadge.textContent = filterCount;
        filterBadge.style.display = filterCount > 0 ? 'inline-block' : 'none';
    }
}