<!-- views/chamados/imprimir.php -->
<div class="chamados-imprimir-container">
    <!-- Cabeçalho com Design Aprimorado -->
    <div class="chamados-imprimir-header">
        <div class="chamados-imprimir-header-content">
            <?php if (!empty($empresa['logo'])): ?>
                <img src="<?= base_url('uploads/logos/' . $empresa['logo']) ?>" alt="Logo" class="chamados-imprimir-logo">
            <?php else: ?>
                <div class="chamados-imprimir-logo-placeholder">
                    <i class="fas fa-ticket-alt"></i>
                </div>
            <?php endif; ?>
            <div class="chamados-imprimir-header-text">
                <h2 class="chamados-imprimir-titulo"><?= htmlspecialchars($empresa['nome'] ?? 'Sistema de Gestão de Chamados') ?></h2>
                <p class="chamados-imprimir-subtitulo">Relatório de Chamado #<?= $chamado['id'] ?></p>
            </div>
        </div>
        <div class="chamados-imprimir-header-stamp">
            <div class="chamados-imprimir-stamp-content">
                <div class="chamados-imprimir-stamp-icon">
                    <?php
                    // Ícone baseado no status
                    $statusIconClass = 'fa-question-circle';
                    $statusColorClass = 'status-unknown';

                    if (isset($status['nome'])) {
                        $statusLower = strtolower($status['nome']);
                        if (strpos($statusLower, 'aberto') !== false) {
                            $statusIconClass = 'fa-exclamation-circle';
                            $statusColorClass = 'status-open';
                        } elseif (strpos($statusLower, 'andamento') !== false) {
                            $statusIconClass = 'fa-clock';
                            $statusColorClass = 'status-progress';
                        } elseif (strpos($statusLower, 'conclu') !== false) {
                            $statusIconClass = 'fa-check-circle';
                            $statusColorClass = 'status-completed';
                        } elseif (strpos($statusLower, 'cancel') !== false) {
                            $statusIconClass = 'fa-times-circle';
                            $statusColorClass = 'status-cancelled';
                        }
                    }
                    ?>
                    <i class="fas <?= $statusIconClass ?> <?= $statusColorClass ?>"></i>
                </div>
                <div class="chamados-imprimir-stamp-text">
                    <?= htmlspecialchars($status['nome'] ?? 'Status Desconhecido') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Ações Aprimorada -->
    <div class="chamados-imprimir-acoes no-print">
        <div class="chamados-imprimir-acoes-grupo">
            <button class="chamados-imprimir-btn chamados-imprimir-btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <button class="chamados-imprimir-btn chamados-imprimir-btn-pdf" id="exportPdfBtn">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </button>
            <button class="chamados-imprimir-btn chamados-imprimir-btn-share" id="shareLinkBtn">
                <i class="fas fa-share-alt"></i> Compartilhar
            </button>
        </div>
        <div class="chamados-imprimir-acoes-grupo">
            <button class="chamados-imprimir-btn chamados-imprimir-btn-theme" id="toggleThemeBtn">
                <i class="fas fa-moon"></i> Modo Escuro
            </button>
            <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>" class="chamados-imprimir-btn chamados-imprimir-btn-back">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Resumo do Chamado -->
    <div class="chamados-imprimir-resumo">
        <div class="chamados-imprimir-resumo-item">
            <div class="chamados-imprimir-resumo-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="chamados-imprimir-resumo-content">
                <div class="chamados-imprimir-resumo-label">Data de Abertura</div>
                <div class="chamados-imprimir-resumo-value"><?= formatarData($chamado['data_solicitacao']) ?></div>
            </div>
        </div>

        <div class="chamados-imprimir-resumo-item">
            <div class="chamados-imprimir-resumo-icon">
                <i class="fas fa-user"></i>
            </div>
            <div class="chamados-imprimir-resumo-content">
                <div class="chamados-imprimir-resumo-label">Solicitante</div>
                <div class="chamados-imprimir-resumo-value"><?= htmlspecialchars($chamado['solicitante']) ?></div>
            </div>
        </div>

        <div class="chamados-imprimir-resumo-item">
            <div class="chamados-imprimir-resumo-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="chamados-imprimir-resumo-content">
                <div class="chamados-imprimir-resumo-label">Setor</div>
                <div class="chamados-imprimir-resumo-value"><?= htmlspecialchars($setor['nome']) ?></div>
            </div>
        </div>

        <?php if (!empty($chamado['data_conclusao'])): ?>
            <div class="chamados-imprimir-resumo-item">
                <div class="chamados-imprimir-resumo-icon">
                    <i class="fas fa-hourglass-end"></i>
                </div>
                <div class="chamados-imprimir-resumo-content">
                    <div class="chamados-imprimir-resumo-label">Tempo de Atendimento</div>
                    <div class="chamados-imprimir-resumo-value">
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
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Informações Detalhadas -->
    <div class="chamados-imprimir-secao">
        <h3 class="chamados-imprimir-secao-titulo">
            <i class="fas fa-info-circle"></i> Informações do Chamado
        </h3>
        <div class="chamados-imprimir-card">
            <div class="chamados-imprimir-grid">
                <div class="chamados-imprimir-coluna">
                    <table class="chamados-imprimir-tabela">
                        <tr>
                            <th>Número do Chamado:</th>
                            <td><strong>#<?= $chamado['id'] ?></strong></td>
                        </tr>
                        <tr>
                            <th>Solicitante:</th>
                            <td><?= htmlspecialchars($chamado['solicitante']) ?></td>
                        </tr>
                        <tr>
                            <th>Setor:</th>
                            <td><?= htmlspecialchars($setor['nome']) ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="chamados-imprimir-status <?= $statusColorClass ?>">
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
                <div class="chamados-imprimir-coluna">
                    <table class="chamados-imprimir-tabela">
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
                                <td><?= $tempoFormatado ?></td>
                            </tr>
                        <?php endif; ?>
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
            </div>
        </div>
    </div>

    <!-- Descrição do Chamado -->
    <div class="chamados-imprimir-secao">
        <h3 class="chamados-imprimir-secao-titulo">
            <i class="fas fa-align-left"></i> Descrição do Chamado
        </h3>
        <div class="chamados-imprimir-card">
            <div class="chamados-imprimir-descricao-texto">
                <?= nl2br(htmlspecialchars($chamado['descricao'])) ?>
            </div>
        </div>
    </div>

    <!-- Comentários -->
    <?php if (!empty($comentarios)): ?>
        <div class="chamados-imprimir-secao">
            <h3 class="chamados-imprimir-secao-titulo">
                <i class="fas fa-comments"></i> Comentários
                <span class="chamados-imprimir-contador"><?= count($comentarios) ?></span>
            </h3>
            <div class="chamados-imprimir-card">
                <div class="chamados-imprimir-comentarios-lista">
                    <?php foreach ($comentarios as $comentario): ?>
                        <div class="chamados-imprimir-comentario">
                            <div class="chamados-imprimir-comentario-header">
                                <div class="chamados-imprimir-comentario-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="chamados-imprimir-comentario-info">
                                    <div class="chamados-imprimir-comentario-autor">
                                        <?= htmlspecialchars($comentario['usuario_nome'] ?? 'Usuário') ?>
                                    </div>
                                    <div class="chamados-imprimir-comentario-data">
                                        <?= formatarData($comentario['data_criacao']) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="chamados-imprimir-comentario-conteudo">
                                <?= nl2br(htmlspecialchars($comentario['comentario'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Histórico do Chamado -->
    <?php if (!empty($historico)): ?>
        <div class="chamados-imprimir-secao">
            <h3 class="chamados-imprimir-secao-titulo">
                <i class="fas fa-history"></i> Histórico do Chamado
                <span class="chamados-imprimir-contador"><?= count($historico) ?></span>
            </h3>
            <div class="chamados-imprimir-card">
                <div class="chamados-imprimir-timeline">
                    <?php foreach ($historico as $index => $item): ?>
                        <div class="chamados-imprimir-timeline-item">
                            <div class="chamados-imprimir-timeline-marker"></div>
                            <div class="chamados-imprimir-timeline-content">
                                <div class="chamados-imprimir-timeline-header">
                                    <div class="chamados-imprimir-timeline-date">
                                        <?= formatarData($item['data_criacao']) ?>
                                    </div>
                                    <div class="chamados-imprimir-timeline-user">
                                        <i class="fas fa-user"></i> <?= htmlspecialchars($item['usuario_nome'] ?? 'Sistema') ?>
                                    </div>
                                </div>
                                <div class="chamados-imprimir-timeline-body">
                                    <?php if ($item['status_id_anterior'] != $item['status_id_novo']): ?>
                                        <div class="chamados-imprimir-timeline-change">
                                            <i class="fas fa-exchange-alt"></i>
                                            Status alterado de
                                            <span class="chamados-imprimir-timeline-old"><?= htmlspecialchars($item['status_anterior_nome']) ?></span>
                                            para
                                            <span class="chamados-imprimir-timeline-new"><?= htmlspecialchars($item['status_novo_nome']) ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($item['setor_id_anterior'] != $item['setor_id_novo']): ?>
                                        <div class="chamados-imprimir-timeline-change">
                                            <i class="fas fa-building"></i>
                                            Transferido do setor
                                            <span class="chamados-imprimir-timeline-old"><?= htmlspecialchars($item['setor_anterior_nome']) ?></span>
                                            para
                                            <span class="chamados-imprimir-timeline-new"><?= htmlspecialchars($item['setor_novo_nome']) ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($item['observacao'])): ?>
                                        <div class="chamados-imprimir-timeline-observation">
                                            <i class="fas fa-comment-dots"></i>
                                            <?= htmlspecialchars($item['observacao']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- QR Code e Informações de Autenticidade -->
    <div class="chamados-imprimir-autenticidade no-print-content">
        <div class="chamados-imprimir-qrcode-container">
            <div id="chamados-imprimir-qrcode"></div>
            <p class="chamados-imprimir-qrcode-text">Escaneie para acessar o chamado</p>
        </div>
        <div class="chamados-imprimir-autenticidade-info">
            <h4>Verificação de Autenticidade</h4>
            <p>Este documento foi gerado eletronicamente e pode ser verificado através do QR Code ao lado ou pelo link:</p>
            <p class="chamados-imprimir-autenticidade-link" id="chamados-imprimir-link"></p>
            <p>Código de verificação: <strong id="chamados-imprimir-hash"></strong></p>
        </div>
    </div>

    <!-- Rodapé Aprimorado -->
    <div class="chamados-imprimir-footer">
        <div class="chamados-imprimir-footer-info">
            <div class="chamados-imprimir-footer-logo">
                <?php if (!empty($empresa['logo'])): ?>
                    <img src="<?= base_url('uploads/logos/' . $empresa['logo']) ?>" alt="Logo" class="chamados-imprimir-footer-logo-img">
                <?php else: ?>
                    <i class="fas fa-ticket-alt"></i>
                <?php endif; ?>
            </div>
            <div class="chamados-imprimir-footer-text">
                <p class="chamados-imprimir-footer-empresa"><?= htmlspecialchars($empresa['nome'] ?? 'Sistema de Gestão de Chamados') ?></p>
                <p class="chamados-imprimir-footer-copyright">Todos os direitos reservados &copy; <?= date('Y') ?></p>
            </div>
        </div>
        <div class="chamados-imprimir-footer-timestamp">
            <p>Documento gerado em <?= date('d/m/Y H:i:s') ?></p>
            <p class="chamados-imprimir-page-number"></p>
        </div>
    </div>

    <!-- Marca d'água para impressão -->
    <div class="chamados-imprimir-watermark"></div>
</div>

<!-- Modal de Compartilhamento -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shareModalLabel">Compartilhar Chamado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="shareLink" class="form-label">Link do Chamado</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="shareLink" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="copyLinkBtn">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" id="emailShareBtn">
                        <i class="fas fa-envelope"></i> Enviar por E-mail
                    </button>
                    <button class="btn btn-success" id="whatsappShareBtn">
                        <i class="fab fa-whatsapp"></i> Compartilhar via WhatsApp
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>