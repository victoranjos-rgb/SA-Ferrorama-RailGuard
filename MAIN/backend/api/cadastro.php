<?php

// Arquivo desenvolvido com auxilio de IA (OpenAI Codex).
// Cadastra usuarios com senha protegida por hash.

require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/resposta.php';

exigirMetodo('POST');

$dados = lerDadosRequisicao();

$nome = trim((string) ($dados['nome'] ?? ''));
$nomeUsuario = trim((string) ($dados['nome_usuario'] ?? ''));
$email = strtolower(trim((string) ($dados['email'] ?? '')));
$senha = (string) ($dados['senha'] ?? '');
$confirmacaoSenha = (string) ($dados['confirmacao_senha'] ?? '');
$pais = trim((string) ($dados['pais'] ?? ''));
$estado = trim((string) ($dados['estado'] ?? ''));
$cidade = trim((string) ($dados['cidade'] ?? ''));
$cargo = trim((string) ($dados['cargo'] ?? ''));

$cargosPermitidos = ['membro', 'gestor', 'maquinista'];
$erros = [];

if ($nome === '' || mb_strlen($nome) > 120) {
    $erros[] = 'Informe um nome com ate 120 caracteres.';
}

if (!preg_match('/^[\p{L}\p{N}._-]{3,50}$/u', $nomeUsuario)) {
    $erros[] = 'O usuario deve ter de 3 a 50 caracteres e pode conter letras, numeros, ponto, hifen e sublinhado.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
    $erros[] = 'Informe um e-mail valido.';
}

if (strlen($senha) < 8) {
    $erros[] = 'A senha deve ter pelo menos 8 caracteres.';
}

if ($senha !== $confirmacaoSenha) {
    $erros[] = 'A confirmacao da senha nao confere.';
}

if ($pais === '' || mb_strlen($pais) > 80) {
    $erros[] = 'Informe um pais com ate 80 caracteres.';
}

if ($estado === '' || mb_strlen($estado) > 80) {
    $erros[] = 'Informe um estado com ate 80 caracteres.';
}

if ($cidade === '' || mb_strlen($cidade) > 100) {
    $erros[] = 'Informe uma cidade com ate 100 caracteres.';
}

if (!in_array($cargo, $cargosPermitidos, true)) {
    $erros[] = 'Selecione um cargo valido.';
}

if (count($erros) > 0) {
    responderJson(422, ['erros' => $erros]);
}

$stmt = $conexao->prepare(
    'SELECT nome_usuario, email FROM usuarios WHERE nome_usuario = ? OR email = ? LIMIT 1'
);
$stmt->bind_param('ss', $nomeUsuario, $email);
$stmt->execute();
$usuarioExistente = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($usuarioExistente) {
    $mensagem = $usuarioExistente['email'] === $email
        ? 'Ja existe uma conta cadastrada com este e-mail.'
        : 'Este nome de usuario ja esta em uso.';

    responderErro(409, $mensagem);
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

if ($senhaHash === false) {
    responderErro(500, 'Nao foi possivel proteger a senha informada.');
}

$stmt = $conexao->prepare(
    'INSERT INTO usuarios (nome, nome_usuario, email, senha_hash, pais, estado, cidade, cargo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param(
    'ssssssss',
    $nome,
    $nomeUsuario,
    $email,
    $senhaHash,
    $pais,
    $estado,
    $cidade,
    $cargo
);

if (!$stmt->execute()) {
    $stmt->close();
    responderErro(500, 'Nao foi possivel cadastrar o usuario.');
}

$idUsuario = $conexao->insert_id;
$stmt->close();

responderJson(201, [
    'mensagem' => 'Cadastro enviado. Aguarde a aprovacao de um gestor para entrar.',
    'usuario' => [
        'id_usuario' => $idUsuario,
        'nome' => $nome,
        'nome_usuario' => $nomeUsuario,
        'email' => $email,
        'cargo' => $cargo,
        'status_acesso' => 'pendente',
    ],
]);

