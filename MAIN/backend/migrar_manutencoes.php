<?php
// Arquivo desenvolvido com auxílio de IA (OpenAI Codex).
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Execute somente pelo terminal.'); }
require_once __DIR__ . '/conexao.php';
$sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS manutencoes_trens (
 id_manutencao INT AUTO_INCREMENT PRIMARY KEY,
 id_trem INT NOT NULL,
 tipo_manutencao ENUM('preventiva','corretiva','inspecao') NOT NULL,
 descricao VARCHAR(500) NOT NULL,
 data_inicio DATETIME NOT NULL,
 data_fim DATETIME NULL,
 status_manutencao ENUM('agendada','em_andamento','concluida','cancelada') NOT NULL DEFAULT 'agendada',
 responsavel VARCHAR(120) NOT NULL,
 custo DECIMAL(12,2) NOT NULL DEFAULT 0,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_manutencoes_trem FOREIGN KEY (id_trem) REFERENCES trens(id_trem) ON DELETE RESTRICT,
 INDEX ix_manutencoes_trem (id_trem), INDEX ix_manutencoes_status (status_manutencao)
)
SQL;
if (!$conexao->query($sql)) { fwrite(STDERR,"Falha: {$conexao->error}\n"); exit(1); }
echo "Tabela manutencoes_trens pronta.\n";
