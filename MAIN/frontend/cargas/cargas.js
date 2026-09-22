const API = '../../backend/api/';
const busca = document.querySelector('#busca');
const status = document.querySelector('#status');
const lista = document.querySelector('#lista-cargas');
const mensagem = document.querySelector('#mensagem');

function escapar(valor) {
    const div = document.createElement('div');
    div.textContent = valor ?? '';
    return div.innerHTML;
}

function dataFormatada(valor) {
    if (!valor) return 'Sem previsão';
    return String(valor).replace('T', ' ').slice(0, 16);
}

function linha(item) {
    const codigo = item.codigo_carga || item.codigo || item.codigo_rota || '';
    const nome = item.descricao || item.nome || item.carga || 'Rota monitorada';
    const prefixo = item.prefixo_trem || item.prefixo || item.trem || 'Não atribuído';
    const situacao = String(item.status_carga || item.status || 'aguardando').replaceAll('_', ' ');
    const previsao = item.chegada_prevista || item.chegada || item.previsao;
    return '<tr><td>' + escapar(prefixo) + '</td><td>' + escapar(codigo) + '</td><td>' +
        escapar(nome) + '</td><td>' + escapar(item.origem) + ' → ' + escapar(item.destino) +
        '</td><td><span class="status">' + escapar(situacao) + '</span></td><td>' +
        escapar(dataFormatada(previsao)) + '</td></tr>';
}

async function jsonSeguro(resposta) {
    const texto = await resposta.text();
    try { return JSON.parse(texto); }
    catch { throw new Error('O servidor retornou uma resposta inválida. Execute as migrações e confira o Apache.'); }
}

async function carregar() {
    mensagem.textContent = 'Carregando...';
    lista.innerHTML = '';
    const termo = busca.value.trim();
    try {
        const params = new URLSearchParams();
        if (termo) params.set('busca', termo);
        if (status.value) params.set('status', status.value);

        const respostaRotas = await fetch(API + 'rotas.php?' + params, { credentials: 'same-origin' });
        const dadosRotas = await jsonSeguro(respostaRotas);
        const rotas = dadosRotas.rotas || [];
        let cargas = [];
        try {
            const respostaCargas = await fetch(API + 'cargas.php?' + params, { credentials: 'same-origin' });
            const dadosCargas = await jsonSeguro(respostaCargas);
            cargas = dadosCargas.cargas || [];
        } catch (erro) {
            mensagem.textContent = 'Rotas carregadas. Para listar cargas cadastradas, execute a migração de cargas.';
        }
        const itens = [...rotas, ...cargas];
        lista.innerHTML = itens.map(linha).join('');
        if (!itens.length) mensagem.textContent = 'Nenhuma carga ou rota encontrada.';
        else if (!mensagem.textContent.includes('migração')) mensagem.textContent = itens.length + ' registro(s) encontrado(s).';
    } catch (erro) {
        mensagem.textContent = erro.message;
    }
}
document.querySelector('#filtrar').addEventListener('click', carregar);
busca.addEventListener('keydown', evento => { if (evento.key === 'Enter') carregar(); });
carregar();

