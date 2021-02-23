<?php
/*
var_dump("----------SERVER---------");
var_dump($_SERVER);
var_dump("----------POST---------");
var_dump($_POST);
var_dump("----------GET---------");
var_dump($_GET);
var_dump("-------------------");
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	require dirname(__FILE__) .'/config.php';
		
	$conn->beginTransaction();
	//Registra o atendimento ao beneficiário! (UPDATE)
	if (isset($_POST['Atendimento'])) {
		$nr_sequencial = $_POST['nr_sequencial'];
		$qtd_dias_validade = $_POST['qtd_dias_validade'];
		//var_dump($nr_sequencial);
		// update data
		if ($qtd_dias_validade > 89)
			print '<script>alert("Prazo da senha de encaminhamento expirado! Favor retornar ao médico generalista para uma nova senha!");</script>';
		else{
				$sqlAlteracao = "UPDATE tiss_autorizacao_consulta
									SET dt_atendimento = sysdate,
										sn_atendimento = 'S'
								  WHERE nr_sequencial  = :nr_sequencial";
				//var_dump($sqlAlteracao);
				$stmt1Alteracao = $conn->prepare($sqlAlteracao);
				$stmt1Alteracao->execute(array(':nr_sequencial' => $nr_sequencial));
				//var_dump($sqlAlteracao);
				$conn->commit();
			}
	}
	//Pesquisa por Nome do Paciente/Beneficiário (SELECT)
	if (isset($_POST['Consultar'])) {
		$txtNome = $_POST['txtNome'];
		if (isset($_POST['txtNome']))
			{
				$q1 = " and UPPER(c.nome) like UPPER('".$txtNome."%') ";
				/*$sqlConsultar = "select ac.nr_sequencial autorizacao,
									    c.nome paciente,
									    to_char(ac.dt_solicitacao,'dd/mm/yyyy') dt_solicitacao,
									    to_char(ac.dt_solicitacao + 89,'dd/mm/yyyy') dt_validade,
									    ac.sn_atendimento sn_atendimento,
									    ac.cpf_cgc_atendimento cpf_cgc_atend,
									    1 count,
									    trunc(sysdate - ac.dt_solicitacao) qtd_dias_validade
								   from tiss_autorizacao_consulta ac,
									    iasm_carteira c
								  where ac.nr_matricula_fioprev = c.nr_matricula_fioprev
								    and ac.nr_dep_fioprev       = c.nr_dep_fioprev
								    and ac.nr_dv_fioprev        = c.nr_dv_fioprev
								    and (ac.cpf_cgc_atendimento =".$_SESSION['cpf_cnpj']." or ac.cpf_cgc_atendimento is null)
									and c.nome like  :txtNome
								    and ac.dt_atendimento is null
								    and c.nr_carteira = (select max(nr_carteira)
									 					   from iasm_carteira c1
									 					  where c1.nr_matricula_fioprev = c.nr_matricula_fioprev
									 					    and c1.nr_dep_fioprev       = c.nr_dep_fioprev
									 					    and c1.nr_dv_fioprev        = c.nr_dv_fioprev)
							   order by ac.dt_solicitacao desc";
				$stmt1Consultar = $conn->prepare($sqlConsultar);
				$stmt1Consultar->execute(array(':txtNome' => $txtNome));
				$row = $stmt1Consultar->fetchAll();							   
				var_dump($sqlConsultar);*/
			}
		else
			{$q1 = "";}
	}
}
?>
<?php if (!isset($_SESSION['logado'])) return; ?>

<h3>Autorização de Consultas</h3>

<table id="MinhaTabela" class="table1" style="width:60%">
    <tr>
		<td></td>
		<td colspan="3">
			<form action="" method="POST">
				Nome do Paciente <input type="text" style="width:250px;" name="txtNome" autofocus>
				<button type="submit" name="Consultar" value="Consultar" font-weight="bold">Consultar</button>
			</form>
		</td>		
		<td></td>
	</tr>
	<tr>
		<th>Autorização</th>
		<th>Paciente</th>
		<th style="width: 15%;">Solicitação</th>
		<th style="width: 15%;">Validade</th>
		<!--<th>Cpf Cgc Atend</th>-->
		<th></th>
	</tr>
