<?php
//var_dump("----------SERVER---------");
//var_dump($_SERVER);
//var_dump("--------POST-------");
//var_dump($_POST);
//var_dump("-------------------");
//var_dump("----------GET---------");
//var_dump($_GET);
//var_dump("-------------------");
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	require dirname(__FILE__) .'/config.php';
	$conn->beginTransaction();
	//Registra o atendimento ao beneficiário! (UPDATE)
	//var_dump("----wwwwwww------POST---------");
    //var_dump($_POST);
	if (isset($_POST['Atendimento'])) {
		$nr_sequencial = $_POST['nr_sequencial'];
		$qtd_dias_validade = $_POST['qtd_dias_validade'];
		if ($qtd_dias_validade > 89)
			print '<script>alert("Prazo da senha de encaminhamento expirado! Favor retornar ao médico generalista para uma nova senha!");</script>';
		else{
			/*
				$sqlAlteracao = "UPDATE tiss_autorizacao_consulta
									SET dt_atendimento = sysdate,
										sn_atendimento = 'S'
								  WHERE nr_sequencial  = :nr_sequencial";
				//var_dump($sqlAlteracao);
				$stmt1Alteracao = $conn->prepare($sqlAlteracao);
				$stmt1Alteracao->execute(array(':nr_sequencial' => $nr_sequencial));
			*/
				//var_dump($sqlAlteracao);
				// Testes de chamada de procedure!
				$stmt = $conn->prepare("CALL TESTE_WEB(:nr_sequencial, :p_erro)");
				$nr_sequencialparam = $nr_sequencial;
				//var_dump($nr_sequencialparam);
				$stmt->bindParam(':nr_sequencial', $nr_sequencialparam, PDO::PARAM_INT);
				$stmt->bindParam(':p_erro',$p_erro,PDO::PARAM_STR|PDO::PARAM_INPUT_OUTPUT, 100);
				$stmt->execute();
				echo '<br>'; // Pular uma linha!!
				if (!empty($p_erro))
					{
						$conn->rollback();
						echo '<font color="red">Exceção capturada: '. $p_erro .'!!</font>' ."\n";
					}
				else
					{	$conn->commit();}
				//oci_bind_by_name($stmt, ':p_erro', $value1);
				//oci_execute($stmt);
				//print '<script>alert("Procedure de teste executada!!!");</script>';
				//var_dump($p_erro);
			}
	}
	if (isset($_POST['ConsultarSA'])) {
		//Pesquisa por Nome Social/Afetivo do Paciente/Beneficiário (SELECT)
		$txtNomeSocialAfetivo = $_POST['txtNomeSA'];
		if (!empty($_POST['txtNomeSA'])) // empty testa se a variável está vazia!
			{$qSocialAfetivo = " and UPPER(NVL(c.nm_social, c.nm_afetivo)) like UPPER('".$txtNomeSocialAfetivo."%') ";}
		else
			{$qSocialAfetivo = "";}

		//Pesquisa por Nome Civil do Paciente/Beneficiário (SELECT)
		$txtNome = $_POST['txtNome'];
		if (!empty($_POST['txtNome'])) // empty testa se a variável está vazia!
			{$qNomeCivil = " and UPPER(c.nome) like UPPER('".$txtNome."%') and c.nm_social is null and c.nm_afetivo is null ";}
		else
			{$qNomeCivil = "";}
	}
	if (isset($_POST['Atendimento_Realizado'])) {
		$qAtendimentosRealizados = " and UPPER(ac.sn_atendimento) = 'S' ";}
	else
	{
		$qAtendimentosRealizados = " and ((ac.sn_atendimento is null) or (ac.sn_atendimento is not null and trunc(ac.dt_atendimento) = trunc(sysdate))) ";
	}
	var_dump($_POST['Finalizar_Atendimento']);
/*
	if (isset($_POST['Finalizar_Atendimento'])) {
		$qAtendimentosRealizados = " and UPPER(ac.sn_atendimento) = 'S' ";}
	else
	{
		$qAtendimentosRealizados = " and ac.sn_atendimento is NULL ";
	}
*/
	//var_dump($qAtendimentosRealizados);
}
?>
<?php if (!isset($_SESSION['logado'])) return; ?>

