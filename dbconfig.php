<?php

define('HOST', '157.86.206.5');
//define('USER', 'tisshom');
//define('PASS', 'tisshom');
define('USER', 'tiss');
define('PASS', 'saudeconnect');

try
{
     $db_con = new PDO('oci:dbname=//'. HOST .':1521/xe;charset=UTF8', USER, PASS);
     //$db_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $exception)
{
     echo $exception->getMessage();
}

$totalCount = 0;
include_once 'class.paging.php';
$paginate = new paginate($db_con);
?>