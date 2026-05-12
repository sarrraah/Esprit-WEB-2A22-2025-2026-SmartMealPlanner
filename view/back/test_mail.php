<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../_project_files/vendor/autoload.php';
require_once __DIR__ . '/../../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load mail credentials from .env
$_envPath = __DIR__ . '/../../_project_files/config';
if (file_exists($_envPath . '/.env')) {
    $lines = file($_envPath . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$key, $val] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
        }
    }
}
define('GMAIL_USER', $_ENV['SMTP_USER'] ?? '');
define('GMAIL_PASS', $_ENV['SMTP_PASS'] ?? '');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    $mail->SMTPDebug  = 2; // Show full debug output
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = GMAIL_USER;
    $mail->Password   = GMAIL_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(GMAIL_USER, 'Smart Meal Planner Test');
    $mail->addAddress(GMAIL_USER); // send to self

    $mail->isHTML(true);
    $mail->Subject = '✅ Test Email — Smart Meal Planner';
    $mail->Body    = '<h2>Test réussi !</h2><p>L\'envoi d\'email fonctionne correctement.</p>';
    $mail->AltBody = 'Test email - Smart Meal Planner';

    $mail->send();
    echo '<h2 style="color:green">✅ Email envoyé avec succès !</h2>';
    echo '<p>Vérifie ta boîte Gmail : <strong>' . GMAIL_USER . '</strong></p>';
} catch (Exception $e) {
    echo '<h2 style="color:red">❌ Erreur d\'envoi</h2>';
    echo '<pre>' . htmlspecialchars($mail->ErrorInfo) . '</pre>';
}
