<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mail
{
    public static function send($title, $fullname, $email, $content)
    {
        $config =  (object)require_once(APP_DIR."/config/mailer.php");

        $mail = new PHPMailer(true);

        try {
            #$mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->isSMTP();
            $mail->Host = $config->host;
            $mail->Port = $config->port;
            $mail->SMTPAuth = $config->smtpauth;
            $mail->Username = $config->username;
            $mail->Password = $config->password;
            #Enable implicit TLS encryption
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            //Recipients
            $mail->setFrom($config->username, Config::get()->title." - ".$title);
            $mail->addAddress($email, $fullname);
            $mail->CharSet = "utf-8";

            //Content
            $mail->isHTML(true); //Set email format to HTML
            $mail->Subject = $title;
            $mail->Body = $content;
            $mail->send();
            
            return true;

        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        return false;
    }
}

?>