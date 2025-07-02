<div class="chamado-visualizar">
    <div class="chamado-header">
        <div class="chamado-titulo">
            <h1 class="chamado-id">
                Chamado #<?= $chamado['id'] ?>
                <span class="chamado-status-badge chamado-status-<?= getStatusColor(strtolower(str_replace(' ', '_', $status['nome']))) ?>">
                    <?= htmlspecialchars($status['nome']) ?>
                </span>
            </h1>
        </div>
        <div class="chamado-acoes-header">
            <div class="chamado-btn-group">
                <a href="javascript:history.back();" class="chamado-btn chamado-btn-voltar">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <a href="<?= base_url('chamados/editar/' . $chamado['id']) ?>" class="chamado-btn chamado-btn-editar">
                    <i class="fas fa-edit"></i> Editar
                </a>
            </div> 
            <button type="button" class="chamado-btn chamado-btn-status" data-bs-toggle="modal" data-bs-target="#alterarStatusModal">
                <i class="fas fa-exchange-alt"></i> Alterar Status
            </button>
        </div>
    </div>

    <div class="chamado-conteudo">
        <!-- Coluna Principal -->
        <div class="chamado-coluna-principal">
            <!-- Informações do Chamado -->
            <div class="chamado-card chamado-info-card">
                <div class="chamado-card-header">
                    <h2 class="chamado-card-titulo">Informações do Chamado</h2>
                </div>
                <div class="chamado-card-body">
                    <div class="chamado-info-grid">
                        <div class="chamado-info-secao">
                            <h3 class="chamado-secao-titulo">Informações Gerais</h3>
                            <table class="chamado-tabela">
                                <tr>
                                    <th>Solicitante:</th>
                                    <td><?= htmlspecialchars($chamado['solicitante']) ?></td>
                                </tr>
                                <tr>
                                    <th>Setor:</th>
                                    <td>
                                        <?= htmlspecialchars($setor['nome']) ?>
                                        <button type="button" class="chamado-btn-link" data-bs-toggle="modal" data-bs-target="#transferirSetorModal">
                                            <i class="fas fa-exchange-alt"></i> Transferir
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="chamado-status-badge chamado-status-<?= getStatusColor(strtolower(str_replace(' ', '_', $status['nome']))) ?>">
                                            <?= htmlspecialchars($status['nome']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tipo de Serviço:</th>
                                    <td><?= htmlspecialchars($chamado['tipo_servico'] ?? 'Não especificado') ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="chamado-info-secao">
                            <h3 class="chamado-secao-titulo">Datas</h3>
                            <table class="chamado-tabela">
                                <tr>
                                    <th>Data de Solicitação:</th>
                                    <td><?= formatarData($chamado['data_solicitacao']) ?></td>
                                </tr>
                                <?php if (!empty($chamado['data_conclusao'])): ?>
                                    <tr>
                                        <th>Data de Conclusão:</th>
                                        <td><?= formatarData($chamado['data_conclusao']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Tempo de Atendimento:</th>
                                        <td>
                                            <?php
                                            $inicio = new DateTime($chamado['data_solicitacao']);
                                            $fim = new DateTime($chamado['data_conclusao']);
                                            $diff = $inicio->diff($fim);

                                            $tempoFormatado = '';
                                            if ($diff->d > 0) {
                                                $tempoFormatado .= $diff->d . ' dia(s), ';
                                            }
                                            $tempoFormatado .= sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
                                            echo $tempoFormatado;
                                            ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <th>Tempo em Aberto:</th>
                                        <td class="chamado-tempo-aberto" data-inicio="<?= $chamado['data_solicitacao'] ?>">
                                            <?php
                                            $inicio = new DateTime($chamado['data_solicitacao']);
                                            $agora = new DateTime();
                                            $diff = $inicio->diff($agora);

                                            $tempoFormatado = '';
                                            if ($diff->d > 0) {
                                                $tempoFormatado .= $diff->d . ' dia(s), ';
                                            }
                                            $tempoFormatado .= sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
                                            echo $tempoFormatado;
                                            ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <?php if (!empty($chamado['paciente']) || !empty($chamado['quarto_leito'])): ?>
                        <div class="chamado-info-secao chamado-info-paciente">
                            <h3 class="chamado-secao-titulo">Informações do Paciente</h3>
                            <table class="chamado-tabela">
                                <?php if (!empty($chamado['paciente'])): ?>
                                    <tr>
                                        <th>Paciente:</th>
                                        <td><?= htmlspecialchars($chamado['paciente']) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (!empty($chamado['quarto_leito'])): ?>
                                    <tr>
                                        <th>Quarto/Leito:</th>
                                        <td><?= htmlspecialchars($chamado['quarto_leito']) ?></td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    <?php endif; ?>

                    <div class="chamado-info-secao chamado-descricao-secao">
                        <h3 class="chamado-secao-titulo">Descrição</h3>
                        <div class="chamado-descricao">
                            <?= nl2br(htmlspecialchars($chamado['descricao'])) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comentários -->
            <div class="chamado-card chamado-comentarios-card">
                <div class="chamado-card-header">
                    <h2 class="chamado-card-titulo">Comentários</h2>
                </div>
                <div class="chamado-card-body">
                    <?php if (!empty($comentarios)): ?>
                        <div class="chamado-comentarios-lista">
                            <?php foreach ($comentarios as $comentario): ?>
                                <div class="chamado-comentario">
                                    <div class="chamado-comentario-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="chamado-comentario-conteudo">
                                        <div class="chamado-comentario-header">
                                            <h4 class="chamado-comentario-autor"><?= htmlspecialchars($comentario['usuario_nome'] ?? 'Usuário') ?></h4>
                                            <span class="chamado-comentario-data"><?= formatarData($comentario['data_criacao']) ?></span>
                                        </div>
                                        <div class="chamado-comentario-texto">
                                            <?= nl2br(htmlspecialchars($comentario['comentario'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="chamado-sem-comentarios">
                            <i class="fas fa-comments"></i>
                            <p>Nenhum comentário ainda.</p>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('chamados/adicionarComentario/' . $chamado['id']) ?>" method="post" class="chamado-form-comentario">
                        <div class="chamado-form-grupo">
                            <label for="comentario" class="chamado-form-label">Adicionar Comentário</label>
                            <textarea class="chamado-form-textarea" id="comentario" name="comentario" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="chamado-btn chamado-btn-comentar">
                            <i class="fas fa-paper-plane"></i> Enviar Comentário
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Coluna Lateral -->
        <div class="chamado-coluna-lateral">
            <!-- Ações Rápidas -->
            <div class="chamado-card chamado-acoes-card">
                <div class="chamado-card-header">
                    <h2 class="chamado-card-titulo">Ações Rápidas</h2>
                </div>
                <div class="chamado-card-body">
                    <div class="chamado-acoes-lista">
                        <button type="button" class="chamado-acao-btn chamado-acao-status" data-bs-toggle="modal" data-bs-target="#alterarStatusModal">
                            <i class="fas fa-exchange-alt"></i> Alterar Status
                        </button>
                        <button type="button" class="chamado-acao-btn chamado-acao-transferir" data-bs-toggle="modal" data-bs-target="#transferirSetorModal">
                            <i class="fas fa-random"></i> Transferir para Outro Setor
                        </button>
                        <a href="<?= base_url('chamados/editar/' . $chamado['id']) ?>" class="chamado-acao-btn chamado-acao-editar">
                            <i class="fas fa-edit"></i> Editar Chamado
                        </a>
                        <a href="<?= base_url('chamados/imprimir/' . $chamado['id']) ?>" class="chamado-acao-btn chamado-acao-imprimir">
                            <i class="fas fa-print"></i> Imprimir Chamado
                        </a>
                    </div>
                </div>
            </div>

            <!-- Histórico -->
            <div class="chamado-card chamado-historico-card">
                <div class="chamado-card-header">
                    <h2 class="chamado-card-titulo">Histórico do Chamado</h2>
                </div>
                <div class="chamado-card-body">
                    <?php if (!empty($historico)): ?>
                        <div class="chamado-timeline">
                            <?php foreach ($historico as $index => $item): ?>
                                <div class="chamado-timeline-item">
                                    <div class="chamado-timeline-marcador"></div>
                                    <div class="chamado-timeline-conteudo">
                                        <div class="chamado-timeline-data"><?= formatarData($item['data_criacao']) ?></div>
                                        <div class="chamado-timeline-texto">
                                            <?php if ($item['status_id_anterior'] != $item['status_id_novo']): ?>
                                                <div class="chamado-timeline-alteracao">
                                                    Status alterado de
                                                    <span class="chamado-status-badge chamado-status-<?= getStatusColor(strtolower(str_replace(' ', '_', $item['status_anterior_nome']))) ?>">
                                                        <?= htmlspecialchars($item['status_anterior_nome']) ?>
                                                    </span>
                                                    para
                                                    <span class="chamado-status-badge chamado-status-<?= getStatusColor(strtolower(str_replace(' ', '_', $item['status_novo_nome']))) ?>">
                                                        <?= htmlspecialchars($item['status_novo_nome']) ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($item['setor_id_anterior'] != $item['setor_id_novo']): ?>
                                                <div class="chamado-timeline-alteracao <?= $item['status_id_anterior'] != $item['status_id_novo'] ? 'chamado-timeline-alteracao-secundaria' : '' ?>">
                                                    Transferido do setor
                                                    <span class="chamado-setor"><?= htmlspecialchars($item['setor_anterior_nome']) ?></span>
                                                    para
                                                    <span class="chamado-setor"><?= htmlspecialchars($item['setor_novo_nome']) ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!empty($item['observacao'])): ?>
                                            <div class="chamado-timeline-observacao">
                                                <?= htmlspecialchars($item['observacao']) ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="chamado-timeline-autor">
                                            Por: <?= htmlspecialchars($item['usuario_nome'] ?? 'Sistema') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="chamado-sem-historico">
                            <i class="fas fa-history"></i>
                            <p>Nenhum histórico disponível.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Alterar Status -->
<div class="modal fade" id="alterarStatusModal" tabindex="-1" aria-labelledby="alterarStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content chamado-modal-content">
            <form action="<?= base_url('chamados/alterarStatus/' . $chamado['id']) ?>" method="post">
                <div class="modal-header chamado-modal-header">
                    <h5 class="modal-title chamado-modal-titulo" id="alterarStatusModalLabel">Alterar Status do Chamado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body chamado-modal-body">
                    <div class="chamado-form-grupo">
                        <label for="status_id" class="chamado-form-label">Novo Status</label>
                        <select class="chamado-form-select" id="status_id" name="status_id" required>
                            <option value="">Selecione um status</option>
                            <?php foreach ($statusDisponiveis as $statusItem): ?>
                                <option value="<?= $statusItem['id'] ?>" <?= $chamado['status_id'] == $statusItem['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($statusItem['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="chamado-form-grupo">
                        <label for="observacao_status" class="chamado-form-label">Observação</label>
                        <textarea class="chamado-form-textarea" id="observacao_status" name="observacao" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer chamado-modal-footer">
                    <button type="button" class="chamado-btn chamado-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="chamado-btn chamado-btn-salvar">Salvar Alteração</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Transferir Setor -->
<div class="modal fade" id="transferirSetorModal" tabindex="-1" aria-labelledby="transferirSetorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content chamado-modal-content">
            <form action="<?= base_url('chamados/transferirSetor/' . $chamado['id']) ?>" method="post">
                <div class="modal-header chamado-modal-header">
                    <h5 class="modal-title chamado-modal-titulo" id="transferirSetorModalLabel">Transferir Chamado para Outro Setor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body chamado-modal-body">
                    <div class="chamado-form-grupo">
                        <label for="setor_id" class="chamado-form-label">Novo Setor</label>
                        <select class="chamado-form-select" id="setor_id" name="setor_id" required>
                            <option value="">Selecione um setor</option>
                            <?php foreach ($setoresDisponiveis as $setorItem): ?>
                                <option value="<?= $setorItem['id'] ?>" <?= $chamado['setor_id'] == $setorItem['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($setorItem['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="chamado-form-grupo">
                        <label for="observacao_setor" class="chamado-form-label">Motivo da Transferência</label>
                        <textarea class="chamado-form-textarea" id="observacao_setor" name="observacao" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer chamado-modal-footer">
                    <button type="button" class="chamado-btn chamado-btn-cancelar" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="chamado-btn chamado-btn-salvar">Transferir</button>
                </div>
            </form>
        </div>
    </div>
</div>