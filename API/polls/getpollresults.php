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

//$_SERVER['REQUEST_METHOD'] === 'POST'
if (true) {
    $jsonData = file_get_contents("php://input");
    $data = json_decode($jsonData, true);

    //$token = $_POST["token"];
    $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6Im1hcnRvbmphbm9zMTk5MEBnbWFpbC5jb20iLCJleHBpcmF0aW9uVGltZSI6MTY5OTI0OTUwNH0.MT9-oeVoRGSQeGM1iwLWyAwUz97eEThbEbXnZbQu_ys";


    class GetPollResults
    {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn)
        {
            $this->conn = $conn;
        }

        public function getData($token, $conn)
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
            try {
                //$dataToHandleInDb = array();

                $dataToHandleInDb = [
                    'table' => "polls_questions",
                    'method' => "get",
                    'columns' => ['question_id'],
                    'values' => [],
                    'conditions' => ['active' => 0]
                ];
                $data = dataToHandleInDb($this->conn, $dataToHandleInDb);
                echo json_encode($data);
            } catch (Exception $e) {
                $error = array(
                    "error" => $e->getMessage()
                );
                echo json_encode($error);
            }
        }
    }
    $getpollresults = new GetPollResults($conn);
    $getpollresults->getData($token, $conn);
}


?>