<?php
require_once ROOT_DIR . '/app/controllers/Controller.php';
require_once ROOT_DIR . '/app/models/Setor.php';
require_once ROOT_DIR . '/app/models/Chamado.php';

/**
 * Controlador para gerenciamento de setores
 */
class SetoresController extends Controller
{
    private $setorModel;
    private $chamadoModel;

    /**
     * Construtor
     */
    public function __construct()
    {
        // Se não estiver autenticado, redireciona para o login
        if (!is_authenticated()) {
            redirect('auth');
            exit;
        }

        // Inicializa os modelos
        $this->setorModel = new Setor();
        $this->chamadoModel = new Chamado();
    }

    /**
     * Redireciona para a visualização de setores (para usuários comuns)
     * ou para a administração de setores (para administradores)
     */
    public function index()
    {
        // Se for admin, redireciona para a área de administração de setores
        if (is_admin()) {
            redirect('setores/admin');
        } else {
            // Se for usuário comum, redireciona para a visualização de setores
            redirect('setores/visualizacao');
        }
    }

    /**
     * Visualização de setores para usuários comuns
     * Mostra estatísticas e chamados por setor
     */
    public function visualizacao()
    {
        $empresaId = get_empresa_id();

        // Obtém todos os setores ativos da empresa
        $setores = $this->setorModel->findAll(
            'empresa_id = :empresa_id AND ativo = 1 AND (removido = 0 OR removido IS NULL)',
            ['empresa_id' => $empresaId],
            'nome ASC'
        );

        // Para cada setor, obtém estatísticas de chamados
        foreach ($setores as &$setor) {
            // Total de chamados no setor
            $setor['total_chamados'] = $this->setorModel->contarChamados($setor['id']);

            // Chamados por status
            $setor['chamados_por_status'] = $this->chamadoModel->getChamadosPorStatusESetor($empresaId, $setor['id']);

            // Chamados recentes
            $setor['chamados_recentes'] = $this->chamadoModel->getChamadosRecentesPorSetor($empresaId, $setor['id'], 5);

            // Tempo médio de atendimento
            $setor['tempo_medio_atendimento'] = $this->chamadoModel->getTempoMedioAtendimentoPorSetor($empresaId, $setor['id']);

            // Prioridade dos chamados
            $setor['chamados_por_prioridade'] = $this->chamadoModel->getChamadosPorPrioridadeESetor($empresaId, $setor['id']);
        }

        $this->render('setores/visualizacao', [
            'setores' => $setores
        ]);
    }

