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
    public static function getAll()
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT COUNT(user_id) from users;");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}