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

    $token = $_POST["token"];
    //$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6Im1hcnRvbmphbm9zMTk5MEBnbWFpbC5jb20iLCJleHBpcmF0aW9uVGltZSI6MTcwMzgxOTIzOH0.ETphY4intQpdeVWcHTylIGNA4jDLmy5_6XZaSwF7Ca4';

    class GetPollsData
    {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn)
        {
            $this->conn = $conn;
        }

        public function getData($token)
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
                //Get those questionIds which userId has already voted for.
                $stmt = $this->conn->prepare(
                    "SELECT
                        pv.question_id as 'questionId'
                        FROM polls_votes pv
                        where pv.user_id = '$userId'
                    ");
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $alreadyVotedIds = array();

                foreach ($result as $row) {
                    $alreadyVotedIds[] = $row['questionId'];
                }

                if (!empty($alreadyVotedIds)) {
                    $stmt2 = $this->conn->prepare(
                        "SELECT
                            pq.question_id as 'questionId',
                            pq.question as 'question',
                            pq.multiple_choice as 'multiple',
                            po.option_id as 'optionId',
                            po.option_text as 'option',
                            pq.active as 'active',
                            pq.deadline as 'deadline'
                            FROM polls_questions pq
                            LEFT JOIN polls_votes pv on pv.question_id = pq.question_id
                            LEFT JOIN polls_options po on po.poll_id = pq.question_id
                            where pq.active = 1
                            AND pq.id_condominiums = '$condominiumId'
                            AND pq.question_id NOT IN (" . implode(',', $alreadyVotedIds) . ")
                            AND IF(TIMESTAMPDIFF(SECOND, NOW(), pq.deadline) <= 0, 0, 1) = 1
                            GROUP BY po.option_id
                            order by pq.question_id desc
                        ");
                } else {
                    $stmt2 = $this->conn->prepare(
                        "SELECT
                            pq.question_id as 'questionId',
                            pq.question as 'question',
                            pq.multiple_choice as 'multiple',
                            po.option_id as 'optionId',
                            po.option_text as 'option',
                            pq.active as 'active',
                            pq.deadline as 'deadline'
                            FROM polls_questions pq
                            LEFT JOIN polls_votes pv on pv.question_id = pq.question_id
                            LEFT JOIN polls_options po on po.poll_id = pq.question_id
                            where pq.active = 1
                            AND pq.id_condominiums = '$condominiumId'
                            AND IF(TIMESTAMPDIFF(SECOND, NOW(), pq.deadline) <= 0, 0, 1) = 1
                            GROUP BY po.option_id
                            order by pq.question_id desc
                        ");
                }

                $stmt2->execute();
                $result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                $response['result'] = array();

                if ($result2) {
                    $polls = array();
                    foreach ($result2 as $row) {
                        $questionId = $row['questionId'];
                        $optionId = $row['optionId'];
                        if (!isset($polls[$questionId])) {
                            $polls[$questionId] = array(
                                'active' => $row['active'],
                                'questionId' => $row['questionId'],
                                'questionText' => $row['question'],
                                'multiple' => $row['multiple'],
                                'deadline' => $row['deadline'],
                                'countdown' => null,
                                'options' => array() // Itt inicializálj egy üres felelősök tömböt
                            );
                        }
                        if (!isset($polls[$questionId]['options'][$optionId])) {
                            $polls[$questionId]['options'][] = array(
                                'id' => strval($row['optionId']),
                                'value' => $row['option'],
                                'checked' => false,
                                'disabled' => false,
                            );
                        }
                    }
                    // Az események hozzáadása a válaszhoz

                    $response['result'] = array_values($polls);

                    echo json_encode($response);
                    //print_r($response);
                } else {
                    $error["error"] = "Hiba történt az adatok lekérdezése közben";
                    echo json_encode($error);
                }
            } catch (Exception $e) {
                $error = array(
                    "error" => $e->getMessage()
                );
                echo json_encode($error);
            }
        }
    }
    $getpolls = new GetPollsData($conn);
    $getpolls->getData($token);
}


?>