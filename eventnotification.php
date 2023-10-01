<?php
require_once('inc/conn.php');
require_once('functions/getter/getevents.php');
require_once('PHPMailer/vendor/autoload.php');


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

//Mikori eseményekről jöjjön értesítés, hány nap múlva esedékes eseményről?
$daysRemainBeforeStartEvent = 1;

$dateTime = new DateTime("now", new DateTimeZone('Europe/Budapest'));
$dateTime->modify('+' . $daysRemainBeforeStartEvent . ' days');
$now = $dateTime->format('Y-m-d H:i:s');

//Adatok begyűjtése
$events = fetchEventsFromDatabase($conn, $now);
//print_r($events);

//Új adatszerkezet létrehozása
$responsibles = [];


foreach ($events as $event) {
    $date = new DateTime($event['start']);
    $formedDate = $date->format("Y-m-d");
    foreach ($event['responsibles'] as $key => $responsibleName) {
        $uniqueKey = $event['responsiblesIds'][$key];
        $responsibles[$uniqueKey][] = [
            'event' => $event['name'],
            'name' => $responsibleName,
            'responsiblesEmail' => $event['responsiblesEmails'][$key],
            'start' => $formedDate,
        ];
    }
}


echo json_encode($responsibles);

//TODO úgy kell megcsinálni, hogy egy emailben több feladatot is fel tudjunk sorolni.
//Ehhez egy táblázatot célszerű beilleszteni az email templatebe, aminek sorait és adatait PHP-val generáljuk ki



// foreach ($events as $event) {
//     $date = new DateTime($event['start']);
//     $formedDate = $date->format("Y-m-d");

//     foreach ($event['responsibles'] as $key => $responsibleName) {
//         $responsibles[$responsibleName][] = [
//             'event' => $event['name'],
//             'name' => $responsibleName,
//             'responsiblesEmail' => $event['responsiblesEmails'][$key],
//             'start' => $formedDate,
//         ];
//     }
// }


// foreach ($responsibles as $key => $responsible) {
//     //Email sablon betöltése
//     $emailTemplate = file_get_contents('templates/email/eventnotification.html');

//     $emailTemplate = str_replace("%firstname%", $responsible['name'], $emailTemplate);
//     $emailTemplate = str_replace("%DATE%", $responsible['start'], $emailTemplate);
//     $emailTemplate = str_replace("%title%", $responsible['event'], $emailTemplate);

//     $email = $responsible['responsiblesEmail'];

//echo $emailTemplate;

// try {

//     //Server settings
//     $mail->CharSet = 'UTF-8';
//     $mail->SMTPDebug = 0;                      //Enable verbose debug output
//     $mail->isSMTP();                                            //Send using SMTP
//     $mail->Host       = 'mail.nethely.hu';                     //Set the SMTP server to send through
//     $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
//     $mail->Username   = 'noreply@tarsashaz-fustike.hu';                     //SMTP username
//     $mail->Password   = 'Fustike3537';                               //SMTP password
//     $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
//     $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

//     //Sender
//     $mail->setFrom('noreply@tarsashaz-fustike.hu', 'Társasház');


//     //Attachments
//     //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
//     //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

//     //Content
//     $mail->isHTML(true);                                  //Set email format to HTML
//     $mail->Subject = 'Közelgő Társasház esemény!';

//     $mail->ClearAllRecipients();
//     $mail->AddAddress($email);
//     $mail->AddBCC('martonjanos1990@gmail.com');

//     $mail->Body = $emailTemplate;
//     $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

//     $mail->send();
//     echo 'Message has been sent';
// } catch (Exception $e) {
//     echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
// }
//}

?>