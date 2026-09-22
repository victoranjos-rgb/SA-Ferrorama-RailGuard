<?php

// Arquivo desenvolvido com auxílio de IA (OpenAI Codex).
// Adiciona o fluxo de aprovação sem apagar os usuários existentes.

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este arquivo deve ser executado somente pelo terminal.');
}

require_once __DIR__ . '/conexao.php';

$banco = $conexao->real_escape_string($config['banco']);
$resultado = $conexao->query(
    "SELECT COUNT(*) AS total FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = '{$banco}' AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'status_acesso'"
);
$colunaExiste = (int) $resultado->fetch_assoc()['total'] > 0;
$resultado->free();

if ($colunaExiste) {
    echo "A migracao de aprovacao ja foi aplicada.\n";
    exit(0);
}

$sql = <<<'SQL'
ALTER TABLE usuarios
    ADD COLUMN status_acesso ENUM('pendente', 'aprovado', 'rejeitado', 'bloqueado') NOT NULL DEFAULT 'pendente' AFTER cargo,
    ADD COLUMN analisado_por INT NULL AFTER status_acesso,
    ADD COLUMN analisado_em DATETIME NULL AFTER analisado_por,
    ADD COLUMN observacao_acesso VARCHAR(255) NULL AFTER analisado_em,
    ADD INDEX ix_usuarios_status_acesso (status_acesso),
    ADD CONSTRAINT fk_usuarios_analisador FOREIGN KEY (analisado_por) REFERENCES usuarios (id_usuario) ON DELETE SET NULL ON UPDATE CASCADE
SQL;

if (!$conexao->query($sql)) {
    fwrite(STDERR, "Falha ao alterar a tabela usuarios: {$conexao->error}\n");
    exit(1);
}

// Preserva o acesso das contas criadas antes da implantação da aprovação.
if (!$conexao->query("UPDATE usuarios SET status_acesso = 'aprovado', analisado_em = NOW()")) {
    fwrite(STDERR, "A estrutura foi criada, mas nao foi possivel preservar as contas existentes: {$conexao->error}\n");
    exit(1);
}

echo "Migracao concluida. Contas existentes foram aprovadas e novos cadastros ficarao pendentes.\n";

