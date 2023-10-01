<?php
header("Access-Control-Allow-Origin: http://192.168.76.68:3000"); // Változtasd meg a frontend URL-t, ha szükséges
header("Access-Control-Allow-Methods: *"); // Engedélyezett HTTP metódusok (pl. POST)
header("Access-Control-Allow-Headers: *"); // Engedélyezett fejlécek
header("Content-Type: application/json"); // Példa: JSON válasz küldése

require('../../inc/conn.php');
require('../../functions/getter/getmaxid.php');
require('../../functions/deletebyid/deletebyid.php');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //adatok kinyerése
    $jsonData = file_get_contents("php://input");
    $data = json_decode($jsonData, true);

    $id = $data['id'] ?? null;
    $categoryid = $data['categoryId'];
    $responsiblesIds = $data['responsiblesIds'] ?? null;
    $title = $data['name'];
    $starteventunix = $data['start'];
    $endeventunix = $data['end'];
    $startdate = date("Y-m-d H:i:s",($starteventunix)/1000 );
    $enddate = date("Y-m-d H:i:s",($endeventunix)/1000 );
    $createdby = $data['userId'] ?? $data['createdBy'];


    class AddEvent
    {
        private $conn; // Adatbázis kapcsolat

        public function __construct($conn)
        {
            $this->conn = $conn;
        }

        public function addEvent($id, $categoryid, $title, $startdate, $enddate, $starteventunix, $endeventunix, $createdby, $responsiblesIds)
        {
            //Ha új eseményt adnak hozzá, akkor insertálunk
            if (!$id) {
                try {
                    $stmt = $this->conn->prepare(
                        "INSERT INTO events
                        (id_category, title, start_event, end_event ,start_event_unix, end_event_unix, created_at, created_by)
                        VALUES (:id_category, :title, :start_event, :end_event, :start_event_unix, :end_event_unix, NOW(), :created_by);
                    "
                    );
                    $stmt->bindParam(":id_category", $categoryid);
                    $stmt->bindParam(":title", $title);
                    $stmt->bindParam(":start_event", $startdate);
                    $stmt->bindParam(":end_event", $enddate);
                    $stmt->bindParam(":start_event_unix", $starteventunix);
                    $stmt->bindParam(":end_event_unix", $endeventunix);
                    $stmt->bindParam(":created_by", $createdby);

                    $stmt->execute();
                    $rowCount = $stmt->rowCount();

                    $maxId = getMaxId($this->conn, 'events', 'id');
                    if ($responsiblesIds) {
                        if ($maxId != -1) {
                            //echo "A maximális ID: $maxId";
                        } else {
                            echo "Hiba történt a maximális ID lekérdezése során.";
                        }

                        $insert = 0;
                        $error = '';
                        foreach ($responsiblesIds as $responsibleId) {
                            $stmt2 = $this->conn->prepare(
                                "INSERT INTO events_responsibles
                                (responsible_user_id, event_id)
                                VALUES (:responsibleUserId, :event_id)
                                "
                            );
                            $stmt2->bindParam(":responsibleUserId", $responsibleId);
                            $stmt2->bindParam(":event_id", $maxId);
                            $stmt2->execute();
                            $rowCount2 = $stmt2->rowCount();
                            if ($rowCount2) {
                                $insert++;
                            } else {
                                $error['error'] = "Hiba történt a felelősök beszúrásakor";
                                echo json_encode($error);
                            }
                        }
                        if ($insert > 0) {
                            $response['confirmAddNewEvent'] = true;

                        } else {
                            $error['error'] = "Nem sikerült minden felelőst hozzáadni";
                            echo json_encode($error);
                        }
                    }

                    if ($rowCount > 0) {
                        $response['confirmAddNewEvent'] = true;
                        $response['eventId'] = $maxId;
                        echo json_encode($response);
                    }

                } catch (Exception $e) {
                    $error = "Hiba történt a művelet során: " . $e->getMessage();
                    echo json_encode($error);
                }
            }
            //Ha nem újat adnak hozzá, akkor updatelünk
            else {
                try {
                    $stmt = $this->conn->prepare(
                        "UPDATE events SET
                                id_category = :id_category,
                                title = :title,
                                start_event = :start_event,
                                end_event = :end_event,
                                start_event_unix = :start_event_unix,
                                end_event_unix = :end_event_unix,
                                updated_at = NOW(),
                                updated_by = :updated_by
                                WHERE id = :id
                            "
                    );
                    $stmt->bindParam(":id", $id);
                    $stmt->bindParam(":id_category", $categoryid);
                    $stmt->bindParam(":title", $title);
                    $stmt->bindParam(":start_event", $startdate);
                    $stmt->bindParam(":end_event", $enddate);
                    $stmt->bindParam(":start_event_unix", $starteventunix);
                    $stmt->bindParam(":end_event_unix", $endeventunix);
                    $stmt->bindParam(":updated_by", $createdby);

                    $stmt->execute();
                    $rowCount = $stmt->rowCount();


                    //Kitörlünk minden felelőst.
                    $deletedRowCount = deleteResponsiblesByEventId($this->conn, 'events_responsibles', $id);
                    if ($deletedRowCount >= 0) {
                        $response['confirmUpdateEvent'] = true;
                    } else {
                        $error["error"] = "Hiba történt a törlés során";
                        echo json_encode($error);
                    }

                    //Hozzáadunk minden felelőst.
                    if ($responsiblesIds) {
                        $insert = 0;
                        $error = '';
                        foreach ($responsiblesIds as $responsibleId) {
                            $stmt2 = $this->conn->prepare(
                                "INSERT INTO events_responsibles
                                (responsible_user_id, event_id)
                                VALUES (:responsibleUserId, :event_id)
                                "
                            );
                            $stmt2->bindParam(":responsibleUserId", $responsibleId);
                            $stmt2->bindParam(":event_id", $id);
                            $stmt2->execute();
                            $rowCount2 = $stmt2->rowCount();
                            if ($rowCount2) {
                                $insert++;
                            } else {
                                $error = "Hiba történt a felelősök frissítésekor";
                            }
                        }
                        if ($insert > 0) {
                            $response['confirmUpdateEvent'] = true;
                        } else {
                            $error['error'] = "Nem sikerült minden felelőst frissíteni";
                            echo json_encode($error);
                        }

                    }

                    if ($rowCount > 0) {
                        $response = array(
                            "confirmUpdateEvent" => true
                        );
                        echo json_encode($response);
                    } else {
                        $error = "Hiba történt az adatok frissítése közben";
                        echo json_encode($error);
                    }
                } catch (Exception $e) {
                    $error["error"] = "Hiba történt az adatok frissítése közben: " . $e->getMessage();
                    echo json_encode($error);
                }
            }

        }
    }
    $addevent = new AddEvent($conn);
    $addevent->AddEvent($id, $categoryid, $title, $startdate, $enddate, $starteventunix, $endeventunix, $createdby, $responsiblesIds);
}

?>