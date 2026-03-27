<?php
/**
 * Class UserDataSet
 * This class is designed to hold multiple UserData instances.
 * However, it will verify a users login details
 * and allow for new users to register onto the webiste
 */

namespace App\models;
require_once('Database.php');

final class UserDataSet
{
    //region Total Count functions
    /**
     * @return mixed
     * Gets the total number of users registered aside from the admin
     * Used in the home page
     */
    public static function getTotalUsers()
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE role NOT LIKE 'admin' ;");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * @return mixed
     * Gets the total number of pets with the status of found.
     * Used in the home page
     */
    public static function getTotalFoundPets()
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT COUNT(*) FROM pets WHERE status = 'found';");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * @return mixed
     * Gets the total number of pets regardless of their status
     * Used in the home page
     */
    public static function getTotalPets()
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT COUNT(*) FROM pets;");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    //endregion
}