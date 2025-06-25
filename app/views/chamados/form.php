<?php

/**
 * Formulário de Criação/Edição de Setores
 * Esta página permite criar ou editar setores da organização
 * 
 * @version 2.0
 * @author Desenvolvedor
 */

// Título da página
$pageTitle = isset($setor) ? 'Editar Setor' : 'Novo Setor';
$formAction = isset($setor) ? base_url('setores/atualizar/' . $setor['id']) : base_url('setores/salvar');
?>

<div class="setor-form-v2">
    <div class="form-header">
        <div class="header-content">
            <h1 class="form-title">
                <i class="fas <?= isset($setor) ? 'fa-edit' : 'fa-plus-circle' ?>"></i>
                <?= $pageTitle ?>
            </h1>
            <p class="form-subtitle">
                <?= isset($setor) ? 'Atualize as informações do setor existente' : 'Preencha os dados para criar um novo setor' ?>
            </p>
        </div>
        <div class="header-actions">
            <a href="<?= base_url('setores') ?>" class="btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Voltar
            </a>
        </div>
    </div>

    <?php if (isset($erro) && !empty($erro)): ?>
        <div class="alert-container">
            <div class="alert-error">
                <i class="fas fa-exclamation-circle alert-icon"></i>
                <div class="alert-content">
                    <h4 class="alert-title">Erro ao processar formulário</h4>
                    <p class="alert-message"><?= $erro ?></p>
                </div>
                <button type="button" class="alert-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($sucesso) && !empty($sucesso)): ?>
        <div class="alert-container">
            <div class="alert-success">
                <i class="fas fa-check-circle alert-icon"></i>
                <div class="alert-content">
                    <h4 class="alert-title">Operação realizada com sucesso</h4>
                    <p class="alert-message"><?= $sucesso ?></p>
                </div>
                <button type="button" class="alert-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form id="setorForm" action="<?= $formAction ?>" method="post" class="form-content">
            <div class="form-section">
                <h2 class="section-title">Informações Básicas</h2>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nome" class="form-label">Nome do Setor <span class="required">*</span></label>
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-building"></i>
                            </span>
                            <input type="text" id="nome" name="nome" class="form-control"
                                value="<?= isset($setor) ? htmlspecialchars($setor['nome']) : '' ?>"
                                placeholder="Ex: Recursos Humanos" required>
                        </div>
                        <div class="form-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="codigo" class="form-label">Código do Setor</label>
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-hashtag"></i>
                            </span>
                            <input type="text" id="codigo" name="codigo" class="form-control"
                                value="<?= isset($setor) ? htmlspecialchars($setor['codigo']) : '' ?>"
                                placeholder="Ex: RH-001">
                        </div>
                        <div class="form-feedback"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea id="descricao" name="descricao" class="form-control"
                        placeholder="Descreva as responsabilidades e funções deste setor..."
                        rows="4"><?= isset($setor) ? htmlspecialchars($setor['descricao']) : '' ?></textarea>
                    <div class="form-feedback"></div>
                </div>
            </div>

            <div class="form-section">
                <h2 class="section-title">Configurações</h2>

                <div class="form-row">
                    <div class="form-group">
                        <label for="responsavel" class="form-label">Responsável</label>
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-user-tie"></i>
                            </span>
                            <select id="responsavel" name="responsavel_id" class="form-control">
                                <option value="">Selecione um responsável</option>
                                <?php if (isset($usuarios) && is_array($usuarios)): ?>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <option value="<?= $usuario['id'] ?>"
                                            <?= (isset($setor) && $setor['responsavel_id'] == $usuario['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($usuario['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="setor_pai" class="form-label">Setor Superior</label>
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-sitemap"></i>
                            </span>
                            <select id="setor_pai" name="setor_pai_id" class="form-control">
                                <option value="">Nenhum (Setor Principal)</option>
                                <?php if (isset($setores) && is_array($setores)): ?>
                                    <?php foreach ($setores as $setor_pai): ?>
                                        <?php if (!isset($setor) || $setor['id'] != $setor_pai['id']): ?>
                                            <option value="<?= $setor_pai['id'] ?>"
                                                <?= (isset($setor) && $setor['setor_pai_id'] == $setor_pai['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($setor_pai['nome']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-feedback"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="form-label">Email de Contato</label>
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" id="email" name="email" class="form-control"
                                value="<?= isset($setor) ? htmlspecialchars($setor['email']) : '' ?>"
                                placeholder="Ex: setor@empresa.com">
                        </div>
                        <div class="form-feedback"></div>
                    </div>

                    <div class="form-group">
                        <label for="telefone" class="form-label">Telefone</label>
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-phone"></i>
                            </span>
                            <input type="text" id="telefone" name="telefone" class="form-control"
                                value="<?= isset($setor) ? htmlspecialchars($setor['telefone']) : '' ?>"
                                placeholder="Ex: (00) 0000-0000">
                        </div>
                        <div class="form-feedback"></div>
                    </div>
                </div>

                <div class="form-group switch-group">
                    <label class="switch-label">
                        <span class="switch-title">Status do Setor</span>
                        <div class="switch-container">
                            <label class="switch">
                                <input type="checkbox" id="ativo" name="ativo" value="1"
                                    <?= (!isset($setor) || (isset($setor) && $setor['ativo'] == 1)) ? 'checked' : '' ?>>
                                <span class="slider round"></span>
                            </label>
                            <span class="switch-text" id="statusText">
                                <?= (!isset($setor) || (isset($setor) && $setor['ativo'] == 1)) ? 'Ativo' : 'Inativo' ?>
                            </span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" id="cancelarBtn" class="btn-secondary">
                    <i class="fas fa-times me-2"></i> Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save me-2"></i> <?= isset($setor) ? 'Atualizar Setor' : 'Criar Setor' ?>
                </button>
            </div>
        </form>
    </div>
</div>