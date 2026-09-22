<?php

// Arquivo desenvolvido com auxílio de IA (OpenAI Codex).

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este arquivo deve ser executado somente pelo terminal.');
}

require_once __DIR__ . '/conexao.php';

$sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS trens (
    id_trem INT NOT NULL AUTO_INCREMENT,
    prefixo_trem VARCHAR(20) NOT NULL,
    modelo_trem VARCHAR(80) NOT NULL,
    tipo_combustivel ENUM('diesel', 'eletrico', 'hibrido', 'outro') NOT NULL,
    ano_fabricacao YEAR NOT NULL,
    capacidade_toneladas DECIMAL(10, 2) NOT NULL,
    velocidade_maxima_kmh DECIMAL(6, 2) NOT NULL,
    situacao_trem ENUM('ativo', 'manutencao', 'inativo') NOT NULL DEFAULT 'ativo',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_trem),
    CONSTRAINT uq_trens_prefixo UNIQUE (prefixo_trem),
    INDEX ix_trens_situacao (situacao_trem)
)
SQL;

if (!$conexao->query($sql)) {
    fwrite(STDERR, "Falha ao criar a tabela trens: {$conexao->error}\n");
    exit(1);
}

echo "Tabela trens pronta.\n";

