<?php
session_start();

if (!isset($_SESSION['logado'])) return;
if (empty($_GET['nr_protocolo'])) return;

$nr_protocolo = $_GET['nr_protocolo'];

?>
<html>
<head>
<title>FioSaúde - Sistema de Conectividade</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="css/protocolo.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" href="imgs/favicon.ico" type="image/x-icon" />
</head>
<body>
<?php

require_once dirname(__FILE__) . '/config.php';

$acao = isset($_GET['acao']) ? $_GET['acao'] : null;

if ($acao == 'excluir') {
	$conn->beginTransaction();

	$conn->exec("ALTER SESSION SET NLS_DATE_FORMAT='DD/MM/YYYY HH24:MI:SS'");

	$stmt = $conn->query('SELECT COUNT(*) ACHOU FROM TISS_CALENDARIO WHERE SYSDATE BETWEEN DT_INICIO AND DT_FIM');
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	$autorizado = $_SESSION['master'] || (!empty($row['ACHOU']) && $row['ACHOU'] === '1');

	if (!$autorizado) {
		$stmt = $conn->prepare('SELECT COUNT(*) ACHOU FROM TISS_USUARIO WHERE DT_LIBERADO = TRUNC(SYSDATE) AND CPF_CNPJ = :cpf_cnpj');
		$stmt->execute(array(':cpf_cnpj' => $_SESSION['cpf_cnpj']));
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		$autorizado = !empty($row['ACHOU']) && $row['ACHOU'] === '1';
	}

	if (!$autorizado) {
		print 'Não pode excluir arquivo após fechar o envio do mês!';
		return;
	}

	$stmt = $conn->prepare("
		SELECT XML_ENVIADO
		  FROM TISS_TRANSACAO_PROTOCOLO
		  WHERE CPF_CGC = :cpf_cgc
		    AND NR_PROTOCOLO_RECEBIMENTO = :nr_protocolo
		    AND CD_STATUS_PROTOCOLO IN (0, 1)
		    AND DT_RECEBIMENTO IS NULL
		    AND TRUNC(DT_ENVIO, 'MM') = TRUNC(SYSDATE, 'MM')");

	$stmt->execute(array(':cpf_cgc' => $_SESSION['cpf_cnpj'] , ':nr_protocolo' => $nr_protocolo));
	$dados   = $stmt->fetch(PDO::FETCH_ASSOC);
	if (empty($dados)) {
		print 'Este arquivo não pode ser excluído!';
		return;
	}
	$arquivo   = $dados['XML_ENVIADO'];

	if (empty($arquivo)) {
		print 'Erro ao excluir arquivo!';
		exit;
	}

	unlink(dirname(__FILE__) .'/arquivos/'. $_SESSION['cpf_cnpj'] .'/'. date('mY') .'/'. $arquivo);

	$stmt = $conn->prepare('BEGIN DELETA_PROTOCOLO(:cpf_cgc, :nr_protocolo); END;');
	$stmt->execute(array(':cpf_cgc' => $_SESSION['cpf_cnpj'] , ':nr_protocolo' => $nr_protocolo));

	$conn->commit();

	print 'Arquivo excluído com sucesso!';
	exit;
} else if ($acao == 'ver') {
	if ($_SESSION['tp_usuario'] >= 2) {
		$stmt = $conn->prepare("
			SELECT NR_LOTE,
					A.TP_GUIA,
					A.NR_GUIA,
					A.NR_CARTEIRA,
					B.NM_USUARIO,
					A.VL_TOTAL_GUIA,
					C.NR_PROTOCOLO_RECEBIMENTO,
					C.CPF_CGC,
					Z.NM_USUARIO NM_PRESTADOR
				FROM TISS_TRANSACAO_PROTOCOLO C,
					 TISS_V_GUIAS A,
					 TISS_BENEFICIARIO B,
					 TISS_USUARIO Z
				WHERE A.NR_CARTEIRA = B.NR_CARTEIRA(+)
					AND C.NR_LOTE = A.NR_PROTOCOLO
					AND C.NR_PROTOCOLO_RECEBIMENTO = :nr_protocolo
					AND C.NR_SEQ_IMPORTACAO = A.NR_SEQ_IMPORTACAO
					AND Z.CPF_CNPJ = C.CPF_CGC");

		$stmt->execute(array(':nr_protocolo' => $_GET['nr_protocolo']));
	} else {
		$stmt = $conn->prepare("
			SELECT NR_LOTE,
					A.TP_GUIA,
					A.NR_GUIA,
					A.NR_CARTEIRA,
					B.NM_USUARIO,
					A.VL_TOTAL_GUIA,
					C.NR_PROTOCOLO_RECEBIMENTO,
					C.CPF_CGC,
					Z.NM_USUARIO NM_PRESTADOR
				FROM TISS_TRANSACAO_PROTOCOLO C,
					 TISS_V_GUIAS A,
					 TISS_BENEFICIARIO B,
					 TISS_USUARIO Z
				WHERE A.NR_CARTEIRA = B.NR_CARTEIRA(+)
					AND C.NR_LOTE = A.NR_PROTOCOLO
					AND C.CPF_CGC =  :cpf_cgc
					AND C.NR_PROTOCOLO_RECEBIMENTO = :nr_protocolo
					AND C.NR_SEQ_IMPORTACAO = A.NR_SEQ_IMPORTACAO
					AND Z.CPF_CNPJ = C.CPF_CGC");

		$stmt->execute(array(':cpf_cgc' => $_SESSION['cpf_cnpj'],
							      ':nr_protocolo' => $_GET['nr_protocolo']));
	}

$total = 0;
$dados = $stmt->fetchAll();

if (empty($dados)) return;

foreach ($dados as $data) { $total += $data['VL_TOTAL_GUIA']; }

$nr_lote    = $dados[0]['NR_LOTE'];
$cpf_cgc    = $dados[0]['CPF_CGC'];
$nm_usuario = $dados[0]['NM_PRESTADOR'];

$data = date('d/m/Y');
$hora = date('H:i');

?>

<p class="imprimir">[ <a href="#" onclick="window.print(); return false;">Imprimir</a> ]</p>

<div id="protocolo">
	<h3>Relação de Guias</h3>
	<p class="datahora">Data: <?php print $data ?> / Hora: <?php print $hora ?></p>
	<table>
		<tr>
			<th colspan="5">Dados da Operadora</th>
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
			<td colspan="2">
				<strong>CNPJ da operadora</strong><br/>
				03.033.006/0001-53
			</td>
		</tr>
		<tr>
			<th colspan="5">Dados do prestador</th>
		</tr>
		<tr>
			<td>
				<strong>Código operadora</strong><br/>
				<?php print $cpf_cgc ?>
			</td>
			<td colspan="2">
				<strong>Nome</strong><br/>
				<?php print $nm_usuario ?>
			</td>
			<td>
				<strong>Protocolo</strong><br/>
				<?php print $_GET['nr_protocolo'] ?>
			</td>
			<td>
				<strong>Lote</strong><br/>
				<?php print $nr_lote ?>
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<th colspan="5">Guias</th>
		</tr>
		<tr>
			<td style="width: 15%">
				<strong>Número de Guia</strong><br/>
				<?php foreach ($dados as $data) { print $data['NR_GUIA'] . '<br/>'; } ?>
			</td>
			<td style="width: 12%">
				<strong>Tipo de Guia</strong><br/>
				<?php foreach ($dados as $data) { print $data['TP_GUIA'] . '<br/>'; } ?>
			</td>
			<td style="width: 13%">
				<strong>Nr da Carteira</strong><br/>
				<?php foreach ($dados as $data) { print $data['NR_CARTEIRA'] . '<br/>'; } ?>
			</td>
			<td>
				<strong>Nome do Usuário</strong><br/>
				<?php foreach ($dados as $data) { print $data['NM_USUARIO'] . '<br/>'; } ?>
			</td>
			<td style="width: 15%">
				<strong>Valor</strong><br/>
				<?php foreach ($dados as $data) { print 'R$ '. number_format($data['VL_TOTAL_GUIA'], 2, ',', '.') .'<br/>'; } ?>
			</td>
		</tr>
<?php  ?>
		<tr>
			<th colspan="4">Total</th>
			<th>R$ <?php print number_format($total, 2, ',', '.') ?></th>
		</tr>
	</table>
</div>

<?php } ?>
</body>
</html>
