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
    $jsonData = file_get_contents("php://input");
    $data = json_decode($jsonData, true);

    $firstname = $data['firstName'];
    $lastname = $data['lastName'];
    $email = $data['email'];
    $phonenumber = $data['phoneNumber'];
    $password = $data['password'];
    $newpassword = $data['newPassword'] ?? '';
    $newpasswordconfirm = $data['newPasswordConfirm'] ?? '';
    $staircase = $data['stairCase'];
    $flat = $data['flat'];

    class UpdateUserData
    {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn)
        {
            $this->conn = $conn;
        }

        public function updateUserData($firstname, $lastname, $email, $phonenumber, $password, $newpassword, $newpasswordconfirm, $staircase, $flat)
        {

            try {
                $stmt1 = $this->conn->prepare(
                    "SELECT *
                    FROM users u
                    WHERE email = :email
                "
                );
                $stmt1->bindParam(":email", $email);
                $stmt1->execute();
                $result = $stmt1->fetch(PDO::FETCH_ASSOC);
                $storedHashedPassword = $result['password'];

                if (password_verify($password, $storedHashedPassword)) {

                    if ($newpassword && $newpassword == $newpasswordconfirm) {
                        $hashedNewPassword = password_hash($newpassword, PASSWORD_DEFAULT, array('cost' => 7)) ?? '';

                    } else if ($newpassword !== $newpasswordconfirm) {
                        $error["error"] = "Nem sikerült a jelszó megerősítése. Ellenőrizd, hogy jól adtad meg az új jelszót";
                        echo json_encode($error);
                        exit;
                    } else {
                        $hashedNewPassword = $storedHashedPassword;
                    }

                    try {
                        $stmt1 = $this->conn->prepare(
                            "UPDATE users SET
                            first_name = :firstname,
                            last_name = :lastname,
                            email = :email,
                            phonenumber = :phonenumber,
                            stair_case = :staircase,
                            flat = :flat,
                            password = :password
                            WHERE email = :email
                        "
                        );
                        $stmt1->bindParam(":firstname", $firstname);
                        $stmt1->bindParam(":lastname", $lastname);
                        $stmt1->bindParam(":email", $email);
                        $stmt1->bindParam(":phonenumber", $phonenumber);
                        $stmt1->bindParam(":staircase", $staircase);
                        $stmt1->bindParam(":flat", $flat);
                        $stmt1->bindParam(":password", $hashedNewPassword);
                        $stmt1->execute();
                        $rowCount1 = $stmt1->rowCount();

                        $stmt2 = $this->conn->prepare(
                            "UPDATE accounts SET
                            email = :email,
                            phonenumber = :phonenumber
                            WHERE email = :email
                        "
                        );

                        $stmt2->bindParam(":email", $email);
                        $stmt2->bindParam(":phonenumber", $phonenumber);
                        $stmt2->execute();
                        $rowCount2 = $stmt2->rowCount();

                        if ($rowCount1 > 0 || $rowCount2 > 0) {
                            $response = array(
                                "confirmUpdateUserData" => true
                            );
                            echo json_encode($response);
                        } else {
                            $error["error"] = "Hiba történt az adatok frissítése közben";
                            echo json_encode($error);
                        }
                    } catch (Exception $e) {
                        $error["error"] = "Hiba történt az adatok frissítése közben: " . $e->getMessage();
                        echo json_encode($error);
                    }

                } else {
                    $error["error"] = "Hibás felhasználónév vagy jelszó";
                    echo json_encode($error);
                }

            } catch (Exception $e) {
                $error["error"] = "Hiba történt az adatok frissítése közben: " . $e->getMessage();
                echo json_encode($error);
            }

        }
    }

    $updateuser = new UpdateUserData($conn);
    $updateuser->updateUserData($firstname, $lastname, $email, $phonenumber, $password, $newpassword, $newpasswordconfirm, $staircase, $flat);
}