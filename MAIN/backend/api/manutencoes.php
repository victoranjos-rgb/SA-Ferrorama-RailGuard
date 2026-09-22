<?php
// CRUD desenvolvido com auxílio de IA (OpenAI Codex), conforme padrão do CRUD do professor.
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/resposta.php';
require_once __DIR__ . '/sessao_helper.php';
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$usuario = exigirUsuarioAutenticado();
if (in_array($metodo, ['POST', 'PUT', 'DELETE'], true) && $usuario['cargo'] !== 'gestor') responderErro(403, 'Somente gestores podem alterar manutencoes.');
function buscarManutencao(mysqli $c, int $id): array
{
    $s = $c->prepare('SELECT m.*,t.prefixo_trem,t.modelo_trem FROM manutencoes_trens m JOIN trens t ON t.id_trem=m.id_trem WHERE m.id_manutencao=?');
    $s->bind_param('i', $id);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();
    $s->close();
    if (!$r) responderErro(404, 'Manutencao nao encontrada.');
    $r['id_manutencao'] = (int)$r['id_manutencao'];
    $r['id_trem'] = (int)$r['id_trem'];
    return $r;
}
function validarManutencao(array $d): array
{
    $r = ['id_trem' => (int)($d['id_trem'] ?? 0), 'tipo_manutencao' => trim($d['tipo_manutencao'] ?? ''), 'descricao' => trim($d['descricao'] ?? ''), 'data_inicio' => str_replace('T', ' ', trim($d['data_inicio'] ?? '')), 'data_fim' => str_replace('T', ' ', trim($d['data_fim'] ?? '')), 'status_manutencao' => trim($d['status_manutencao'] ?? 'agendada'), 'responsavel' => trim($d['responsavel'] ?? ''), 'custo' => (float)($d['custo'] ?? 0)];
    $e = [];
    if ($r['id_trem'] < 1) $e[] = 'Selecione um trem.';
    if (!in_array($r['tipo_manutencao'], ['preventiva', 'corretiva', 'inspecao'], true)) $e[] = 'Tipo invalido.';
    if ($r['descricao'] === '' || mb_strlen($r['descricao']) > 500) $e[] = 'Informe uma descricao de ate 500 caracteres.';
    if (!DateTime::createFromFormat('Y-m-d H:i', substr($r['data_inicio'], 0, 16))) $e[] = 'Data de inicio invalida.';
    if ($r['data_fim'] !== '' && !DateTime::createFromFormat('Y-m-d H:i', substr($r['data_fim'], 0, 16))) $e[] = 'Data final invalida.';
    if ($r['data_fim'] !== '' && $r['data_fim'] < $r['data_inicio']) $e[] = 'A data final nao pode ser anterior ao inicio.';
    if (!in_array($r['status_manutencao'], ['agendada', 'em_andamento', 'concluida', 'cancelada'], true)) $e[] = 'Status invalido.';
    if ($r['responsavel'] === '' || mb_strlen($r['responsavel']) > 120) $e[] = 'Informe o responsavel.';
    if ($r['custo'] < 0) $e[] = 'Custo invalido.';
    if ($e) responderJson(422, ['erros' => $e]);
    $r['data_inicio'] = substr($r['data_inicio'], 0, 16) . ':00';
    $r['data_fim'] = $r['data_fim'] === '' ? null : substr($r['data_fim'], 0, 16) . ':00';
    return $r;
}
function confirmarTrem(mysqli $c, int $id): void
{
    $s = $c->prepare('SELECT id_trem FROM trens WHERE id_trem=?');
    $s->bind_param('i', $id);
    $s->execute();
    $ok = $s->get_result()->fetch_assoc();
    $s->close();
    if (!$ok) responderErro(422, 'Trem inexistente.');
}
function sincronizarTrem(mysqli $c, int $id): void
{
    $s = $c->prepare("SELECT COUNT(*) total FROM manutencoes_trens WHERE id_trem=? AND status_manutencao='em_andamento'");
    $s->bind_param('i', $id);
    $s->execute();
    $ativo = (int)$s->get_result()->fetch_assoc()['total'] > 0;
    $s->close();
    $situacao = $ativo ? 'manutencao' : 'ativo';
    $s = $c->prepare("UPDATE trens SET situacao_trem=? WHERE id_trem=? AND situacao_trem<>'inativo'");
    $s->bind_param('si', $situacao, $id);
    $s->execute();
    $s->close();
}
if ($metodo === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id) responderJson(200, buscarManutencao($conexao, $id));
    $q = $conexao->query('SELECT m.*,t.prefixo_trem,t.modelo_trem FROM manutencoes_trens m JOIN trens t ON t.id_trem=m.id_trem ORDER BY m.data_inicio DESC');
    $lista = [];
    while ($r = $q->fetch_assoc()) {
        $r['id_manutencao'] = (int)$r['id_manutencao'];
        $r['id_trem'] = (int)$r['id_trem'];
        $lista[] = $r;
    }
    responderJson(200, ['total' => count($lista), 'manutencoes' => $lista]);
}
if ($metodo === 'POST') {
    $r = validarManutencao(lerDadosRequisicao());
    confirmarTrem($conexao, $r['id_trem']);
    $conexao->begin_transaction();
    try {
        $s = $conexao->prepare('INSERT INTO manutencoes_trens(id_trem,tipo_manutencao,descricao,data_inicio,data_fim,status_manutencao,responsavel,custo) VALUES(?,?,?,?,?,?,?,?)');
        $s->bind_param('issssssd', $r['id_trem'], $r['tipo_manutencao'], $r['descricao'], $r['data_inicio'], $r['data_fim'], $r['status_manutencao'], $r['responsavel'], $r['custo']);
        $s->execute();
        $id = $conexao->insert_id;
        $s->close();
        sincronizarTrem($conexao, $r['id_trem']);
        $conexao->commit();
    } catch (Throwable $e) {
        $conexao->rollback();
        responderErro(500, 'Nao foi possivel cadastrar.');
    }
    responderJson(201, buscarManutencao($conexao, $id));
}
if ($metodo === 'PUT') {
    $d = lerDadosRequisicao();
    $id = (int)($_GET['id'] ?? ($d['id_manutencao'] ?? 0));
    $antes = buscarManutencao($conexao, $id);
    $r = validarManutencao($d);
    confirmarTrem($conexao, $r['id_trem']);
    $conexao->begin_transaction();
    try {
        $s = $conexao->prepare('UPDATE manutencoes_trens SET id_trem=?,tipo_manutencao=?,descricao=?,data_inicio=?,data_fim=?,status_manutencao=?,responsavel=?,custo=? WHERE id_manutencao=?');
        $s->bind_param('issssssdi', $r['id_trem'], $r['tipo_manutencao'], $r['descricao'], $r['data_inicio'], $r['data_fim'], $r['status_manutencao'], $r['responsavel'], $r['custo'], $id);
        $s->execute();
        $s->close();
        sincronizarTrem($conexao, (int)$antes['id_trem']);
        if ((int)$antes['id_trem'] !== $r['id_trem']) sincronizarTrem($conexao, $r['id_trem']);
        $conexao->commit();
    } catch (Throwable $e) {
        $conexao->rollback();
        responderErro(500, 'Nao foi possivel atualizar.');
    }
    responderJson(200, buscarManutencao($conexao, $id));
}
if ($metodo === 'DELETE') {
    $d = lerDadosRequisicao();
    $id = (int)($_GET['id'] ?? ($d['id_manutencao'] ?? 0));
    $r = buscarManutencao($conexao, $id);
    $conexao->begin_transaction();
    try {
        $s = $conexao->prepare('DELETE FROM manutencoes_trens WHERE id_manutencao=?');
        $s->bind_param('i', $id);
        $s->execute();
        $s->close();
        sincronizarTrem($conexao, (int)$r['id_trem']);
        $conexao->commit();
    } catch (Throwable $e) {
        $conexao->rollback();
        responderErro(500, 'Nao foi possivel excluir.');
    }
    responderJson(200, ['mensagem' => 'Manutencao excluida.']);
}
responderErro(405, 'Metodo nao permitido.');
