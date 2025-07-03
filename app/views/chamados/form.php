<?php

/**
 * Formulário de Criação/Edição de Chamados Hospitalares
 * Esta página permite criar ou editar chamados no sistema de atendimento hospitalar
 * 
 * @version 3.2
 */

// Título da página
$pageTitle = isset($chamado) ? 'Editar Chamado' : 'Novo Chamado';
$formAction = isset($chamado) ? base_url('chamados/update/' . $chamado['id']) : base_url('chamados/store');
$acao = isset($chamado) ? 'editar' : 'criar';

// Mapeamento de setores para tipos de serviço específicos
$tiposServicoPorSetor = [
    // Setor de Enfermagem
    'Enfermagem' => [
        'Assistência ao Paciente' => 'Assistência ao Paciente (medicação, cuidados)',
        'Monitoramento' => 'Monitoramento de Sinais Vitais',
        'Troca de Curativos' => 'Troca de Curativos',
        'Administração de Medicamentos' => 'Administração de Medicamentos',
        'Orientação ao Paciente' => 'Orientação ao Paciente ou Acompanhante'
    ],
    // Setor de Limpeza
    'Limpeza' => [
        'Limpeza de Quarto' => 'Limpeza de Quarto (rotina)',
        'Limpeza Emergencial' => 'Limpeza Emergencial (derramamentos)',
        'Troca de Roupas de Cama' => 'Troca de Roupas de Cama',
        'Coleta de Resíduos' => 'Coleta de Resíduos',
        'Limpeza de Banheiro' => 'Limpeza de Banheiro'
    ],
    // Setor de Manutenção
    'Manutenção' => [
        'Manutenção Elétrica' => 'Manutenção Elétrica (luzes, tomadas)',
        'Manutenção Hidráulica' => 'Manutenção Hidráulica (vazamentos, entupimentos)',
        'Ar Condicionado' => 'Problemas com Ar Condicionado',
        'Mobiliário' => 'Reparo de Mobiliário',
        'Portas e Janelas' => 'Problemas com Portas e Janelas'
    ],
    // Setor de Nutrição
    'Nutrição' => [
        'Refeição Regular' => 'Solicitação de Refeição Regular',
        'Dieta Especial' => 'Solicitação de Dieta Especial',
        'Refeição Extra' => 'Refeição Extra',
        'Alteração de Dieta' => 'Alteração de Dieta',
        'Hidratação' => 'Solicitação de Água/Bebidas'
    ],
    // Setor de TI
    'TI' => [
        'Suporte a Computadores' => 'Suporte a Computadores',
        'Problemas com TV' => 'Problemas com TV do Quarto',
        'Internet/WiFi' => 'Problemas com Internet/WiFi',
        'Telefonia' => 'Problemas com Telefone',
        'Sistema de Chamada' => 'Sistema de Chamada de Enfermagem'
    ],
    // Setor de Hotelaria
    'Hotelaria' => [
        'Itens de Conforto' => 'Solicitação de Itens de Conforto (travesseiros, cobertores)',
        'Controle de Temperatura' => 'Ajuste de Temperatura do Quarto',
        'Controle de Ruído' => 'Problemas com Ruído',
        'Privacidade' => 'Questões de Privacidade',
        'Acomodação de Acompanhante' => 'Acomodação para Acompanhante'
    ],
    // Setor de Farmácia
    'Farmácia' => [
        'Medicamentos' => 'Solicitação de Medicamentos',
        'Materiais Médicos' => 'Materiais Médicos',
        'Orientação sobre Medicação' => 'Orientação sobre Medicação',
        'Reposição de Estoque' => 'Reposição de Estoque de Medicamentos',
        'Medicação de Alta' => 'Medicação para Alta Hospitalar'
    ],
    // Setor de Segurança
    'Segurança' => [
        'Controle de Acesso' => 'Problemas com Controle de Acesso',
        'Situação de Risco' => 'Reporte de Situação de Risco',
        'Objetos Perdidos' => 'Objetos Perdidos',
        'Acompanhamento' => 'Solicitação de Acompanhamento',
        'Ocorrência' => 'Registro de Ocorrência'
    ],
    // Tipos genéricos para qualquer setor não mapeado
    'default' => [
        'Solicitação Geral' => 'Solicitação Geral',
        'Dúvidas' => 'Dúvidas e Informações',
        'Reclamação' => 'Reclamação',
        'Sugestão' => 'Sugestão',
        'Outros' => 'Outros Serviços'
    ]
];

