<?php
header("Access-Control-Allow-Origin: http://192.168.76.68:3000"); // Változtasd meg a frontend URL-t, ha szükséges
header("Access-Control-Allow-Methods: *"); // Engedélyezett HTTP metódusok (pl. POST)
header("Access-Control-Allow-Headers: *"); // Engedélyezett fejlécek
header("Content-Type: application/json"); // Példa: JSON válasz küldése

require('../../inc/conn.php');
require('../../functions/getter/getmaxid.php');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonData = file_get_contents("php://input");
    $polls = json_decode($jsonData, true);
    $count = count($polls);

    $token = $polls[0]['token'];
    $condominiumId = $polls[0]['condominiumId'];


    class Poll
    {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn)
        {
            $this->conn = $conn;
        }

        public function addPoll($token, $polls, $count, $condominiumId)
        {
            //GET condominium data
            try {
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
                    $userId = $result[0]['userId'];
                    foreach ($polls as $key => $value) {
                        $question = $value['question'];
                        $multiple = $value['multiple'];
                        $deadline = $value['deadline'] ?? null;
                        $active = 1;

                        $stmt2 = $this->conn->prepare(
                            "INSERT INTO polls_questions
                            (id_condominiums, active, multiple_choice, question, created_by, deadline)
                            VALUES (:id_condominiums, :active, :multiple_choice, :question, :created_by, :deadline);
                        "
                        );
                        $stmt2->bindParam(":id_condominiums", $condominiumId);
                        $stmt2->bindParam(":active", $active);
                        $stmt2->bindParam(":multiple_choice", $multiple);
                        $stmt2->bindParam(":question", $question);
                        $stmt2->bindParam(":created_by", $userId);
                        $stmt2->bindParam(":deadline", $deadline);
                        $stmt2->execute();
                        $rowCount2 = $stmt2->rowCount();

                        $questionId = getMaxId($this->conn, 'polls_questions', 'question_id');

                        if (is_array($value['choices'])) {
                            foreach ($value['choices'] as $key => $element) {
                                $option = $element;
                                $stmt3 = $this->conn->prepare(
                                    "INSERT INTO polls_options
                                    (poll_id, option_text)
                                    VALUES (:poll_id, :option_text);
                                "
                                );
                                $stmt3->bindParam(":poll_id", $questionId);
                                $stmt3->bindParam(":option_text", $option);
                                $stmt3->execute();
                                $rowCount3 = $stmt3->rowCount();

                            }
                        }
                    }
                    if ($rowCount2 && $rowCount3) {
                        $response = array(
                            "confirmAddPolls" => true
                        );
                        echo json_encode($response);
                    }
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
                echo json_encode($error);
            }

        }
    }

    $createpolls = new Poll($conn);
    $createpolls->addPoll($token, $polls, $count, $condominiumId);
}


?>