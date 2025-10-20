<?php
// Spamschutz: Honeypot auslesen
if (!empty($_POST['_honey'])) {
    die("Spam erkannt. Nachricht wurde nicht gesendet.");
}

// Benutzer-IP ermitteln (achtet auf Proxy)
$ip = $_SERVER['REMOTE_ADDR'];
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
}

// Formulardaten empfangen
$email = htmlspecialchars($_POST['email'] ?? '');
$subject = htmlspecialchars($_POST['subject'] ?? '');
$message = htmlspecialchars($_POST['message'] ?? '');
$timestamp = date('Y-m-d H:i:s');

// Optional: alles in einer Textdatei speichern
$line = "Zeit: $timestamp\nEmail: $email\nBetreff: $subject\nNachricht: $message\nIP: $ip\n---\n";
file_put_contents('submissions.txt', $line, FILE_APPEND | LOCK_EX);

// Optional: Mail an Admin (funktioniert, wenn Mailserver konfiguriert ist)
$to = 'deine@adresse.de'; // hier deine Mail eintragen
$body = "Neue Formular-Eingabe:\n\n$line";
$headers = "From: $email\r\n";
@mail($to, "Kontaktformular: $subject", $body, $headers);

// Rückmeldung an den Nutzer
echo "<h3>Vielen Dank für Ihre Nachricht!</h3>";
echo "<p>Ihre Nachricht wurde erfolgreich gesendet.</p>";
echo "<p>Ihre IP-Adresse: $ip</p>";
?>
