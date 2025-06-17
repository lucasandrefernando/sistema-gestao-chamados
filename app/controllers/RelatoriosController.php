<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Chamado.php';
require_once ROOT_DIR . '/app/models/Setor.php';
require_once ROOT_DIR . '/app/models/StatusChamado.php';



/**
 * Controlador para geração de relatórios
 */
class RelatoriosController extends Controller
{
    private $chamadoModel;
    private $setorModel;
    private $statusModel;

    /**
     * Construtor
     */
    public function __construct()
    {
        // Se não estiver autenticado, redireciona para o login
        if (function_exists('is_authenticated') && !is_authenticated()) {
            if (function_exists('redirect')) {
                redirect('auth');
            } else {
                header('Location: /auth');
            }
            exit;
        }

        // Inicializa os modelos
        $this->chamadoModel = new Chamado();
        $this->setorModel = new Setor();
        $this->statusModel = new StatusChamado();
    }

    /**
     * Exporta o relatório de chamados para PDF usando MPDF
     */
    public function exportarChamadosPDF()
    {
        // Habilita a exibição de erros para depuração
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        // Log para verificar se o método está sendo chamado
        error_log('Método exportarChamadosPDF() chamado em ' . date('Y-m-d H:i:s'));
        error_log('GET params: ' . print_r($_GET, true));

        try {
            // Obtém o ID da empresa
            $empresaId = function_exists('get_empresa_id') ? get_empresa_id() : 1;

            // Obtém os filtros da URL
            $anoFiltro = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');
            $mesFiltro = isset($_GET['mes']) && $_GET['mes'] !== '' ? (int)$_GET['mes'] : null;
            $setorFiltro = isset($_GET['setor']) && $_GET['setor'] !== '' ? $_GET['setor'] : null;
            $statusFiltro = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
            $tipoServicoFiltro = isset($_GET['tipo_servico']) && $_GET['tipo_servico'] !== '' ? $_GET['tipo_servico'] : null;
            $solicitanteFiltro = isset($_GET['solicitante']) && $_GET['solicitante'] !== '' ? $_GET['solicitante'] : null;
            $dataInicioFiltro = isset($_GET['data_inicio']) && $_GET['data_inicio'] !== '' ? $_GET['data_inicio'] : null;
            $dataFimFiltro = isset($_GET['data_fim']) && $_GET['data_fim'] !== '' ? $_GET['data_fim'] : null;

            // Obtém os setores e status
            $setores = $this->setorModel->findAll('empresa_id = :empresa_id AND ativo = 1', ['empresa_id' => $empresaId], 'nome ASC');
            $statusList = $this->statusModel->findAll(null, null, 'nome ASC');

            // Aplica os filtros básicos (ano, mês, setor)
            $condicaoBase = "empresa_id = :empresa_id";
            $paramsBase = ['empresa_id' => $empresaId];

            if ($anoFiltro) {
                $condicaoBase .= " AND YEAR(data_solicitacao) = :ano";
                $paramsBase['ano'] = $anoFiltro;
            }

            if ($mesFiltro) {
                $condicaoBase .= " AND MONTH(data_solicitacao) = :mes";
                $paramsBase['mes'] = $mesFiltro;
            }

            if ($setorFiltro) {
                $condicaoBase .= " AND setor_id = :setor_id";
                $paramsBase['setor_id'] = $setorFiltro;
            }

            // Aplica os filtros adicionais
            if ($statusFiltro) {
                $condicaoBase .= " AND status_id = :status_id";
                $paramsBase['status_id'] = $statusFiltro;
            }

            if ($tipoServicoFiltro) {
                $condicaoBase .= " AND tipo_servico = :tipo_servico";
                $paramsBase['tipo_servico'] = $tipoServicoFiltro;
            }

            if ($solicitanteFiltro) {
                $condicaoBase .= " AND solicitante = :solicitante";
                $paramsBase['solicitante'] = $solicitanteFiltro;
            }

            if ($dataInicioFiltro) {
                $condicaoBase .= " AND data_solicitacao >= :data_inicio";
                $paramsBase['data_inicio'] = $dataInicioFiltro . ' 00:00:00';
            }

            if ($dataFimFiltro) {
                $condicaoBase .= " AND data_solicitacao <= :data_fim";
                $paramsBase['data_fim'] = $dataFimFiltro . ' 23:59:59';
            }

            // Obtém os dados para o relatório
            $chamadosPorStatus = $this->chamadoModel->getChamadosPorStatusRelatorio($empresaId, $anoFiltro, $mesFiltro, $setorFiltro, $condicaoBase, $paramsBase);
            $chamadosPorSetor = $this->chamadoModel->getChamadosPorSetorRelatorio($empresaId, $anoFiltro, $mesFiltro, $condicaoBase, $paramsBase);
            $chamadosPorTipoServico = $this->chamadoModel->getChamadosPorTipoServicoRelatorio($empresaId, $anoFiltro, $mesFiltro, $setorFiltro, $condicaoBase, $paramsBase);
            $tempoMedioAtendimento = $this->chamadoModel->getTempoMedioAtendimento($empresaId, $anoFiltro, $mesFiltro, $setorFiltro, $condicaoBase, $paramsBase);
            $estatisticasGerais = $this->chamadoModel->getEstatisticasGerais($empresaId, $anoFiltro, $mesFiltro, $setorFiltro, $condicaoBase, $paramsBase);

            // Obtém a lista de chamados para incluir no relatório
            $chamados = $this->chamadoModel->findAll($condicaoBase, $paramsBase, 'data_solicitacao DESC', 100);

            // Prepara o texto dos filtros
            $filtrosTexto = $this->prepararTextoFiltros($anoFiltro, $mesFiltro, $setorFiltro, $statusFiltro, $tipoServicoFiltro, $solicitanteFiltro, $dataInicioFiltro, $dataFimFiltro, $setores, $statusList);

            // Gera o PDF usando MPDF
            $this->gerarPDFComMPDF(
                "Sistema de Gestão de Chamados",
                $filtrosTexto,
                $estatisticasGerais,
                $chamadosPorStatus,
                $chamadosPorSetor,
                $chamadosPorTipoServico,
                $tempoMedioAtendimento,
                $chamados,
                $setores,
                $statusList
            );
        } catch (Exception $e) {
            // Log do erro
            error_log('Erro ao exportar relatório: ' . $e->getMessage());
            error_log('Trace: ' . $e->getTraceAsString());

            // Mensagem para o usuário
            echo '<h1>Erro ao exportar relatório</h1>';
            echo '<p>' . $e->getMessage() . '</p>';
            echo '<p><a href="javascript:history.back()">Voltar</a></p>';
            exit;
        }
    }

