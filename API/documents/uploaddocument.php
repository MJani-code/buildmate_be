<?php
header("Access-Control-Allow-Origin: http://192.168.76.68:3000"); // Változtasd meg a frontend URL-t, ha szükséges
header("Access-Control-Allow-Methods: *"); // Engedélyezett HTTP metódusok (pl. POST)
header("Access-Control-Allow-Headers: *"); // Engedélyezett fejlécek
header("Content-Type: application/json"); // Példa: JSON válasz küldése

require_once('../../functions/uploader/fileuploader.php');
require_once('../../functions/interface/fileuploaderinterface.php');
require('../../inc/conn.php');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// ParentClass.php
class ParentClass
{
    private $fileUploader;
    private $DOC_PATH;
    private $DOC_URL;
    public function __construct($conn, $DOC_URL, $DOC_PATH)
    {
        $this->fileUploader = new FileUploader($conn, DOC_URL, DOC_PATH);

    }

    public function handleUpload($file, $title, $type, $type_id, $userid, $createdby)
    {
        $result = $this->fileUploader->uploadFile($file, $title, $type, $type_id, $userid, $createdby);

    }
}

// Használat
$parent = new ParentClass($conn, DOC_URL, DOC_PATH);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $file = $_FILES['filelist_0'];
    $title = $_POST['title'];
    $type = $_POST['type'];
    $type_id = $_POST['type_id'];
    $userid = $_POST['userId'];
    $createdby = $_POST['createdBy'];

    $parent->handleUpload($file, $title, $type, $type_id, $userid, $createdby);
}