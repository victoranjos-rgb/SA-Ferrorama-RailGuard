<?php
// Migração desenvolvida com auxílio de IA (OpenAI Codex).
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Execute somente pelo terminal.');
}
require_once __DIR__ . '/conexao.php';
$sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS rotas (
 id_rota INT AUTO_INCREMENT PRIMARY KEY,
 codigo_rota VARCHAR(20) NOT NULL UNIQUE,
 nome_rota VARCHAR(100) NOT NULL,
 origem VARCHAR(120) NOT NULL, destino VARCHAR(120) NOT NULL,
 origem_lat DECIMAL(10,7) NOT NULL, origem_lng DECIMAL(10,7) NOT NULL,
 destino_lat DECIMAL(10,7) NOT NULL, destino_lng DECIMAL(10,7) NOT NULL,
 distancia_km DECIMAL(8,2) NOT NULL,
 id_trem INT NULL,
 partida_prevista DATETIME NOT NULL, chegada_prevista DATETIME NOT NULL,
 status_rota ENUM('programada','em_andamento','atrasada','concluida','cancelada','manutencao') NOT NULL DEFAULT 'programada',
 aviso VARCHAR(255) NULL,
 criado_em DATETIME DEFAULT CURRENT_TIMESTAMP, atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_rotas_trem FOREIGN KEY(id_trem) REFERENCES trens(id_trem) ON DELETE SET NULL,
 INDEX ix_rotas_status(status_rota), INDEX ix_rotas_partida(partida_prevista)
)
SQL;
if (!$conexao->query($sql)) {
    fwrite(STDERR, "Falha: {$conexao->error}\n");
    exit(1);
}
echo "Tabela rotas pronta.\n";
