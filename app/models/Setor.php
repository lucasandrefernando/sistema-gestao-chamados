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
}
