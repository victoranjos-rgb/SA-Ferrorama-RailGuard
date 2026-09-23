<?php
require_once __DIR__.'/../conexao.php';require_once __DIR__.'/../garantir_cargas.php';require_once __DIR__.'/resposta.php';require_once __DIR__.'/sessao_helper.php';exigirMetodo('GET');exigirUsuarioAutenticado();try{garantirTabelaCargas($conexao);}catch(Throwable $erro){responderErro(500,$erro->getMessage());}
$res=$conexao->query('SELECT status_carga,COUNT(*) total,COALESCE(SUM(peso_kg),0) peso FROM cargas GROUP BY status_carga');$status=[];while($l=$res->fetch_assoc())$status[]=$l;$ultimos=$conexao->query('SELECT codigo_carga,descricao,status_carga,peso_kg,origem,destino,atualizado_em FROM cargas ORDER BY atualizado_em DESC LIMIT 10')->fetch_all(MYSQLI_ASSOC);responderJson(200,['resumo'=>$status,'ultimas_cargas'=>$ultimos]);

