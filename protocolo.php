<?php
session_start();
 
if (!isset($_SESSION['logado'])) return;
if (empty($_GET['nr_protocolo'])) return;

?>
<html>
<head>
<title>FioSaúde - Sistema de Conectividade</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="css/protocolo.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" href="imgs/favicon.ico" type="image/x-icon" />
</head>
<body>

<p class="imprimir">[ <a href="#" onclick="window.print(); return false;">Imprimir</a> ]</p>

<?php

$data = date('d/m/Y');
$hora = date('H:i');

require_once dirname(__FILE__) . '/config.php';

$stmt = $conn->prepare("
	SELECT NR_LOTE,
			TO_CHAR(DT_ENVIO, 'DD/MM/YYYY') DT_ENVIO,
			VL_TOTAL
		FROM TISS_TRANSACAO_PROTOCOLO 
		WHERE CPF_CGC = :cpf_cgc 
		  AND NR_PROTOCOLO_RECEBIMENTO = :nr_protocolo");
	
$stmt->execute(array(':cpf_cgc'      => $_SESSION['cpf_cnpj'],
					 ':nr_protocolo' => $_GET['nr_protocolo']));

$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($data)) return;

?>

<div id="protocolo">	
	<h3>Protocolo de Recebimento</h3>
	<p class="datahora">Data: <?php print $data ?> / Hora: <?php print $hora ?></p>
	<table>
		<tr>
			<th colspan="4">Dados da Operadora</th>
		</tr>
		<tr>
			<td>
				<strong>Registro ANS</strong><br/>
				417548
			</td>
			<td colspan="2">
				<strong>Nome da operadora</strong><br/>
				FIOSAUDE
			</td>
			<td>
				<strong>CNPJ da operadora</strong><br/>
				03.033.006/0001-53
			</td>
		</tr>
		<tr>
			<th colspan="4">Dados do prestador</th>
		</tr>
		<tr>
			<td>
				<strong>Código operadora</strong><br/>
				<?php print $_SESSION['cpf_cnpj'] ?>
			</td>
			<td colspan="2">
				<strong>Nome</strong><br/>
				<?php print $_SESSION['nm_usuario'] ?>
			</td>
			<td>
				<strong>Código CNES</strong><br/>
				<?php print $_SESSION['cnes'] .'&nbsp;' ?>
			</td>
		</tr>
		<tr>
			<th colspan="4">Dados do lote</th>
		</tr>
		<tr>
			<td>
				<strong>Data de envio do lote</strong><br/>
				<?php print $dados['DT_ENVIO'] ?>
			</td>
			<td>
				<strong>Número do lote</strong><br/>
				<?php print $dados['NR_LOTE'] ?>
			</td>
			<td>
				<strong>Número do protocolo</strong><br/>
				<?php print $_GET['nr_protocolo'] ?>
			</td>
			<td>
				<strong>Valor do protocolo</strong><br/>
				R$ <?php print number_format($dados['VL_TOTAL'], 2, ',', '.') ?>
			</td>
		</tr>
	</table>
	<p class="via">2ª Via Prestador</p>
</div>

<br/>
<br/>
<br/>
<br/>
<hr>
<br/>
<br/>
<br/>
<br/>

<div id="protocolo">	
	<h3>Protocolo de Recebimento</h3>
	<p class="datahora">Data: <?php print $data ?> / Hora: <?php print $hora ?></p>
	<table>
		<tr>
			<th colspan="4">Dados da Operadora</th>
		</tr>
		<tr>
			<td>
				<strong>Registro ANS</strong><br/>
				417548
			</td>
			<td colspan="2">
				<strong>Nome da operadora</strong><br/>
				FIOSAUDE
			</td>
			<td>
				<strong>CNPJ da operadora</strong><br/>
				03.033.006/0001-53
			</td>
		</tr>
		<tr>
			<th colspan="4">Dados do prestador</th>
		</tr>
		<tr>
			<td>
				<strong>Código operadora</strong><br/>
				<?php print $_SESSION['cpf_cnpj'] ?>
			</td>
			<td colspan="2">
				<strong>Nome</strong><br/>
				<?php print $_SESSION['nm_usuario'] ?>
			</td>
			<td>
				<strong>Código CNES</strong><br/>
				<?php print $_SESSION['cnes'] .'&nbsp;' ?>
			</td>
		</tr>
		<tr>
			<th colspan="4">Dados do lote</th>
		</tr>
		<tr>
			<td>
				<strong>Data de envio do lote</strong><br/>
				<?php print $dados['DT_ENVIO'] ?>
			</td>
			<td>
				<strong>Número do lote</strong><br/>
				<?php print $dados['NR_LOTE'] ?>
			</td>
			<td>
				<strong>Número do protocolo</strong><br/>
				<?php print $_GET['nr_protocolo'] ?>
			</td>
			<td>
				<strong>Valor do protocolo</strong><br/>
				R$ <?php print number_format($dados['VL_TOTAL'], 2, ',', '.') ?>
			</td>
		</tr>
	</table>
	<p class="via">1ª Via Prestador</p>
</div>
</body>
</html>
