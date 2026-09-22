<?php

// Arquivo desenvolvido com auxílio de IA (OpenAI Codex).
// Utilitário local para verificar ou definir um gestor inicial.

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este arquivo deve ser executado somente pelo terminal.');
}

require_once __DIR__ . '/conexao.php';

$email = strtolower(trim((string) ($argv[1] ?? '')));

if ($email === '') {
    $resultado = $conexao->query(
        "SELECT COUNT(*) AS total FROM usuarios
         WHERE cargo = 'gestor' AND status_acesso = 'aprovado' AND ativo = 1"
    );
    $total = (int) $resultado->fetch_assoc()['total'];
    echo "Gestores aprovados e ativos: {$total}\n";
    echo "Para promover uma conta: php MAIN/backend/gerenciar_gestor.php email@exemplo.com\n";
    exit(0);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Informe um e-mail valido.\n");
    exit(1);
}

$stmt = $conexao->prepare(
    "UPDATE usuarios
     SET cargo = 'gestor', status_acesso = 'aprovado', ativo = 1,
         analisado_em = NOW(), observacao_acesso = 'Gestor definido pelo utilitario local.'
     WHERE email = ?"
);
$stmt->bind_param('s', $email);
$stmt->execute();
$alterados = $stmt->affected_rows;
$stmt->close();

if ($alterados !== 1) {
    fwrite(STDERR, "Nenhuma conta foi promovida. Confira o e-mail ou verifique se ela ja possui essa configuracao.\n");
    exit(1);
}

echo "Conta promovida a gestor e aprovada com sucesso.\n";

