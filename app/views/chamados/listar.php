<div class="chamados-listar">
    <!-- Cabeçalho da Página -->
    <div class="chamados-listar-header">
        <div class="chamados-listar-header-content">
            <div class="chamados-listar-titulo-secao">
                <h1 class="chamados-listar-titulo">
                    <i class="fas fa-headset chamados-listar-icone"></i>
                    Central de Atendimento
                </h1>
                <p class="chamados-listar-subtitulo">Gerencie e acompanhe todas as solicitações de suporte</p>
            </div>
            <div class="chamados-listar-acoes">
                <a href="<?= base_url('chamados/relatorio') ?>" class="chamados-listar-btn-relatorio">
                    <i class="fas fa-chart-bar"></i> Relatórios
                </a>
                <a href="<?= base_url('chamados/criar') ?>" class="chamados-listar-btn-novo">
                    <i class="fas fa-plus"></i> Novo Chamado
                </a>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas Clicáveis -->
    <div class="chamados-listar-estatisticas">
        <div class="chamados-listar-card-estatistica chamados-listar-total" data-filter="todos">
            <div class="chamados-listar-icone-estatistica">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="chamados-listar-conteudo-estatistica">
                <div class="chamados-listar-valor-estatistica"><?= isset($estatisticas['total']) ? $estatisticas['total'] : count($chamados) ?></div>
                <div class="chamados-listar-label-estatistica">Total de Chamados</div>
                <div class="chamados-listar-descricao-estatistica">Todos os chamados registrados no sistema</div>
            </div>
            <div class="chamados-listar-estatistica-progresso">
                <div class="chamados-listar-estatistica-barra" style="width: 100%"></div>
            </div>
        </div>

        <div class="chamados-listar-card-estatistica chamados-listar-abertos" data-filter="status" data-status="1">
            <div class="chamados-listar-icone-estatistica">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="chamados-listar-conteudo-estatistica">
                <div class="chamados-listar-valor-estatistica"><?= $estatisticas['abertos'] ?? 0 ?></div>
                <div class="chamados-listar-label-estatistica">Chamados Abertos</div>
                <div class="chamados-listar-descricao-estatistica">Chamados que aguardam atendimento</div>
            </div>
            <div class="chamados-listar-estatistica-progresso">
                <?php $percentualAbertos = isset($estatisticas['total']) && $estatisticas['total'] > 0 ? ($estatisticas['abertos'] / $estatisticas['total']) * 100 : 0; ?>
                <div class="chamados-listar-estatistica-barra" style="width: <?= $percentualAbertos ?>%"></div>
            </div>
        </div>

        <div class="chamados-listar-card-estatistica chamados-listar-andamento" data-filter="status" data-status="2">
            <div class="chamados-listar-icone-estatistica">
                <i class="fas fa-clock"></i>
            </div>
            <div class="chamados-listar-conteudo-estatistica">
                <div class="chamados-listar-valor-estatistica"><?= $estatisticas['em_andamento'] ?? 0 ?></div>
                <div class="chamados-listar-label-estatistica">Em Atendimento</div>
                <div class="chamados-listar-descricao-estatistica">Chamados que estão sendo processados</div>
            </div>
            <div class="chamados-listar-estatistica-progresso">
                <?php $percentualAndamento = isset($estatisticas['total']) && $estatisticas['total'] > 0 ? ($estatisticas['em_andamento'] / $estatisticas['total']) * 100 : 0; ?>
                <div class="chamados-listar-estatistica-barra" style="width: <?= $percentualAndamento ?>%"></div>
            </div>
        </div>

        <div class="chamados-listar-card-estatistica chamados-listar-concluidos" data-filter="status" data-status="4">
            <div class="chamados-listar-icone-estatistica">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="chamados-listar-conteudo-estatistica">
                <div class="chamados-listar-valor-estatistica"><?= $estatisticas['concluidos'] ?? 0 ?></div>
                <div class="chamados-listar-label-estatistica">Concluídos</div>
                <div class="chamados-listar-descricao-estatistica">Chamados finalizados com sucesso</div>
            </div>
            <div class="chamados-listar-estatistica-progresso">
                <?php $percentualConcluidos = isset($estatisticas['total']) && $estatisticas['total'] > 0 ? ($estatisticas['concluidos'] / $estatisticas['total']) * 100 : 0; ?>
                <div class="chamados-listar-estatistica-barra" style="width: <?= $percentualConcluidos ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Formulário de Filtros Avançados -->
    <div class="chamados-listar-filtros-card">
        <div class="chamados-listar-filtros-header" data-bs-toggle="collapse" data-bs-target="#filtrosCollapse" aria-expanded="false" aria-controls="filtrosCollapse">
            <h5 class="chamados-listar-filtros-titulo">
                <i class="fas fa-filter"></i> Filtros Avançados
                <?php if (isset($filtros) && is_array($filtros) && array_filter($filtros)): ?>
                    <span class="chamados-listar-filtros-badge"><?= count(array_filter($filtros)) ?></span>
                <?php endif; ?>
            </h5>
            <button class="chamados-listar-filtros-toggle" type="button">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        <div class="collapse" id="filtrosCollapse">
            <div class="chamados-listar-filtros-body">
                <form action="<?= base_url('chamados/listar') ?>" method="get" class="chamados-listar-filtros-form">
                    <div class="chamados-listar-filtros-grid">
                        <div class="chamados-listar-filtro-grupo">
                            <label for="chamados-listar-status" class="chamados-listar-filtro-label">Status</label>
                            <select class="chamados-listar-filtro-select" id="chamados-listar-status" name="status">
                                <option value="">Todos</option>
                                <?php foreach ($statusList as $statusItem): ?>
                                    <option value="<?= $statusItem['id'] ?>" <?= (isset($filtros['status']) && $filtros['status'] == $statusItem['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($statusItem['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="chamados-listar-filtro-grupo">
                            <label for="chamados-listar-setor" class="chamados-listar-filtro-label">Setor</label>
                            <select class="chamados-listar-filtro-select" id="chamados-listar-setor" name="setor">
                                <option value="">Todos</option>
                                <?php foreach ($setores as $setorItem): ?>
                                    <option value="<?= $setorItem['id'] ?>" <?= (isset($filtros['setor']) && $filtros['setor'] == $setorItem['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($setorItem['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="chamados-listar-filtro-grupo">
                            <label for="chamados-listar-tipo" class="chamados-listar-filtro-label">Tipo de Serviço</label>
                            <select class="chamados-listar-filtro-select" id="chamados-listar-tipo" name="tipo_servico">
                                <option value="">Todos</option>
                                <?php foreach ($tiposServico as $tipo): ?>
                                    <option value="<?= $tipo ?>" <?= (isset($filtros['tipo_servico']) && $filtros['tipo_servico'] == $tipo) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tipo) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="chamados-listar-filtro-grupo">
                            <label for="chamados-listar-solicitante" class="chamados-listar-filtro-label">Solicitante</label>
                            <select class="chamados-listar-filtro-select" id="chamados-listar-solicitante" name="solicitante">
                                <option value="">Todos</option>
                                <?php if (isset($solicitantes) && is_array($solicitantes)): ?>
                                    <?php foreach ($solicitantes as $solicitante): ?>
                                        <option value="<?= $solicitante ?>" <?= (isset($filtros['solicitante']) && $filtros['solicitante'] == $solicitante) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($solicitante) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="chamados-listar-filtro-grupo">
                            <label for="chamados-listar-data-inicio" class="chamados-listar-filtro-label">Data Inicial</label>
                            <input type="date" class="chamados-listar-filtro-input" id="chamados-listar-data-inicio" name="data_inicio" value="<?= isset($filtros['data_inicio']) ? $filtros['data_inicio'] : '' ?>">
                        </div>
                        <div class="chamados-listar-filtro-grupo">
                            <label for="chamados-listar-data-fim" class="chamados-listar-filtro-label">Data Final</label>
                            <input type="date" class="chamados-listar-filtro-input" id="chamados-listar-data-fim" name="data_fim" value="<?= isset($filtros['data_fim']) ? $filtros['data_fim'] : '' ?>">
                        </div>
                        <div class="chamados-listar-filtro-grupo">
                            <label for="chamados-listar-busca" class="chamados-listar-filtro-label">Busca</label>
                            <input type="text" class="chamados-listar-filtro-input" id="chamados-listar-busca" name="busca" value="<?= isset($filtros['busca']) && $filtros['busca'] !== null ? htmlspecialchars($filtros['busca']) : '' ?>" placeholder="Descrição, solicitante ou paciente">
                        </div>
                        <div class="chamados-listar-filtro-grupo">
                            <label for="chamados-listar-ordenacao" class="chamados-listar-filtro-label">Ordenação</label>
                            <select class="chamados-listar-filtro-select" id="chamados-listar-ordenacao" name="ordenacao">
                                <option value="recentes" <?= (isset($filtros['ordenacao']) && $filtros['ordenacao'] == 'recentes') || (!isset($filtros['ordenacao'])) ? 'selected' : '' ?>>Mais recentes</option>
                                <option value="antigos" <?= (isset($filtros['ordenacao']) && $filtros['ordenacao'] == 'antigos') ? 'selected' : '' ?>>Mais antigos</option>
                                <option value="status" <?= (isset($filtros['ordenacao']) && $filtros['ordenacao'] == 'status') ? 'selected' : '' ?>>Por status</option>
                                <option value="setor" <?= (isset($filtros['ordenacao']) && $filtros['ordenacao'] == 'setor') ? 'selected' : '' ?>>Por setor</option>
                            </select>
                        </div>
                    </div>
                    <div class="chamados-listar-filtros-acoes">
                        <button type="submit" class="chamados-listar-filtros-btn chamados-listar-filtros-btn-aplicar">
                            <i class="fas fa-search"></i> Aplicar Filtros
                        </button>
                        <a href="<?= base_url('chamados/listar') ?>" class="chamados-listar-filtros-btn chamados-listar-filtros-btn-limpar">
                            <i class="fas fa-times"></i> Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Filtros Ativos -->
    <?php
    $filtrosAtivos = [];

    if (isset($filtros['status']) && $filtros['status'] !== '') {
        $statusNome = '';
        foreach ($statusList as $statusItem) {
            if ($statusItem['id'] == $filtros['status']) {
                $statusNome = $statusItem['nome'];
                break;
            }
        }
        $filtrosAtivos[] = ['label' => 'Status', 'value' => $statusNome];
    }

    if (isset($filtros['setor']) && $filtros['setor'] !== '') {
        $setorNome = '';
        foreach ($setores as $setorItem) {
            if ($setorItem['id'] == $filtros['setor']) {
                $setorNome = $setorItem['nome'];
                break;
            }
        }
        $filtrosAtivos[] = ['label' => 'Setor', 'value' => $setorNome];
    }

    if (isset($filtros['tipo_servico']) && $filtros['tipo_servico'] !== '') {
        $filtrosAtivos[] = ['label' => 'Tipo de Serviço', 'value' => $filtros['tipo_servico']];
    }

    if (isset($filtros['solicitante']) && $filtros['solicitante'] !== '') {
        $filtrosAtivos[] = ['label' => 'Solicitante', 'value' => $filtros['solicitante']];
    }

    if (isset($filtros['data_inicio']) && $filtros['data_inicio'] !== '') {
        $filtrosAtivos[] = ['label' => 'Data Inicial', 'value' => date('d/m/Y', strtotime($filtros['data_inicio']))];
    }

    if (isset($filtros['data_fim']) && $filtros['data_fim'] !== '') {
        $filtrosAtivos[] = ['label' => 'Data Final', 'value' => date('d/m/Y', strtotime($filtros['data_fim']))];
    }

    if (isset($filtros['busca']) && $filtros['busca'] !== '') {
        $filtrosAtivos[] = ['label' => 'Busca', 'value' => $filtros['busca']];
    }

    if (isset($filtros['ordenacao']) && $filtros['ordenacao'] !== '' && $filtros['ordenacao'] !== 'recentes') {
        $ordenacaoLabel = [
            'antigos' => 'Mais antigos',
            'status' => 'Por status',
            'setor' => 'Por setor'
        ];
        $filtrosAtivos[] = ['label' => 'Ordenação', 'value' => $ordenacaoLabel[$filtros['ordenacao']]];
    }

    if (!empty($filtrosAtivos)):
    ?>
        <div class="chamados-listar-filtros-ativos">
            <div class="chamados-listar-filtros-ativos-header">
                <i class="fas fa-filter"></i> Filtros aplicados:
                <a href="<?= base_url('chamados/listar') ?>" class="chamados-listar-filtros-ativos-limpar">
                    <i class="fas fa-times"></i> Limpar todos
                </a>
            </div>
            <div class="chamados-listar-filtros-ativos-lista">
                <?php foreach ($filtrosAtivos as $filtro): ?>
                    <div class="chamados-listar-filtro-ativo">
                        <span class="chamados-listar-filtro-ativo-label"><?= htmlspecialchars($filtro['label']) ?>:</span>
                        <span class="chamados-listar-filtro-ativo-valor"><?= htmlspecialchars($filtro['value']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Lista de Chamados -->
    <div class="chamados-listar-tabela-card">
        <div class="chamados-listar-tabela-header">
            <h5 class="chamados-listar-tabela-titulo">
                <i class="fas fa-list-ul"></i> Solicitações
            </h5>
            <div class="chamados-listar-tabela-acoes">
                <span class="chamados-listar-tabela-contador"><?= count($chamados) ?> chamados encontrados</span>
                <div class="chamados-listar-tabela-visualizacao">
                    <button class="chamados-listar-tabela-btn chamados-listar-tabela-btn-ativo" id="visualizacaoTabela">
                        <i class="fas fa-table"></i>
                    </button>
                    <button class="chamados-listar-tabela-btn" id="visualizacaoCards">
                        <i class="fas fa-th-large"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="chamados-listar-tabela-body">
            <?php if (!empty($chamados)): ?>
                <!-- Visualização em Tabela -->
                <div class="chamados-listar-tabela-container" id="visualizacaoTabelaContainer">
                    <table class="chamados-listar-tabela">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Descrição</th>
                                <th>Solicitante</th>
                                <th>Setor</th>
                                <th>Status</th>
                                <th>Tipo de Serviço</th>
                                <th>Data de Solicitação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($chamados as $chamado): ?>
                                <tr>
                                    <td class="chamados-listar-id"><?= $chamado['id'] ?></td>
                                    <td>
                                        <div class="chamados-listar-descricao" data-bs-toggle="tooltip" title="<?= htmlspecialchars($chamado['descricao']) ?>">
                                            <?= htmlspecialchars(substr($chamado['descricao'], 0, 50)) . (strlen($chamado['descricao']) > 50 ? '...' : '') ?>
                                        </div>
                                    </td>
                                    <td class="chamados-listar-solicitante"><?= htmlspecialchars($chamado['solicitante']) ?></td>
                                    <td>
                                        <?php
                                        $setorEncontrado = false;
                                        foreach ($setores as $setorItem) {
                                            if ($setorItem['id'] == $chamado['setor_id']) {
                                                echo '<span class="chamados-listar-setor">' . htmlspecialchars($setorItem['nome']) . '</span>';
                                                $setorEncontrado = true;
                                                break;
                                            }
                                        }
                                        if (!$setorEncontrado) {
                                            echo '<span class="chamados-listar-setor chamados-listar-na">N/A</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusEncontrado = false;
                                        foreach ($statusList as $statusItem) {
                                            if ($statusItem['id'] == $chamado['status_id']) {
                                                echo '<span class="chamados-listar-status chamados-listar-status-' . $chamado['status_id'] . '">' . htmlspecialchars($statusItem['nome']) . '</span>';
                                                $statusEncontrado = true;
                                                break;
                                            }
                                        }
                                        if (!$statusEncontrado) {
                                            echo '<span class="chamados-listar-status chamados-listar-status-unknown">Desconhecido</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?= !empty($chamado['tipo_servico']) ?
                                            '<span class="chamados-listar-tipo">' . htmlspecialchars($chamado['tipo_servico']) . '</span>' :
                                            '<span class="chamados-listar-tipo chamados-listar-na">N/A</span>'
                                        ?>
                                    </td>
                                    <td class="chamados-listar-data"><?= formatarData($chamado['data_solicitacao']) ?></td>
                                    <td>
                                        <div class="chamados-listar-acoes-chamado">
                                            <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>" class="chamados-listar-acao-btn chamados-listar-acao-visualizar" data-bs-toggle="tooltip" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= base_url('chamados/editar/' . $chamado['id']) ?>" class="chamados-listar-acao-btn chamados-listar-acao-editar" data-bs-toggle="tooltip" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Visualização em Cards -->
                <div class="chamados-listar-cards-container" id="visualizacaoCardsContainer" style="display: none;">
                    <div class="chamados-listar-cards-grid">
                        <?php foreach ($chamados as $chamado): ?>
                            <div class="chamados-listar-card-chamado">
                                <div class="chamados-listar-card-chamado-header">
                                    <div class="chamados-listar-card-chamado-id">
                                        #<?= $chamado['id'] ?>
                                    </div>
                                    <?php
                                    $statusEncontrado = false;
                                    foreach ($statusList as $statusItem) {
                                        if ($statusItem['id'] == $chamado['status_id']) {
                                            $statusClass = getStatusColor(strtolower(str_replace(' ', '_', $statusItem['nome'])));
                                            echo '<span class="chamados-listar-status chamados-listar-status-' . $statusClass . '">' . htmlspecialchars($statusItem['nome']) . '</span>';
                                            $statusEncontrado = true;
                                            break;
                                        }
                                    }
                                    if (!$statusEncontrado) {
                                        echo '<span class="chamados-listar-status chamados-listar-status-unknown">Desconhecido</span>';
                                    }
                                    ?>
                                </div>
                                <div class="chamados-listar-card-chamado-body">
                                    <div class="chamados-listar-card-chamado-descricao">
                                        <?= htmlspecialchars(substr($chamado['descricao'], 0, 100)) . (strlen($chamado['descricao']) > 100 ? '...' : '') ?>
                                    </div>
                                    <div class="chamados-listar-card-chamado-info">
                                        <div class="chamados-listar-card-chamado-info-item">
                                            <i class="fas fa-user"></i>
                                            <span><?= htmlspecialchars($chamado['solicitante']) ?></span>
                                        </div>
                                        <div class="chamados-listar-card-chamado-info-item">
                                            <i class="fas fa-building"></i>
                                            <span>
                                                <?php
                                                $setorEncontrado = false;
                                                foreach ($setores as $setorItem) {
                                                    if ($setorItem['id'] == $chamado['setor_id']) {
                                                        echo htmlspecialchars($setorItem['nome']);
                                                        $setorEncontrado = true;
                                                        break;
                                                    }
                                                }
                                                if (!$setorEncontrado) {
                                                    echo 'N/A';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                        <div class="chamados-listar-card-chamado-info-item">
                                            <i class="fas fa-tag"></i>
                                            <span><?= !empty($chamado['tipo_servico']) ? htmlspecialchars($chamado['tipo_servico']) : 'N/A' ?></span>
                                        </div>
                                        <div class="chamados-listar-card-chamado-info-item">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span><?= formatarData($chamado['data_solicitacao']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="chamados-listar-card-chamado-footer">
                                    <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>" class="chamados-listar-card-chamado-btn chamados-listar-card-chamado-btn-visualizar">
                                        <i class="fas fa-eye"></i> Visualizar
                                    </a>
                                    <a href="<?= base_url('chamados/editar/' . $chamado['id']) ?>" class="chamados-listar-card-chamado-btn chamados-listar-card-chamado-btn-editar">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="chamados-listar-sem-chamados">
                    <div class="chamados-listar-mensagem-vazia">
                        <i class="fas fa-ticket-alt"></i>
                        <p>Nenhum chamado encontrado com os filtros selecionados.</p>
                        <a href="<?= base_url('chamados/listar') ?>" class="chamados-listar-btn-limpar-filtros">
                            <i class="fas fa-filter"></i> Limpar Filtros
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Paginação -->
    <?php if (!empty($chamados) && isset($paginacao) && $paginacao['total_paginas'] > 1): ?>
        <div class="chamados-listar-paginacao" data-total-pages="<?= $paginacao['total_paginas'] ?>">
            <ul class="chamados-listar-paginacao-lista">
                <li class="chamados-listar-paginacao-item">
                    <a href="#" class="chamados-listar-paginacao-link chamados-listar-paginacao-link-anterior<?= $paginacao['pagina_atual'] <= 1 ? ' chamados-listar-paginacao-link-desabilitado' : '' ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>

                <?php
                // Determina quais páginas mostrar
                $pagina_atual = $paginacao['pagina_atual'];
                $total_paginas = $paginacao['total_paginas'];

                // Sempre mostra a primeira página
                if ($pagina_atual > 1) {
                    echo '<li class="chamados-listar-paginacao-item">';
                    echo '<a href="#" class="chamados-listar-paginacao-link">1</a>';
                    echo '</li>';
                }

                // Adiciona reticências se necessário
                if ($pagina_atual > 3) {
                    echo '<li class="chamados-listar-paginacao-item chamados-listar-paginacao-item-ellipsis">...</li>';
                }

                // Mostra páginas ao redor da página atual
                for ($i = max(2, $pagina_atual - 1); $i <= min($total_paginas - 1, $pagina_atual + 1); $i++) {
                    echo '<li class="chamados-listar-paginacao-item">';
                    echo '<a href="#" class="chamados-listar-paginacao-link' . ($i == $pagina_atual ? ' chamados-listar-paginacao-link-ativo' : '') . '">' . $i . '</a>';
                    echo '</li>';
                }

                // Adiciona reticências se necessário
                if ($pagina_atual < $total_paginas - 2) {
                    echo '<li class="chamados-listar-paginacao-item chamados-listar-paginacao-item-ellipsis">...</li>';
                }

                // Sempre mostra a última página
                if ($pagina_atual < $total_paginas) {
                    echo '<li class="chamados-listar-paginacao-item">';
                    echo '<a href="#" class="chamados-listar-paginacao-link">' . $total_paginas . '</a>';
                    echo '</li>';
                }
                ?>

                <li class="chamados-listar-paginacao-item">
                    <a href="#" class="chamados-listar-paginacao-link chamados-listar-paginacao-link-proximo<?= $paginacao['pagina_atual'] >= $paginacao['total_paginas'] ? ' chamados-listar-paginacao-link-desabilitado' : '' ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Resumo de Estatísticas -->
    <div class="chamados-listar-resumo">
        <div class="chamados-listar-resumo-header">
            <h3 class="chamados-listar-resumo-titulo">
                <i class="fas fa-chart-pie"></i> Resumo de Estatísticas
            </h3>
            <a href="<?= base_url('chamados/relatorio') ?>" class="chamados-listar-resumo-link">
                Ver relatório completo <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="chamados-listar-resumo-grid">
            <div class="chamados-listar-resumo-card">
                <div class="chamados-listar-resumo-card-header">
                    <h4 class="chamados-listar-resumo-card-titulo">Chamados por Status</h4>
                </div>
                <div class="chamados-listar-resumo-card-body">
                    <canvas id="graficoStatus" height="200"></canvas>
                </div>
            </div>
            <div class="chamados-listar-resumo-card">
                <div class="chamados-listar-resumo-card-header">
                    <h4 class="chamados-listar-resumo-card-titulo">Chamados por Setor</h4>
                </div>
                <div class="chamados-listar-resumo-card-body">
                    <canvas id="graficoSetor" height="200"></canvas>
                </div>
            </div>
            <div class="chamados-listar-resumo-card">
                <div class="chamados-listar-resumo-card-header">
                    <h4 class="chamados-listar-resumo-card-titulo">Chamados por Mês (<?= date('Y') ?>)</h4>
                </div>
                <div class="chamados-listar-resumo-card-body">
                    <canvas id="graficoMensal" height="200"></canvas>
                </div>
            </div>
            <div class="chamados-listar-resumo-card">
                <div class="chamados-listar-resumo-card-header">
                    <h4 class="chamados-listar-resumo-card-titulo">Tempo Médio de Resolução</h4>
                </div>
                <div class="chamados-listar-resumo-card-body">
                    <div class="chamados-listar-resumo-tempo">
                        <div class="chamados-listar-resumo-tempo-valor">
                            <?php
                            // Obtém o tempo médio real do banco de dados
                            $tempoMedio = 0;
                            if (isset($tempoMedioAtendimento) && !empty($tempoMedioAtendimento['data'])) {
                                $tempoMedio = array_sum($tempoMedioAtendimento['data']) / count($tempoMedioAtendimento['data']);
                            }
                            echo number_format($tempoMedio, 1);
                            ?>
                            <span>horas</span>
                        </div>
                        <div class="chamados-listar-resumo-tempo-info">
                            Tempo médio para resolução de chamados
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dados para os gráficos -->
    <div id="chamados-listar-dados"
        data-status='<?= json_encode($chamadosPorStatus ?? []) ?>'
        data-setor='<?= json_encode($chamadosPorSetor ?? []) ?>'
        data-mensal='<?= json_encode($chamadosPorMes ?? []) ?>'
        data-tempo='<?= json_encode($tempoMedioAtendimento ?? []) ?>'>
    </div>

    <!-- Modal de Exportação -->
    <div class="chamados-listar-modal" id="exportarModal">
        <div class="chamados-listar-modal-conteudo">
            <div class="chamados-listar-modal-header">
                <h3 class="chamados-listar-modal-titulo">Exportar Dados</h3>
                <button class="chamados-listar-modal-fechar" id="fecharModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="chamados-listar-modal-body">
                <p class="chamados-listar-modal-descricao">
                    Selecione o formato de exportação desejado:
                </p>
                <div class="chamados-listar-modal-opcoes">
                    <button class="chamados-listar-modal-opcao" id="exportarCsvBtn">
                        <i class="fas fa-file-csv"></i>
                        <span>CSV</span>
                    </button>
                    <button class="chamados-listar-modal-opcao" id="exportarExcelBtn">
                        <i class="fas fa-file-excel"></i>
                        <span>Excel</span>
                    </button>
                    <button class="chamados-listar-modal-opcao" id="exportarPdfBtn">
                        <i class="fas fa-file-pdf"></i>
                        <span>PDF</span>
                    </button>
                </div>
                <div class="chamados-listar-modal-opcoes-avancadas">
                    <h4 class="chamados-listar-modal-subtitulo">Opções Avançadas</h4>
                    <div class="chamados-listar-modal-opcao-grupo">
                        <label class="chamados-listar-modal-opcao-label">
                            <input type="checkbox" class="chamados-listar-modal-opcao-checkbox" checked>
                            Incluir cabeçalho
                        </label>
                    </div>
                    <div class="chamados-listar-modal-opcao-grupo">
                        <label class="chamados-listar-modal-opcao-label">
                            <input type="checkbox" class="chamados-listar-modal-opcao-checkbox" checked>
                            Aplicar filtros atuais
                        </label>
                    </div>
                    <div class="chamados-listar-modal-opcao-grupo">
                        <label class="chamados-listar-modal-opcao-label">
                            <input type="checkbox" class="chamados-listar-modal-opcao-checkbox">
                            Incluir dados detalhados
                        </label>
                    </div>
                </div>
            </div>
            <div class="chamados-listar-modal-footer">
                <button class="chamados-listar-modal-btn chamados-listar-modal-btn-cancelar" id="cancelarExportacao">
                    Cancelar
                </button>
                <button class="chamados-listar-modal-btn chamados-listar-modal-btn-confirmar" id="confirmarExportacao">
                    <i class="fas fa-download"></i> Exportar
                </button>
            </div>
        </div>
    </div>
</div>