<?php
function deleteResponsiblesByEventId(PDO $conn, $tableName, $eventId) {
    try {
        $stmt = $conn->prepare("DELETE FROM $tableName WHERE event_id = :eventId");
        $stmt->bindParam(":eventId", $eventId);
        $stmt->execute();
        $rowCount = $stmt->rowCount();

        return $rowCount;
    } catch (PDOException $e) {
        // Hiba kezelése itt, például hibaüzenet logolása vagy továbbítása
        return -1; // Vagy más érték, ami a hiba jelzésére szolgál
    }
}


function deleteEventById(PDO $conn, $tableName, $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM $tableName WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $rowCount = $stmt->rowCount();

        return $rowCount;
    } catch (PDOException $e) {
        // Hiba kezelése itt, például hibaüzenet logolása vagy továbbítása
        return -1; // Vagy más érték, ami a hiba jelzésére szolgál
    }
}



?>