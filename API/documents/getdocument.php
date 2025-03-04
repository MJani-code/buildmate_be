<?php
header("Access-Control-Allow-Origin: http://192.168.76.68:3000"); // Változtasd meg a frontend URL-t, ha szükséges
header("Access-Control-Allow-Methods: *"); // Engedélyezett HTTP metódusok (pl. POST)
header("Access-Control-Allow-Headers: *"); // Engedélyezett fejlécek
header("Content-Type: application/json"); // Példa: JSON válasz küldése

require_once('../../functions/getter/filegetter.php');
require_once('../../functions/interface/filegetterinterface.php');
require('../../inc/conn.php');


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


// ParentClass.php
class GetDocument
{
    private $filegetter;

    public function __construct($conn)
    {
        $this->filegetter = new FileGetter($conn);

    }
    public function handleGet($id, $token, $user_data)
    {
        $result = $this->filegetter->getFile($id, $token, $user_data);

    }
}


// Használat
$parent = new GetDocument($conn);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_data = $_SESSION["user_data"];
    $token = $_POST['token'];
    $id = $_GET['id'] ?? null;
    $parent->handleGet($id, $token, $user_data);
}