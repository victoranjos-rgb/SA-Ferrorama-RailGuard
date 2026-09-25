<?php

// Migração desenvolvida com auxílio de IA (OpenAI Codex).
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Execute somente pelo terminal.');
}

require_once __DIR__ . '/conexao.php';

$sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS sensores (
    id_sensor INT NOT NULL AUTO_INCREMENT,
    codigo_sensor VARCHAR(30) NOT NULL,
    tipo_sensor ENUM('proximidade','velocidade','vibracao','temperatura') NOT NULL,
    modelo_sensor VARCHAR(80) NOT NULL,
    id_rota INT NULL,
    localizacao VARCHAR(150) NOT NULL,
    unidade_medida VARCHAR(20) NOT NULL,
    limite_alerta DECIMAL(10,2) NULL,
    status_sensor ENUM('ativo','inativo','manutencao') NOT NULL DEFAULT 'ativo',
    ultima_leitura DECIMAL(10,2) NULL,
    ultima_comunicacao DATETIME NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_sensor),
    CONSTRAINT uq_sensores_codigo UNIQUE (codigo_sensor),
    CONSTRAINT fk_sensores_rota FOREIGN KEY (id_rota) REFERENCES rotas (id_rota) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX ix_sensores_tipo (tipo_sensor),
    INDEX ix_sensores_status (status_sensor),
    INDEX ix_sensores_rota (id_rota)
)
SQL;

if (!$conexao->query($sql)) {
    fwrite(STDERR, "Falha ao criar a tabela sensores: {$conexao->error}\n");
    exit(1);
}

echo "Tabela sensores pronta.\n";
