<?php

/**
 * Página de Visualização de Chamados Hospitalares
 * Versão 5.0 - UX/UI Profissional e Organizada
 */
?>

<div class="chamado-visualizar">
    <!-- Cabeçalho do Chamado -->
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
                    <h2 class="chamado-card-titulo">
                        <i class="fas fa-info-circle"></i>
                        Informações do Chamado
                    </h2>
                </div>
                <div class="chamado-card-body">
                    <!-- Layout em Cards Organizados -->
                    <div class="chamado-info-cards">

                        <!-- Card: Solicitante -->
                        <div class="chamado-info-card-item">
                            <div class="chamado-info-card-header">
                                <i class="fas fa-user"></i>
                                <h4>Solicitante</h4>
                            </div>
                            <div class="chamado-info-card-content">
                                <div class="chamado-info-row">
                                    <label>Nome:</label>
                                    <span><?= htmlspecialchars($chamado['solicitante']) ?></span>
                                </div>

                                <?php if (!empty($chamado['numero_solicitante'])): ?>
                                    <div class="chamado-info-row">
                                        <label>Contato do Solicitante:</label>
                                        <div class="chamado-contato-wrapper">
                                            <span class="chamado-telefone"><?= formatarTelefone($chamado['numero_solicitante']) ?></span>
                                            <button type="button"
                                                class="chamado-btn-copiar"
                                                data-telefone="<?= htmlspecialchars($chamado['numero_solicitante']) ?>"
                                                title="Copiar número">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($chamado['email_origem'])): ?>
                                    <div class="chamado-info-row">
                                        <label>Email:</label>
                                        <a href="mailto:<?= htmlspecialchars($chamado['email_origem']) ?>"
                                            class="chamado-email-link">
                                            <?= htmlspecialchars($chamado['email_origem']) ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card: Detalhes do Chamado -->
                        <div class="chamado-info-card-item">
                            <div class="chamado-info-card-header">
                                <i class="fas fa-ticket-alt"></i>
                                <h4>Detalhes</h4>
                            </div>
                            <div class="chamado-info-card-content">
                                <div class="chamado-info-row">
                                    <label>Setor:</label>
                                    <div class="chamado-setor-wrapper">
                                        <span><?= htmlspecialchars($setor['nome']) ?></span>
                                        <button type="button" class="chamado-btn-link" data-bs-toggle="modal" data-bs-target="#transferirSetorModal">
                                            <i class="fas fa-exchange-alt"></i> Transferir
                                        </button>
                                    </div>
                                </div>

                                <div class="chamado-info-row">
                                    <label>Status:</label>
                                    <span class="chamado-status-badge chamado-status-<?= getStatusColor(strtolower(str_replace(' ', '_', $status['nome']))) ?>">
                                        <i class="fas fa-circle"></i> <?= htmlspecialchars($status['nome']) ?>
                                    </span>
                                </div>

                                <div class="chamado-info-row">
                                    <label>Tipo de Serviço:</label>
                                    <span><?= htmlspecialchars($chamado['tipo_servico'] ?? 'Não especificado') ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Card: Cronologia -->
                        <div class="chamado-info-card-item">
                            <div class="chamado-info-card-header">
                                <i class="fas fa-clock"></i>
                                <h4>Cronologia</h4>
                            </div>
                            <div class="chamado-info-card-content">
                                <div class="chamado-info-row">
                                    <label>Solicitado em:</label>
                                    <span><?= formatarData($chamado['data_solicitacao']) ?></span>
                                </div>

                                <?php if (!empty($chamado['data_conclusao'])): ?>
                                    <div class="chamado-info-row">
                                        <label>Concluído em:</label>
                                        <span><?= formatarData($chamado['data_conclusao']) ?></span>
                                    </div>
                                    <div class="chamado-info-row">
                                        <label>Tempo de Atendimento:</label>
                                        <span class="chamado-tempo-badge chamado-tempo-concluido">
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
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="chamado-info-row">
                                        <label>Tempo em Aberto:</label>
                                        <span class="chamado-tempo-badge chamado-tempo-aberto" data-inicio="<?= $chamado['data_solicitacao'] ?>">
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
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card: Paciente (se disponível) -->
                        <?php if (!empty($chamado['paciente']) || !empty($chamado['quarto_leito'])): ?>
                            <div class="chamado-info-card-item">
                                <div class="chamado-info-card-header">
                                    <i class="fas fa-user-injured"></i>
                                    <h4>Paciente</h4>
                                </div>
                                <div class="chamado-info-card-content">
                                    <?php if (!empty($chamado['paciente'])): ?>
                                        <div class="chamado-info-row">
                                            <label>Nome:</label>
                                            <span><?= htmlspecialchars($chamado['paciente']) ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($chamado['quarto_leito'])): ?>
                                        <div class="chamado-info-row">
                                            <label>Quarto/Leito:</label>
                                            <span class="chamado-quarto-badge"><?= htmlspecialchars($chamado['quarto_leito']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Descrição do Chamado -->
                    <div class="chamado-descricao-container">
                        <div class="chamado-descricao-header">
                            <i class="fas fa-align-left"></i>
                            <h4>Descrição do Chamado</h4>
                        </div>
                        <div class="chamado-descricao-content">
                            <?= nl2br(htmlspecialchars($chamado['descricao'])) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comentários -->
            <div class="chamado-card chamado-comentarios-card">
                <div class="chamado-card-header">
                    <h2 class="chamado-card-titulo">
                        <i class="fas fa-comments"></i>
                        Comentários
                    </h2>
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
                            <label for="comentario" class="chamado-form-label">
                                <i class="fas fa-comment-dots"></i> Adicionar Comentário
                            </label>
                            <textarea class="chamado-form-textarea" id="comentario" name="comentario" rows="3"
                                placeholder="Digite seu comentário sobre este chamado..." required></textarea>
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
                    <h2 class="chamado-card-titulo">
                        <i class="fas fa-bolt"></i>
                        Ações Rápidas
                    </h2>
                </div>
                <div class="chamado-card-body">
                    <div class="chamado-acoes-lista">
                        <button type="button" class="chamado-acao-btn chamado-acao-status" data-bs-toggle="modal" data-bs-target="#alterarStatusModal">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Alterar Status</span>
                        </button>

                        <button type="button" class="chamado-acao-btn chamado-acao-transferir" data-bs-toggle="modal" data-bs-target="#transferirSetorModal">
                            <i class="fas fa-random"></i>
                            <span>Transferir Setor</span>
                        </button>

                        <a href="<?= base_url('chamados/editar/' . $chamado['id']) ?>" class="chamado-acao-btn chamado-acao-editar">
                            <i class="fas fa-edit"></i>
                            <span>Editar</span>
                        </a>

                        <a href="<?= base_url('chamados/imprimir/' . $chamado['id']) ?>" class="chamado-acao-btn chamado-acao-imprimir" target="_blank">
                            <i class="fas fa-print"></i>
                            <span>Imprimir</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Histórico -->
            <div class="chamado-card chamado-historico-card">
                <div class="chamado-card-header">
                    <h2 class="chamado-card-titulo">
                        <i class="fas fa-history"></i>
                        Histórico
                    </h2>
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
                                                    <i class="fas fa-flag"></i> Status alterado de
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
                                                    <i class="fas fa-exchange-alt"></i> Transferido do setor
                                                    <span class="chamado-setor"><?= htmlspecialchars($item['setor_anterior_nome']) ?></span>
                                                    para
                                                    <span class="chamado-setor"><?= htmlspecialchars($item['setor_novo_nome']) ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!empty($item['observacao'])): ?>
                                            <div class="chamado-timeline-observacao">
                                                <i class="fas fa-comment"></i> <?= htmlspecialchars($item['observacao']) ?>
                                            </div>
                                        <?php endif; ?>

                                        <div class="chamado-timeline-autor">
                                            <i class="fas fa-user"></i> Por: <?= htmlspecialchars($item['usuario_nome'] ?? 'Sistema') ?>
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

