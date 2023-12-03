<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function emailSender($fromEmail, $fromName, $username, $password, $email, $subject, $emailTemplate)
{
    // $response = array(
    //     'fromEmail' => $fromEmail,
    //     'fromName' => $fromName,
    //     'username' => $username,
    //     'password' => $password,
    //     'email' => $email,
    //     'subject' => $subject
    // );

    $mail = new PHPMailer(true);
    try {

        //Server settings
        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug = 0; //Enable verbose debug output
        $mail->isSMTP(); //Send using SMTP
        $mail->Host = 'mail.nethely.hu'; //Set the SMTP server to send through
        $mail->SMTPAuth = true; //Enable SMTP authentication
        $mail->Username = $username; //SMTP username
        $mail->Password = $password; //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; //Enable implicit TLS encryption
        $mail->Port = 465; //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Sender
        $mail->setFrom($fromEmail, $fromName);


        //Attachments
        //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

        //Content
        $mail->isHTML(true); //Set email format to HTML
        $mail->Subject = $subject;

        $mail->ClearAllRecipients();
        $mail->AddAddress($email);
        $mail->AddBCC('martonjanos1990@gmail.com');

        $mail->Body = $emailTemplate;
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        $response = 'Message has been sent to '.$email;
        return $response;
    } catch (Exception $e) {
        $response = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        return $response;
    }
}
?>