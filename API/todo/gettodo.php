<?php
header("Access-Control-Allow-Origin: http://192.168.76.68:3000"); // Változtasd meg a frontend URL-t, ha szükséges
header("Access-Control-Allow-Methods: *"); // Engedélyezett HTTP metódusok (pl. POST)
header("Access-Control-Allow-Headers: *"); // Engedélyezett fejlécek
header("Content-Type: application/json"); // Példa: JSON válasz küldése

require('../../inc/conn.php');
//require('scripts.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $jsonData = file_get_contents("php://input");
    $data = json_decode($jsonData, true);

    class GetTodoData {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn) {
            $this->conn = $conn;
        }

        public function getTodoData() {
                try {
                    $stmt = $this->conn->prepare(
                        "SELECT * FROM todos
                    ");
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if($result){
                        $response = $result;
                        echo json_encode($response);
                    }else{
                        $error["error"] = "Hiba történt az adatok lekérdezése közben";
                        echo json_encode($error);
                    }
                }catch (Exception $e) {
                    $error = array(
                        "error" => $e->getMessage()
                    );
                    //$error["error"] = "Hiba történt az adatok lekérdezése közben: " . $e->getMessage();
                    echo json_encode($error);
                }
        }
    }
    $gettodo = new GetTodoData($conn);
    $gettodo->getTodoData();
}


?>