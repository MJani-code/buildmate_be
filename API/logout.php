<?php


header("Access-Control-Allow-Origin: http://192.168.76.68:3000"); // Változtasd meg a frontend URL-t, ha szükséges
header("Access-Control-Allow-Methods: *"); // Engedélyezett HTTP metódusok (pl. POST)
header("Access-Control-Allow-Headers: *"); // Engedélyezett fejlécek
header("Content-Type: application/json"); // Példa: JSON válasz küldése

require('../inc/conn.php');
require('../vendor/autoload.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

use \Firebase\JWT\JWT;

class LogoutHandler {
    private $secretKey;
    private $conn; // Adatbázis kapcsolat

    public function __construct($conn) {
        //$length = 32; // 32 bájtos kulcs (256 bites)
        //$this->secretKey = bin2hex(random_bytes($length));
        $this->secretKey = '0815bd5951b692cfd181cb677d75d034f2be8edf9bf70729737106a1f3c9335c';
        $this->conn = $conn;
    }

    public function handleLogout() {
        $jsonData = file_get_contents("php://input");
        $token = $jsonData;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            try {
                $stmt = $this->conn->prepare(
                    "DELETE FROM user_login
                    WHERE token = :token
                ");
                $stmt->bindParam(":token", $token);
                $stmt->execute();
                $affected_rows = $stmt->rowCount();

                if($affected_rows > 0){
                    $response = array(
                        "confirmLogout" => true,
                        "token" => $token,
                        "loggedIn" => false
                    );
                    echo json_encode($response);
                }else{
                    $error["error"] = "Nem létezik a megadott token: ".$token;
                    echo json_encode($error);
                }
            }catch (Exception $e) {
                $error["error"] = "Hiba történt a kijelentkezés közben: " . $e->getMessage();
                echo json_encode($error);
            }
        }
    }
}
$logoutHandler = new LogoutHandler($conn);
$logoutHandler->handleLogout();
?>