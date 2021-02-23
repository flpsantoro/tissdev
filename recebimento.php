<?php

if (!isset($_SESSION['logado'])) return;

if ($_SESSION['tp_usuario'] <=  1) return;

?>
<h3>Recebimento</h3>
<form class="form1" method="post" action="?pagina=recebimento">
	Protocolo: <input type="text" name="nr_protocolo" size="10" value="<?php print isset($_REQUEST['nr_protocolo']) ? $_REQUEST['nr_protocolo'] : '' ?>" />
	CPF/CNPJ: <input type="text" name="cpf_cnpj" size="14" value="<?php print isset($_POST['cpf_cnpj']) ? $_POST['cpf_cnpj'] : '' ?>" />
	Nome: <input type="text" name="nm_prestador" value="<?php print isset($_POST['nm_prestador']) ? $_POST['nm_prestador'] : '' ?>" />
	<input type="submit" value="Buscar" />
</form>

<p></p>

<div class="resultado">

<?php

require_once dirname(__FILE__) . '/config.php';

$nr_protocolo = empty($_REQUEST['nr_protocolo']) ? null : $_REQUEST['nr_protocolo'];

$flag = '';

if (isset($nr_protocolo) && isset($_GET['acao']) && $_GET['acao'] == 'marcar') 
{
	$conn->beginTransaction();
	$stmt = $conn->prepare('
	UPDATE TISS_TRANSACAO_PROTOCOLO
		SET DT_RECEBIMENTO = SYSDATE
		WHERE NR_PROTOCOLO_RECEBIMENTO = :nr_protocolo
			AND CD_STATUS_PROTOCOLO = 1
			AND DT_RECEBIMENTO IS NULL
			AND ROWNUM = 1');
	$stmt->execute(array(':nr_protocolo' => $nr_protocolo));
	$conn->commit();

	if ($stmt->rowCount()) 
	{
		print '<div id="sucesso">Protocolo '. $nr_protocolo .' marcado como recebido!</div>';
	} 
	else 
	{
		$conn->beginTransaction();
		$stmt = $conn->prepare('
			UPDATE TISS_TRANSACAO_PROTOCOLO_DIG
				SET DT_RECEBIMENTO = SYSDATE
				WHERE NR_PROTOCOLO_RECEBIMENTO = :nr_protocolo
					AND CD_STATUS_PROTOCOLO = 5
					AND DT_RECEBIMENTO IS NULL
					AND ROWNUM = 1');
		$stmt->execute(array(':nr_protocolo' => $nr_protocolo));
		$conn->commit();
		if ($stmt->rowCount()) 
		{
			print '<div id="sucesso">Protocolo '. $nr_protocolo .' marcado como recebido!</div>';
		}
		else
		{
			print '<div id="erro">Não foi possível marcar o protocolo '. $nr_protocolo . ' como recebido!</div>';		
		}
	}
}

?>

</div>

<?php

if (!empty($_POST) || !is_null($nr_protocolo)) {
	$where = array();

	if (!is_null($nr_protocolo)) {
		$sql = "SELECT CPF_CGC, NR_PROTOCOLO_RECEBIMENTO, NR_LOTE, DECODE(CD_STATUS_PROTOCOLO, 0, 'Erro', 1, 'Aguardando', 2, 'Em analise', 3, 'Finalizado', 4, 'Excluído') DS_STATUS, TO_CHAR(DT_RECEBIMENTO, 'DD/MM/YYYY') DT_RECEBIMENTO, tiss_web.calcula_qtde_guias(nr_protocolo_recebimento) nr_guias, 'guias' as PAGINA FROM TISS_TRANSACAO_PROTOCOLO WHERE NR_PROTOCOLO_RECEBIMENTO = ". intval($nr_protocolo);

		$sql.= " UNION SELECT CPF_CGC, NR_PROTOCOLO_RECEBIMENTO, NR_LOTE, DECODE(CD_STATUS_PROTOCOLO, 0, 'Erro', 1, 'Aguardando', 2, 'Em analise', 3, 'Finalizado', 4, 'Excluído') DS_STATUS, TO_CHAR(DT_RECEBIMENTO, 'DD/MM/YYYY') DT_RECEBIMENTO, tiss_web.calcula_qtde_guias(nr_protocolo_recebimento) nr_guias, 'guias_dig' as PAGINA FROM TISS_TRANSACAO_PROTOCOLO_DIG WHERE NR_PROTOCOLO_RECEBIMENTO= ". intval($nr_protocolo);
		
	} else {
		$sql = "SELECT * FROM TISS_USUARIO ";
		$where[] = "ID_STATUS = 'A'";
		if (!empty($_POST['cpf_cnpj'])) {
			$where[] = "CPF_CNPJ LIKE '". $_POST['cpf_cnpj'] ."%'";
		}
		if (!empty($_POST['nm_prestador'])) {
			$where[] = "UPPER(NM_USUARIO) LIKE '%". $_POST['nm_prestador'] ."%'";
		}
		if (count($where) === 1) {
			$where[] = "1 = 0";
		}
	}
	$where = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

	if (!is_null($nr_protocolo)) {
?>
		<table class="table1">
			<tr>
				<th style="width: 18%;">CPF/CNPJ</th>
				<th style="width: 12%;">Protocolo</th>
				<th>Lote</th>
				<th style="width: 10%;">Nr guias</th>
				<th style="width: 15%;">Status</th>
				<th style="width: 15%;">Recebimento</th>
				<th>Ação</th>
			</tr>
<?php
	foreach ($conn->query($sql . $where) as $row) {
			switch ($row['DS_STATUS']) {
				case 'Erro':       $style = 'status_erro';       break;
				case 'Aguardando': $style = 'status_aguardando'; break;
				case 'Em analise': $style = 'status_analise';    break;
				case 'Excluído':   $style = 'status_excluido';   break;
				case 'Finalizado': $style = 'status_finalizado'; break;
			}

			if ($row['DS_STATUS'] == 'Em analise') {
				$row['DS_STATUS'] = 'Em análise';
			}

			if (($row['DS_STATUS'] == 'Erro' || $row['DS_STATUS'] == 'Excluído') || !empty($row['DT_RECEBIMENTO'])) {
				$acao = '&nbsp;';
			} else {
				$acao = '<a href="?pagina=recebimento&acao=marcar&nr_protocolo='. $row['NR_PROTOCOLO_RECEBIMENTO'] .'">Marcar como recebido</a>';
			}
?>
			<tr>
				<td><?php print $row['CPF_CGC'] ?></td>
				<td>
				<?php if ($row['PAGINA'] == 'guias')
				{
				?>
					<a href='guias.php?acao=ver&nr_protocolo=<?php echo stripslashes($row['NR_PROTOCOLO_RECEBIMENTO']);?>' target="_blank">
					<?php print $row['NR_PROTOCOLO_RECEBIMENTO'] ?></a>	
				<?php
				}
				else
				{
				?>
					<a href='guias_dig.php?acao=ver&cpf_cgc=<?php echo stripslashes($row['CPF_CGC']);?>&nr_protocolo=<?php echo stripslashes($row['NR_PROTOCOLO_RECEBIMENTO']);?>' target="_blank">
					<?php print $row['NR_PROTOCOLO_RECEBIMENTO'] ?></a>	
				<?php
				}
				?>
				</td>
				<td><?php print $row['NR_LOTE'] ?></td>
				<td><?php print $row['NR_GUIAS'] ?></td>
				<td class="<?php print $style ?>"><?php print $row['DS_STATUS'] ?></td>
				<td><?php print empty($row['DT_RECEBIMENTO']) ? '&nbsp;' : $row['DT_RECEBIMENTO'] ?></td>
				<td><?php print $acao ?></td>
			</tr>
<?php
		}

	} else {
?>
		<table class="table1">
			<tr>
				<th style="width: 20%;">CPF/CNPJ</th>
				<th>Nome</th>
				<th style="width: 25%;">Protocolo</th>
			</tr>
<?php
		foreach ($conn->query($sql . $where) as $row) {
			$nome = $row['NM_USUARIO'];
			if (!empty($_POST['nm_prestador'])) {
				$nome = str_replace($_POST['nm_prestador'], '<strong>'. $_POST['nm_prestador'] .'</strong>', $nome);
			}
?>
		<tr>
			<td><?php print $row['CPF_CNPJ'] ?></td>
			<td><?php print $nome ?></td>
			<td>
				<form method="get" action="?pagina=recebimento">
					<input type="hidden" name="pagina" value="recebimento"/>
					<select name="nr_protocolo" style="width: 100px;">
<?php $sql='SELECT NR_PROTOCOLO_RECEBIMENTO FROM TISS_TRANSACAO_PROTOCOLO
							 WHERE CD_STATUS_PROTOCOLO <> 0 AND CPF_CGC = '. $row['CPF_CNPJ'].' UNION SELECT NR_PROTOCOLO_RECEBIMENTO FROM TISS_TRANSACAO_PROTOCOLO_DIG WHERE CD_STATUS_PROTOCOLO <> 0 AND CPF_CGC = '. $row['CPF_CNPJ'];
	foreach ($conn->query($sql) as $row) { ?>
						<option><?php print $row['NR_PROTOCOLO_RECEBIMENTO'] ?></option>
<?php } ?>
					</select>
					<input type="submit" value="Ver"/>
				</form>
			</td>
		</tr>
<?php
		}
	}
}
?>
</table>
