<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/resposta.php';
exigirMetodo('POST');
$dados = lerDadosRequisicao();
$email = strtolower(trim((string) ($dados['email'] ?? '')));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) responderErro(422, 'Informe um e-mail valido.');
$stmt = $conexao->prepare('SELECT id_usuario, nome, email, ativo FROM usuarios WHERE email=? LIMIT 1');
$stmt->bind_param('s', $email); $stmt->execute(); $usuario = $stmt->get_result()->fetch_assoc(); $stmt->close();
$resposta = ['mensagem' => 'Se existir uma conta ativa com este e-mail, enviaremos um codigo de recuperacao.'];
if (!$usuario || (int)$usuario['ativo'] !== 1) responderJson(200, $resposta);
$codigo = (string)random_int(100000, 999999); $hash = hash('sha256', $codigo); $expira = date('Y-m-d H:i:s', time() + 900); $id = (int)$usuario['id_usuario'];
$conexao->begin_transaction();
try {
  $s=$conexao->prepare('UPDATE recuperacoes_senha SET usado_em=NOW() WHERE id_usuario=? AND usado_em IS NULL'); $s->bind_param('i',$id); $s->execute(); $s->close();
  $s=$conexao->prepare('INSERT INTO recuperacoes_senha (id_usuario,codigo_hash,expira_em) VALUES (?,?,?)'); $s->bind_param('iss',$id,$hash,$expira); $s->execute(); $s->close(); $conexao->commit();
} catch (Throwable $e) { $conexao->rollback(); responderErro(500,'Nao foi possivel iniciar a recuperacao de senha.'); }
$opcoes = $config['recuperacao_senha'] ?? []; $modo = $opcoes['modo'] ?? 'desenvolvimento';
if ($modo === 'email' && !empty($opcoes['remetente'])) {
  @mail($usuario['email'], 'Codigo de recuperacao - RailGuard', "Seu codigo e: {$codigo}\nEle expira em 15 minutos.", "From: {$opcoes['remetente']}\r\nContent-Type: text/plain; charset=UTF-8");
} else { $resposta['codigo_teste']=$codigo; $resposta['mensagem']='Codigo gerado. Em ambiente local, use o codigo exibido nesta tela.'; }
responderJson(200,$resposta);
