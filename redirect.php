<h1><center> <img src="imgs/Box_calendario_02062020.jpg" height="80%" /></center> </h1>

<?php
$link = '/';
switch($_GET['q']) {
	case 'inicial':
		$link = '/index.php?pagina=inicial';
	break;
}
?>
<center> <input type="submit" onclick="javascript: window.location.href = '<?= $link ?>'" style="width: 10%; font-size: 20px; padding: 5px; background-color: green; color: #ffffff; border: 1px #000 solid; margin-bottom: 20px" value="Prosseguir"/></center>