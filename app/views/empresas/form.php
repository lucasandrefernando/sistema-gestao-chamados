<div class="empresa-header mb-4">
    <div class="d-flex align-items-center mb-2">
        <i class="fas fa-building me-3 empresa-header-icon"></i>
        <h1 class="h2 mb-0"><?= $titulo ?></h1>
    </div>
    <p class="text-muted empresa-header-desc">Gerencie as informações cadastrais da empresa</p>
</div>

<?php if (isset($isAdminRegular) && $isAdminRegular): ?>
    <div class="alert alert-info empresa-alerta-info mb-4">
        <i class="fas fa-info-circle me-2"></i> Você está visualizando os dados da sua empresa. Apenas administradores master podem alterar os registros cadastrais.
    </div>
<?php endif; ?>

<?php if (isset($empresa['ativo']) && !$empresa['ativo']): ?>
    <div class="empresa-inativa-banner mb-4">
        <div class="empresa-inativa-icon">
            <i class="fas fa-ban"></i>
        </div>
        <div class="empresa-inativa-content">
            <h5 class="empresa-inativa-title">Empresa Desativada</h5>
            <p class="empresa-inativa-text">Esta empresa está atualmente desativada no sistema.</p>
        </div>
        <?php if (isset($isAdminMaster) && $isAdminMaster): ?>
            <div class="empresa-inativa-action">
                <a href="<?= base_url('empresas/toggle/' . $empresa['id']) ?>" class="btn btn-success btn-sm empresa-btn-reativar">
                    <i class="fas fa-check me-1"></i> Reativar Empresa
                </a>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- CSS para o sistema de empresas -->
