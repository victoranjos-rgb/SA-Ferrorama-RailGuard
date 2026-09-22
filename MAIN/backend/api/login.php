<?php

// Arquivo desenvolvido com auxilio de IA (OpenAI Codex).
// Autentica o usuario e cria uma sessao no servidor.

require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/resposta.php';
require_once __DIR__ . '/sessao_helper.php';

exigirMetodo('POST');

$dados = lerDadosRequisicao();
$email = strtolower(trim((string) ($dados['email'] ?? '')));
$senha = (string) ($dados['senha'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '') {
    responderErro(422, 'Informe o e-mail e a senha.');
}

$stmt = $conexao->prepare(
    'SELECT id_usuario, nome, nome_usuario, email, senha_hash, cargo, status_acesso, ativo FROM usuarios WHERE email = ? LIMIT 1'
);
$stmt->bind_param('s', $email);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
    responderErro(401, 'E-mail ou senha invalidos.');
}

if ((int) $usuario['ativo'] !== 1) {
    responderErro(403, 'Esta conta esta desativada.');
}

if ($usuario['status_acesso'] !== 'aprovado') {
    $mensagensStatus = [
        'pendente' => 'Seu cadastro ainda esta aguardando a aprovacao de um gestor.',
        'rejeitado' => 'Seu cadastro foi rejeitado. Procure um gestor para mais informacoes.',
        'bloqueado' => 'Seu acesso esta bloqueado. Procure um gestor.',
    ];

    responderErro(403, $mensagensStatus[$usuario['status_acesso']] ?? 'Seu acesso nao esta liberado.');
}

iniciarSessaoRailGuard();
session_regenerate_id(true);

$_SESSION['usuario'] = [
    'id_usuario' => (int) $usuario['id_usuario'],
    'nome' => $usuario['nome'],
    'nome_usuario' => $usuario['nome_usuario'],
    'email' => $usuario['email'],
    'cargo' => $usuario['cargo'],
];

responderJson(200, [
    'mensagem' => 'Login realizado com sucesso.',
    'usuario' => $_SESSION['usuario'],
]);

