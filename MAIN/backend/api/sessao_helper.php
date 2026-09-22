<?php

// Arquivo desenvolvido com auxilio de IA (OpenAI Codex).
// Centraliza a configuracao da sessao usada na autenticacao.

function iniciarSessaoRailGuard(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'httponly' => true,
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'samesite' => 'Lax',
        'path' => '/',
    ]);

    session_start();
}

function dadosUsuarioLogado(): ?array
{
    if (empty($_SESSION['usuario'])) {
        return null;
    }

    return $_SESSION['usuario'];
}

function exigirUsuarioAutenticado(): array
{
    iniciarSessaoRailGuard();
    $usuario = dadosUsuarioLogado();

    if ($usuario === null) {
        responderErro(401, 'Usuario nao autenticado.');
    }

    return $usuario;
}

function exigirCargo(array $cargosPermitidos): array
{
    $usuario = exigirUsuarioAutenticado();

    if (!in_array($usuario['cargo'], $cargosPermitidos, true)) {
        responderErro(403, 'Voce nao possui permissao para realizar esta acao.');
    }

    return $usuario;
}

