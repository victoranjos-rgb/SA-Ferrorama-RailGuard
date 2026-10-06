<?php

// Gestão de usuários desenvolvida com auxílio de IA (OpenAI Codex).
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/resposta.php';
require_once __DIR__ . '/sessao_helper.php';

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$gestor = exigirCargo(['gestor']);

function buscarUsuarioGestao(mysqli $conexao, int $id): array
{
    $stmt = $conexao->prepare(
        'SELECT u.id_usuario,u.nome,u.nome_usuario,u.email,u.pais,u.estado,u.cidade,u.cargo,u.status_acesso,u.ativo,u.criado_em,u.analisado_em,u.observacao_acesso,a.nome AS analisado_por_nome
         FROM usuarios u LEFT JOIN usuarios a ON a.id_usuario=u.analisado_por WHERE u.id_usuario=?'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$usuario) responderErro(404, 'Usuario nao encontrado.');
    $usuario['id_usuario'] = (int) $usuario['id_usuario'];
    $usuario['ativo'] = (bool) $usuario['ativo'];
    return $usuario;
}

if ($metodo === 'GET') {
    $resultado = $conexao->query(
        'SELECT u.id_usuario,u.nome,u.nome_usuario,u.email,u.pais,u.estado,u.cidade,u.cargo,u.status_acesso,u.ativo,u.criado_em,u.analisado_em,u.observacao_acesso,a.nome AS analisado_por_nome
         FROM usuarios u LEFT JOIN usuarios a ON a.id_usuario=u.analisado_por ORDER BY u.criado_em DESC'
    );
    $usuarios = [];
    while ($usuario = $resultado->fetch_assoc()) {
        $usuario['id_usuario'] = (int) $usuario['id_usuario'];
        $usuario['ativo'] = (bool) $usuario['ativo'];
        $usuarios[] = $usuario;
    }
    responderJson(200, ['total' => count($usuarios), 'usuarios' => $usuarios, 'id_usuario_logado' => (int) $gestor['id_usuario']]);
}

if ($metodo === 'PUT') {
    $dados = lerDadosRequisicao();
    $id = (int) ($_GET['id'] ?? ($dados['id_usuario'] ?? 0));
    $usuarioAtual = buscarUsuarioGestao($conexao, $id);
    $cargo = trim((string) ($dados['cargo'] ?? ''));
    $status = trim((string) ($dados['status_acesso'] ?? ''));
    $ativo = filter_var($dados['ativo'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    $observacao = trim((string) ($dados['observacao_acesso'] ?? ''));

    if (!in_array($cargo, ['membro', 'gestor', 'maquinista'], true)) responderErro(422, 'Selecione um cargo valido.');
    if (!in_array($status, ['pendente', 'aprovado', 'rejeitado', 'bloqueado'], true)) responderErro(422, 'Selecione um status de acesso valido.');
    if (mb_strlen($observacao) > 255) responderErro(422, 'A observacao deve possuir no maximo 255 caracteres.');

    if ($id === (int) $gestor['id_usuario'] && ($cargo !== 'gestor' || $status !== 'aprovado' || $ativo !== 1)) {
        responderErro(409, 'Voce nao pode remover o proprio acesso de gestor.');
    }

    $idGestor = (int) $gestor['id_usuario'];
    $stmt = $conexao->prepare('UPDATE usuarios SET cargo=?,status_acesso=?,ativo=?,analisado_por=?,analisado_em=NOW(),observacao_acesso=? WHERE id_usuario=?');
    $stmt->bind_param('ssiisi', $cargo, $status, $ativo, $idGestor, $observacao, $id);
    $stmt->execute();
    $stmt->close();
    responderJson(200, ['mensagem' => 'Usuario atualizado com sucesso.', 'usuario' => buscarUsuarioGestao($conexao, $id)]);
}

responderErro(405, 'Metodo nao permitido.');