    /**
     * Prepara o texto dos filtros aplicados para exibição no PDF
     */
    private function prepararTextoFiltros($ano, $mes, $setorId, $statusId, $tipoServico, $solicitante, $dataInicio, $dataFim, $setores, $statusList)
    {
        $filtros = [];

        // Ano
        if ($ano && $ano != date('Y')) {
            $filtros[] = "Ano: $ano";
        }

        // Mês
        if ($mes) {
            $meses = [
                1 => 'Janeiro',
                2 => 'Fevereiro',
                3 => 'Março',
                4 => 'Abril',
                5 => 'Maio',
                6 => 'Junho',
                7 => 'Julho',
                8 => 'Agosto',
                9 => 'Setembro',
                10 => 'Outubro',
                11 => 'Novembro',
                12 => 'Dezembro'
            ];
            $filtros[] = "Mês: " . $meses[$mes];
        }

        // Setor
        if ($setorId) {
            $setorNome = '';
            foreach ($setores as $setor) {
                if ($setor['id'] == $setorId) {
                    $setorNome = $setor['nome'];
                    break;
                }
            }
            $filtros[] = "Setor: $setorNome";
        }

        // Status
        if ($statusId) {
            $statusNome = '';
            foreach ($statusList as $status) {
                if ($status['id'] == $statusId) {
                    $statusNome = $status['nome'];
                    break;
                }
            }
            $filtros[] = "Status: $statusNome";
        }

        // Tipo de Serviço
        if ($tipoServico) {
            $filtros[] = "Tipo de Serviço: $tipoServico";
        }

        // Solicitante
        if ($solicitante) {
            $filtros[] = "Solicitante: $solicitante";
        }

        // Data Início
        if ($dataInicio) {
            $filtros[] = "Data Inicial: " . date('d/m/Y', strtotime($dataInicio));
        }

        // Data Fim
        if ($dataFim) {
            $filtros[] = "Data Final: " . date('d/m/Y', strtotime($dataFim));
        }

        return !empty($filtros) ? implode(' | ', $filtros) : 'Nenhum filtro aplicado';
    }

