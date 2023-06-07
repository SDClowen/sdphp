<?php 
    return [
        # Set the SMTP server to send through
        "host" => "",
        
        #TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        "port" => 587,

        # SMTP username
        "username" => "",

        #SMTP password
        "password" => "",

        #Enable SMTP authentication
        "smtpauth" => true
    ];