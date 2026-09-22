<?php
require_once __DIR__ . '/../conexao.php'; require_once __DIR__ . '/resposta.php'; exigirMetodo('POST');
$d=lerDadosRequisicao(); $email=strtolower(trim((string)($d['email']??''))); $codigo=trim((string)($d['codigo']??''));
if (!filter_var($email,FILTER_VALIDATE_EMAIL)||!preg_match('/^\d{6}$/',$codigo)) responderErro(422,'Informe o e-mail e o codigo de seis digitos.');
$hash=hash('sha256',$codigo); $s=$conexao->prepare('SELECT r.id_recuperacao FROM recuperacoes_senha r INNER JOIN usuarios u ON u.id_usuario=r.id_usuario WHERE u.email=? AND r.codigo_hash=? AND r.usado_em IS NULL AND r.expira_em>=NOW() ORDER BY r.id_recuperacao DESC LIMIT 1'); $s->bind_param('ss',$email,$hash); $s->execute(); $r=$s->get_result()->fetch_assoc(); $s->close();
if(!$r) responderErro(400,'Codigo invalido ou expirado. Solicite um novo codigo.');
$token=bin2hex(random_bytes(32)); $tokenHash=hash('sha256',$token); $id=(int)$r['id_recuperacao']; $s=$conexao->prepare('UPDATE recuperacoes_senha SET token_redefinicao_hash=?,verificado_em=NOW() WHERE id_recuperacao=?'); $s->bind_param('si',$tokenHash,$id); $s->execute(); $s->close(); responderJson(200,['mensagem'=>'Codigo confirmado. Escolha sua nova senha.','token_redefinicao'=>$token]);
