<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Execute somente pelo terminal.'); }
require_once __DIR__ . '/conexao.php';
$sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS cargas (
 id_carga INT AUTO_INCREMENT PRIMARY KEY, codigo_carga VARCHAR(20) NOT NULL UNIQUE,
 descricao VARCHAR(120) NOT NULL, peso_kg DECIMAL(10,2) NOT NULL,
 origem VARCHAR(120) NOT NULL, destino VARCHAR(120) NOT NULL, id_trem INT NULL,
 status_carga ENUM('aguardando','em_transito','entregue','atrasada','cancelada') NOT NULL DEFAULT 'aguardando',
 chegada_prevista DATETIME NULL, criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_cargas_trem FOREIGN KEY (id_trem) REFERENCES trens(id_trem) ON DELETE SET NULL,
 INDEX ix_cargas_status(status_carga), INDEX ix_cargas_trem(id_trem)
)
SQL;
if(!$conexao->query($sql)){fwrite(STDERR,"Falha: {$conexao->error}\n");exit(1);} echo "Tabela cargas pronta.\n";
