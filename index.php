<?php
// Cél URL, ahova át szeretnénk irányítani a felhasználót
session_start();

$celUrl = 'https://'.$_SERVER['HTTP_HOST']; // Itt cseréld le a saját cél URL-re

// Irányítás a cél URL-re
//header("Location: $celUrl");

echo $celUrl;
// Azonnali átirányítás befejezése
exit();
?>