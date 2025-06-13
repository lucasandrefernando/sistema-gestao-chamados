<?php
require_once ROOT_DIR . '/app/models/Model.php';

/**
 * Modelo para gerenciamento de licenças
 */
class Licenca extends Model
{
    protected $table = 'licencas';

    /**
     * Construtor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtém o total de licenças ativas para uma empresa
     */
    public function getTotalLicencas($empresaId)
    {
        $hoje = date('Y-m-d');

        $sql = "SELECT SUM(quantidade) as total 
                FROM {$this->table} 
                WHERE empresa_id = :empresa_id 
                AND ativo = 1 
                AND data_inicio <= :hoje 
                AND data_fim >= :hoje";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'empresa_id' => $empresaId,
            'hoje' => $hoje
        ]);

        $result = $stmt->fetch();

        return $result['total'] ?? 0;
    }

    /**
     * Verifica se uma licença está ativa
     */
    public function isLicencaAtiva($id)
    {
        $hoje = date('Y-m-d');

        $licenca = $this->findById($id);

        return $licenca &&
            $licenca['ativo'] &&
            $licenca['data_inicio'] <= $hoje &&
            $licenca['data_fim'] >= $hoje;
    }

    /**
     * Busca licenças por empresa
     */
    public function findByEmpresa($empresaId)
    {
        return $this->findAll(
            'empresa_id = :empresa_id',
            ['empresa_id' => $empresaId],
            'data_fim DESC'
        );
    }

    /**
     * Busca licenças ativas por empresa
     */
    public function findAtivasByEmpresa($empresaId)
    {
        $hoje = date('Y-m-d');

        return $this->findAll(
            'empresa_id = :empresa_id AND ativo = 1 AND data_inicio <= :hoje AND data_fim >= :hoje',
            ['empresa_id' => $empresaId, 'hoje' => $hoje],
            'data_fim ASC'
        );
    }
}