    /**
     * Gera o PDF do relatório usando MPDF
     */
    private function gerarPDFComMPDF($nomeEmpresa, $filtrosTexto, $estatisticasGerais, $chamadosPorStatus, $chamadosPorSetor, $chamadosPorTipoServico, $tempoMedioAtendimento, $chamados, $setores, $statusList)
    {
        // Log para verificar se o método está sendo chamado
        error_log('Método gerarPDFComMPDF() chamado em ' . date('Y-m-d H:i:s'));

        try {
            // Carrega a biblioteca MPDF via Composer
            require_once ROOT_DIR . '/vendor/autoload.php';

            // Verifica se o MPDF está instalado
            if (!class_exists('Mpdf\Mpdf')) {
                echo "A biblioteca MPDF não está instalada. Por favor, instale-a usando o Composer.";
                exit;
            }

            // Cria uma nova instância de MPDF
            $mpdf = new \Mpdf\Mpdf([
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 15,
                'margin_bottom' => 15,
            ]);

            // Define o título do documento
            $mpdf->SetTitle('Relatório de Chamados');

            // Conteúdo do PDF em HTML
            $html = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Relatório de Chamados</title>
                <style>
                    body { font-family: Arial, sans-serif; font-size: 10pt; }
                    h1 { font-size: 16pt; text-align: center; margin-bottom: 5pt; }
                    h2 { font-size: 12pt; margin-top: 15pt; margin-bottom: 5pt; }
                    .empresa { font-size: 12pt; text-align: center; margin-bottom: 5pt; }
                    .data { font-size: 10pt; text-align: center; font-style: italic; margin-bottom: 15pt; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 15pt; }
                    th { background-color: #f0f0f0; font-weight: bold; text-align: left; padding: 5pt; }
                    td { padding: 5pt; }
                    th, td { border: 1px solid #ddd; }
                    .center { text-align: center; }
                    .right { text-align: right; }
                    .footer { font-size: 8pt; text-align: center; font-style: italic; margin-top: 20pt; }
                </style>
            </head>
            <body>
                <h1>Relatório de Chamados</h1>
                <div class="empresa">' . $nomeEmpresa . '</div>
                <div class="data">Gerado em: ' . date('d/m/Y H:i:s') . '</div>
                
                <h2>Filtros aplicados</h2>
                <p>' . $filtrosTexto . '</p>
                
                <h2>Estatísticas Gerais</h2>
                <table>
                    <tr>
                        <th>Total de Chamados</th>
                        <td class="center">' . $estatisticasGerais['total'] . '</td>
                        <th>Concluídos</th>
                        <td class="center">' . $estatisticasGerais['concluidos'] . ' (' . $estatisticasGerais['taxa_conclusao'] . '%)</td>
                    </tr>
                    <tr>
                        <th>Em Andamento</th>
                        <td class="center">' . $estatisticasGerais['em_andamento'] . '</td>
                        <th>Abertos</th>
                        <td class="center">' . $estatisticasGerais['abertos'] . '</td>
                    </tr>
                </table>';

            // Chamados por Status
            $html .= '
                <h2>Chamados por Status</h2>
                <table>
                    <tr>
                        <th>Status</th>
                        <th class="center">Quantidade</th>
                        <th class="center">Percentual</th>
                    </tr>';

            $totalChamados = array_sum($chamadosPorStatus['data'] ?? []);
            foreach ($chamadosPorStatus['raw'] ?? [] as $row) {
                $percentual = $totalChamados > 0 ? ($row['total'] / $totalChamados) * 100 : 0;

                $html .= '
                    <tr>
                        <td>' . $row['status_nome'] . '</td>
                        <td class="center">' . $row['total'] . '</td>
                        <td class="center">' . number_format($percentual, 1) . '%</td>
                    </tr>';
            }

            $html .= '</table>';

            // Chamados por Setor
            $html .= '
                <h2>Chamados por Setor</h2>
                <table>
                    <tr>
                        <th>Setor</th>
                        <th class="center">Quantidade</th>
                        <th class="center">Percentual</th>
                    </tr>';

            $totalChamadosSetor = array_sum($chamadosPorSetor['data'] ?? []);
            foreach ($chamadosPorSetor['raw'] ?? [] as $row) {
                $percentual = $totalChamadosSetor > 0 ? ($row['total'] / $totalChamadosSetor) * 100 : 0;

                $html .= '
                    <tr>
                        <td>' . $row['setor_nome'] . '</td>
                        <td class="center">' . $row['total'] . '</td>
                        <td class="center">' . number_format($percentual, 1) . '%</td>
                    </tr>';
            }

            $html .= '</table>';

            // Chamados por Tipo de Serviço (Top 10)
            $html .= '
                <h2>Top 10 Tipos de Serviço</h2>
                <table>
                    <tr>
                        <th>Tipo de Serviço</th>
                        <th class="center">Quantidade</th>
                        <th class="center">Percentual</th>
                    </tr>';

            $totalChamadosTipo = array_sum($chamadosPorTipoServico['data'] ?? []);
            $contador = 0;
            foreach ($chamadosPorTipoServico['raw'] ?? [] as $row) {
                if ($contador >= 10) break; // Limita aos 10 primeiros

                $percentual = $totalChamadosTipo > 0 ? ($row['total'] / $totalChamadosTipo) * 100 : 0;

                $html .= '
                    <tr>
                        <td>' . $row['tipo_servico'] . '</td>
                        <td class="center">' . $row['total'] . '</td>
                        <td class="center">' . number_format($percentual, 1) . '%</td>
                    </tr>';

                $contador++;
            }

            $html .= '</table>';

            // Lista dos últimos chamados
            $html .= '
                <h2>Últimos Chamados (' . count($chamados) . ')</h2>
                <table>
                    <tr>
                        <th class="center">ID</th>
                        <th class="center">Data</th>
                        <th>Solicitante</th>
                        <th>Setor</th>
                        <th class="center">Status</th>
                        <th>Tipo</th>
                    </tr>';

            foreach ($chamados as $chamado) {
                // Obtém o nome do setor
                $setorNome = '';
                foreach ($setores as $setor) {
                    if ($setor['id'] == $chamado['setor_id']) {
                        $setorNome = $setor['nome'];
                        break;
                    }
                }

                // Obtém o nome do status
                $statusNome = '';
                foreach ($statusList as $status) {
                    if ($status['id'] == $chamado['status_id']) {
                        $statusNome = $status['nome'];
                        break;
                    }
                }

                $html .= '
                    <tr>
                        <td class="center">' . $chamado['id'] . '</td>
                        <td class="center">' . date('d/m/Y H:i', strtotime($chamado['data_solicitacao'])) . '</td>
                        <td>' . $chamado['solicitante'] . '</td>
                        <td>' . $setorNome . '</td>
                        <td class="center">' . $statusNome . '</td>
                        <td>' . $chamado['tipo_servico'] . '</td>
                    </tr>';
            }

            $html .= '</table>';

            // Rodapé
            $html .= '
                <div class="footer">Relatório gerado pelo sistema de chamados - ' . date('d/m/Y H:i:s') . '</div>
            </body>
            </html>';

            // Adiciona o conteúdo HTML ao PDF
            $mpdf->WriteHTML($html);

            // Saída do PDF
            $mpdf->Output('Relatorio_Chamados_' . date('Y-m-d') . '.pdf', 'D');
            exit;
        } catch (Exception $e) {
            error_log('Erro ao gerar PDF com MPDF: ' . $e->getMessage());
            error_log('Trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    // Método para testar a geração de PDF simples usando MPDF
    public function testarPDF()
    {
        try {
            // Carrega a biblioteca MPDF via Composer
            require_once ROOT_DIR . '/vendor/autoload.php';

            // Verifica se o MPDF está instalado
            if (!class_exists('Mpdf\Mpdf')) {
                echo "A biblioteca MPDF não está instalada. Por favor, instale-a usando o Composer.";
                exit;
            }

            // Cria uma nova instância de MPDF com configurações básicas
            $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);

            // Define o título do documento
            $mpdf->SetTitle('Teste de PDF');

            // Conteúdo do PDF em HTML
            $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Teste de PDF</title>
            <style>
                body { font-family: Arial, sans-serif; }
                h1 { color: #333; text-align: center; }
                p { margin-bottom: 10px; }
                .date { font-style: italic; text-align: center; }
            </style>
        </head>
        <body>
            <h1>Teste de Geração de PDF</h1>
            <p class="date">Gerado em: ' . date('d/m/Y H:i:s') . '</p>
            <p>Este é um teste para verificar se a geração de PDF está funcionando corretamente usando a biblioteca MPDF.</p>
            <p>Se você está vendo este documento, a geração de PDF está funcionando!</p>
        </body>
        </html>';

            // Adiciona o conteúdo HTML ao PDF
            $mpdf->WriteHTML($html);

            // Saída do PDF
            $mpdf->Output('Teste_PDF_' . date('Y-m-d') . '.pdf', 'D');
            exit;
        } catch (Exception $e) {
            echo '<h1>Erro ao gerar PDF de teste</h1>';
            echo '<p>' . $e->getMessage() . '</p>';
            exit;
        }
    }

    /**
     * Gera o PDF do relatório usando DOMPDF
     */
    private function gerarPDFComDOMPDF($nomeEmpresa, $filtrosTexto, $estatisticasGerais, $chamadosPorStatus, $chamadosPorSetor, $chamadosPorTipoServico, $tempoMedioAtendimento, $chamados, $setores, $statusList)
    {
        // Log para verificar se o método está sendo chamado
        error_log('Método gerarPDFComDOMPDF() chamado em ' . date('Y-m-d H:i:s'));

        try {
            // Carrega a biblioteca DOMPDF via Composer
            require_once ROOT_DIR . '/vendor/autoload.php';

            // Verifica se o DOMPDF está instalado
            if (!class_exists('Dompdf\Dompdf')) {
                echo "A biblioteca DOMPDF não está instalada. Por favor, instale-a usando o Composer.";
                exit;
            }

            // Conteúdo do PDF em HTML (mesmo HTML usado no MPDF)
            $html = '...'; // Mesmo HTML do método gerarPDFComMPDF

            // Cria uma nova instância de DOMPDF
            $dompdf = new \Dompdf\Dompdf();

            // Configurações
            $dompdf->setPaper('A4', 'portrait');

            // Carrega o HTML
            $dompdf->loadHtml($html);

            // Renderiza o PDF
            $dompdf->render();

            // Saída do PDF
            $dompdf->stream('Relatorio_Chamados_' . date('Y-m-d') . '.pdf', ['Attachment' => true]);
            exit;
        } catch (Exception $e) {
            error_log('Erro ao gerar PDF com DOMPDF: ' . $e->getMessage());
            error_log('Trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Gera o PDF do relatório usando FPDF
     */
    private function gerarPDFComFPDF($nomeEmpresa, $filtrosTexto, $estatisticasGerais, $chamadosPorStatus, $chamadosPorSetor, $chamadosPorTipoServico, $tempoMedioAtendimento, $chamados, $setores, $statusList)
    {
        // Log para verificar se o método está sendo chamado
        error_log('Método gerarPDFComFPDF() chamado em ' . date('Y-m-d H:i:s'));

        try {
            // Carrega a biblioteca FPDF via Composer
            require_once ROOT_DIR . '/vendor/autoload.php';

            // Verifica se o FPDF está instalado
            if (!class_exists('FPDF')) {
                echo "A biblioteca FPDF não está instalada. Por favor, instale-a usando o Composer.";
                exit;
            }

            // Cria uma nova instância de FPDF
            $pdf = new \FPDF('P', 'mm', 'A4');

            // Adiciona uma página
            $pdf->AddPage();

            // Define a fonte
            $pdf->SetFont('Arial', 'B', 16);

            // Título
            $pdf->Cell(0, 10, 'Relatorio de Chamados', 0, 1, 'C');

            // Nome da empresa
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, $nomeEmpresa, 0, 1, 'C');

            // Data de geração
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->Cell(0, 5, 'Gerado em: ' . date('d/m/Y H:i:s'), 0, 1, 'C');

            // Filtros aplicados
            $pdf->Ln(5);
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(0, 5, 'Filtros aplicados:', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 10);
            $pdf->MultiCell(0, 5, $filtrosTexto, 0, 'L');

            // Linha divisória
            $pdf->Ln(5);
            $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
            $pdf->Ln(5);

            // Estatísticas Gerais
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'Estatisticas Gerais', 0, 1, 'L');

            $pdf->SetFont('Arial', '', 10);

            // Tabela de estatísticas
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(45, 7, 'Total de Chamados', 1, 0, 'L', true);
            $pdf->Cell(25, 7, $estatisticasGerais['total'], 1, 0, 'C');
            $pdf->Cell(45, 7, 'Concluidos', 1, 0, 'L', true);
            $pdf->Cell(25, 7, $estatisticasGerais['concluidos'] . ' (' . $estatisticasGerais['taxa_conclusao'] . '%)', 1, 0, 'C');
            $pdf->Cell(45, 7, 'Em Andamento', 1, 0, 'L', true);
            $pdf->Cell(25, 7, $estatisticasGerais['em_andamento'], 1, 1, 'C');

            $pdf->Cell(45, 7, 'Abertos', 1, 0, 'L', true);
            $pdf->Cell(25, 7, $estatisticasGerais['abertos'], 1, 0, 'C');
            $pdf->Cell(45, 7, 'Tempo Medio', 1, 0, 'L', true);

            // Formata o tempo médio
            $horas = $estatisticasGerais['tempo_medio'];
            if ($horas < 24) {
                $tempoFormatado = number_format($horas, 1) . ' horas';
            } else {
                $dias = floor($horas / 24);
                $horasRestantes = number_format(fmod($horas, 24), 1);
                $tempoFormatado = $dias . 'd ' . $horasRestantes . 'h';
            }

            $pdf->Cell(25, 7, $tempoFormatado, 1, 0, 'C');
            $pdf->Cell(45, 7, 'Chamados por Dia', 1, 0, 'L', true);
            $pdf->Cell(25, 7, round($estatisticasGerais['total'] / max(1, $estatisticasGerais['dias_periodo']), 1), 1, 1, 'C');

            $pdf->Ln(5);

            // Chamados por Status
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'Chamados por Status', 0, 1, 'L');

            $pdf->SetFont('Arial', '', 10);

            // Cabeçalho da tabela
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(120, 7, 'Status', 1, 0, 'L', true);
            $pdf->Cell(30, 7, 'Quantidade', 1, 0, 'C', true);
            $pdf->Cell(30, 7, 'Percentual', 1, 1, 'C', true);

            // Dados da tabela
            $totalChamados = array_sum($chamadosPorStatus['data'] ?? []);
            foreach ($chamadosPorStatus['raw'] ?? [] as $row) {
                $percentual = $totalChamados > 0 ? ($row['total'] / $totalChamados) * 100 : 0;

                $pdf->Cell(120, 7, $row['status_nome'], 1, 0, 'L');
                $pdf->Cell(30, 7, $row['total'], 1, 0, 'C');
                $pdf->Cell(30, 7, number_format($percentual, 1) . '%', 1, 1, 'C');
            }

            $pdf->Ln(5);

            // Chamados por Setor
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'Chamados por Setor', 0, 1, 'L');

            $pdf->SetFont('Arial', '', 10);

            // Cabeçalho da tabela
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(120, 7, 'Setor', 1, 0, 'L', true);
            $pdf->Cell(30, 7, 'Quantidade', 1, 0, 'C', true);
            $pdf->Cell(30, 7, 'Percentual', 1, 1, 'C', true);

            // Dados da tabela
            $totalChamadosSetor = array_sum($chamadosPorSetor['data'] ?? []);
            foreach ($chamadosPorSetor['raw'] ?? [] as $row) {
                $percentual = $totalChamadosSetor > 0 ? ($row['total'] / $totalChamadosSetor) * 100 : 0;

                $pdf->Cell(120, 7, $row['setor_nome'], 1, 0, 'L');
                $pdf->Cell(30, 7, $row['total'], 1, 0, 'C');
                $pdf->Cell(30, 7, number_format($percentual, 1) . '%', 1, 1, 'C');
            }

            $pdf->Ln(5);

            // Chamados por Tipo de Serviço (Top 10)
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'Top 10 Tipos de Servico', 0, 1, 'L');

            $pdf->SetFont('Arial', '', 10);

            // Cabeçalho da tabela
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(120, 7, 'Tipo de Servico', 1, 0, 'L', true);
            $pdf->Cell(30, 7, 'Quantidade', 1, 0, 'C', true);
            $pdf->Cell(30, 7, 'Percentual', 1, 1, 'C', true);

            // Dados da tabela (limitados aos 10 primeiros)
            $totalChamadosTipo = array_sum($chamadosPorTipoServico['data'] ?? []);
            $contador = 0;
            foreach ($chamadosPorTipoServico['raw'] ?? [] as $row) {
                if ($contador >= 10) break; // Limita aos 10 primeiros

                $percentual = $totalChamadosTipo > 0 ? ($row['total'] / $totalChamadosTipo) * 100 : 0;

                $pdf->Cell(120, 7, $row['tipo_servico'], 1, 0, 'L');
                $pdf->Cell(30, 7, $row['total'], 1, 0, 'C');
                $pdf->Cell(30, 7, number_format($percentual, 1) . '%', 1, 1, 'C');

                $contador++;
            }

            $pdf->Ln(5);

            // Lista dos últimos chamados
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, 'Ultimos Chamados (' . count($chamados) . ')', 0, 1, 'L');

            $pdf->SetFont('Arial', '', 9);

            // Cabeçalho da tabela
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(15, 7, 'ID', 1, 0, 'C', true);
            $pdf->Cell(30, 7, 'Data', 1, 0, 'C', true);
            $pdf->Cell(40, 7, 'Solicitante', 1, 0, 'L', true);
            $pdf->Cell(35, 7, 'Setor', 1, 0, 'L', true);
            $pdf->Cell(25, 7, 'Status', 1, 0, 'C', true);
            $pdf->Cell(40, 7, 'Tipo', 1, 1, 'L', true);

            // Dados da tabela
            foreach ($chamados as $chamado) {
                // Verifica se precisa adicionar uma nova página
                if ($pdf->GetY() > 250) {
                    $pdf->AddPage();

                    // Repete o cabeçalho da tabela
                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->Cell(0, 10, 'Ultimos Chamados (continuacao)', 0, 1, 'L');

                    $pdf->SetFont('Arial', '', 9);
                    $pdf->SetFillColor(240, 240, 240);
                    $pdf->Cell(15, 7, 'ID', 1, 0, 'C', true);
                    $pdf->Cell(30, 7, 'Data', 1, 0, 'C', true);
                    $pdf->Cell(40, 7, 'Solicitante', 1, 0, 'L', true);
                    $pdf->Cell(35, 7, 'Setor', 1, 0, 'L', true);
                    $pdf->Cell(25, 7, 'Status', 1, 0, 'C', true);
                    $pdf->Cell(40, 7, 'Tipo', 1, 1, 'L', true);
                }

                // Obtém o nome do setor
                $setorNome = '';
                foreach ($setores as $setor) {
                    if ($setor['id'] == $chamado['setor_id']) {
                        $setorNome = $setor['nome'];
                        break;
                    }
                }

                // Obtém o nome do status
                $statusNome = '';
                foreach ($statusList as $status) {
                    if ($status['id'] == $chamado['status_id']) {
                        $statusNome = $status['nome'];
                        break;
                    }
                }

                $pdf->Cell(15, 7, $chamado['id'], 1, 0, 'C');
                $pdf->Cell(30, 7, date('d/m/Y H:i', strtotime($chamado['data_solicitacao'])), 1, 0, 'C');
                $pdf->Cell(40, 7, utf8_decode(substr($chamado['solicitante'], 0, 20)), 1, 0, 'L');
                $pdf->Cell(35, 7, utf8_decode(substr($setorNome, 0, 15)), 1, 0, 'L');
                $pdf->Cell(25, 7, utf8_decode(substr($statusNome, 0, 10)), 1, 0, 'C');
                $pdf->Cell(40, 7, utf8_decode(substr($chamado['tipo_servico'], 0, 20)), 1, 1, 'L');
            }

            // Rodapé
            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->Cell(0, 5, 'Relatorio gerado pelo sistema de chamados - ' . date('d/m/Y H:i:s'), 0, 1, 'C');

            // Saída do PDF
            $pdf->Output('D', 'Relatorio_Chamados_' . date('Y-m-d') . '.pdf');
            exit;
        } catch (Exception $e) {
            error_log('Erro ao gerar PDF com FPDF: ' . $e->getMessage());
            error_log('Trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Gera o PDF do relatório usando TCPDF
     */
    private function gerarPDFComTCPDF($nomeEmpresa, $filtrosTexto, $estatisticasGerais, $chamadosPorStatus, $chamadosPorSetor, $chamadosPorTipoServico, $tempoMedioAtendimento, $chamados, $setores, $statusList)
    {
        // Log para verificar se o método está sendo chamado
        error_log('Método gerarPDFComTCPDF() chamado em ' . date('Y-m-d H:i:s'));

        try {
            // Carrega a biblioteca TCPDF via Composer
            require_once ROOT_DIR . '/vendor/autoload.php';

            // Verifica se o TCPDF está instalado
            if (!class_exists('TCPDF')) {
                echo "A biblioteca TCPDF não está instalada. Por favor, instale-a usando o Composer.";
                exit;
            }

            // Cria uma nova instância de TCPDF
            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

            // Define informações do documento
            $pdf->SetCreator('Sistema de Gestão de Chamados');
            $pdf->SetAuthor($nomeEmpresa);
            $pdf->SetTitle('Relatório de Chamados');
            $pdf->SetSubject('Relatório de Chamados');
            $pdf->SetKeywords('Chamados, Relatório, Suporte, Atendimento');

            // Remove cabeçalho e rodapé padrão
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Define margens
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);

            // Define fonte padrão
            $pdf->SetFont('helvetica', '', 10);

            // Adiciona uma página
            $pdf->AddPage();

            // Cabeçalho do relatório
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'Relatório de Chamados', 0, 1, 'C');

            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, $nomeEmpresa, 0, 1, 'C');

            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 5, 'Gerado em: ' . date('d/m/Y H:i:s'), 0, 1, 'C');

            // Filtros aplicados
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 5, 'Filtros aplicados:', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 10);
            $pdf->MultiCell(0, 5, $filtrosTexto, 0, 'L');

            // Linha divisória
            $pdf->Ln(5);
            $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
            $pdf->Ln(5);

            // Estatísticas Gerais
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Estatísticas Gerais', 0, 1, 'L');

            $pdf->SetFont('helvetica', '', 10);

            // Tabela de estatísticas
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(45, 7, 'Total de Chamados', 1, 0, 'L', true);
            $pdf->Cell(25, 7, $estatisticasGerais['total'], 1, 0, 'C');
            $pdf->Cell(45, 7, 'Concluídos', 1, 0, 'L', true);
            $pdf->Cell(25, 7, $estatisticasGerais['concluidos'] . ' (' . $estatisticasGerais['taxa_conclusao'] . '%)', 1, 0, 'C');
            $pdf->Cell(45, 7, 'Em Andamento', 1, 0, 'L', true);
            $pdf->Cell(25, 7, $estatisticasGerais['em_andamento'], 1, 1, 'C');

            $pdf->Cell(45, 7, 'Abertos', 1, 0, 'L', true);
            $pdf->Cell(25, 7, $estatisticasGerais['abertos'], 1, 0, 'C');
            $pdf->Cell(45, 7, 'Tempo Médio', 1, 0, 'L', true);

            // Formata o tempo médio
            $horas = $estatisticasGerais['tempo_medio'];
            if ($horas < 24) {
                $tempoFormatado = number_format($horas, 1) . ' horas';
            } else {
                $dias = floor($horas / 24);
                $horasRestantes = number_format(fmod($horas, 24), 1);
                $tempoFormatado = $dias . 'd ' . $horasRestantes . 'h';
            }

            $pdf->Cell(25, 7, $tempoFormatado, 1, 0, 'C');
            $pdf->Cell(45, 7, 'Chamados por Dia', 1, 0, 'L', true);
            $pdf->Cell(25, 7, round($estatisticasGerais['total'] / max(1, $estatisticasGerais['dias_periodo']), 1), 1, 1, 'C');

            $pdf->Ln(5);

            // Chamados por Status
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Chamados por Status', 0, 1, 'L');

            $pdf->SetFont('helvetica', '', 10);

            // Cabeçalho da tabela
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(120, 7, 'Status', 1, 0, 'L', true);
            $pdf->Cell(30, 7, 'Quantidade', 1, 0, 'C', true);
            $pdf->Cell(30, 7, 'Percentual', 1, 1, 'C', true);

            // Dados da tabela
            $totalChamados = array_sum($chamadosPorStatus['data'] ?? []);
            foreach ($chamadosPorStatus['raw'] ?? [] as $row) {
                $percentual = $totalChamados > 0 ? ($row['total'] / $totalChamados) * 100 : 0;

                $pdf->Cell(120, 7, $row['status_nome'], 1, 0, 'L');
                $pdf->Cell(30, 7, $row['total'], 1, 0, 'C');
                $pdf->Cell(30, 7, number_format($percentual, 1) . '%', 1, 1, 'C');
            }

            $pdf->Ln(5);

            // Chamados por Setor
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Chamados por Setor', 0, 1, 'L');

            $pdf->SetFont('helvetica', '', 10);

            // Cabeçalho da tabela
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(120, 7, 'Setor', 1, 0, 'L', true);
            $pdf->Cell(30, 7, 'Quantidade', 1, 0, 'C', true);
            $pdf->Cell(30, 7, 'Percentual', 1, 1, 'C', true);

            // Dados da tabela
            $totalChamadosSetor = array_sum($chamadosPorSetor['data'] ?? []);
            foreach ($chamadosPorSetor['raw'] ?? [] as $row) {
                $percentual = $totalChamadosSetor > 0 ? ($row['total'] / $totalChamadosSetor) * 100 : 0;

                $pdf->Cell(120, 7, $row['setor_nome'], 1, 0, 'L');
                $pdf->Cell(30, 7, $row['total'], 1, 0, 'C');
                $pdf->Cell(30, 7, number_format($percentual, 1) . '%', 1, 1, 'C');
            }

            $pdf->Ln(5);

            // Chamados por Tipo de Serviço (Top 10)
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Top 10 Tipos de Serviço', 0, 1, 'L');

            $pdf->SetFont('helvetica', '', 10);

            // Cabeçalho da tabela
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(120, 7, 'Tipo de Serviço', 1, 0, 'L', true);
            $pdf->Cell(30, 7, 'Quantidade', 1, 0, 'C', true);
            $pdf->Cell(30, 7, 'Percentual', 1, 1, 'C', true);

            // Dados da tabela (limitados aos 10 primeiros)
            $totalChamadosTipo = array_sum($chamadosPorTipoServico['data'] ?? []);
            $contador = 0;
            foreach ($chamadosPorTipoServico['raw'] ?? [] as $row) {
                if ($contador >= 10) break; // Limita aos 10 primeiros

                $percentual = $totalChamadosTipo > 0 ? ($row['total'] / $totalChamadosTipo) * 100 : 0;

                $pdf->Cell(120, 7, $row['tipo_servico'], 1, 0, 'L');
                $pdf->Cell(30, 7, $row['total'], 1, 0, 'C');
                $pdf->Cell(30, 7, number_format($percentual, 1) . '%', 1, 1, 'C');

                $contador++;
            }

            $pdf->Ln(5);

            // Lista dos últimos chamados
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Últimos Chamados (' . count($chamados) . ')', 0, 1, 'L');

            $pdf->SetFont('helvetica', '', 9);

            // Cabeçalho da tabela
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(15, 7, 'ID', 1, 0, 'C', true);
            $pdf->Cell(30, 7, 'Data', 1, 0, 'C', true);
            $pdf->Cell(40, 7, 'Solicitante', 1, 0, 'L', true);
            $pdf->Cell(35, 7, 'Setor', 1, 0, 'L', true);
            $pdf->Cell(25, 7, 'Status', 1, 0, 'C', true);
            $pdf->Cell(40, 7, 'Tipo', 1, 1, 'L', true);

            // Dados da tabela
            foreach ($chamados as $chamado) {
                // Verifica se precisa adicionar uma nova página
                if ($pdf->GetY() > 250) {
                    $pdf->AddPage();

                    // Repete o cabeçalho da tabela
                    $pdf->SetFont('helvetica', 'B', 12);
                    $pdf->Cell(0, 10, 'Últimos Chamados (continuação)', 0, 1, 'L');

                    $pdf->SetFont('helvetica', '', 9);
                    $pdf->SetFillColor(240, 240, 240);
                    $pdf->Cell(15, 7, 'ID', 1, 0, 'C', true);
                    $pdf->Cell(30, 7, 'Data', 1, 0, 'C', true);
                    $pdf->Cell(40, 7, 'Solicitante', 1, 0, 'L', true);
                    $pdf->Cell(35, 7, 'Setor', 1, 0, 'L', true);
                    $pdf->Cell(25, 7, 'Status', 1, 0, 'C', true);
                    $pdf->Cell(40, 7, 'Tipo', 1, 1, 'L', true);
                }

                // Obtém o nome do setor
                $setorNome = '';
                foreach ($setores as $setor) {
                    if ($setor['id'] == $chamado['setor_id']) {
                        $setorNome = $setor['nome'];
                        break;
                    }
                }

                // Obtém o nome do status
                $statusNome = '';
                foreach ($statusList as $status) {
                    if ($status['id'] == $chamado['status_id']) {
                        $statusNome = $status['nome'];
                        break;
                    }
                }

                $pdf->Cell(15, 7, $chamado['id'], 1, 0, 'C');
                $pdf->Cell(30, 7, date('d/m/Y H:i', strtotime($chamado['data_solicitacao'])), 1, 0, 'C');
                $pdf->Cell(40, 7, $chamado['solicitante'], 1, 0, 'L');
                $pdf->Cell(35, 7, $setorNome, 1, 0, 'L');
                $pdf->Cell(25, 7, $statusNome, 1, 0, 'C');
                $pdf->Cell(40, 7, $chamado['tipo_servico'], 1, 1, 'L');
            }

            // Rodapé
            $pdf->Ln(10);
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->Cell(0, 5, 'Relatório gerado pelo sistema de chamados - ' . date('d/m/Y H:i:s'), 0, 1, 'C');

            // Saída do PDF
            $pdf->Output('Relatorio_Chamados_' . date('Y-m-d') . '.pdf', 'D');
            exit;
        } catch (Exception $e) {
            error_log('Erro ao gerar PDF com TCPDF: ' . $e->getMessage());
            error_log('Trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
}
