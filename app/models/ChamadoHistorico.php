<?php
require_once ROOT_DIR . '/app/models/Model.php';

/**
 * Model para gerenciamento de histórico de chamados
 */
class ChamadoHistorico extends Model
{
    /**
     * Construtor
     */
    public function __construct()
    {
        parent::__construct('historico_chamados');
    }

    /**
     * Obtém o histórico de um chamado com informações detalhadas
     * 
     * @param int $chamadoId ID do chamado
     * @return array Histórico do chamado
     */
    public function getHistoricoChamado($chamadoId)
    {
        try {
            $sql = "SELECT h.*, 
                        u.nome as usuario_nome, 
                        s_ant.nome as setor_anterior_nome,
                        s_novo.nome as setor_novo_nome,
                        st_ant.nome as status_anterior_nome,
                        st_novo.nome as status_novo_nome
                    FROM {$this->table} h
                    LEFT JOIN usuarios u ON h.usuario_id = u.id
                    LEFT JOIN setores s_ant ON h.setor_id_anterior = s_ant.id
                    LEFT JOIN setores s_novo ON h.setor_id_novo = s_novo.id
                    LEFT JOIN status_chamados st_ant ON h.status_id_anterior = st_ant.id
                    LEFT JOIN status_chamados st_novo ON h.status_id_novo = st_novo.id
                    WHERE h.chamado_id = :chamado_id
                    ORDER BY h.data_criacao DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['chamado_id' => $chamadoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Erro ao obter histórico do chamado: ' . $e->getMessage());
            return [];
        }
    }
}
