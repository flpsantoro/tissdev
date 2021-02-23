<?php

require_once 'libs/arquivoxml.class.php';

define( 'TESTING', 0);

define( 'RETURN_DIR', './return/' );
define( 'XML_DIR', './data/' );
define( 'BACKUP_DIR', './backup/' );
//SSH
#define( 'RETURN_DIR', 'ssh_data/transmissao/' );
#define( 'XML_DIR', 'ssh_data/recepcao/' );
#define( 'BACKUP_DIR', 'ssh_data/backup/' );


function prepare_bkp_directory($dir) {
  $dirs = split('/', $dir);
  $path = '';
  foreach ($dirs as $d) {
    $path .= $d .'/';
    if (!file_exists($path)) {
      mkdir($path);
    }
  }
}

function clean_data_directory ($dir) {
  $d = dir( $dir );
  $has_file = false;
  while ( FALSE !== ( $entry = $d->read() ) ) {
    if ( $entry == '.' || $entry == '..' ) continue;
    $entry = $dir . '/' . $entry;
    if ( is_dir($entry) ) {
      if ( !clean_data_directory($entry) ) return FALSE;
      continue;
    }
    $has_file = true;
  }

  $d->close();

  if (!$has_file) {
    @rmdir($dir);
  }

  return TRUE;
}

//$db = oci_connect( 'asm', 'teste', "//10.2.0.2:1521/des9" );
$db = oci_connect( 'asm', 'teste', "//10.2.0.2:1521/des" );

$command = "find ".XML_DIR." -iname '*.xml'";
$rs = `$command`;
$files = preg_split('/\r?\n/', trim($rs));

foreach ($files as $file) {
  if (empty($file)) continue;

  $file_date = filectime($file);
  print "*** $file\n";

  try {
    $obj = new ArquivoXML($file);
    $obj->processa();

    $dir = BACKUP_DIR . date('Y/m/d/') . $obj->CPF_CGC;
    prepare_bkp_directory($dir);

    $dest = $dir . '/' . basename($file);

    print "  - backuping... ($dest)\n";

    if (!TESTING) { rename($file, $dest); }
  }
  catch ( Exception $e ) {
    trigger_error($e->getMessage(), E_USER_WARNING);
  }

  unset($obj);
}

file_put_contents(XML_DIR . '.locker', "prevent directory remove.");
clean_data_directory(dirname(XML_DIR . 'tiss'));

?>
