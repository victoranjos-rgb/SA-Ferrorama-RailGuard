<?php
declare(strict_types=1);

function garantirTabelaCargas(mysqli $conexao): void
{
    $sql = "CREATE TABLE IF NOT EXISTS cargas (
        id_carga INT AUTO_INCREMENT PRIMARY KEY,
        codigo_carga VARCHAR(20) NOT NULL UNIQUE,
        descricao VARCHAR(120) NOT NULL,
        peso_kg DECIMAL(12,2) NOT NULL,
        origem VARCHAR(120) NOT NULL,
        destino VARCHAR(120) NOT NULL,
        id_trem INT NULL,
        status_carga ENUM('aguardando','em_transito','entregue','atrasada','cancelada') NOT NULL DEFAULT 'aguardando',
        chegada_prevista DATETIME NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_cargas_trem FOREIGN KEY (id_trem) REFERENCES trens(id_trem) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if (!$conexao->query($sql)) {
        throw new RuntimeException('Não foi possível preparar a tabela de cargas.');
    }
}

