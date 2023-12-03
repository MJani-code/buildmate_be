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
    //adatok kinyerése

    $description = $_POST['description'];
    $title = $_POST['title'];
    $token = $_POST['token'];


    class AddNewFaq
    {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn)
        {
            $this->conn = $conn;
        }

        public function addData($description, $title, $token)
        {

            $maxId = getMaxId($this->conn, 'faqs', 'id');
            //GET condominium data
            try {
                $stmt = $this->conn->prepare(
                    "SELECT
                    u.id_condominiums as 'condominiumId',
                    u.id as 'userId'
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
                    "INSERT INTO faqs
                    (title, id_condominiums, description, created_at, created_by)
                    VALUES (:title, :id_condominiums, :description, NOW(), :created_by);
                "
                );
                $stmt->bindParam(":title", $title);
                $stmt->bindParam(":description", $description);
                $stmt->bindParam(":id_condominiums", $condominiumId);
                $stmt->bindParam(":created_by", $userId);
                $stmt->execute();
                $rowCount = $stmt->rowCount();

                $response =
                    array(
                        "confirmAddNewFaq" => true,
                        'id' => $maxId + 1,
                        'title' => $title,
                        'description' => $description
                    )
                ;
                echo json_encode($response);

            } catch (Exception $e) {
                $error["error"] = "Hiba történt a művelet során: " . $e->getMessage();
                echo json_encode($error);
            }
        }
    }
    $addnewfaq = new AddNewFaq($conn);
    $addnewfaq->addData($description, $title, $token);
}



?>