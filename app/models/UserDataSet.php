<?php
/**
 * Class UserDataSet
 * This class is designed to hold multiple UserData instances.
 * However, it will verify a users login details
 * and allow for new users to register onto the website
 */

namespace App\models;

use PDO;

require_once('Database.php');
require_once('UserData.php');

final class UserDataSet
{
    //region Login and Registration
    /**
     * @param string $username
     * @param string $password
     * @return UserData|false
     * Attempts to log the user in by comparing the inputted username with one from the database.
     * Returns an instance of the User if true, returns false if the user does not exist
     */
    public static function Login(string $username, string $password)
    {
        $db = Database::connect();
        $userExistQuery = $db->prepare("SELECT * FROM users WHERE username = :username;");
        $userExistQuery->bindParam(':username', $username, PDO::PARAM_STR);
        $userExistQuery->execute();
        $row = $userExistQuery->fetch();
        if ($row && password_verify($password, $row['password_hash'])) {
            return new UserData($row);
        } else {
            return false;
        }
    }

    /**
     * @return void
     * Mimics logging the user out by unsetting relevant session variables
     * This forces the user to login if they want to access certain pages
     */
    public static function Logout()
    {
        unset($_SESSION['username']);
        unset($_SESSION['userID']);
        unset($_SESSION['role']);
        unset($_SESSION['loggedIn']);
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool
     * Registers the user onto the database, but checks to see if they already exist.
     * If they don't exist on the database, their details get added and auto assigned
     * A role of user until they add a pet for the first time
     */
    public static function Register(string $username, string $password): bool
    {
        //Check if user isn't already registered
        $db = Database::connect();
        $userExistQuery = "SELECT username FROM users WHERE username = :username AND 1=1;";
        $stmt = $db->prepare($userExistQuery);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row['username'] == $username) {
            return true;
        } else {
            $addUserQuery = "INSERT INTO users (username, password_hash, role) VALUES (:username, :password, 'user')";
            $stmt = $db->prepare($addUserQuery);
            $stmt->bindParam(':username', $username);
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->execute();
            return false;
        }
    }
    //endregion

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