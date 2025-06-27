<?php

/**
 * View para gerenciamento de usuários do setor
 * 
 * Esta view utiliza classes CSS e IDs com prefixo 'su-' para garantir
 * que não haja conflito com outros estilos do sistema.
 * 
 * Autor: [Nome do Desenvolvedor]
 * Data: [Data de Criação/Atualização]
 */
?>

<!-- Container principal com escopo isolado -->
<div class="su-container">
    <!-- Cabeçalho da página -->
    <div class="su-header">
        <div class="su-header-info">
            <h1 class="su-title">
                <i class="fas fa-users-cog"></i>
                Gerenciamento de Usuários
            </h1>
            <div class="su-subtitle">
                Setor: <?= htmlspecialchars($setor['nome']) ?>
                <?php if ($setor['ativo']): ?>
                    <span class="su-badge su-badge-success">Ativo</span>
                <?php else: ?>
                    <span class="su-badge su-badge-secondary">Inativo</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="su-actions">
            <a href="<?= base_url('setores/admin') ?>" class="su-btn su-btn-outline">
                <i class="fas fa-arrow-left"></i>
                Voltar
            </a>
        </div>
    </div>

    <!-- Layout principal -->
    <div class="su-content">
        <!-- Coluna da esquerda: Informações do setor -->
        <div>
            <div class="su-card">
                <div class="su-card-header">
                    <h2 class="su-card-title">
                        <i class="fas fa-info-circle"></i>
                        Informações do Setor
                    </h2>
                </div>
                <div class="su-card-body">
                    <?php if (!empty($setor['descricao'])): ?>
                        <p class="su-description"><?= nl2br(htmlspecialchars($setor['descricao'])) ?></p>
                        <hr style="margin: 16px 0; border: 0; border-top: 1px solid #e5e7eb;">
                    <?php endif; ?>

                    <ul class="su-info-list">
                        <li class="su-info-item">
                            <div class="su-info-icon">
                                <i class="fas fa-toggle-on"></i>
                            </div>
                            <div class="su-info-content">
                                <div class="su-info-label">Status</div>
                                <div class="su-info-value">
                                    <?= $setor['ativo'] ? 'Ativo' : 'Inativo' ?>
                                </div>
                            </div>
                        </li>

                        <li class="su-info-item">
                            <div class="su-info-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="su-info-content">
                                <div class="su-info-label">Total de Usuários</div>
                                <div class="su-info-value"><?= count($usuariosSetor) ?></div>
                            </div>
                        </li>

                        <?php if (!empty($setor['email'])): ?>
                            <li class="su-info-item">
                                <div class="su-info-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="su-info-content">
                                    <div class="su-info-label">Email</div>
                                    <div class="su-info-value"><?= htmlspecialchars($setor['email']) ?></div>
                                </div>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($setor['criado_em'])): ?>
                            <li class="su-info-item">
                                <div class="su-info-icon">
                                    <i class="fas fa-calendar-plus"></i>
                                </div>
                                <div class="su-info-content">
                                    <div class="su-info-label">Criado em</div>
                                    <div class="su-info-value"><?= date('d/m/Y H:i', strtotime($setor['criado_em'])) ?></div>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Coluna da direita: Usuários associados -->
        <div>
            <div class="su-card">
                <div class="su-card-header">
                    <h2 class="su-card-title">
                        <i class="fas fa-user-check"></i>
                        Usuários Associados
                    </h2>
                    <span class="su-badge su-badge-primary"><?= count($usuariosSetor) ?></span>
                </div>
                <div class="su-card-body">
                    <?php if (!empty($usuariosSetor)): ?>
                        <!-- Barra de pesquisa -->
                        <div class="su-filter-bar">
                            <div class="su-search-box">
                                <i class="fas fa-search su-search-icon"></i>
                                <input type="text" id="su-search-associados" class="su-search-input" placeholder="Buscar usuários...">
                            </div>

                            <div class="su-checkbox-group">
                                <label class="su-checkbox-wrapper">
                                    <input type="checkbox" id="su-select-all-associados" class="su-checkbox">
                                    <span class="su-checkbox-label">Selecionar página atual</span>
                                </label>

                                <button type="button" id="su-select-all-pages-associados" class="su-btn su-btn-sm su-btn-outline">
                                    <i class="fas fa-check-double"></i> Selecionar todos os registros
                                </button>
                            </div>
                        </div>

                        <!-- Tabela de usuários associados -->
                        <div class="su-table-container">
                            <table class="su-table" id="su-table-associados">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;"></th>
                                        <th>Usuário</th>
                                        <th>Cargo</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuariosSetor as $usuarioSetor): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="su-checkbox su-user-checkbox-associado" value="<?= $usuarioSetor['usuario_id'] ?>">
                                            </td>
                                            <td>
                                                <div class="su-user-cell">
                                                    <div class="su-user-avatar" data-name="<?= htmlspecialchars($usuarioSetor['nome']) ?>"></div>
                                                    <div class="su-user-info">
                                                        <div class="su-user-name"><?= htmlspecialchars($usuarioSetor['nome']) ?></div>
                                                        <div class="su-user-email"><?= htmlspecialchars($usuarioSetor['email']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($usuarioSetor['cargo']) ?></td>
                                            <td>
                                                <form action="<?= base_url('setores/associarUsuario') ?>" method="post">
                                                    <input type="hidden" name="setor_id" value="<?= $setor['id'] ?>">
                                                    <input type="hidden" name="usuario_id" value="<?= $usuarioSetor['usuario_id'] ?>">
                                                    <input type="hidden" name="associar" value="0">
                                                    <button type="submit" class="su-btn su-btn-danger su-btn-sm su-btn-desassociar">
                                                        <i class="fas fa-unlink"></i> Desassociar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginação para usuários associados -->
                        <div class="su-pagination-container">
                            <div id="su-pagination-info-associados" class="su-pagination-info">
                                Mostrando 1-4 de <?= count($usuariosSetor) ?> usuários
                            </div>
                            <div id="su-pagination-associados" class="su-pagination"></div>
                        </div>

                        <!-- Botão flutuante para desassociação em massa -->
                        <div id="su-disassociate-floating" class="su-disassociate-floating">
                            <button type="button" id="su-bulk-disassociate-button" class="su-disassociate-btn" onclick="SetoresUsuarios.desassociarEmMassa(<?= $setor['id'] ?>)" disabled>
                                <i class="fas fa-unlink"></i>
                                Desassociar <span class="su-count" id="su-selected-count-associado">0</span> usuários
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- Estado vazio -->
                        <div class="su-empty-state">
                            <div class="su-empty-icon">
                                <i class="fas fa-users-slash"></i>
                            </div>
                            <h3 class="su-empty-title">Nenhum usuário associado</h3>
                            <p class="su-empty-description">Este setor ainda não possui usuários associados.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Associar Usuários -->
    <div class="su-card">
        <div class="su-card-header">
            <h2 class="su-card-title">
                <i class="fas fa-user-plus"></i>
                Associar Usuários
            </h2>
        </div>
        <div class="su-card-body">
            <?php
            // Filtrar apenas usuários não associados
            $usuariosDisponiveis = array_filter($usuarios, function ($usuario) {
                return !$usuario['associado'];
            });

            if (!empty($usuariosDisponiveis)):
            ?>
                <!-- Barra de filtro e seleção -->
                <div class="su-filter-bar">
                    <div class="su-search-box">
                        <i class="fas fa-search su-search-icon"></i>
                        <input type="text" id="su-search-disponiveis" class="su-search-input" placeholder="Buscar usuários disponíveis...">
                    </div>

                    <div class="su-checkbox-group">
                        <label class="su-checkbox-wrapper">
                            <input type="checkbox" id="su-select-all-disponiveis" class="su-checkbox">
                            <span class="su-checkbox-label">Selecionar página atual</span>
                        </label>

                        <button type="button" id="su-select-all-pages-disponiveis" class="su-btn su-btn-sm su-btn-outline">
                            <i class="fas fa-check-double"></i> Selecionar todos os registros
                        </button>
                    </div>
                </div>

                <!-- Tabela de usuários disponíveis -->
                <div class="su-table-container">
                    <table class="su-table" id="su-table-disponiveis">
                        <thead>
                            <tr>
                                <th style="width: 40px;"></th>
                                <th>Usuário</th>
                                <th>Cargo</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuariosDisponiveis as $usuario): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="su-checkbox su-user-checkbox-disponivel" value="<?= $usuario['id'] ?>">
                                    </td>
                                    <td>
                                        <div class="su-user-cell">
                                            <div class="su-user-avatar" data-name="<?= htmlspecialchars($usuario['nome']) ?>"></div>
                                            <div class="su-user-info">
                                                <div class="su-user-name"><?= htmlspecialchars($usuario['nome']) ?></div>
                                                <div class="su-user-email"><?= htmlspecialchars($usuario['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($usuario['cargo']) ?></td>
                                    <td>
                                        <form action="<?= base_url('setores/associarUsuario') ?>" method="post">
                                            <input type="hidden" name="setor_id" value="<?= $setor['id'] ?>">
                                            <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                            <input type="hidden" name="associar" value="1">
                                            <button type="submit" class="su-btn su-btn-primary su-btn-sm">
                                                <i class="fas fa-link"></i> Associar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginação para usuários disponíveis -->
                <div class="su-pagination-container">
                    <div id="su-pagination-info-disponiveis" class="su-pagination-info">
                        Mostrando 1-4 de <?= count($usuariosDisponiveis) ?> usuários
                    </div>
                    <div id="su-pagination-disponiveis" class="su-pagination"></div>
                </div>

                <!-- Botão flutuante para associação em massa -->
                <div id="su-mass-assign-floating" class="su-mass-assign-floating">
                    <button type="button" id="su-bulk-assign-button" class="su-mass-assign-btn" onclick="SetoresUsuarios.associarEmMassa(<?= $setor['id'] ?>)" disabled>
                        <i class="fas fa-user-plus"></i>
                        Associar <span class="su-count" id="su-selected-count-disponivel">0</span> usuários
                    </button>
                </div>
            <?php else: ?>
                <!-- Estado vazio -->
                <div class="su-empty-state">
                    <div class="su-empty-icon">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <h3 class="su-empty-title">Nenhum usuário disponível</h3>
                    <p class="su-empty-description">Não há usuários disponíveis para associar a este setor.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Dicas rápidas -->
    <div class="su-card" style="margin-top: 24px;">
        <div class="su-card-header">
            <h2 class="su-card-title">
                <i class="fas fa-lightbulb"></i>
                Dicas Rápidas
            </h2>
        </div>
        <div class="su-card-body">
            <div class="su-tips-container">
                <div class="su-tip-card" style="background-color: #f0f9ff;">
                    <div class="su-tip-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h4 class="su-tip-title">Notificações</h4>
                    <p class="su-tip-description">Todos os usuários associados a este setor receberão notificações sobre chamados direcionados a ele.</p>
                </div>

                <div class="su-tip-card" style="background-color: #f0fdf4;">
                    <div class="su-tip-icon" style="color: #16a34a;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="su-tip-title">Múltiplos Setores</h4>
                    <p class="su-tip-description">Um usuário pode estar associado a vários setores simultaneamente, facilitando o trabalho em equipes multidisciplinares.</p>
                </div>

                <div class="su-tip-card" style="background-color: #fef2f2;">
                    <div class="su-tip-icon" style="color: #dc2626;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h4 class="su-tip-title">Associação em Massa</h4>
                    <p class="su-tip-description">Selecione dois ou mais usuários para usar as opções de associação ou desassociação em massa.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Adicionar a variável base_url para o JavaScript -->
<script>
    var base_url = '<?= base_url() ?>';
</script>