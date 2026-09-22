<?php

// Arquivo desenvolvido com auxílio de IA (OpenAI Codex).
// Lista cadastros que aguardam análise de um gestor.

require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/resposta.php';
require_once __DIR__ . '/sessao_helper.php';

exigirMetodo('GET');
exigirCargo(['gestor']);

$resultado = $conexao->query(
    "SELECT id_usuario, nome, nome_usuario, email, pais, estado, cidade, cargo, criado_em
     FROM usuarios
     WHERE status_acesso = 'pendente' AND ativo = 1
     ORDER BY criado_em ASC"
);

$usuarios = [];

while ($usuario = $resultado->fetch_assoc()) {
    $usuario['id_usuario'] = (int) $usuario['id_usuario'];
    $usuarios[] = $usuario;
}

responderJson(200, [
    'total' => count($usuarios),
    'usuarios' => $usuarios,
]);

