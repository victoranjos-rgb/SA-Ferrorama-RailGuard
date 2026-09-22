-- Arquivo desenvolvido com auxilio de IA (OpenAI Codex).
-- Consulte o arquivo REGISTRO_IA.md na raiz do projeto.

CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(120) NOT NULL,
    nome_usuario VARCHAR(50) NOT NULL,
    email VARCHAR(150) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    pais VARCHAR(80) NOT NULL,
    estado VARCHAR(80) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    cargo ENUM('membro', 'gestor', 'maquinista') NOT NULL,
    status_acesso ENUM('pendente', 'aprovado', 'rejeitado', 'bloqueado') NOT NULL DEFAULT 'pendente',
    analisado_por INT NULL,
    analisado_em DATETIME NULL,
    observacao_acesso VARCHAR(255) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario),
    CONSTRAINT uq_usuarios_nome_usuario UNIQUE (nome_usuario),
    CONSTRAINT uq_usuarios_email UNIQUE (email),
    CONSTRAINT fk_usuarios_analisador FOREIGN KEY (analisado_por) REFERENCES usuarios (id_usuario) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX ix_usuarios_cargo (cargo),
    INDEX ix_usuarios_status_acesso (status_acesso),
    INDEX ix_usuarios_ativo (ativo)
);
