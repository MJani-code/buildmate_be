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
            if($userId){
                try {
                    $dataToHandleInDb = [
                        'table' => "polls_votes pv",
                        'method' => "get",
                        'columns' => ['pv.option_id', 'po.option_text', 'pv.question_id', 'pq.question', 'COUNT(pv.option_id) AS count'],
                        'values' => [],
                        'others' => "
                            LEFT JOIN polls_options po on po.option_id = pv.option_id
                            LEFT JOIN polls_questions pq on pq.question_id = pv.question_id
                        ",
                        'order' =>"
                            GROUP BY pv.option_id
                            ORDER BY count DESC
                        ",
                        'conditions' => ['pq.deleted' => 0],
                        'conditionExtra' => "IF(TIMESTAMPDIFF(SECOND, NOW(), pq.deadline) <= 0, 0, 1) = 0"
                    ];
                    $result = dataToHandleInDb($this->conn, $dataToHandleInDb);

                    if($result){
                        $pollResults = array();
                        foreach ($result as $value) {
                            $questionId = $value['question_id'];
                            $optionText = $value['option_text'];
                            if (!isset($pollResults[$questionId])) {
                                $pollResults[$questionId] = array(
                                    'question' => $value['question'],
                                    'labels' => [],
                                    'data' => []
                                );
                            }
                            if (!in_array($optionText, $pollResults[$questionId]['labels'])) {
                                $pollResults[$questionId]['labels'][] = $optionText;
                                $pollResults[$questionId]['data'][] = intval($value['count']);
                            }
                        }
                        echo json_encode($pollResults, JSON_PRETTY_PRINT);

                    }else{
                        $error = array(
                            "error" => "Nincsen lezárt szavazás"
                        );
                    }
                } catch (Exception $e) {
                    $error = array(
                        "error" => $e->getMessage()
                    );
                    echo json_encode($error);
                }
            }
        }
    }
    $getpollresults = new GetPollResults($conn);
    $getpollresults->getData($token, $conn);
}


?>