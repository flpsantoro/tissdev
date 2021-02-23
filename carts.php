<?php

ini_set('display_errors', 0);

session_start();

if (!isset($_SESSION['logado'])) return;

require_once dirname(__FILE__) .'/config.php';
$rows = $conn->query(
	sprintf("
		SELECT 
			CART.NOME, 
			TO_CHAR(CART.DT_VALIDADE,'DD/MM/YYYY') DT_VALIDADE,
			NR_CNS	
		FROM  
			IASM_CARTEIRA CART
		WHERE 
			NR_CARTEIRA = %d",$_GET['codCarteira']));

$nomes = array();
foreach ($rows as $row) {
	$nomes[] = $row;
}


print json_encode($nomes);
exit;
