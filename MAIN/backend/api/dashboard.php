<?php
require_once __DIR__.'/../conexao.php';require_once __DIR__.'/resposta.php';require_once __DIR__.'/sessao_helper.php';exigirMetodo('GET');exigirUsuarioAutenticado();
function totais(mysqli $c,string $sql):array{$r=$c->query($sql);$dados=[];while($l=$r->fetch_assoc())$dados[$l['nome']]=(int)$l['total'];return $dados;}
$trens=totais($conexao,'SELECT situacao_trem nome,COUNT(*) total FROM trens GROUP BY situacao_trem');
$manutencoes=totais($conexao,'SELECT status_manutencao nome,COUNT(*) total FROM manutencoes_trens GROUP BY status_manutencao');
responderJson(200,['trens'=>['total'=>array_sum($trens),'ativo'=>$trens['ativo']??0,'manutencao'=>$trens['manutencao']??0,'inativo'=>$trens['inativo']??0],'manutencoes'=>['total'=>array_sum($manutencoes),'agendada'=>$manutencoes['agendada']??0,'em_andamento'=>$manutencoes['em_andamento']??0,'concluida'=>$manutencoes['concluida']??0,'cancelada'=>$manutencoes['cancelada']??0]]);

