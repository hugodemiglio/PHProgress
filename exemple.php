<?php

include 'phprogress.class.php';

$tela = new Porcentagem;
$tela->inicio();
$tela->turbo(true);
$tela->mostrar(array('barra'=>true,'tempo'=>true,'roda'=>true,'fps'=>true));

for($i = 1;$i <= 100; $i = $i + 0.001){
	$tela->exibir($i);
}

$tela->concluido();

?>