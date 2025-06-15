<?php
require_once ROOT_DIR . '/app/models/Model.php';

/**
 * Model para gerenciamento de comentários de chamados
 */
class ChamadoComentario extends Model
{
    /**
     * Construtor
     */
    public function __construct()
    {
        parent::__construct('chamados_comentarios');
    }

    /**
     * Obtém comentários de um chamado com informações do usuário
     * 
     * @param int $chamadoId ID do chamado
     * @return array Comentários do chamado
     */
    public function getComentariosChamado($chamadoId)
    {
        try {
            $sql = "SELECT c.*, u.nome as usuario_nome, u.email as usuario_email
                    FROM {$this->table} c
                    LEFT JOIN usuarios u ON c.usuario_id = u.id
                    WHERE c.chamado_id = :chamado_id
                    ORDER BY c.data_criacao ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['chamado_id' => $chamadoId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Erro ao obter comentários do chamado: ' . $e->getMessage());
            return [];
        }
    }
}
