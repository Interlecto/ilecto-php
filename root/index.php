<?php
ini_set('display_errors', true);
error_reporting(E_ALL);

if(file_exists("site_def.php"))
	require_once "site_def.php";

$page = require "lib/page.php";
$page->go();
$page->close();
?>
