<?php

// Teste desenvolvido com auxílio de IA (OpenAI Codex).
if (PHP_SAPI !== 'cli') exit("Execute pelo terminal.\n");
require_once __DIR__ . '/../MAIN/backend/conexao.php';
$base = 'http://127.0.0.1:8080/RailGuard/SA-Ferrorama-RailGuard/MAIN/backend/api';
$sufixo = bin2hex(random_bytes(4));
$senha = 'TesteSeguro123!';
$idGestor = 0;
$idUsuario = 0;
$cookie = tempnam(sys_get_temp_dir(), 'rg_usr_');

function reqUsuario(string $metodo, string $url, ?array $dados, string $cookie): array
{
    $curl = curl_init($url);
    $cabecalhos = ['Accept: application/json'];
    if ($dados !== null) {
        $cabecalhos[] = 'Content-Type: application/json';
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dados));
    }
    curl_setopt_array($curl, [CURLOPT_CUSTOMREQUEST=>$metodo,CURLOPT_HTTPHEADER=>$cabecalhos,CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEJAR=>$cookie,CURLOPT_COOKIEFILE=>$cookie]);
    $corpo = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    return [$status, json_decode($corpo, true)];
}

function okUsuario(bool $condicao, string $descricao): void
{
    if (!$condicao) throw new RuntimeException("Falha: $descricao");
    echo "OK: $descricao.\n";
}

try {
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $emailGestor = "gestor.usuarios.$sufixo@railguard.test";
    $loginGestor = 'gu_' . $sufixo;
    $nomeGestor = 'Gestor Usuarios';
    $stmt = $conexao->prepare("INSERT INTO usuarios(nome,nome_usuario,email,senha_hash,pais,estado,cidade,cargo,status_acesso) VALUES(?,?,?,?,'Brasil','SC','Joinville','gestor','aprovado')");
    $stmt->bind_param('ssss', $nomeGestor, $loginGestor, $emailGestor, $hash);
    $stmt->execute();
    $idGestor = $conexao->insert_id;

    $emailUsuario = "membro.usuarios.$sufixo@railguard.test";
    $loginUsuario = 'mu_' . $sufixo;
    $nomeUsuario = 'Membro Usuarios';
    $stmt = $conexao->prepare("INSERT INTO usuarios(nome,nome_usuario,email,senha_hash,pais,estado,cidade,cargo,status_acesso) VALUES(?,?,?,?,'Brasil','SC','Joinville','membro','pendente')");
    $stmt->bind_param('ssss', $nomeUsuario, $loginUsuario, $emailUsuario, $hash);
    $stmt->execute();
    $idUsuario = $conexao->insert_id;

    reqUsuario('POST', "$base/login.php", ['email'=>$emailGestor,'senha'=>$senha], $cookie);
    [$status,$resposta] = reqUsuario('GET', "$base/usuarios.php", null, $cookie);
    okUsuario($status===200 && in_array($idUsuario,array_column($resposta['usuarios']??[],'id_usuario'),true), 'listar usuarios');

    [$status,$resposta] = reqUsuario('PUT', "$base/usuarios.php?id=$idUsuario", ['cargo'=>'maquinista','status_acesso'=>'aprovado','ativo'=>true,'observacao_acesso'=>'Teste automatizado'], $cookie);
    okUsuario($status===200 && ($resposta['usuario']['cargo']??'')==='maquinista', 'alterar cargo e acesso');

    [$status] = reqUsuario('PUT', "$base/usuarios.php?id=$idGestor", ['cargo'=>'membro','status_acesso'=>'aprovado','ativo'=>true], $cookie);
    okUsuario($status===409, 'proteger o proprio acesso do gestor');
} finally {
    if ($idUsuario) $conexao->query('DELETE FROM usuarios WHERE id_usuario='.$idUsuario);
    if ($idGestor) $conexao->query('DELETE FROM usuarios WHERE id_usuario='.$idGestor);
    @unlink($cookie);
}

echo "Dados temporarios removidos.\n";