<!-- Toast para feedback -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toastCopia" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i class="fas fa-check-circle text-success me-2"></i>
            <strong class="me-auto">Sucesso</strong>
            <small>agora</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            Número copiado para a área de transferência!
        </div>
    </div>
</div>

<!-- Modal Alterar Status -->
<div class="modal fade" id="alterarStatusModal" tabindex="-1" aria-labelledby="alterarStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content chamado-modal-content">
            <form action="<?= base_url('chamados/alterarStatus/' . $chamado['id']) ?>" method="post">
                <div class="modal-header chamado-modal-header">
                    <h5 class="modal-title chamado-modal-titulo" id="alterarStatusModalLabel">
                        <i class="fas fa-exchange-alt"></i> Alterar Status do Chamado
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body chamado-modal-body">
                    <div class="chamado-form-grupo">
                        <label for="status_id" class="chamado-form-label">
                            <i class="fas fa-flag"></i> Novo Status
                        </label>
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
                        <label for="observacao_status" class="chamado-form-label">
                            <i class="fas fa-comment"></i> Observação
                        </label>
                        <textarea class="chamado-form-textarea" id="observacao_status" name="observacao" rows="3"
                            placeholder="Descreva o motivo da alteração de status..."></textarea>
                    </div>
                </div>
                <div class="modal-footer chamado-modal-footer">
                    <button type="button" class="chamado-btn chamado-btn-cancelar" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="chamado-btn chamado-btn-salvar">
                        <i class="fas fa-save"></i> Salvar Alteração
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Transferir Setor -->
<div class="modal fade" id="transferirSetorModal" tabindex="-1" aria-labelledby="transferirSetorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content chamado-modal-content">
            <form action="<?= base_url('chamados/transferirSetor/' . $chamado['id']) ?>" method="post">
                <div class="modal-header chamado-modal-header">
                    <h5 class="modal-title chamado-modal-titulo" id="transferirSetorModalLabel">
                        <i class="fas fa-random"></i> Transferir Chamado para Outro Setor
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body chamado-modal-body">
                    <div class="chamado-form-grupo">
                        <label for="setor_id" class="chamado-form-label">
                            <i class="fas fa-building"></i> Novo Setor
                        </label>
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
                        <label for="observacao_setor" class="chamado-form-label">
                            <i class="fas fa-comment"></i> Motivo da Transferência
                        </label>
                        <textarea class="chamado-form-textarea" id="observacao_setor" name="observacao" rows="3"
                            placeholder="Explique o motivo da transferência para o novo setor..."></textarea>
                    </div>
                </div>
                <div class="modal-footer chamado-modal-footer">
                    <button type="button" class="chamado-btn chamado-btn-cancelar" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="chamado-btn chamado-btn-salvar">
                        <i class="fas fa-exchange-alt"></i> Transferir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>