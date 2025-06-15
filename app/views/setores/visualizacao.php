<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Setores</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleViewBtn">
                <i class="fas fa-th-large me-1"></i> Alternar Visualização
            </button>
        </div>
    </div>
</div>

<!-- Visualização em Cards -->
<div id="cardsView" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
    <?php foreach ($setores as $setor): ?>
        <div class="col">
            <div class="card h-100 <?= $setor['ativo'] ? '' : 'bg-light' ?>">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><?= htmlspecialchars($setor['nome']) ?></h5>
                    <span class="badge bg-<?= $setor['ativo'] ? 'success' : 'secondary' ?>">
                        <?= $setor['ativo'] ? 'Ativo' : 'Inativo' ?>
                    </span>
                </div>
                <div class="card-body">
                    <?php if (!empty($setor['descricao'])): ?>
                        <p class="card-text"><?= htmlspecialchars($setor['descricao']) ?></p>
                    <?php else: ?>
                        <p class="card-text text-muted"><em>Sem descrição</em></p>
                    <?php endif; ?>

                    <div class="mt-3">
                        <h6>Estatísticas</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total de Chamados:</span>
                            <span class="badge bg-primary"><?= $setor['total_chamados'] ?></span>
                        </div>

                        <?php if (!empty($setor['chamados_por_status'])): ?>
                            <div class="progress mb-2" style="height: 20px;">
                                <?php foreach ($setor['chamados_por_status'] as $status): ?>
                                    <div class="progress-bar bg-<?= getStatusColor($status['status']) ?>"
                                        role="progressbar"
                                        style="width: <?= $status['percentual'] ?>%;"
                                        title="<?= $status['nome'] ?>: <?= $status['total'] ?> chamados (<?= $status['percentual'] ?>%)">
                                        <?= $status['total'] ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($setor['tempo_medio_atendimento'])): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tempo Médio de Atendimento:</span>
                                <span class="badge bg-info"><?= formatarTempo($setor['tempo_medio_atendimento']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?= base_url('setores/detalhes/' . $setor['id']) ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye me-1"></i> Ver Detalhes
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($setores)): ?>
        <div class="col-12">
            <div class="alert alert-info">
                Nenhum setor encontrado.
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Visualização em Tabela -->
<div id="tableView" class="mb-4" style="display: none;">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Total de Chamados</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($setores as $setor): ?>
                            <tr>
                                <td><?= htmlspecialchars($setor['nome']) ?></td>
                                <td>
                                    <?php if (!empty($setor['descricao'])): ?>
                                        <?= htmlspecialchars(substr($setor['descricao'], 0, 50)) ?>
                                        <?= strlen($setor['descricao']) > 50 ? '...' : '' ?>
                                    <?php else: ?>
                                        <span class="text-muted"><em>Sem descrição</em></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= $setor['total_chamados'] ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $setor['ativo'] ? 'success' : 'secondary' ?>">
                                        <?= $setor['ativo'] ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= base_url('setores/detalhes/' . $setor['id']) ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($setores)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Nenhum setor encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleViewBtn = document.getElementById('toggleViewBtn');
        const cardsView = document.getElementById('cardsView');
        const tableView = document.getElementById('tableView');

        // Verifica se há preferência salva no localStorage
        const preferredView = localStorage.getItem('setoresViewPreference') || 'cards';

        if (preferredView === 'table') {
            cardsView.style.display = 'none';
            tableView.style.display = 'block';
            toggleViewBtn.innerHTML = '<i class="fas fa-th-large me-1"></i> Ver em Cards';
        }

        toggleViewBtn.addEventListener('click', function() {
            if (cardsView.style.display === 'none') {
                cardsView.style.display = 'flex';
                tableView.style.display = 'none';
                toggleViewBtn.innerHTML = '<i class="fas fa-table me-1"></i> Ver em Tabela';
                localStorage.setItem('setoresViewPreference', 'cards');
            } else {
                cardsView.style.display = 'none';
                tableView.style.display = 'block';
                toggleViewBtn.innerHTML = '<i class="fas fa-th-large me-1"></i> Ver em Cards';
                localStorage.setItem('setoresViewPreference', 'table');
            }
        });
    });

    // Função auxiliar para obter a cor do status
    function getStatusColor(status) {
        const colors = {
            'aberto': 'danger',
            'em_andamento': 'warning',
            'pausado': 'info',
            'concluido': 'success',
            'cancelado': 'secondary'
        };

        return colors[status] || 'primary';
    }

    // Função auxiliar para formatar tempo
    function formatarTempo(minutos) {
        if (minutos < 60) {
            return minutos + ' min';
        } else {
            const horas = Math.floor(minutos / 60);
            const min = minutos % 60;
            return horas + 'h ' + (min > 0 ? min + 'min' : '');
        }
    }
</script>