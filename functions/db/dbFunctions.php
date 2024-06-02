<?php
require ('../../inc/conn.php');

// $dataToHandleInDb = array();

// $dataToHandleInDb = [
//     'dbName' => "teszt",
//     'method' => "",
//     'columns' => ['token'],
//     'values' => ['token'],
//     'conditions' => ['user_id' => 2]
// ];

function dataToHandleInDb($conn, $dataToHandleInDb)
{
    $columnsFormatted = '';
    $valuesFormatted = '';
    $dbName = $dataToHandleInDb['dbName'];
    $method = $dataToHandleInDb['method'];

    foreach ($dataToHandleInDb['columns'] as $key => $column) {
        $lastColumnKey = array_key_last($dataToHandleInDb['columns']);
        $columnsFormatted .= $column;
        if ($lastColumnKey != $key) {
            $columnsFormatted .= ",";
        }
    }
    foreach ($dataToHandleInDb['columns'] as $key => $column) {
        $lastValueKey = array_key_last($dataToHandleInDb['columns']);
        $valuesFormatted .= ":" . $column;
        if ($lastValueKey != $key) {
            $valuesFormatted .= ",";
        }
    }

    switch ($method) {
        case 'insert':
            try {
                foreach ($dataToHandleInDb['columns'] as $key => $column) {
                    $stmt = $conn->prepare(
                        "INSERT INTO " . $dbName . "
                            (" . $columnsFormatted . ")
                            VALUES (" . $valuesFormatted . ");
                        "
                    );
                }
                foreach ($dataToHandleInDb['values'] as $key => $value) {
                    $column = ":" . $dataToHandleInDb['columns'][$key];
                    $stmt->bindValue($column, $value);
                }
                $stmt->execute();

            } catch (Exception $e) {
                $error = "Hiba történt a művelet során: " . $e->getMessage();
                echo json_encode($error);
            }
            break;
        case 'get':
            $conditions = $dataToHandleInDb['conditions'];
            $conditionString = implode(" AND ", array_map(function ($col) {
                return "$col = :$col";
            }, array_keys($conditions)));

            try {
                $stmt = $conn->prepare(
                    "SELECT $columnsFormatted FROM $dbName WHERE $conditionString"
                );

                foreach ($conditions as $col => $value) {
                    $stmt->bindValue(":$col", $value);
                }

                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode($results);
            } catch (Exception $e) {
                $error = "Hiba történt a művelet során: " . $e->getMessage();
                echo json_encode($error);
            }
            break;
        case 'update':
            $columns = $dataToHandleInDb['columns'];
            $values = $dataToHandleInDb['values'];
            $conditions = $dataToHandleInDb['conditions'];

            $setString = implode(", ", array_map(function ($col) {
                return "$col = :$col";
            }, $columns));

            $conditionString = implode(" AND ", array_map(function ($col) {
                return "$col = :cond_$col";
            }, array_keys($conditions)));

            try {
                $stmt = $conn->prepare(
                    "UPDATE $dbName SET $setString WHERE $conditionString"
                );

                foreach ($columns as $key => $column) {
                    $stmt->bindValue(":$column", $values[$key]);
                }

                foreach ($conditions as $col => $value) {
                    $stmt->bindValue(":cond_$col", $value);
                }

                $stmt->execute();
                echo "Data updated successfully.";
            } catch (Exception $e) {
                $error = "Hiba történt a művelet során: " . $e->getMessage();
                echo json_encode($error);
            }
            break;
        case 'delete':
            $conditions = $dataToHandleInDb['conditions'];
            $conditionString = implode(" AND ", array_map(function ($col) {
                return "$col = :$col";
            }, array_keys($conditions)));

            try {
                $stmt = $conn->prepare(
                    "DELETE FROM $dbName WHERE $conditionString"
                );

                foreach ($conditions as $col => $value) {
                    $stmt->bindValue(":$col", $value);
                }

                $stmt->execute();
                echo "Data deleted successfully.";
            } catch (Exception $e) {
                $error = "Hiba történt a művelet során: " . $e->getMessage();
                echo json_encode($error);
            }
            break;
    }

}

dataToHandleInDb($conn, $dataToHandleInDb);