<!-- Cabeçalho da Página -->
<div class="dashboard-header">
    <div class="header-title">
        <h1><i class="fas fa-edit"></i> <?= $titulo ?></h1>
        <p class="subtitle"><?= $acao == 'criar' ? 'Preencha os dados para criar um novo setor' : 'Edite as informações do setor conforme necessário' ?></p>
    </div>
    <div class="header-actions">
        <a href="<?= base_url('setores') ?>" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            <span>Voltar</span>
        </a>
    </div>
</div>

<!-- Formulário de Setor -->
<div class="form-container">
    <form action="<?= base_url('setores/' . ($acao == 'criar' ? 'store' : 'update/' . $setor['id'])) ?>" method="post" class="setor-form">

        <!-- Informações Básicas -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-info-circle"></i>
                <h2>Informações Básicas</h2>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="nome">
                        Nome do Setor <span class="required">*</span>
                    </label>
                    <div class="input-with-icon">
                        <i class="fas fa-sitemap"></i>
                        <input type="text" id="nome" name="nome" value="<?= $setor['nome'] ?? '' ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="empresa_nome">Empresa</label>
                    <div class="input-with-icon">
                        <i class="fas fa-building"></i>
                        <input type="text" id="empresa_nome" value="<?= $_SESSION['empresa_nome'] ?>" readonly class="readonly">
                        <input type="hidden" name="empresa_id" value="<?= get_empresa_id() ?>">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group full-width">
                    <label for="descricao">Descrição</label>
                    <div class="input-with-icon textarea-container">
                        <i class="fas fa-align-left"></i>
                        <textarea id="descricao" name="descricao" rows="4"><?= $setor['descricao'] ?? '' ?></textarea>
                    </div>
                    <div class="form-hint">
                        <i class="fas fa-info-circle"></i>
                        <span>Uma breve descrição sobre o setor e suas responsabilidades.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configurações -->
        <div class="form-section">
            <div class="section-header">
                <i class="fas fa-cog"></i>
                <h2>Configurações</h2>
            </div>

            <div class="form-row">
                <div class="form-group full-width">
                    <div class="toggle-card">
                        <div class="toggle-switch">
                            <input type="checkbox" id="ativo" name="ativo" value="1" <?= (isset($setor['ativo']) && $setor['ativo']) || !isset($setor['ativo']) ? 'checked' : '' ?>>
                            <label for="ativo" class="toggle-label"></label>
                        </div>
                        <div class="toggle-content">
                            <label for="ativo" class="toggle-title">Setor Ativo</label>
                            <p class="toggle-description">Setores inativos não aparecem nas listas de seleção.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="form-actions">
            <a href="<?= base_url('setores') ?>" class="btn-secondary">
                <i class="fas fa-times"></i>
                <span>Cancelar</span>
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i>
                <span><?= $acao == 'criar' ? 'Criar Setor' : 'Salvar Alterações' ?></span>
            </button>
        </div>
    </form>
</div>