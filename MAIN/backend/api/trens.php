<?php

// CRUD adaptado do repositório do professor com auxílio de IA (OpenAI Codex).

require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/resposta.php';
require_once __DIR__ . '/sessao_helper.php';

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$usuario = exigirUsuarioAutenticado();

if (in_array($metodo, ['POST', 'PUT', 'DELETE'], true) && $usuario['cargo'] !== 'gestor') {
    responderErro(403, 'Somente gestores podem alterar trens.');
}

function validarTrem(array $dados): array
{
    $trem = [
        'prefixo_trem' => strtoupper(trim((string) ($dados['prefixo_trem'] ?? ''))),
        'modelo_trem' => trim((string) ($dados['modelo_trem'] ?? '')),
        'tipo_combustivel' => trim((string) ($dados['tipo_combustivel'] ?? '')),
        'ano_fabricacao' => (int) ($dados['ano_fabricacao'] ?? 0),
        'capacidade_toneladas' => (float) ($dados['capacidade_toneladas'] ?? 0),
        'velocidade_maxima_kmh' => (float) ($dados['velocidade_maxima_kmh'] ?? 0),
        'situacao_trem' => trim((string) ($dados['situacao_trem'] ?? 'ativo')),
    ];
    $erros = [];

    if (!preg_match('/^[A-Z0-9-]{2,20}$/', $trem['prefixo_trem'])) {
        $erros[] = 'Informe um prefixo de 2 a 20 caracteres.';
    }
    if ($trem['modelo_trem'] === '' || mb_strlen($trem['modelo_trem']) > 80) {
        $erros[] = 'Informe um modelo com ate 80 caracteres.';
    }
    if (!in_array($trem['tipo_combustivel'], ['diesel', 'eletrico', 'hibrido', 'outro'], true)) {
        $erros[] = 'Selecione um tipo de combustivel valido.';
    }
    if ($trem['ano_fabricacao'] < 1900 || $trem['ano_fabricacao'] > (int) date('Y') + 1) {
        $erros[] = 'Informe um ano de fabricacao valido.';
    }
    if ($trem['capacidade_toneladas'] <= 0) {
        $erros[] = 'A capacidade deve ser maior que zero.';
    }
    if ($trem['velocidade_maxima_kmh'] <= 0) {
        $erros[] = 'A velocidade maxima deve ser maior que zero.';
    }
    if (!in_array($trem['situacao_trem'], ['ativo', 'manutencao', 'inativo'], true)) {
        $erros[] = 'Selecione uma situacao valida.';
    }

    if ($erros) {
        responderJson(422, ['erros' => $erros]);
    }

    return $trem;
}

function buscarTrem(mysqli $conexao, int $id): array
{
    $stmt = $conexao->prepare('SELECT * FROM trens WHERE id_trem = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $trem = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$trem) {
        responderErro(404, 'Trem nao encontrado.');
    }

    $trem['id_trem'] = (int) $trem['id_trem'];
    return $trem;
}

if ($metodo === 'GET') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id > 0) {
        responderJson(200, buscarTrem($conexao, $id));
    }

    $resultado = $conexao->query('SELECT * FROM trens ORDER BY prefixo_trem');
    $trens = [];
    while ($linha = $resultado->fetch_assoc()) {
        $linha['id_trem'] = (int) $linha['id_trem'];
        $trens[] = $linha;
    }
    responderJson(200, ['total' => count($trens), 'trens' => $trens]);
}

if ($metodo === 'POST') {
    $trem = validarTrem(lerDadosRequisicao());
    $stmt = $conexao->prepare(
        'INSERT INTO trens (prefixo_trem, modelo_trem, tipo_combustivel, ano_fabricacao, capacidade_toneladas, velocidade_maxima_kmh, situacao_trem) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('sssidds', $trem['prefixo_trem'], $trem['modelo_trem'], $trem['tipo_combustivel'], $trem['ano_fabricacao'], $trem['capacidade_toneladas'], $trem['velocidade_maxima_kmh'], $trem['situacao_trem']);
    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $erro) {
        responderErro($erro->getCode() === 1062 ? 409 : 500, $erro->getCode() === 1062 ? 'O prefixo informado ja esta cadastrado.' : 'Nao foi possivel cadastrar o trem.');
    }
    $id = $conexao->insert_id;
    $stmt->close();
    responderJson(201, buscarTrem($conexao, $id));
}

if ($metodo === 'PUT') {
    $dados = lerDadosRequisicao();
    $id = (int) ($_GET['id'] ?? ($dados['id_trem'] ?? 0));
    buscarTrem($conexao, $id);
    $trem = validarTrem($dados);
    $stmt = $conexao->prepare(
        'UPDATE trens SET prefixo_trem=?, modelo_trem=?, tipo_combustivel=?, ano_fabricacao=?, capacidade_toneladas=?, velocidade_maxima_kmh=?, situacao_trem=? WHERE id_trem=?'
    );
    $stmt->bind_param('sssiddsi', $trem['prefixo_trem'], $trem['modelo_trem'], $trem['tipo_combustivel'], $trem['ano_fabricacao'], $trem['capacidade_toneladas'], $trem['velocidade_maxima_kmh'], $trem['situacao_trem'], $id);
    $stmt->execute();
    $stmt->close();
    responderJson(200, buscarTrem($conexao, $id));
}

if ($metodo === 'DELETE') {
    $dados = lerDadosRequisicao();
    $id = (int) ($_GET['id'] ?? ($dados['id_trem'] ?? 0));
    buscarTrem($conexao, $id);
    $stmt = $conexao->prepare('DELETE FROM trens WHERE id_trem = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    responderJson(200, ['mensagem' => 'Trem excluido com sucesso.']);
}

responderErro(405, 'Metodo nao permitido.');


