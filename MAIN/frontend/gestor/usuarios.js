// Gestão de usuários desenvolvida com auxílio de IA (OpenAI Codex).
const API = '../../backend/api/usuarios.php';
const lista = document.getElementById('lista-usuarios');
const mensagem = document.getElementById('mensagem');
const busca = document.getElementById('busca');
const filtroStatus = document.getElementById('filtro-status');
const dialogo = document.getElementById('dialogo-edicao');
const form = document.getElementById('form-usuario');
let usuarios = [];
let idLogado = 0;

async function respostaJson(resposta) {
    const dados = await resposta.json().catch(() => ({}));
    if (resposta.status === 401 || resposta.status === 403) {
        location.href = '../login/login.html';
        throw new Error('Acesso restrito aos gestores.');
    }
    if (!resposta.ok) throw new Error(dados.erro || 'Não foi possível concluir a operação.');
    return dados;
}

function avisar(texto, tipo = '') {
    mensagem.textContent = texto;
    mensagem.dataset.tipo = tipo;
}

function textoCargo(cargo) {
    return ({membro: 'Membro', gestor: 'Gestor', maquinista: 'Maquinista'})[cargo] || cargo;
}

function desenhar() {
    const termo = busca.value.trim().toLocaleLowerCase('pt-BR');
    const status = filtroStatus.value;
    const filtrados = usuarios.filter(usuario => (!status || usuario.status_acesso === status) && [usuario.nome, usuario.nome_usuario, usuario.email, usuario.cidade, usuario.estado, usuario.cargo].some(valor => String(valor || '').toLocaleLowerCase('pt-BR').includes(termo)));
    lista.replaceChildren();
    for (const usuario of filtrados) {
        const linha = document.createElement('tr');
        linha.innerHTML = '<td><span class="nome"></span><span class="detalhe usuario"></span><span class="detalhe email"></span></td><td><span class="local"></span></td><td><span class="cargo"></span></td><td><span class="etiqueta acesso"></span></td><td><span class="etiqueta situacao"></span></td><td><span class="data"></span></td><td><button class="editar" type="button">Editar</button></td>';
        linha.querySelector('.nome').textContent = usuario.nome + (usuario.id_usuario === idLogado ? ' (você)' : '');
        linha.querySelector('.usuario').textContent = `@${usuario.nome_usuario}`;
        linha.querySelector('.email').textContent = usuario.email;
        linha.querySelector('.local').textContent = `${usuario.cidade}, ${usuario.estado} — ${usuario.pais}`;
        linha.querySelector('.cargo').textContent = textoCargo(usuario.cargo);
        const acesso = linha.querySelector('.acesso');
        acesso.textContent = usuario.status_acesso[0].toUpperCase() + usuario.status_acesso.slice(1);
        acesso.classList.add(usuario.status_acesso);
        const situacao = linha.querySelector('.situacao');
        situacao.textContent = usuario.ativo ? 'Ativa' : 'Inativa';
        situacao.classList.add(usuario.ativo ? 'ativo' : 'inativo');
        linha.querySelector('.data').textContent = new Date(usuario.criado_em.replace(' ', 'T')).toLocaleDateString('pt-BR');
        linha.querySelector('.editar').addEventListener('click', () => editar(usuario));
        lista.appendChild(linha);
    }
    if (!filtrados.length) {
        const linha = document.createElement('tr');
        linha.innerHTML = '<td colspan="7">Nenhum usuário encontrado.</td>';
        lista.appendChild(linha);
    }
}

function indicadores() {
    document.getElementById('total').textContent = usuarios.length;
    document.getElementById('aprovados').textContent = usuarios.filter(u => u.status_acesso === 'aprovado').length;
    document.getElementById('pendentes').textContent = usuarios.filter(u => u.status_acesso === 'pendente').length;
    document.getElementById('bloqueados').textContent = usuarios.filter(u => u.status_acesso === 'bloqueado' || !u.ativo).length;
}

function editar(usuario) {
    document.getElementById('id_usuario').value = usuario.id_usuario;
    document.getElementById('nome-edicao').textContent = usuario.nome;
    document.getElementById('cargo').value = usuario.cargo;
    document.getElementById('status_acesso').value = usuario.status_acesso;
    document.getElementById('ativo').checked = usuario.ativo;
    document.getElementById('observacao_acesso').value = usuario.observacao_acesso || '';
    dialogo.showModal();
}

async function carregar() {
    try {
        const dados = await respostaJson(await fetch(API, {credentials: 'same-origin'}));
        usuarios = dados.usuarios || [];
        idLogado = dados.id_usuario_logado;
        indicadores();
        desenhar();
        avisar(`${usuarios.length} usuário(s) cadastrado(s).`);
    } catch (erro) {
        avisar(erro.message, 'erro');
    }
}

form.addEventListener('submit', async evento => {
    evento.preventDefault();
    const id = document.getElementById('id_usuario').value;
    const dados = {cargo: document.getElementById('cargo').value, status_acesso: document.getElementById('status_acesso').value, ativo: document.getElementById('ativo').checked, observacao_acesso: document.getElementById('observacao_acesso').value.trim()};
    try {
        await respostaJson(await fetch(`${API}?id=${id}`, {method: 'PUT', headers: {'Content-Type': 'application/json'}, credentials: 'same-origin', body: JSON.stringify(dados)}));
        dialogo.close();
        await carregar();
        avisar('Usuário atualizado com sucesso.');
    } catch (erro) {
        avisar(erro.message, 'erro');
    }
});

document.getElementById('fechar').addEventListener('click', () => dialogo.close());
document.getElementById('cancelar').addEventListener('click', () => dialogo.close());
busca.addEventListener('input', desenhar);
filtroStatus.addEventListener('change', desenhar);
carregar();
