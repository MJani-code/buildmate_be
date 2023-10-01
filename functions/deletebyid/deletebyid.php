<?php
function deleteResponsiblesByEventId(PDO $conn, $tableName, $eventId) {
    try {
        $stmt = $conn->prepare("DELETE FROM $tableName WHERE event_id = :eventId");
        $stmt->bindParam(":eventId", $eventId);
        $stmt->execute();
        $rowCount = $stmt->rowCount();

        return $rowCount;
    } catch (Exception $e) {
        $error = "Hiba történt a művelet során: " . $e->getMessage();
        return json_encode($error);
    }
}


function deleteEventById(PDO $conn, $tableName, $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM $tableName WHERE id = :id");
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $rowCount = $stmt->rowCount();

        return $rowCount;
    } catch (Exception $e) {
        $error = "Hiba történt a művelet során: " . $e->getMessage();
        return json_encode($error);
    }
}



?>