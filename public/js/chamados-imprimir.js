/**
 * chamados-imprimir.js - Script avançado para a página de impressão de chamados
 */

document.addEventListener('DOMContentLoaded', function () {
    // Inicializa a página
    initPage();

    // Configura os botões de ação
    setupActionButtons();

    // Gera o QR Code
    generateQRCode();

    // Configura o modal de compartilhamento
    setupShareModal();

    // Adiciona marca d'água para impressão
    setupWatermark();

    // Adiciona hash de verificação
    generateVerificationHash();
});

/**
 * Inicializa a página com configurações básicas
 */
function initPage() {
    // Adiciona classe para animação de entrada
    document.querySelector('.chamados-imprimir-container').classList.add('fade-in');

    // Configura tooltips do Bootstrap (se disponível)
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Adiciona atalho de teclado para impressão (Ctrl+P)
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            window.print();
        }
    });
}

/**
 * Configura os botões de ação
 */
function setupActionButtons() {
    // Botão de exportar PDF
    const pdfButton = document.getElementById('exportPdfBtn');
    if (pdfButton) {
        pdfButton.addEventListener('click', function () {
            // Adiciona classe para otimizar a impressão como PDF
            document.body.classList.add('export-pdf');

            // Usa a função de impressão do navegador para salvar como PDF
            window.print();

            // Remove a classe após a impressão
            setTimeout(function () {
                document.body.classList.remove('export-pdf');
            }, 1000);
        });
    }

    // Botão de compartilhar
    const shareButton = document.getElementById('shareLinkBtn');
    if (shareButton) {
        shareButton.addEventListener('click', function () {
            // Abre o modal de compartilhamento
            const shareModal = new bootstrap.Modal(document.getElementById('shareModal'));
            shareModal.show();
        });
    }

    // Botão de alternar tema
    const themeButton = document.getElementById('toggleThemeBtn');
    if (themeButton) {
        themeButton.addEventListener('click', function () {
            toggleDarkMode();
        });
    }
}

/**
 * Alterna entre modo claro e escuro
 */
function toggleDarkMode() {
    const body = document.body;
    const themeButton = document.getElementById('toggleThemeBtn');

    if (body.classList.contains('dark-mode')) {
        // Muda para modo claro
        body.classList.remove('dark-mode');
        if (themeButton) {
            themeButton.innerHTML = '<i class="fas fa-moon"></i> Modo Escuro';
        }
        localStorage.setItem('chamados-imprimir-theme', 'light');
    } else {
        // Muda para modo escuro
        body.classList.add('dark-mode');
        if (themeButton) {
            themeButton.innerHTML = '<i class="fas fa-sun"></i> Modo Claro';
        }
        localStorage.setItem('chamados-imprimir-theme', 'dark');
    }
}

/**
 * Verifica e aplica o tema salvo
 */
function checkSavedTheme() {
    const savedTheme = localStorage.getItem('chamados-imprimir-theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-mode');
        const themeButton = document.getElementById('toggleThemeBtn');
        if (themeButton) {
            themeButton.innerHTML = '<i class="fas fa-sun"></i> Modo Claro';
        }
    }
}

/**
 * Gera o QR Code com o link do chamado
 */
function generateQRCode() {
    const qrcodeElement = document.getElementById('chamados-imprimir-qrcode');
    if (!qrcodeElement) return;

    // Verifica se a biblioteca QRCode está disponível
    if (typeof QRCode === 'undefined') {
        console.error('Biblioteca QRCode não encontrada');
        return;
    }

    // Obtém a URL atual
    const chamadoUrl = window.location.href;

    // Gera o QR Code
    new QRCode(qrcodeElement, {
        text: chamadoUrl,
        width: 128,
        height: 128,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });

    // Preenche o link de verificação
    const linkElement = document.getElementById('chamados-imprimir-link');
    if (linkElement) {
        linkElement.textContent = chamadoUrl;
    }
}

/**
 * Configura o modal de compartilhamento
 */
