<?php

// Arquivo desenvolvido com auxilio de IA (OpenAI Codex).
// Informa se existe um usuario autenticado na sessao atual.

require_once __DIR__ . '/resposta.php';
require_once __DIR__ . '/sessao_helper.php';

exigirMetodo('GET');
iniciarSessaoRailGuard();

$usuario = dadosUsuarioLogado();

if ($usuario === null) {
    responderErro(401, 'Usuario nao autenticado.');
}

responderJson(200, ['usuario' => $usuario]);

