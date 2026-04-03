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
use PetData;

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

    /**
     * @param $petID
     * @param $status
     * @return void
     * Changes a pet status to lost or found
     */
    public function changePetStatus($petID, $status)
    {
        $db = Database::connect();
        $sqlQuery = "UPDATE pets SET status = :status WHERE pet_id = :petID;";
        $stmt = $db->prepare($sqlQuery);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':petID', $petID, PDO::PARAM_INT);
        $stmt->execute();
    }

    //region Pet Reporting Functions

    /**
     * @param PetData $petData
     * @return void
     * If the pet has not been reported missing for the first time
     * This function will be called to add a new missing sighting to the database
     * Whilst also setting the pets status to lost
     */
    public function reportMissingPet(PetData $petData)
    {
        $petID = $petData->getPetID();
        $userID = $_SESSION['userID'] ?? null;
        $comment = $petData->comment ?? '';
        $latitude = $petData->latitude ?? '';
        $longitude = $petData->longitude ?? '';
        $db = Database::connect();
        $addMissingPetQuery = "INSERT INTO sightings(pet_id, user_id, comment, latitude, longitude)
        VALUES('$petID', '$userID', '$comment', '$latitude', '$longitude');";
        $stmt = $db->prepare($addMissingPetQuery);
        $stmt->bindParam(':petID', $petID, PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':latitude', $latitude, PDO::PARAM_STR);
        $stmt->bindParam(':longitude', $longitude, PDO::PARAM_STR);
        $stmt->execute();

        $this->changePetStatus($petData->getPetID(), 'lost');
    }
    //endregion

    //region Add/Delete Pet Functions
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
            VALUES ($name, $species, $breed, $color, $photo_url, 'found', $userID, $description);)";
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

            //Check if the pet exists
            $checkPetExists = "SELECT pet_id FROM pets WHERE pet_id = :petID AND user_id = :userID;";
            $stmt = $db->prepare($checkPetExists);
            $userID = $_SESSION['userID'] ?? null;
            $stmt->bindParam(':petID', $petID, PDO::PARAM_INT);
            $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                $db->rollBack();
                return false;
            }

            //Deletes all the sightings relating to the pet
            $deleteSightingQuery = $db->prepare("DELETE FROM sightings WHERE pet_id = :petID;");
            $deleteSightingQuery->bindParam(':petID', $petID, PDO::PARAM_INT);
            $deleteSightingQuery->execute();

            //Deletes the pet from the Database
            $deletePetQuery = $db->prepare("DELETE FROM pets WHERE pet_id = :petID;");
            $deletePetQuery->bindParam(':petID', $petID, PDO::PARAM_INT);
            $deletePetQuery->execute();
            $db->commit();
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            return false;
        }
    }
    //endregion
}