// Converte o array de tipos de serviço para formato JSON para uso no JavaScript
$tiposServicoPorSetorJSON = json_encode($tiposServicoPorSetor);
?>

<div class="chamados-form">
    <div class="chamados-form-header">
        <div class="chamados-form-header-content">
            <h1 class="chamados-form-titulo">
                <i class="fas <?= isset($chamado) ? 'fa-edit' : 'fa-headset' ?>"></i>
                <?= $pageTitle ?>
            </h1>
            <p class="chamados-form-subtitulo">
                <?= isset($chamado) ? 'Atualize as informações do chamado existente' : 'Preencha os dados para abrir um novo chamado de atendimento' ?>
            </p>
        </div>
        <div class="chamados-form-acoes">
            <a href="javascript:history.back();" class="chamados-form-btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <?php if (isset($erro) && !empty($erro)): ?>
        <div class="chamados-form-alert chamados-form-alert-error">
            <div class="chamados-form-alert-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="chamados-form-alert-content">
                <h4 class="chamados-form-alert-title">Erro ao processar formulário</h4>
                <p class="chamados-form-alert-message"><?= $erro ?></p>
            </div>
            <button type="button" class="chamados-form-alert-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($sucesso) && !empty($sucesso)): ?>
        <div class="chamados-form-alert chamados-form-alert-success">
            <div class="chamados-form-alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="chamados-form-alert-content">
                <h4 class="chamados-form-alert-title">Operação realizada com sucesso</h4>
                <p class="chamados-form-alert-message"><?= $sucesso ?></p>
            </div>
            <button type="button" class="chamados-form-alert-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <div class="chamados-form-card">
        <div class="chamados-form-card-header">
            <h2 class="chamados-form-card-titulo">
                <?= isset($chamado) ? 'Editar informações do chamado' : 'Informações do novo chamado' ?>
            </h2>
        </div>
        <div class="chamados-form-card-body">
            <form id="chamadoForm" action="<?= $formAction ?>" method="post" class="chamados-form-formulario">
                <div class="chamados-form-grid">
                    <div class="chamados-form-grupo">
                        <label for="setor_id" class="chamados-form-label">
                            Setor Responsável <span class="chamados-form-obrigatorio">*</span>
                        </label>
                        <select id="setor_id" name="setor_id" class="chamados-form-select" required>
                            <option value="">Selecione o setor</option>
                            <?php
                            // Verifica se há setores ativos disponíveis
                            if (isset($setores) && is_array($setores) && count($setores) > 0):
                                // Filtra apenas os setores ativos
                                $setoresAtivos = array_filter($setores, function ($setor) {
                                    return isset($setor['ativo']) && $setor['ativo'] == 1;
                                });

                                foreach ($setoresAtivos as $setor):
                            ?>
                                    <option value="<?= $setor['id'] ?>"
                                        data-nome="<?= htmlspecialchars($setor['nome']) ?>"
                                        <?= (isset($chamado) && $chamado['setor_id'] == $setor['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($setor['nome']) ?>
                                    </option>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </select>
                        <div class="chamados-form-feedback"></div>
                    </div>

                    <div class="chamados-form-grupo">
                        <label for="tipo_servico" class="chamados-form-label">
                            Tipo de Serviço <span class="chamados-form-obrigatorio">*</span>
                        </label>
                        <select id="tipo_servico" name="tipo_servico" class="chamados-form-select" required disabled>
                            <option value="">Selecione primeiro o setor</option>
                        </select>
                        <div class="chamados-form-feedback"></div>
                    </div>
                </div>

                <!-- Campo para outro tipo de serviço -->
                <div id="outroTipoServico" class="chamados-form-outro-tipo">
                    <div class="chamados-form-grupo">
                        <label for="outro_tipo_servico" class="chamados-form-label">
                            Especifique o tipo de serviço <span class="chamados-form-obrigatorio">*</span>
                        </label>
                        <input type="text" id="outro_tipo_servico" name="outro_tipo_servico"
                            class="chamados-form-input" placeholder="Descreva o tipo de serviço">
                        <div class="chamados-form-feedback"></div>
                    </div>
                </div>

                <div class="chamados-form-grid">
                    <div class="chamados-form-grupo">
                        <label for="solicitante" class="chamados-form-label">
                            Solicitante <span class="chamados-form-obrigatorio">*</span>
                        </label>
                        <input type="text" id="solicitante" name="solicitante" class="chamados-form-input"
                            value="<?= isset($chamado) ? htmlspecialchars($chamado['solicitante']) : '' ?>"
                            placeholder="Nome do solicitante" required>
                        <div class="chamados-form-feedback"></div>
                    </div>

                    <div class="chamados-form-grupo">
                        <label for="email_origem" class="chamados-form-label">
                            Email do Solicitante
                        </label>
                        <input type="email" id="email_origem" name="email_origem" class="chamados-form-input"
                            value="<?= isset($chamado) ? htmlspecialchars($chamado['email_origem']) : '' ?>"
                            placeholder="Email para contato">
                        <div class="chamados-form-feedback"></div>
                    </div>
                </div>

                <div class="chamados-form-grid">
                    <div class="chamados-form-grupo">
                        <label for="paciente" class="chamados-form-label">
                            Paciente
                        </label>
                        <input type="text" id="paciente" name="paciente" class="chamados-form-input"
                            value="<?= isset($chamado) ? htmlspecialchars($chamado['paciente']) : '' ?>"
                            placeholder="Nome do paciente (se aplicável)">
                        <div class="chamados-form-feedback"></div>
                    </div>

                    <div class="chamados-form-grupo">
                        <label for="quarto_leito" class="chamados-form-label">
                            Quarto/Leito
                        </label>
                        <input type="text" id="quarto_leito" name="quarto_leito" class="chamados-form-input"
                            value="<?= isset($chamado) ? htmlspecialchars($chamado['quarto_leito']) : '' ?>"
                            placeholder="Ex: 101/A">
                        <div class="chamados-form-feedback"></div>
                    </div>
                </div>

                <!-- Dicas contextuais para ambiente hospitalar -->
                <div class="chamados-form-tip-container">
                    <!-- As dicas serão inseridas via JavaScript -->
                </div>

                <div class="chamados-form-grupo">
                    <label for="descricao" class="chamados-form-label">
                        Descrição do Chamado <span class="chamados-form-obrigatorio">*</span>
                    </label>
                    <textarea id="descricao" name="descricao" class="chamados-form-textarea"
                        placeholder="Descreva detalhadamente sua solicitação. Quanto mais informações, mais rápido poderemos atendê-lo."
                        rows="5" required><?= isset($chamado) ? htmlspecialchars($chamado['descricao']) : '' ?></textarea>
                    <div class="chamados-form-contador">0 caracteres</div>
                    <div class="chamados-form-feedback"></div>
                </div>

                <div id="sugestoesDescricao" class="chamados-form-sugestoes-container"></div>

                <div class="chamados-form-acoes-form">
                    <button type="button" onclick="history.back()" class="chamados-form-btn-cancelar">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="chamados-form-btn-salvar" id="btnSubmit">
                        <i class="fas fa-save"></i> <?= isset($chamado) ? 'Atualizar Chamado' : 'Abrir Chamado' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Dados para sugestões de descrição e tipos de serviço por setor
    document.addEventListener('DOMContentLoaded', function() {
        // Mapeamento de tipos de serviço por setor
        const tiposServicoPorSetor = <?= $tiposServicoPorSetorJSON ?>;

        // Sugestões de descrição para ambiente hospitalar
        const sugestoesDescricao = [
            'Solicito limpeza do quarto devido a derramamento de líquido no chão próximo à cama.',
            'A TV do quarto não está funcionando. A tela fica preta quando ligamos.',
            'Precisamos de troca de lençóis e toalhas para o paciente do leito A.',
            'O ar condicionado está fazendo barulho alto e incomodando o paciente.',
            'Solicito refeição especial para paciente com restrição de sal e açúcar.',
            'A luz do banheiro está piscando e precisa ser trocada.',
            'Necessito de travesseiro adicional para melhor posicionamento do paciente.',
            'O sistema de chamada de enfermagem não está funcionando corretamente.',
            'A porta do quarto está com dificuldade para fechar completamente.',
            'Solicito ajuste na temperatura do quarto, está muito frio para o paciente.'
        ];

        // Inicializa os componentes
        if (typeof initChamadosForm === 'function') {
            initChamadosForm(sugestoesDescricao, tiposServicoPorSetor);
        }

        // Verifica se há setores disponíveis
        const setorSelect = document.getElementById('setor_id');
        if (setorSelect && setorSelect.options.length <= 1) {
            // Se não houver setores, exibe um alerta
            const alertContainer = document.createElement('div');
            alertContainer.className = 'chamados-form-alert chamados-form-alert-error';
            alertContainer.innerHTML = `
                <div class="chamados-form-alert-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="chamados-form-alert-content">
                    <h4 class="chamados-form-alert-title">Atenção</h4>
                    <p class="chamados-form-alert-message">Não há setores ativos disponíveis para abertura de chamados. Por favor, entre em contato com o administrador do sistema.</p>
                </div>
                <button type="button" class="chamados-form-alert-close">
                    <i class="fas fa-times"></i>
                </button>
            `;

            // Insere o alerta antes do formulário
            const formCard = document.querySelector('.chamados-form-card');
            if (formCard) {
                formCard.parentNode.insertBefore(alertContainer, formCard);
            }

            // Desabilita o botão de envio
            const submitBtn = document.getElementById('btnSubmit');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('chamados-form-btn-disabled');
                submitBtn.title = 'Não é possível abrir chamados sem setores ativos';
            }
        }

        // Se estiver editando um chamado, preenche os tipos de serviço com base no setor selecionado
        <?php if (isset($chamado)): ?>
            const setorId = setorSelect.value;
            if (setorId) {
                const setorNome = setorSelect.options[setorSelect.selectedIndex].getAttribute('data-nome');
                const tipoServicoSelect = document.getElementById('tipo_servico');

                if (tipoServicoSelect) {
                    // Habilita o select de tipo de serviço
                    tipoServicoSelect.disabled = false;

                    // Preenche as opções com base no setor selecionado
                    preencherTiposServico(setorNome, tipoServicoSelect);

                    // Seleciona o tipo de serviço atual do chamado
                    const tipoServicoAtual = '<?= isset($chamado) ? htmlspecialchars($chamado['tipo_servico']) : '' ?>';
                    if (tipoServicoAtual) {
                        // Tenta encontrar a opção correspondente
                        let encontrado = false;
                        for (let i = 0; i < tipoServicoSelect.options.length; i++) {
                            if (tipoServicoSelect.options[i].value === tipoServicoAtual) {
                                tipoServicoSelect.selectedIndex = i;
                                encontrado = true;
                                break;
                            }
                        }

                        // Se não encontrou, adiciona como "Outro"
                        if (!encontrado && tipoServicoAtual !== 'outro') {
                            const option = document.createElement('option');
                            option.value = tipoServicoAtual;
                            option.textContent = tipoServicoAtual;
                            option.selected = true;
                            tipoServicoSelect.appendChild(option);
                        }
                    }
                }
            }
        <?php endif; ?>

        /**
         * Preenche o select de tipos de serviço com base no setor selecionado
         * @param {string} setorNome - Nome do setor selecionado
         * @param {HTMLElement} tipoServicoSelect - Elemento select para tipos de serviço
         */
        function preencherTiposServico(setorNome, tipoServicoSelect) {
            // Limpa as opções atuais
            tipoServicoSelect.innerHTML = '';

            // Adiciona a opção padrão
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Selecione o tipo de serviço';
            tipoServicoSelect.appendChild(defaultOption);

            // Obtém os tipos de serviço para o setor selecionado ou usa os tipos padrão
            const tiposServico = tiposServicoPorSetor[setorNome] || tiposServicoPorSetor['default'];

            // Adiciona as opções de tipos de serviço
            for (const [valor, descricao] of Object.entries(tiposServico)) {
                const option = document.createElement('option');
                option.value = valor;
                option.textContent = descricao;
                tipoServicoSelect.appendChild(option);
            }

            // Adiciona a opção "Outro"
            const outroOption = document.createElement('option');
            outroOption.value = 'outro';
            outroOption.textContent = 'Outro (especificar)';
            tipoServicoSelect.appendChild(outroOption);
        }
    });
</script>