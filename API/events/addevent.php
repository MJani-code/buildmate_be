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
    $categoryid = $_POST[''];
    $responsibleusers = $_POST[''];
    $title = $_POST[''];
    $startevent = $_POST[''];
    $endevent = $_POST[''];
    $createdby = $_POST[''];


    class AddEvent {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn) {
            $this->conn = $conn;
        }

        public function addEvent($categoryid, $title, $startevent, $endevent, $createdby, $responsibleusers) {

            try {
                $stmt = $this->conn->prepare(
                    "INSERT INTO events
                    (id_category, title, start_event, end_event, created_by)
                    VALUES (:id_category, :title, :start_event, :end_event, :created_by);
                ");
                $stmt->bindParam(":id_category", $categoryid);
                $stmt->bindParam(":title", $title);
                $stmt->bindParam(":start_event", $startevent);
                $stmt->bindParam(":end_event", $endevent);
                $stmt->bindParam(":created_by", $createdby);

                $stmt->execute();
                $rowCount = $stmt->rowCount();

                if($responsibleusers){
                    //Ide jön majd egy foreach
                }

                if($rowCount > 0){
                    $response['confirmAddNewEvent'] = true;
                    echo json_encode($response);
                }

            }catch (Exception $e) {
                $error = "Hiba történt a művelet során: " . $e->getMessage();
                echo json_encode($error);
            }
        }
    }
    $addevent = new AddEvent($conn);
    $addevent->AddEvent($categoryid, $responsibleusers, $title, $startevent, $endevent, $createdby );
}



?>