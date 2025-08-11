/**
 * chamado-visualizar.js - Script específico para a página de visualização de chamados
 * Versão 6.0 - Botão copiar funcionando 100%
 */

document.addEventListener('DOMContentLoaded', function () {
    console.log('Inicializando página de visualização de chamados...');

    // Inicializa tooltips do Bootstrap
    initTooltips();

    // Configura o botão voltar
    setupBotaoVoltar();

    // Configura o contador de tempo em aberto
    setupTempoAberto();

    // Adiciona efeitos de hover aos cards
    setupCardHoverEffects();

    // Configura os modais
    setupModals();

    // Configura o formulário de comentários
    setupComentarioForm();

    // Adiciona efeitos visuais à timeline
    enhanceTimeline();

    // FUNCIONALIDADE PRINCIPAL: Configura botão de copiar telefone
    setupBotaoCopiarTelefone();

    console.log('Página de visualização inicializada com sucesso!');
});

/**
 * FUNÇÃO PRINCIPAL: Configura a funcionalidade de copiar telefone
 */
function setupBotaoCopiarTelefone() {
    console.log('Configurando botões de copiar telefone...');

    // Seleciona todos os botões de copiar
    const botoesCopiar = document.querySelectorAll('.chamado-btn-copiar');

    console.log(`Encontrados ${botoesCopiar.length} botões de copiar`);

    botoesCopiar.forEach((botao, index) => {
        console.log(`Configurando botão ${index + 1}`);

        botao.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const telefone = this.getAttribute('data-telefone');
            console.log('Tentando copiar telefone:', telefone);

            if (telefone) {
                copiarTelefoneParaClipboard(telefone, this);
            } else {
                console.error('Número de telefone não encontrado no atributo data-telefone');
                mostrarToastErro('Erro: Número não encontrado');
            }
        });
    });
}

/**
 * Função principal para copiar telefone
 * @param {string} telefone - Número do telefone
 * @param {HTMLElement} botao - Botão que foi clicado
 */
function copiarTelefoneParaClipboard(telefone, botao) {
    console.log('Iniciando processo de cópia:', telefone);

    // Limpa o telefone (remove formatação)
    const telefoneNumeros = telefone.replace(/\D/g, '');
    console.log('Telefone limpo:', telefoneNumeros);

    // Tenta usar a API moderna de clipboard primeiro
    if (navigator.clipboard && window.isSecureContext) {
        console.log('Usando Clipboard API moderna');

        navigator.clipboard.writeText(telefoneNumeros).then(() => {
            console.log('✅ Telefone copiado com sucesso via Clipboard API');
            mostrarFeedbackSucesso(botao);
            mostrarToastSucesso();
        }).catch(err => {
            console.error('❌ Erro ao copiar via Clipboard API:', err);
            // Fallback para método antigo
            copiarComMetodoFallback(telefoneNumeros, botao);
        });
    } else {
        console.log('Clipboard API não disponível, usando método fallback');
        copiarComMetodoFallback(telefoneNumeros, botao);
    }
}

/**
 * Método fallback para navegadores mais antigos
 * @param {string} texto - Texto a ser copiado
 * @param {HTMLElement} botao - Botão que foi clicado
 */
function copiarComMetodoFallback(texto, botao) {
    console.log('Executando método fallback');

    // Cria um elemento temporário
    const elementoTemporario = document.createElement('textarea');
    elementoTemporario.value = texto;
    elementoTemporario.style.position = 'fixed';
    elementoTemporario.style.left = '-9999px';
    elementoTemporario.style.top = '-9999px';
    elementoTemporario.style.opacity = '0';
    elementoTemporario.style.pointerEvents = 'none';

    // Adiciona ao DOM
    document.body.appendChild(elementoTemporario);

    try {
        // Seleciona o texto
        elementoTemporario.focus();
        elementoTemporario.select();
        elementoTemporario.setSelectionRange(0, 99999); // Para mobile

        // Executa o comando de cópia
        const sucesso = document.execCommand('copy');

        if (sucesso) {
            console.log('✅ Telefone copiado com sucesso via fallback');
            mostrarFeedbackSucesso(botao);
            mostrarToastSucesso();
        } else {
            throw new Error('Comando execCommand falhou');
        }
    } catch (err) {
        console.error('❌ Erro no método fallback:', err);

        // Última tentativa: mostrar prompt para cópia manual
        const numeroFormatado = formatarTelefoneJS(texto);
        const copiouManualmente = prompt(
            'Não foi possível copiar automaticamente.\nCopie o número manualmente:',
            numeroFormatado
        );

        if (copiouManualmente !== null) {
            mostrarToastInfo('Número exibido para cópia manual');
        }
    } finally {
        // Remove o elemento temporário
        document.body.removeChild(elementoTemporario);
    }
}

