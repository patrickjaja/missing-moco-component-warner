<?php
/**
 * Find and send missing Komponents in Moco (Helper functionality)
 */
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

$curl = curl_init();

// smpt
$sUn = 'xxxxx@xxxxxxx.com';
$sH = 'xxxxxxxx';

// Moco app url
$userId = 00000000; //Moco UserID
$sender = 'xxxxx@xxx.com';
$from = date('Y-m-01');
$to = date('Y-m-d', strtotime('last day of this month'));
$url = 'https://xxxxxxxx.mocoapp.com/api/v1/activities?from='.$from.'&to='.$to.'&user_id='.$userId;
$token = 'xxxxxxxxxxxxxxxxx';
$taskId = 0000000000; //Sprint-Minute
$taskName = 'xxxxxx-xxxxx';

// set url
curl_setopt($curl, CURLOPT_URL, $url);

//return the transfer as a string
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

// set header
curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Token token='.$token]);

// $output contains the output string
$json = curl_exec($curl);

$entries = json_decode($json, true);
foreach ($entries as $entryKey => $entry) {
    if ($entry['task']['id']!==$taskId) {
        unset($entries[$entryKey]);
    }
}
$unmappedEntries = array_values($entries);

$mailText = 'Hallo, wir brauchen folgende Komponenten angelegt: <ul>';
$sendMail = false;
foreach ($unmappedEntries as $unmappedEntry) {
    $mailText.='<li>'.$unmappedEntry['description'].'</li>';
    $sendMail = true;
}
$mailText.='</ul>';

if ($sendMail) {
    $mail = new PHPMailer(); // create a new object
    $mail->isSMTP(); // enable SMTP
    $mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
    $mail->SMTPAuth = true; // authentication enabled
    $mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for Gmail
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 465; // or 587
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Username = $sUn;
    $mail->Password = $sH;
    try {
        $mail->setFrom($sender);
    } catch (\PHPMailer\PHPMailer\Exception $e) { }
    $mail->Subject = 'https://xxxxxx.mocoapp.com - Fehlende Komponenten am '.date('d.m.Y');

    $mail->addAddress('xxxxxxxxx@xxxxxx.com');
    $mail->addCC('xxxxxxxxx@xxxxxxxx.com');

    $mailText.='<br/><div style="color: grey;">Diese Liste basiert auf Buchungen die auf '.$taskName.' gebucht sind. (Zeitraum '.date('d.m.Y', strtotime($from)).' bis '.date('d.m.Y', strtotime($to)).')</div>';
    $mail->Body = $mailText;

    try {
        if (!$mail->send()) {
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo 'Message has been sent';
        }
    } catch (\PHPMailer\PHPMailer\Exception $e) { }
}

die();
