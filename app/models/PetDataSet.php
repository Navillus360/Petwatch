<?php
/**
 * Class PetDataSet
 * This class is designed to hold multiple PetData instances.
 * Which is helpful in the report sighting and my pets page
 * For when we need to find retrieve pets that are missing or the logged-in users pets.
 */

namespace App\models;

use PDO;
use PDOException;

require_once('Database.php');
require_once('PetData.php');

class PetDataSet
{
    /**
     * @return array
     */
    public function getMissingPets(): array
    {
        $db = Database::connect();
        $sqlQuery = "SELECT * FROM pets WHERE status = 'lost';";
        $stmt = $db->prepare($sqlQuery);
        $stmt->execute();
        $missingPets = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $missingPets[] = new PetData($row);
        return $missingPets;
    }

    /**
     * @return array
     */
    public function getUserPets(): array
    {
        $userID = $_SESSION['userID'] ?? null;
        $db = Database::connect();
        $sqlQuery = "SELECT * FROM pets WHERE user_id = $userID;";
        $stmt = $db->prepare($sqlQuery);
        $stmt->execute();
        $userPets = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $userPets[] = new PetData($row);
        return $userPets;
    }

    public function getPetStatusById(int $petID): array
    {
        $db = Database::connect();
        $sqlQuery = "SELECT status FROM pets WHERE pet_id = $petID;";
        $stmt = $db->prepare($sqlQuery);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['status'];
    }

    /**
     * @param $petID
     * @param $status
     * @return void
     * Changes a pet status to lost or found
     */
    public function changePetStatus($petID, $status)
    {
        $db = Database::connect();
        $userID = $_SESSION['userID'];
        $sqlQuery = "UPDATE pets SET status = :status WHERE pet_id = :petID AND user_id = :userID;";
        $stmt = $db->prepare($sqlQuery);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':petID', $petID, PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
    }

    //region Pet Reporting Functions

    /**
     * @param $petID
     * @param $comment
     * @param $latitude
     * @param $longitude
     * @return void
     * Sets the owners pet as missing whilst adding a new sighting of said pet to the database
     */
    public function reportMissingPet($petID, $comment, $latitude, $longitude)
    {
        $db = Database::connect();
        $userID = $_SESSION['userID'];

        //Get the pets photo first
        $getPetPhoto = "SELECT photo_url FROM pets WHERE pet_id = :petID AND user_id = :userID;";
        $stmt = $db->prepare($getPetPhoto);
        $stmt->bindParam(':petID', $petID, PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $photoURL = $row['photo_url'];

        //Add the missing pet sighting to the database
        $sqlQuery = "INSERT INTO sightings(pet_id, user_id, comment, latitude, longitude, photo_url)
        VALUES(:pet_id, :user_id, :comment, :latitude, :longitude, :photoURL);";
        $stmt = $db->prepare($sqlQuery);
        $stmt->bindParam(':pet_id', $petID, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':latitude', $latitude, PDO::PARAM_INT);
        $stmt->bindParam(':longitude', $longitude, PDO::PARAM_INT);
        $stmt->bindParam(':photoURL', $photoURL, PDO::PARAM_STR);
        $stmt->execute();
    }
    //endregion

    //region Add/Delete/Update Pet Functions
    /**
     * @param string $name
     * @param string $species
     * @param string $breed
     * @param string $color
     * @param string $photo_url
     * @param string $description
     * @return bool
     * Adds the new pet to the database with an automatic status of found
     */
    public function addPet(string $name, string $species, string $breed, string $color,
                           string $photo_url, string $description): bool
    {
        $db = Database::connect();
        $userID = $_SESSION['userID'] ?? null;
        $sqlQuery = "INSERT INTO pets(name, species, breed, color, photo_url, status, user_id, description) 
            VALUES ('$name', '$species', '$breed', '$color', '$photo_url', 'found', $userID, '$description');)";
        $stmt = $db->prepare($sqlQuery);
        $stmt->execute();
        return true;
    }

    /**
     * @param int $petID
     * @return bool
     * Deletes the pet alongside any reported sightings of it
     * After a check ensures that the pets exists
     */
    public function DeletePet(int $petID): bool
    {
        $db = Database::connect();

        try {
            $db->beginTransaction();

            if (!isset($_SESSION['userID'])) return false;
            $userID = (int)$_SESSION['userID'];

            //
            $sql = "SELECT photo_url FROM pets WHERE pet_id = :petID AND user_id = :userID;";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':petID' => $petID,
                ':userID' => $userID
            ]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $db->rollBack();
                return false;
            }


            $sql = "DELETE FROM pets WHERE pet_id = :petID AND user_id = :userID;";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':petID' => $petID,
                ':userID' => $userID
            ]);

            if ($stmt->rowCount() === 0) {
                $db->rollBack();
                return false;
            }


            if (!empty($row['photo_url'])) {
                $filePath = __DIR__ . '/../../public/images/uploads/' . $row['photo_url'];

                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $db->commit();
            return true;

        } catch (PDOException $e) {
            $db->rollBack();
            return false;
        }
    }

    public function UpdatePetDetails($petID, $name, $species, $breed, $color, $description)
    {
        $db = Database::connect();
        $userID = $_SESSION['userID'] ?? null;
        $sqlQuery = "UPDATE pets SET name = :name, species = :species, breed = :breed, color = :color, description = :description 
            WHERE pet_id = :petID;";
        $stmt = $db->prepare($sqlQuery);
        $stmt->bindParam(':petID', $petID, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':species', $species, PDO::PARAM_STR);
        $stmt->bindParam(':breed', $breed, PDO::PARAM_STR);
        $stmt->bindParam(':color', $color, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':petID', $petID, PDO::PARAM_INT);
        $stmt->execute();
    }
    //endregion
}