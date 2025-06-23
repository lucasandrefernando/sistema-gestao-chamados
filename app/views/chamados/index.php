<div class="chamados-index">
    <!-- Cabeçalho da Página -->
    <div class="chamados-index-header">
        <h1 class="chamados-index-titulo">Chamados</h1>
        <div class="chamados-index-acoes">
            <a href="<?= base_url('chamados/criar') ?>" class="chamados-index-btn-novo">
                <i class="fas fa-plus"></i> Novo Chamado
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="chamados-index-filtros-card">
        <div class="chamados-index-filtros-header">
            <h5 class="chamados-index-filtros-titulo">Filtros</h5>
        </div>
        <div class="chamados-index-filtros-body">
            <form action="<?= base_url('chamados') ?>" method="get" class="chamados-index-filtros-form">
                <div class="chamados-index-filtros-grid">
                    <div class="chamados-index-filtro-grupo">
                        <label for="chamados-index-status" class="chamados-index-filtro-label">Status</label>
                        <select class="chamados-index-filtro-select" id="chamados-index-status" name="status">
                            <option value="">Todos</option>
                            <?php foreach ($statusList as $statusItem): ?>
                                <option value="<?= $statusItem['id'] ?>" <?= $filtros['status'] == $statusItem['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($statusItem['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="chamados-index-filtro-grupo">
                        <label for="chamados-index-setor" class="chamados-index-filtro-label">Setor</label>
                        <select class="chamados-index-filtro-select" id="chamados-index-setor" name="setor">
                            <option value="">Todos</option>
                            <?php foreach ($setores as $setorItem): ?>
                                <option value="<?= $setorItem['id'] ?>" <?= $filtros['setor'] == $setorItem['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($setorItem['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="chamados-index-filtro-grupo chamados-index-filtro-busca">
                        <label for="chamados-index-busca" class="chamados-index-filtro-label">Busca</label>
                        <input type="text" class="chamados-index-filtro-input" id="chamados-index-busca" name="busca"
                            value="<?= htmlspecialchars($filtros['busca']) ?>" placeholder="Descrição ou solicitante">
                    </div>
                    <div class="chamados-index-filtro-grupo chamados-index-filtro-acoes">
                        <div class="chamados-index-filtro-botoes">
                            <button type="submit" class="chamados-index-filtro-btn chamados-index-filtro-btn-aplicar">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="<?= base_url('chamados') ?>" class="chamados-index-filtro-btn chamados-index-filtro-btn-limpar">
                                <i class="fas fa-times"></i> Limpar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Chamados -->
    <div class="chamados-index-lista-card">
        <div class="chamados-index-lista-header">
            <h5 class="chamados-index-lista-titulo">Lista de Chamados</h5>
        </div>
        <div class="chamados-index-lista-body">
            <?php if (!empty($chamados)): ?>
                <div class="chamados-index-tabela-container">
                    <table class="chamados-index-tabela">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Descrição</th>
                                <th>Solicitante</th>
                                <th>Setor</th>
                                <th>Status</th>
                                <th>Data de Solicitação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($chamados as $chamado): ?>
                                <tr>
                                    <td class="chamados-index-id"><?= $chamado['id'] ?></td>
                                    <td class="chamados-index-descricao" data-bs-toggle="tooltip" title="<?= htmlspecialchars($chamado['descricao']) ?>">
                                        <?= htmlspecialchars(substr($chamado['descricao'], 0, 50)) . (strlen($chamado['descricao']) > 50 ? '...' : '') ?>
                                    </td>
                                    <td class="chamados-index-solicitante"><?= htmlspecialchars($chamado['solicitante']) ?></td>
                                    <td>
                                        <?php
                                        $setorEncontrado = false;
                                        foreach ($setores as $setorItem) {
                                            if ($setorItem['id'] == $chamado['setor_id']) {
                                                echo '<span class="chamados-index-setor">' . htmlspecialchars($setorItem['nome']) . '</span>';
                                                $setorEncontrado = true;
                                                break;
                                            }
                                        }
                                        if (!$setorEncontrado) {
                                            echo '<span class="chamados-index-setor chamados-index-na">N/A</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusEncontrado = false;
                                        foreach ($statusList as $statusItem) {
                                            if ($statusItem['id'] == $chamado['status_id']) {
                                                $statusClass = getStatusColor($statusItem['nome']);
                                                echo '<span class="chamados-index-status chamados-index-status-' . $statusClass . '">' . htmlspecialchars($statusItem['nome']) . '</span>';
                                                $statusEncontrado = true;
                                                break;
                                            }
                                        }
                                        if (!$statusEncontrado) {
                                            echo '<span class="chamados-index-status chamados-index-status-unknown">Desconhecido</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="chamados-index-data"><?= formatarData($chamado['data_solicitacao']) ?></td>
                                    <td>
                                        <div class="chamados-index-acoes-chamado">
                                            <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>" class="chamados-index-acao-btn chamados-index-acao-visualizar" data-bs-toggle="tooltip" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= base_url('chamados/editar/' . $chamado['id']) ?>" class="chamados-index-acao-btn chamados-index-acao-editar" data-bs-toggle="tooltip" title="Editar">
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
                <div class="chamados-index-sem-chamados">
                    <div class="chamados-index-mensagem-vazia">
                        <i class="fas fa-ticket-alt"></i>
                        <p>Nenhum chamado encontrado.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>