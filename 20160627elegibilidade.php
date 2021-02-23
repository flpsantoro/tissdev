<?php if (!isset($_SESSION['logado'])) return; ?>

<link href="js/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
<script src="js/jquery-1.10.2.js"></script>
<script src="js/jquery-ui-1.10.4.custom.js"></script>

<script>
$(function() {
	$( "#nome" ).autocomplete({ source: 'benefs.php', minLength: 2 });
});
</script>

<h3>Elegibilidade</h3>
<p>
  <form method="post" class="form1">
	Carteira: <input type="text" name="nr_carteira" size="10" value="<?php print isset($_POST['nr_carteira']) ? $_POST['nr_carteira'] : ''; ?>" />
	CPF: <input type="text" name="cpf" size="11" value="<?php print isset($_POST['cpf']) ? $_POST['cpf'] : ''; ?>" />
	Nome: <input type="text" id="nome" name="nome" size="32" />
	<input type="submit" value="Consultar" />
  </form>
</p>

<?php
if (!isset($_POST['nr_carteira'])) return;
?>

<div id="elegibilidade">

<?php
require_once 'config.php';

if (!empty($_POST['nome'])) {
	$stmt = $conn->prepare('
		SELECT NM_USUARIO, NR_MATRICULA, SEXO, NM_PLANO PLANO FROM IASM.IASM_USUARIO WHERE NM_USUARIO = :nome AND DT_EXCLUSAO IS NULL
		UNION
		SELECT NM_USUARIO, NR_MATRICULA, SEXO, PLANO FROM IASM.IASM_DEPENDENTES WHERE NM_USUARIO = :nome AND DT_EXCLUIDO IS NULL');

	$stmt->execute(array(':nome' => $_POST['nome']));
} else if (!empty($_POST['cpf'])) {
	$stmt = $conn->prepare('
		SELECT NM_USUARIO, NR_MATRICULA, SEXO, NM_PLANO PLANO FROM IASM.IASM_USUARIO WHERE CPF = :cpf AND DT_EXCLUSAO IS NULL
		UNION
		SELECT NM_USUARIO, NR_MATRICULA, SEXO, PLANO FROM IASM.IASM_DEPENDENTES WHERE CPF = :cpf AND DT_EXCLUIDO IS NULL');

	$stmt->execute(array(':cpf' => $_POST['cpf']));
} else {
	$stmt = $conn->prepare('
		SELECT NM_USUARIO, NR_MATRICULA, SEXO, NM_PLANO PLANO FROM IASM.IASM_USUARIO WHERE NR_MATRICULA = :matricula
		UNION
		SELECT NM_USUARIO, NR_MATRICULA, SEXO, PLANO FROM IASM.IASM_DEPENDENTES WHERE NR_MATRICULA = :matricula2');

	$matricula = substr($_POST['nr_carteira'], 0, -2);

	$stmt->execute(array(':matricula' => $matricula, ':matricula2' => $matricula));
}

$dados    = $stmt->fetch(PDO::FETCH_ASSOC);
$nome     = empty($dados) ? null : $dados['NM_USUARIO'];
$carteira = empty($dados) ? null : $dados['NR_MATRICULA'];
$sexo     = empty($dados) ? null : $dados['SEXO'];
$plano    = empty($dados) ? null : $dados['PLANO'];

$stmt = $conn->prepare("
	SELECT COUNT(*) ACHOU
	  FROM IASM.IASM_LIVRO_CRED_EXC
	  WHERE CD_PLANO = :cd_plano
	    AND CPF_CGC = :cpf_cgc");

$cd_plano = array('BASICO' => 1, 'SUPERIOR' => 2, 'EXECUTIVO' => 3, 'CLASSICO' => 4, 'ESSENCIAL' => 5, 'EXECUTIVO ESPECIAL' => 6, 'FAMILIA I' => 7, 'FAMILIA II' => 8, 'FAMILIA III' => 9);

$stmt->execute(array(':cd_plano' => isset($cd_plano[$plano]) ? $cd_plano[$plano] : 0,
										 ':cpf_cgc'  => $_SESSION['cpf_cnpj']));
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

$coberto = $dados['ACHOU'] === '0';

$stmt = $conn->prepare("
	SELECT   U.NR_CARTEIRA,
			 TO_CHAR(DT_INSCRICAO + NR_DIAS_CONSULTA, 'YYYY-MM-DD')  CONSULTA,
			 TO_CHAR(DT_INSCRICAO + NR_DIAS_EXAME, 'YYYY-MM-DD') EXAME,
			 TO_CHAR(DT_INSCRICAO + NR_DIAS_PROC, 'YYYY-MM-DD') PROC,
			 TO_CHAR(DT_INSCRICAO + NR_DIAS_INTERN_ENFM, 'YYYY-MM-DD') INTERN_ENFM,
			 TO_CHAR(DT_INSCRICAO + NR_DIAS_INTERN_QRTP, 'YYYY-MM-DD') INTERN_QRTP,
			 TO_CHAR(DT_INSCRICAO + NR_DIAS_OBST_ENFM, 'YYYY-MM-DD') OBST_ENFM,
			 TO_CHAR(DT_INSCRICAO + NR_DIAS_OBST_QRTP, 'YYYY-MM-DD') OBST_QRTP,
			 TO_CHAR(C.DT_VALIDADE,'YYYY-MM-DD') DT_VALIDADE
	  FROM IASM.IASM_USUARIO_CARENCIA U
	  LEFT JOIN IASM.IASM_CARTEIRA C ON C.NR_CARTEIRA = U.NR_CARTEIRA
	  WHERE U.NR_CARTEIRA LIKE :nr_carteira || '__' ");

$stmt->execute(array(':nr_carteira' => $carteira));
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (empty($dados)) {
	print '<div id="erro"><strong>Erro:</strong> Beneficiário não encontrado!</div>';
} else {
	function elegivel($data) {
		return strtotime($data) > strtotime('today') ? date('d/m/Y', strtotime($data)) : 'isento';
	}

?>
	<h3><?php print $nome ?></h3>

<?php if ($coberto) { ?>
	<dl class="dados">
		<dt>Carteira</dt>
		<dd><?php print $dados['NR_CARTEIRA'] ?></dd>

	<?php if (!is_null($dados['DT_VALIDADE'])) { ?>
		<dt>Data Validade</dt>
		<dd><?php print elegivel($dados['DT_VALIDADE']) ?></dd>
	<?php } ?>

		<dt>Plano</dt>
		<dd><?php print $plano ?></dd>

		<dt>Consulta</dt>
		<dd><?php print elegivel($dados['CONSULTA']) ?></dd>

		<dt>Exame</dt>
		<dd><?php print elegivel($dados['EXAME']) ?></dd>

		<dt>Proc. Ambulatorial</dt>
		<dd><?php print elegivel($dados['PROC']) ?></dd>

	<?php if (!is_null($dados['INTERN_ENFM'])) { ?>
		<dt>Internação Enferm.</dt>
		<dd><?php print elegivel($dados['INTERN_ENFM']) ?></dd>
	<?php } ?>

	<?php if (!is_null($dados['INTERN_QRTP'])) { ?>
		<dt>Internação Quarto</dt>
		<dd><?php print elegivel($dados['INTERN_QRTP']) ?></dd>
	<?php } ?>

	<?php if ($sexo === 'F') { ?>
	<?php if (!is_null($dados['OBST_ENFM'])) { ?>
		<dt>Obstetrícia Enferm.</dt>
		<dd><?php print elegivel($dados['OBST_ENFM']) ?></dd>
	<?php } ?>

	<?php if (!is_null($dados['OBST_QRTP'])) { ?>
		<dt>Obstetrícia Quarto</dt>
		<dd><?php print elegivel($dados['OBST_QRTP']) ?></dd>
	<?php } ?>
	<?php } ?>
	</dl>
<?php } else { ?>
	<div id="erro">Plano <?php print $plano ?> não coberto por prestador!</div>
<?php
	}
}
?>

</div>
