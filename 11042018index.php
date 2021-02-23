<?php

//ini_set('session.use_trans_sid', 1);
/*
if (isset($_GET['PHPSESSID'])) {
	session_id($_GET['PHPSESSID']);
}*/
session_start();
//exit;
?>
<html>
<head>
<title>FioSaúde - Sistema de Conectividade</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="css/style.css" rel="stylesheet" type="text/css">

<link rel="shortcut icon" href="imgs/favicon.ico" type="image/x-icon" />
</head>
<body>
<div id="layout">
	<div id="topo">
		<span class="logout"><?php if (isset($_SESSION['logado'])) print $_SESSION['login'] .' - '. $_SESSION['nm_usuario'] . ' - <a href="?pagina=mudar_senha">Mudar senha</a> - <a href="logout.php">Sair</a>'; ?></span>
		<h1><a href="index.php"><img src="imgs/FioSaude.jpg" /></a> <span class="titulo">TISS</span></h1>
	</div>
	<div id="conteudo">
<?php

if (isset($_SESSION['logado'])) {
	$pagina = isset($_GET['pagina'])? $_GET['pagina'] : null;

	require_once 'menu.php';

	switch ($pagina) {
		case 'mudar_senha':	  require_once 'mudar_senha.php';	break;
		case 'recebimento':	  require_once 'recebimento.php';	break;
		case 'elegibilidade': require_once 'elegibilidade.php'; break;
		case 'upload':        require_once 'upload.php';        break;
		case 'calendario':    require_once 'calendario.php';    break;
		case 'exportar':      require_once 'exportar.php';    break;
		case 'guiaconsulta':      require_once 'guiaconsulta.php';    break;
		case 'guiaconsulta_fabio':      require_once 'guiaconsulta_fabio.php';    break;
		case 'guiaconsultateste':      require_once 'guiaconsultateste.php';    break;
		case 'gridguia':      require_once 'gridguias.php';    break;
		default:
			require_once 'principal.php';
	}
} else {
	require_once 'login.php';
}

?>
	</div>
	<!--
	<div id="rodape">
		FioSaúde - <?php print date('Y') ?>
	</div>
	-->
</div>
</body>
</html>
