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

    class Faqs
    {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn)
        {
            $this->conn = $conn;
        }

        public function GetData($token)
        {
            //GET condominium data
            try {
                $stmt = $this->conn->prepare(
                    "SELECT
                    u.id_condominiums as 'condominiumId'
                    from users u
                    LEFT JOIN user_login ul on ul.user_id = u.id
                    where ul.token = '$token'
                    "
                );
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);


                if ($result) {
                    $condominiumId = $result[0]['condominiumId'];
                }

            } catch (Exception $e) {
                $error = $e->getMessage();
                echo json_encode($error);
            }
            //GET faqs
            try {
                $stmt = $this->conn->prepare(
                    "SELECT
                    f.id as 'id',
                    f.title as 'title',
                    f.description as 'description',
                    f.created_at as 'created_at',
                    f.created_by as 'created_by'
                    FROM faqs f
                    LEFT JOIN users u on u.id = f.created_by
                    where f.deleted = 0
                    AND f.id_condominiums = '$condominiumId'
                    ORDER BY f.id desc
                "
                );

                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($result) {
                    $response['result'] = $result;
                    echo json_encode($response);
                    //print_r($response);
                } else {
                    $error["error"] = "Hiba történt az adatok lekérdezése közben";
                    echo json_encode($error);
                }

            } catch (Exception $e) {
                $error = $e->getMessage();
                echo json_encode($error);
            }
        }
    }
    $getfaqs = new Faqs($conn);
    $token = $_POST['token'];
    $getfaqs->GetData($token);
}



?>