    /**
     * Detalhes de um setor específico para usuários comuns
     */
    public function detalhes($id)
    {
        $empresaId = get_empresa_id();
        error_log("Acessando detalhes do setor ID: $id, Empresa ID: $empresaId");

        // Obtém o setor
        $setor = $this->setorModel->findById($id);
        error_log("Dados do setor: " . print_r($setor, true));

        // Verifica se o setor existe e pertence à empresa do usuário logado
        if (!$setor || $setor['empresa_id'] != $empresaId || !$setor['ativo'] || $setor['removido']) {
            set_flash_message('error', 'Setor não encontrado ou inativo.');
            redirect('setores/visualizacao');
            return;
        }

        // Obtém todos os chamados do setor
        try {
            error_log("Buscando chamados para o setor ID: $id");
            $chamados = $this->chamadoModel->getChamadosPorSetor($empresaId, $id);

            error_log("Total de chamados encontrados: " . count($chamados));
            if (!empty($chamados)) {
                error_log("Primeiro chamado: " . print_r($chamados[0], true));
            }
        } catch (Exception $e) {
            error_log("ERRO ao obter chamados: " . $e->getMessage());
            $chamados = [];
        }

        // Obtém estatísticas detalhadas do setor
        try {
            error_log("Buscando estatísticas para o setor ID: $id");

            // Chamados por status
            $chamadosPorStatus = $this->chamadoModel->getChamadosPorStatusESetor($empresaId, $id);
            error_log("Chamados por status: " . print_r($chamadosPorStatus, true));

            // Chamados por prioridade (tipo de serviço)
            $chamadosPorPrioridade = $this->chamadoModel->getChamadosPorPrioridadeESetor($empresaId, $id);
            error_log("Chamados por prioridade: " . print_r($chamadosPorPrioridade, true));

            // Tempo médio de atendimento
            $tempoMedio = $this->chamadoModel->getTempoMedioAtendimentoPorSetor($empresaId, $id);
            error_log("Tempo médio de atendimento: $tempoMedio");

            // Chamados por mês
            $chamadosPorMes = $this->chamadoModel->getChamadosPorMesESetor($empresaId, $id);
            error_log("Chamados por mês: " . print_r($chamadosPorMes, true));

            // Usuários mais ativos
            $usuariosMaisAtivos = $this->chamadoModel->getUsuariosMaisAtivosPorSetor($empresaId, $id);
            error_log("Usuários mais ativos: " . print_r($usuariosMaisAtivos, true));

            $estatisticas = [
                'total_chamados' => count($chamados),
                'chamados_por_status' => $chamadosPorStatus,
                'chamados_por_prioridade' => $chamadosPorPrioridade,
                'tempo_medio_atendimento' => $tempoMedio,
                'chamados_por_mes' => $chamadosPorMes,
                'usuarios_mais_ativos' => $usuariosMaisAtivos
            ];
        } catch (Exception $e) {
            error_log("ERRO ao obter estatísticas: " . $e->getMessage());
            $estatisticas = [
                'total_chamados' => 0,
                'chamados_por_status' => [],
                'chamados_por_prioridade' => [],
                'tempo_medio_atendimento' => 0,
                'chamados_por_mes' => [],
                'usuarios_mais_ativos' => []
            ];
        }

        error_log("Renderizando view com dados: " . json_encode([
            'setor_id' => $setor['id'],
            'total_chamados' => $estatisticas['total_chamados'],
            'tem_chamados_por_status' => !empty($estatisticas['chamados_por_status']),
            'tem_chamados_por_prioridade' => !empty($estatisticas['chamados_por_prioridade']),
            'tem_chamados_por_mes' => !empty($estatisticas['chamados_por_mes']),
            'tem_usuarios_mais_ativos' => !empty($estatisticas['usuarios_mais_ativos'])
        ]));

        $this->render('setores/detalhes', [
            'setor' => $setor,
            'chamados' => $chamados,
            'estatisticas' => $estatisticas
        ]);
    }

    /**
     * Lista de setores para administração
     * Apenas para administradores
     */
    public function admin()
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $empresaId = get_empresa_id();

        // Verifica se deve mostrar setores removidos
        $mostrarRemovidos = isset($_GET['mostrar_removidos']) && $_GET['mostrar_removidos'] == 1;

        // Condição para filtrar setores
        $condicao = 'empresa_id = :empresa_id';
        if (!$mostrarRemovidos) {
            $condicao .= ' AND (removido = 0 OR removido IS NULL)';
        }

        // Obtém a lista de setores
        $setores = $this->setorModel->findAll(
            $condicao,
            ['empresa_id' => $empresaId],
            'nome ASC'
        );

        // Para cada setor, obtém o número de chamados e usuários associados
        foreach ($setores as &$setor) {
            $setor['total_chamados'] = $this->setorModel->contarChamados($setor['id']);
            $setor['total_usuarios'] = $this->setorModel->contarUsuarios($setor['id']);
        }

