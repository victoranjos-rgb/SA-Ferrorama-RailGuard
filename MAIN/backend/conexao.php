<?php

// Arquivo desenvolvido com auxilio de IA (OpenAI Codex).
// Consulte o arquivo REGISTRO_IA.md na raiz do projeto.

$caminhoConfiguracao = __DIR__ . '/config.php';

if (!file_exists($caminhoConfiguracao)) {
    die('Arquivo config.php nao encontrado. Copie config.exemplo.php para config.php e preencha os dados de conexao.');
}

$config = require $caminhoConfiguracao;

$camposObrigatorios = ['servidor', 'porta', 'usuario', 'senha', 'banco'];

foreach ($camposObrigatorios as $campo) {
    if (!array_key_exists($campo, $config)) {
        die('Configuracao obrigatoria ausente: ' . $campo);
    }
}

$conexao = mysqli_init();

if ($conexao === false) {
    die('Nao foi possivel iniciar a conexao com o MySQL.');
}

$certificadoCa = $config['certificado_ca'] ?? null;

if (!empty($certificadoCa)) {
    if (!file_exists($certificadoCa)) {
        die('Certificado CA nao encontrado no caminho configurado.');
    }

    $conexao->ssl_set(null, null, $certificadoCa, null, null);
}

$conectou = $conexao->real_connect(
    $config['servidor'],
    $config['usuario'],
    $config['senha'],
    $config['banco'],
    (int) $config['porta']
);

if (!$conectou) {
    die('Falha na conexao com o banco de dados: ' . $conexao->connect_error);
}

$conexao->set_charset('utf8mb4');

