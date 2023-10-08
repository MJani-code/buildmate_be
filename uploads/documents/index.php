<?php
// Cél URL, ahova át szeretnénk irányítani a felhasználót
session_start();

$celUrl = 'http://'.$_SERVER['HTTP_HOST']; // Itt cseréld le a saját cél URL-re

// Irányítás a cél URL-re
header("Location: http://localhost:5000");

// Azonnali átirányítás befejezése
exit();
?>