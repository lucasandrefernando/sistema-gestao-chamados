<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chamado #<?= $chamado['id'] ?> - Impressão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .logo {
            max-height: 80px;
            margin-bottom: 10px;
        }

        .chamado-info {
            margin-bottom: 30px;
        }

        .section-title {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #0d6efd;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                padding: 0;
            }

            .container {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <?php if (!empty($empresa['logo'])): ?>
                <img src="<?= base_url('uploads/logos/' . $empresa['logo']) ?>" alt="Logo" class="logo">
            <?php endif; ?>
            <h2><?= htmlspecialchars($empresa['nome'] ?? 'Sistema de Gestão de Chamados') ?></h2>
            <p>Relatório de Chamado #<?= $chamado['id'] ?></p>
        </div>

        <div class="no-print mb-4">
            <button class="btn btn-primary" onclick="window.print()">Imprimir</button>
            <a href="<?= base_url('chamados/visualizar/' . $chamado['id']) ?>" class="btn btn-secondary">Voltar</a>
        </div>

        <div class="chamado-info">
            <h4 class="section-title">Informações do Chamado</h4>
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Número do Chamado:</th>
                            <td><?= $chamado['id'] ?></td>
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
                            <td><?= htmlspecialchars($status['nome']) ?></td>
                        </tr>
                        <tr>
                            <th>Tipo de Serviço:</th>
                            <td><?= htmlspecialchars($chamado['tipo_servico'] ?? 'Não especificado') ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Data de Solicitação:</th>
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

        <div class="descricao mb-4">
            <h4 class="section-title">Descrição do Chamado</h4>
            <div class="p-3 bg-light rounded">
                <?= nl2br(htmlspecialchars($chamado['descricao'])) ?>
            </div>
        </div>

        <?php if (!empty($comentarios)): ?>
            <div class="comentarios mb-4">
                <h4 class="section-title">Comentários</h4>
                <?php foreach ($comentarios as $comentario): ?>
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between">
                            <span><?= htmlspecialchars($comentario['usuario_nome'] ?? 'Usuário') ?></span>
                            <span><?= formatarData($comentario['data_criacao']) ?></span>
                        </div>
                        <div class="card-body">
                            <?= nl2br(htmlspecialchars($comentario['comentario'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($historico)): ?>
            <div class="historico mb-4">
                <h4 class="section-title">Histórico do Chamado</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Alteração</th>
                            <th>Observação</th>
                            <th>Usuário</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historico as $item): ?>
                            <tr>
                                <td><?= formatarData($item['data_criacao']) ?></td>
                                <td>
                                    <?php if ($item['status_id_anterior'] != $item['status_id_novo']): ?>
                                        Status alterado de
                                        <strong><?= htmlspecialchars($item['status_anterior_nome']) ?></strong>
                                        para
                                        <strong><?= htmlspecialchars($item['status_novo_nome']) ?></strong>
                                    <?php endif; ?>

                                    <?php if ($item['setor_id_anterior'] != $item['setor_id_novo']): ?>
                                        <?= $item['status_id_anterior'] != $item['status_id_novo'] ? '<br>' : '' ?>
                                        Transferido do setor
                                        <strong><?= htmlspecialchars($item['setor_anterior_nome']) ?></strong>
                                        para
                                        <strong><?= htmlspecialchars($item['setor_novo_nome']) ?></strong>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item['observacao'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($item['usuario_nome'] ?? 'Sistema') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="footer">
            <p>Documento gerado em <?= date('d/m/Y H:i:s') ?></p>
            <p><?= htmlspecialchars($empresa['nome'] ?? 'Sistema de Gestão de Chamados') ?> - Todos os direitos reservados</p>
        </div>
    </div>
</body>

</html>