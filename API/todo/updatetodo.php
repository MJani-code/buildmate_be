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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonData = file_get_contents("php://input");
    $data = json_decode($jsonData, true);

    $id = $data['id'];

    class UpdateTodo {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn) {
            $this->conn = $conn;
        }

        public function updateTodo($id) {
                try {
                    $stmt1 = $this->conn->prepare(
                        "UPDATE todos SET
                        status = 2
                        WHERE id = :id
                    "
                    );
                    $stmt1->bindParam(":id", $id);
                    $stmt1->execute();
                    $rowCount = $stmt1->rowCount();

                    if ($rowCount > 0) {
                        $response = array(
                            "confirmUpdateTodoData" => true
                        );
                        echo json_encode($response);
                    } else {
                        $error["error"] = "Hiba történt az adatok frissítése közben";
                        echo json_encode($error);
                    }

                }catch (Exception $e) {
                    $error = array(
                        "error" => $e->getMessage()
                    );
                    echo json_encode($error);
                }
        }
    }
    $updatetodo = new UpdateTodo($conn);
    $updatetodo->updateTodo($id);
}


?>