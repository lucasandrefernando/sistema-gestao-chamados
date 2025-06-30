<div class="licencas-module">
    <div class="licenca-header">
        <div class="header-title">
            <h1><i class="fas fa-key"></i> <?= $titulo ?></h1>
            <p class="subtitle">
                <?= $acao == 'criar' ? 'Adicione uma nova licença para uma empresa' : 'Edite as informações da licença selecionada' ?>
            </p>
        </div>

        <a href="<?= base_url('licencas') ?>" class="licenca-form-btn licenca-form-btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="licenca-form">
        <div class="licenca-form-header">
            <div class="licenca-form-icon">
                <i class="fas fa-key"></i>
            </div>
            <h2 class="licenca-form-title">
                <?= $acao == 'criar' ? 'Adicionar Nova Licença' : 'Editar Licença' ?>
            </h2>
        </div>

        <form id="licenca-form" action="<?= base_url('licencas/' . ($acao == 'criar' ? 'store' : 'update/' . $licenca['id'])) ?>" method="post">
            <div class="licenca-form-body">
                <div class="licenca-form-group">
                    <label for="empresa_id" class="licenca-form-label">
                        Empresa <span class="required">*</span>
                    </label>
                    <select class="licenca-form-control licenca-form-select" id="empresa_id" name="empresa_id" required>
                        <option value="">Selecione uma empresa</option>
                        <?php foreach ($empresas as $empresa): ?>
                            <option value="<?= $empresa['id'] ?>" <?= (isset($licenca['empresa_id']) && $licenca['empresa_id'] == $empresa['id']) ? 'selected' : '' ?>>
                                <?= $empresa['nome'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="licenca-form-text">
                        Selecione a empresa que receberá a licença. Cada empresa pode ter apenas uma licença.
                    </div>
                </div>

                <div class="licenca-form-group">
                    <label for="quantidade" class="licenca-form-label">
                        Quantidade de Licenças <span class="required">*</span>
                    </label>
                    <input type="number" class="licenca-form-control" id="quantidade" name="quantidade" value="<?= $licenca['quantidade'] ?? '1' ?>" min="1" required>
                    <div class="licenca-form-text">
                        Número de licenças que serão disponibilizadas para a empresa. Cada licença permite um usuário simultâneo.
                    </div>
                </div>

                <div class="licenca-form-group">
                    <label class="licenca-form-label">
                        Período de Validade <span class="required">*</span>
                    </label>

                    <div class="licenca-form-dates">
                        <div class="licenca-form-date">
                            <label for="data_inicio" class="licenca-form-label">Data de Início</label>
                            <input type="date" class="licenca-form-control" id="data_inicio" name="data_inicio" value="<?= isset($licenca['data_inicio']) ? date('Y-m-d', strtotime($licenca['data_inicio'])) : date('Y-m-d') ?>" required>
                        </div>

                        <div class="licenca-form-date-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>

                        <div class="licenca-form-date">
                            <label for="data_fim" class="licenca-form-label">Data de Fim</label>
                            <input type="date" class="licenca-form-control" id="data_fim" name="data_fim" value="<?= isset($licenca['data_fim']) ? date('Y-m-d', strtotime($licenca['data_fim'])) : date('Y-m-d', strtotime('+1 year')) ?>" required>
                        </div>
                    </div>

                    <div class="text-center mt-3">
                        <span class="licenca-form-duration">
                            <i class="fas fa-clock"></i> Duração: <span id="duracao-licenca">Calculando...</span>
                        </span>
                    </div>

                    <div class="licenca-form-text">
                        Defina o período em que as licenças estarão disponíveis para uso.
                    </div>
                </div>

                <?php if ($acao == 'editar'): ?>
                    <div class="licenca-form-group">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1" <?= (isset($licenca['ativo']) && $licenca['ativo'] == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ativo">Licença ativa</label>
                        </div>
                        <div class="licenca-form-text">
                            Desmarque para desativar temporariamente esta licença.
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="licenca-form-footer">
                <a href="<?= base_url('licencas') ?>" class="licenca-form-btn licenca-form-btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="licenca-form-btn licenca-form-btn-primary">
                    <i class="fas fa-save"></i> <?= $acao == 'criar' ? 'Criar Licença' : 'Salvar Alterações' ?>
                </button>
            </div>
        </form>
    </div>
</div>