<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ILM Test</title>
<link rel="stylesheet" href="/style/default/css/style.css" type="text/css" />
<link rel="stylesheet" href="/style/default/css/layout.css" type="text/css" />
<link rel="icon" href="/images/interlecto.ico">
</head>
<?php
ini_set('display_errors', true);
error_reporting(E_ALL);
$ilm = file_get_contents('mod/base/sample2.ilm');
require_once "lib/ilm.php";
require_once "lib/tidy.php";

$html = ILM::tohtml($ilm);
echo tidy::go($html,5);

?>
</html>
