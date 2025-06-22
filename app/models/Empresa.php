<?php
require_once ROOT_DIR . '/app/models/Model.php';

/**
 * Modelo para gerenciamento de empresas
 */
class Empresa extends Model
{
    protected $table = 'empresas';

    /**
     * Construtor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca empresas ativas
     */
    public function findAtivas()
    {
        return $this->findAll('ativo = 1', [], 'nome ASC');
    }

    /**
     * Verifica se uma empresa tem usuários associados
     */
    public function temUsuarios($id)
    {
        require_once ROOT_DIR . '/app/models/Usuario.php';
        $usuarioModel = new Usuario();

        $count = $usuarioModel->count('empresa_id = :empresa_id', ['empresa_id' => $id]);

        return $count > 0;
    }


    /**
     * Verifica se uma empresa tem licenças associadas
     */
    public function temLicencas($id)
    {
        require_once ROOT_DIR . '/app/models/Licenca.php';
        $licencaModel = new Licenca();

        $count = $licencaModel->count('empresa_id = :empresa_id', ['empresa_id' => $id]);

        return $count > 0;
    }

    /**
     * Verifica se já existe uma empresa com o CNPJ informado
     * 
     * @param string $cnpj CNPJ a ser verificado
     * @param int|null $excluirId ID da empresa a ser excluída da verificação (útil para edição)
     * @return bool
     */
    public function cnpjExiste($cnpj, $excluirId = null)
    {
        // Normaliza o CNPJ (remove caracteres não numéricos)
        $cnpjNormalizado = preg_replace('/[^0-9]/', '', $cnpj);

        $params = ['cnpj' => $cnpjNormalizado];
        $where = 'cnpj = :cnpj';

        // Se estiver editando, exclui a própria empresa da verificação
        if ($excluirId) {
            $where .= ' AND id != :id';
            $params['id'] = $excluirId;
        }

        $count = $this->count($where, $params);
        return $count > 0;
    }
}
