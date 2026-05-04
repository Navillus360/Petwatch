<?php

use App\models\SightingDataSet;

require_once __DIR__ . '/../Models/SightingDataSet.php';
session_start();
header("Content-Type: application/json");
$headers = getallheaders();
$csrfToken = $headers['X-CSRF-Token'] ?? '';

if (!$csrfToken || $csrfToken !== $_SESSION['csrf_token']) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Invalid CSRF token"]);
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$sightingId = isset($data['id']) ? (int)$data['id'] : 0;

if (!$sightingId) {
    echo json_encode(['success' => false]);
    exit;
}

$dataSet = new SightingDataSet();
$deleted = $dataSet->deleteSighting($sightingId);

echo json_encode(['success' => $deleted]);
exit;