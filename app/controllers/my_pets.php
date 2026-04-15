<?php

namespace App\controllers;

use App\models\PetDataSet;
use App\models\SightingDataSet;
use finfo;

require_once __DIR__ . ('/../models/PetDataSet.php');
require_once __DIR__ . ('/../models/SightingDataSet.php');

$petDataSet = new PetDataSet();
$sightingDataSet = new SightingDataSet();
$editingPetID = $_GET['editPet'] ?? null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addPetBtn'])) {
    $name = cleanInput($_POST['name']);
    $species = cleanInput($_POST['species']);
    $breed = cleanInput($_POST['breed']);
    $color = cleanInput($_POST['color']);
    $description = cleanInput($_POST['description'], 1000);
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
    $petDataSet->addPet($name, $species, $breed, $color, $photoFileName, $description);
}
if (isset($_POST['action'], $_POST['petID'])) {
    switch ($_POST['action']) {
        case 'found':
            $petDataSet->changePetStatus((int)$_POST['petID'], 'found');
            break;
        case 'lost':
            $petDataSet->changePetStatus((int)$_POST['petID'], 'lost');
            $latitude = cleanInput($_POST['lat']);
            $longitude = cleanInput($_POST['long']);
            $description = cleanInput($_POST['description'], 200);
            $petDataSet->reportMissingPet((int)$_POST['petID'], $description, $latitude, $longitude);
            break;
        case 'delete':
            $petDataSet->DeletePet((int)$_POST['petID']);
            break;
        case 'update':
            $name = cleanInput($_POST['name']);
            $species = cleanInput($_POST['species']);
            $breed = cleanInput($_POST['breed']);
            $color = cleanInput($_POST['color']);
            $description = cleanInput($_POST['description'], 200);
            $petDataSet->UpdatePetDetails((int)$_POST['petID'], $name, $species, $breed, $color, $description );
            break;
    }
}

function cleanInput(string $value, int $maxLength = 255): string
{
    $value = trim($value);
    $value = strip_tags($value);
    return substr($value, 0, $maxLength);
}

$userPets = $petDataSet->getUserPets() ?? [];

