<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $acao == 'criar' ? 'Novo Chamado' : 'Editar Chamado #' . $chamado['id'] ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= $acao == 'criar' ? base_url('chamados') : base_url('chamados/visualizar/' . $chamado['id']) ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><?= $acao == 'criar' ? 'Dados do Novo Chamado' : 'Editar Dados do Chamado' ?></h5>
    </div>
    <div class="card-body">
        <form action="<?= base_url('chamados/' . ($acao == 'criar' ? 'store' : 'update/' . $chamado['id'])) ?>" method="post">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="setor_id" class="form-label">Setor <span class="text-danger">*</span></label>
                    <select class="form-select" id="setor_id" name="setor_id" required>
                        <option value="">Selecione um setor</option>
                        <?php foreach ($setores as $setor): ?>
                            <option value="<?= $setor['id'] ?>" <?= isset($chamado) && $chamado['setor_id'] == $setor['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($setor['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="tipo_servico" class="form-label">Tipo de Serviço</label>
                    <select class="form-select" id="tipo_servico" name="tipo_servico">
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
                </div>
            </div>

            <div class="mb-3" id="outroTipoServico" style="display: none;">
                <label for="outro_tipo_servico" class="form-label">Especifique o Tipo de Serviço</label>
                <input type="text" class="form-control" id="outro_tipo_servico" name="outro_tipo_servico" value="<?= isset($chamado) && !in_array($chamado['tipo_servico'], $tiposServico) ? htmlspecialchars($chamado['tipo_servico']) : '' ?>">
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="solicitante" class="form-label">Solicitante <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="solicitante" name="solicitante" value="<?= isset($chamado) ? htmlspecialchars($chamado['solicitante']) : '' ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="paciente" class="form-label">Paciente (se aplicável)</label>
                    <input type="text" class="form-control" id="paciente" name="paciente" value="<?= isset($chamado) ? htmlspecialchars($chamado['paciente'] ?? '') : '' ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="quarto_leito" class="form-label">Quarto/Leito (se aplicável)</label>
                <input type="text" class="form-control" id="quarto_leito" name="quarto_leito" value="<?= isset($chamado) ? htmlspecialchars($chamado['quarto_leito'] ?? '') : '' ?>">
            </div>

            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição <span class="text-danger">*</span></label>
                <textarea class="form-control" id="descricao" name="descricao" rows="5" required><?= isset($chamado) ? htmlspecialchars($chamado['descricao']) : '' ?></textarea>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="<?= $acao == 'criar' ? base_url('chamados') : base_url('chamados/visualizar/' . $chamado['id']) ?>" class="btn btn-secondary me-md-2">Cancelar</a>
                <button type="submit" class="btn btn-primary"><?= $acao == 'criar' ? 'Criar Chamado' : 'Salvar Alterações' ?></button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tipoServicoSelect = document.getElementById('tipo_servico');
        const outroTipoServico = document.getElementById('outroTipoServico');
        const outroTipoServicoInput = document.getElementById('outro_tipo_servico');

        // Verifica o valor inicial
        if (tipoServicoSelect.value === 'outro') {
            outroTipoServico.style.display = 'block';
        }

        // Adiciona o evento de mudança
        tipoServicoSelect.addEventListener('change', function() {
            if (this.value === 'outro') {
                outroTipoServico.style.display = 'block';
                outroTipoServicoInput.setAttribute('required', 'required');
            } else {
                outroTipoServico.style.display = 'none';
                outroTipoServicoInput.removeAttribute('required');
            }
        });

        // Manipula o envio do formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            if (tipoServicoSelect.value === 'outro' && outroTipoServicoInput.value.trim() !== '') {
                e.preventDefault();
                tipoServicoSelect.innerHTML += `<option value="${outroTipoServicoInput.value}" selected>${outroTipoServicoInput.value}</option>`;
                this.submit();
            }
        });
    });
</script>