<h3>Autorização de Consultas</h3>

<table id="MinhaTabela" class="table1" style="width:54%">
	<form action="" method="POST">
    <tr height="25">
		<td width="15%" ></td>
		<td colspan="3">
			Nome do Social/Afetivo <input type="text" style="width:250px;" name="txtNomeSA" autofocus>
			<button type="submit" name="ConsultarSA" value="ConsultarSocialAfetivo" font-weight="bold">Consultar</button>
			<!-- Abrir a página (do google, por exemplo) em uma nova janela!! -->
			<!-- <input type="button" value="Visualizar" id="Visualizar" onclick="javascript: location.href='http://www.google.com';" />  -->
			<!-- <input type="submit" name="Visualizar" id="Visualizar" value="Visualizar" onclick="window.open('http://www.google.com', '_blank');" /> -->
		</td>		
		<td width="30%"></td>
		<td width="30%" bgcolor="#E2E2E2" align="center">Legenda</td>
	</tr>
    <tr height="25">
		<td></td>
		<td colspan="3">
			Nome Civil <input type="text" style="width:250px;" name="txtNome">
			<input type="checkbox" name="Atendimento_Realizado" value="S"> Atendimentos Realizados<br>
		</td>		
		<td></td>
		<td bgcolor="#FF6969" align="center">Prazo Expirado</td>
	</tr>
    <tr height="25">
		<td  colspan="7" height="25" bgcolor="#CDF7FF"></td>
	</tr>
	</form>
	<tr height="25">
		<th style="width: 5%;">Autorização</th>
		<th style="width: 48%;">Paciente</th>
		<th style="width: 18%;">Solicitação</th>
		<th style="width: 18%;">Validade</th>
		<th style="width: 18%;"></th>
		<!-- <th style="width: 20%;">Especialidade</th> -->
		<!-- <th style="width: 15%;">Link</th>  -->
		<!--<th>Cpf Cgc Atend</th>-->
		<th></th>
	</tr>
<?php
require_once dirname(__FILE__) .'/config.php';
//var_dump("---EEE---POST---------");
//var_dump($_POST);
//var_dump("---------POST---------");
if (!isset($_POST['txtNomeSA'])) {
	$qSocialAfetivo = "";
}
if (!isset($_POST['txtNome'])) {
	$qNomeCivil = "";
}
if (!isset($_POST['Atendimento_Realizado'])) {
	$qAtendimentosRealizados = " and ((ac.sn_atendimento is null) or (ac.sn_atendimento is not null and trunc(ac.dt_atendimento) = trunc(sysdate))) ";
}
//var_dump($qNomeCivil);
$count = 0;
$query = "select ac.nr_sequencial autorizacao,
			   nvl(c.nm_social,nvl(c.nm_afetivo, c.nome)) paciente,
			   to_char(ac.dt_solicitacao,'dd/mm/yyyy') dt_solicitacao,
               to_char(ac.dt_solicitacao + 89,'dd/mm/yyyy') dt_validade,
               ac.sn_atendimento sn_atendimento,
               ac.cpf_cgc_atendimento cpf_cgc_atend,
               ac.cd_especialidade cd_especialidade,
               ac.ds_link ds_link,
               1 count,
               trunc(sysdate - ac.dt_solicitacao) qtd_dias_validade
          from tiss_autorizacao_consulta ac,
               iasm_carteira c
         where ac.nr_matricula_fioprev = c.nr_matricula_fioprev
           and ac.nr_dep_fioprev       = c.nr_dep_fioprev
           and ac.nr_dv_fioprev        = c.nr_dv_fioprev
		   and (ac.cpf_cgc_atendimento =".$_SESSION['cpf_cnpj']." or ac.cpf_cgc_atendimento is null)".$qNomeCivil."
		   ".$qSocialAfetivo."
		   ".$qAtendimentosRealizados."
           and c.nr_carteira = (select max(nr_carteira)
                                  from iasm_carteira c1
                                 where c1.nr_matricula_fioprev = c.nr_matricula_fioprev
                                   and c1.nr_dep_fioprev       = c.nr_dep_fioprev
                                   and c1.nr_dv_fioprev        = c.nr_dv_fioprev)
      order by ac.dt_solicitacao desc";
