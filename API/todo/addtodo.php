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

    $newtodotitle = $data['newTodo'];
    $userid = $data['userId'];

    class Todo {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn) {
            $this->conn = $conn;
        }

        public function addTodo($newtodotitle, $userid) {

            try {
                $stmt = $this->conn->prepare(
                    "INSERT INTO todos
                    (title, created_by)
                    VALUES (:title, :created_by);
                ");
                $stmt->bindParam(":title", $newtodotitle);
                $stmt->bindParam(":created_by", $userid);
                $stmt->execute();
                $rowCount = $stmt->rowCount();

                $response = array(
                    "confirmAddTodo" => true
                );
                echo json_encode($response);

            }catch (Exception $e) {
                $error["error"] = "Hiba történt a művelet során: " . $e->getMessage();
                echo json_encode($error);
            }

        }
    }

    $addtodo = new Todo($conn);
    $addtodo->addTodo($newtodotitle, $userid);
}


?>