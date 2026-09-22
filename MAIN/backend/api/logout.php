<?php

// Arquivo desenvolvido com auxilio de IA (OpenAI Codex).
// Encerra a sessao do usuario autenticado.

require_once __DIR__ . '/resposta.php';
require_once __DIR__ . '/sessao_helper.php';

exigirMetodo('POST');
iniciarSessaoRailGuard();

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $parametros['path'],
        $parametros['domain'],
        $parametros['secure'],
        $parametros['httponly']
    );
}

session_destroy();

responderJson(200, ['mensagem' => 'Logout realizado com sucesso.']);

