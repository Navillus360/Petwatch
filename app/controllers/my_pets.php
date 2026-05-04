<?php

namespace App\controllers;

use App\models\PetDataSet;
use App\models\SightingDataSet;
use finfo;

require_once __DIR__ . ('/../models/PetDataSet.php');
require_once __DIR__ . ('/../models/SightingDataSet.php');

$petDataSet = new PetDataSet();
$editingPetID = $_GET['editPet'] ?? null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addPetBtn'])) {
    csrf_validate();
    $name = cleanInput($_POST['name']);
    $species = cleanInput($_POST['species']);
    $breed = cleanInput($_POST['breed']);
    $color = cleanInput($_POST['color']);
    $description = cleanInput($_POST['description'], 1000);
    $uploadDirectory = __DIR__ . '/../../public/images/uploads/';
    $photoFileName = null;
    if ($_FILES['photo_url']['error'] === UPLOAD_ERR_OK) {
        $photoTmp = $_FILES['photo_url']['tmp_name'];
        if (!is_uploaded_file($photoTmp)) $photoFileName = null;
        else {
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
                if (!move_uploaded_file($photoTmp, $targetFile)) $photoFileName = null;
            }
        }
    }
    $petDataSet->addPet($name, $species, $breed, $color, $photoFileName, $description);
}
if (isset($_POST['action'], $_POST['petID'])) {
    switch ($_POST['action']) {
        case 'found':
            csrf_validate();
            $petDataSet->changePetStatus((int)$_POST['petID'], 'found');
            break;
        case 'lost':
            csrf_validate();
            $petDataSet->changePetStatus((int)$_POST['petID'], 'lost');
            $latitude = cleanInput($_POST['lat']);
            $longitude = cleanInput($_POST['long']);
            $description = cleanInput($_POST['description'], 200);
            $petDataSet->reportMissingPet((int)$_POST['petID'], $description, $latitude, $longitude);
            break;
        case 'delete':
            csrf_validate();
            $petDataSet->DeletePet((int)$_POST['petID']);
            break;
        case 'update':
            csrf_validate();
            checkPetStatus($_POST['petID'], $petDataSet);
            break;
    }
}

function checkPetStatus($petID, $petDataSet)
{
    $pet = $petDataSet->getPetStatusById($petID);
    if ($pet->getStatus() === 'lost') {
        die("You cannot edit a pet while it is marked as lost.");
    } else {
        $name = cleanInput($_POST['name']);
        $species = cleanInput($_POST['species']);
        $breed = cleanInput($_POST['breed']);
        $color = cleanInput($_POST['color']);
        $description = cleanInput($_POST['description'], 200);
        $petDataSet->UpdatePetDetails((int)$_POST['petID'], $name, $species, $breed, $color, $description);
    }
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

$userPets = $petDataSet->getUserPets() ?? [];

