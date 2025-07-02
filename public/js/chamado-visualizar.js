/**
 * chamado-visualizar.js - Script específico para a página de visualização de chamados
 */

document.addEventListener('DOMContentLoaded', function () {
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
});

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
                document.getElementById('status_id').focus();
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
                document.getElementById('setor_id').focus();
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
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            submitBtn.disabled = true;

            // Simula o envio (você pode remover isso em produção)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
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
 * Função para adicionar um novo comentário via AJAX
 * Esta é uma função de exemplo que pode ser implementada para adicionar comentários sem recarregar a página
 */
function adicionarComentarioAjax(chamadoId, comentario, callback) {
    // Implementação de exemplo - substitua por sua lógica real de AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('POST', `/chamados/adicionarComentarioAjax/${chamadoId}`, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.onload = function () {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Adiciona o novo comentário à lista
                    adicionarComentarioDOM(response.comentario);
                    if (callback) callback(null, response);
                } else {
                    if (callback) callback(new Error(response.message || 'Erro ao adicionar comentário'), null);
                }
            } catch (e) {
                if (callback) callback(new Error('Erro ao processar resposta do servidor'), null);
            }
        } else {
            if (callback) callback(new Error('Erro na requisição: ' + xhr.status), null);
        }
    };

    xhr.onerror = function () {
        if (callback) callback(new Error('Erro de rede'), null);
    };

    xhr.send(`comentario=${encodeURIComponent(comentario)}`);
}

/**
 * Função para adicionar um novo comentário ao DOM
 */
function adicionarComentarioDOM(comentario) {
    const comentariosList = document.querySelector('.chamado-comentarios-lista');
    const semComentarios = document.querySelector('.chamado-sem-comentarios');

    if (semComentarios) {
        semComentarios.remove();
    }

    if (!comentariosList) {
        // Cria a lista de comentários se não existir
        const novaLista = document.createElement('div');
        novaLista.className = 'chamado-comentarios-lista';

        const cardBody = document.querySelector('.chamado-comentarios-card .chamado-card-body');
        if (cardBody) {
            cardBody.insertBefore(novaLista, cardBody.firstChild);
        }
    }

    // Cria o elemento do novo comentário
    const novoComentario = document.createElement('div');
    novoComentario.className = 'chamado-comentario';
    novoComentario.style.opacity = '0';
    novoComentario.style.transform = 'translateY(20px)';

    // Preenche o HTML do comentário
    novoComentario.innerHTML = `
        <div class="chamado-comentario-avatar">
            <i class="fas fa-user"></i>
        </div>
        <div class="chamado-comentario-conteudo">
            <div class="chamado-comentario-header">
                <h4 class="chamado-comentario-autor">${comentario.usuario_nome || 'Usuário'}</h4>
                <span class="chamado-comentario-data">${comentario.data_formatada || 'Agora'}</span>
            </div>
            <div class="chamado-comentario-texto">
                ${comentario.comentario.replace(/\n/g, '<br>')}
            </div>
        </div>
    `;

    // Adiciona o comentário à lista
    document.querySelector('.chamado-comentarios-lista').appendChild(novoComentario);

    // Anima a entrada do comentário
    setTimeout(() => {
        novoComentario.style.transition = 'all 0.5s ease';
        novoComentario.style.opacity = '1';
        novoComentario.style.transform = 'translateY(0)';
    }, 10);
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
                window.location.href = BASE_URL + 'chamados/listar';
            }
            // Caso contrário, o comportamento padrão (history.back()) será executado
        });
    });
}