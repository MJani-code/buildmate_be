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
    //adatok kinyerése

    class GetEvents
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
            //GET events
            try {
                $stmt = $this->conn->prepare(
                    "SELECT
                    e.id as 'id',
                    e.id_category as 'categoryId',
                    ec.description as 'category',
                    e.title as 'title',
                    e.start_event_unix as 'start',
                    e.end_event_unix as 'end',
                    e.created_by as 'createdBy',
                    ec.color as 'color',
                    er.event_id as 'event_id',
                    er.responsible_user_id as 'responsibleUserIds',
                    u.stair_case_flat as 'flat',
                    CONCAT(u.last_name,' ',u.first_name) as 'name'
                    FROM events e
                    LEFT JOIN events_categories ec on ec.id = e.id_category
                    LEFT JOIN events_responsibles er on er.event_id = e.id
                    LEFT JOIN users u on u.id = er.responsible_user_id
                    where e.deleted = 0
                    AND e.id_condominiums = '$condominiumId'
                "
                );

                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response['result'] = array();

                if ($result) {
                    // Az események csoportosítása az esemény azonosítója (event_id) alapján
                    $events = array();
                    foreach ($result as $row) {
                        $eventId = $row['id'];

                        if (!isset($events[$eventId])) {
                            $events[$eventId] = array(
                                'id' => $row['id'],
                                'categoryId' => $row['categoryId'],
                                'category' => $row['category'],
                                'name' => $row['title'],
                                'start' => (int)$row['start'],
                                'end' => (int)$row['end'],
                                'timed' => true,
                                'createdBy' => $row['createdBy'],
                                'color' => $row['color'],
                                'flat' => $row['flat'],
                                'responsibles' => array(), // Itt inicializálj egy üres felelősök tömböt
                                'responsiblesIds' => array() // Itt inicializálj egy üres felelősök tömböt
                            );
                        }
                        // Hozzáadhatod a felelősöket az adott eseményhez
                        $events[$eventId]['responsibles'][] = $row['name'];
                        $events[$eventId]['responsiblesIds'][] = $row['responsibleUserIds'];
                    }
                    // Az események hozzáadása a válaszhoz
                    $response['result'] = array_values($events);
                }

            } catch (Exception $e) {
                $error = "Hiba történt a művelet során: " . $e->getMessage();
                echo json_encode($error);
            }

            //GET users
            try {
                $stmt2 = $this->conn->prepare(
                    "SELECT
                    u.id as 'id',
                    CONCAT(u.last_name,' ',u.first_name) as 'name'
                    FROM users u
                    where u.deleted = 0
                "
                );

                $stmt2->execute();
                $result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

                if ($result2) {
                    $response['users'] = $result2;
                }
            } catch (Exception $e) {
                $error = "Hiba történt a művelet során: " . $e->getMessage();
                echo json_encode($error);
            }

            //GET event categories
            try {
                $stmt3 = $this->conn->prepare(
                    "SELECT
                    ec.id as 'id',
                    ec.description as 'name',
                    ec.color as 'color'
                    FROM events_categories ec
                "
                );

                $stmt3->execute();
                $result3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);

                if ($result3) {
                    $response['categories'] = $result3;
                    echo json_encode($response);
                    //print_r($response);
                }
            } catch (Exception $e) {
                $error = "Hiba történt a művelet során: " . $e->getMessage();
                echo json_encode($error);
            }
        }
    }
    $getevents = new GetEvents($conn);
    $token = $_POST['token'];
    $getevents->GetData($token);
}



?>