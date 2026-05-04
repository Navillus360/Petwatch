<?php

use App\models\SightingDataSet;

header('Content-Type: application/json');
require_once __DIR__ . '/../Models/SightingDataSet.php';
$sightingDataSet = new SightingDataSet();
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$term = isset($_GET['term']) ? trim($_GET['term']) : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 200;
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$sightings = $sightingDataSet->getSightings($term, $status, $sort, $limit, $offset);
$total = $sightingDataSet->getTotalSightings($term, $status);
echo json_encode([
    'sightings' => $sightings,
    'total' => $total
]);
exit;