<style>
    /* Cabeçalho */
    .empresa-header {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 1rem;
    }

    .empresa-header-icon {
        font-size: 1.8rem;
        color: #3498db;
    }

    .empresa-header-desc {
        margin-bottom: 0;
        font-size: 0.95rem;
    }

    /* Banner de empresa inativa */
    .empresa-inativa-banner {
        display: flex;
        align-items: center;
        background: linear-gradient(to right, #ffeaa7, #fff3cd);
        border-left: 5px solid #e74c3c;
        border-radius: 8px;
        padding: 0;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(231, 76, 60, 0.15);
        position: relative;
    }

    .empresa-inativa-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #e74c3c;
        color: white;
        padding: 20px;
        font-size: 2rem;
    }

    .empresa-inativa-content {
        padding: 15px 20px;
        flex: 1;
    }

    .empresa-inativa-title {
        font-weight: 600;
        color: #e74c3c;
        margin-bottom: 5px;
    }

    .empresa-inativa-text {
        margin-bottom: 0;
        color: #7f8c8d;
    }

    .empresa-inativa-action {
        padding: 0 20px;
    }

    .empresa-btn-reativar {
        white-space: nowrap;
        background-color: #27ae60;
        border-color: #27ae60;
        transition: all 0.3s ease;
    }

    .empresa-btn-reativar:hover {
        background-color: #2ecc71;
        border-color: #2ecc71;
        box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);
        transform: translateY(-2px);
    }

    /* Estilos gerais */
    .empresa-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
        margin-bottom: 30px;
    }

    .empresa-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .empresa-card-body {
        padding: 25px;
    }

    .empresa-alerta-info {
        border-left: 4px solid #3498db;
        background-color: rgba(52, 152, 219, 0.1);
        border-radius: 4px;
        padding: 15px;
    }

    /* Formulário moderno */
    .empresa-form-section {
        margin-bottom: 30px;
        position: relative;
    }

    .empresa-form-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    .empresa-form-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
        font-size: 1.2rem;
    }

    .empresa-form-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0;
        font-size: 1.1rem;
    }

    .empresa-form-group {
        margin-bottom: 20px;
        position: relative;
    }

    .empresa-form-label {
        font-weight: 500;
        color: #2c3e50;
        margin-bottom: 8px;
    }

    .empresa-form-control {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 12px 15px;
        height: auto;
        transition: all 0.3s ease;
    }

    .empresa-form-control:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
    }

    .empresa-form-control[readonly] {
        background-color: #f8f9fa;
        border-color: #e9ecef;
        color: #6c757d;
    }

    /* Botões */
    .empresa-btn-salvar {
        background: linear-gradient(135deg, #3498db, #2980b9);
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .empresa-btn-salvar:hover {
        background: linear-gradient(135deg, #2980b9, #3498db);
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.4);
        transform: translateY(-2px);
    }

    .empresa-btn-voltar {
        background-color: #6c757d;
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .empresa-btn-voltar:hover {
        background-color: #5a6268;
        box-shadow: 0 4px 10px rgba(108, 117, 125, 0.3);
        transform: translateY(-2px);
    }

    .empresa-form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 30px;
    }

    /* Empresa inativa - estilo para o formulário */
    .empresa-inativa .empresa-card {
        position: relative;
        overflow: visible;
    }

    .empresa-inativa .empresa-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border: 2px dashed #e74c3c;
        border-radius: 12px;
        pointer-events: none;
        z-index: 1;
    }

    .empresa-inativa .empresa-form-control {
        border-color: #f8d7da;
        background-color: #fff8f8;
    }

    .empresa-inativa-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-30deg);
        font-size: 8rem;
        color: rgba(231, 76, 60, 0.07);
        font-weight: bold;
        pointer-events: none;
        z-index: 0;
        white-space: nowrap;
    }

    /* Responsividade */
    @media (max-width: 992px) {
        .empresa-inativa-banner {
            flex-direction: column;
            text-align: center;
        }

        .empresa-inativa-icon {
            width: 100%;
            padding: 15px;
        }

        .empresa-inativa-action {
            padding: 0 20px 15px;
        }
    }

    @media (max-width: 768px) {
        .empresa-card-body {
            padding: 20px;
        }

        .empresa-form-header {
            margin-bottom: 15px;
        }

        .empresa-form-icon {
            width: 35px;
            height: 35px;
            font-size: 1rem;
        }

        .empresa-form-actions {
            flex-direction: column-reverse;
            gap: 15px;
        }

        .empresa-btn-voltar,
        .empresa-btn-salvar {
            width: 100%;
        }
    }
</style>

