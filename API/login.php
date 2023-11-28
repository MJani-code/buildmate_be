<?php
header("Access-Control-Allow-Origin: http://192.168.76.68:3000"); // Változtasd meg a frontend URL-t, ha szükséges
header("Access-Control-Allow-Methods: *"); // Engedélyezett HTTP metódusok (pl. POST)
header("Access-Control-Allow-Headers: *"); // Engedélyezett fejlécek
header("Content-Type: application/json"); // Példa: JSON válasz küldése

require('../inc/conn.php');
require('../vendor/autoload.php');


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


use \Firebase\JWT\JWT;

class LoginHandler {
    private $secretKey;
    private $conn; // Adatbázis kapcsolat

    public function __construct($conn) {
        //$length = 32; // 32 bájtos kulcs (256 bites)
        //$this->secretKey = bin2hex(random_bytes($length));
        $this->secretKey = '0815bd5951b692cfd181cb677d75d034f2be8edf9bf70729737106a1f3c9335c';
        $this->conn = $conn;
    }

    public function handleLogin() {
        $jsonData = file_get_contents("php://input");
        $data = json_decode($jsonData, true);

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Itt helyezd el a valós bejelentkezési logikát, például adatbázis lekérdezéssel

                // TODO: Létre kell hozni status oszlopot a user táblába!
                $stmt = $this->conn->prepare(
                    "SELECT
                    u.id, u.first_name, u.last_name, u.email, u.password, u.id_condominiums,
                    ur.page_category, ur.description
                    FROM users u
                    LEFT JOIN user_roles ur ON ur.id = u.id_user_roles
                    WHERE email = :email AND u.deleted = 0
                ");
                $stmt->bindParam(":email", $email);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                //print_r($result);
                $storedHashedPassword = $result['password'];
                $user_id = $result['id'];
                $firstName = $result['first_name'];
                $lastName = $result['last_name'];
                $condominium_id = $result['id_condominiums'];

                if (password_verify($password, $storedHashedPassword)) {
                    //echo "sikeres belépés";
                    $header = array(
                        "typ" => "JWT",
                        "alg" => "HS256",
                        "kid" => "unique-key-id" // Adj meg egy egyedi kulcs azonosítót
                    );
                    //Lejárati idő meghatározása
                    $currentTimestamp = time();
                    $expirationTimestamp = strtotime('+8 hours', $currentTimestamp);

                    // JWT kibocsátása
                    $payload = array(
                        'email' => $email,
                        'expirationTime' => $expirationTimestamp
                    );
                    $jwt = JWT::encode($payload, $this->secretKey, 'HS256');

                    $response = array(
                        "firstName" => $firstName,
                        "lastName" => $lastName,
                        "loginStatus" => "success",
                        "loginMessage" => "Sikeres bejelentkezés!",
                        "userRole" => $result['page_category'],
                        "token" => $jwt,
                        "userId" => $result['id'],
                        "condominium_id" => $condominium_id,
                        "loggedIn" => true
                    );


                    // Tárold el a tömböt a session-ben
                    session_start();
                    $_SESSION['user_data'] = $response;
                    session_write_close();

                    echo json_encode($response);

                    $stmt = $this->conn->prepare(
                        "INSERT INTO user_login
                        (user_id, condominium_id, token, token_expire_date, token_created_date)
                        VALUES (:user_id, :condominium_id, :token, :token_expire_date, :token_created_date);
                    ");

                    $formattedExpirationTimestamp = date('Y-m-d H:i:s', $expirationTimestamp);
                    $formattedCurrentTimestamp = date('Y-m-d H:i:s', $currentTimestamp);

                    $stmt->bindParam(":user_id", $user_id);
                    $stmt->bindParam(":condominium_id", $condominium_id);
                    $stmt->bindParam(":token", $jwt);
                    $stmt->bindParam(":token_expire_date", $formattedExpirationTimestamp);
                    $stmt->bindParam(":token_created_date", $formattedCurrentTimestamp);
                    $stmt->execute();

                } else {
                    $error["error"] = "Hibás felhasználónév vagy jelszó";
                    echo json_encode($error);
                }
            } catch (Exception $e) {
                $error["error"] = "Hiba történt a bejelentkezés közben: " . $e->getMessage();
                echo json_encode($error);
            }

        }
    }
}
$loginHandler = new LoginHandler($conn);
$loginHandler->handleLogin();
?>