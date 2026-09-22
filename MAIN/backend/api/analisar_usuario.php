<?php

// Arquivo desenvolvido com auxílio de IA (OpenAI Codex).
// Permite que um gestor aprove ou rejeite um cadastro pendente.

require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/resposta.php';
require_once __DIR__ . '/sessao_helper.php';

exigirMetodo('POST');
$gestor = exigirCargo(['gestor']);
$dados = lerDadosRequisicao();

$idUsuario = (int) ($dados['id_usuario'] ?? 0);
$decisao = trim((string) ($dados['decisao'] ?? ''));
$cargo = trim((string) ($dados['cargo'] ?? ''));
$observacao = trim((string) ($dados['observacao'] ?? ''));
$cargosPermitidos = ['membro', 'gestor', 'maquinista'];

if ($idUsuario <= 0) {
    responderErro(422, 'Informe o usuario que sera analisado.');
}

if (!in_array($decisao, ['aprovar', 'rejeitar'], true)) {
    responderErro(422, 'A decisao deve ser aprovar ou rejeitar.');
}

if ($decisao === 'aprovar' && !in_array($cargo, $cargosPermitidos, true)) {
    responderErro(422, 'Selecione um cargo valido para aprovar o usuario.');
}

if (mb_strlen($observacao) > 255) {
    responderErro(422, 'A observacao deve ter no maximo 255 caracteres.');
}

$stmt = $conexao->prepare('SELECT status_acesso FROM usuarios WHERE id_usuario = ? LIMIT 1');
$stmt->bind_param('i', $idUsuario);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    responderErro(404, 'Usuario nao encontrado.');
}

if ($usuario['status_acesso'] !== 'pendente') {
    responderErro(409, 'Este cadastro ja foi analisado.');
}

$novoStatus = $decisao === 'aprovar' ? 'aprovado' : 'rejeitado';
$cargoAprovado = $decisao === 'aprovar' ? $cargo : 'membro';
$idGestor = (int) $gestor['id_usuario'];

$stmt = $conexao->prepare(
    'UPDATE usuarios SET status_acesso = ?, cargo = ?, analisado_por = ?, analisado_em = NOW(), observacao_acesso = ? WHERE id_usuario = ? AND status_acesso = \'pendente\''
);
$stmt->bind_param('ssisi', $novoStatus, $cargoAprovado, $idGestor, $observacao, $idUsuario);
$stmt->execute();
$alterados = $stmt->affected_rows;
$stmt->close();

if ($alterados !== 1) {
    responderErro(409, 'O cadastro foi alterado por outra operacao. Atualize a lista.');
}

responderJson(200, [
    'mensagem' => $decisao === 'aprovar' ? 'Usuario aprovado com sucesso.' : 'Usuario rejeitado com sucesso.',
]);

