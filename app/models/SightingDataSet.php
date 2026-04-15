<?php

namespace App\models;

use PDOException;
use PDO;
use PetData;
use SightingData;

require_once('Database.php');
require_once('SightingData.php');

class SightingDataSet
{

    //region Get info methods
    /**
     * @return array
     * Get all the missing pets from the database
     * This will be used in the view_sightings page once search filtering is implemented
     */
    public static function getMissingPets(): array
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
     * Gets all the logged-in user sightings
     * This will be used in the my_sightings page
     */
    public static function getUserSightings(): array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT s.*, p.name AS pet_name, p.photo_url AS photo_url FROM sightings s
        JOIN pets p ON s.pet_id = p.pet_id WHERE s.user_id = :user_id;");
        $stmt->bindParam(':user_id', $_SESSION['userID'], PDO::PARAM_INT);
        $stmt->execute();
        $userSightings = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $userSightings[] = new SightingData($row);
        return $userSightings;
    }

    /**
     * @return array
     * Gets sightings depending on what the user has searched
     * Used in view_sightings page
     */
    public static function getSightings(): array
    {
        $db = Database::connect();
        $sqlQuery = "SELECT s.*, p.name AS pet_name FROM sightings s, pets p WHERE status = 'lost';";
        $stmt = $db->prepare($sqlQuery);
        $stmt->execute();
        $sightings = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) $sightings[] = new SightingData($row);
        return $sightings;
    }

    //endregion

    //region Add/Delete sighting functions
    /**
     * @param $photo_url
     * @param $petID
     * @param $comment
     * @param $latitude
     * @param $longitude
     * @return void
     * Adds a sighting to the database
     * Used in the report sighting page
     */
    public static function addSighting($photo_url, $petID, $comment, $latitude, $longitude)
    {
        $db = Database::connect();
        $userID = $_SESSION['userID'];
        $sqlQuery = "INSERT INTO sightings(photo_url, pet_id, user_id, comment, latitude, longitude, timestamp) 
        VALUES(:photo_url, :pet_id, :user_id, :comment, :latitude, :longitude, NOW());";
        $stmt = $db->prepare($sqlQuery);
        $stmt->bindParam(':photo_url', $photo_url, PDO::PARAM_STR);
        $stmt->bindParam(':pet_id', $petID, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':latitude', $latitude, PDO::PARAM_STR);
        $stmt->bindParam(':longitude', $longitude, PDO::PARAM_STR);
        $stmt->execute();
    }

    /**
     * @return void
     * Deletes the selected sighting from the database
     * Used in view sightings page, but will only be accessible to admins
     */
    public static function deleteSighting()
    {
        $db = Database::connect();
        $sightingID = isset($_POST['sightingID']) ? (int)$_POST['sightingID'] : 0;
        $userID = $_SESSION['userID'];
        $role = $_SESSION['role'];
        if ($role == 'admin') {
            $sqlQuery = "DELETE FROM sightings WHERE sighting_id = :sightingID;";
            $stmt = $db->prepare($sqlQuery);
            $stmt->bindParam(':sightingID', $sightingID, PDO::PARAM_INT);
            $stmt->execute();
        }
    }
    //endregion
}