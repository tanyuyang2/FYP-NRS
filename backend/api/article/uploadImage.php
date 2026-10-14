<?php
require __DIR__ . "/../../config/cors.php";
require __DIR__ . "/../../config/database.php";
require __DIR__ . "/../utils/requireUser.php";
require __DIR__ . "/../../config/cloudinary.php";

if (!isset($_FILES["upload"])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "No image uploaded"
    ]);
    exit;
}

$file = $_FILES["upload"];

if ($file["error"] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Image upload failed"
    ]);
    exit;
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
$mimeType = mime_content_type($file["tmp_name"]);

if (!in_array($mimeType, $allowedTypes, true)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Unsupported image file type"
    ]);
    exit;
}

$folder = "FYP-NRS/articles";
$timestamp = time();

$paramsToSign = "folder={$folder}&timestamp={$timestamp}{$apiSecret}";
$signature = sha1($paramsToSign);

$ch = curl_init("https://api.cloudinary.com/v1_1/{$cloudName}/image/upload");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'file'      => new CURLFile($file['tmp_name']),
    'api_key'   => $apiKey,
    'timestamp' => $timestamp,
    'folder'    => $folder,
    'signature' => $signature,
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    echo json_encode([
        "success" => false,
        "message" => "Cloudinary upload failed"
    ]);
    exit;
}

$data = json_decode($response, true);

if (!isset($data["secure_url"])) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Cloudinary upload failed"
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "url" => $data["secure_url"]
]);