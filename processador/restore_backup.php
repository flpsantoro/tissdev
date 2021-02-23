<?php

die('edit source to continue...');

$command = "find ../backup -iname '*.xml'";
$rs = `$command`;
$files = preg_split('/\r?\n/', trim($rs));

foreach ($files as $file) {
  $dest = basename($file);

  if (file_exists($dest)) continue;
  copy($file, $dest);
}

?>
