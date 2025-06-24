<?php
require_once ROOT_DIR . '/app/models/Model.php';

/**
 * Model para gerenciamento de setores
 */
class Setor extends Model
{
    /**
     * Construtor
     */
    public function __construct()
    {
        parent::__construct('setores');
    }

    /**
     * Obtém todos os setores ativos de uma empresa
     * 
     * @param int $empresaId ID da empresa
     * @return array Lista de setores
     */
    public function getSetoresByEmpresa($empresaId)
    {
        return $this->findAll(
            'empresa_id = :empresa_id AND (removido = 0 OR removido IS NULL)',
            ['empresa_id' => $empresaId],
            'nome ASC'
        );
    }

    /**
     * Verifica se um setor com o mesmo nome já existe na empresa
     * 
     * @param string $nome Nome do setor
     * @param int $empresaId ID da empresa
     * @param int|null $excluirId ID do setor a ser excluído da verificação (para edição)
     * @return bool True se já existe, false caso contrário
     */
    public function existeNaEmpresa($nome, $empresaId, $excluirId = null)
    {
        $condicao = 'nome = :nome AND empresa_id = :empresa_id AND (removido = 0 OR removido IS NULL)';
        $params = [
            'nome' => $nome,
            'empresa_id' => $empresaId
        ];

        if ($excluirId) {
            $condicao .= ' AND id != :id';
            $params['id'] = $excluirId;
        }

        $setor = $this->findOne($condicao, $params);
        return $setor ? true : false;
    }

    /**
     * Conta o número de chamados associados a um setor
     * 
     * @param int $setorId ID do setor
     * @return int Número de chamados
     */
    public function contarChamados($setorId)
    {
        // Verifica se a tabela chamados existe
        try {
            $sql = "SELECT COUNT(*) as total FROM chamados WHERE setor_id = :setor_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['setor_id' => $setorId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['total'] : 0;
        } catch (PDOException $e) {
            // Se a tabela não existir ou ocorrer outro erro, retorna 0
            return 0;
        }
    }

    /**
     * Conta o número de usuários associados a um setor
     * 
     * @param int $setorId ID do setor
     * @return int Número de usuários
     */
    public function contarUsuarios($setorId)
    {
        // Verifica se a tabela usuarios_setores existe
        try {
            $sql = "SELECT COUNT(*) as total FROM usuarios_setores WHERE setor_id = :setor_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['setor_id' => $setorId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['total'] : 0;
        } catch (PDOException $e) {
            // Se a tabela não existir ou ocorrer outro erro, retorna 0
            return 0;
        }
    }

    /**
     * Busca o e-mail de um setor
     * 
     * @param int $id ID do setor
     * @return string|null E-mail do setor ou null se não encontrado
     */
    public function buscarEmail($id)
    {
        $setor = $this->findById($id);
        return $setor ? $setor['email'] : null;
    }

    /**
     * Busca o ID de um setor pelo e-mail
     * 
     * @param string $email E-mail do setor
     * @return int|null ID do setor ou null se não encontrado
     */
    public function buscarIdPorEmail($email)
    {
        $setor = $this->findOne('email = :email', ['email' => $email]);
        return $setor ? $setor['id'] : null;
    }

