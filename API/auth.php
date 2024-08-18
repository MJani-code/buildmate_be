<?php


header("Access-Control-Allow-Origin: http://192.168.76.68:3000"); // Változtasd meg a frontend URL-t, ha szükséges
header("Access-Control-Allow-Methods: *"); // Engedélyezett HTTP metódusok (pl. POST)
header("Access-Control-Allow-Headers: *"); // Engedélyezett fejlécek
header("Content-Type: application/json"); // Példa: JSON válasz küldése

require ('../inc/conn.php');
require ('../functions/db/dbFunctions.php');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class AuthHandler
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function handleAuth()
    {
        $jsonData = file_get_contents("php://input");
        $data = json_decode($jsonData, true);

        $token = $data['token'] ?? '';
        $path = $data['path'] ?? '';

        //Jött Token?
        if (!$token || !$path) {
            $response = array(
                "tokenValid" => false
            );
            echo json_encode($response);
            return false;
        }

        $currentTimestamp = date("Y-m-d H:i:s", time());

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            try {
                $stmt = $this->conn->prepare(
                    "SELECT * FROM user_login ul
                    LEFT JOIN users u ON u.id = ul.user_id
                    LEFT JOIN user_roles ur ON ur.id = u.id_user_roles
                    WHERE ul.token = :token
                "
                );
                $stmt->bindParam(":token", $token);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                //Token meglétének ellenőrzése
                if(!$result){
                    $response = array(
                        "status" => 404,
                        "tokenValid" => false,
                        "message" => "Kijelentkeztél, jelentkezz be újra!"
                    );
                    echo json_encode($response);
                    return false;
                }

                $expirationTimestamp = $result['token_expire_date'];
                $pageCategory = $result['page_category'];
                $userRoleId = $result['id_user_roles'];


                //Token érvényességének ellenőrzése
                if ($token && $currentTimestamp < $expirationTimestamp) {
                    $dataToHandleInDb = [
                        'table' => "role_routes rr",
                        'method' => "get",
                        'columns' => ['*'],
                        'values' => [],
                        'others' => "
                            LEFT JOIN routes r ON r.id = rr.route_id
                        ",
                        'order' => "",
                        'conditions' => ['rr.role_id' => $userRoleId, 'r.path' => $path],
                        'conditionExtra' => ""
                    ];

                    //Útvonal engedély ellenőrzése
                    $isPathAllowed = dataToHandleInDb($this->conn, $dataToHandleInDb);

                    if ($isPathAllowed['status'] === 200) {
                        if($isPathAllowed['payload']){
                            $response = array(
                                "status" => $isPathAllowed['status'],
                                "tokenValid" => true,
                                "pageCategory" => $pageCategory,
                                "currentTimestamp" => $currentTimestamp,
                                "expirationTimestamp" => $expirationTimestamp
                            );
                        }else{
                            $response = array(
                                "status" => 401,
                                "tokenValid" => false,
                                "error_info" => "Nincs jogosultságod a útvonalra",
                                "pageCategory" => $pageCategory,
                                "currentTimestamp" => $currentTimestamp,
                                "expirationTimestamp" => $expirationTimestamp
                            );
                        }
                    } else {
                        $response = $isPathAllowed;
                    }
                } else {
                    //Lejárt a Token
                    $response = array(
                        "status" => 404,
                        "tokenValid" => false,
                        "error_info" => "Lejárt token",
                        "pageCategory" => $pageCategory,
                        "currentTimestamp" => $currentTimestamp,
                        "expirationTimestamp" => $expirationTimestamp
                    );
                }
                echo json_encode($response);
            } catch (Exception $e) {
                $errorInfo = $e->getMessage();
                $response = array(
                    "status" => 500,
                    "tokenValid" => false,
                    "error_info" => $errorInfo,
                    "pageCategory" => $pageCategory,
                    "currentTimestamp" => $currentTimestamp,
                    "expirationTimestamp" => $expirationTimestamp
                );
                echo json_encode($errorInfo);
            }
        }
    }
}

$authHandler = new AuthHandler($conn);
$authHandler->handleAuth();