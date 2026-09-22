<?php
declare(strict_types=1);

require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/resposta.php';
require_once __DIR__ . '/sessao_helper.php';

exigirMetodo('GET');
$usuario = exigirUsuarioAutenticado();

$stmt = $conexao->prepare(
    'SELECT id_usuario, nome, nome_usuario, email, pais, estado, cidade, cargo, status_acesso, criado_em
     FROM usuarios WHERE id_usuario = ? LIMIT 1'
);
$stmt->bind_param('i', $usuario['id_usuario']);
$stmt->execute();
$perfil = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$perfil) {
    responderErro(404, 'Usuário não encontrado.');
}
responderJson(200, ['perfil' => $perfil]);

