<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$email = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);

if (!$email) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
    exit();
}

$leadDir = __DIR__ . '/../vault_data';
if (!is_dir($leadDir)) {
    mkdir($leadDir, 0750, true);
    file_put_contents($leadDir . '/.htaccess', "Order Deny,Allow\nDeny from all\n");
}

$leadFile = $leadDir . '/leads_somatic_vault.csv';
$isNew = !file_exists($leadFile);

$fp = fopen($leadFile, 'a');
if ($fp) {
    if ($isNew) {
        fputcsv($fp, ['Timestamp', 'Email', 'Source', 'IP', 'UserAgent']);
    }
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $source = $data['source'] ?? 'flow.ompresent.com';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    fputcsv($fp, [date('Y-m-d H:i:s'), $email, $source, $ip, $ua]);
    fclose($fp);
}

echo json_encode([
    'status' => 'success',
    'message' => 'Access granted to uncompressed 24-bit clinical vault.',
    'download_url' => '/vault/THE_1_PAGE_VAGUS_DESK_PROTOCOL_OMPRESENT.pdf',
    'audio_vault_link' => 'https://assets.ompresent.org/vault/OMPRESENT_FLOW_EP02_SATIE_24BIT_FLAC_VAULT.zip'
]);
