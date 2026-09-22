<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Execute este arquivo somente pelo terminal.');
}

require_once __DIR__ . '/conexao.php';

$sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS recuperacoes_senha (
    id_recuperacao INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    codigo_hash CHAR(64) NOT NULL,
    token_redefinicao_hash CHAR(64) NULL,
    expira_em DATETIME NOT NULL,
    verificado_em DATETIME NULL,
    usado_em DATETIME NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_recuperacao),
    CONSTRAINT fk_recuperacoes_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON DELETE CASCADE,
    INDEX ix_recuperacoes_usuario (id_usuario),
    INDEX ix_recuperacoes_expiracao (expira_em)
)
SQL;

if (!$conexao->query($sql)) {
    fwrite(STDERR, "Falha ao criar a tabela de recuperacao de senha: {$conexao->error}\n");
    exit(1);
}

echo "Tabela de recuperacao de senha pronta.\n";
