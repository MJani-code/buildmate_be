<?php

function fetchEventsFromDatabase($conn, $dateWithRemainDay, $now) {
    try {
        $stmt = $conn->prepare(
            "SELECT
            e.id as 'id',
            e.id_category as 'categoryId',
            ec.description as 'category',
            e.title as 'title',
            e.start_event as 'start',
            e.end_event as 'end',
            e.created_by as 'createdBy',
            ec.color as 'color',
            er.event_id as 'event_id',
            u.stair_case_flat as 'flat',
            u.email as 'email',
            u.first_name as 'firstName',
            er.responsible_user_id as 'responsiblesIds',
            c.name as 'condominiumName'
            FROM events e
            LEFT JOIN events_categories ec on ec.id = e.id_category
            LEFT JOIN events_responsibles er on er.event_id = e.id
            LEFT JOIN users_dev u on u.id = er.responsible_user_id
            LEFT JOIN condominiums c on c.id = u.id_condominiums
            where e.deleted = 0 AND e.start_event < '$dateWithRemainDay' AND e.start_event > '$now'
        ");

        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['result'] = array();

        if ($result) {
            $events = array();
            foreach ($result as $row) {
                $eventId = $row['id'];

                if (!isset($events[$eventId])) {
                    $events[$eventId] = array(
                        'id' => $row['id'],
                        'categoryId' => $row['categoryId'],
                        'category' => $row['category'],
                        'name' => $row['title'],
                        'start' => $row['start'],
                        'end' => $row['end'],
                        'timed' => true,
                        'createdBy' => $row['createdBy'],
                        'color' => $row['color'],
                        'flat' => $row['flat'],
                        'responsibles' => array(),
                        'responsiblesEmails' => array(),
                        'responsiblesIds' => array(),
                        'condominiumName' => $row['condominiumName']
                    );
                }

                $events[$eventId]['responsibles'][] = $row['firstName'];
                $events[$eventId]['responsiblesEmails'][] = $row['email'];
                $events[$eventId]['responsiblesIds'][] = $row['responsiblesIds'];
            }

            $response['result'] = array_values($events);
            return $response['result'];
        }

    } catch (Exception $e) {
        $error = "Hiba történt a művelet során: " . $e->getMessage();
        echo json_encode($error);
    }
}
