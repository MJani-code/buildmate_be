<?php
header("Access-Control-Allow-Origin: http://192.168.76.68:3000"); // Változtasd meg a frontend URL-t, ha szükséges
//header("Access-Control-Allow-Origin: *"); // Változtasd meg a frontend URL-t, ha szükséges
header("Access-Control-Allow-Methods: *"); // Engedélyezett HTTP metódusok (pl. POST)
header("Access-Control-Allow-Headers: *"); // Engedélyezett fejlécek
header("Content-Type: application/json"); // Példa: JSON válasz küldése

require('../../functions/db/dbFunctions.php');
require('../../functions/token/validator.php');
require('../../inc/conn.php');

$response = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jsonData = file_get_contents("php://input");
    $data = json_decode($jsonData, true);

    $tokenCheck = checkBearerToken();
    $token = $tokenCheck['token'];

    //Checking Token and its format
    if ($tokenCheck['status'] != 200) {
        $response = $tokenCheck;
        return $response;
    }

    class GetPollResults
    {
        private $conn;
        private $response;

        public function __construct($conn, &$response)
        {
            $this->conn = $conn;
            $this->response = &$response;
        }

        public function getData($token, &$response)
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
                } else {
                    $this->response = array(
                        'status' => 404,
                        'errorInfo' => "Hiányzó vagy lejárt token!"
                    );
                    return;
                }

            } catch (Exception $e) {
                $errorInfo = $e->getMessage();
                $this->response = array(
                    'status' => 500,
                    'errorInfo' => $errorInfo
                );
                return;
            }
            if ($userId) {
                try {
                    $dataToHandleInDb = [
                        'table' => "polls_votes pv",
                        'method' => "get",
                        'cte' => "
                            valid_votes AS (
                                SELECT pv.option_id,
                                    pv.question_id,
                                    u.id AS user_id,
                                    u.stair_case_flat,
                                    u.ownership_share
                                FROM polls_votes pv
                                JOIN users u ON u.id = pv.user_id
                                GROUP BY pv.option_id, u.stair_case_flat)
                        ",
                        'columns' => ['pv.option_id',
                            'po.option_text',
                            'pv.question_id',
                            'pq.question',
                            'SUM(u.ownership_share) AS total_ownership_share'],
                        'values' => [],
                        'others' => "
                            JOIN users u ON u.id = pv.user_id
                            JOIN polls_options po ON po.option_id = pv.option_id
                            JOIN polls_questions pq ON pq.question_id = pv.question_id
                            JOIN valid_votes vv ON vv.option_id = pv.option_id AND vv.user_id = pv.user_id
                        ",
                        'order' => "
                            GROUP BY pv.option_id, pv.question_id, po.option_text, pq.question
                            ORDER BY total_ownership_share DESC
                        ",
                        'conditions' => ['pq.deleted' => 0],
                        'conditionExtra' => "IF(TIMESTAMPDIFF(SECOND, NOW(), pq.deadline) <= 0, 0, 1) = 0"
                    ];
                    $result = dataToHandleInDb($this->conn, $dataToHandleInDb);

                    if ($result['payload']) {
                        $this->response = array();
                        foreach ($result['payload'] as $value) {
                            $questionId = $value['question_id'];
                            $optionText = $value['option_text'];
                            if (!isset($this->response[$questionId])) {
                                $this->response[$questionId] = array(
                                    'question' => $value['question'],
                                    'labels' => [],
                                    'data' => []
                                );
                            }
                            if (!in_array($optionText, $this->response[$questionId]['labels'])) {
                                $this->response[$questionId]['labels'][] = $optionText;
                                //$this->response[$questionId]['data'][] = intval($value['count']);
                                $this->response[$questionId]['data'][] = number_format($value['total_ownership_share'], 2);
                            }
                        }
                        return;

                    } else {
                        $this->response = array(
                            'status' => 500,
                            'errorInfo' => 'Nincsen lezárt szavazás'
                        );
                        return;
                    }
                } catch (Exception $e) {
                    $errorInfo = $e->getMessage();
                    $this->response = array(
                        'status' => 500,
                        'errorInfo' => $errorInfo
                    );
                    return;
                }
            }
        }
    }
    $getpollresults = new GetPollResults($conn, $response);
    $getpollresults->getData($token, $conn);

    echo json_encode($response);
}


?>