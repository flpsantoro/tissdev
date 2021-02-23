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

 if ($acao == 'ver') {

		$stmt = $conn->prepare("
			       	SELECT 
			          G.NR_GUIA,
			          TO_DATE(G.DT_EMISSAO,'DD/MM/YYYY') DT_EMISSAO,
			          G.NR_CARTEIRA,
			          G.NR_CNS,
			          G.CPF_CGC,
			          G.CD_CNES,
			          G.NM_PROFISSIONAL_EXECUTANTE,
			          G.CD_CONSELHO,
			          G.NR_CONSELHO,               
			          G.CD_UF_CONSELHO,
			          G.CD_CBO,                   
			          G.CD_INDICADOR_ACIDENTE,
			          TO_DATE(G.DT_EVENTO,'DD/MM/YYYY') DT_EVENTO,
			          G.CD_TABELA,                 
			          G.CD_ESPECIALIDADE,
			          G.CD_TIPO_CONSULTA,          
			          G.DS_OBS,
			          G.NR_PROCESSO_REFERENCIA,
			          TO_DATE(G.DT_RECEBIMENTO,'DD/MM/YYYY') DT_RECEBIMENTO,
					  G.NR_GUIA_PRESTADOR,
					  TO_DATE(G.DT_IMPORT_SISTEMA,'DD/MM/YYYY') DT_IMPORT_SISTEMA,
					  G.CD_USUARIO_IMPORT_SISTEMA,
					  G.NR_SEQ_IMPORTACAO,
					  G.SN_ATENDIMENTO_RN,
					  G.VL_CONSULTA,
					  TO_DATE(G.DT_VALIDADE_CARTEIRA,'DD/MM/YYYY'),
					  IP.NM_PRESTADOR NOMEPRESTADOR,
					  CAR.NOME NM_USUARIO,
					  P.TP_GUIA,
					  P.NR_LOTE
					   FROM  
					   		TISS_GUIA_CONSULTA_DIG G
					   LEFT JOIN  TISS_TRANSACAO_PROTOCOLO_DIG P
								ON G.NR_SEQ_IMPORTACAO = P.NR_SEQ_IMPORTACAO
					   LEFT JOIN IASM_PRESTADOR IP
			           			ON IP.CPF_CGC = :cpf_cgc
					   LEFT JOIN IASM_CARTEIRA CAR 
                          		ON CAR.NR_CARTEIRA = G.NR_CARTEIRA
					   WHERE p.nr_protocolo_recebimento = :nr_protocolo");

		$stmt->execute(array(':cpf_cgc' => $_GET['cpf_cgc'],
							      ':nr_protocolo' => $_GET['nr_protocolo']));
	}

$total = 0;
$dados = $stmt->fetchAll();

if (empty($dados)) return;

foreach ($dados as $data) { $total += $data['VL_CONSULTA']; }

$nr_lote    = $dados[0]['NR_LOTE'];
$cpf_cgc    = $dados[0]['CPF_CGC'];
$nm_usuario = $dados[0]['NOMEPRESTADOR'];

$data = date('d/m/Y');
$hora = date('H:i');

?>

<p class="imprimir">[ <a href="#" onclick="window.print(); return false;">Imprimir</a> ]</p>

<div id="protocolo">
	<h3><u>Relação de Guias</u></h3>
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
				<?php foreach ($dados as $data) { print 'R$ '. number_format($data['VL_CONSULTA'], 2, ',', '.') .'<br/>'; } ?>
			</td>
		</tr>
<?php  ?>
		<tr>
			<th colspan="4">Total</th>
			<th>R$ <?php print number_format($total, 2, ',', '.') ?></th>
		</tr>
	</table>
	</div>
</body>
</html>
