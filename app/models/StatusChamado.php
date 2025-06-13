<?php
require_once ROOT_DIR . '/app/models/Model.php';

/**
 * Modelo para gerenciamento de status de chamados
 */
class StatusChamado extends Model
{
    protected $table = 'status_chamados';

    /**
     * Construtor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca status ativos
     */
    public function findAtivos()
    {
        return $this->findAll('ativo = 1', [], 'id ASC');
    }
}
