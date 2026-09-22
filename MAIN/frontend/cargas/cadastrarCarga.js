const API = '../../backend/api/';
const form = document.querySelector('#form-carga');
const mensagem = document.querySelector('#mensagem');
const codigo = document.querySelector('#codigo_carga');
const descricao = document.querySelector('#descricao');
const origem = document.querySelector('#origem');
const destino = document.querySelector('#destino');
const trem = document.querySelector('#id_trem');
const status = document.querySelector('#status_carga');
const chegada = document.querySelector('#chegada_prevista');
let rotas = [];

async function lerJson(resposta) {
    const texto = await resposta.text();
    try {
        return JSON.parse(texto);
    } catch {
        throw new Error('O servidor retornou uma resposta inválida. Verifique se o Apache e o banco de dados estão ativos.');
    }
}

async function carregarDados() {
    try {
        const [trensResposta, rotasResposta] = await Promise.all([
            fetch(API + 'trens.php', { credentials: 'same-origin' }),
            fetch(API + 'rotas.php', { credentials: 'same-origin' })
        ]);
        const dadosTrens = await lerJson(trensResposta);
        const dadosRotas = await lerJson(rotasResposta);
        (dadosTrens.trens || []).forEach(item => {
            const opcao = new Option(item.prefixo_trem, item.id_trem);
            trem.add(opcao);
        });
        rotas = dadosRotas.rotas || [];
    } catch (erro) {
        mensagem.textContent = erro.message;
    }
}

function preencherPelaRota() {
    const valor = codigo.value.trim().toUpperCase();
    const rota = rotas.find(item => String(item.codigo || item.codigo_rota || '').toUpperCase() === valor);
    if (!rota) return;

    descricao.value = rota.nome || rota.carga || descricao.value;
    origem.value = rota.origem || origem.value;
    destino.value = rota.destino || destino.value;
    const idTrem = rota.id_trem || rota.trem_id;
    if (idTrem) trem.value = idTrem;
    if (rota.status) status.value = rota.status === 'em_andamento' ? 'em_transito' : rota.status;
    const data = rota.chegada || rota.chegada_prevista || rota.previsao;
    if (data) chegada.value = String(data).replace(' ', 'T').slice(0, 16);
    mensagem.textContent = 'Dados da rota ' + valor + ' preenchidos. Informe o peso para concluir.';
}
codigo.addEventListener('change', preencherPelaRota);
codigo.addEventListener('blur', preencherPelaRota);

form.addEventListener('submit', async evento => {
    evento.preventDefault();
    mensagem.textContent = 'Cadastrando carga...';
    const payload = {
        codigo_carga: codigo.value.trim().toUpperCase(),
        descricao: descricao.value.trim(),
        id_trem: Number(trem.value),
        peso_kg: Number(document.querySelector('#peso_kg').value),
        origem: origem.value.trim(),
        destino: destino.value.trim(),
        status_carga: status.value,
        chegada_prevista: chegada.value ? chegada.value.replace('T', ' ') + ':00' : null
    };
    try {
        const resposta = await fetch(API + 'cargas.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const dados = await lerJson(resposta);
        if (!resposta.ok) throw new Error(dados.erro || 'Não foi possível cadastrar a carga.');
        mensagem.textContent = dados.mensagem;
        form.reset();
    } catch (erro) {
        mensagem.textContent = erro.message;
    }
});
carregarDados();

