<div class="usuarios-setor-container">
    <div class="usuarios-setor-header">
        <div class="header-content">
            <div class="setor-avatar" style="background-color: <?= gerarCorAvatar($setor['nome']) ?>">
                <?= strtoupper(substr($setor['nome'], 0, 1)) ?>
            </div>
            <div class="header-titles">
                <h1 class="usuarios-setor-title">Usuários do Setor</h1>
                <h2 class="setor-nome"><?= $setor['nome'] ?></h2>
            </div>
        </div>
        <div class="usuarios-setor-actions">
            <a href="<?= base_url('setores') ?>" class="usuarios-setor-btn back-btn">
                <i class="fas fa-arrow-left"></i> Voltar para Setores
            </a>
        </div>
    </div>

    <div class="usuarios-setor-card info-card">
        <div class="card-header">
            <h5 class="card-title">Informações do Setor</h5>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Nome</div>
                    <div class="info-value"><?= $setor['nome'] ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Empresa</div>
                    <div class="info-value"><?= $_SESSION['empresa_nome'] ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <?php if ($setor['ativo']): ?>
                            <span class="status-badge active">Ativo</span>
                        <?php else: ?>
                            <span class="status-badge inactive">Inativo</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Descrição</div>
                    <div class="info-value"><?= $setor['descricao'] ?? '<span class="text-muted">Sem descrição</span>' ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="usuarios-setor-card">
        <div class="card-header">
            <div class="header-content">
                <h5 class="card-title">Usuários Associados</h5>
                <div class="search-container">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="usuariosSearch" class="search-input" placeholder="Buscar usuários...">
                    </div>
                </div>
            </div>
            <button type="button" class="usuarios-setor-btn add-btn" data-bs-toggle="modal" data-bs-target="#associarUsuarioModal">
                <i class="fas fa-user-plus"></i> Associar Usuário
            </button>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="usuarios-table" id="usuariosTable">
                    <thead>
                        <tr>
                            <th class="col-id">#</th>
                            <th class="col-nome">Nome</th>
                            <th class="col-email">E-mail</th>
                            <th class="col-cargo">Cargo</th>
                            <th class="col-principal">Principal</th>
                            <th class="col-acoes">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($usuariosSetor) && !empty($usuariosSetor)): ?>
                            <?php foreach ($usuariosSetor as $usuarioSetor): ?>
                                <tr>
                                    <td class="col-id"><?= $usuarioSetor['usuario_id'] ?></td>
                                    <td class="col-nome">
                                        <div class="usuario-info">
                                            <div class="usuario-avatar" style="background-color: <?= gerarCorAvatar($usuarioSetor['nome']) ?>">
                                                <?= strtoupper(substr($usuarioSetor['nome'], 0, 1)) ?>
                                            </div>
                                            <span class="usuario-nome"><?= $usuarioSetor['nome'] ?></span>
                                        </div>
                                    </td>
                                    <td class="col-email">
                                        <div class="email-container">
                                            <i class="fas fa-envelope email-icon"></i>
                                            <span class="email-text"><?= $usuarioSetor['email'] ?></span>
                                        </div>
                                    </td>
                                    <td class="col-cargo"><?= $usuarioSetor['cargo'] ?? '<span class="text-muted">Não definido</span>' ?></td>
                                    <td class="col-principal">
                                        <?php if ($usuarioSetor['principal']): ?>
                                            <span class="principal-badge yes">
                                                <i class="fas fa-check-circle"></i> Principal
                                            </span>
                                        <?php else: ?>
                                            <span class="principal-badge no">
                                                <i class="fas fa-minus-circle"></i> Secundário
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-acoes">
                                        <div class="acoes-grupo">
                                            <button type="button" class="acao-btn editar"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editarAssociacaoModal"
                                                data-usuario-id="<?= $usuarioSetor['usuario_id'] ?>"
                                                data-usuario-nome="<?= $usuarioSetor['nome'] ?>"
                                                data-principal="<?= $usuarioSetor['principal'] ?>">
                                                <i class="fas fa-edit"></i>
                                                <span class="tooltip-text">Editar</span>
                                            </button>
                                            <button type="button" class="acao-btn remover"
                                                data-bs-toggle="modal"
                                                data-bs-target="#desassociarUsuarioModal"
                                                data-usuario-id="<?= $usuarioSetor['usuario_id'] ?>"
                                                data-usuario-nome="<?= $usuarioSetor['nome'] ?>">
                                                <i class="fas fa-unlink"></i>
                                                <span class="tooltip-text">Desassociar</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="empty-row">
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <p>Nenhum usuário associado a este setor.</p>
                                        <button type="button" class="usuarios-setor-btn add-btn-empty" data-bs-toggle="modal" data-bs-target="#associarUsuarioModal">
                                            <i class="fas fa-user-plus"></i> Associar Usuário
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Associar Usuário -->
<div class="modal fade" id="associarUsuarioModal" tabindex="-1" aria-labelledby="associarUsuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-primary">
                <h5 class="modal-title" id="associarUsuarioModalLabel">Associar Usuário ao Setor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-search-container">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="modalUsuariosSearch" class="search-input" placeholder="Buscar usuários disponíveis...">
                    </div>
                </div>

                <div class="table-container">
                    <table class="usuarios-table" id="usuariosDisponiveis">
                        <thead>
                            <tr>
                                <th class="col-id">#</th>
                                <th class="col-nome">Nome</th>
                                <th class="col-email">E-mail</th>
                                <th class="col-cargo">Cargo</th>
                                <th class="col-status">Status</th>
                                <th class="col-acoes">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($usuarios) && !empty($usuarios)): ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr class="<?= $usuario['associado'] ? 'row-associado' : '' ?>">
                                        <td class="col-id"><?= $usuario['id'] ?></td>
                                        <td class="col-nome">
                                            <div class="usuario-info">
                                                <div class="usuario-avatar" style="background-color: <?= gerarCorAvatar($usuario['nome']) ?>">
                                                    <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                                                </div>
                                                <span class="usuario-nome"><?= $usuario['nome'] ?></span>
                                            </div>
                                        </td>
                                        <td class="col-email">
                                            <div class="email-container">
                                                <i class="fas fa-envelope email-icon"></i>
                                                <span class="email-text"><?= $usuario['email'] ?></span>
                                            </div>
                                        </td>
                                        <td class="col-cargo"><?= $usuario['cargo'] ?? '<span class="text-muted">Não definido</span>' ?></td>
                                        <td class="col-status">
                                            <?php if ($usuario['associado']): ?>
                                                <span class="status-badge associado">
                                                    <i class="fas fa-link"></i> Associado
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge nao-associado">
                                                    <i class="fas fa-unlink"></i> Não Associado
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="col-acoes">
                                            <?php if ($usuario['associado']): ?>
                                                <form action="<?= base_url('setores/associarUsuario') ?>" method="post" class="form-associacao">
                                                    <input type="hidden" name="setor_id" value="<?= $setor['id'] ?>">
                                                    <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                                    <input type="hidden" name="associar" value="0">
                                                    <button type="submit" class="acao-btn desassociar">
                                                        <i class="fas fa-unlink"></i>
                                                        <span class="acao-texto">Desassociar</span>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form action="<?= base_url('setores/associarUsuario') ?>" method="post" class="form-associacao">
                                                    <input type="hidden" name="setor_id" value="<?= $setor['id'] ?>">
                                                    <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                                    <input type="hidden" name="associar" value="1">
                                                    <div class="associar-container">
                                                        <div class="principal-check">
                                                            <input class="principal-input" type="checkbox" name="principal" id="principal_<?= $usuario['id'] ?>" value="1">
                                                            <label class="principal-label" for="principal_<?= $usuario['id'] ?>">
                                                                Principal
                                                            </label>
                                                        </div>
                                                        <button type="submit" class="acao-btn associar">
                                                            <i class="fas fa-link"></i>
                                                            <span class="acao-texto">Associar</span>
                                                        </button>
                                                    </div>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="empty-row">
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="fas fa-users"></i>
                                            </div>
                                            <p>Nenhum usuário disponível para associação.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn cancel" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Associação -->
