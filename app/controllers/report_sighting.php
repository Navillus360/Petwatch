<?php

namespace App\controllers;

use App\models\SightingDataSet;
use finfo;

require_once __DIR__ . ('/../models/SightingDataSet.php');
$sightingDataSet = new SightingDataSet();
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['addSightingBtn'])) {
    csrf_validate();
    $missingPetID = (int)$_POST['missingPetID'];
    $latitude = cleanInput($_POST['latitude']);
    $longitude = cleanInput($_POST['longitude']);
    $comment = cleanInput($_POST['comment']);
    $uploadDirectory = __DIR__ . '/../../public/images/uploads/';
    $photoFileName = 'placeholder.jpg';
    if (!is_dir($uploadDirectory)) mkdir($uploadDirectory, 0755, true);
    if ($_FILES['photo_url']['error'] === UPLOAD_ERR_OK) {
        $photoTmp = $_FILES['photo_url']['tmp_name'];
        $photoName = $_FILES['photo_url']['name'];
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $allowedMimeTypes = ['image/jpeg', 'image/png'];
        $extension = strtolower(pathinfo($photoName, PATHINFO_EXTENSION));
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($photoTmp);
        if (in_array($extension, $allowedExtensions) && in_array($mimeType, $allowedMimeTypes)) {
            $safePetName = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($name));
            $safePetName = $safePetName ?: 'pet';
            $photoFileName = $safePetName . '_' . time() . '.' . $extension;
            $targetFile = $uploadDirectory . $photoFileName;
            move_uploaded_file($photoTmp, $targetFile);
        }
    }
    $sightingDataSet->addSighting($photoFileName, $missingPetID, $comment, $latitude, $longitude);
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