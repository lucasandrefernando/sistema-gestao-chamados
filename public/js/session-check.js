// public/js/session-check.js
function verificarSessao() {
    fetch(BASE_URL + 'auth/verificar_sessao', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (!data.valid) {
                // Sessão inválida, redireciona para o login
                window.location.href = BASE_URL + 'auth/logout?sessao_encerrada=1';
            }
        })
        .catch(error => console.error('Erro ao verificar sessão:', error));
}

// Inicia a verificação de sessão quando o documento estiver pronto
document.addEventListener('DOMContentLoaded', function () {
    // Verifica a sessão a cada 5 minutos
    setInterval(verificarSessao, 5 * 60 * 1000);
});