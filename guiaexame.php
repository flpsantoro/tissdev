<?php
if (!isset($_SESSION['logado'])) return;
require_once dirname(__FILE__) . '/config.php';

$_SESSION['dados'] = null;
$_SESSION['nrLote'] = '-';

$dados = null;
$nrGuiaPrestador = null;
$seqImportacao = null;
?>

