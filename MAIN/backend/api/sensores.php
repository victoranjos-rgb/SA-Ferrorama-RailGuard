<?php

// CRUD de sensores adaptado do CRUD do professor com auxílio de IA (OpenAI Codex).
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/resposta.php';
require_once __DIR__ . '/sessao_helper.php';

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$usuario = exigirUsuarioAutenticado();

if (in_array($metodo, ['POST', 'PUT', 'DELETE'], true) && $usuario['cargo'] !== 'gestor') {
    responderErro(403, 'Somente gestores podem alterar sensores.');
}

function buscarSensor(mysqli $conexao, int $id): array
{
    $stmt = $conexao->prepare(
        'SELECT s.*, r.codigo_rota, r.nome_rota, t.prefixo_trem, t.modelo_trem FROM sensores s LEFT JOIN rotas r ON r.id_rota=s.id_rota LEFT JOIN trens t ON t.id_trem=s.id_trem WHERE s.id_sensor=?'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $sensor = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$sensor) {
        responderErro(404, 'Sensor nao encontrado.');
    }

    $sensor['id_sensor'] = (int) $sensor['id_sensor'];
    $sensor['id_rota'] = $sensor['id_rota'] === null ? null : (int) $sensor['id_rota'];
    $sensor['id_trem'] = $sensor['id_trem'] === null ? null : (int) $sensor['id_trem'];
    $sensor['limite_alerta'] = $sensor['limite_alerta'] === null ? null : (float) $sensor['limite_alerta'];
    $sensor['ultima_leitura'] = $sensor['ultima_leitura'] === null ? null : (float) $sensor['ultima_leitura'];
    return $sensor;
}

function validarSensor(mysqli $conexao, array $dados): array
{
    $instalacao = trim((string) ($dados['instalacao_sensor'] ?? (($dados['id_trem'] ?? '') !== '' ? 'trem' : 'trilho')));
    $sensor = [
        'codigo_sensor' => strtoupper(trim((string) ($dados['codigo_sensor'] ?? ''))),
        'tipo_sensor' => trim((string) ($dados['tipo_sensor'] ?? '')),
        'modelo_sensor' => trim((string) ($dados['modelo_sensor'] ?? '')),
        'id_rota' => ($dados['id_rota'] ?? '') === '' ? null : (int) $dados['id_rota'],
        'id_trem' => ($dados['id_trem'] ?? '') === '' ? null : (int) $dados['id_trem'],
        'localizacao' => trim((string) ($dados['localizacao'] ?? '')),
        'unidade_medida' => trim((string) ($dados['unidade_medida'] ?? '')),
        'limite_alerta' => ($dados['limite_alerta'] ?? '') === '' ? null : (float) $dados['limite_alerta'],
        'status_sensor' => trim((string) ($dados['status_sensor'] ?? 'ativo')),
    ];
    $erros = [];

    if (!in_array($instalacao, ['trilho', 'trem'], true)) $erros[] = 'Selecione onde o sensor sera instalado.';
    if ($instalacao === 'trilho') {
        $sensor['id_trem'] = null;
        if ($sensor['id_rota'] === null) $erros[] = 'Selecione a rota do sensor instalado nos trilhos.';
    }
    if ($instalacao === 'trem') {
        $sensor['id_rota'] = null;
        if ($sensor['id_trem'] === null) $erros[] = 'Selecione o trem onde o sensor sera instalado.';
    }
    if (!preg_match('/^[A-Z0-9-]{2,30}$/', $sensor['codigo_sensor'])) $erros[] = 'Informe um codigo de 2 a 30 caracteres, usando letras, numeros ou hifen.';
    if (!in_array($sensor['tipo_sensor'], ['proximidade', 'velocidade', 'vibracao', 'temperatura'], true)) $erros[] = 'Selecione um tipo de sensor valido.';
    if ($sensor['modelo_sensor'] === '' || mb_strlen($sensor['modelo_sensor']) > 80) $erros[] = 'Informe o modelo do sensor com ate 80 caracteres.';
    if ($sensor['localizacao'] === '' || mb_strlen($sensor['localizacao']) > 150) $erros[] = 'Informe a localizacao do sensor.';
    if ($sensor['unidade_medida'] === '' || mb_strlen($sensor['unidade_medida']) > 20) $erros[] = 'Informe a unidade de medida.';
    if ($sensor['limite_alerta'] !== null && $sensor['limite_alerta'] < 0) $erros[] = 'O limite de alerta nao pode ser negativo.';
    if (!in_array($sensor['status_sensor'], ['ativo', 'inativo', 'manutencao'], true)) $erros[] = 'Selecione um status valido.';
    if ($sensor['id_rota'] !== null) {
        $stmt = $conexao->prepare('SELECT 1 FROM rotas WHERE id_rota=?');
        $stmt->bind_param('i', $sensor['id_rota']);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_row()) $erros[] = 'A rota selecionada nao existe.';
        $stmt->close();
    }
    if ($sensor['id_trem'] !== null) {
        $stmt = $conexao->prepare('SELECT 1 FROM trens WHERE id_trem=?');
        $stmt->bind_param('i', $sensor['id_trem']);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_row()) $erros[] = 'O trem selecionado nao existe.';
        $stmt->close();
    }

    if ($erros) responderJson(422, ['erros' => $erros]);
    return $sensor;
}

