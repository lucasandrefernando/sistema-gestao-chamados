<?php
require_once ROOT_DIR . '/app/models/Model.php';

/**
 * Modelo para gerenciamento de chamados
 */
class Chamado extends Model
{
    protected $table = 'chamados';

    /**
     * Construtor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca chamados por setor
     */
    public function findBySetor($setorId, $empresaId)
    {
        return $this->findAll(
            'setor_id = :setor_id AND empresa_id = :empresa_id',
            ['setor_id' => $setorId, 'empresa_id' => $empresaId],
            'data_solicitacao DESC'
        );
    }

    /**
     * Busca chamados por status
     */
    public function findByStatus($statusId, $empresaId)
    {
        return $this->findAll(
            'status_id = :status_id AND empresa_id = :empresa_id',
            ['status_id' => $statusId, 'empresa_id' => $empresaId],
            'data_solicitacao DESC'
        );
    }

    /**
     * Busca chamados por empresa
     */
    public function findByEmpresa($empresaId)
    {
        return $this->findAll(
            'empresa_id = :empresa_id',
            ['empresa_id' => $empresaId],
            'data_solicitacao DESC'
        );
    }

    /**
     * Obtém estatísticas para o dashboard
     */
    public function getEstatisticas($empresaId)
    {
        // Chamados por setor
        $chamadosPorSetor = $this->query(
            "SELECT s.id, s.nome, COUNT(c.id) as total 
             FROM setores s 
             LEFT JOIN chamados c ON s.id = c.setor_id AND c.empresa_id = :empresa_id 
             WHERE s.ativo = 1 
             GROUP BY s.id, s.nome 
             ORDER BY total DESC",
            ['empresa_id' => $empresaId]
        );

        // Chamados por status
        $chamadosPorStatus = $this->query(
            "SELECT st.id, st.nome, st.cor, COUNT(c.id) as total 
             FROM status_chamados st 
             LEFT JOIN chamados c ON st.id = c.status_id AND c.empresa_id = :empresa_id 
             WHERE st.ativo = 1 
             GROUP BY st.id, st.nome, st.cor 
             ORDER BY st.id",
            ['empresa_id' => $empresaId]
        );

        // Chamados recentes
        $chamadosRecentes = $this->query(
            "SELECT c.id, c.solicitante, c.paciente, c.quarto_leito, c.descricao, 
                    c.data_solicitacao, s.nome as setor, st.nome as status, st.cor as status_cor 
             FROM chamados c 
             JOIN setores s ON c.setor_id = s.id 
             JOIN status_chamados st ON c.status_id = st.id 
             WHERE c.empresa_id = :empresa_id 
             ORDER BY c.data_solicitacao DESC 
             LIMIT 10",
            ['empresa_id' => $empresaId]
        );

        // Total de chamados
        $totalChamados = $this->count('empresa_id = :empresa_id', ['empresa_id' => $empresaId]);

        // Chamados abertos (não concluídos nem cancelados)
        $chamadosAbertos = $this->count(
            'empresa_id = :empresa_id AND status_id NOT IN (4, 5)',
            ['empresa_id' => $empresaId]
        );

        // Chamados concluídos hoje
        $hoje = date('Y-m-d');
        $chamadosConcluidosHoje = $this->count(
            'empresa_id = :empresa_id AND status_id = 4 AND DATE(data_conclusao) = :hoje',
            ['empresa_id' => $empresaId, 'hoje' => $hoje]
        );

        return [
            'por_setor' => $chamadosPorSetor,
            'por_status' => $chamadosPorStatus,
            'recentes' => $chamadosRecentes,
            'total' => $totalChamados,
            'abertos' => $chamadosAbertos,
            'concluidos_hoje' => $chamadosConcluidosHoje
        ];
    }
}
