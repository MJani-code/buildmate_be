<?php
header("Access-Control-Allow-Origin: http://192.168.76.68:3000"); // Változtasd meg a frontend URL-t, ha szükséges
header("Access-Control-Allow-Methods: *"); // Engedélyezett HTTP metódusok (pl. POST)
header("Access-Control-Allow-Headers: *"); // Engedélyezett fejlécek
header("Content-Type: application/json"); // Példa: JSON válasz küldése

require ('../../inc/conn.php');
require ('../../functions/db/dbFunctions.php');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonData = file_get_contents("php://input");
    $votes = json_decode($jsonData, true);

    $token = $votes['token'];

    class Vote
    {
        private $conn;

        public function __construct($conn)
        {
            $this->conn = $conn;
        }

        public function addVotes($token, $votes)
        {
            //User authentication
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
                    //To check if user has already voted
                    $dataToHandleInDb = [
                        'table' => "polls_votes pv",
                        'method' => "get",
                        'columns' => ['pv.vote_id'],
                        'values' => [],
                        'others' => null,
                        'order' => null,
                        'conditions' => ['pv.user_id' => $votes['userId'], 'pv.question_id' => $votes['questionId']]
                    ];
                    $userAlreadVoted = dataToHandleInDb($this->conn, $dataToHandleInDb);

                    if ($userAlreadVoted) {
                        //Delete all votes
                        $dataToHandleInDb = [
                            'table' => "polls_votes",
                            'method' => "delete",
                            'columns' => [],
                            'values' => [],
                            'others' => null,
                            'order' => null,
                            'conditions' => ['user_id' => $votes['userId'], 'question_id' => $votes['questionId']]
                        ];
                        $deleteVote = dataToHandleInDb($this->conn, $dataToHandleInDb);
                        $isVoteDeleted = $deleteVote['isDeleted'];

                        if ($isVoteDeleted) {
                            //Insert new values
                            foreach ($votes as $key => $value) {
                                if ($key === 'answerIds' && is_array($value)) {
                                    foreach ($value as $newVote) {
                                        $dataToHandleInDb = [
                                            'table' => "polls_votes",
                                            'method' => "insert",
                                            'columns' => ['user_id', 'question_id', 'option_id'],
                                            'values' => [$votes['userId'], $votes['questionId'], $newVote],
                                            'others' => null,
                                            'order' => null,
                                            'conditions' => []
                                        ];
                                        $insertNewVote = dataToHandleInDb($this->conn, $dataToHandleInDb);
                                    }
                                    $isNewVoteInserted = $insertNewVote['isInserted'];
                                    if ($isNewVoteInserted) {
                                        $response = array(
                                            "confirmAddVotes" => true
                                        );
                                        echo json_encode($response);
                                    } else {
                                        $error = $isNewVoteInserted['message'];
                                        echo json_encode($error);
                                    }
                                }
                            }
                        }
                    } else {
                        //Insert values
                        foreach ($votes as $key => $value) {
                            $questionId = $votes['questionId'];
                            $userId = $votes['userId'];

                            if ($key === 'answerIds' && is_array($value)) {
                                foreach ($value as $element) {
                                    $optionId = $element;
                                    $stmt2 = $this->conn->prepare(
                                        "INSERT INTO polls_votes
                                        (user_id, question_id, option_id)
                                        VALUES (:user_id, :question_id, :option_id);
                                    "
                                    );
                                    $stmt2->bindParam(":user_id", $userId);
                                    $stmt2->bindParam(":question_id", $questionId);
                                    $stmt2->bindParam(":option_id", $optionId);
                                    $stmt2->execute();
                                    $rowCount = $stmt2->rowCount();

                                }
                                if ($rowCount) {
                                    $response = array(
                                        "confirmAddVotes" => true
                                    );
                                    echo json_encode($response);
                                }
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
                echo json_encode($error);
            }

        }
    }

    $addvotes = new Vote($conn);
    $addvotes->addVotes($token, $votes);
}


?>