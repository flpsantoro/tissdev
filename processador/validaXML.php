<?php

require_once 'libs/arquivoxml.class.php';

$file = $argv[1];

if (!file_exists($file)) {
  trigger_error('File not found! '.$file, E_USER_ERROR);
}

try {
  $obj = new ArquivoXML($file);
  $obj->schemaValidate();
  $obj->hashValidate();

  print "Arquivo Válido!\n";
}
catch (Exception $e) {
  //var_dump($e);
}

?>
