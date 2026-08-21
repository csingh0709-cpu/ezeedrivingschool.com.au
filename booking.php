<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

function clean(string $value): string {
    return trim(str_replace(["\r", "\n"], ' ', $value));
}

$name     = clean($_POST['name'] ?? '');
$phone    = clean($_POST['phone'] ?? '');
$emailRaw = trim($_POST['email'] ?? '');
$email    = filter_var($emailRaw, FILTER_VALIDATE_EMAIL);
$suburb   = clean($_POST['suburb'] ?? '');
$service  = clean($_POST['service'] ?? '');
$date     = clean($_POST['date'] ?? '');
$time     = clean($_POST['time'] ?? '');
$duration = clean($_POST['duration'] ?? '');
$message  = trim($_POST['message'] ?? '');

if ($name === '' || $phone === '' || !$email || $suburb === '' || $service === '' || $date === '' || $time === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please complete all required booking details.']);
    exit;
}

$adminEmail = 'ezeedrivingschool@yahoo.com';
$fromEmail  = 'no-reply@ezeedrivingschool.com.au';

$adminSubject = 'New Booking Request - Ezee Driving School';
$adminBody =
"NEW BOOKING REQUEST\n\n" .
"Name: {$name}\n" .
"Phone: {$phone}\n" .
"Email: {$email}\n" .
"Suburb: {$suburb}\n" .
"Service: {$service}\n" .
"Preferred Date: {$date}\n" .
"Preferred Time: {$time}\n" .
"Duration: {$duration}\n\n" .
"Client Message:\n{$message}\n";

$adminHeaders = implode("\r\n", [
    'From: Ezee Driving School Website <' . $fromEmail . '>',
    'Reply-To: ' . $email,
    'Content-Type: text/plain; charset=UTF-8'
]);

$clientSubject = 'We received your booking request - Ezee Driving School';
$clientBody =
"Dear {$name},\n\n" .
"Thank you for choosing Ezee Driving School.\n\n" .
"Your booking request has been received successfully. We will review your preferred lesson date and time and send your booking confirmation shortly.\n\n" .
"Booking request details:\n" .
"Service: {$service}\n" .
"Preferred Date: {$date}\n" .
"Preferred Time: {$time}\n" .
"Duration: {$duration}\n\n" .
"Please note that this email confirms receipt of your request only. Your lesson is confirmed once Ezee Driving School sends you a final booking confirmation.\n\n" .
"If you need assistance, please call 0425 635 087.\n\n" .
"Kind regards,\n" .
"Ezee Driving School\n" .
"0425 635 087\n" .
"ezeedrivingschool@yahoo.com\n" .
"www.ezeedrivingschool.com.au\n";

$clientHeaders = implode("\r\n", [
    'From: Ezee Driving School <' . $fromEmail . '>',
    'Reply-To: ' . $adminEmail,
    'Content-Type: text/plain; charset=UTF-8'
]);

$adminSent  = mail($adminEmail, $adminSubject, $adminBody, $adminHeaders);
$clientSent = mail($email, $clientSubject, $clientBody, $clientHeaders);

if ($adminSent && $clientSent) {
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(500);
echo json_encode([
    'success' => false,
    'message' => 'The server could not send the booking emails.'
]);
