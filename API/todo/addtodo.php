<?php
header("Access-Control-Allow-Origin: http://192.168.76.68:3000"); // Változtasd meg a frontend URL-t, ha szükséges
header("Access-Control-Allow-Methods: *"); // Engedélyezett HTTP metódusok (pl. POST)
header("Access-Control-Allow-Headers: *"); // Engedélyezett fejlécek
header("Content-Type: application/json"); // Példa: JSON válasz küldése

require('../../inc/conn.php');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonData = file_get_contents("php://input");
    $data = json_decode($jsonData, true);

    $newtodotitle = $data['newTodo'];
    $userid = $data['userId'];
    $token = $data['token'];


    class Todo
    {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn)
        {
            $this->conn = $conn;
        }

        public function addTodo($newtodotitle, $userid, $token)
        {
            //GET condominium data
            try {
                $stmt = $this->conn->prepare(
                    "SELECT
                        u.id_condominiums as 'condominiumId'
                        from users u
                        LEFT JOIN user_login ul on ul.user_id = u.id
                        where ul.token = '$token'
                    "
                );
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($result) {
                    $condominiumId = $result[0]['condominiumId'];
                }

            } catch (Exception $e) {
                $error = $e->getMessage();
                echo json_encode($error);
            }
            try {
                $stmt = $this->conn->prepare(
                    "INSERT INTO todos
                    (title, id_condominiums, created_by)
                    VALUES (:title, :id_condominiums, :created_by);
                "
                );
                $stmt->bindParam(":title", $newtodotitle);
                $stmt->bindParam(":id_condominiums", $condominiumId);
                $stmt->bindParam(":created_by", $userid);
                $stmt->execute();
                $rowCount = $stmt->rowCount();

                $response = array(
                    "confirmAddTodo" => true
                );
                echo json_encode($response);

            } catch (Exception $e) {
                $error["error"] = "Hiba történt a művelet során: " . $e->getMessage();
                echo json_encode($error);
            }

        }
    }

    $addtodo = new Todo($conn);
    $token = $_POST['token'];
    $addtodo->addTodo($newtodotitle, $userid, $token);
}


?>