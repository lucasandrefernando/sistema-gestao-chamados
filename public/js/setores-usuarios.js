/**
 * setores-usuarios.js
 * Funcionalidades específicas para a página de gerenciamento de usuários do setor
 * 
 * Este arquivo JavaScript utiliza um namespace específico (SetoresUsuarios)
 * para garantir que não haja conflito com outras funcionalidades do sistema.
 * 
 * Autor: [Nome do Desenvolvedor]
 * Data: [Data de Criação/Atualização]
 */

// Namespace para isolar as funcionalidades
var SetoresUsuarios = (function () {
    // Variáveis privadas
    var itemsPerPage = 4; // Configurado para 4 registros por página
    var currentAssociadosPage = 1;
    var currentDisponiveisPage = 1;

    /**
     * Inicializa todas as funcionalidades da página
     */
    function initialize() {
        // Inicializar filtros de pesquisa
        initializeSearch();

        // Inicializar seleção em massa
        initializeCheckboxes();

        // Inicializar confirmações de ações
        initializeConfirmations();

        // Inicializar avatares coloridos
        initializeAvatars();

        // Inicializar paginação
        initializePagination();

        console.log('SetoresUsuarios: Módulo inicializado com sucesso');
    }

    /**
     * Inicializa a funcionalidade de pesquisa
     */
    function initializeSearch() {
        // Pesquisa para usuários associados
        var searchAssociados = document.getElementById('su-search-associados');
        if (searchAssociados) {
            searchAssociados.addEventListener('input', function () {
                filterTable('su-table-associados', this.value);

                // Resetar paginação para a primeira página quando pesquisar
                if (currentAssociadosPage) {
                    goToPage('associados', 1);
                }
            });
        }

        // Pesquisa para usuários disponíveis
        var searchDisponiveis = document.getElementById('su-search-disponiveis');
        if (searchDisponiveis) {
            searchDisponiveis.addEventListener('input', function () {
                filterTable('su-table-disponiveis', this.value);

                // Resetar paginação para a primeira página quando pesquisar
                if (currentDisponiveisPage) {
                    goToPage('disponiveis', 1);
                }
            });
        }
    }

    /**
     * Filtra uma tabela com base no texto de pesquisa
     * @param {string} tableId - ID da tabela a ser filtrada
     * @param {string} searchText - Texto de pesquisa
     */
    function filterTable(tableId, searchText) {
        var table = document.getElementById(tableId);
        if (!table) return;

        var rows = table.querySelectorAll('tbody tr');
        var lowerSearchText = searchText.toLowerCase();

        rows.forEach(function (row) {
            var text = row.textContent.toLowerCase();
            if (text.includes(lowerSearchText)) {
                row.classList.remove('su-filtered-out');
            } else {
                row.classList.add('su-filtered-out');
            }
        });

        // Atualizar paginação após filtrar
        updatePagination(tableId === 'su-table-associados' ? 'associados' : 'disponiveis');
    }

    /**
     * Inicializa a funcionalidade de checkboxes para seleção em massa
     */
    function initializeCheckboxes() {
        // Checkbox "Selecionar todos" para usuários disponíveis (página atual)
        var selectAllDisponiveis = document.getElementById('su-select-all-disponiveis');
        if (selectAllDisponiveis) {
            selectAllDisponiveis.addEventListener('change', function () {
                var checkboxes = document.querySelectorAll('.su-user-checkbox-disponivel:not([disabled])');
                var visibleCheckboxes = Array.from(checkboxes).filter(function (cb) {
                    var row = cb.closest('tr');
                    return !row.classList.contains('su-filtered-out') && !row.classList.contains('su-pagination-hidden');
                });

                visibleCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = this.checked;
                }, this);

                updateSelectedCount('disponivel');
            });
        }

        // Botão "Selecionar todos os registros" para usuários disponíveis
        var selectAllPagesDisponiveis = document.getElementById('su-select-all-pages-disponiveis');
        if (selectAllPagesDisponiveis) {
            selectAllPagesDisponiveis.addEventListener('click', function () {
                var checkboxes = document.querySelectorAll('.su-user-checkbox-disponivel:not([disabled])');
                var visibleCheckboxes = Array.from(checkboxes).filter(function (cb) {
                    var row = cb.closest('tr');
                    return !row.classList.contains('su-filtered-out');
                });

                // Verificar se todos já estão selecionados
                var allSelected = visibleCheckboxes.every(function (cb) {
                    return cb.checked;
                });

                // Se todos estiverem selecionados, desmarcar todos, caso contrário, marcar todos
                visibleCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = !allSelected;
                });

                // Atualizar o checkbox "Selecionar página atual"
                if (selectAllDisponiveis) {
                    var currentPageCheckboxes = visibleCheckboxes.filter(function (cb) {
                        var row = cb.closest('tr');
                        return !row.classList.contains('su-pagination-hidden');
                    });

                    selectAllDisponiveis.checked = !allSelected && currentPageCheckboxes.length > 0;
                }

                updateSelectedCount('disponivel');

                // Mostrar feedback ao usuário
                showSelectionFeedback('disponivel', !allSelected, visibleCheckboxes.length);
            });
        }

        // Checkbox "Selecionar todos" para usuários associados (página atual)
        var selectAllAssociados = document.getElementById('su-select-all-associados');
        if (selectAllAssociados) {
            selectAllAssociados.addEventListener('change', function () {
                var checkboxes = document.querySelectorAll('.su-user-checkbox-associado:not([disabled])');
                var visibleCheckboxes = Array.from(checkboxes).filter(function (cb) {
                    var row = cb.closest('tr');
                    return !row.classList.contains('su-filtered-out') && !row.classList.contains('su-pagination-hidden');
                });

                visibleCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = this.checked;
                }, this);

                updateSelectedCount('associado');
            });
        }

        // Botão "Selecionar todos os registros" para usuários associados
        var selectAllPagesAssociados = document.getElementById('su-select-all-pages-associados');
        if (selectAllPagesAssociados) {
            selectAllPagesAssociados.addEventListener('click', function () {
                var checkboxes = document.querySelectorAll('.su-user-checkbox-associado:not([disabled])');
                var visibleCheckboxes = Array.from(checkboxes).filter(function (cb) {
                    var row = cb.closest('tr');
                    return !row.classList.contains('su-filtered-out');
                });

                // Verificar se todos já estão selecionados
                var allSelected = visibleCheckboxes.every(function (cb) {
                    return cb.checked;
                });

                // Se todos estiverem selecionados, desmarcar todos, caso contrário, marcar todos
                visibleCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = !allSelected;
                });

                // Atualizar o checkbox "Selecionar página atual"
                if (selectAllAssociados) {
                    var currentPageCheckboxes = visibleCheckboxes.filter(function (cb) {
                        var row = cb.closest('tr');
                        return !row.classList.contains('su-pagination-hidden');
                    });

                    selectAllAssociados.checked = !allSelected && currentPageCheckboxes.length > 0;
                }

                updateSelectedCount('associado');

                // Mostrar feedback ao usuário
                showSelectionFeedback('associado', !allSelected, visibleCheckboxes.length);
            });
        }

        // Checkboxes individuais para usuários disponíveis
        var userCheckboxesDisponiveis = document.querySelectorAll('.su-user-checkbox-disponivel');
        userCheckboxesDisponiveis.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                updateSelectedCount('disponivel');

                // Verificar se todos os checkboxes visíveis estão selecionados
                if (selectAllDisponiveis) {
                    var visibleCheckboxes = Array.from(userCheckboxesDisponiveis).filter(function (cb) {
                        var row = cb.closest('tr');
                        return !row.classList.contains('su-filtered-out') && !row.classList.contains('su-pagination-hidden');
                    });

                    var allChecked = visibleCheckboxes.every(function (cb) {
                        return cb.checked;
                    });

                    selectAllDisponiveis.checked = allChecked && visibleCheckboxes.length > 0;
                }
            });
        });

        // Checkboxes individuais para usuários associados
        var userCheckboxesAssociados = document.querySelectorAll('.su-user-checkbox-associado');
        userCheckboxesAssociados.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                updateSelectedCount('associado');

                // Verificar se todos os checkboxes visíveis estão selecionados
                if (selectAllAssociados) {
                    var visibleCheckboxes = Array.from(userCheckboxesAssociados).filter(function (cb) {
                        var row = cb.closest('tr');
                        return !row.classList.contains('su-filtered-out') && !row.classList.contains('su-pagination-hidden');
                    });

                    var allChecked = visibleCheckboxes.every(function (cb) {
                        return cb.checked;
                    });

                    selectAllAssociados.checked = allChecked && visibleCheckboxes.length > 0;
                }
            });
        });

        // Inicializar contadores
        updateSelectedCount('disponivel');
        updateSelectedCount('associado');
    }

    /**
     * Mostra um feedback ao usuário sobre a seleção em massa
     * @param {string} type - Tipo de usuário ('disponivel' ou 'associado')
     * @param {boolean} selected - Se os usuários foram selecionados ou desmarcados
     * @param {number} count - Quantidade de usuários afetados
     */
    function showSelectionFeedback(type, selected, count) {
        // Criar e mostrar uma notificação temporária
        var message = selected ?
            count + ' usuários selecionados em todas as páginas' :
            'Todos os usuários foram desmarcados';

        var notification = document.createElement('div');
        notification.className = 'su-notification';
        notification.innerHTML =
            '<div class="su-notification-content">' +
            '<i class="fas ' + (selected ? 'fa-check-circle' : 'fa-info-circle') + '"></i>' +
            '<span>' + message + '</span>' +
            '</div>';

        document.body.appendChild(notification);

        // Adicionar classe para animar a entrada
        setTimeout(function () {
            notification.classList.add('su-show');
        }, 10);

        // Remover após alguns segundos
        setTimeout(function () {
            notification.classList.remove('su-show');
            setTimeout(function () {
                notification.remove();
            }, 300);
        }, 3000);
    }

    /**
     * Atualiza o contador de usuários selecionados
     * @param {string} type - Tipo de usuário ('disponivel' ou 'associado')
     */
    function updateSelectedCount(type) {
        if (type === 'disponivel') {
            var selectedCount = document.querySelectorAll('.su-user-checkbox-disponivel:checked').length;

            // Atualizar contador no botão flutuante
            var countElement = document.getElementById('su-selected-count-disponivel');
            if (countElement) {
                countElement.textContent = selectedCount;
            }

            // Mostrar/ocultar botão flutuante
            var floatingButton = document.getElementById('su-mass-assign-floating');
            if (floatingButton) {
                if (selectedCount >= 2) {
                    floatingButton.style.display = 'block';
                } else {
                    floatingButton.style.display = 'none';
                }
            }

            // Habilitar/desabilitar botão de associação em massa
            var bulkButton = document.getElementById('su-bulk-assign-button');
            if (bulkButton) {
                bulkButton.disabled = selectedCount < 2;
            }
        } else if (type === 'associado') {
            var selectedCount = document.querySelectorAll('.su-user-checkbox-associado:checked').length;

            // Atualizar contador no botão flutuante
            var countElement = document.getElementById('su-selected-count-associado');
            if (countElement) {
                countElement.textContent = selectedCount;
            }

            // Mostrar/ocultar botão flutuante
            var floatingButton = document.getElementById('su-disassociate-floating');
            if (floatingButton) {
                if (selectedCount >= 2) {
                    floatingButton.style.display = 'block';
                } else {
                    floatingButton.style.display = 'none';
                }
            }

            // Habilitar/desabilitar botão de desassociação em massa
            var bulkButton = document.getElementById('su-bulk-disassociate-button');
            if (bulkButton) {
                bulkButton.disabled = selectedCount < 2;
            }
        }
    }

    /**
     * Inicializa confirmações para ações destrutivas
     */
    function initializeConfirmations() {
        var deleteButtons = document.querySelectorAll('.su-btn-desassociar');
        deleteButtons.forEach(function (button) {
            button.addEventListener('click', function (e) {
                if (!confirm('Tem certeza que deseja desassociar este usuário do setor?')) {
                    e.preventDefault();
                }
            });
        });
    }

    /**
     * Inicializa avatares coloridos baseados no nome
     */
    function initializeAvatars() {
        var avatars = document.querySelectorAll('.su-user-avatar');
        avatars.forEach(function (avatar) {
            var name = avatar.getAttribute('data-name');
            if (name) {
                // Definir inicial
                avatar.textContent = name.charAt(0).toUpperCase();

                // Definir cor baseada no nome
                var hue = getHueFromName(name);
                avatar.style.backgroundColor = 'hsl(' + hue + ', 85%, 90%)';
                avatar.style.color = 'hsl(' + hue + ', 70%, 35%)';
            }
        });
    }

    /**
     * Inicializa a paginação para as tabelas
     */
    function initializePagination() {
        // Inicializar paginação para ambas as tabelas
        updatePagination('associados');
        updatePagination('disponiveis');
    }

    /**
     * Atualiza a paginação para uma tabela específica
     * @param {string} tableType - Tipo de tabela ('associados' ou 'disponiveis')
     */
    function updatePagination(tableType) {
        var tableId = tableType === 'associados' ? 'su-table-associados' : 'su-table-disponiveis';
        var table = document.getElementById(tableId);
        if (!table) return;

        var rows = table.querySelectorAll('tbody tr:not(.su-filtered-out)');
        var totalItems = rows.length;
        var totalPages = Math.ceil(totalItems / itemsPerPage);

        // Atualizar o container de paginação
        var paginationContainer = document.getElementById('su-pagination-' + tableType);
        if (paginationContainer) {
            renderPagination(paginationContainer, totalPages, tableType === 'associados' ? currentAssociadosPage : currentDisponiveisPage, tableType);
        }

        // Mostrar/ocultar linhas com base na página atual
        var currentPage = tableType === 'associados' ? currentAssociadosPage : currentDisponiveisPage;
        var startIndex = (currentPage - 1) * itemsPerPage;
        var endIndex = startIndex + itemsPerPage;

        rows.forEach(function (row, index) {
            if (index >= startIndex && index < endIndex) {
                row.classList.remove('su-pagination-hidden');
            } else {
                row.classList.add('su-pagination-hidden');
            }
        });

        // Atualizar o texto de informação da paginação
        var paginationInfo = document.getElementById('su-pagination-info-' + tableType);
        if (paginationInfo) {
            paginationInfo.textContent = 'Mostrando ' + Math.min(startIndex + 1, totalItems) + '-' + Math.min(endIndex, totalItems) + ' de ' + totalItems + ' usuários';
        }
    }

    /**
     * Renderiza os controles de paginação
     * @param {HTMLElement} container - Container da paginação
     * @param {number} totalPages - Total de páginas
     * @param {number} currentPage - Página atual
     * @param {string} tableType - Tipo de tabela ('associados' ou 'disponiveis')
     */
    function renderPagination(container, totalPages, currentPage, tableType) {
        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        var html = '';

        // Botão anterior
        html += '<button class="su-pagination-btn" ' + (currentPage === 1 ? 'disabled' : '') + ' onclick="SetoresUsuarios.goToPage(\'' + tableType + '\', ' + (currentPage - 1) + ')">';
        html += '<i class="fas fa-chevron-left"></i>';
        html += '</button>';

        // Páginas
        if (totalPages <= 7) {
            // Mostrar todas as páginas se forem 7 ou menos
            for (var i = 1; i <= totalPages; i++) {
                html += '<button class="su-pagination-btn ' + (i === currentPage ? 'su-active' : '') + '" onclick="SetoresUsuarios.goToPage(\'' + tableType + '\', ' + i + ')">' + i + '</button>';
            }
        } else {
            // Lógica para mostrar páginas com elipses
            if (currentPage <= 3) {
                // Primeiras páginas
                for (var i = 1; i <= 5; i++) {
                    html += '<button class="su-pagination-btn ' + (i === currentPage ? 'su-active' : '') + '" onclick="SetoresUsuarios.goToPage(\'' + tableType + '\', ' + i + ')">' + i + '</button>';
                }
                html += '<span class="su-pagination-ellipsis">...</span>';
                html += '<button class="su-pagination-btn" onclick="SetoresUsuarios.goToPage(\'' + tableType + '\', ' + totalPages + ')">' + totalPages + '</button>';
            } else if (currentPage >= totalPages - 2) {
                // Últimas páginas
                html += '<button class="su-pagination-btn" onclick="SetoresUsuarios.goToPage(\'' + tableType + '\', 1)">1</button>';
                html += '<span class="su-pagination-ellipsis">...</span>';
                for (var i = totalPages - 4; i <= totalPages; i++) {
                    html += '<button class="su-pagination-btn ' + (i === currentPage ? 'su-active' : '') + '" onclick="SetoresUsuarios.goToPage(\'' + tableType + '\', ' + i + ')">' + i + '</button>';
                }
            } else {
                // Páginas do meio
                html += '<button class="su-pagination-btn" onclick="SetoresUsuarios.goToPage(\'' + tableType + '\', 1)">1</button>';
                html += '<span class="su-pagination-ellipsis">...</span>';
                for (var i = currentPage - 1; i <= currentPage + 1; i++) {
                    html += '<button class="su-pagination-btn ' + (i === currentPage ? 'su-active' : '') + '" onclick="SetoresUsuarios.goToPage(\'' + tableType + '\', ' + i + ')">' + i + '</button>';
                }
                html += '<span class="su-pagination-ellipsis">...</span>';
                html += '<button class="su-pagination-btn" onclick="SetoresUsuarios.goToPage(\'' + tableType + '\', ' + totalPages + ')">' + totalPages + '</button>';
            }
        }

        // Botão próximo
        html += '<button class="su-pagination-btn" ' + (currentPage === totalPages ? 'disabled' : '') + ' onclick="SetoresUsuarios.goToPage(\'' + tableType + '\', ' + (currentPage + 1) + ')">';
        html += '<i class="fas fa-chevron-right"></i>';
        html += '</button>';

        container.innerHTML = html;
    }

    /**
     * Navega para uma página específica
     * @param {string} tableType - Tipo de tabela ('associados' ou 'disponiveis')
     * @param {number} page - Número da página
     */
    function goToPage(tableType, page) {
        if (tableType === 'associados') {
            currentAssociadosPage = page;
        } else {
            currentDisponiveisPage = page;
        }

        updatePagination(tableType);

        // Resetar o checkbox "selecionar todos" da página atual
        var selectAllId = tableType === 'associados' ? 'su-select-all-associados' : 'su-select-all-disponiveis';
        var selectAll = document.getElementById(selectAllId);
        if (selectAll) {
            // Verificar se todos os checkboxes visíveis na nova página estão selecionados
            var checkboxClass = tableType === 'associados' ? '.su-user-checkbox-associado' : '.su-user-checkbox-disponivel';
            var checkboxes = document.querySelectorAll(checkboxClass);
            var visibleCheckboxes = Array.from(checkboxes).filter(function (cb) {
                var row = cb.closest('tr');
                return !row.classList.contains('su-filtered-out') && !row.classList.contains('su-pagination-hidden');
            });

            var allChecked = visibleCheckboxes.every(function (cb) {
                return cb.checked;
            });

            selectAll.checked = allChecked && visibleCheckboxes.length > 0;
        }

        // Atualizar contadores
        updateSelectedCount(tableType === 'associados' ? 'associado' : 'disponivel');

        // Rolar para o topo da tabela
        var tableId = tableType === 'associados' ? 'su-table-associados' : 'su-table-disponiveis';
        var table = document.getElementById(tableId);
        if (table) {
            table.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    /**
     * Gera um valor de matiz (0-360) baseado em uma string
     * @param {string} name - Nome para gerar a cor
     * @return {number} Valor de matiz (0-360)
     */
    function getHueFromName(name) {
        var hash = 0;
        for (var i = 0; i < name.length; i++) {
            hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        return hash % 360;
    }

    /**
     * Associa usuários em massa ao setor
     * @param {number} setorId - ID do setor
     */
    function associarEmMassa(setorId) {
        // Verificar se base_url está definido
        if (typeof base_url === 'undefined') {
            console.error('Erro: base_url não está definido');
            alert('Erro ao associar usuários: URL base não definida');
            return;
        }

        var selectedUsers = Array.from(document.querySelectorAll('.su-user-checkbox-disponivel:checked'))
            .map(function (checkbox) {
                return checkbox.value;
            });

        if (selectedUsers.length < 2) {
            alert('Selecione pelo menos dois usuários para associar em massa.');
            return;
        }

        // Mostrar loading aprimorado
        var loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'su-loading-overlay';
        loadingOverlay.innerHTML =
            '<div class="su-loading-content">' +
            '<div class="su-loading-spinner"></div>' +
            '<h3 class="su-loading-title">Associando Usuários</h3>' +
            '<p class="su-loading-text">Processando <strong>' + selectedUsers.length + '</strong> usuários</p>' +
            '<div class="su-loading-progress">' +
            '<div class="su-loading-progress-bar" id="su-progress-bar" style="width: 0%"></div>' +
            '</div>' +
            '<div class="su-loading-stats">' +
            '<span>Progresso: <strong id="su-progress-count">0</strong>/' + selectedUsers.length + '</span>' +
            '<span id="su-progress-percent">0%</span>' +
            '</div>' +
            '</div>';
        document.body.appendChild(loadingOverlay);

        // Associar usuários um por um
        var processed = 0;

        function associarProximo(index) {
            if (index >= selectedUsers.length) {
                // Todos os usuários foram processados, recarregar a página
                setTimeout(function () {
                    window.location.reload();
                }, 1000);
                return;
            }

            var userId = selectedUsers[index];

            // Atualizar progresso
            var progressCount = document.getElementById('su-progress-count');
            var progressBar = document.getElementById('su-progress-bar');
            var progressPercent = document.getElementById('su-progress-percent');

            if (progressCount && progressBar && progressPercent) {
                var current = index + 1;
                var percent = Math.round((current / selectedUsers.length) * 100);

                progressCount.textContent = current;
                progressBar.style.width = percent + '%';
                progressPercent.textContent = percent + '%';
            }

            // Criar e enviar formulário para este usuário
            var form = document.createElement('form');
            form.style.display = 'none';
            form.method = 'post';

            // Garantir que a URL termina com uma barra
            var url = base_url;
            if (!url.endsWith('/')) {
                url += '/';
            }

            form.action = url + 'setores/associarUsuario';

            // Adicionar campos
            var setorIdInput = document.createElement('input');
            setorIdInput.type = 'hidden';
            setorIdInput.name = 'setor_id';
            setorIdInput.value = setorId;
            form.appendChild(setorIdInput);

            var userIdInput = document.createElement('input');
            userIdInput.type = 'hidden';
            userIdInput.name = 'usuario_id';
            userIdInput.value = userId;
            form.appendChild(userIdInput);

            var associarInput = document.createElement('input');
            associarInput.type = 'hidden';
            associarInput.name = 'associar';
            associarInput.value = '1';
            form.appendChild(associarInput);

            // Usar um iframe para submeter o formulário sem recarregar a página
            var iframe = document.createElement('iframe');
            iframe.name = 'submit-frame-' + index;
            iframe.style.display = 'none';
            document.body.appendChild(iframe);

            form.target = iframe.name;
            document.body.appendChild(form);

            // Quando o iframe terminar de carregar, processar o próximo usuário
            iframe.onload = function () {
                processed++;

                // Processar o próximo usuário
                setTimeout(function () {
                    associarProximo(index + 1);
                }, 300);
            };

            form.submit();
        }

        // Iniciar o processo
        setTimeout(function () {
            associarProximo(0);
        }, 500);
    }

    /**
     * Desassocia usuários em massa do setor
     * @param {number} setorId - ID do setor
     */
    function desassociarEmMassa(setorId) {
        // Verificar se base_url está definido
        if (typeof base_url === 'undefined') {
            console.error('Erro: base_url não está definido');
            alert('Erro ao desassociar usuários: URL base não definida');
            return;
        }

        var selectedUsers = Array.from(document.querySelectorAll('.su-user-checkbox-associado:checked'))
            .map(function (checkbox) {
                return checkbox.value;
            });

        if (selectedUsers.length < 2) {
            alert('Selecione pelo menos dois usuários para desassociar em massa.');
            return;
        }

        // Confirmar a ação
        if (!confirm('Tem certeza que deseja desassociar ' + selectedUsers.length + ' usuários deste setor?')) {
            return;
        }

        // Mostrar loading aprimorado
        var loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'su-loading-overlay';
        loadingOverlay.innerHTML =
            '<div class="su-loading-content">' +
            '<div class="su-loading-spinner"></div>' +
            '<h3 class="su-loading-title">Desassociando Usuários</h3>' +
            '<p class="su-loading-text">Processando <strong>' + selectedUsers.length + '</strong> usuários</p>' +
            '<div class="su-loading-progress">' +
            '<div class="su-loading-progress-bar" id="su-progress-bar" style="width: 0%"></div>' +
            '</div>' +
            '<div class="su-loading-stats">' +
            '<span>Progresso: <strong id="su-progress-count">0</strong>/' + selectedUsers.length + '</span>' +
            '<span id="su-progress-percent">0%</span>' +
            '</div>' +
            '</div>';
        document.body.appendChild(loadingOverlay);

        // Desassociar usuários um por um
        var processed = 0;

        function desassociarProximo(index) {
            if (index >= selectedUsers.length) {
                // Todos os usuários foram processados, recarregar a página
                setTimeout(function () {
                    window.location.reload();
                }, 1000);
                return;
            }

            var userId = selectedUsers[index];

            // Atualizar progresso
            var progressCount = document.getElementById('su-progress-count');
            var progressBar = document.getElementById('su-progress-bar');
            var progressPercent = document.getElementById('su-progress-percent');

            if (progressCount && progressBar && progressPercent) {
                var current = index + 1;
                var percent = Math.round((current / selectedUsers.length) * 100);

                progressCount.textContent = current;
                progressBar.style.width = percent + '%';
                progressPercent.textContent = percent + '%';
            }

            // Criar e enviar formulário para este usuário
            var form = document.createElement('form');
            form.style.display = 'none';
            form.method = 'post';

            // Garantir que a URL termina com uma barra
            var url = base_url;
            if (!url.endsWith('/')) {
                url += '/';
            }

            form.action = url + 'setores/associarUsuario';

            // Adicionar campos
            var setorIdInput = document.createElement('input');
            setorIdInput.type = 'hidden';
            setorIdInput.name = 'setor_id';
            setorIdInput.value = setorId;
            form.appendChild(setorIdInput);

            var userIdInput = document.createElement('input');
            userIdInput.type = 'hidden';
            userIdInput.name = 'usuario_id';
            userIdInput.value = userId;
            form.appendChild(userIdInput);

            var associarInput = document.createElement('input');
            associarInput.type = 'hidden';
            associarInput.name = 'associar';
            associarInput.value = '0'; // 0 para desassociar
            form.appendChild(associarInput);

            // Usar um iframe para submeter o formulário sem recarregar a página
            var iframe = document.createElement('iframe');
            iframe.name = 'submit-frame-' + index;
            iframe.style.display = 'none';
            document.body.appendChild(iframe);

            form.target = iframe.name;
            document.body.appendChild(form);

            // Quando o iframe terminar de carregar, processar o próximo usuário
            iframe.onload = function () {
                processed++;

                // Processar o próximo usuário
                setTimeout(function () {
                    desassociarProximo(index + 1);
                }, 300);
            };

            form.submit();
        }

        // Iniciar o processo
        setTimeout(function () {
            desassociarProximo(0);
        }, 500);
    }

    // Expor funções públicas
    return {
        initialize: initialize,
        goToPage: goToPage,
        associarEmMassa: associarEmMassa,
        desassociarEmMassa: desassociarEmMassa
    };
})();

// Inicializar o módulo quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', SetoresUsuarios.initialize);