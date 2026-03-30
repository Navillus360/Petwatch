<?php
/**
 * Class UserData
 * This class is designed to represent a logged-in/existing user instance.
 * Primarily used for a user that's logging in.
 */

namespace App\models;

class UserData
{
    private $_userID, $_username, $_passwordHash, $_role;

    /**
     * @param $dbrow
     * Constructor to initialize properties
     */
    public function __construct($dbrow)
    {
        $this->_userID = $dbrow['user_id'];
        $this->_username = $dbrow['username'];
        $this->_passwordHash = $dbrow['password_hash'];
        $this->_role = $dbrow['role'];
    }

    /**
     * @return mixed
     * Accessor for User ID
     */
    public function GetUserID()
    {
        return $this->_userID;
    }

    /**
     * @return mixed
     * Accessor for Username
     */
    public function GetUsername()
    {
        return $this->_username;
    }

    /**
     * @return mixed
     * Accessor for Password Hash
     */
    public function GetPasswordHash()
    {
        return $this->_passwordHash;
    }

    /**
     * @return mixed
     * Accessor for User Role
     */
    public function GetRole()
    {
        return $this->_role;
    }
}