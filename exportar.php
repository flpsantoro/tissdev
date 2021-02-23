<?php

if (!isset($_SESSION['logado'])) return;

if ($_SESSION['tp_usuario'] !== 3) return;

require_once dirname(__FILE__) .'/config.php';

if (isset($_POST['mes'])) {
	$mes = sprintf('%06d', intval(str_replace('/', '', $_POST['mes'])));

	$arq  = '/tmp/arq.zip';
	$arq2 = '/tmp/protocolos.txt';

	@unlink($arq);
	@unlink($arq2);

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
		    -- AND A.NR_PROTOCOLO_RECEBIMENTO in (91007786,91007785,91007784)
		    AND EXISTS (SELECT 1 FROM TISS_OUTRAS_DESPESAS Z WHERE Z.NR_SEQ_IMPORTACAO = A.NR_SEQ_IMPORTACAO AND ROWNUM = 1)");

	$stmt->execute(array(':mes' => $mes));
	$rows = $stmt->fetchAll();

	if (!count($rows)) {
		echo '<div id="erro">Nenhum XML foi encontrado!</div>';
		exit;
	}

	$erro = '';
	$lines = array();

	foreach ($rows as $row) {
		$xml_enviado = preg_replace('/ /', '\ ', $row['XML_ENVIADO']);

		if (!file_exists('arquivos/'. $row['CPF_CGC'] .'/'. $mes .'/'. $row['XML_ENVIADO'])) {
			$erro .= 'Arquivo '. $xml_enviado . ' não encontrado do prestador '. $row['CPF_CGC'] .'<br/>';
		} else {
			$lines[] = '02;'. $row['CPF_CGC'] .';'. $row['NM_USUARIO'] .';'. $row['NR_PROTOCOLO_RECEBIMENTO'] .';'. $row['XML_ENVIADO'];
		}

		/* adiciona xml o arquivo ao zip */
		@exec('cd arquivos/ ; zip /tmp/arq.zip '. $row['CPF_CGC'] .'/'. $mes .'/'. $xml_enviado);
	}

	$erro = false;

	if ($erro) {
		echo '<div id="erro">'. $erro .'</div>';
	} else {
		/* arquivo de protocolo */
		$fp = fopen($arq2, 'w');
		fwrite($fp, '01;'. date('dmY') .';'. count($lines) ."\r\n");
		fwrite($fp, implode("\r\n", $lines));
		fclose($fp);

		@exec('cd /tmp ; zip '. basename($arq) .' '. basename($arq2));

		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="xmls_'. $mes .'.zip"');
		header('Content-Length: '. filesize($arq));

		ob_end_clean();

		passthru('cat '. $arq, $err);
		exit;
	}
}

$meses = array();
foreach ($conn->query("
	SELECT DISTINCT TO_CHAR(DT_ENVIO, 'MM/YYYY') MES
      FROM TISS_TRANSACAO_PROTOCOLO
	  WHERE CD_STATUS_PROTOCOLO NOT IN (0,4)
	  ORDER BY 1") as $row) {
	$meses[] = $row['MES'];
}

?>

<h3>Exportar XMLs</h3>

<form method="post">
	<select name="mes">
<?php foreach ($meses as $mes) { ?>
		<option value="<?php print $mes ?>"><?php print $mes ?></option>
<?php } ?>
	</select>

	<input type="submit" value="Gerar .zip" />
</form>
