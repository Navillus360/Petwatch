<?php

namespace App\controllers;

use App\models\PetDataSet;
use App\models\SightingDataSet;
use finfo;

require_once __DIR__ . ('/../models/PetDataSet.php');
require_once __DIR__ . ('/../models/SightingDataSet.php');

$sightingDataSet = new SightingDataSet();
$editingSightingID = $_GET['editSighting'] ?? null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updateSightingBtn'], $_POST['sightingID'])) {
    csrf_validate();
    $comment = cleanInput($_POST['comment']);
    $latitude = cleanInput($_POST['latitude']);
    $longitude = cleanInput($_POST['longitude']);
    $uploadDirectory = __DIR__ . '/../../public/images/uploads/';
    $photoFileName = 'placeholder.jpg';

    if ($_FILES['photo_url']['error'] === UPLOAD_ERR_OK) {
        $photoTmp = $_FILES['photo_url']['tmp_name'];
        $photoName = $_FILES['photo_url']['name'];
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $allowedMimeTypes = ['image/jpeg', 'image/png'];
        $extension = strtolower(pathinfo($photoName, PATHINFO_EXTENSION));
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($photoTmp);
        if (in_array($extension, $allowedExtensions) && in_array($mimeType, $allowedMimeTypes)) {
            $baseName = pathinfo($photoName, PATHINFO_FILENAME);
            $photoFileName = $baseName . '_' . time() . '.' . $extension;
            $targetFile = $uploadDirectory . $photoFileName;
            move_uploaded_file($photoTmp, $targetFile);
        }
    }
    $sightingDataSet->updateSighting($photoFileName, (int)$_POST['sightingID'], $comment, $latitude, $longitude);
}

function cleanInput(string $value, int $maxLength = 255): string
{
    $value = trim($value);
    $value = strip_tags($value);
    return substr($value, 0, $maxLength);
}

function csrf_validate()
{
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(401);
        exit('Invalid CSRF token');
    }
    if (!isset($_SESSION['userID'], $_SESSION['userType'])) {
        http_response_code(401);
        exit('Unauthorized');
    }
}

$userSightings = $sightingDataSet->getUserSightings() ?? [];

