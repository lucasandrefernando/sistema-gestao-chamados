<div class="chamados-form">
    <!-- Cabeçalho da Página -->
    <div class="chamados-form-header">
        <h1 class="chamados-form-titulo">
            <?= $acao == 'criar' ? 'Novo Chamado' : 'Editar Chamado #' . $chamado['id'] ?>
        </h1>
        <div class="chamados-form-acoes">
            <a href="<?= $acao == 'criar' ? base_url('chamados/listar') : base_url('chamados/visualizar/' . $chamado['id']) ?>" class="chamados-form-btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <!-- Card do Formulário -->
    <div class="chamados-form-card">
        <div class="chamados-form-card-header">
            <h2 class="chamados-form-card-titulo">
                <?= $acao == 'criar' ? 'Dados do Novo Chamado' : 'Editar Dados do Chamado' ?>
            </h2>
        </div>
        <div class="chamados-form-card-body">
            <form action="<?= base_url('chamados/' . ($acao == 'criar' ? 'store' : 'update/' . $chamado['id'])) ?>" method="post" class="chamados-form-formulario">
                <div class="chamados-form-grid">
                    <div class="chamados-form-grupo">
                        <label for="setor_id" class="chamados-form-label">
                            Setor <span class="chamados-form-obrigatorio">*</span>
                        </label>
                        <select class="chamados-form-select" id="setor_id" name="setor_id" required>
                            <option value="">Selecione um setor</option>
                            <?php foreach ($setores as $setor): ?>
                                <option value="<?= $setor['id'] ?>" <?= isset($chamado) && $chamado['setor_id'] == $setor['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($setor['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="chamados-form-feedback"></div>
                    </div>
                    <div class="chamados-form-grupo">
                        <label for="tipo_servico" class="chamados-form-label">Tipo de Serviço</label>
                        <select class="chamados-form-select" id="tipo_servico" name="tipo_servico">
                            <option value="">Selecione um tipo de serviço</option>
                            <?php foreach ($tiposServico as $tipo): ?>
                                <option value="<?= $tipo ?>" <?= isset($chamado) && $chamado['tipo_servico'] == $tipo ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tipo) ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="outro" <?= isset($chamado) && !in_array($chamado['tipo_servico'], $tiposServico) && !empty($chamado['tipo_servico']) ? 'selected' : '' ?>>
                                Outro (especificar)
                            </option>
                        </select>
                        <div class="chamados-form-feedback"></div>
                    </div>
                </div>

                <div class="chamados-form-grupo chamados-form-outro-tipo" id="outroTipoServico">
                    <label for="outro_tipo_servico" class="chamados-form-label">Especifique o Tipo de Serviço</label>
                    <input type="text" class="chamados-form-input" id="outro_tipo_servico" name="outro_tipo_servico" value="<?= isset($chamado) && !in_array($chamado['tipo_servico'], $tiposServico) ? htmlspecialchars($chamado['tipo_servico']) : '' ?>">
                    <div class="chamados-form-feedback"></div>
                </div>

                <div class="chamados-form-grid">
                    <div class="chamados-form-grupo">
                        <label for="solicitante" class="chamados-form-label">
                            Solicitante <span class="chamados-form-obrigatorio">*</span>
                        </label>
                        <input type="text" class="chamados-form-input" id="solicitante" name="solicitante" value="<?= isset($chamado) ? htmlspecialchars($chamado['solicitante']) : '' ?>" required>
                        <div class="chamados-form-feedback"></div>
                    </div>
                    <div class="chamados-form-grupo">
                        <label for="paciente" class="chamados-form-label">Paciente (se aplicável)</label>
                        <input type="text" class="chamados-form-input" id="paciente" name="paciente" value="<?= isset($chamado) ? htmlspecialchars($chamado['paciente'] ?? '') : '' ?>">
                        <div class="chamados-form-feedback"></div>
                    </div>
                </div>

                <div class="chamados-form-grupo">
                    <label for="quarto_leito" class="chamados-form-label">Quarto/Leito (se aplicável)</label>
                    <input type="text" class="chamados-form-input" id="quarto_leito" name="quarto_leito" value="<?= isset($chamado) ? htmlspecialchars($chamado['quarto_leito'] ?? '') : '' ?>">
                    <div class="chamados-form-feedback"></div>
                </div>

                <div class="chamados-form-grupo">
                    <label for="descricao" class="chamados-form-label">
                        Descrição <span class="chamados-form-obrigatorio">*</span>
                    </label>
                    <textarea class="chamados-form-textarea" id="descricao" name="descricao" rows="5" required><?= isset($chamado) ? htmlspecialchars($chamado['descricao']) : '' ?></textarea>
                    <div class="chamados-form-contador">0 caracteres</div>
                    <div class="chamados-form-feedback"></div>
                </div>

                <div class="chamados-form-acoes-form">
                    <a href="<?= $acao == 'criar' ? base_url('chamados/listar') : base_url('chamados/visualizar/' . $chamado['id']) ?>" class="chamados-form-btn-cancelar">Cancelar</a>
                    <button type="submit" class="chamados-form-btn-salvar">
                        <?= $acao == 'criar' ? 'Criar Chamado' : 'Salvar Alterações' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>