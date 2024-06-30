<?php
header("Access-Control-Allow-Origin: http://192.168.76.68:3000"); // Változtasd meg a frontend URL-t, ha szükséges
header("Access-Control-Allow-Methods: *"); // Engedélyezett HTTP metódusok (pl. POST)
header("Access-Control-Allow-Headers: *"); // Engedélyezett fejlécek
header("Content-Type: application/json"); // Példa: JSON válasz küldése

require ('../../functions/db/dbFunctions.php');
require ('../../inc/conn.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonData = file_get_contents("php://input");
    $data = json_decode($jsonData, true);

    $token = $_POST["token"];
    $id = $_POST["id"];
    $method = $_POST["method"];

    class UpdatePoll
    {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn)
        {
            $this->conn = $conn;
        }

        public function getData($token, $id, $method, $conn)
        {
            try {
                //GET condominium data
                $stmt = $this->conn->prepare(
                    "SELECT
                    u.id as 'userId',
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
                    $userId = $result[0]['userId'];
                }

            } catch (Exception $e) {
                $error = $e->getMessage();
                echo json_encode($error);
            }
            if ($userId) {
                switch ($method) {
                    case 'Close':
                        try {
                            $dataToHandleInDb = [
                                'table' => "polls_questions pq",
                                'method' => "update",
                                'columns' => ['active'],
                                'values' => [0],
                                'others' => "",
                                'order' => "",
                                'conditions' => ['question_id' => $id]
                            ];
                            $result = dataToHandleInDb($this->conn, $dataToHandleInDb);
                            if ($result['isUpdated']) {
                                $response['result'] = $result['message'];
                            } else {
                                $response['error'] = $result['error'];
                            }
                        } catch (Exception $e) {
                            $response['error'] = $result['error'];
                        }
                        echo json_encode($response);
                        break;
                    // case 'Delete':
                    //     echo "Ma kedd van.";
                    //     break;
                }
            }
        }
    }
    $updatepoll = new UpdatePoll($conn);
    $updatepoll->getData($token, $id, $method, $conn);
}
?>