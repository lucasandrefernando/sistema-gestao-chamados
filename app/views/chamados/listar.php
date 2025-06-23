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
                <a href="<?= base_url('chamados/criar') ?>" class="chamados-listar-btn-novo">
                    <i class="fas fa-plus"></i> Novo Chamado
                </a>
            </div>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="chamados-listar-estatisticas">
        <div class="chamados-listar-card-estatistica chamados-listar-total">
            <div class="chamados-listar-icone-estatistica">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="chamados-listar-conteudo-estatistica">
                <div class="chamados-listar-valor-estatistica"><?= isset($estatisticas['total']) ? $estatisticas['total'] : count($chamados) ?></div>
                <div class="chamados-listar-label-estatistica">Total de Chamados</div>
                <div class="chamados-listar-descricao-estatistica">Todos os chamados registrados no sistema</div>
            </div>
        </div>

        <div class="chamados-listar-card-estatistica chamados-listar-abertos">
            <div class="chamados-listar-icone-estatistica">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="chamados-listar-conteudo-estatistica">
                <div class="chamados-listar-valor-estatistica">
                    <?php
                    $abertos = 0;
                    foreach ($chamados as $chamado) {
                        if ($chamado['status_id'] == 1) { // Assumindo que status_id 1 é "Aberto"
                            $abertos++;
                        }
                    }
                    echo $abertos;
                    ?>
                </div>
                <div class="chamados-listar-label-estatistica">Chamados Abertos</div>
                <div class="chamados-listar-descricao-estatistica">Chamados que aguardam atendimento</div>
            </div>
        </div>

        <div class="chamados-listar-card-estatistica chamados-listar-andamento">
            <div class="chamados-listar-icone-estatistica">
                <i class="fas fa-clock"></i>
            </div>
            <div class="chamados-listar-conteudo-estatistica">
                <div class="chamados-listar-valor-estatistica">
                    <?php
                    $emAndamento = 0;
                    foreach ($chamados as $chamado) {
                        if ($chamado['status_id'] == 2) { // Assumindo que status_id 2 é "Em Andamento"
                            $emAndamento++;
                        }
                    }
                    echo $emAndamento;
                    ?>
                </div>
                <div class="chamados-listar-label-estatistica">Em Atendimento</div>
                <div class="chamados-listar-descricao-estatistica">Chamados que estão sendo processados</div>
            </div>
        </div>

        <div class="chamados-listar-card-estatistica chamados-listar-concluidos">
            <div class="chamados-listar-icone-estatistica">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="chamados-listar-conteudo-estatistica">
                <div class="chamados-listar-valor-estatistica">
                    <?php
                    $concluidos = 0;
                    foreach ($chamados as $chamado) {
                        if ($chamado['status_id'] == 4) { // Assumindo que status_id 4 é "Concluído"
                            $concluidos++;
                        }
                    }
                    echo $concluidos;
                    ?>
                </div>
                <div class="chamados-listar-label-estatistica">Concluídos</div>
                <div class="chamados-listar-descricao-estatistica">Chamados finalizados com sucesso</div>
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

    <!-- Lista de Chamados -->
    <div class="chamados-listar-tabela-card">
        <div class="chamados-listar-tabela-header">
            <h5 class="chamados-listar-tabela-titulo">
                <i class="fas fa-list-ul"></i> Solicitações
            </h5>
            <span class="chamados-listar-tabela-contador"><?= count($chamados) ?> chamados encontrados</span>
        </div>
        <div class="chamados-listar-tabela-body">
            <?php if (!empty($chamados)): ?>
                <div class="chamados-listar-tabela-container">
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
            <?php else: ?>
                <div class="chamados-listar-sem-chamados">
                    <div class="chamados-listar-mensagem-vazia">
                        <i class="fas fa-ticket-alt"></i>
                        <p>Nenhum chamado encontrado com os filtros selecionados.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>