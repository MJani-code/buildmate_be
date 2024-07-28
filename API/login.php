<?php
header("Access-Control-Allow-Origin: http://192.168.76.68:3000");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json");

require('../inc/conn.php');
require('../inc/secretkey.php');
require('../vendor/autoload.php');

require ('../functions/db/dbFunctions.php');

use \Firebase\JWT\JWT;

class LoginHandler {
    private $secretKey;
    private $secretkey;
    private $conn; // Adatbázis kapcsolat

    public function __construct($conn, $secretkey) {
        //$length = 32; // 32 bájtos kulcs (256 bites)
        //$this->secretKey = bin2hex(random_bytes($length));
        $this->secretKey = $secretkey;
        $this->conn = $conn;
    }

    public function handleLogin() {
        $jsonData = file_get_contents("php://input");
        $data = json_decode($jsonData, true);

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // TODO: Létre kell hozni status oszlopot a user táblába!
                $stmt = $this->conn->prepare(
                    "SELECT
                    u.id, u.first_name, u.last_name, u.email, u.password, u.id_condominiums,
                    ur.page_category, ur.id as 'userRoleId', ur.description
                    FROM users u
                    LEFT JOIN user_roles ur ON ur.id = u.id_user_roles
                    WHERE email = :email AND u.deleted = 0
                ");
                $stmt->bindParam(":email", $email);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $storedHashedPassword = $result['password'];
                $user_id = $result['id'];
                $firstName = $result['first_name'];
                $lastName = $result['last_name'];
                $condominium_id = $result['id_condominiums'];

                if (password_verify($password, $storedHashedPassword)) {
                    $header = array(
                        "typ" => "JWT",
                        "alg" => "HS256",
                        "kid" => "unique-key-id"
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

                    //GET menu items
                    $dataToHandleInDb = [
                        'table' => "role_routes rr",
                        'method' => "get",
                        'columns' => ['*'],
                        'values' => [],
                        'others' => "
                            LEFT JOIN routes r ON r.id = rr.route_id
                        ",
                        'order' => "",
                        'conditions' => ['rr.role_id' => $result['userRoleId']],
                        'conditionExtra' => ""
                    ];

                    $menuItems = dataToHandleInDb($this->conn, $dataToHandleInDb);

                    $response = array(
                        "firstName" => $firstName,
                        "lastName" => $lastName,
                        "loginStatus" => "success",
                        "loginMessage" => "Sikeres bejelentkezés!",
                        "userRole" => $result['description'],
                        "userRoleId" => $result['userRoleId'],
                        "pageCategory" => $result['page_category'],
                        "token" => $jwt,
                        "userId" => $result['id'],
                        "condominium_id" => $condominium_id,
                        "loggedIn" => true,
                        "menuItems" => $menuItems
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
$loginHandler = new LoginHandler($conn, $secretkey);
$loginHandler->handleLogin();
?>