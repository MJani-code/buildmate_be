<?php
header("Access-Control-Allow-Origin: http://192.168.76.68:3000"); // Változtasd meg a frontend URL-t, ha szükséges
header("Access-Control-Allow-Methods: *"); // Engedélyezett HTTP metódusok (pl. POST)
header("Access-Control-Allow-Headers: *"); // Engedélyezett fejlécek
header("Content-Type: application/json"); // Példa: JSON válasz küldése

require('../../inc/conn.php');
require('../../functions/getmaxid/getmaxid.php');
require('../../functions/deletebyid/deletebyid.php');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //adatok kinyerése
    $jsonData = file_get_contents("php://input");
    $data = json_decode($jsonData, true);

    $id = $data['id'];

    class DeleteEvent
    {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn)
        {
            $this->conn = $conn;
        }

        public function deleteEvent($id)
        {
            //Kitörlünk minden felelőst.
            $deletedRowCount = deleteResponsiblesByEventId($this->conn, 'events_responsibles', $id);
            if ($deletedRowCount >= 0) {
                $response['confirmDeleteEvent'] = true;
            } else {
                $error["error"] = "Hiba történt a felelősök törlése során";
                echo json_encode($error);
            }

            //Kitörlünk minden eseményt.
            $deletedRowCount = deleteEventById($this->conn, 'events', $id);
            if ($deletedRowCount >= 0) {
                $response['confirmDeleteEvent'] = true;
            } else {
                $error["error"] = "Hiba történt az esemény törlése során";
                echo json_encode($error);
            }

            echo json_encode($response);
        }
    }
    $deleteevent = new DeleteEvent($conn);
    $deleteevent->deleteEvent($id);
}


?>