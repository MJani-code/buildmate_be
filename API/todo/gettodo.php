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

    $token = $data["token"];

    class GetTodoData {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn) {
            $this->conn = $conn;
        }

        public function getTodoData($token) {
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
                        "SELECT
                        id as 'id',
                        title as 'title',
                        status as 'status',
                        created_at as 'createdAt',
                        created_by as 'createdBy',
                        updated_at as 'updatedAt'
                        FROM todos
                        where id_condominiums = '$condominiumId'
                    ");
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if($result){
                        $response = $result;
                        echo json_encode($response);
                    }else{
                        $error["error"] = "Nem sikerült egy adatot sem betöltenünk!";
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
    $gettodo->getTodoData($token);
}


?>