<?php

require_once dirname(__FILE__) .'/config.php';

if (!isset($_SESSION['logado'])) exit;

?>

<div class="resultado">

<?php

if (isset($_POST['senha'])) {
	if (strlen(trim($_POST['senha'])) == 0) {
		print '<div id="erro">Você precisa informar uma senha!</div>';
	} else if ($_POST['senha'] == $_POST['senha_confirma']) {
		print '<div id="sucesso">Senha alterada com sucesso!</div>';

		$conn->beginTransaction();
		$stmt = $conn->prepare('UPDATE TISS_USUARIO SET SENHA = :senha WHERE CPF_CNPJ = :cpf_cnpj AND ROWNUM = 1');
		$stmt->bindValue(':cpf_cnpj', $_SESSION['cpf_cnpj']);
		$stmt->bindValue(':senha', strtoupper(md5($_POST['senha'])));
		$stmt->execute();
		$conn->commit();
	} else {
		print '<div id="erro"><strong>Erro:</strong> A senha de confirmação não confere</div>';
	}
}
?>
</div>

<form id="login" method="post" action="?pagina=mudar_senha" class="form1">
	Nova senha: <input type="password" name="senha" /><br/>
	Confirmar senha: <input type="password" name="senha_confirma" /><br/>
	<br/>
	<input type="submit" name="enviar" value=" OK " />
</form>