var_dump($query);
foreach ($conn->query($query) as $row) {
	$count = $count + $row['COUNT'];
?>
	<form action="" method="POST">
	<tr>
		<td width="8%" style="<?= $row['QTD_DIAS_VALIDADE'] > 89 ? "background: #FF6969; color: #000000" : ''; ?>" align="center">	<?php print $row['AUTORIZACAO'] ?></td>
		<!-- só vou deixar esta linha como exemplo para o futuro...<td width="8%" <?php if ($row['QTD_DIAS_VALIDADE'] > 89) { print 'style="background: #FF0000; color: #FFFFFF"'; } ?>>	<?php print $row['AUTORIZACAO'] ?></td> -->
		<td width="25%" align="left" style="<?= $row['QTD_DIAS_VALIDADE'] > 89 ? "background: #FF6969; color: #000000" : ''; ?>">  <?php print $row['PACIENTE'] ?></td>
		<td width="3%"  align="center" style="<?= $row['QTD_DIAS_VALIDADE'] > 89 ? "background: #FF6969; color: #000000" : ''; ?>"><?php print $row['DT_SOLICITACAO'] ?></td>
		<td width="3%"  align="center" style="<?= $row['QTD_DIAS_VALIDADE'] > 89 ? "background: #FF6969; color: #000000" : ''; ?>"><?php print $row['DT_VALIDADE'] ?></td>
		<!-- <td width="3%"  align="center" style="<?= $row['QTD_DIAS_VALIDADE'] > 89 ? "background: #FF6969; color: #000000" : ''; ?>"><?php print $row['CD_ESPECIALIDADE'] ?></td>  -->
		<!-- <td width="3%"  align="center" style="<?= $row['QTD_DIAS_VALIDADE'] > 89 ? "background: #FF6969; color: #000000" : ''; ?>"><?php print $row['DS_LINK'] ?></td>  -->
		<!-- <td width="7%"  align="left" style="<?= $row['QTD_DIAS_VALIDADE'] > 89 ? "background: #FF6969; color: #000000" : ''; ?>">  <?php print $row['CPF_CGC_ATEND'] ?></td> -->
        <td width="5%">
			
				<input type="hidden" name="nr_sequencial"     value="<?php print $row['AUTORIZACAO']?>">
				<input type="hidden" name="qtd_dias_validade" value="<?php print $row['QTD_DIAS_VALIDADE']?>">
			<!--	<input type="submit" name="cancelar" value="cancelar">  -->
			<!--	<input type="submit" name="Atendimento" value="Finalizar Atendimento" onclick="<?= (($row['QTD_DIAS_VALIDADE'] <= 89) and empty($p_erro)) ? "window.open('http://www.google.com', '_blank');" : ''; ?>"> -->
			<input type="submit" name="Atendimento" value="Atendimento" onclick="<?= (($row['QTD_DIAS_VALIDADE'] <= 89) and empty($p_erro)) ? "window.open('https://app02-homolog.healthmap.com.br/contrarefexterna?CD_CNSUL=917&CD_ESPMD=9', '_blank');" : ''; ?>">
				<!--	<input type="submit" name="Reverter" value="Reverter">	  -->	
		</td>
        <td width="5%">
			<input type="submit" name="Finalizar_Atendimento" value="Não Usar!!!" onclick="<?= (($row['QTD_DIAS_VALIDADE'] <= 89) and empty($p_erro)) ? "window.open('https://fiosaude.healthmap.com.br/contrarefexterna?CD_CNSUL=3741&CD_ESPMD=16', '_blank');" : ''; ?>">
		</td>		
	</tr>
	</form>
<?php } ?>
</table>
<!-- -------------------------------------------------------------------------------------------------- -->
