<?php

// Dados de demonstração adicionados com auxílio de IA (OpenAI Codex).
// O prefixo é único; executar novamente apenas atualiza os mesmos cinco trens.

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este arquivo deve ser executado somente pelo terminal.');
}

require_once __DIR__ . '/conexao.php';

$trens = [
    ['LOC-1001', 'GE AC44i', 'diesel', 2014, 6200.00, 120.00, 'ativo'],
    ['LOC-1002', 'EMD SD70ACe', 'diesel', 2011, 5800.50, 110.00, 'manutencao'],
    ['LOC-1003', 'GE Dash 9', 'diesel', 2006, 5400.00, 105.00, 'ativo'],
    ['AUT-2001', 'Automotriz VLI', 'diesel', 2019, 900.00, 100.00, 'ativo'],
    ['LOC-1004', 'EMD GT46AC', 'diesel', 1999, 5100.75, 100.00, 'inativo'],
];

$sql = 'INSERT INTO trens (prefixo_trem, modelo_trem, tipo_combustivel, ano_fabricacao, capacidade_toneladas, velocidade_maxima_kmh, situacao_trem)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE modelo_trem=VALUES(modelo_trem), tipo_combustivel=VALUES(tipo_combustivel), ano_fabricacao=VALUES(ano_fabricacao), capacidade_toneladas=VALUES(capacidade_toneladas), velocidade_maxima_kmh=VALUES(velocidade_maxima_kmh), situacao_trem=VALUES(situacao_trem)';
$stmt = $conexao->prepare($sql);

foreach ($trens as [$prefixo, $modelo, $combustivel, $ano, $capacidade, $velocidade, $situacao]) {
    $stmt->bind_param('sssidds', $prefixo, $modelo, $combustivel, $ano, $capacidade, $velocidade, $situacao);
    $stmt->execute();
    echo $prefixo . " salvo.\n";
}

$stmt->close();
echo "Cinco trens de demonstracao estao disponiveis.\n";
