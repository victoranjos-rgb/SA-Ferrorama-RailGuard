<?php
// Migração desenvolvida com auxílio de IA (OpenAI Codex).
if(PHP_SAPI!=='cli'){http_response_code(403);exit('Terminal.');}require_once __DIR__.'/conexao.php';$sql=<<<'SQL'
CREATE TABLE IF NOT EXISTS manutencoes_trilhos (
 id_manutencao_trilho INT AUTO_INCREMENT PRIMARY KEY,id_rota INT NOT NULL,
 local_trilho VARCHAR(150) NOT NULL,descricao VARCHAR(500) NOT NULL,
 inicio DATETIME NOT NULL,fim DATETIME NULL,
 prioridade ENUM('baixa','media','alta','critica') NOT NULL DEFAULT 'media',
 status_manutencao ENUM('aberta','em_andamento','concluida','cancelada') NOT NULL DEFAULT 'aberta',
 responsavel VARCHAR(120) NOT NULL,criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_manut_trilho_rota FOREIGN KEY(id_rota) REFERENCES rotas(id_rota) ON DELETE RESTRICT,
 INDEX ix_manut_trilho_status(status_manutencao)
)
SQL;if(!$conexao->query($sql)){fwrite(STDERR,$conexao->error."\n");exit(1);}echo "Tabela manutencoes_trilhos pronta.\n";
