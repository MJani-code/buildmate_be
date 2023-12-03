<?php
function getMaxId(PDO $conn, $tableName, $idColumn) {
    try {
        $stmt = $conn->prepare("SELECT COALESCE(MAX($idColumn), 0) as max_id FROM $tableName");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && isset($result['max_id'])) {
            return (int)$result['max_id'];
        } else {
            return -1; // Hiba esetén
        }
    } catch (Exception $e) {
        return -1; // Hiba esetén
    }
}
?>
