// Integração com o backend desenvolvida com auxílio de IA (OpenAI Codex).

const formularioLogin = document.getElementById('form-login');
const mensagemLogin = document.getElementById('mensagem-login');

const paginasPorCargo = {
    membro: '../PaginaInicial/paginaInicialMembro.html',
    gestor: '../PaginaInicial/paginaInicialGestor.html',
    maquinista: '../PaginaInicial/paginaInicialMaquinista.html',
};

function mostrarMensagemLogin(mensagem, tipo = 'erro') {
    mensagemLogin.textContent = mensagem;
    mensagemLogin.dataset.tipo = tipo;
}

formularioLogin.addEventListener('submit', async (evento) => {
    evento.preventDefault();

    const botaoLogin = formularioLogin.querySelector('button[type="submit"]');
    botaoLogin.disabled = true;
    mostrarMensagemLogin('Entrando...', 'informacao');

    const credenciais = {
        email: document.getElementById('email').value.trim(),
        senha: document.getElementById('senha').value,
    };

    try {
        const resposta = await fetch('../../backend/api/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify(credenciais),
        });

        const resultado = await resposta.json();

        if (!resposta.ok) {
            mostrarMensagemLogin(resultado.erro || 'Não foi possível realizar o login.');
            return;
        }

        const paginaInicial = paginasPorCargo[resultado.usuario.cargo];

        if (!paginaInicial) {
            mostrarMensagemLogin('O usuário possui um cargo inválido.');
            return;
        }

        mostrarMensagemLogin('Login realizado! Redirecionando...', 'sucesso');
        window.location.href = paginaInicial;
    } catch {
        mostrarMensagemLogin('Não foi possível conectar ao servidor.');
    } finally {
        botaoLogin.disabled = false;
    }
});
