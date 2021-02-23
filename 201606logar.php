<?php

session_start();

require dirname(__FILE__) .'/config.php';

$stmt = $conn->prepare("
	SELECT CPF_CNPJ,
	       NM_USUARIO,
	       TP_USUARIO,
	       CNES
	  FROM TISS_USUARIO
	  WHERE LOGIN = :login
		AND (SENHA = :senha OR :senha = :senha_master)
		AND ID_STATUS = 'A'
		AND ROWNUM = 1");

$login = trim(ltrim($_POST['cpf_cnpj'], '0'));
$senha = strtoupper(md5($_POST['senha']));
$senha_master = strtoupper(md5('fiosaude%tiss'));

$stmt->bindParam(':login', $login);
$stmt->bindParam(':senha', $senha);
$stmt->bindParam(':senha_master', $senha_master);

if ($stmt->execute()) {
	if ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$_SESSION['logado']     = true;
		$_SESSION['nm_usuario'] = $data['NM_USUARIO'];
		$_SESSION['login']      = $login;
		$_SESSION['cpf_cnpj']   = $data['CPF_CNPJ'];
		$_SESSION['tp_usuario'] = intval($data['TP_USUARIO']);
		$_SESSION['cnes']       = $data['CNES'];
		$_SESSION['master']     = $senha == $senha_master;

		// Log de acesso
		$conn->beginTransaction();

		$stmt = $conn->prepare('UPDATE TISS_USUARIO SET DT_ULT_LOGIN = SYSDATE WHERE CPF_CNPJ = :cpf_cnpj AND ROWNUM = 1');
		$stmt->execute(array(':cpf_cnpj' => $data['CPF_CNPJ']));

		$stmt = $conn->prepare('INSERT INTO TISS_ACESSO_LOG (CPF_CNPJ, USER_AGENT, DT_ACESSO) VALUES (:cpf_cnpj, :user_agent, SYSDATE)');
		$stmt->execute(array(':cpf_cnpj'    => $data['CPF_CNPJ'], ':user_agent'  => substr($_SERVER['HTTP_USER_AGENT'], 0, 150)));

		$conn->commit();
	}
}

header('location: index.php');