/**
 * Mostra feedback visual no botão
 * @param {HTMLElement} botao - Botão que foi clicado
 */
function mostrarFeedbackSucesso(botao) {
    console.log('Mostrando feedback visual no botão');

    // Salva o estado original
    const iconOriginal = botao.innerHTML;
    const classesOriginais = botao.className;

    // Muda para estado de sucesso
    botao.innerHTML = '<i class="fas fa-check"></i>';
    botao.classList.add('copiado');
    botao.style.transform = 'scale(1.1)';

    // Desabilita temporariamente
    botao.disabled = true;

    // Restaura após 2 segundos
    setTimeout(() => {
        botao.innerHTML = iconOriginal;
        botao.className = classesOriginais;
        botao.style.transform = '';
        botao.disabled = false;
        console.log('Feedback visual restaurado');
    }, 2000);
}

/**
 * Mostra toast de sucesso
 */
function mostrarToastSucesso() {
    console.log('Mostrando toast de sucesso');

    const toastElement = document.getElementById('toastCopia');

    if (toastElement) {
        // Verifica se Bootstrap está disponível
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const toast = new bootstrap.Toast(toastElement, {
                delay: 3000
            });
            toast.show();
        } else {
            // Fallback sem Bootstrap
            mostrarToastSimples('Número copiado para a área de transferência!', 'success');
        }
    } else {
        // Cria toast se não existir
        mostrarToastSimples('Número copiado para a área de transferência!', 'success');
    }
}

/**
 * Mostra toast de erro
 * @param {string} mensagem - Mensagem de erro
 */
function mostrarToastErro(mensagem) {
    console.log('Mostrando toast de erro:', mensagem);
    mostrarToastSimples(mensagem, 'error');
}

/**
 * Mostra toast informativo
 * @param {string} mensagem - Mensagem informativa
 */
function mostrarToastInfo(mensagem) {
    console.log('Mostrando toast informativo:', mensagem);
    mostrarToastSimples(mensagem, 'info');
}

/**
 * Cria um toast simples quando o elemento não existe
 * @param {string} mensagem - Mensagem do toast
 * @param {string} tipo - Tipo do toast (success, error, info)
 */
