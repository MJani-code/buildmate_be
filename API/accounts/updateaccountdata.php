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

    $id = $data['id'];
    $phonenumber = $data['phoneNumber'];
    $email = $data['email'];


    class UpdateAccountData
    {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn)
        {
            $this->conn = $conn;
        }

        public function updateAccountData($id, $email, $phonenumber)
        {

            try {
                $stmt = $this->conn->prepare(
                    "UPDATE accounts SET
                            email = :email,
                            phonenumber = :phonenumber
                            WHERE id = :id
                        "
                );
                $stmt->bindParam(":id", $id);
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":phonenumber", $phonenumber);

                $stmt->execute();
                $rowCount = $stmt->rowCount();

                if ($rowCount > 0) {
                    $response = array(
                        "confirmUpdateAccountData" => true
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

    $updateaccount = new UpdateAccountData($conn);
    $updateaccount->updateAccountData($id, $email, $phonenumber);
}