<?php
require_once dirname(__FILE__) .'/config.php';
//var_dump($_SESSION['cpf_cnpj']);
if (!isset($_POST['Consultar'])) {
	$q1 = "";
}
$count = 0;
$query = "select ac.nr_sequencial autorizacao,
			   c.nome paciente,
			   to_char(ac.dt_solicitacao,'dd/mm/yyyy') dt_solicitacao,
               to_char(ac.dt_solicitacao + 89,'dd/mm/yyyy') dt_validade,
               ac.sn_atendimento sn_atendimento,
               ac.cpf_cgc_atendimento cpf_cgc_atend,
               1 count,
               trunc(sysdate - ac.dt_solicitacao) qtd_dias_validade
          from tiss_autorizacao_consulta ac,
               iasm_carteira c
         where ac.nr_matricula_fioprev = c.nr_matricula_fioprev
           and ac.nr_dep_fioprev       = c.nr_dep_fioprev
           and ac.nr_dv_fioprev        = c.nr_dv_fioprev
		   and (ac.cpf_cgc_atendimento =".$_SESSION['cpf_cnpj']." or ac.cpf_cgc_atendimento is null)".$q1."
		   and ac.dt_atendimento is null
           and c.nr_carteira = (select max(nr_carteira)
                                  from iasm_carteira c1
                                 where c1.nr_matricula_fioprev = c.nr_matricula_fioprev
                                   and c1.nr_dep_fioprev       = c.nr_dep_fioprev
                                   and c1.nr_dv_fioprev        = c.nr_dv_fioprev)
      order by ac.dt_solicitacao desc";
foreach ($conn->query($query) as $row) {
	$count = $count + $row['COUNT'];
?>
	<tr>
		<td width="8%" style="<?= $row['QTD_DIAS_VALIDADE'] > 89 ? "background: #FF0000; color: #FFFFFF" : ''; ?>">	<?php print $row['AUTORIZACAO'] ?></td>
		<!-- <td width="8%" <?php if ($row['QTD_DIAS_VALIDADE'] > 89) { print 'style="background: #FF0000; color: #FFFFFF"'; } ?>>	<?php print $row['AUTORIZACAO'] ?></td> -->
		<td width="25%" align="left" style="<?= $row['QTD_DIAS_VALIDADE'] > 89 ? "background: #FF0000; color: #FFFFFF" : ''; ?>">  <?php print $row['PACIENTE'] ?></td>
		<td width="3%"  align="center" style="<?= $row['QTD_DIAS_VALIDADE'] > 89 ? "background: #FF0000; color: #FFFFFF" : ''; ?>"><?php print $row['DT_SOLICITACAO'] ?></td>
		<td width="3%"  align="center" style="<?= $row['QTD_DIAS_VALIDADE'] > 89 ? "background: #FF0000; color: #FFFFFF" : ''; ?>"><?php print $row['DT_VALIDADE'] ?></td>
		<!-- <td width="7%"  align="left" style="<?= $row['QTD_DIAS_VALIDADE'] > 89 ? "background: #FF0000; color: #FFFFFF" : ''; ?>">  <?php print $row['CPF_CGC_ATEND'] ?></td> -->
        <td width="7%">
			<form action="" method="POST">
				<input type="hidden" name="nr_sequencial"     value="<?php print $row['AUTORIZACAO']?>">
				<input type="hidden" name="qtd_dias_validade" value="<?php print $row['QTD_DIAS_VALIDADE']?>">
			<!--	<input type="submit" name="cancelar" value="cancelar">  -->
				<input type="submit" name="Atendimento" value="Atendimento">				
			<!--	<input type="submit" name="Reverter" value="Reverter">	  -->	
			</form>
		</td>
	</tr>
<?php } ?>

</table>
<!-- -------------------------------------------------------------------------------------------------- -->
