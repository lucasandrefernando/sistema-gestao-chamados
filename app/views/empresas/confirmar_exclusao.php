<div class="exclusao-header mb-4">
    <div class="d-flex align-items-center mb-2">
        <i class="fas fa-trash-alt me-3 exclusao-header-icon"></i>
        <h1 class="h2 mb-0">Confirmar Exclusão de Empresa</h1>
    </div>
    <p class="text-muted exclusao-header-desc">Revise os dados antes de confirmar a exclusão permanente</p>
</div>

<!-- CSS para o sistema de empresas -->
<style>
    /* Cabeçalho */
    .exclusao-header {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 1rem;
    }

    .exclusao-header-icon {
        font-size: 1.8rem;
        color: #e74c3c;
    }

    .exclusao-header-desc {
        margin-bottom: 0;
        font-size: 0.95rem;
    }

    /* Estilos gerais */
    .exclusao-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        overflow: hidden;
        margin-bottom: 30px;
        border-top: 4px solid #e74c3c;
    }

    .exclusao-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .exclusao-header {
        background-color: rgba(231, 76, 60, 0.1);
        color: #c0392b;
        padding: 15px 20px;
        font-weight: 600;
    }

    .exclusao-body {
        padding: 25px;
    }

    /* Alerta de empresa */
    .exclusao-empresa-info {
        background-color: #fff8e1;
        border-left: 4px solid #f39c12;
        border-radius: 4px;
        padding: 20px;
        margin-bottom: 25px;
    }

    .exclusao-empresa-nome {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    /* Cards de dependências */
    .exclusao-deps-container {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
    }

    .exclusao-dep-card {
        flex: 1;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s, box-shadow 0.3s;
        border: 1px solid #f1f2f3;
    }

    .exclusao-dep-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .exclusao-dep-header {
        padding: 15px;
        text-align: center;
        border-bottom: 1px solid #f1f2f3;
        background-color: #f8f9fa;
    }

    .exclusao-dep-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 1.2rem;
        color: white;
    }

    .exclusao-dep-icon-users {
        background: linear-gradient(135deg, #3498db, #2980b9);
    }

    .exclusao-dep-icon-licenses {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
    }

    .exclusao-dep-icon-tickets {
        background: linear-gradient(135deg, #f1c40f, #f39c12);
    }

    .exclusao-dep-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .exclusao-dep-count {
        font-size: 0.9rem;
        color: #7f8c8d;
    }

    .exclusao-dep-body {
        padding: 15px;
    }

    .exclusao-dep-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .exclusao-dep-item {
        padding: 8px 0;
        border-bottom: 1px solid #f1f2f3;
        font-size: 0.9rem;
        color: #2c3e50;
    }

    .exclusao-dep-item:last-child {
        border-bottom: none;
    }

    .exclusao-dep-more {
        text-align: center;
        padding: 8px 0;
        font-size: 0.85rem;
        color: #7f8c8d;
        font-style: italic;
    }

    /* Formulário de confirmação */
    .exclusao-form {
        background-color: #fdecea;
        border-radius: 10px;
        padding: 20px;
        margin-top: 30px;
        border-left: 4px solid #e74c3c;
    }

    .exclusao-form-title {
        font-weight: 600;
        color: #c0392b;
        margin-bottom: 15px;
    }

    .exclusao-input-container {
        max-width: 300px;
        margin-bottom: 20px;
    }

    .exclusao-input {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .exclusao-input:focus {
        border-color: #e74c3c;
        box-shadow: 0 0 0 0.25rem rgba(231, 76, 60, 0.25);
    }

    .exclusao-btn-container {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .exclusao-btn-cancelar {
        background-color: #95a5a6;
        border: none;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .exclusao-btn-cancelar:hover {
        background-color: #7f8c8d;
        box-shadow: 0 4px 10px rgba(127, 140, 141, 0.3);
    }

    .exclusao-btn-excluir {
        background-color: #e74c3c;
        border: none;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .exclusao-btn-excluir:hover:not(:disabled) {
        background-color: #c0392b;
        box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3);
    }

    .exclusao-btn-excluir:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    /* Responsividade */
    @media (max-width: 992px) {
        .exclusao-deps-container {
            flex-direction: column;
        }

        .exclusao-dep-card {
            margin-bottom: 15px;
        }
    }

    @media (max-width: 768px) {
        .exclusao-body {
            padding: 20px;
        }

        .exclusao-btn-container {
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
        }

        .exclusao-btn-cancelar,
        .exclusao-btn-excluir {
            width: 100%;
        }
    }
</style>

<?php if (isset($isAdminMaster) && $isAdminMaster): ?>
    <div class="d-flex justify-content-end mb-3">
        <a href="<?= base_url('empresas') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Voltar para Lista
        </a>
    </div>

    <div class="card exclusao-card">
        <div class="card-header exclusao-header">
            <i class="fas fa-exclamation-triangle me-2"></i> Atenção: Esta ação não pode ser desfeita!
        </div>
        <div class="card-body exclusao-body">
            <h5 class="mb-3">Você está prestes a excluir a empresa:</h5>
            <div class="exclusao-empresa-info">
                <h4 class="exclusao-empresa-nome"><?= $empresa['nome'] ?></h4>
                <p class="mb-1"><strong>CNPJ:</strong> <?= $empresa['cnpj'] ?></p>
                <p class="mb-0"><strong>E-mail:</strong> <?= $empresa['email'] ?></p>
            </div>

            <h5 class="mb-4">Os seguintes dados serão excluídos permanentemente:</h5>

            <div class="exclusao-deps-container">
                <div class="exclusao-dep-card">
                    <div class="exclusao-dep-header">
                        <div class="exclusao-dep-icon exclusao-dep-icon-users">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="exclusao-dep-title">Usuários</h5>
                        <div class="exclusao-dep-count"><?= count($dependencias['usuarios']) ?> registros</div>
                    </div>
                    <div class="exclusao-dep-body">
                        <?php if (!empty($dependencias['usuarios'])): ?>
                            <ul class="exclusao-dep-list">
                                <?php foreach (array_slice($dependencias['usuarios'], 0, 5) as $usuario): ?>
                                    <li class="exclusao-dep-item"><?= $usuario['nome'] ?></li>
                                <?php endforeach; ?>
                                <?php if (count($dependencias['usuarios']) > 5): ?>
                                    <li class="exclusao-dep-more">E mais <?= count($dependencias['usuarios']) - 5 ?> usuário(s)...</li>
                                <?php endif; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0">Nenhum usuário encontrado.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="exclusao-dep-card">
                    <div class="exclusao-dep-header">
                        <div class="exclusao-dep-icon exclusao-dep-icon-licenses">
                            <i class="fas fa-key"></i>
                        </div>
                        <h5 class="exclusao-dep-title">Licenças</h5>
                        <div class="exclusao-dep-count"><?= count($dependencias['licencas']) ?> registros</div>
                    </div>
                    <div class="exclusao-dep-body">
                        <?php if (!empty($dependencias['licencas'])): ?>
                            <ul class="exclusao-dep-list">
                                <?php foreach (array_slice($dependencias['licencas'], 0, 5) as $licenca): ?>
                                    <li class="exclusao-dep-item">
                                        <?= $licenca['quantidade'] ?> licença(s) -
                                        Válida até: <?= date('d/m/Y', strtotime($licenca['data_fim'])) ?>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (count($dependencias['licencas']) > 5): ?>
                                    <li class="exclusao-dep-more">E mais <?= count($dependencias['licencas']) - 5 ?> licença(s)...</li>
                                <?php endif; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0">Nenhuma licença encontrada.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="exclusao-dep-card">
                    <div class="exclusao-dep-header">
                        <div class="exclusao-dep-icon exclusao-dep-icon-tickets">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <h5 class="exclusao-dep-title">Chamados</h5>
                        <div class="exclusao-dep-count"><?= count($dependencias['chamados']) ?> registros</div>
                    </div>
                    <div class="exclusao-dep-body">
                        <?php if (!empty($dependencias['chamados'])): ?>
                            <ul class="exclusao-dep-list">
                                <?php foreach (array_slice($dependencias['chamados'], 0, 5) as $chamado): ?>
                                    <li class="exclusao-dep-item">
                                        #<?= $chamado['id'] ?> -
                                        <?= date('d/m/Y', strtotime($chamado['data_solicitacao'])) ?>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (count($dependencias['chamados']) > 5): ?>
                                    <li class="exclusao-dep-more">E mais <?= count($dependencias['chamados']) - 5 ?> chamado(s)...</li>
                                <?php endif; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0">Nenhum chamado encontrado.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="exclusao-form">
                <form action="<?= base_url('empresas/excluir/' . $empresa['id']) ?>" method="post" id="form-exclusao">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

                    <h5 class="exclusao-form-title">Confirmação de Exclusão</h5>
                    <p>Para confirmar a exclusão, digite <strong>"SIM"</strong> no campo abaixo:</p>

                    <div class="exclusao-input-container">
                        <input type="text" class="form-control exclusao-input" name="confirmar" id="confirmar" placeholder="Digite SIM para confirmar" required>
                    </div>

                    <div class="exclusao-btn-container">
                        <a href="<?= base_url('empresas') ?>" class="btn exclusao-btn-cancelar">
                            <i class="fas fa-times me-2"></i> Cancelar
                        </a>
                        <button type="submit" class="btn exclusao-btn-excluir" id="btn-excluir" disabled>
                            <i class="fas fa-trash-alt me-2"></i> Excluir Permanentemente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const confirmarInput = document.getElementById('confirmar');
            const btnExcluir = document.getElementById('btn-excluir');
            const formExclusao = document.getElementById('form-exclusao');

            // Efeito de foco no campo de confirmação
            confirmarInput.focus();

            // Habilita/desabilita o botão de exclusão com base no valor do campo de confirmação
            confirmarInput.addEventListener('input', function() {
                btnExcluir.disabled = this.value !== 'SIM';

                // Adiciona uma classe visual quando o campo estiver correto
                if (this.value === 'SIM') {
                    confirmarInput.classList.add('is-valid');
                    confirmarInput.classList.remove('is-invalid');
                } else if (this.value.length > 0) {
                    confirmarInput.classList.add('is-invalid');
                    confirmarInput.classList.remove('is-valid');
                } else {
                    confirmarInput.classList.remove('is-valid');
                    confirmarInput.classList.remove('is-invalid');
                }
            });

            // Confirmação adicional ao enviar o formulário
            formExclusao.addEventListener('submit', function(e) {
                if (!confirm('ATENÇÃO: Você está prestes a excluir permanentemente esta empresa e todos os seus dados relacionados. Esta ação NÃO PODE ser desfeita. Deseja continuar?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
<?php else: ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle me-2"></i> Você não tem permissão para excluir empresas.
    </div>
<?php endif; ?>