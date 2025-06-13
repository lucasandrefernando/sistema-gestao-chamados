<?php
require_once ROOT_DIR . '/app/models/Model.php';

/**
 * Modelo para gerenciamento de histórico de chamados
 */
class HistoricoChamado extends Model
{
    protected $table = 'historico_chamados';

    /**
     * Construtor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca histórico de um chamado
     */
    public function findByChamado($chamadoId)
    {
        $sql = "SELECT h.*, 
                       s_ant.nome as setor_anterior, 
                       s_novo.nome as setor_novo, 
                       st_ant.nome as status_anterior, 
                       st_novo.nome as status_novo,
                       u.nome as usuario
                FROM {$this->table} h
                JOIN setores s_ant ON h.setor_id_anterior = s_ant.id
                JOIN setores s_novo ON h.setor_id_novo = s_novo.id
                JOIN status_chamados st_ant ON h.status_id_anterior = st_ant.id
                JOIN status_chamados st_novo ON h.status_id_novo = st_novo.id
                LEFT JOIN usuarios u ON h.usuario_id = u.id
                WHERE h.chamado_id = :chamado_id
                ORDER BY h.data_criacao DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['chamado_id' => $chamadoId]);

        return $stmt->fetchAll();
    }
}
