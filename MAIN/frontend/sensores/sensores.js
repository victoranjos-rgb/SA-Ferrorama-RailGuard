// CRUD desenvolvido com auxílio de IA (OpenAI Codex), adaptado do repositório do professor.
const API = '../../backend/api/sensores.php';
const API_ROTAS = '../../backend/api/rotas.php';
const form = document.getElementById('form-sensor');
const lista = document.getElementById('lista-sensores');
const mensagem = document.getElementById('mensagem');
const busca = document.getElementById('busca');
let sensores = [];

async function respostaJson(resposta) {
    const dados = await resposta.json().catch(() => ({}));
    if (resposta.status === 401) {
        location.href = '../login/login.html';
        throw new Error('Sessão expirada.');
    }
    if (!resposta.ok) throw new Error(dados.erros?.join(' ') || dados.erro || 'Não foi possível concluir a operação.');
    return dados;
}

function avisar(texto, tipo = '') {
    mensagem.textContent = texto;
    mensagem.dataset.tipo = tipo;
}

function abrirFormulario(sensor = null) {
    form.hidden = false;
    form.reset();
    document.getElementById('id_sensor').value = sensor?.id_sensor || '';
    document.getElementById('titulo-formulario').textContent = sensor ? 'Editar sensor' : 'Cadastrar sensor';
    if (sensor) {
        for (const campo of ['codigo_sensor', 'tipo_sensor', 'modelo_sensor', 'id_rota', 'localizacao', 'unidade_medida', 'limite_alerta', 'status_sensor']) document.getElementById(campo).value = sensor[campo] ?? '';
    }
    form.scrollIntoView({behavior: 'smooth', block: 'start'});
}

function fecharFormulario() {
    form.hidden = true;
    form.reset();
    document.getElementById('id_sensor').value = '';
}

function tipoLegivel(tipo) {
    return ({proximidade: 'Proximidade', velocidade: 'Velocidade', vibracao: 'Vibração', temperatura: 'Temperatura'})[tipo] || tipo;
}

function desenhar() {
    const termo = busca.value.trim().toLocaleLowerCase('pt-BR');
    const filtrados = sensores.filter(sensor => [sensor.codigo_sensor, sensor.tipo_sensor, sensor.modelo_sensor, sensor.codigo_rota, sensor.nome_rota, sensor.localizacao].some(valor => String(valor || '').toLocaleLowerCase('pt-BR').includes(termo)));
    lista.replaceChildren();
    for (const sensor of filtrados) {
        const linha = document.createElement('tr');
        const leitura = sensor.ultima_leitura === null ? 'Sem leitura' : `${sensor.ultima_leitura} ${sensor.unidade_medida}`;
        const emAlerta = sensor.ultima_leitura !== null && sensor.limite_alerta !== null && Number(sensor.ultima_leitura) > Number(sensor.limite_alerta);
        linha.innerHTML = '<td><span class="sensor-codigo"></span><span class="sensor-modelo"></span></td><td class="tipo"></td><td><span class="rota"></span><span class="local"></span></td><td class="leitura"></td><td><span class="status"></span></td><td class="acoes"><button class="editar" type="button">Editar</button><button class="excluir" type="button">Excluir</button></td>';
        linha.querySelector('.sensor-codigo').textContent = sensor.codigo_sensor;
        linha.querySelector('.sensor-modelo').textContent = sensor.modelo_sensor;
        linha.querySelector('.tipo').textContent = tipoLegivel(sensor.tipo_sensor);
        linha.querySelector('.rota').textContent = sensor.codigo_rota || 'Sem rota';
        linha.querySelector('.local').textContent = sensor.localizacao;
        linha.querySelector('.leitura').textContent = leitura;
        if (emAlerta) linha.querySelector('.leitura').classList.add('alerta');
        const status = linha.querySelector('.status');
        status.textContent = sensor.status_sensor === 'manutencao' ? 'Em manutenção' : sensor.status_sensor[0].toUpperCase() + sensor.status_sensor.slice(1);
        status.classList.add(sensor.status_sensor);
        linha.querySelector('.editar').addEventListener('click', () => abrirFormulario(sensor));
        linha.querySelector('.excluir').addEventListener('click', () => excluir(sensor));
        lista.appendChild(linha);
    }
    if (!filtrados.length) {
        const linha = document.createElement('tr');
        linha.innerHTML = '<td colspan="6">Nenhum sensor encontrado.</td>';
        lista.appendChild(linha);
    }
}

function atualizarIndicadores() {
    document.getElementById('total-sensores').textContent = sensores.length;
    document.getElementById('sensores-ativos').textContent = sensores.filter(s => s.status_sensor === 'ativo').length;
    document.getElementById('sensores-manutencao').textContent = sensores.filter(s => s.status_sensor === 'manutencao').length;
    document.getElementById('sensores-alerta').textContent = sensores.filter(s => s.ultima_leitura !== null && s.limite_alerta !== null && Number(s.ultima_leitura) > Number(s.limite_alerta)).length;
}

async function carregarRotas() {
    const dados = await respostaJson(await fetch(API_ROTAS, {credentials: 'same-origin'}));
    const seletor = document.getElementById('id_rota');
    for (const rota of dados.rotas || []) {
        const opcao = document.createElement('option');
        opcao.value = rota.id_rota;
        opcao.textContent = `${rota.codigo_rota} — ${rota.nome_rota}`;
        seletor.appendChild(opcao);
    }
}

async function carregar() {
    try {
        const dados = await respostaJson(await fetch(API, {credentials: 'same-origin'}));
        sensores = dados.sensores || [];
        atualizarIndicadores();
        desenhar();
        avisar(sensores.length ? `${sensores.length} sensor(es) cadastrado(s).` : 'Nenhum sensor cadastrado.');
    } catch (erro) {
        avisar(erro.message, 'erro');
    }
}

async function excluir(sensor) {
    if (!confirm(`Excluir o sensor ${sensor.codigo_sensor}?`)) return;
    try {
        await respostaJson(await fetch(`${API}?id=${sensor.id_sensor}`, {method: 'DELETE', headers: {'Content-Type': 'application/json'}, credentials: 'same-origin', body: '{}'}));
        await carregar();
    } catch (erro) {
        avisar(erro.message, 'erro');
    }
}

form.addEventListener('submit', async evento => {
    evento.preventDefault();
    const id = document.getElementById('id_sensor').value;
    const dados = {};
    for (const campo of ['codigo_sensor', 'tipo_sensor', 'modelo_sensor', 'id_rota', 'localizacao', 'unidade_medida', 'limite_alerta', 'status_sensor']) dados[campo] = document.getElementById(campo).value;
    try {
        await respostaJson(await fetch(id ? `${API}?id=${id}` : API, {method: id ? 'PUT' : 'POST', headers: {'Content-Type': 'application/json'}, credentials: 'same-origin', body: JSON.stringify(dados)}));
        fecharFormulario();
        await carregar();
    } catch (erro) {
        avisar(erro.message, 'erro');
    }
});

document.getElementById('novo-sensor').addEventListener('click', () => abrirFormulario());
document.getElementById('fechar-formulario').addEventListener('click', fecharFormulario);
document.getElementById('cancelar').addEventListener('click', fecharFormulario);
busca.addEventListener('input', desenhar);
Promise.all([carregarRotas(), carregar()]).catch(erro => avisar(erro.message, 'erro'));
