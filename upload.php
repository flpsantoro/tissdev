<?php

if (!isset($_SESSION['logado'])) return;

require_once dirname(__FILE__) .'/config.php';

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

?>

<h3>Consultar/Enviar arquivo</h3>
<!-- <script>
mostrarImagemCovid19(0);
</script>

<div id="mostrarErrorHtml0"></div> -->

<?php if ($autorizado) { ?>

<div class="form1">
	<form method="post" enctype="multipart/form-data">
		Arquivo: <input type="file" name="arquivo"/> <input type="submit" value="Enviar" /><br/><br/>
		<span class="upload_obs"><strong>Obs.:</strong> Aceitamos somente arquivo .XML!<br/> Estamos aceitando as versões do TISS 2.02.1, 2.02.2, 2.02.3, 3.02.0</span>
	</form>
</div>

<?php } ?>

<div class="resultado">
<?php

if ($autorizado && isset($_FILES['arquivo'])) {
	if ($_FILES['arquivo']['error'] != 0) {
		switch ($_FILES['arquivo']['error']) {
			case 1:
			case 2:	$msg = 'O seu arquivo excedeu o tamanho limite de envio!'; break;
			case 3:	$msg = 'O arquivo foi enviado parcialmente!'; break;
			case 4:	$msg = 'Nenhum arquivo foi enviado!'; break;
			case 7:	$msg = 'Falha ao escrever no disco!'; break;
		}
		print '<div id="erro"><strong>Erro:</strong> '.  $msg .'</div>';
	} else {
		define('TESTING', 0);
		define('RETURN_DIR', './return/' );

		require_once dirname(__FILE__) .'/config.php';
		require_once dirname(__FILE__) .'/processador/libs/arquivoxml.class.php';

		$arquivo  = $_FILES['arquivo']['tmp_name'];
		$ret_dir  = dirname(__FILE__) .'/retorno/' . date('mY') .'/';
		$xml_dir  = dirname(__FILE__) .'/arquivos/'. $_SESSION['cpf_cnpj'] .'/'. date('mY') .'/';
		$xml_file = $xml_dir . $_FILES['arquivo']['name'];

		try {
			if (!preg_match('/\.xml$/i', $xml_file)) {
				throw new ExtensaoInvalida;
			}

			if (file_exists($xml_file)) {
				throw new Exception('Este arquivo já foi enviado!');
			}

			$db = oci_connect(USER, PASS, '//'. HOST .':1521/XE', 'AL32UTF8');

			@mkdir(dirname($ret_dir));
			@mkdir($ret_dir);
			@mkdir(dirname($xml_dir));
			@mkdir($xml_dir);
			rename($arquivo, $xml_file);

			$file_date = filectime($xml_file);

			$obj = new ArquivoXML($xml_file);
			$ret = $obj->processa($ret_dir, $msg);

			if ($ret) {
				print '<div id="sucesso">Validado e processado com sucesso!</div>';
			} else {
				print '<div id="erro">Arquivo com erro no processamento!<br/>('. $msg .')</div>';
			}
		} catch (Exception $e) {
			if (($e instanceof ErrorXMLInvalido) ||
					($e instanceof ErrorSchemaInvalido) ||
					($e instanceof ErrorSchema) ||
					($e instanceof ErrorDB)) {
				@unlink($xml_file);
			}
			print '<div id="erro"><strong>Erro:</strong> '. $e->getMessage() . (empty($e->tiss_obs) ? '' : ' ('. $e->tiss_obs . ')') .'</div>';
		}
	}
}

$stmt = $conn->prepare("
	SELECT NR_PROTOCOLO_RECEBIMENTO,
			NR_LOTE,
			TO_CHAR(DT_ENVIO, 'DD/MM/YYYY') DT_ENVIO,
			CPF_CGC,
			XML_RETORNO,
			XML_ENVIADO,
			DECODE(CD_STATUS_PROTOCOLO, 2, 'Em analise', 0, 'Erro', 1, 'Aguardando', 4, 'Excluído', 3, 'Finalizado') DS_STATUS
		FROM TISS_TRANSACAO_PROTOCOLO
		WHERE CPF_CGC = :cpf_cgc
		ORDER BY NR_SEQUENCIAL_TRANSACAO DESC");

$stmt->execute(array(':cpf_cgc' => $_SESSION['cpf_cnpj']));

?>
</div>

<table class="table1">
	<tr>
		<th>Protocolo</th>
		<th>Lote</th>
		<th style="width: 15%;">Data Envio</th>
		<th>Ações</th>
		<th>Status</th>
	</tr>
<?php
while ($dados = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$xml_enviado = '';

	if ($dados['DS_STATUS'] != 'Excluído') {
		$xml_enviado = 'arquivos/'. $dados['CPF_CGC'] .'/'. implode('', explode('/', substr($dados['DT_ENVIO'], 3, 7))) . '/'. $dados['XML_ENVIADO'];
	}
	$xml_retorno = 'retorno/'. implode('', explode('/', substr($dados['DT_ENVIO'], 3, 7))) .'/'. $dados['CPF_CGC'] .'/'. $dados['XML_RETORNO'];

	if ($dados['DS_STATUS'] == 'Em analise') {
		$dados['DS_STATUS'] = 'Em análise';
	}

	$links = '';
	if ($dados['DS_STATUS'] != 'Erro' && $dados['DS_STATUS'] != 'Excluído') {
		$links .= ' - <a href="guias.php?acao=ver&nr_protocolo='. $dados['NR_PROTOCOLO_RECEBIMENTO'] .'" target="_blank">Guias</a>';
		$links .= ' - <a href="protocolo.php?nr_protocolo='. $dados['NR_PROTOCOLO_RECEBIMENTO'] .'" target="_blank">Protocolo</a>';
	}
	if ($autorizado && $dados['DS_STATUS'] !== 'Em análise' && $dados['DS_STATUS'] != 'Excluído') {
		$links .= ' - <a href="guias.php?acao=excluir&nr_protocolo='. $dados['NR_PROTOCOLO_RECEBIMENTO'] .'" target="_blank">Excluir</a>';
	}

	switch ($dados['DS_STATUS']) {
		case 'Erro':       $style = 'status_erro';       break;
		case 'Aguardando': $style = 'status_aguardando'; break;
		case 'Em análise': $style = 'status_analise';    break;
		case 'Excluído':   $style = 'status_excluido';   break;
		case 'Finalizado': $style = 'status_finalizado'; break;
	}
?>
		<tr>
			<td width="13%"><?php print $dados['NR_PROTOCOLO_RECEBIMENTO'] ?></td>
			<td><?php print $dados['NR_LOTE'] ?></td>
			<td><?php print $dados['DT_ENVIO'] ?></td>
			<td><?php if (!empty($xml_enviado)) { ?><a href="<?php print $xml_enviado ?>" target="_blank">XML</a> -<?php } ?>
				<a href="<?php print $xml_retorno ?>" target="_blank">Resposta</a> <?php print $links ?></td>
			<td class="<?php print $style ?>"><?php print $dados['DS_STATUS'] ?></td>
		</tr>
<?php } ?>
</table>
