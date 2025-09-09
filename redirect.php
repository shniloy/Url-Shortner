<?php
// Supabase credentials
const SUPABASE_URL = '#';
const SUPABASE_ANON_KEY = '#';

// Get the short code from the URL query parameter
if (!isset($_GET['code'])) {
    http_response_code(404);
    echo "URL not found.";
    exit();
}

$shortCode = $_GET['code'];

// cURL setup to fetch the URL from Supabase
$url = SUPABASE_URL . '/rest/v1/urls?short_code=eq.' . urlencode($shortCode);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'apikey: ' . SUPABASE_ANON_KEY,
    'Authorization: Bearer ' . SUPABASE_ANON_KEY
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    $data = json_decode($response, true);
    if (!empty($data)) {
        $longUrl = $data[0]['long_url'];
        // Perform a server-side redirect
        header("Location: " . $longUrl);
        exit();
    }
}

// If the code is not found or an error occurred
http_response_code(404);
echo "URL not found.";
exit();
