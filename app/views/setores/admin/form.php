<?php

/**
 * Formulário de Administração de Setores
 * Esta página permite que administradores criem ou editem setores
 * 
 * @version 2.0
 * @author Desenvolvedor
 */
?>

<div class="setores-admin-v2">
    <div class="admin-header">
        <div class="header-content">
            <h1 class="admin-title">
                <i class="fas <?= $acao == 'criar' ? 'fa-plus-circle' : 'fa-edit' ?>"></i>
                <?= $titulo ?>
            </h1>
            <p class="admin-subtitle">
                <?= $acao == 'criar' ? 'Crie um novo setor para a organização' : 'Edite as informações do setor selecionado' ?>
            </p>
        </div>
        <div class="header-actions">
            <a href="<?= base_url('setores/admin') ?>" class="btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Voltar
            </a>
        </div>
    </div>

    <div class="admin-form-container">
        <div class="form-card">
            <div class="card-body">
                <form id="adminSetorForm" action="<?= base_url('setores/' . ($acao == 'criar' ? 'store' : 'update/' . $setor['id'])) ?>" method="post">
                    <div class="form-group">
                        <label for="nome" class="form-label">Nome do Setor <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-building"></i>
                            </span>
                            <input type="text" class="form-control" id="nome" name="nome"
                                value="<?= isset($setor['nome']) ? htmlspecialchars($setor['nome']) : '' ?>"
                                placeholder="Digite o nome do setor" required>
                        </div>
                        <div class="form-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao"
                            rows="4" placeholder="Descreva as funções e responsabilidades deste setor"><?= isset($setor['descricao']) ? htmlspecialchars($setor['descricao']) : '' ?></textarea>
                        <div class="form-feedback"></div>
                    </div>

                    <div class="form-group switch-group">
                        <label class="switch-label">
                            <span class="switch-title">Status do Setor</span>
                            <div class="switch-container">
                                <label class="switch">
                                    <input type="checkbox" id="ativo" name="ativo" value="1"
                                        <?= !isset($setor['ativo']) || $setor['ativo'] ? 'checked' : '' ?>>
                                    <span class="slider round"></span>
                                </label>
                                <span class="switch-text" id="statusText">
                                    <?= !isset($setor['ativo']) || $setor['ativo'] ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </div>
                        </label>
                    </div>

                    <div class="form-actions">
                        <a href="<?= base_url('setores/admin') ?>" class="btn-secondary">
                            <i class="fas fa-times me-2"></i> Cancelar
                        </a>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save me-2"></i> Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>