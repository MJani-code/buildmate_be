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
    //adatok kinyerése
    $id = $_POST['id'] ?? null;

    class DeleteFaq
    {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn)
        {
            $this->conn = $conn;
        }

        public function deleteRow($id)
        {
            try {
                $stmt = $this->conn->prepare(
                    "UPDATE faqs SET
                                deleted = 1
                                WHERE id = :id
                            "
                );
                $stmt->bindParam(":id", $id);

                $stmt->execute();
                $rowCount = $stmt->rowCount();

                if ($rowCount > 0) {
                    $response = array(
                        "confirmDeleteFaq" => true
                    );
                    echo json_encode($response);
                } else {
                    $error["error"] = "Hiba történt a sor törlése közben";
                    echo json_encode($error);
                }
            } catch (Exception $e) {
                $error["error"] = "Hiba történt a sor törlése közben: " . $e->getMessage();
                echo json_encode($error);
            }

        }
    }
}
$deletefaq = new DeleteFaq($conn);
$deletefaq->deleteRow($id);



?>