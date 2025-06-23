<div class="usuarios-container">
    <div class="user-form-container">
        <!-- Cabeçalho da página -->
        <div class="page-header">
            <div class="header-content">
                <div class="header-title">
                    <h1>
                        <i class="fas fa-user-<?= $acao == 'criar' ? 'plus' : 'edit' ?>"></i>
                        <?= $titulo ?>
                    </h1>
                    <p class="subtitle">
                        <?= $acao == 'criar' ? 'Preencha os dados para criar um novo usuário' : 'Edite as informações do usuário' ?>
                    </p>
                </div>
                <div class="header-actions">
                    <a href="<?= base_url('usuarios') ?>" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        <span>Voltar</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Formulário de usuário -->
        <div class="form-card">
            <div class="form-card-header">
                <h2>Informações do Usuário</h2>
                <?php if ($acao == 'editar' && isset($usuario['nome'])): ?>
                    <div class="user-avatar-preview">
                        <div class="avatar-circle <?= isset($usuario['admin']) && $usuario['admin'] ? (isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master' ? 'admin-master' : 'admin') : 'user' ?>">
                            <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-card-body">
                <form action="<?= base_url('usuarios/' . ($acao == 'criar' ? 'store' : 'update/' . $usuario['id'])) ?>" method="post" class="user-form">
                    <div class="form-grid">
                        <!-- Nome e Email -->
                        <div class="form-group">
                            <label for="nome">
                                Nome <span class="required">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" id="nome" name="nome" value="<?= $usuario['nome'] ?? '' ?>" required placeholder="Nome completo">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">
                                E-mail <span class="required">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" id="email" name="email" value="<?= $usuario['email'] ?? '' ?>" required placeholder="exemplo@email.com">
                            </div>
                        </div>

                        <!-- Senha e Cargo -->
                        <div class="form-group">
                            <label for="senha">
                                <?= $acao == 'criar' ? 'Senha <span class="required">*</span>' : 'Nova Senha <span class="optional">(opcional)</span>' ?>
                            </label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" id="senha" name="senha" <?= $acao == 'criar' ? 'required' : '' ?> placeholder="<?= $acao == 'criar' ? 'Mínimo 8 caracteres' : 'Deixe em branco para manter a atual' ?>">
                                <button type="button" class="toggle-password" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <?php if ($acao == 'criar'): ?>
                                <div class="form-hint">
                                    <i class="fas fa-info-circle"></i> A senha deve ter pelo menos 8 caracteres.
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="cargo">
                                Cargo <span class="optional">(opcional)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="fas fa-briefcase"></i>
                                </span>
                                <input type="text" id="cargo" name="cargo" value="<?= $usuario['cargo'] ?? '' ?>" placeholder="Ex: Analista de Suporte">
                            </div>
                        </div>

                        <!-- Empresa e Tipo de Usuário -->
                        <div class="form-group">
                            <label for="empresa_nome">
                                Empresa
                            </label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="fas fa-building"></i>
                                </span>
                                <input type="text" id="empresa_nome" value="<?= $_SESSION['empresa_nome'] ?>" readonly class="readonly">
                                <input type="hidden" name="empresa_id" value="<?= get_empresa_id() ?>">
                            </div>
                            <div class="form-hint">
                                <i class="fas fa-info-circle"></i> Os usuários são criados na empresa atual.
                            </div>
                        </div>

                        <div class="form-group">
                            <!-- Espaço vazio para manter o grid alinhado -->
                        </div>
                    </div>

                    <!-- Seção de Permissões -->
                    <div class="permissions-section">
                        <h3 class="section-title">Permissões de Acesso</h3>

                        <div class="permission-toggle">
                            <div class="toggle-header">
                                <div class="toggle-info">
                                    <h4>Usuário Administrador</h4>
                                    <p>Concede permissões administrativas ao usuário</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" id="admin" name="admin" value="1" <?= (isset($usuario['admin']) && $usuario['admin']) ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>

                        <div id="adminTipoContainer" class="admin-tipo-container">
                            <?php if (is_admin_master()): ?>
                                <div class="admin-tipo-header">
                                    <h4>Tipo de Administrador</h4>
                                    <p>Selecione o nível de acesso administrativo</p>
                                </div>

                                <div class="admin-tipo-options">
                                    <label class="admin-tipo-card">
                                        <input type="radio" name="admin_tipo" value="regular" <?= (!isset($usuario['admin_tipo']) || $usuario['admin_tipo'] == 'regular') ? 'checked' : '' ?>>
                                        <div class="admin-tipo-content">
                                            <div class="admin-tipo-icon regular">
                                                <i class="fas fa-user-cog"></i>
                                            </div>
                                            <div class="admin-tipo-details">
                                                <h5>Administrador Regular</h5>
                                                <ul>
                                                    <li>Gerenciar usuários comuns</li>
                                                    <li>Visualizar relatórios básicos</li>
                                                    <li>Acesso limitado às configurações</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </label>

                                    <label class="admin-tipo-card">
                                        <input type="radio" name="admin_tipo" value="master" <?= (isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master') ? 'checked' : '' ?>>
                                        <div class="admin-tipo-content">
                                            <div class="admin-tipo-icon master">
                                                <i class="fas fa-user-shield"></i>
                                            </div>
                                            <div class="admin-tipo-details">
                                                <h5>Administrador Master</h5>
                                                <ul>
                                                    <li>Acesso completo ao sistema</li>
                                                    <li>Gerenciar todos os usuários</li>
                                                    <li>Configurações avançadas</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            <?php else: ?>
                                <!-- Para administradores regulares, apenas mostra o tipo sem opção de alterar -->
                                <?php if (isset($usuario['admin_tipo']) && $usuario['admin_tipo'] == 'master'): ?>
                                    <input type="hidden" name="admin_tipo" value="master">
                                    <div class="admin-tipo-info master">
                                        <div class="info-icon">
                                            <i class="fas fa-shield-alt"></i>
                                        </div>
                                        <div class="info-content">
                                            <h4>Administrador Master</h4>
                                            <p>Este usuário tem acesso completo ao sistema, incluindo todas as configurações e gerenciamento de usuários.</p>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <input type="hidden" name="admin_tipo" value="regular">
                                    <div class="admin-tipo-info regular">
                                        <div class="info-icon">
                                            <i class="fas fa-user-cog"></i>
                                        </div>
                                        <div class="info-content">
                                            <h4>Administrador Regular</h4>
                                            <p>Este usuário terá permissões para gerenciar usuários comuns e acessar relatórios básicos.</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Botões de ação -->
                    <div class="form-actions">
                        <a href="<?= base_url('usuarios') ?>" class="btn-outline">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> <?= $acao == 'criar' ? 'Criar Usuário' : 'Salvar Alterações' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>  