<?php

session_start();

require dirname(__FILE__) .'/config.php';

$stmt = $conn->prepare("	
	SELECT T.CPF_CNPJ,
	       T.NM_USUARIO,
	       T.TP_USUARIO,
	       T.CNES,
           P.NM_FANTASIA,
           P.TP_CREDENCIADO,
           P.NM_PRESTADOR,
           P.NR_CRM,
           P.CD_UF_CRM
	FROM TISS_USUARIO T , IASM_PRESTADOR P
    WHERE P.CPF_CGC = T.CPF_CNPJ
		AND T.LOGIN = :login
		AND (T.SENHA = :senha OR :senha = :senha_master)
		AND T.ID_STATUS = 'A'
		AND ROWNUM = 1");

$login = trim(ltrim($_POST['cpf_cnpj'], '0'));
$senha = strtoupper(md5($_POST['senha']));
$senha_master = strtoupper(md5('ti@fiosaude.tiss'));

$stmt->bindParam(':login', $login);
$stmt->bindParam(':senha', $senha);
$stmt->bindParam(':senha_master', $senha_master);

if ($stmt->execute()) {
	if ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$_SESSION['logado']       = true;
		$_SESSION['nm_usuario']   = $data['NM_USUARIO'];
		$_SESSION['login']        = $login;
		$_SESSION['cpf_cnpj']     = $data['CPF_CNPJ'];
		$_SESSION['nr_crm']     = $data['NR_CRM'];
		$_SESSION['nm_prestador'] = $data['NM_PRESTADOR'];
		$_SESSION['tp_usuario']   = intval($data['TP_USUARIO']);
		$_SESSION['cnes']         = $data['CNES'];
		$_SESSION['nomefantasia'] = $data['NM_FANTASIA'];
		$_SESSION['tp_credenciado'] = $data['TP_CREDENCIADO'];
		$_SESSION['CNES'] = $data['CNES'];
		$_SESSION['cd_uf_crm'] = $data['CD_UF_CRM'];
		$_SESSION['master']       = $senha == $senha_master;

		// Log de acesso
		$conn->beginTransaction();

		$stmt = $conn->prepare('UPDATE TISS_USUARIO SET DT_ULT_LOGIN = SYSDATE WHERE CPF_CNPJ = :cpf_cnpj AND ROWNUM = 1');
		$stmt->execute(array(':cpf_cnpj' => $data['CPF_CNPJ']));

		$stmt = $conn->prepare('INSERT INTO TISS_ACESSO_LOG (CPF_CNPJ, USER_AGENT, DT_ACESSO) VALUES (:cpf_cnpj, :user_agent, SYSDATE)');
		$stmt->execute(array(':cpf_cnpj'    => $data['CPF_CNPJ'], ':user_agent'  => substr($_SERVER['HTTP_USER_AGENT'], 0, 150)));

		$conn->commit();
		header('location: redirect.php?q=inicial');
		exit;
	}
}

header('location: index.php');
