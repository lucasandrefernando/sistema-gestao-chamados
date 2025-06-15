<?php
require_once ROOT_DIR . '/app/models/Model.php';

/**
 * Model para gerenciamento de status de chamados
 */
class StatusChamado extends Model
{
    /**
     * Construtor
     */
    public function __construct()
    {
        parent::__construct('status_chamados');
    }
}
