<?php
// Teste desenvolvido com auxílio de IA (OpenAI Codex).
if (PHP_SAPI !== 'cli') exit("Execute pelo terminal.\n");
require_once __DIR__ . '/../MAIN/backend/conexao.php';
$base = 'http://127.0.0.1:8080/RailGuard/SA-Ferrorama-RailGuard/MAIN/backend/api';
$sufixo = bin2hex(random_bytes(4)); $email = "gestor.trens.{$sufixo}@railguard.test"; $senha = 'TesteSeguro123!';
$idUsuario = 0; $idTrem = 0; $cookie = tempnam(sys_get_temp_dir(), 'railguard_trens_'); $resultados=[];
function req(string $metodo,string $url,?array $dados,string $cookie):array{$c=curl_init($url);$h=['Accept: application/json'];if($dados!==null){$h[]='Content-Type: application/json';curl_setopt($c,CURLOPT_POSTFIELDS,json_encode($dados));}curl_setopt_array($c,[CURLOPT_CUSTOMREQUEST=>$metodo,CURLOPT_HTTPHEADER=>$h,CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEJAR=>$cookie,CURLOPT_COOKIEFILE=>$cookie,CURLOPT_TIMEOUT=>30]);$corpo=curl_exec($c);if($corpo===false)throw new RuntimeException(curl_error($c));$status=curl_getinfo($c,CURLINFO_RESPONSE_CODE);curl_close($c);return ['status'=>$status,'dados'=>json_decode($corpo,true)];}
function ok(bool $valor,string $texto,array &$resultados):void{if(!$valor)throw new RuntimeException('FALHOU: '.$texto);$resultados[]='OK: '.$texto;}
try{
 $hash=password_hash($senha,PASSWORD_DEFAULT);$nome='Gestor CRUD';$usuario='gestor_crud_'.$sufixo;$cargo='gestor';$status='aprovado';$stmt=$conexao->prepare("INSERT INTO usuarios(nome,nome_usuario,email,senha_hash,pais,estado,cidade,cargo,status_acesso) VALUES(?,?,?,?,'Brasil','SC','Joinville',?,?)");$stmt->bind_param('ssssss',$nome,$usuario,$email,$hash,$cargo,$status);$stmt->execute();$idUsuario=$conexao->insert_id;$stmt->close();
 $login=req('POST',$base.'/login.php',['email'=>$email,'senha'=>$senha],$cookie);ok($login['status']===200,'gestor autentica para administrar trens',$resultados);
 $prefixo='TST-'.strtoupper($sufixo);$novo=['prefixo_trem'=>$prefixo,'modelo_trem'=>'Modelo Teste','tipo_combustivel'=>'diesel','ano_fabricacao'=>2020,'capacidade_toneladas'=>1000,'velocidade_maxima_kmh'=>120,'situacao_trem'=>'ativo'];
 $criar=req('POST',$base.'/trens.php',$novo,$cookie);$idTrem=(int)($criar['dados']['id_trem']??0);ok($criar['status']===201&&$idTrem>0,'criar trem',$resultados);
 $listar=req('GET',$base.'/trens.php',null,$cookie);ok($listar['status']===200&&in_array($idTrem,array_column($listar['dados']['trens']??[],'id_trem'),true),'listar trem',$resultados);
 $novo['modelo_trem']='Modelo Atualizado';$novo['situacao_trem']='manutencao';$editar=req('PUT',$base.'/trens.php?id='.$idTrem,$novo,$cookie);ok($editar['status']===200&&($editar['dados']['situacao_trem']??'')==='manutencao','editar trem e marcar manutenção',$resultados);
 $excluir=req('DELETE',$base.'/trens.php?id='.$idTrem,[],$cookie);ok($excluir['status']===200,'excluir trem',$resultados);$idTrem=0;
}finally{if($idTrem){$conexao->query('DELETE FROM trens WHERE id_trem='.(int)$idTrem);}if($idUsuario){$conexao->query('DELETE FROM usuarios WHERE id_usuario='.(int)$idUsuario);}@unlink($cookie);}
echo implode("\n",$resultados)."\nDados temporarios removidos.\n";
