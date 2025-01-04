<?php
header("Access-Control-Allow-Origin: *");

// id
$id = empty($_GET['id']) ? 0 : $_GET['id'];
if ($id == 0) {
  header("HTTP/1.1 400 Bad Request");
  echo "No id provided";
  exit;
}
$fname = __DIR__."/midi/$id.mid";
if (!file_exists($fname)) {
  header("HTTP/1.1 404 Not Found");
  echo "File not found";
  exit;
}

header("Content-Type: audio/midi");
header("Content-Disposition: attachment; filename=\"output.mid\"");
header("Content-Length: ".filesize($fname));
readfile($fname);


