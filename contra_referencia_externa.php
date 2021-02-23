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
    var_dump($_POST);
	//if (isset($_POST['Atendimento'])) {
		
	//}
}
?>
<?php if (!isset($_SESSION['logado'])) return; ?>

<h3>Contra Referência Externa</h3>

<table style="width:100%" border="0">
	<form action="" method="POST">
	<tr>
		<td align="left" > <textarea name="referencia_ida" rows="10" cols="200" maxlength="1000" disabled></textarea> </td>
	</tr>
	<tr>
		<td align="left" > <textarea name="referencia_volta" rows="10" cols="200" maxlength="1000" autofocus></textarea> </td>
	</tr>
	<tr>
		<td align="left" > <textarea name="referencia_volta_retorno" rows="10" cols="200" maxlength="1000"></textarea> </td>
	</tr>
	<tr>
		<td align="right" > <!-- <input type="submit" font="20" style="width: 150px; height: 80px" name="Reg_Referencia" value="Registra Atendimento"  align="right" /> -->
		<button type="submit" class="btn btn-primary btn-lg" style="width: 150px; height: 80px" >Registra Atendimento</button> </td>
	</tr>
	</form>
</table>
<!-- -------------------------------------------------------------------------------------------------- -->