function mostrarToastSimples(mensagem, tipo = 'success') {
    console.log('Criando toast simples:', tipo, mensagem);

    // Remove toast anterior se existir
    const toastAnterior = document.querySelector('.toast-customizado');
    if (toastAnterior) {
        toastAnterior.remove();
    }

    // Define cores por tipo
    const cores = {
        success: { bg: '#2ecc71', icon: 'check-circle' },
        error: { bg: '#e74c3c', icon: 'exclamation-circle' },
        info: { bg: '#3498db', icon: 'info-circle' }
    };

    const config = cores[tipo] || cores.success;

    // Cria o toast
    const toast = document.createElement('div');
    toast.className = 'toast-customizado';
    toast.innerHTML = `
        <div style="
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: ${config.bg};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            max-width: 350px;
            animation: slideInRight 0.3s ease;
        ">
            <i class="fas fa-${config.icon}"></i>
            <span>${mensagem}</span>
        </div>
    `;

    document.body.appendChild(toast);

    // Remove após 3 segundos
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

/**
 * Formata um número de telefone para exibição (JavaScript)
 * @param {string} telefone - Número de telefone (apenas números)
 * @returns {string} - Telefone formatado
 */
function formatarTelefoneJS(telefone) {
    if (!telefone) return '';

    // Remove caracteres não numéricos
    const numeros = telefone.replace(/\D/g, '');

    // Formata conforme o padrão brasileiro
    if (numeros.length === 11) {
        // Celular: (11) 91234-5678
        return `(${numeros.substring(0, 2)}) ${numeros.substring(2, 7)}-${numeros.substring(7)}`;
    } else if (numeros.length === 10) {
        // Fixo: (11) 1234-5678
        return `(${numeros.substring(0, 2)}) ${numeros.substring(2, 6)}-${numeros.substring(6)}`;
    }

    return telefone;
}

// ================================
// FUNÇÕES EXISTENTES MANTIDAS
// ================================

/**
 * Inicializa os tooltips do Bootstrap
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            boundary: document.body
        });
    });
}

/**
 * Configura o contador de tempo em aberto para atualizar em tempo real
 */
function setupTempoAberto() {
    const tempoAbertoElement = document.querySelector('.chamado-tempo-aberto');

    if (tempoAbertoElement) {
        const dataInicio = tempoAbertoElement.getAttribute('data-inicio');

        if (dataInicio) {
            // Atualiza o tempo a cada segundo
            setInterval(function () {
                const inicio = new Date(dataInicio);
                const agora = new Date();
                const diff = Math.floor((agora - inicio) / 1000); // diferença em segundos

                const dias = Math.floor(diff / (24 * 60 * 60));
                const horas = Math.floor((diff % (24 * 60 * 60)) / (60 * 60));
                const minutos = Math.floor((diff % (60 * 60)) / 60);
                const segundos = diff % 60;

                let tempoFormatado = '';
                if (dias > 0) {
                    tempoFormatado += dias + ' dia(s), ';
                }
                tempoFormatado += `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;

                tempoAbertoElement.textContent = tempoFormatado;
            }, 1000);
        }
    }
}

/**
 * Configura efeitos de hover para os cards
 */
function setupCardHoverEffects() {
    const cards = document.querySelectorAll('.chamado-card');

    cards.forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.1)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'var(--chamado-box-shadow)';
        });
    });
}

/**
 * Configura os modais com funcionalidades adicionais
 */
function setupModals() {
    // Modal de alteração de status
    const alterarStatusModal = document.getElementById('alterarStatusModal');
    if (alterarStatusModal) {
        alterarStatusModal.addEventListener('show.bs.modal', function () {
            // Foca no select quando o modal é aberto
            setTimeout(() => {
                const statusSelect = document.getElementById('status_id');
                if (statusSelect) {
                    statusSelect.focus();
                }
            }, 500);
        });

        // Adiciona validação visual ao select de status
        const statusSelect = document.getElementById('status_id');
        if (statusSelect) {
            statusSelect.addEventListener('change', function () {
                if (this.value) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                } else {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            });
        }
    }

    // Modal de transferência de setor
    const transferirSetorModal = document.getElementById('transferirSetorModal');
    if (transferirSetorModal) {
        transferirSetorModal.addEventListener('show.bs.modal', function () {
            // Foca no select quando o modal é aberto
            setTimeout(() => {
                const setorSelect = document.getElementById('setor_id');
                if (setorSelect) {
                    setorSelect.focus();
                }
            }, 500);
        });

        // Adiciona validação visual ao select de setor
        const setorSelect = document.getElementById('setor_id');
        if (setorSelect) {
            setorSelect.addEventListener('change', function () {
                if (this.value) {
                    this.classList.add('is-valid');
                    this.classList.remove('is-invalid');
                } else {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                }
            });
        }
    }
}

/**
 * Configura o formulário de comentários com funcionalidades adicionais
 */
function setupComentarioForm() {
    const comentarioForm = document.querySelector('.chamado-form-comentario');
    const comentarioTextarea = document.getElementById('comentario');

    if (comentarioForm && comentarioTextarea) {
        // Adiciona contador de caracteres
        const contadorContainer = document.createElement('div');
        contadorContainer.className = 'chamado-contador-caracteres';
        contadorContainer.style.textAlign = 'right';
        contadorContainer.style.fontSize = '0.8rem';
        contadorContainer.style.color = 'var(--chamado-gray)';
        contadorContainer.style.marginTop = '0.25rem';

        comentarioTextarea.parentNode.appendChild(contadorContainer);

        comentarioTextarea.addEventListener('input', function () {
            const caracteres = this.value.length;
            contadorContainer.textContent = `${caracteres} caractere(s)`;

            // Muda a cor do contador conforme o tamanho do texto
            if (caracteres > 500) {
                contadorContainer.style.color = 'var(--chamado-warning)';
            } else if (caracteres > 1000) {
                contadorContainer.style.color = 'var(--chamado-danger)';
            } else {
                contadorContainer.style.color = 'var(--chamado-gray)';
            }
        });

        // Adiciona validação ao formulário
        comentarioForm.addEventListener('submit', function (e) {
            if (comentarioTextarea.value.trim() === '') {
                e.preventDefault();
                comentarioTextarea.classList.add('is-invalid');

                // Adiciona mensagem de erro
                if (!document.querySelector('.chamado-erro-comentario')) {
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'chamado-erro-comentario';
                    errorMsg.textContent = 'Por favor, digite um comentário antes de enviar.';
                    errorMsg.style.color = 'var(--chamado-danger)';
                    errorMsg.style.fontSize = '0.85rem';
                    errorMsg.style.marginTop = '0.25rem';

                    comentarioTextarea.parentNode.appendChild(errorMsg);
                }

                return false;
            }

            // Adiciona efeito de loading ao botão
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
                submitBtn.disabled = true;

                // Remove o loading após o envio (será recarregado pela página)
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 2000);
            }
        });

        // Remove a classe de erro quando o usuário começa a digitar
        comentarioTextarea.addEventListener('input', function () {
            this.classList.remove('is-invalid');
            const errorMsg = document.querySelector('.chamado-erro-comentario');
            if (errorMsg) {
                errorMsg.remove();
            }
        });
    }
}

/**
 * Adiciona efeitos visuais à timeline
 */
function enhanceTimeline() {
    const timelineItems = document.querySelectorAll('.chamado-timeline-item');

    timelineItems.forEach((item, index) => {
        // Adiciona atraso na animação para cada item
        item.style.animationDelay = `${index * 0.1}s`;

        // Adiciona cores diferentes para os marcadores
        const marker = item.querySelector('.chamado-timeline-marcador');
        if (marker) {
            // Alterna entre cores para os marcadores
            const colors = [
                'var(--chamado-primary)',
                'var(--chamado-success)',
                'var(--chamado-info)',
                'var(--chamado-warning)'
            ];

            marker.style.backgroundColor = colors[index % colors.length];
        }
    });

    // Adiciona efeito de hover nos itens da timeline
    timelineItems.forEach(item => {
        item.addEventListener('mouseenter', function () {
            this.style.transform = 'translateX(5px)';
        });

        item.addEventListener('mouseleave', function () {
            this.style.transform = 'translateX(0)';
        });
    });
}

/**
 * Configura o botão voltar para retornar à página anterior
 */
function setupBotaoVoltar() {
    const botoesVoltar = document.querySelectorAll('.chamado-btn-voltar');

    botoesVoltar.forEach(botao => {
        botao.addEventListener('click', function (e) {
            // Verifica se há uma página anterior no histórico
            if (window.history.length <= 1) {
                e.preventDefault();
                // Fallback para URL base se BASE_URL não estiver definida
                const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '/';
                window.location.href = baseUrl + 'chamados/listar';
            }
            // Caso contrário, o comportamento padrão (history.back()) será executado
        });
    });
}

// Adiciona CSS para animações se não estiver presente
document.addEventListener('DOMContentLoaded', function () {
    if (!document.querySelector('#chamado-visualizar-animations')) {
        const style = document.createElement('style');
        style.id = 'chamado-visualizar-animations';
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
    }
});