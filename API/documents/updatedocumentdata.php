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
    // $jsonData = file_get_contents("php://input");
    // $data = json_decode($jsonData, true);

    $id = $_POST['id'];
    $typeid = $_POST['typeId'];
    $statusid = $_POST['statusId'];
    $title = $_POST['title'];
    $eventtype = $_POST['eventType'];
    $deleted = 0;

    class UpdateDocumentData
    {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn)
        {
            $this->conn = $conn;
        }

        public function updateData($id, $typeid, $statusid, $title, $eventtype, $deleted)
        {
            if ($typeid == 2 && $statusid < 4 && !$deleted) {
                $statusid = $statusid + 1;
            }
            if ($eventtype == 'delete') {
                $deleted = 1;
            }

            try {
                $stmt = $this->conn->prepare(
                    "UPDATE documents SET
                            id_status = :id_status,
                            title = :title,
                            deleted = :deleted
                            WHERE id = :id
                        "
                );
                $stmt->bindParam(":id", $id);
                $stmt->bindParam(":id_status", $statusid);
                $stmt->bindParam(":title", $title);
                $stmt->bindParam(":deleted", $deleted);

                $stmt->execute();
                $rowCount = $stmt->rowCount();

                if ($rowCount > 0) {
                    $response = array(
                        "confirmUpdateDocumentData" => true,
                        "typeId" => $typeid,
                        "statusId" => $statusid
                    );
                    echo json_encode($response);
                } else {
                    $error = "Hiba történt az adatok frissítése közben";
                    echo json_encode($error);
                }
            } catch (Exception $e) {
                $error = "Hiba történt az adatok frissítése közben: " . $e->getMessage();
                echo json_encode($error);
            }
        }
    }

    $updatedocument = new UpdateDocumentData($conn);
    $updatedocument->updateData($id, $typeid, $statusid, $title, $eventtype, $deleted);
}