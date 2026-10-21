<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html');
    exit;
}

// Honeypot: silently drop likely spam submissions
if (!empty($_POST['website'])) {
    header('Location: contact-thanks.html');
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
    header('Location: contact.html?error=1');
    exit;
}

mb_language('Japanese');
mb_internal_encoding('UTF-8');

$to = 'kazuoikeda0820@gmail.com,k.keda@bernal-inc.com';
$subject = '【Bernal】お問い合わせ: ' . mb_substr($name, 0, 50);

$body = "Bernalサイトのお問い合わせフォームより送信されました。\n\n";
$body .= "お名前: {$name}\n";
$body .= "会社名: " . ($company !== '' ? $company : '(未入力)') . "\n";
$body .= "メールアドレス: {$email}\n\n";
$body .= "お問い合わせ内容:\n{$message}\n";

$headers = "From: no-reply@bernal-inc.com\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8";

$sent = mb_send_mail($to, $subject, $body, $headers);

if ($sent) {
    header('Location: contact-thanks.html');
} else {
    header('Location: contact.html?error=1');
}
exit;
