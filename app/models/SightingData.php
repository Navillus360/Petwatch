<?php
namespace App\models;

class SightingData
{
    private $_sightingID, $_petID, $_userID, $_comment, $_latitude, $_longitude, $_timestamp;
    private $_petName, $_userName, $_photo_url;

    public function __construct($dbRow)
    {
        $this->_sightingID = $dbRow['sighting_id'];
        $this->_petID = $dbRow['pet_id'];
        $this->_userID = $dbRow['user_id'];
        $this->_comment = $dbRow['comment'];
        $this->_latitude = $dbRow['latitude'];
        $this->_longitude = $dbRow['longitude'];
        $this->_timestamp = $dbRow['timestamp'];
        $this->_petName = $dbRow['pet_name'] ?? '';
        $this->_userName = $dbRow['username'] ?? '';
        $this->_photo_url = $dbRow['photo_url'] ?? '';
    }

    public function getSightingID()
    {
        return $this->_sightingID;
    }

    public function getPetID()
    {
        return $this->_petID;
    }

    public function getUserID()
    {
        return $this->_userID;
    }

    public function getComment()
    {
        return $this->_comment;
    }

    public function getLatitude()
    {
        return $this->_latitude;
    }

    public function getLongitude()
    {
        return $this->_longitude;
    }

    public function getTimestamp()
    {
        return $this->_timestamp;
    }

    public function getPetName()
    {
        return $this->_petName;
    }

    public function getUserName()
    {
        return $this->_userName;
    }

    public function getPhotoUrl()
    {
        return $this->_photo_url;
    }
}