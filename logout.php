<?php

session_start();
/*
require dirname(__FILE__) . '/config.php';

$stmt = $conn->prepare('
	UPDATE TISS_ACESSO_LOG
	   SET HORA_FIM = SYSDATE
	   WHERE CD_MEDICO = :cd_medico
	     AND HORA_INI = (
	     SELECT MAX(HORA_INI)
	       FROM CST_ACESSO_LOG
	       WHERE CD_MEDICO = :cd_medico
	         AND IP = :ip
	         AND USER_AGENT = :user_agent
	     )
	     AND ROWNUM = 1');

$stmt->execute(array(':cd_medico' 	=> $_SESSION['cd_medico'],
					 ':ip'			=> $_SERVER['REMOTE_ADDR'],
					 ':user_agent'	=> $_SERVER['HTTP_USER_AGENT']));

$conn->exec('COMMIT');
*/

session_unset();
session_destroy();

header('location: index.php');
