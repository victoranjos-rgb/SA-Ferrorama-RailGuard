<?php
// CRUD de rotas desenvolvido com auxílio de IA (OpenAI Codex).
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/resposta.php';
require_once __DIR__ . '/sessao_helper.php';
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$u = exigirUsuarioAutenticado();
if (in_array($metodo, ['POST', 'PUT', 'DELETE'], true) && $u['cargo'] !== 'gestor') responderErro(403, 'Somente gestores podem alterar rotas.');
function rota(mysqli $c, int $id): array
{
    $s = $c->prepare('SELECT r.*,t.prefixo_trem FROM rotas r LEFT JOIN trens t ON t.id_trem=r.id_trem WHERE r.id_rota=?');
    $s->bind_param('i', $id);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();
    $s->close();
    if (!$r) responderErro(404, 'Rota nao encontrada.');
    $r['id_rota'] = (int)$r['id_rota'];
    return $r;
}
function validarRota(array $d): array
{
    $r = ['codigo_rota' => strtoupper(trim($d['codigo_rota'] ?? '')), 'nome_rota' => trim($d['nome_rota'] ?? ''), 'origem' => trim($d['origem'] ?? ''), 'destino' => trim($d['destino'] ?? ''), 'origem_lat' => (float)($d['origem_lat'] ?? 999), 'origem_lng' => (float)($d['origem_lng'] ?? 999), 'destino_lat' => (float)($d['destino_lat'] ?? 999), 'destino_lng' => (float)($d['destino_lng'] ?? 999), 'distancia_km' => (float)($d['distancia_km'] ?? 0), 'id_trem' => ($d['id_trem'] ?? '') === '' ? null : (int)$d['id_trem'], 'partida_prevista' => str_replace('T', ' ', trim($d['partida_prevista'] ?? '')), 'chegada_prevista' => str_replace('T', ' ', trim($d['chegada_prevista'] ?? '')), 'status_rota' => trim($d['status_rota'] ?? 'programada'), 'aviso' => trim($d['aviso'] ?? '')];
    $e = [];
    if (!preg_match('/^[A-Z0-9-]{2,20}$/', $r['codigo_rota'])) $e[] = 'Codigo invalido.';
    foreach (['nome_rota', 'origem', 'destino'] as $c) if ($r[$c] === '') $e[] = 'Preencha nome, origem e destino.';
    if (abs($r['origem_lat']) > 90 || abs($r['destino_lat']) > 90 || abs($r['origem_lng']) > 180 || abs($r['destino_lng']) > 180) $e[] = 'Coordenadas invalidas.';
    if ($r['distancia_km'] <= 0) $e[] = 'Distancia invalida.';
    if (!DateTime::createFromFormat('Y-m-d H:i', substr($r['partida_prevista'], 0, 16)) || !DateTime::createFromFormat('Y-m-d H:i', substr($r['chegada_prevista'], 0, 16)) || $r['chegada_prevista'] <= $r['partida_prevista']) $e[] = 'Horarios invalidos.';
    if (!in_array($r['status_rota'], ['programada', 'em_andamento', 'atrasada', 'concluida', 'cancelada', 'manutencao'], true)) $e[] = 'Status invalido.';
    if (mb_strlen($r['aviso']) > 255) $e[] = 'Aviso muito longo.';
    if ($e) responderJson(422, ['erros' => array_values(array_unique($e))]);
    $r['partida_prevista'] = substr($r['partida_prevista'], 0, 16) . ':00';
    $r['chegada_prevista'] = substr($r['chegada_prevista'], 0, 16) . ':00';
    return $r;
}
if ($metodo === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) responderJson(200, rota($conexao, $id));
    $q = $conexao->query('SELECT r.*,t.prefixo_trem FROM rotas r LEFT JOIN trens t ON t.id_trem=r.id_trem ORDER BY partida_prevista');
    $a = [];
    while ($r = $q->fetch_assoc()) {
        $r['id_rota'] = (int)$r['id_rota'];
        $a[] = $r;
    }
    responderJson(200, ['total' => count($a), 'rotas' => $a]);
}
if ($metodo === 'POST' || $metodo === 'PUT') {
    $d = lerDadosRequisicao();
    $r = validarRota($d);
    $id = (int)($_GET['id'] ?? ($d['id_rota'] ?? 0));
    if ($metodo === 'PUT') rota($conexao, $id);
    $sql = $metodo === 'POST' ? 'INSERT INTO rotas(codigo_rota,nome_rota,origem,destino,origem_lat,origem_lng,destino_lat,destino_lng,distancia_km,id_trem,partida_prevista,chegada_prevista,status_rota,aviso) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)' : 'UPDATE rotas SET codigo_rota=?,nome_rota=?,origem=?,destino=?,origem_lat=?,origem_lng=?,destino_lat=?,destino_lng=?,distancia_km=?,id_trem=?,partida_prevista=?,chegada_prevista=?,status_rota=?,aviso=? WHERE id_rota=?';
    $s = $conexao->prepare($sql);
    if ($metodo === 'POST') $s->bind_param('ssssdddddissss', $r['codigo_rota'], $r['nome_rota'], $r['origem'], $r['destino'], $r['origem_lat'], $r['origem_lng'], $r['destino_lat'], $r['destino_lng'], $r['distancia_km'], $r['id_trem'], $r['partida_prevista'], $r['chegada_prevista'], $r['status_rota'], $r['aviso']);
    else $s->bind_param('ssssdddddissssi', $r['codigo_rota'], $r['nome_rota'], $r['origem'], $r['destino'], $r['origem_lat'], $r['origem_lng'], $r['destino_lat'], $r['destino_lng'], $r['distancia_km'], $r['id_trem'], $r['partida_prevista'], $r['chegada_prevista'], $r['status_rota'], $r['aviso'], $id);
    try {
        $s->execute();
        if ($metodo === 'POST') $id = $conexao->insert_id;
    } catch (mysqli_sql_exception $e) {
        responderErro($e->getCode() === 1062 ? 409 : 500, $e->getCode() === 1062 ? 'Codigo ja cadastrado.' : 'Nao foi possivel salvar.');
    }
    $s->close();
    responderJson($metodo === 'POST' ? 201 : 200, rota($conexao, $id));
}
if ($metodo === 'DELETE') {
    $d = lerDadosRequisicao();
    $id = (int)($_GET['id'] ?? ($d['id_rota'] ?? 0));
    rota($conexao, $id);
    $s = $conexao->prepare('DELETE FROM rotas WHERE id_rota=?');
    $s->bind_param('i', $id);
    $s->execute();
    responderJson(200, ['mensagem' => 'Rota excluida.']);
}
responderErro(405, 'Metodo nao permitido.');

