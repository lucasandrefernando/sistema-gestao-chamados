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
}
