<?php

use App\models\PetDataSet;

session_start();
require_once __DIR__ . ('/../app/models/PetDataSet.php');
$petDataSet = new PetDataSet();
$editingPetID = $_POST['editPet'] ?? null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['addPetBtn'])) {
        $name = is_string(trim($_POST['name']));
        $species = is_string(trim($_POST['species']));
        $breed = is_string(trim($_POST['breed']));
        $color = is_string(trim($_POST['color']));
        $description = is_string(trim($_POST['description']));
        $status = ($_POST['status'] === 'lost') ? 'lost' : 'found';
        $uploadDirectory = __DIR__ . ('/public/images/uploads/');
        $photoFileName = 'placeholder.jpg';
        if ($_FILES['photo_url']['error'] === UPLOAD_ERR_OK) {
            $photoTmp = $_FILES['photo_url']['tmp_name'];
            $photoName = $_FILES['photo_url']['name'];
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $allowedMimeTypes = ['image/jpeg', 'image/png'];
            $extension = strtolower(pathinfo($photoName, PATHINFO_EXTENSION));
            $mimeType = mime_content_type($photoTmp);
            if (in_array($extension, $allowedExtensions) && in_array($mimeType, $allowedMimeTypes)) {
                $safePetName = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($name));
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
                break;
            case 'delete':
                $petDataSet->DeletePet((int)$_POST['petID']);
                break;
        }
    }
}
require_once __DIR__ . "/../app/views/my_pets.phtml";