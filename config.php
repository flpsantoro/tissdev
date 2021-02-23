<?php

define('HOST', '157.86.206.5');
define('USER', 'tiss');
define('PASS', 'saudeconnect');

$conn = new PDO('oci:dbname=//'. HOST .':1521/xe;charset=UTF8', USER, PASS);
