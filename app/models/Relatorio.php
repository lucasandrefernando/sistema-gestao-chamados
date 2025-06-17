<?php
require_once ROOT_DIR . '/app/models/Model.php';

/**
 * Model para geração de relatórios
 */
class Relatorio extends Model
{
    private $chamadoModel;
    private $setorModel;
    private $statusModel;

    /**
     * Construtor
     */
    public function __construct()
    {
        parent::__construct('chamados'); // Usamos a mesma tabela base dos chamados

        // Inicializa os modelos necessários
        require_once ROOT_DIR . '/app/models/Chamado.php';
        require_once ROOT_DIR . '/app/models/Setor.php';
        require_once ROOT_DIR . '/app/models/StatusChamado.php';

        $this->chamadoModel = new Chamado();
        $this->setorModel = new Setor();
        $this->statusModel = new StatusChamado();
    }

    /**
     * Obtém dados para o relatório de chamados
     * 
     * @param int $empresaId ID da empresa
     * @param array $filtros Filtros para o relatório
     * @return array Dados do relatório
     */
    public function getDadosRelatorioChamados($empresaId, $filtros = [])
    {
        try {
            // Log para depuração
            error_log('Obtendo dados para relatório. Empresa ID: ' . $empresaId);
            error_log('Filtros: ' . print_r($filtros, true));

            // Extrai os filtros
            $anoFiltro = isset($filtros['ano']) ? (int)$filtros['ano'] : (int)date('Y');
            $mesFiltro = isset($filtros['mes']) && $filtros['mes'] !== '' ? (int)$filtros['mes'] : null;
            $setorFiltro = isset($filtros['setor']) && $filtros['setor'] !== '' ? $filtros['setor'] : null;
            $statusFiltro = isset($filtros['status']) && $filtros['status'] !== '' ? $filtros['status'] : null;
            $tipoServicoFiltro = isset($filtros['tipo_servico']) && $filtros['tipo_servico'] !== '' ? $filtros['tipo_servico'] : null;
            $solicitanteFiltro = isset($filtros['solicitante']) && $filtros['solicitante'] !== '' ? $filtros['solicitante'] : null;
            $dataInicioFiltro = isset($filtros['data_inicio']) && $filtros['data_inicio'] !== '' ? $filtros['data_inicio'] : null;
            $dataFimFiltro = isset($filtros['data_fim']) && $filtros['data_fim'] !== '' ? $filtros['data_fim'] : null;

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

            // Log da condição SQL
            error_log('Condição SQL: ' . $condicaoBase);
            error_log('Parâmetros: ' . print_r($paramsBase, true));

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

            // Log dos dados obtidos
            error_log('Dados obtidos com sucesso para o relatório');

            return [
                'nomeEmpresa' => "Sistema de Gestão de Chamados", // Nome padrão da empresa
                'filtrosTexto' => $filtrosTexto,
                'estatisticasGerais' => $estatisticasGerais,
                'chamadosPorStatus' => $chamadosPorStatus,
                'chamadosPorSetor' => $chamadosPorSetor,
                'chamadosPorTipoServico' => $chamadosPorTipoServico,
                'tempoMedioAtendimento' => $tempoMedioAtendimento,
                'chamados' => $chamados,
                'setores' => $setores,
                'statusList' => $statusList
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter dados para relatório: ' . $e->getMessage());
            error_log('Trace: ' . $e->getTraceAsString());
            throw $e;
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
     * Método de teste para verificar a conexão com o banco de dados
     * 
     * @return bool True se a conexão estiver funcionando, False caso contrário
     */
    public function testarConexao()
    {
        try {
            $this->db->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            error_log("Erro na conexão com o banco de dados: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Método de teste para verificar se os modelos estão funcionando
     * 
     * @param int $empresaId ID da empresa
     * @return array Dados básicos para teste
     */
    public function getDadosTeste($empresaId)
    {
        try {
            // Obtém dados básicos para teste
            $totalChamados = $this->chamadoModel->count('empresa_id = :empresa_id', ['empresa_id' => $empresaId]);
            $setores = $this->setorModel->findAll('empresa_id = :empresa_id AND ativo = 1', ['empresa_id' => $empresaId], 'nome ASC', 5);
            $statusList = $this->statusModel->findAll(null, null, 'nome ASC');

            return [
                'totalChamados' => $totalChamados,
                'setores' => $setores,
                'statusList' => $statusList
            ];
        } catch (Exception $e) {
            error_log('Erro ao obter dados de teste: ' . $e->getMessage());
            throw $e;
        }
    }
}
