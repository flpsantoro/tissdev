<?php if (!isset($_SESSION['logado'])) return; ?>

<h3>Calendário de Entrega/Envio</h3>


<font  color="red" align="center">

<p>Lembrando que o eletrônico (XML), será recebido até o último dia do calendário até às 13h.</p>
</font>

<table class="table1 calendario">
	<tr>
		<th>Mês</th>
		<th>Entrega Físico e Eletrônico</th>
		<th>Previsão de Pagamento</th>
	</tr>
<?php

require_once dirname(__FILE__) .'/config.php';

foreach ($conn->query("
	SELECT TO_CHAR(DT_REF, 'MM/YYYY') MES,
			DS_DIAS_ENTREGA,
			TO_CHAR(DT_PREVISTA_PAGAMENTO, 'DD/MM/YYYY') DT_PREVISTA
		FROM TISS_CALENDARIO
		ORDER BY DT_REF") as $row) {
?>

	<tr><td><?php print $row['MES'] ?></td><td><?php print $row['DS_DIAS_ENTREGA'] ?></td><td><?php print $row['DT_PREVISTA'] ?></td></tr>

<?php } ?>

</table>

<!--
<p>O site fica disponível para o envio a partir das 08:00h do primeiro dia útil do mês, e finaliza no 4º dia às 13:00h.</p>
<p>O horário de entrega do Faturamento Físico na sede da FioSaúde é de 08:00 às 16:30 h (Seguindo os dias do calendário)</p>
<p><strong>Endereço de entrega: </strong> Av. Brasil, 4.036 / 3° andar – Manguinhos – CEP: 21.040-361</p>
-->
<p><strong>Endereço de entrega: </strong> Av. Brasil, 4.036 / 3° andar – Manguinhos – CEP: 21.040-361</p>