function setupShareModal() {
    // Preenche o campo de link
    const shareLinkInput = document.getElementById('shareLink');
    if (shareLinkInput) {
        shareLinkInput.value = window.location.href;
    }

    // Configura o botão de copiar
    const copyLinkBtn = document.getElementById('copyLinkBtn');
    if (copyLinkBtn) {
        copyLinkBtn.addEventListener('click', function () {
            if (shareLinkInput) {
                shareLinkInput.select();
                document.execCommand('copy');

                // Feedback visual
                const originalHTML = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check"></i>';

                setTimeout(() => {
                    this.innerHTML = originalHTML;
                }, 2000);
            }
        });
    }

    // Configura o botão de compartilhar por e-mail
    const emailShareBtn = document.getElementById('emailShareBtn');
    if (emailShareBtn) {
        emailShareBtn.addEventListener('click', function () {
            // Obtém o ID do chamado da URL
            const chamadoId = window.location.pathname.split('/').pop();

            // Cria o link de e-mail
            const subject = encodeURIComponent(`Relatório do Chamado #${chamadoId}`);
            const body = encodeURIComponent(`Segue o link para o relatório do chamado #${chamadoId}:\n\n${window.location.href}`);

            window.location.href = `mailto:?subject=${subject}&body=${body}`;
        });
    }

    // Configura o botão de compartilhar via WhatsApp
    const whatsappShareBtn = document.getElementById('whatsappShareBtn');
    if (whatsappShareBtn) {
        whatsappShareBtn.addEventListener('click', function () {
            // Obtém o ID do chamado da URL
            const chamadoId = window.location.pathname.split('/').pop();

            // Cria o link do WhatsApp
            const text = encodeURIComponent(`Relatório do Chamado #${chamadoId}: ${window.location.href}`);

            window.open(`https://wa.me/?text=${text}`, '_blank');
        });
    }
}

/**
 * Configura a marca d'água para impressão
 */
function setupWatermark() {
    const watermark = document.querySelector('.chamados-imprimir-watermark');
    if (!watermark) return;

    // Obtém o nome da empresa do cabeçalho
    const empresaNome = document.querySelector('.chamados-imprimir-titulo').textContent || 'DOCUMENTO OFICIAL';

    // Define o texto da marca d'água
    watermark.textContent = empresaNome;

    // Adiciona evento para quando a impressão começar
    window.addEventListener('beforeprint', function () {
        watermark.style.display = 'block';
    });

    // Adiciona evento para quando a impressão terminar
    window.addEventListener('afterprint', function () {
        watermark.style.display = 'none';
    });
}

/**
 * Gera um hash de verificação para o documento
 */
function generateVerificationHash() {
    const hashElement = document.getElementById('chamados-imprimir-hash');
    if (!hashElement) return;

    // Obtém o ID do chamado da URL
    const chamadoId = window.location.pathname.split('/').pop();

    // Gera um hash simples baseado no ID do chamado e na data atual
    const now = new Date();
    const dateStr = now.getFullYear() + '' + (now.getMonth() + 1) + '' + now.getDate();

    // Função simples para gerar um hash (apenas para demonstração)
    function simpleHash(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }
        return Math.abs(hash).toString(16).toUpperCase().padStart(8, '0');
    }

    const verificationHash = simpleHash(chamadoId + dateStr + 'CHAMADO');
    hashElement.textContent = verificationHash;
}

/**
 * Função para adicionar animações de entrada aos elementos
 */
function addEntryAnimations() {
    const elements = [
        '.chamados-imprimir-header',
        '.chamados-imprimir-resumo-item',
        '.chamados-imprimir-secao',
        '.chamados-imprimir-comentario',
        '.chamados-imprimir-timeline-item'
    ];

    elements.forEach((selector, index) => {
        const items = document.querySelectorAll(selector);
        items.forEach((item, itemIndex) => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';

            setTimeout(() => {
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, 100 + (index * 50) + (itemIndex * 50));
        });
    });
}

// Verifica o tema salvo e aplica animações após o carregamento da página
window.addEventListener('load', function () {
    checkSavedTheme();
    addEntryAnimations();
});