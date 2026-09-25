<?php

// Teste desenvolvido com auxílio de IA (OpenAI Codex).
if (PHP_SAPI !== 'cli') exit("Execute pelo terminal.\n");
require_once __DIR__ . '/../MAIN/backend/conexao.php';

$base = 'http://127.0.0.1:8080/RailGuard/SA-Ferrorama-RailGuard/MAIN/backend/api';
$sufixo = bin2hex(random_bytes(4));
$email = "sensor.$sufixo@railguard.test";
$senha = 'TesteSeguro123!';
$idUsuario = 0;
$idSensor = 0;
$cookie = tempnam(sys_get_temp_dir(), 'rg_sensor_');

function requisicaoSensor(string $metodo, string $url, ?array $dados, string $cookie): array
{
    $curl = curl_init($url);
    $cabecalhos = ['Accept: application/json'];
    if ($dados !== null) {
        $cabecalhos[] = 'Content-Type: application/json';
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dados));
    }
    curl_setopt_array($curl, [CURLOPT_CUSTOMREQUEST => $metodo, CURLOPT_HTTPHEADER => $cabecalhos, CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $cookie, CURLOPT_COOKIEFILE => $cookie]);
    $corpo = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    return [$status, json_decode($corpo, true)];
}

function confirmarSensor(bool $condicao, string $descricao): void
{
    if (!$condicao) throw new RuntimeException("Falha: $descricao");
    echo "OK: $descricao.\n";
}

try {
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $nome = 'Gestor Sensor';
    $login = 'sensor_' . $sufixo;
    $cargo = 'gestor';
    $statusAcesso = 'aprovado';
    $stmt = $conexao->prepare("INSERT INTO usuarios(nome,nome_usuario,email,senha_hash,pais,estado,cidade,cargo,status_acesso) VALUES(?,?,?,?,'Brasil','SC','Joinville',?,?)");
    $stmt->bind_param('ssssss', $nome, $login, $email, $hash, $cargo, $statusAcesso);
    $stmt->execute();
    $idUsuario = $conexao->insert_id;
    requisicaoSensor('POST', "$base/login.php", ['email' => $email, 'senha' => $senha], $cookie);

    $dados = ['codigo_sensor' => 'SEN-' . strtoupper($sufixo), 'tipo_sensor' => 'velocidade', 'modelo_sensor' => 'Modelo teste', 'id_rota' => '', 'localizacao' => 'Km 12', 'unidade_medida' => 'km/h', 'limite_alerta' => 80, 'status_sensor' => 'ativo'];
    [$status, $resposta] = requisicaoSensor('POST', "$base/sensores.php", $dados, $cookie);
    $idSensor = (int) ($resposta['id_sensor'] ?? 0);
    confirmarSensor($status === 201 && $idSensor > 0, 'criar sensor');

    [$status, $resposta] = requisicaoSensor('GET', "$base/sensores.php", null, $cookie);
    confirmarSensor($status === 200 && in_array($idSensor, array_column($resposta['sensores'] ?? [], 'id_sensor'), true), 'listar sensor');

    $dados['status_sensor'] = 'manutencao';
    [$status, $resposta] = requisicaoSensor('PUT', "$base/sensores.php?id=$idSensor", $dados, $cookie);
    confirmarSensor($status === 200 && ($resposta['status_sensor'] ?? '') === 'manutencao', 'editar sensor');

    [$status] = requisicaoSensor('DELETE', "$base/sensores.php?id=$idSensor", [], $cookie);
    confirmarSensor($status === 200, 'excluir sensor');
    $idSensor = 0;
} finally {
    if ($idSensor) $conexao->query('DELETE FROM sensores WHERE id_sensor=' . $idSensor);
    if ($idUsuario) $conexao->query('DELETE FROM usuarios WHERE id_usuario=' . $idUsuario);
    @unlink($cookie);
}

echo "Dados temporarios removidos.\n";
