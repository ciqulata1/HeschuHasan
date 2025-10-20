<?php
// -------------------- 1) HTTPS erzwingen --------------------
if (
    empty($_SERVER['HTTPS']) ||
    $_SERVER['HTTPS'] === 'off'
) {
    $httpsUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("Location: $httpsUrl", true, 301);
    exit; // Achtung: POST geht bei Redirect verloren. Am besten Formular direkt per HTTPS absenden
}

// -------------------- 2) Nur POST verarbeiten --------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Dieses Skript darf nur über das Kontaktformular aufgerufen werden.";
    exit;
}

// -------------------- 3) Honeypot-Spamschutz --------------------
if (!empty($_POST['_honey'])) {
    $back = $_SERVER['HTTP_REFERER'] ?? 'kontakt.php';
    header("Location: {$back}?status=spam");
    exit;
}

// -------------------- 4) Formulardaten einlesen --------------------
$emailRaw   = $_POST['email'] ?? '';
$subjectRaw = $_POST['subject'] ?? '';
$messageRaw = $_POST['message'] ?? '';

// Minimalvalidierung
if (empty($emailRaw) || empty($subjectRaw) || empty($messageRaw)) {
    $back = $_SERVER['HTTP_REFERER'] ?? 'kontakt.php';
    header("Location: {$back}?status=missing");
    exit;
}

// E-Mail validieren
if (!filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
    $back = $_SERVER['HTTP_REFERER'] ?? 'kontakt.php';
    header("Location: {$back}?status=bademail");
    exit;
}

// Sicheres HTML für Speicherung / Ausgabe
$email   = htmlspecialchars(trim($emailRaw), ENT_QUOTES, 'UTF-8');
$subject = htmlspecialchars(trim($subjectRaw), ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars(trim($messageRaw), ENT_QUOTES, 'UTF-8');
$timestamp = date('Y-m-d H:i:s');

// -------------------- 5) IP-Adresse ermitteln --------------------
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ip = trim($parts[0]);
}

// -------------------- 6) Daten in submissions.txt speichern --------------------
$entry = "Zeit: $timestamp\nEmail: $email\nBetreff: $subject\nNachricht: $message\nIP: $ip\n---\n";
$storeFile = __DIR__ . '/submissions.txt';
$ok = false;
try {
    $res = @file_put_contents($storeFile, $entry, FILE_APPEND | LOCK_EX);
    if ($res !== false) $ok = true;
} catch (Exception $e) {
    $ok = false;
}

// -------------------- 7) Mail an Admin --------------------
$adminEmail = 'Heschu.hasan@fdp-freiburg.de';
$mailBody = "Neue Formular-Eingabe:\n\n$entry";
$mailHeaders = "From: $email\r\n";
@mail($adminEmail, "Kontaktformular: $subject", $mailBody, $mailHeaders);

// -------------------- 8) Redirect mit Feedback --------------------
$back = $_SERVER['HTTP_REFERER'] ?? 'kontakt.php';
if ($ok) {
    header("Location: {$back}?status=ok");
    exit;
} else {
    header("Location: {$back}?status=error");
    exit;
}
