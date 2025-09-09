<?php
header('Content-Type: application/json');

// Supabase credentials
const SUPABASE_URL = '#';
const SUPABASE_ANON_KEY = '#';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['longUrl'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid request.']);
    exit();
}

$longUrl = trim($_POST['longUrl']);

if (empty($longUrl) || !filter_var($longUrl, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(['message' => 'The URL is invalid. Make sure it includes http:// or https://']);
    exit();
}

// Generate a random short code
$shortCode = substr(md5(uniqid(rand(), true)), 0, 6);

$url = SUPABASE_URL . '/rest/v1/urls';
$data = json_encode([
    'long_url' => $longUrl,
    'short_code' => $shortCode
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'apikey: ' . SUPABASE_ANON_KEY,
    'Authorization: Bearer ' . SUPABASE_ANON_KEY
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    echo json_encode(['shortCode' => $shortCode]);
} else {
    $errorData = json_decode($response, true);
    http_response_code($httpCode);
    echo json_encode(['message' => $errorData['message'] ?? 'Failed to save URL.']);
}
