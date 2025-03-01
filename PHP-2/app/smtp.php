<?php
// Define recipient, subject, message, and headers
$to = 'teplov.vladimir.2004@mail.ru';
$subject = 'Test Mail';
$message = 'Hello, this is a test mail from PHP script.';
$headers = 'From: sender@example.com' . "\r\n" .
           'Reply-To: sender@example.com' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();
 
// Send the email
if(mail($to, $subject, $message, $headers)) {
    echo "Mail sent successfully!";
} else {
    echo "Failed to send mail.";
}