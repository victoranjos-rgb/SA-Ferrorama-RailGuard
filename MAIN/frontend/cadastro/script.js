const buttonCadastro = document.getElementById("buttonCadastro");
// Integração com o backend desenvolvida com auxílio de IA (OpenAI Codex).

const CHAVE_CADASTRO = 'railguard_cadastro_pendente';

const formularioInicial = document.getElementById('form-cadastro-inicial');
const formularioFinal = document.getElementById('form-cadastro-final');
const elementoMensagem = document.getElementById('mensagem-cadastro');

function mostrarMensagem(mensagem, tipo = 'erro') {
    if (!elementoMensagem) {
        return;
    }

    elementoMensagem.textContent = mensagem;
    elementoMensagem.dataset.tipo = tipo;
}

if (formularioInicial) {
    formularioInicial.addEventListener('submit', (evento) => {
        evento.preventDefault();

        const senha = document.getElementById('senha').value;
        const confirmacaoSenha = document.getElementById('confirmacao_senha').value;

        if (senha !== confirmacaoSenha) {
            mostrarMensagem('As senhas não conferem.');
            return;
        }

        const cadastroPendente = {
            nome: document.getElementById('nome').value.trim(),
            nome_usuario: document.getElementById('nome_usuario').value.trim(),
            email: document.getElementById('email').value.trim(),
            senha,
            confirmacao_senha: confirmacaoSenha,
        };

        sessionStorage.setItem(CHAVE_CADASTRO, JSON.stringify(cadastroPendente));
        window.location.href = 'finalizarCadastro.html';
    });
}

if (formularioFinal) {
    const cadastroPendente = sessionStorage.getItem(CHAVE_CADASTRO);

    if (!cadastroPendente) {
        mostrarMensagem('Preencha primeiro os dados iniciais do cadastro.');
        formularioFinal.querySelector('button[type="submit"]').disabled = true;
    }

    formularioFinal.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        let dadosIniciais;

        try {
            dadosIniciais = JSON.parse(sessionStorage.getItem(CHAVE_CADASTRO));
        } catch {
            mostrarMensagem('Os dados iniciais do cadastro são inválidos. Preencha novamente.');
            return;
        }

        if (!dadosIniciais) {
            mostrarMensagem('Preencha primeiro os dados iniciais do cadastro.');
            return;
        }

        const botaoCadastrar = formularioFinal.querySelector('button[type="submit"]');
        botaoCadastrar.disabled = true;
        mostrarMensagem('Realizando cadastro...', 'informacao');

        const dadosCadastro = {
            ...dadosIniciais,
            pais: document.getElementById('pais').value.trim(),
            estado: document.getElementById('estado').value.trim(),
            cidade: document.getElementById('cidade').value.trim(),
            cargo: document.getElementById('cargo').value,
        };

        try {
            const resposta = await fetch('../../backend/api/cadastro.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(dadosCadastro),
            });

            const resultado = await resposta.json();

            if (!resposta.ok) {
                const mensagem = resultado.erros?.join(' ') || resultado.erro || 'Não foi possível realizar o cadastro.';
                mostrarMensagem(mensagem);
                return;
            }

            sessionStorage.removeItem(CHAVE_CADASTRO);
            mostrarMensagem('Cadastro realizado! Redirecionando para o login...', 'sucesso');

            window.setTimeout(() => {
                window.location.href = '../login/login.html';
            }, 1000);
        } catch {
            mostrarMensagem('Não foi possível conectar ao servidor. Confirme se o Apache está iniciado.');
        } finally {
            botaoCadastrar.disabled = false;
        }
    });
}
