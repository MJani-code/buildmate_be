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
    $jsonData = file_get_contents("php://input");
    $data = json_decode($jsonData, true);

    $lastname = $data['lastName'];
    $firstname = $data['firstName'];
    $phonenumber = $data['phoneNumber'];
    $email = $data['email'];
    $staircase = $data['stairCase'];
    $flat = $data['flat'];
    $resident = $data['resident'];
    $professionalfield = $data['professionalField'];


    class AddNewAccount {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn) {
            $this->conn = $conn;
        }

        public function addAccount($lastname, $firstname, $phonenumber, $email, $staircase, $flat, $resident, $professionalfield) {

            try {
                $stmt = $this->conn->prepare(
                    "INSERT INTO accounts
                    (first_name, last_name, phonenumber, email, stair_case, flat, resident, professional_field)
                    VALUES (:first_name, :last_name, :phonenumber, :email, :stair_case, :flat, :resident, :professional_field);
                ");
                $stmt->bindParam(":first_name", $firstname);
                $stmt->bindParam(":last_name", $lastname);
                $stmt->bindParam(":phonenumber", $phonenumber);
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":stair_case", $staircase);
                $stmt->bindParam(":flat", $flat);
                $stmt->bindParam(":resident", $resident);
                $stmt->bindParam(":professional_field", $professionalfield);
                $stmt->execute();
                $rowCount = $stmt->rowCount();

                $response = array(
                    "confirmAddNewAccount" => true
                );
                echo json_encode($response);

            }catch (Exception $e) {
                $error["error"] = "Hiba történt a művelet során: " . $e->getMessage();
                echo json_encode($error);
            }
        }
    }
    $addnewaccounts = new AddNewAccount($conn);
    $addnewaccounts->addAccount($lastname, $firstname, $phonenumber, $email, $staircase, $flat, $resident, $professionalfield);
}



?>