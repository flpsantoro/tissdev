<?php if (!isset($_SESSION['logado'])) return; ?>

<link href="js/ui-lightness/jquery-ui-1.10.4.custom.css" rel="stylesheet">
<script src="js/jquery-1.10.2.js"></script>
<script src="js/jquery-ui-1.10.4.custom.js"></script>

<script>
$(function() {
	$( "#nome_civil" ).autocomplete({ source: 'benefs.php', minLength: 2 });
	$( "#nome_socioafetivo" ).autocomplete({ source: 'benefs_socioafetivo.php', minLength: 2 });
});
</script>

<h3>Elegibilidade</h3>
<p>
<div align = "center">
  <form method="post" class="form1">    
	Carteira: <input type="text" name="nr_carteira" size="20"  />
	CPF: <input type="text" name="cpf" size="20" /><br>	
	Nome Social*/ Afetivo*(caso exista): <input type="text" id="nome_socioafetivo" name="nome_socioafetivo" size="40" /> </br>
	Nome civil***: <input type="text" id="nome_civil" name="nome_civil" size="40" />
	<input type="submit" value="Consultar" />
  
  <p align="left">* Nome social é a forma pela qual pessoas transexuais e travestis preferem ser chamadas cotidianamente.</p>
  <p align="left">** Nome afetivo é usado por pessoas em processo de adoção (identificação escolhida pelas famílias adotantes) antes da adoção definitiva.</p>
  <p align="left">*** Nome civil é o que todas as pessoas possuem em seu registro em certidão (feito na ocasião do nascimento ou do casamento).</p>	
	
  </form>
</div>  
</p>

<?php
if (!isset($_POST['nr_carteira'])) return;
?>

<div id="elegibilidade">

<?php
require_once 'config.php';

if (!empty($_POST['nome_civil'])) {	
	$stmt = $conn->prepare('
		SELECT NM_USUARIO, NVL(NOME_SOCIAL, NVL(NOME_AFETIVO,NM_USUARIO)) NOME_SOCIOAFETIVO, NR_MATRICULA, SEXO, NM_PLANO PLANO FROM IASM.IASM_USUARIO WHERE NM_USUARIO = :nome_civil AND DT_EXCLUSAO IS NULL');

	$stmt->execute(array(':nome_civil' => $_POST['nome_civil']));
	
} else if (!empty($_POST['nome_socioafetivo'])) {	
	$stmt = $conn->prepare('
		SELECT NM_USUARIO, NVL(NOME_SOCIAL, NVL(NOME_AFETIVO,NM_USUARIO)) NOME_SOCIOAFETIVO, NR_MATRICULA, SEXO, NM_PLANO PLANO FROM IASM.IASM_USUARIO WHERE ((NOME_SOCIAL= :nome_socioafetivo) or (NOME_AFETIVO = :NOME_SOCIOAFETIVO)) AND DT_EXCLUSAO IS NULL');

	$stmt->execute(array(':nome_socioafetivo' => $_POST['nome_socioafetivo']));	
	
} else if (!empty($_POST['cpf'])) {
	$stmt = $conn->prepare('
		SELECT NM_USUARIO, NVL(NOME_SOCIAL, NVL(NOME_AFETIVO,NM_USUARIO)) NOME_SOCIOAFETIVO, NR_MATRICULA, SEXO, NM_PLANO PLANO FROM IASM.IASM_USUARIO WHERE CPF = :cpf AND DT_EXCLUSAO IS NULL');

	$stmt->execute(array(':cpf' => $_POST['cpf']));
} else {
	$stmt = $conn->prepare('
		SELECT NM_USUARIO, NVL(NOME_SOCIAL, NVL(NOME_AFETIVO,NM_USUARIO)) NOME_SOCIOAFETIVO, NR_MATRICULA, SEXO, NM_PLANO PLANO FROM IASM.IASM_USUARIO WHERE NR_MATRICULA = :matricula');

	$matricula = substr($_POST['nr_carteira'], 0, -2);

	$stmt->execute(array(':matricula' => $matricula));
}

$dados    = $stmt->fetch(PDO::FETCH_ASSOC);
$nome     = empty($dados) ? null : $dados['NM_USUARIO'];
$nome_socioafetivo = empty($dados) ? null : $dados['NOME_SOCIOAFETIVO'];
$carteira = empty($dados) ? null : $dados['NR_MATRICULA'];
$sexo     = empty($dados) ? null : $dados['SEXO'];
$plano    = empty($dados) ? null : $dados['PLANO'];

$stmt = $conn->prepare("
	SELECT COUNT(*) ACHOU
	  FROM IASM.IASM_LIVRO_CRED_EXC
	  WHERE CD_PLANO = :cd_plano
	    AND CPF_CGC = :cpf_cgc");

$cd_plano = array('BASICO' => 1, 'SUPERIOR' => 2, 'EXECUTIVO' => 3, 'CLASSICO' => 5, 'ESSENCIAL' => 4, 'EXECUTIVO ESPECIAL' => 6, 'FAMILIA I' => 7, 'FAMILIA II' => 8, 'FAMILIA III' => 9);

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
	<?php if (!is_null($nome_socioafetivo)) { ?>
		<h3><?php print $nome_socioafetivo ?></h3>
	<?php } else { ?>
		<h3><?php print $nome ?></h3>
	<?php } ?>

<?php if ($coberto) { ?>
	<dl class="dados">
		<dt>Carteira</dt>
		<dd><?php print $dados['NR_CARTEIRA'] ?></dd>		
		
	<?php if (!is_null($dados['DT_VALIDADE'])) { ?>
		<dt>Data Validade</dt>
		<dd><?php print elegivel($dados['DT_VALIDADE']) ?></dd>
		<?php /*<dd><?php print date('d/m/Y', strtotime($dados['DT_VALIDADE'])) ?></dd>	*/ ?>				
	<?php } ?>
	
	<?php if (!is_null($nome_socioafetivo)) { ?>
		<dt>Nome Civil</dt>
		<dd><?php print $nome ?></dd>
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
