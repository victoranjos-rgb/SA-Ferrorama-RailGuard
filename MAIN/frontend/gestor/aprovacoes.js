// Integração desenvolvida com auxílio de IA (OpenAI Codex).

const listaUsuarios = document.getElementById('lista-usuarios');
const mensagem = document.getElementById('mensagem');
const modeloUsuario = document.getElementById('modelo-usuario');

function exibirMensagem(texto, tipo = 'informacao') {
    mensagem.textContent = texto;
    mensagem.dataset.tipo = tipo;
}

function preencherCartao(usuario) {
    const fragmento = modeloUsuario.content.cloneNode(true);
    const cartao = fragmento.querySelector('.cartao-usuario');
    const formulario = fragmento.querySelector('.form-analise');

    cartao.querySelector('[data-campo="nome"]').textContent = usuario.nome;
    cartao.querySelector('[data-campo="nome_usuario"]').textContent = usuario.nome_usuario;
    cartao.querySelector('[data-campo="email"]').textContent = usuario.email;
    cartao.querySelector('[data-campo="local"]').textContent = `${usuario.cidade}, ${usuario.estado} - ${usuario.pais}`;
    cartao.querySelector('[data-campo="cargo_solicitado"]').textContent = usuario.cargo;
    formulario.elements.id_usuario.value = usuario.id_usuario;
    formulario.elements.cargo.value = usuario.cargo;

    formulario.addEventListener('submit', analisarUsuario);
    return fragmento;
}

async function carregarPendentes() {
    exibirMensagem('Carregando cadastros...');
    listaUsuarios.replaceChildren();

    try {
        const resposta = await fetch('../../backend/api/usuarios_pendentes.php', {
            credentials: 'same-origin',
        });
        const resultado = await resposta.json();

        if (resposta.status === 401 || resposta.status === 403) {
            window.location.href = '../login/login.html';
            return;
        }

        if (!resposta.ok) {
            throw new Error(resultado.erro || 'Não foi possível carregar os cadastros.');
        }

        if (resultado.total === 0) {
            exibirMensagem('Não existem cadastros aguardando aprovação.');
            return;
        }

        resultado.usuarios.forEach((usuario) => {
            listaUsuarios.appendChild(preencherCartao(usuario));
        });
        exibirMensagem(`${resultado.total} cadastro(s) aguardando análise.`);
    } catch (erro) {
        exibirMensagem(erro.message, 'erro');
    }
}

async function analisarUsuario(evento) {
    evento.preventDefault();

    const formulario = evento.currentTarget;
    const botao = evento.submitter;
    const botoes = formulario.querySelectorAll('button');
    botoes.forEach((item) => { item.disabled = true; });

    const dados = {
        id_usuario: Number(formulario.elements.id_usuario.value),
        decisao: botao.value,
        cargo: formulario.elements.cargo.value,
        observacao: formulario.elements.observacao.value.trim(),
    };

    try {
        const resposta = await fetch('../../backend/api/analisar_usuario.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify(dados),
        });
        const resultado = await resposta.json();

        if (!resposta.ok) {
            throw new Error(resultado.erro || 'Não foi possível analisar o cadastro.');
        }

        exibirMensagem(resultado.mensagem);
        await carregarPendentes();
    } catch (erro) {
        exibirMensagem(erro.message, 'erro');
        botoes.forEach((item) => { item.disabled = false; });
    }
}

carregarPendentes();
