<?php
header("Access-Control-Allow-Origin: http://192.168.76.68:3000"); // Változtasd meg a frontend URL-t, ha szükséges
header("Access-Control-Allow-Methods: *"); // Engedélyezett HTTP metódusok (pl. POST)
header("Access-Control-Allow-Headers: *"); // Engedélyezett fejlécek
header("Content-Type: application/json"); // Példa: JSON válasz küldése

require('../../inc/conn.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (true) {
    $jsonData = file_get_contents("php://input");
    $data = json_decode($jsonData, true);

    // $userid = $data['userId'];
    $userid = 4;

class GetUserData {
    private $conn; // Adatbázis kapcsolat

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getUserData($userid) {
            try {
                $stmt = $this->conn->prepare(
                    "SELECT * FROM users
                    WHERE id = :id
                ");
                $stmt->bindParam(":id", $userid);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if($result){
                    $response = $result;
                    echo json_encode($response);
                }else{
                    $error["error"] = "Hiba történt az adatok lekérdezése közben";
                    echo json_encode($error);
                }
            }catch (Exception $e) {
                $error["error"] = "Hiba történt az adatok lekérdezése közben: " . $e->getMessage();
                echo json_encode($error);
            }
    }
}

$getuser = new GetUserData($conn);
$getuser->getUserData($userid);
}


?>