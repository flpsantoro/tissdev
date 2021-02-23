<?php

ini_set('display_errors', 0);

session_start();

if (!isset($_SESSION['logado'])) return;

require_once dirname(__FILE__) .'/config.php';

$rows = $conn->query(
	sprintf("
		SELECT NM_USUARIO
		  FROM IASM.IASM_USUARIO
		  WHERE NM_USUARIO LIKE '%s%%'
		    AND DT_EXCLUSAO IS NULL
	   ORDER BY 1", isset($_GET['term']) ? strtoupper($_GET['term']) : ''));

$nomes = array();
foreach ($rows as $row) {
	$nomes[] = $row['NM_USUARIO'];
}

print json_encode($nomes);
exit;