<div class="modal fade" id="editarAssociacaoModal" tabindex="-1" aria-labelledby="editarAssociacaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-info">
                <h5 class="modal-title" id="editarAssociacaoModalLabel">Editar Associação</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('setores/associarUsuario') ?>" method="post" id="formEditarAssociacao">
                <div class="modal-body">
                    <input type="hidden" name="setor_id" value="<?= $setor['id'] ?>">
                    <input type="hidden" name="usuario_id" id="editarUsuarioId">
                    <input type="hidden" name="associar" value="1">

                    <div class="modal-icon-container">
                        <div class="modal-icon info">
                            <i class="fas fa-user-edit"></i>
                        </div>
                    </div>

                    <div class="modal-info-text">
                        Editando associação do usuário <strong id="editarUsuarioNome"></strong> com o setor <strong><?= $setor['nome'] ?></strong>.
                    </div>

                    <div class="principal-toggle-container">
                        <div class="principal-toggle">
                            <input type="checkbox" id="editarPrincipal" name="principal" value="1" class="principal-toggle-input">
                            <label for="editarPrincipal" class="principal-toggle-label">
                                <span class="principal-toggle-inner"></span>
                                <span class="principal-toggle-switch"></span>
                            </label>
                        </div>
                        <div class="principal-toggle-text">
                            <div class="principal-toggle-title">Usuário Principal do Setor</div>
                            <div class="principal-toggle-desc">
                                <i class="fas fa-info-circle"></i> Usuários principais são os responsáveis pelo setor.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-btn cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="modal-btn confirm-info">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Desassociar Usuário -->
<div class="modal fade" id="desassociarUsuarioModal" tabindex="-1" aria-labelledby="desassociarUsuarioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header modal-danger">
                <h5 class="modal-title" id="desassociarUsuarioModalLabel">Confirmar Desassociação</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('setores/associarUsuario') ?>" method="post" id="formDesassociarUsuario">
                <div class="modal-body">
                    <input type="hidden" name="setor_id" value="<?= $setor['id'] ?>">
                    <input type="hidden" name="usuario_id" id="desassociarUsuarioId">
                    <input type="hidden" name="associar" value="0">

                    <div class="modal-icon-container">
                        <div class="modal-icon danger">
                            <i class="fas fa-unlink"></i>
                        </div>
                    </div>

                    <h5 class="modal-message">Tem certeza que deseja desassociar este usuário do setor?</h5>

                    <div class="modal-alert warning">
                        <i class="fas fa-info-circle"></i>
                        <span>O usuário <strong id="desassociarUsuarioNome"></strong> não terá mais acesso aos recursos deste setor.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="modal-btn cancel" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="modal-btn confirm-danger">Confirmar Desassociação</button>
                </div>
            </form>
        </div>
    </div>
</div>