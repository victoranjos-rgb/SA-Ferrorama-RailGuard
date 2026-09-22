<?php
// CRUD desenvolvido com auxílio de IA (OpenAI Codex).
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/resposta.php';
require_once __DIR__ . '/sessao_helper.php';
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$u = exigirUsuarioAutenticado();
if (in_array($metodo, ['POST', 'PUT', 'DELETE'], true) && $u['cargo'] !== 'gestor') responderErro(403, 'Somente gestores podem alterar manutencoes dos trilhos.');
function mtBuscar(mysqli $c, int $id): array
{
    $s = $c->prepare('SELECT m.*,r.codigo_rota,r.nome_rota FROM manutencoes_trilhos m JOIN rotas r ON r.id_rota=m.id_rota WHERE id_manutencao_trilho=?');
    $s->bind_param('i', $id);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();
    $s->close();
    if (!$r) responderErro(404, 'Manutencao nao encontrada.');
    $r['id_manutencao_trilho'] = (int)$r['id_manutencao_trilho'];
    return $r;
}
function mtValidar(array $d): array
{
    $r = ['id_rota' => (int)($d['id_rota'] ?? 0), 'local_trilho' => trim($d['local_trilho'] ?? ''), 'descricao' => trim($d['descricao'] ?? ''), 'inicio' => str_replace('T', ' ', trim($d['inicio'] ?? '')), 'fim' => str_replace('T', ' ', trim($d['fim'] ?? '')), 'prioridade' => trim($d['prioridade'] ?? 'media'), 'status_manutencao' => trim($d['status_manutencao'] ?? 'aberta'), 'responsavel' => trim($d['responsavel'] ?? '')];
    $e = [];
    if ($r['id_rota'] < 1) $e[] = 'Selecione uma rota.';
    if ($r['local_trilho'] === '' || $r['descricao'] === '' || $r['responsavel'] === '') $e[] = 'Preencha os campos obrigatorios.';
    if (!DateTime::createFromFormat('Y-m-d H:i', substr($r['inicio'], 0, 16))) $e[] = 'Inicio invalido.';
    if ($r['fim'] !== '' && !DateTime::createFromFormat('Y-m-d H:i', substr($r['fim'], 0, 16))) $e[] = 'Fim invalido.';
    if (!in_array($r['prioridade'], ['baixa', 'media', 'alta', 'critica'], true) || !in_array($r['status_manutencao'], ['aberta', 'em_andamento', 'concluida', 'cancelada'], true)) $e[] = 'Opcao invalida.';
    if ($e) responderJson(422, ['erros' => $e]);
    $r['inicio'] = substr($r['inicio'], 0, 16) . ':00';
    $r['fim'] = $r['fim'] === '' ? null : substr($r['fim'], 0, 16) . ':00';
    return $r;
}
function mtSincronizar(mysqli $c, int $id): void
{
    $s = $c->prepare("SELECT COUNT(*) total FROM manutencoes_trilhos WHERE id_rota=? AND status_manutencao IN('aberta','em_andamento')");
    $s->bind_param('i', $id);
    $s->execute();
    $bloqueada = (int)$s->get_result()->fetch_assoc()['total'] > 0;
    $s->close();
    $status = $bloqueada ? 'manutencao' : 'programada';
    $s = $c->prepare("UPDATE rotas SET status_rota=? WHERE id_rota=? AND status_rota NOT IN('concluida','cancelada')");
    $s->bind_param('si', $status, $id);
    $s->execute();
}
if ($metodo === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) responderJson(200, mtBuscar($conexao, $id));
    $q = $conexao->query('SELECT m.*,r.codigo_rota,r.nome_rota FROM manutencoes_trilhos m JOIN rotas r ON r.id_rota=m.id_rota ORDER BY inicio DESC');
    $a = [];
    while ($r = $q->fetch_assoc()) {
        $r['id_manutencao_trilho'] = (int)$r['id_manutencao_trilho'];
        $a[] = $r;
    }
    responderJson(200, ['total' => count($a), 'manutencoes' => $a]);
}
if ($metodo === 'POST' || $metodo === 'PUT') {
    $d = lerDadosRequisicao();
    $r = mtValidar($d);
    $id = (int)($_GET['id'] ?? ($d['id_manutencao_trilho'] ?? 0));
    $ant = $metodo === 'PUT' ? mtBuscar($conexao, $id) : null;
    $conexao->begin_transaction();
    try {
        $sql = $metodo === 'POST' ? 'INSERT INTO manutencoes_trilhos(id_rota,local_trilho,descricao,inicio,fim,prioridade,status_manutencao,responsavel) VALUES(?,?,?,?,?,?,?,?)' : 'UPDATE manutencoes_trilhos SET id_rota=?,local_trilho=?,descricao=?,inicio=?,fim=?,prioridade=?,status_manutencao=?,responsavel=? WHERE id_manutencao_trilho=?';
        $s = $conexao->prepare($sql);
        if ($metodo === 'POST') $s->bind_param('isssssss', $r['id_rota'], $r['local_trilho'], $r['descricao'], $r['inicio'], $r['fim'], $r['prioridade'], $r['status_manutencao'], $r['responsavel']);
        else $s->bind_param('isssssssi', $r['id_rota'], $r['local_trilho'], $r['descricao'], $r['inicio'], $r['fim'], $r['prioridade'], $r['status_manutencao'], $r['responsavel'], $id);
        $s->execute();
        if ($metodo === 'POST') $id = $conexao->insert_id;
        mtSincronizar($conexao, $r['id_rota']);
        if ($ant && (int)$ant['id_rota'] !== $r['id_rota']) mtSincronizar($conexao, (int)$ant['id_rota']);
        $conexao->commit();
    } catch (Throwable $e) {
        $conexao->rollback();
        responderErro(500, 'Nao foi possivel salvar.');
    }
    responderJson($metodo === 'POST' ? 201 : 200, mtBuscar($conexao, $id));
}
if ($metodo === 'DELETE') {
    $d = lerDadosRequisicao();
    $id = (int)($_GET['id'] ?? ($d['id_manutencao_trilho'] ?? 0));
    $r = mtBuscar($conexao, $id);
    $s = $conexao->prepare('DELETE FROM manutencoes_trilhos WHERE id_manutencao_trilho=?');
    $s->bind_param('i', $id);
    $s->execute();
    mtSincronizar($conexao, (int)$r['id_rota']);
    responderJson(200, ['mensagem' => 'Manutencao excluida.']);
}
responderErro(405, 'Metodo nao permitido.');
