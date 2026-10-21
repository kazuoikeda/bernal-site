<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html');
    exit;
}

$lang = (($_POST['lang'] ?? '') === 'en') ? 'en' : 'ja';
$contactPage = $lang === 'en' ? 'en/contact.html' : 'contact.html';
$thanksPage = $lang === 'en' ? 'en/contact-thanks.html' : 'contact-thanks.html';

// Honeypot: silently drop likely spam submissions
if (!empty($_POST['website'])) {
    header('Location: ' . $thanksPage);
    exit;
}

function clean_field(string $value): string {
    $value = trim($value);
    return str_replace(["\r", "\n"], '', $value);
}

$name = clean_field($_POST['name'] ?? '');
$company = clean_field($_POST['company'] ?? '');
$email = clean_field($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . $contactPage . '?error=1');
    exit;
}

mb_internal_encoding('UTF-8');

$to = 'kazuoikeda0820@gmail.com,k.keda@bernal-inc.com';

if ($lang === 'en') {
    $subject = '[Bernal] Contact form: ' . mb_substr($name, 0, 50);
    $body  = "A new message was submitted via the Bernal website contact form.\n\n";
    $body .= "Name: {$name}\n";
    $body .= "Company: " . ($company !== '' ? $company : '(not provided)') . "\n";
    $body .= "Email: {$email}\n\n";
    $body .= "Message:\n{$message}\n";
} else {
    $subject = '【Bernal】お問い合わせ: ' . mb_substr($name, 0, 50);
    $body  = "Bernalサイトのお問い合わせフォームより送信されました。\n\n";
    $body .= "お名前: {$name}\n";
    $body .= "会社名: " . ($company !== '' ? $company : '(未入力)') . "\n";
    $body .= "メールアドレス: {$email}\n\n";
    $body .= "お問い合わせ内容:\n{$message}\n";
}

$encodedSubject = mb_encode_mimeheader($subject, 'UTF-8', 'B', "\r\n");

$headers = "From: no-reply@bernal-inc.com\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "Content-Transfer-Encoding: base64";

$encodedBody = chunk_split(base64_encode($body));

$sent = mail($to, $encodedSubject, $encodedBody, $headers);

if ($sent) {
    header('Location: ' . $thanksPage);
} else {
    header('Location: ' . $contactPage . '?error=1');
}
exit;
