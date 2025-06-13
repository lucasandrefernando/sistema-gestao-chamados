<?php
require_once ROOT_DIR . '/app/models/Model.php';

/**
 * Modelo para gerenciamento de setores
 */
class Setor extends Model
{
    protected $table = 'setores';

    /**
     * Construtor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca setores ativos
     */
    public function findAtivos()
    {
        return $this->findAll('ativo = 1', [], 'nome ASC');
    }

    /**
     * Verifica se um setor tem chamados associados
     */
    public function temChamados($id)
    {
        require_once ROOT_DIR . '/app/models/Chamado.php';
        $chamadoModel = new Chamado();

        $count = $chamadoModel->count('setor_id = :setor_id', ['setor_id' => $id]);

        return $count > 0;
    }
}
