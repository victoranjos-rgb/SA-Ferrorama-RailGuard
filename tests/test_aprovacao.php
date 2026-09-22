<?php

// Teste desenvolvido com auxílio de IA (OpenAI Codex).
// Executa o fluxo real de aprovação e remove todos os dados temporários ao final.

if (PHP_SAPI !== 'cli') {
    exit("Execute este teste pelo terminal.\n");
}

require_once __DIR__ . '/../MAIN/backend/conexao.php';

$baseUrl = $argv[1] ?? 'http://127.0.0.1:8080/RailGuard/SA-Ferrorama-RailGuard/MAIN/backend/api';
$sufixo = bin2hex(random_bytes(5));
$senhaTeste = 'TesteSeguro123!';
$emailGestor = "gestor.{$sufixo}@railguard.test";
$emailPendente = "pendente.{$sufixo}@railguard.test";
$idsCriados = [];
$resultados = [];
$cookieGestor = tempnam(sys_get_temp_dir(), 'railguard_gestor_');
$cookieUsuario = tempnam(sys_get_temp_dir(), 'railguard_usuario_');

function requisicao(string $metodo, string $url, ?array $dados = null, string $cookieJar = ''): array
{
    $curl = curl_init($url);
    $cabecalhos = ['Accept: application/json'];

    if ($dados !== null) {
        $cabecalhos[] = 'Content-Type: application/json';
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dados));
    }

    curl_setopt_array($curl, [
        CURLOPT_CUSTOMREQUEST => $metodo,
        CURLOPT_HTTPHEADER => $cabecalhos,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 30,
    ]);

    if ($cookieJar !== '') {
        curl_setopt($curl, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieJar);
    }

    $resposta = curl_exec($curl);

    if ($resposta === false) {
        throw new RuntimeException('Falha HTTP no teste: ' . curl_error($curl));
    }

    $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $tamanhoCabecalho = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $cabecalho = substr($resposta, 0, $tamanhoCabecalho);
    $corpo = substr($resposta, $tamanhoCabecalho);
    curl_close($curl);

    preg_match('/^Set-Cookie:\s*([^;\r\n]+)/mi', $cabecalho, $cookieEncontrado);

    return [
        'status' => $status,
        'dados' => json_decode($corpo, true),
        'cookie' => $cookieEncontrado[1] ?? '',
    ];
}

function verificar(bool $condicao, string $descricao, array &$resultados): void
{
    if (!$condicao) {
        throw new RuntimeException('FALHOU: ' . $descricao);
    }

    $resultados[] = 'OK: ' . $descricao;
}

try {
    $senhaHash = password_hash($senhaTeste, PASSWORD_DEFAULT);
    $stmt = $conexao->prepare(
        "INSERT INTO usuarios (nome, nome_usuario, email, senha_hash, pais, estado, cidade, cargo, status_acesso)
         VALUES (?, ?, ?, ?, 'Brasil', 'Santa Catarina', 'Joinville', ?, ?)"
    );

    $nome = 'Gestor Teste';
    $usuario = 'gestor_' . $sufixo;
    $cargo = 'gestor';
    $status = 'aprovado';
    $stmt->bind_param('ssssss', $nome, $usuario, $emailGestor, $senhaHash, $cargo, $status);
    $stmt->execute();
    $idGestor = $conexao->insert_id;
    $idsCriados[] = $idGestor;

    $nome = 'Pendente Teste';
    $usuario = 'pendente_' . $sufixo;
    $cargo = 'membro';
    $status = 'pendente';
    $stmt->bind_param('ssssss', $nome, $usuario, $emailPendente, $senhaHash, $cargo, $status);
    $stmt->execute();
    $idPendente = $conexao->insert_id;
    $idsCriados[] = $idPendente;
    $stmt->close();

    $loginPendente = requisicao('POST', $baseUrl . '/login.php', [
        'email' => $emailPendente,
        'senha' => $senhaTeste,
    ]);
    verificar($loginPendente['status'] === 403, 'usuário pendente não consegue entrar', $resultados);

    $loginGestor = requisicao('POST', $baseUrl . '/login.php', [
        'email' => $emailGestor,
        'senha' => $senhaTeste,
    ], $cookieGestor);
    verificar($loginGestor['status'] === 200, 'gestor aprovado consegue entrar', $resultados);

    $lista = requisicao('GET', $baseUrl . '/usuarios_pendentes.php', null, $cookieGestor);
    $idsPendentes = array_column($lista['dados']['usuarios'] ?? [], 'id_usuario');
    verificar(
        $lista['status'] === 200 && in_array($idPendente, $idsPendentes, true),
        'gestor visualiza o cadastro pendente (HTTP ' . $lista['status'] . '; resposta: ' . json_encode($lista['dados']) . ')',
        $resultados
    );

    $analise = requisicao('POST', $baseUrl . '/analisar_usuario.php', [
        'id_usuario' => $idPendente,
        'decisao' => 'aprovar',
        'cargo' => 'maquinista',
        'observacao' => 'Aprovação automática do teste.',
    ], $cookieGestor);
    verificar($analise['status'] === 200, 'gestor aprova e confirma o cargo', $resultados);

    $loginAprovado = requisicao('POST', $baseUrl . '/login.php', [
        'email' => $emailPendente,
        'senha' => $senhaTeste,
    ], $cookieUsuario);
    verificar(
        $loginAprovado['status'] === 200 && ($loginAprovado['dados']['usuario']['cargo'] ?? '') === 'maquinista',
        'usuário aprovado entra com o cargo confirmado',
        $resultados
    );

    $acessoIndevido = requisicao('GET', $baseUrl . '/usuarios_pendentes.php', null, $cookieUsuario);
    verificar($acessoIndevido['status'] === 403, 'não gestor não acessa a lista de aprovações', $resultados);
} finally {
    if (count($idsCriados) > 0) {
        rsort($idsCriados);
        $marcadores = implode(',', array_fill(0, count($idsCriados), '?'));
        $tipos = str_repeat('i', count($idsCriados));
        $stmt = $conexao->prepare("DELETE FROM usuarios WHERE id_usuario IN ({$marcadores})");
        $stmt->bind_param($tipos, ...$idsCriados);
        $stmt->execute();
        $stmt->close();
    }

    @unlink($cookieGestor);
    @unlink($cookieUsuario);
}

echo implode("\n", $resultados) . "\n";
echo "Dados temporarios removidos.\n";