        $this->render('setores/admin/index', [
            'setores' => $setores,
            'mostrarRemovidos' => $mostrarRemovidos
        ]);
    }

    /**
     * Formulário para criar setor
     * Apenas para administradores
     */
    public function criar()
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $this->render('setores/admin/form', [
            'titulo' => 'Novo Setor',
            'acao' => 'criar'
        ]);
    }

    /**
     * Processa a criação de setor
     * Apenas para administradores
     */
    public function store()
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        // Obtém os dados do formulário
        $data = $this->getPostData();

        // Valida os campos obrigatórios
        $requiredFields = [
            'nome' => 'Nome'
        ];

        $errors = $this->validateRequired($data, $requiredFields);

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
            redirect('setores/criar');
            return;
        }

        // Sempre usa a empresa do usuário logado
        $empresaId = get_empresa_id();
        $data['empresa_id'] = $empresaId;

        // Verifica se já existe um setor com o mesmo nome na empresa
        if ($this->setorModel->existeNaEmpresa($data['nome'], $empresaId)) {
            set_flash_message('error', 'Já existe um setor com este nome nesta empresa.');
            redirect('setores/criar');
            return;
        }

        // Define valores padrão
        $data['ativo'] = isset($data['ativo']) ? 1 : 0;
        $data['criado_por'] = get_user_id();
        $data['criado_em'] = date('Y-m-d H:i:s');

        // Cria o setor
        $id = $this->setorModel->create($data);

        if ($id) {
            set_flash_message('success', 'Setor criado com sucesso.');
            redirect('setores/admin');
        } else {
            set_flash_message('error', 'Erro ao criar setor.');
            redirect('setores/criar');
        }
    }

    /**
     * Formulário para editar setor
     * Apenas para administradores
     */
    public function editar($id)
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $empresaId = get_empresa_id();

        // Obtém o setor
        $setor = $this->setorModel->findById($id);

        // Verifica se o setor existe e pertence à empresa do usuário logado
        if (!$setor || $setor['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Setor não encontrado.');
            redirect('setores/admin');
            return;
        }

        $this->render('setores/admin/form', [
            'titulo' => 'Editar Setor',
            'acao' => 'editar',
            'setor' => $setor
        ]);
    }

    /**
     * Processa a atualização de setor
     * Apenas para administradores
     */
    public function update($id)
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $empresaId = get_empresa_id();

        // Obtém o setor
        $setor = $this->setorModel->findById($id);

        // Verifica se o setor existe e pertence à empresa do usuário logado
        if (!$setor || $setor['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Setor não encontrado.');
            redirect('setores/admin');
            return;
        }

        // Obtém os dados do formulário
        $data = $this->getPostData();

        // Valida os campos obrigatórios
        $requiredFields = [
            'nome' => 'Nome'
        ];

        $errors = $this->validateRequired($data, $requiredFields);

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
            redirect('setores/editar/' . $id);
            return;
        }

        // Verifica se já existe um setor com o mesmo nome na empresa (exceto o próprio)
        if ($this->setorModel->existeNaEmpresa($data['nome'], $empresaId, $id)) {
            set_flash_message('error', 'Já existe um setor com este nome nesta empresa.');
            redirect('setores/editar/' . $id);
            return;
        }

        // Define valores padrão
        $data['ativo'] = isset($data['ativo']) ? 1 : 0;
        $data['atualizado_por'] = get_user_id();
        $data['atualizado_em'] = date('Y-m-d H:i:s');

        // Atualiza o setor
        if ($this->setorModel->update($id, $data)) {
            set_flash_message('success', 'Setor atualizado com sucesso.');
            redirect('setores/admin');
        } else {
            set_flash_message('error', 'Erro ao atualizar setor.');
            redirect('setores/editar/' . $id);
        }
    }

    /**
     * Remove um setor
     * Apenas para administradores
     */
    public function remover($id)
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $empresaId = get_empresa_id();

        // Obtém o setor
        $setor = $this->setorModel->findById($id);

        // Verifica se o setor existe e pertence à empresa do usuário logado
        if (!$setor || $setor['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Setor não encontrado.');
            redirect('setores/admin');
            return;
        }

        // Verifica se há chamados ou usuários associados ao setor
        $totalChamados = $this->setorModel->contarChamados($id);
        $totalUsuarios = $this->setorModel->contarUsuarios($id);

        if ($totalChamados > 0 || $totalUsuarios > 0) {
            $mensagem = 'Não é possível remover este setor pois ';
            $itens = [];

            if ($totalChamados > 0) {
                $itens[] = 'existem ' . $totalChamados . ' chamado(s) associado(s) a ele';
            }

            if ($totalUsuarios > 0) {
                $itens[] = 'existem ' . $totalUsuarios . ' usuário(s) associado(s) a ele';
            }

            $mensagem .= implode(' e ', $itens) . '.';
            set_flash_message('error', $mensagem);
            redirect('setores/admin');
            return;
        }

        // Marca o setor como removido em vez de excluir permanentemente
        $data = [
            'removido' => 1,
            'ativo' => 0,
            'removido_por' => get_user_id(),
            'data_remocao' => date('Y-m-d H:i:s')
        ];

        if ($this->setorModel->update($id, $data)) {
            set_flash_message('success', 'Setor removido com sucesso.');
        } else {
            set_flash_message('error', 'Erro ao remover setor.');
        }

        redirect('setores/admin');
    }

    /**
     * Restaura um setor removido
     * Apenas para administradores
     */
    public function restaurar($id)
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $empresaId = get_empresa_id();

        // Obtém o setor
        $setor = $this->setorModel->findById($id);

        // Verifica se o setor existe e pertence à empresa do usuário logado
        if (!$setor || $setor['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Setor não encontrado.');
            redirect('setores/admin');
            return;
        }

        // Verifica se o setor está realmente removido
        if (!isset($setor['removido']) || !$setor['removido']) {
            set_flash_message('error', 'Este setor não está removido.');
            redirect('setores/admin');
            return;
        }

        // Restaura o setor
        $data = [
            'ativo' => 1,
            'removido' => 0,
            'removido_por' => null,
            'data_remocao' => null
        ];

        if ($this->setorModel->update($id, $data)) {
            set_flash_message('success', 'Setor restaurado com sucesso.');
        } else {
            set_flash_message('error', 'Erro ao restaurar setor.');
        }

        redirect('setores/admin?mostrar_removidos=1');
    }

    /**
     * Ativa/desativa um setor
     * Apenas para administradores
     */
    public function toggle($id)
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $empresaId = get_empresa_id();

        // Obtém o setor
        $setor = $this->setorModel->findById($id);

        // Verifica se o setor existe e pertence à empresa do usuário logado
        if (!$setor || $setor['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Setor não encontrado.');
            redirect('setores/admin');
            return;
        }

        // Alterna o status
        $novoStatus = $setor['ativo'] ? 0 : 1;

        if ($this->setorModel->update($id, ['ativo' => $novoStatus])) {
            $mensagem = $novoStatus ? 'Setor ativado com sucesso.' : 'Setor desativado com sucesso.';
            set_flash_message('success', $mensagem);
        } else {
            set_flash_message('error', 'Erro ao alterar status do setor.');
        }

        redirect('setores/admin');
    }

    /**
     * Gerencia usuários do setor
     * Apenas para administradores
     */
    public function usuarios($id)
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        $empresaId = get_empresa_id();

        // Obtém o setor
        $setor = $this->setorModel->findById($id);

        // Verifica se o setor existe e pertence à empresa do usuário logado
        if (!$setor || $setor['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Setor não encontrado.');
            redirect('setores/admin');
            return;
        }

        // Obtém todos os usuários da empresa
        require_once ROOT_DIR . '/app/models/Usuario.php';
        $usuarioModel = new Usuario();
        $usuarios = $usuarioModel->findAll(
            'empresa_id = :empresa_id AND ativo = 1 AND (removido = 0 OR removido IS NULL)',
            ['empresa_id' => $empresaId],
            'nome ASC'
        );

        // Obtém os usuários associados ao setor
        try {
            $sql = "SELECT us.*, u.nome, u.email, u.cargo, us.principal 
                    FROM usuarios_setores us 
                    JOIN usuarios u ON us.usuario_id = u.id 
                    WHERE us.setor_id = :setor_id";

            $usuariosSetor = $this->setorModel->executeQuery($sql, ['setor_id' => $id]);
        } catch (Exception $e) {
            // Se a tabela não existir, define como array vazio
            $usuariosSetor = [];
        }

        // Marca os usuários que já estão associados
        foreach ($usuarios as &$usuario) {
            $usuario['associado'] = false;
            $usuario['principal'] = false;
            foreach ($usuariosSetor as $usuarioSetor) {
                if ($usuario['id'] == $usuarioSetor['usuario_id']) {
                    $usuario['associado'] = true;
                    $usuario['principal'] = $usuarioSetor['principal'];
                    break;
                }
            }
        }

        $this->render('setores/admin/usuarios', [
            'setor' => $setor,
            'usuarios' => $usuarios,
            'usuariosSetor' => $usuariosSetor
        ]);
    }

    /**
     * Associa/desassocia um usuário ao setor
     * Apenas para administradores
     */
    public function associarUsuario()
    {
        // Verifica se o usuário é administrador
        if (!is_admin()) {
            set_flash_message('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
            redirect('setores/visualizacao');
            return;
        }

        // Obtém os dados do formulário
        $data = $this->getPostData();

        // Valida os campos obrigatórios
        $requiredFields = [
            'setor_id' => 'Setor',
            'usuario_id' => 'Usuário'
        ];

        $errors = $this->validateRequired($data, $requiredFields);

        if (!empty($errors)) {
            set_flash_message('error', implode('<br>', $errors));
            redirect('setores/usuarios/' . $data['setor_id']);
            return;
        }

        $empresaId = get_empresa_id();

        // Verifica se o setor existe e pertence à empresa do usuário logado
        $setor = $this->setorModel->findById($data['setor_id']);
        if (!$setor || $setor['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Setor não encontrado.');
            redirect('setores/admin');
            return;
        }

        // Verifica se o usuário existe e pertence à empresa do usuário logado
        require_once ROOT_DIR . '/app/models/Usuario.php';
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findById($data['usuario_id']);
        if (!$usuario || $usuario['empresa_id'] != $empresaId) {
            set_flash_message('error', 'Usuário não encontrado.');
            redirect('setores/usuarios/' . $data['setor_id']);
            return;
        }

        try {
            // Verifica se a tabela usuarios_setores existe
            if ($this->setorModel->tableExists('usuarios_setores')) {
                // Verifica se o usuário já está associado ao setor
                $sql = "SELECT * FROM usuarios_setores WHERE usuario_id = :usuario_id AND setor_id = :setor_id";
                $usuarioSetor = $this->setorModel->executeQuerySingle($sql, [
                    'usuario_id' => $data['usuario_id'],
                    'setor_id' => $data['setor_id']
                ]);

                // Se estiver associando
                if (isset($data['associar']) && $data['associar'] == 1) {
                    // Se já estiver associado, atualiza o status de principal
                    if ($usuarioSetor) {
                        $sql = "UPDATE usuarios_setores SET principal = :principal WHERE usuario_id = :usuario_id AND setor_id = :setor_id";
                        $this->setorModel->executeUpdate($sql, [
                            'principal' => isset($data['principal']) ? 1 : 0,
                            'usuario_id' => $data['usuario_id'],
                            'setor_id' => $data['setor_id']
                        ]);
                        set_flash_message('success', 'Usuário atualizado no setor com sucesso.');
                    } else {
                        // Se não estiver associado, insere
                        $sql = "INSERT INTO usuarios_setores (usuario_id, setor_id, principal, criado_por, criado_em) VALUES (:usuario_id, :setor_id, :principal, :criado_por, :criado_em)";
                        $this->setorModel->executeUpdate($sql, [
                            'usuario_id' => $data['usuario_id'],
                            'setor_id' => $data['setor_id'],
                            'principal' => isset($data['principal']) ? 1 : 0,
                            'criado_por' => get_user_id(),
                            'criado_em' => date('Y-m-d H:i:s')
                        ]);
                        set_flash_message('success', 'Usuário associado ao setor com sucesso.');
                    }
                } else {
                    // Se estiver desassociando
                    if ($usuarioSetor) {
                        $sql = "DELETE FROM usuarios_setores WHERE usuario_id = :usuario_id AND setor_id = :setor_id";
                        $this->setorModel->executeUpdate($sql, [
                            'usuario_id' => $data['usuario_id'],
                            'setor_id' => $data['setor_id']
                        ]);
                        set_flash_message('success', 'Usuário desassociado do setor com sucesso.');
                    } else {
                        set_flash_message('error', 'Usuário não está associado ao setor.');
                    }
                }
            } else {
                // Se a tabela não existir, cria a tabela
                $sql = "CREATE TABLE IF NOT EXISTS `usuarios_setores` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `usuario_id` int(11) NOT NULL,
                    `setor_id` int(11) NOT NULL,
                    `principal` tinyint(1) DEFAULT 0,
                    `criado_por` int(11) DEFAULT NULL,
                    `criado_em` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uk_usuario_setor` (`usuario_id`, `setor_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

                $this->setorModel->executeRawQuery($sql);

                $sql = "INSERT INTO usuarios_setores (usuario_id, setor_id, principal, criado_por, criado_em) VALUES (:usuario_id, :setor_id, :principal, :criado_por, :criado_em)";
                $this->setorModel->executeUpdate($sql, [
                    'usuario_id' => $data['usuario_id'],
                    'setor_id' => $data['setor_id'],
                    'principal' => isset($data['principal']) ? 1 : 0,
                    'criado_por' => get_user_id(),
                    'criado_em' => date('Y-m-d H:i:s')
                ]);
                set_flash_message('success', 'Usuário associado ao setor com sucesso.');
            }
        } catch (Exception $e) {
            set_flash_message('error', 'Erro ao associar/desassociar usuário: ' . $e->getMessage());
        }

        redirect('setores/usuarios/' . $data['setor_id']);
    }
}
