<?php
require_once ROOT_DIR . '/app/models/Model.php';

/**
 * Modelo para gerenciamento de licenças
 */
class Licenca extends Model
{
    protected $table = 'licencas';

    /**
     * Campos permitidos para criação/atualização
     */
    protected $fillable = [
        'empresa_id',
        'quantidade',
        'data_inicio',
        'data_fim',
        'observacoes',
        'ativo',
        'criado_por',
        'data_criacao',
        'atualizado_por',
        'data_atualizacao'
    ];

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
            WHERE empresa_id = ? 
            AND ativo = 1 
            AND data_inicio <= ? 
            AND data_fim >= ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$empresaId, $hoje, $hoje]);

        $result = $stmt->fetch();

        // Garantir que retorne 0 se não houver licenças ou se o resultado for NULL
        return intval($result['total'] ?? 0);
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

    /**
     * Busca licenças que estão prestes a expirar
     */
    public function findProximasExpirar($diasAviso = 30)
    {
        $hoje = date('Y-m-d');
        $dataLimite = date('Y-m-d', strtotime("+{$diasAviso} days"));

        return $this->findAll(
            'ativo = 1 AND data_inicio <= :hoje AND data_fim >= :hoje AND data_fim <= :data_limite',
            ['hoje' => $hoje, 'data_limite' => $dataLimite],
            'data_fim ASC'
        );
    }

    /**
     * Obtém estatísticas de licenças por status
     */
    public function getEstatisticasPorStatus($empresaId = null)
    {
        $hoje = date('Y-m-d');
        $where = [];
        $params = ['hoje' => $hoje];

        if ($empresaId) {
            $where[] = 'empresa_id = :empresa_id';
            $params['empresa_id'] = $empresaId;
        }

        // Licenças ativas
        $whereAtivas = $where;
        $whereAtivas[] = 'ativo = 1 AND data_inicio <= :hoje AND data_fim >= :hoje';
        $ativas = $this->count(implode(' AND ', $whereAtivas), $params);

        // Licenças futuras
        $whereFuturas = $where;
        $whereFuturas[] = 'ativo = 1 AND data_inicio > :hoje';
        $futuras = $this->count(implode(' AND ', $whereFuturas), $params);

        // Licenças expiradas
        $whereExpiradas = $where;
        $whereExpiradas[] = 'ativo = 1 AND data_fim < :hoje';
        $expiradas = $this->count(implode(' AND ', $whereExpiradas), $params);

        // Licenças inativas
        $whereInativas = $where;
        $whereInativas[] = 'ativo = 0';
        $inativas = $this->count(implode(' AND ', $whereInativas), $params);

        return [
            'ativas' => $ativas,
            'futuras' => $futuras,
            'expiradas' => $expiradas,
            'inativas' => $inativas,
            'total' => $ativas + $futuras + $expiradas + $inativas
        ];
    }
}