if ($metodo === 'GET') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id > 0) responderJson(200, buscarSensor($conexao, $id));

    $resultado = $conexao->query(
        'SELECT s.*, r.codigo_rota, r.nome_rota, t.prefixo_trem, t.modelo_trem FROM sensores s LEFT JOIN rotas r ON r.id_rota=s.id_rota LEFT JOIN trens t ON t.id_trem=s.id_trem ORDER BY s.codigo_sensor'
    );
    $sensores = [];
    while ($linha = $resultado->fetch_assoc()) {
        $linha['id_sensor'] = (int) $linha['id_sensor'];
        $linha['id_rota'] = $linha['id_rota'] === null ? null : (int) $linha['id_rota'];
        $linha['id_trem'] = $linha['id_trem'] === null ? null : (int) $linha['id_trem'];
        $linha['limite_alerta'] = $linha['limite_alerta'] === null ? null : (float) $linha['limite_alerta'];
        $linha['ultima_leitura'] = $linha['ultima_leitura'] === null ? null : (float) $linha['ultima_leitura'];
        $sensores[] = $linha;
    }
    responderJson(200, ['total' => count($sensores), 'sensores' => $sensores]);
}

if ($metodo === 'POST' || $metodo === 'PUT') {
    $dados = lerDadosRequisicao();
    $sensor = validarSensor($conexao, $dados);
    $id = (int) ($_GET['id'] ?? ($dados['id_sensor'] ?? 0));
    if ($metodo === 'PUT') buscarSensor($conexao, $id);

    $sql = $metodo === 'POST'
        ? 'INSERT INTO sensores (codigo_sensor,tipo_sensor,modelo_sensor,id_rota,id_trem,localizacao,unidade_medida,limite_alerta,status_sensor) VALUES (?,?,?,?,?,?,?,?,?)'
        : 'UPDATE sensores SET codigo_sensor=?,tipo_sensor=?,modelo_sensor=?,id_rota=?,id_trem=?,localizacao=?,unidade_medida=?,limite_alerta=?,status_sensor=? WHERE id_sensor=?';
    $stmt = $conexao->prepare($sql);
    if ($metodo === 'POST') {
        $stmt->bind_param('sssiissds', $sensor['codigo_sensor'], $sensor['tipo_sensor'], $sensor['modelo_sensor'], $sensor['id_rota'], $sensor['id_trem'], $sensor['localizacao'], $sensor['unidade_medida'], $sensor['limite_alerta'], $sensor['status_sensor']);
    } else {
        $stmt->bind_param('sssiissdsi', $sensor['codigo_sensor'], $sensor['tipo_sensor'], $sensor['modelo_sensor'], $sensor['id_rota'], $sensor['id_trem'], $sensor['localizacao'], $sensor['unidade_medida'], $sensor['limite_alerta'], $sensor['status_sensor'], $id);
    }
    try {
        $stmt->execute();
        if ($metodo === 'POST') $id = $conexao->insert_id;
    } catch (mysqli_sql_exception $erro) {
        responderErro($erro->getCode() === 1062 ? 409 : 500, $erro->getCode() === 1062 ? 'O codigo do sensor ja esta cadastrado.' : 'Nao foi possivel salvar o sensor.');
    }
    $stmt->close();
    responderJson($metodo === 'POST' ? 201 : 200, buscarSensor($conexao, $id));
}

if ($metodo === 'DELETE') {
    $dados = lerDadosRequisicao();
    $id = (int) ($_GET['id'] ?? ($dados['id_sensor'] ?? 0));
    buscarSensor($conexao, $id);
    $stmt = $conexao->prepare('DELETE FROM sensores WHERE id_sensor=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    responderJson(200, ['mensagem' => 'Sensor excluido com sucesso.']);
}

responderErro(405, 'Metodo nao permitido.');
