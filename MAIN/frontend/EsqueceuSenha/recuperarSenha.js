const API = '../../backend/api/';
const mensagem = document.querySelector('#mensagem, #mensagem-reset');

async function requisicao(arquivo, dados) {
    const resposta = await fetch(API + arquivo, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
    });
    const retorno = await resposta.json();
    if (!resposta.ok) throw new Error(retorno.erro || 'Não foi possível concluir a operação.');
    return retorno;
}

document.querySelector('#form-recuperacao')?.addEventListener('submit', async evento => {
    evento.preventDefault();
    mensagem.textContent = 'Enviando código...';
    try {
        const email = document.querySelector('#email').value.trim();
        const dados = await requisicao('solicitar_recuperacao_senha.php', { email });
        sessionStorage.setItem('emailRecuperacao', email);
        mensagem.textContent = dados.mensagem;
        window.location.href = 'codigoVerificacao.html';
    } catch (erro) { mensagem.textContent = erro.message; }
});

document.querySelector('#form-codigo')?.addEventListener('submit', async evento => {
    evento.preventDefault();
    try {
        const codigo = document.querySelector('#codigo').value.trim();
        await requisicao('verificar_codigo_recuperacao.php', { email: sessionStorage.getItem('emailRecuperacao'), codigo });
        sessionStorage.setItem('codigoRecuperacao', codigo);
        window.location.href = 'novaSenha.html';
    } catch (erro) { mensagem.textContent = erro.message; }
});

document.querySelector('#form-nova-senha')?.addEventListener('submit', async evento => {
    evento.preventDefault();
    const senha = document.querySelector('#nova-senha').value;
    if (senha !== document.querySelector('#confirmar-senha').value) { mensagem.textContent = 'As senhas não coincidem.'; return; }
    try {
        const dados = await requisicao('redefinir_senha.php', { email: sessionStorage.getItem('emailRecuperacao'), codigo: sessionStorage.getItem('codigoRecuperacao'), senha });
        mensagem.textContent = dados.mensagem;
        sessionStorage.removeItem('emailRecuperacao'); sessionStorage.removeItem('codigoRecuperacao');
        window.setTimeout(() => window.location.href = '../login/login.html', 1200);
    } catch (erro) { mensagem.textContent = erro.message; }
});

