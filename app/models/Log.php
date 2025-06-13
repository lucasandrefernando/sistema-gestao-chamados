<?php
require_once ROOT_DIR . '/app/models/Model.php';

/**
 * Modelo para gerenciamento de logs
 */
class Log extends Model
{
    protected $table = 'logs';

    /**
     * Construtor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Registra um log
     */
    public function registrar($usuarioId, $chamadoId, $acao, $descricao, $dadosAnteriores = null, $dadosNovos = null)
    {
        $data = [
            'usuario_id' => $usuarioId,
            'chamado_id' => $chamadoId,
            'acao' => $acao,
            'descricao' => $descricao,
            'dados_anteriores' => $dadosAnteriores ? json_encode($dadosAnteriores) : null,
            'dados_novos' => $dadosNovos ? json_encode($dadosNovos) : null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null
        ];

        return $this->create($data);
    }

    /**
     * Busca logs de um chamado
     */
    public function findByChamado($chamadoId)
    {
        $sql = "SELECT l.*, u.nome as usuario
                FROM {$this->table} l
                LEFT JOIN usuarios u ON l.usuario_id = u.id
                WHERE l.chamado_id = :chamado_id
                ORDER BY l.data_criacao DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['chamado_id' => $chamadoId]);

        return $stmt->fetchAll();
    }
}
