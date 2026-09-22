<?php

// Arquivo desenvolvido com auxilio de IA (OpenAI Codex).
// Importa a estrutura inicial do banco configurado em config.php.

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este arquivo deve ser executado somente pelo terminal.');
}

require_once __DIR__ . '/conexao.php';

$caminhoSql = __DIR__ . '/bancoUsuario.sql';
$sql = file_get_contents($caminhoSql);

if ($sql === false || trim($sql) === '') {
    fwrite(STDERR, "Nao foi possivel ler o arquivo bancoUsuario.sql.\n");
    exit(1);
}

if (!$conexao->multi_query($sql)) {
    fwrite(STDERR, "Falha ao importar o banco: {$conexao->error}\n");
    exit(1);
}

do {
    $resultado = $conexao->store_result();

    if ($resultado instanceof mysqli_result) {
        $resultado->free();
    }

    if (!$conexao->more_results()) {
        break;
    }
} while ($conexao->next_result());

if ($conexao->errno !== 0) {
    fwrite(STDERR, "Falha ao importar o banco: {$conexao->error}\n");
    exit(1);
}

echo "Banco importado com sucesso. A tabela usuarios esta pronta.\n";

