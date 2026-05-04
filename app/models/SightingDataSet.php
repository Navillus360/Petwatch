<?php

namespace App\models;

use PDO;
use App\models\PetData;
use App\models\SightingData;

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
     * @param string $term
     * @param string $status
     * @param string $sort
     * @param int $limit
     * @param int $offset
     * @return array
     * Gets sightings from the database
     * Depending on what the user is searching for
     * If the search is empty, show all 200 sightings (performance)
     * Used in the get_sightings api for the live search
     */

    public function getSightings(string $term = '', string $status = '', string $sort = '', int $limit = 0, int $offset = 0): array
    {
        $db = Database::connect();
        $sqlQuery = "SELECT s.*, p.name AS pet_name, p.photo_url AS photo_url, p.status AS pet_status, u.username AS username
        FROM sightings s JOIN pets p ON s.pet_id = p.pet_id JOIN users u ON s.user_id = u.user_id WHERE 1=1";
        $params = [];
        if (!empty($term)) {
            $sqlQuery .= " AND (
            s.latitude LIKE :term OR
            s.longitude LIKE :term OR
            s.comment LIKE :term OR
            p.name LIKE :term OR
            u.username LIKE :term
        )";
            $params[':term'] = "%$term%";
        }

        if ($status === 'lost') {
            $sqlQuery .= " AND p.status = 'lost'";
        } elseif ($status === 'found') {
            $sqlQuery .= " AND p.status = 'found'";
        }

        if ($sort === 'az') $sqlQuery .= " ORDER BY p.name ASC";
        elseif ($sort === 'za') $sqlQuery .= " ORDER BY p.name DESC";
        else $sqlQuery .= " ORDER BY s.pet_id DESC";
        if ($limit > 0) $sqlQuery .= " LIMIT :limit OFFSET :offset;";
        $stmt = $db->prepare($sqlQuery);
        foreach ($params as $key => $value) $stmt->bindValue($key, $value, PDO::PARAM_STR);
        if ($limit != 0) $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $term
     * @param string $status
     * @return int
     * Gets the total amount of sightings on the database
     * Used in the get_sightings api for the live search
     */
    public function getTotalSightings(string $term = '', string $status = ''): int
    {
        $db = Database::connect();
        $sqlQuery = "SELECT COUNT(*) FROM sightings s JOIN pets p ON s.pet_id = p.pet_id JOIN users u ON s.user_id = u.user_id WHERE 1=1";
        $params = [];
        if (!empty($term)) {
            $sqlQuery .= " AND (
            s.latitude LIKE :term OR
            s.longitude LIKE :term OR
            s.comment LIKE :term OR
            p.name LIKE :term OR
            u.username LIKE :term
        )";
            $params[':term'] = "%$term%";
        }

        if ($status === 'lost') $sqlQuery .= " AND p.status = 'lost';";
        elseif ($status === 'found') $sqlQuery .= " AND p.status = 'found';";
        $stmt = $db->prepare($sqlQuery);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v, PDO::PARAM_STR);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    //endregion

    //region Add/Update/Delete sighting functions
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
        $userID = $_SESSION['userID'] ?? null;
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

    public static function updateSighting($photo_url, $sighting_id, $comment, $latitude, $longitude)
    {
        $db = Database::connect();
        $userID = $_SESSION['userID'] ?? null;
        $sqlQuery = "UPDATE sightings SET comment = :comment, latitude = :latitude, longitude = :longitude, photo_url = :photo_url
            WHERE sighting_id = :sighting_id AND user_id = :user_id;";
        $stmt = $db->prepare($sqlQuery);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':latitude', $latitude, PDO::PARAM_STR);
        $stmt->bindParam(':longitude', $longitude, PDO::PARAM_STR);
        $stmt->bindParam(':photo_url', $photo_url, PDO::PARAM_STR);
        $stmt->bindParam(':sighting_id', $sighting_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * @return bool
     * Deletes the selected sighting from the database
     * Used in view sightings page, but will only be accessible to admins
     */
    public static function deleteSighting(int $sightingID): bool
    {
        $db = Database::connect();
        $role = $_SESSION['role'];
        if ($role == 'admin') {
            $sqlQuery = "DELETE FROM sightings WHERE sighting_id = :sightingID;";
            $stmt = $db->prepare($sqlQuery);
            $stmt->bindParam(':sightingID', $sightingID, PDO::PARAM_INT);
           return $stmt->execute();
        }
        return false;
    }
    //endregion
}