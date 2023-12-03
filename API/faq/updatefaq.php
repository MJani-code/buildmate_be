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

    $id = $_POST['id'];
    $description = $_POST['description'];
    $title = $_POST['title'];
    $userid = $_POST['userId'];


    class UpdateFaqData
    {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn)
        {
            $this->conn = $conn;
        }

        public function updateData($id, $title, $description, $userid)
        {

            try {
                $stmt = $this->conn->prepare(
                    "UPDATE faqs SET
                            title = :title,
                            description = :description,
                            updated_at = NOW(),
                            updated_by = :updated_by
                            WHERE id = :id
                        "
                );
                $stmt->bindParam(":id", $id);
                $stmt->bindParam(":title", $title);
                $stmt->bindParam(":description", $description);
                $stmt->bindParam(":updated_by", $userid);

                $stmt->execute();
                $rowCount = $stmt->rowCount();

                if ($rowCount > 0) {
                    $response = array(
                        "confirmUpdateFaqData" => true
                    );
                    echo json_encode($response);
                } else {
                    $error["error"] = "Hiba történt az adatok frissítése közben";
                    echo json_encode($error);
                }
            } catch (Exception $e) {
                $error["error"] = "Hiba történt az adatok frissítése közben: " . $e->getMessage();
                echo json_encode($error);
            }
        }
    }

    $updatefaq = new UpdateFaqData($conn);
    $updatefaq->updateData($id, $title, $description, $userid);
}