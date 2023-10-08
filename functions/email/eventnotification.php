<?php
require_once('../../inc/conn.php');
require_once('../../functions/getter/getevents.php');
require_once('../../functions/email/emailsender.php');
require('../../PHPMailer/vendor/autoload.php');


//Mikori eseményekről jöjjön értesítés, hány nap múlva esedékes eseményről?
$daysRemainBeforeStartEvent = 1;

$dateTime = new DateTime("now", new DateTimeZone('Europe/Budapest'));
$now = $dateTime->format('Y-m-d H:i:s');
$dateTime->modify('+' . $daysRemainBeforeStartEvent . ' days');
$dateWithRemainDay = $dateTime->format('Y-m-d H:i:s');

//Adatok begyűjtése
$events = fetchEventsFromDatabase($conn, $dateWithRemainDay, $now);
//print_r($events);

//Új adatszerkezet létrehozása
$responsibles = [];

if ($events) {
    foreach ($events as $event) {
        $date = new DateTime($event['start']);
        $formedDate = $date->format("Y-m-d");
        foreach ($event['responsibles'] as $key => $responsibleName) {
            $uniqueKey = $event['responsiblesIds'][$key];
            if ($uniqueKey) {
                $responsibles[$uniqueKey][] = [
                    'event' => $event['name'],
                    'name' => $responsibleName,
                    'responsiblesEmail' => $event['responsiblesEmails'][$key],
                    'responsiblesIds' => $event['responsiblesIds'][$key],
                    'start' => $formedDate,
                    'condominiumName' => $event['condominiumName']
                ];
            } else {
                $error['error'] = 'Nincsen egy feladahtoz sem felelős hozzárendelve';
                return false;
            }
        }
    }
} else {
    $error['error'] = 'Jelenleg nincsennek feladatok';
    return false;
}


//echo json_encode($responsibles);
//print_r($responsibles);

//TODO úgy kell megcsinálni, hogy egy emailben több feladatot is fel tudjunk sorolni.
//Ehhez egy táblázatot célszerű beilleszteni az email templatebe, aminek sorait és adatait PHP-val generáljuk ki


if ($responsibles) {
    foreach ($responsibles as $key => $responsible) {
        $table = '';
        $numberOfTask = 1;
        $taskIndex = 0;
        $email = '';
        foreach ($responsible as $index => $event) {
            $taskIndex = $index;
            $email = $event['responsiblesEmail'];
            $emailTemplate = file_get_contents('../../templates/email/eventnotification.html');
            $emailTemplate = str_replace("%firstname%", $event['name'], $emailTemplate);
            $emailTemplate = str_replace("%condominiums name%", $event['condominiumName'], $emailTemplate);

            if ($key = $event['responsiblesIds']) {
                $table .= '
                <tr>
                <td style="border: 1px solid #888;" scope="row">' . $numberOfTask . '</td>
                <td style="border: 1px solid #888;">' . $event['event'] . '</td>
                <td style="border: 1px solid #888;">' . $event['start'] . '</td>
                </tr>
                ';
            } else {
                $table = '
                    <tr>
                    <td style="border: 1px solid #888;" scope="row">' . $numberOfTask . '</td>
                    <td style="border: 1px solid #888;">' . $event['event'] . '</td>
                    <td style="border: 1px solid #888;">' . $event['start'] . '</td>
                    </tr>
                    ';
            }

            $numberOfTask++;

            $emailTemplate = str_replace("%table%", $table, $emailTemplate);

            $email = $event['responsiblesEmail'];
        }

        $result = emailSender('noreply@tarsashaz-fustike.hu', $event['condominiumName'], 'noreply@tarsashaz-fustike.hu', 'Fustike3537', $email, 'Közelgő esemény', $emailTemplate);
        print_r($result);

    }

} else {
    echo "nincs kinek email küldeni";
}




?>