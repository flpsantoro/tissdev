<div id="menu">>>
  <ul>
	<li><a href="?pagina=inicial">Inicial</a></li>
	<li><a href="?pagina=elegibilidade">Elegibilidade</a></li>
<?php if ($_SESSION['tp_usuario'] >= 2) { ?>
	<li><a href="?pagina=recebimento">Recebimento</a></li>
<?php } ?>
<?php if ($_SESSION['tp_usuario'] === 3) { ?>
	<li><a href="?pagina=exportar">Exportar XMLs</a></li>
<?php } ?>
	<li><a href="?pagina=calendario">Calendário de Entrega/Envio</a></li>
	<li><a href="?pagina=upload">Consultar/Enviar arquivo</a></li>
	<li><a href="mailto:jorge@fiosaude.org.br?subject=FioSaúde%20TISS">Contato</a></li>
  </ul>
</div>
