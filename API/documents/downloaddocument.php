<?php
header("Access-Control-Allow-Origin: http://192.168.76.68:3000"); // Változtasd meg a frontend URL-t, ha szükséges
header("Access-Control-Allow-Methods: *"); // Engedélyezett HTTP metódusok (pl. POST)
header("Access-Control-Allow-Headers: *"); // Engedélyezett fejlécek
header("Content-Type: application/json"); // Példa: JSON válasz küldése

require('../../inc/conn.php');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (true) {
    $jsonData = file_get_contents("php://input");
    $data = json_decode($jsonData, true);

    $type = $_GET['type'];
    $filename = $_GET['filename'];
    $path = '/Applications/XAMPP/xamppfiles/htdocs/THFustike3/build_mate_be/uploads/'.$type.'/'.$filename;

    class DownloadDocument
    {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn)
        {
            $this->conn = $conn;
        }

        public function Download($type, $filename, $path)
        {

            try {
                // Ellenőrizd, hogy a fájl létezik-e
                if (file_exists($path)) {
                    // Beállítjuk a HTTP fejlécet a fájl típusára és letöltésre
                    header('Content-type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . basename($path) . '"');
                    header('Content-Length: ' . filesize($path));

                    // Fájl olvasása és kimenet a válaszba
                    readfile($path);
                } else {
                    echo "A dokumentum nem található.";
                }
            } catch (Exception $e) {
                $error = array(
                    "error" => $e->getMessage()
                );
                echo json_encode($error);
            }
        }
    }

    $downloaddocument = new DownloadDocument($conn);
    $downloaddocument->Download($type, $filename, $path);
}
