<?php
ini_set('display_errors', true);
error_reporting(E_ALL);

if(file_exists("config/site.php"))
	require_once "config/site.php";

$page = require "lib/page.php";
$page->go();
$page->close();
?>
