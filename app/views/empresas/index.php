<div class="empresa-header mb-4">
    <div class="d-flex align-items-center mb-2">
        <i class="fas fa-building me-3 empresa-header-icon"></i>
        <h1 class="h2 mb-0">Gerenciamento de Empresas</h1>
    </div>
    <p class="text-muted empresa-header-desc">Gerencie todas as empresas cadastradas no sistema</p>
</div>

<div class="d-flex justify-content-end mb-3">
    <a href="<?= base_url('empresas/criar') ?>" class="btn btn-primary empresa-btn-novo">
        <i class="fas fa-plus me-2"></i> Nova Empresa
    </a>
</div>

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

    /* Botão novo */
    .empresa-btn-novo {
        background: linear-gradient(135deg, #3498db, #2980b9);
        border: none;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .empresa-btn-novo:hover {
        background: linear-gradient(135deg, #2980b9, #3498db);
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.4);
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

    /* Tabela moderna */
    .empresa-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 0;
    }

    .empresa-table thead th {
        background-color: #f8f9fa;
        color: #2c3e50;
        font-weight: 600;
        padding: 15px;
        text-align: left;
        border-bottom: 2px solid #e9ecef;
    }

    .empresa-table thead th:last-child {
        text-align: right;
    }

    .empresa-table tbody tr {
        transition: all 0.2s ease;
    }

    .empresa-table tbody tr:hover {
        background-color: rgba(52, 152, 219, 0.05);
    }

    .empresa-table td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid #e9ecef;
    }

    .empresa-table td:last-child {
        text-align: right;
    }

    .empresa-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Linhas de empresas inativas */
    .empresa-row-inativa {
        position: relative;
    }

    .empresa-row-inativa td {
        color: #95a5a6;
        background-color: #f8f9fa;
    }

    .empresa-row-inativa::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        top: 50%;
        height: 1px;
        background-color: rgba(231, 76, 60, 0.2);
        z-index: 1;
    }

    .empresa-row-inativa .empresa-nome {
        position: relative;
    }

    .empresa-row-inativa .empresa-nome::after {
        content: "DESATIVADA";
        position: absolute;
        top: 0;
        right: 0;
        background-color: #fbe9e7;
        color: #e74c3c;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 3px 6px;
        border-radius: 4px;
        letter-spacing: 0.5px;
    }

    /* Status badges */
    .empresa-badge {
        padding: 6px 12px;
        border-radius: 30px;
        font-weight: 500;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        display: inline-block;
    }

    .empresa-badge-success {
        background-color: #e6f7ee;
        color: #27ae60;
    }

    .empresa-badge-danger {
        background-color: #fbe9e7;
        color: #e74c3c;
    }

    /* Botões de ação */
    .empresa-acoes {
        display: flex;
        gap: 5px;
        justify-content: flex-end;
    }

    .empresa-btn-acao {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        border: none;
    }

    .empresa-btn-acao:hover {
        transform: translateY(-2px);
    }

    .empresa-btn-editar {
        background-color: #3498db;
        color: white;
    }

    .empresa-btn-editar:hover {
        background-color: #2980b9;
        box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
    }

    .empresa-btn-toggle-on {
        background-color: #f39c12;
        color: white;
    }

    .empresa-btn-toggle-on:hover {
        background-color: #e67e22;
        box-shadow: 0 4px 10px rgba(243, 156, 18, 0.3);
    }

    .empresa-btn-toggle-off {
        background-color: #27ae60;
        color: white;
    }

    .empresa-btn-toggle-off:hover {
        background-color: #2ecc71;
        box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);
    }

    .empresa-btn-excluir {
        background-color: #e74c3c;
        color: white;
    }

    .empresa-btn-excluir:hover {
        background-color: #c0392b;
        box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3);
    }

    /* Estado vazio */
    .empresa-empty-state {
        text-align: center;
        padding: 40px 20px;
    }

    .empresa-empty-icon {
        font-size: 3rem;
        color: #bdc3c7;
        margin-bottom: 15px;
    }

    .empresa-empty-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .empresa-empty-text {
        color: #7f8c8d;
        max-width: 400px;
        margin: 0 auto;
    }

    /* Responsividade */
    @media (max-width: 992px) {
        .empresa-table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .empresa-table td,
        .empresa-table th {
            white-space: nowrap;
        }
    }
</style>

<div class="card empresa-card">
    <div class="card-body p-0">
        <div class="empresa-table-responsive">
            <table class="empresa-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Nome</th>
                        <th>CNPJ</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>Status</th>
                        <th style="width: 120px; text-align: right;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($empresas) && !empty($empresas)): ?>
                        <?php foreach ($empresas as $empresa): ?>
                            <tr class="<?= !$empresa['ativo'] ? 'empresa-row-inativa' : '' ?>">
                                <td><?= $empresa['id'] ?></td>
                                <td class="empresa-nome">
                                    <strong><?= $empresa['nome'] ?></strong>
                                </td>
                                <td><?= $empresa['cnpj'] ?></td>
                                <td><?= $empresa['email'] ?></td>
                                <td><?= $empresa['telefone'] ?? '-' ?></td>
                                <td>
                                    <?php if ($empresa['ativo']): ?>
                                        <span class="empresa-badge empresa-badge-success">Ativa</span>
                                    <?php else: ?>
                                        <span class="empresa-badge empresa-badge-danger">Inativa</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="empresa-acoes">
                                        <a href="<?= base_url('empresas/editar/' . $empresa['id']) ?>" class="empresa-btn-acao empresa-btn-editar" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= base_url('empresas/toggle/' . $empresa['id']) ?>" class="empresa-btn-acao <?= $empresa['ativo'] ? 'empresa-btn-toggle-on' : 'empresa-btn-toggle-off' ?>" title="<?= $empresa['ativo'] ? 'Desativar' : 'Ativar' ?>">
                                            <i class="fas <?= $empresa['ativo'] ? 'fa-ban' : 'fa-check' ?>"></i>
                                        </a>
                                        <a href="<?= base_url('empresas/confirmarExclusao/' . $empresa['id']) ?>" class="empresa-btn-acao empresa-btn-excluir" title="Excluir">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empresa-empty-state">
                                    <i class="fas fa-building empresa-empty-icon"></i>
                                    <h5 class="empresa-empty-title">Nenhuma empresa encontrada</h5>
                                    <p class="empresa-empty-text">Clique em "Nova Empresa" para adicionar uma empresa ao sistema.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>