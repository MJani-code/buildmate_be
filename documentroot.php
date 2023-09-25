<?php
session_start();
$rootPath = __DIR__;


$_SESSION['docroot'] = $rootPath;

echo $_SESSION['docroot'];
?>
