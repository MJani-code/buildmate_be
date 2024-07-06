<?php
header("Access-Control-Allow-Origin: http://192.168.76.68:3000"); // Változtasd meg a frontend URL-t, ha szükséges
header("Access-Control-Allow-Methods: *"); // Engedélyezett HTTP metódusok (pl. POST)
header("Access-Control-Allow-Headers: *"); // Engedélyezett fejlécek
header("Content-Type: application/json"); // Példa: JSON válasz küldése

require ('../../inc/conn.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonData = file_get_contents("php://input");
    $data = json_decode($jsonData, true);

    $token = $_POST["token"];
    //$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6ImJqb2tlc3pAZ21haWwuY29tIiwiZXhwaXJhdGlvblRpbWUiOjE3MjAyODgzNjJ9.f-uU1Ajm13NqpC65uKUOWBCL1G8o_z-mGpfkcfrqQJk";


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
                $stmt = $this->conn->prepare(
                    "SELECT
                            pq.question_id as 'questionId',
                            pq.question as 'question',
                            pq.multiple_choice as 'multiple',
                            po.option_id as 'optionId',
                            po.option_text as 'option',
                            pq.active as 'active',
                            if((SELECT vote_id from polls_votes where option_id = po.option_id AND user_id = '$userId' limit 1) is NULL,0,1 ) as 'isOptionVoted',
                            if((SELECT vote_id from polls_votes where user_id = '$userId' AND question_id = pq.question_id limit 1) is NULL,0,1) as 'isQuestionVoted',
                            pq.deadline as 'deadline',
                            pq.created_by as 'createdBy'
                            FROM polls_questions pq
                            LEFT JOIN polls_votes pv on pv.question_id = pq.question_id
                            LEFT JOIN polls_options po on po.poll_id = pq.question_id
                            where pq.active = 1 AND pq.deleted = 0
                            AND pq.id_condominiums = '$condominiumId'
                            AND IF(TIMESTAMPDIFF(SECOND, NOW(), pq.deadline) <= 0, 0, 1) = 1
                            GROUP BY po.option_id
                            order by pq.question_id desc;
                        "
                );
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response['result'] = array();

                if ($result) {
                    $polls = array();
                    foreach ($result as $row) {
                        $questionId = $row['questionId'];
                        $optionId = $row['optionId'];
                        if (!isset($polls[$questionId])) {
                            $polls[$questionId] = array(
                                'active' => intval($row['active']),
                                'questionId' => intval($row['questionId']),
                                'questionText' => $row['question'],
                                'multiple' => intval($row['multiple']),
                                'deadline' => $row['deadline'],
                                'createdBy' => intval($row['createdBy']),
                                'countdown' => null,
                                'options' => array()
                            );
                        }
                        if (!isset($polls[$questionId]['options'][$optionId])) {
                            $isDisabled = null;

                            if($row['multiple']){
                                $isDisabled = false;
                            }else{
                                if(!$row['isQuestionVoted']){
                                    $isDisabled = false;
                                }else{
                                    if(!$row['isOptionVoted']){
                                        $isDisabled = true;
                                    }else{
                                        $isDisabled = false;
                                    }
                                }
                            }
                            $polls[$questionId]['options'][] = array(
                                'id' => strval($row['optionId']),
                                'value' => $row['option'],
                                'checked' => intval($row['isOptionVoted']),
                                'disabled' => $isDisabled,
                            );
                        }
                    }
                    $response['result'] = array_values($polls);
                    echo json_encode($response);
                } else {
                    $response["error"] = "Neked nincsen nyitott szavazásod";
                    echo json_encode($response);
                }
            } catch (Exception $e) {
                $response["error"] = $e->getMessage();
                echo json_encode($response);
            }
        }
    }
    $getpolls = new GetPollsData($conn);
    $getpolls->getData($token);
}


?>