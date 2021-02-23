<?php

ini_set('display_errors', 0);

session_start();

if (!isset($_SESSION['logado'])) return;

require_once dirname(__FILE__) .'/config.php';
$rows = $conn->query(
	sprintf("
		SELECT NVL(NOME_SOCIAL, NOME_AFETIVO) NM_USUARIO
		  FROM IASM.IASM_USUARIO
		 WHERE NVL(NOME_SOCIAL, NOME_AFETIVO) LIKE '%s%%'
		   AND DT_EXCLUSAO IS NULL
	  ORDER BY 1", isset($_GET['term']) ? strtoupper($_GET['term']) : ''));
/*
$rows = $conn->query(
	sprintf("
		SELECT NVL(NOME_SOCIAL,NVL(NOME_AFETIVO,NM_USUARIO)) NM_USUARIO
		  FROM IASM.IASM_USUARIO
		 WHERE NVL(NOME_SOCIAL,NVL(NOME_AFETIVO,NM_USUARIO)) LIKE '%s%%'
		   AND DT_EXCLUSAO IS NULL", isset($_GET['term']) ? strtoupper($_GET['term']) : ''));
*/
$nomes = array();
foreach ($rows as $row) {
	$nomes[] = $row['NM_USUARIO'];
}

print json_encode($nomes);
exit;