<div class="card empresa-card <?= isset($empresa['ativo']) && !$empresa['ativo'] ? 'empresa-inativa-card' : '' ?>">
    <div class="card-body empresa-card-body">
        <?php if (isset($empresa['ativo']) && !$empresa['ativo']): ?>
            <div class="empresa-inativa-watermark">DESATIVADA</div>
        <?php endif; ?>

        <form action="<?= base_url('empresas/' . ($acao == 'criar' ? 'store' : 'update/' . $empresa['id'])) ?>" method="post" class="<?= isset($empresa['ativo']) && !$empresa['ativo'] ? 'empresa-inativa' : '' ?>">
            <div class="empresa-form-section">
                <div class="empresa-form-header">
                    <div class="empresa-form-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h5 class="empresa-form-title">Informações Básicas</h5>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="empresa-form-group">
                            <label for="nome" class="empresa-form-label">Nome <span class="text-danger">*</span></label>
                            <input type="text" class="form-control empresa-form-control" id="nome" name="nome"
                                value="<?= isset($_SESSION['form_data']['nome']) ? $_SESSION['form_data']['nome'] : ($empresa['nome'] ?? '') ?>"
                                required <?= isset($isAdminRegular) && $isAdminRegular ? 'readonly' : '' ?>>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="empresa-form-group">
                            <label for="cnpj" class="empresa-form-label">CNPJ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control empresa-form-control" id="cnpj" name="cnpj"
                                value="<?= isset($_SESSION['form_data']['cnpj']) ? $_SESSION['form_data']['cnpj'] : ($empresa['cnpj'] ?? '') ?>"
                                required <?= isset($isAdminRegular) && $isAdminRegular ? 'readonly' : '' ?>>
                        </div>
                    </div>
                </div>
            </div>

            <div class="empresa-form-section">
                <div class="empresa-form-header">
                    <div class="empresa-form-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h5 class="empresa-form-title">Contato</h5>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="empresa-form-group">
                            <label for="email" class="empresa-form-label">E-mail <span class="text-danger">*</span></label>
                            <input type="email" class="form-control empresa-form-control" id="email" name="email"
                                value="<?= isset($_SESSION['form_data']['email']) ? $_SESSION['form_data']['email'] : ($empresa['email'] ?? '') ?>"
                                required <?= isset($isAdminRegular) && $isAdminRegular ? 'readonly' : '' ?>>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="empresa-form-group">
                            <label for="telefone" class="empresa-form-label">Telefone</label>
                            <input type="text" class="form-control empresa-form-control" id="telefone" name="telefone"
                                value="<?= isset($_SESSION['form_data']['telefone']) ? $_SESSION['form_data']['telefone'] : ($empresa['telefone'] ?? '') ?>"
                                <?= isset($isAdminRegular) && $isAdminRegular ? 'readonly' : '' ?>>
                        </div>
                    </div>
                </div>
            </div>

            <div class="empresa-form-section">
                <div class="empresa-form-header">
                    <div class="empresa-form-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h5 class="empresa-form-title">Localização</h5>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="empresa-form-group">
                            <label for="endereco" class="empresa-form-label">Endereço</label>
                            <input type="text" class="form-control empresa-form-control" id="endereco" name="endereco"
                                value="<?= isset($_SESSION['form_data']['endereco']) ? $_SESSION['form_data']['endereco'] : ($empresa['endereco'] ?? '') ?>"
                                <?= isset($isAdminRegular) && $isAdminRegular ? 'readonly' : '' ?>>
                        </div>
                    </div>
                </div>
            </div>

            <div class="empresa-form-actions">
                <?php if (isset($isAdminMaster) && $isAdminMaster): ?>
                    <a href="<?= base_url('empresas') ?>" class="btn empresa-btn-voltar">
                        <i class="fas fa-arrow-left me-2"></i> Voltar para Lista
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('dashboard') ?>" class="btn empresa-btn-voltar">
                        <i class="fas fa-arrow-left me-2"></i> Voltar para Dashboard
                    </a>
                <?php endif; ?>

                <?php if (!isset($isAdminRegular) || !$isAdminRegular): ?>
                    <button type="submit" class="btn empresa-btn-salvar">
                        <i class="fas fa-save me-2"></i> Salvar Alterações
                    </button>
                <?php else: ?>
                    <div></div> <!-- Espaçador para manter o layout quando não há botão de salvar -->
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if (!isset($isAdminRegular) || !$isAdminRegular): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Máscara para CNPJ
            const cnpjInput = document.getElementById('cnpj');

            cnpjInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');

                if (value.length > 14) {
                    value = value.slice(0, 14);
                }

                // Aplica a máscara de CNPJ (XX.XXX.XXX/XXXX-XX)
                if (value.length > 0) {
                    value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                    value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                    value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                    value = value.replace(/(\d{4})(\d)/, '$1-$2');
                }

                e.target.value = value;
            });

            // Máscara para telefone
            const telefoneInput = document.getElementById('telefone');

            telefoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');

                if (value.length > 11) {
                    value = value.slice(0, 11);
                }

                // Aplica a máscara de telefone ((XX) XXXXX-XXXX ou (XX) XXXX-XXXX)
                if (value.length > 0) {
                    value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
                    value = value.length > 10 ? value.replace(/(\d{5})(\d)/, '$1-$2') : value.replace(/(\d{4})(\d)/, '$1-$2');
                }

                e.target.value = value;
            });
        });
    </script>
<?php endif; ?>