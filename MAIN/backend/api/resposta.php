<?php

// Arquivo desenvolvido com auxilio de IA (OpenAI Codex).
// Funcoes compartilhadas pelas rotas da API do RailGuard.

function responderJson(int $status, array $dados): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function responderErro(int $status, string $mensagem): void
{
    responderJson($status, ['erro' => $mensagem]);
}

function lerDadosRequisicao(): array
{
    $tipoConteudo = $_SERVER['CONTENT_TYPE'] ?? '';

    if (stripos($tipoConteudo, 'application/json') !== false) {
        $conteudo = file_get_contents('php://input');
        $dados = json_decode($conteudo ?: '', true);

        if (!is_array($dados)) {
            responderErro(400, 'O corpo da requisicao deve conter um JSON valido.');
        }

        return $dados;
    }

    return $_POST;
}

function exigirMetodo(string $metodoEsperado): void
{
    $metodoRecebido = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($metodoRecebido !== $metodoEsperado) {
        header('Allow: ' . $metodoEsperado);
        responderErro(405, 'Metodo ' . $metodoRecebido . ' nao permitido.');
    }
}