    /**
     * Busca usuários de um setor
     * 
     * @param int $id ID do setor
     * @return array Lista de usuários
     */
    public function buscarUsuarios($id)
    {
        $sql = "SELECT id, nome, email
            FROM usuarios
            WHERE setor_id = :setor_id AND ativo = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':setor_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtém os setores aos quais o usuário tem acesso
     * 
     * @param int $usuarioId ID do usuário
     * @param int $empresaId ID da empresa
     * @return array Lista de setores
     */
    public function getSetoresByUsuario($usuarioId, $empresaId)
    {
        try {
            // Verifica se a tabela usuarios_setores existe
            if (!$this->tableExists('usuarios_setores')) {
                // Se a tabela não existir, retorna todos os setores (comportamento padrão)
                return $this->findAll(
                    'empresa_id = :empresa_id AND ativo = 1 AND (removido = 0 OR removido IS NULL)',
                    ['empresa_id' => $empresaId],
                    'nome ASC'
                );
            }

            // Consulta para obter os setores aos quais o usuário tem acesso
            $sql = "SELECT s.* FROM setores s
                INNER JOIN usuarios_setores us ON s.id = us.setor_id
                WHERE us.usuario_id = :usuario_id
                AND s.empresa_id = :empresa_id
                AND s.ativo = 1
                AND (s.removido = 0 OR s.removido IS NULL)
                ORDER BY s.nome ASC";

            $params = [
                'usuario_id' => $usuarioId,
                'empresa_id' => $empresaId
            ];

            $setores = $this->executeQuery($sql, $params);

            // Se o usuário não tiver setores associados, verifica se ele é admin
            if (empty($setores)) {
                // Obtém informações do usuário para verificar se é admin
                $sql = "SELECT admin FROM usuarios WHERE id = :usuario_id";
                $usuario = $this->executeQuerySingle($sql, ['usuario_id' => $usuarioId]);

                // Se for admin, retorna todos os setores
                if ($usuario && $usuario['admin']) {
                    return $this->findAll(
                        'empresa_id = :empresa_id AND ativo = 1 AND (removido = 0 OR removido IS NULL)',
                        ['empresa_id' => $empresaId],
                        'nome ASC'
                    );
                }
            }

            return $setores;
        } catch (Exception $e) {
            error_log("Erro ao obter setores do usuário: " . $e->getMessage());
            // Em caso de erro, retorna array vazio
            return [];
        }
    }

    /**
     * Verifica se o usuário tem acesso a um setor específico
     * 
     * @param int $usuarioId ID do usuário
     * @param int $setorId ID do setor
     * @return bool True se tem acesso, false caso contrário
     */
    public function usuarioTemAcessoAoSetor($usuarioId, $setorId)
    {
        try {
            // Verifica se a tabela usuarios_setores existe
            if (!$this->tableExists('usuarios_setores')) {
                // Se a tabela não existir, assume que todos têm acesso
                return true;
            }

            // Verifica se o usuário é admin
            $sql = "SELECT admin FROM usuarios WHERE id = :usuario_id";
            $usuario = $this->executeQuerySingle($sql, ['usuario_id' => $usuarioId]);

            // Se for admin, tem acesso a todos os setores
            if ($usuario && $usuario['admin']) {
                return true;
            }

            // Verifica se o usuário está associado ao setor
            $sql = "SELECT COUNT(*) as total FROM usuarios_setores 
                WHERE usuario_id = :usuario_id AND setor_id = :setor_id";

            $result = $this->executeQuerySingle($sql, [
                'usuario_id' => $usuarioId,
                'setor_id' => $setorId
            ]);

            return $result && $result['total'] > 0;
        } catch (Exception $e) {
            error_log("Erro ao verificar acesso do usuário ao setor: " . $e->getMessage());
            // Em caso de erro, nega o acesso por segurança
            return false;
        }
    }

    /**
     * Conta quantos setores o usuário tem acesso
     * 
     * @param int $usuarioId ID do usuário
     * @return int Número de setores
     */
    public function contarSetoresDoUsuario($usuarioId)
    {
        try {
            // Verifica se a tabela usuarios_setores existe
            if (!$this->tableExists('usuarios_setores')) {
                // Se a tabela não existir, conta todos os setores ativos
                $sql = "SELECT COUNT(*) as total FROM setores 
                    WHERE ativo = 1 AND (removido = 0 OR removido IS NULL)";
                $result = $this->executeQuerySingle($sql, []);
                return $result ? $result['total'] : 0;
            }

            // Verifica se o usuário é admin
            $sql = "SELECT admin FROM usuarios WHERE id = :usuario_id";
            $usuario = $this->executeQuerySingle($sql, ['usuario_id' => $usuarioId]);

            // Se for admin, conta todos os setores ativos
            if ($usuario && $usuario['admin']) {
                $sql = "SELECT COUNT(*) as total FROM setores 
                    WHERE ativo = 1 AND (removido = 0 OR removido IS NULL)";
                $result = $this->executeQuerySingle($sql, []);
                return $result ? $result['total'] : 0;
            }

            // Conta os setores associados ao usuário
            $sql = "SELECT COUNT(DISTINCT us.setor_id) as total 
                FROM usuarios_setores us
                INNER JOIN setores s ON us.setor_id = s.id
                WHERE us.usuario_id = :usuario_id
                AND s.ativo = 1
                AND (s.removido = 0 OR s.removido IS NULL)";

            $result = $this->executeQuerySingle($sql, ['usuario_id' => $usuarioId]);
            return $result ? $result['total'] : 0;
        } catch (Exception $e) {
            error_log("Erro ao contar setores do usuário: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtém apenas chamados ativos (abertos ou em andamento) por setor
     * 
     * @param int $empresaId ID da empresa
     * @param int $setorId ID do setor
     * @return array Lista de chamados
     */
    public function getChamadosAtivosPorSetor($empresaId, $setorId)
    {
        $sql = "SELECT c.*, s.nome as status_nome, u.nome as solicitante_nome
            FROM chamados c
            LEFT JOIN status_chamados s ON c.status_id = s.id
            LEFT JOIN usuarios u ON c.solicitante_id = u.id
            WHERE c.empresa_id = :empresa_id 
            AND c.setor_id = :setor_id
            AND c.status_id IN (1, 2) -- Apenas Aberto (1) e Em Atendimento (2)
            ORDER BY c.data_solicitacao DESC";

        return $this->executeQuery($sql, [
            'empresa_id' => $empresaId,
            'setor_id' => $setorId
        ]);
    }

    /**
     * Obtém a quantidade de chamados por status e setor
     * 
     * @param int $empresaId ID da empresa
     * @param int $setorId ID do setor
     * @param int $statusId ID do status
     * @return int Quantidade de chamados
     */
    public function getQuantidadeChamadosPorStatusESetor($empresaId, $setorId, $statusId)
    {
        $sql = "SELECT COUNT(*) as total
            FROM chamados
            WHERE empresa_id = :empresa_id 
            AND setor_id = :setor_id
            AND status_id = :status_id";

        $result = $this->executeQuerySingle($sql, [
            'empresa_id' => $empresaId,
            'setor_id' => $setorId,
            'status_id' => $statusId
        ]);

        return $result ? $result['total'] : 0;
    }

    /**
     * Obtém a quantidade de chamados do último mês por setor
     * 
     * @param int $empresaId ID da empresa
     * @param int $setorId ID do setor
     * @return int Quantidade de chamados
     */
    public function getChamadosUltimoMesPorSetor($empresaId, $setorId)
    {
        $sql = "SELECT COUNT(*) as total
            FROM chamados
            WHERE empresa_id = :empresa_id 
            AND setor_id = :setor_id
            AND data_solicitacao >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";

        $result = $this->executeQuerySingle($sql, [
            'empresa_id' => $empresaId,
            'setor_id' => $setorId
        ]);

        return $result ? $result['total'] : 0;
    }

    /**
     * Obtém o tempo médio de conclusão dos últimos 30 dias por setor
     * 
     * @param int $empresaId ID da empresa
     * @param int $setorId ID do setor
     * @return int Tempo médio em horas
     */
    public function getTempoMedioConclusaoUltimos30DiasPorSetor($empresaId, $setorId)
    {
        $sql = "SELECT AVG(TIMESTAMPDIFF(HOUR, data_solicitacao, data_conclusao)) as tempo_medio
            FROM chamados
            WHERE empresa_id = :empresa_id 
            AND setor_id = :setor_id
            AND status_id = 4 -- Concluído
            AND data_conclusao IS NOT NULL
                        AND data_conclusao >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";

        $result = $this->executeQuerySingle($sql, [
            'empresa_id' => $empresaId,
            'setor_id' => $setorId
        ]);

        return $result ? round($result['tempo_medio']) : 0;
    }
}
