<?php

session_start();

if (!isset($_SESSION['logado'])) return;

if ($_SESSION['tp_usuario'] !== 3) return;

require_once dirname(__FILE__) .'/config.php';

$stmt = $conn->prepare("
	SELECT A.CPF_CGC,
			A.XML_ENVIADO,
			B.NM_USUARIO,
			A.NR_PROTOCOLO_RECEBIMENTO
		FROM TISS_TRANSACAO_PROTOCOLO A,
			TISS_USUARIO B
		WHERE CD_STATUS_PROTOCOLO IN (1,2)
			AND TO_CHAR(DT_ENVIO, 'MMYYYY') = :mes
			AND A.CPF_CGC = B.CPF_CNPJ
			AND EXISTS (SELECT 1 FROM TISS_OUTRAS_DESPESAS Z WHERE Z.NR_SEQ_IMPORTACAO = A.NR_SEQ_IMPORTACAO)");

$mes = $_GET['mes'];

$stmt->execute(array(':mes' => $mes));
$rows = $stmt->fetchAll();

if (!count($rows)) {
	echo '<div id="erro">Nenhum XML foi encontrado!</div>';
	exit;
}

foreach ($rows as $row) {
	if (!file_exists('arquivos/'. $row['CPF_CGC'] .'/'. $mes .'/'. $row['XML_ENVIADO'])) {
		print $row['CPF_CGC'] .' - '. $row['NM_USUARIO'] . ' - ' . $row['XML_ENVIADO'] . ' - ';
		print file_exists('arquivos/'. $row['CPF_CGC'] .'/'. $mes .'/'. $row['XML_ENVIADO']) ? 'OK' : 'Erro';
		print '<br/>';
	}
//	fwrite($fp, '02;'. $row['CPF_CGC'] .';'. $row['NM_USUARIO'] .';'. $row['NR_PROTOCOLO_RECEBIMENTO'] .';'. $row['XML_ENVIADO'] ."\r\n");
//	@exec('cd arquivos/ ; zip /tmp/arq.zip '. $row['CPF_CGC'] .'/'. $mes .'/'. escapeshellcmd($row['XML_ENVIADO']));
}

