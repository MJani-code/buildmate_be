<?php


header("Access-Control-Allow-Origin: http://192.168.76.68:3000"); // Változtasd meg a frontend URL-t, ha szükséges
header("Access-Control-Allow-Methods: *"); // Engedélyezett HTTP metódusok (pl. POST)
header("Access-Control-Allow-Headers: *"); // Engedélyezett fejlécek
header("Content-Type: application/json"); // Példa: JSON válasz küldése

require('../inc/conn.php');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class AuthHandler {
    private $conn; // Adatbázis kapcsolat

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function handleAuth() {
        $jsonData = file_get_contents("php://input");
        $data = json_decode($jsonData, true);

        $token = $data['token'];
        $currentTimestamp = time();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $stmt = $this->conn->prepare(
                    "SELECT * FROM user_login ul
                    LEFT JOIN users u ON u.id = ul.user_id
                    LEFT JOIN user_roles ur ON ur.id = u.id_user_roles
                    WHERE ul.token = :token
                ");
                $stmt->bindParam(":token", $token);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $expirationTimestamp = $result['token_expire_date'];
                $pageCategory = $result['page_category'];

                if($token && $currentTimestamp < $expirationTimestamp){
                    $response = array(
                        "tokenValid" => true,
                        "pageCategory" => $pageCategory
                    );
                    echo json_encode($response);
                }else{
                    $error["error"] = "A munkamenet lejárt, jelentkezz be újra!";
                    echo json_encode($error);
                }

            } catch (Exception $e) {
                $error["error"] = "Hiba történt a bejelentkezés közben: " . $e->getMessage();
                echo json_encode($error);
            }
        }
    }
}

$authHandler = new AuthHandler($conn);
$authHandler->handleAuth();