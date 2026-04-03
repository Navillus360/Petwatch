<?php

/**
 * Class PetData
 * This class models a pets data, utilizing private fields for data encapsulation.
 * It takes a database row as input in the constructor and assigns the retrieved values to private fields.
 */
class PetData
{
    private $_petID, $_name, $_species, $_breed, $_color, $_photo_url, $_status;
    private $_description, $_date_reported;

    public function __construct($dbRow)
    {
        $this->_petID = $dbRow['pet_id'];
        $this->_name = $dbRow['name'];
        $this->_species = $dbRow['species'];
        $this->_breed = $dbRow['breed'];
        $this->_color = $dbRow['color'];
        $this->_photo_url = $dbRow['photo_url'];
        if ($dbRow['status'] == 'lost'):
            $this->_date_reported = $dbRow['date_reported'];
        else:
            $this->_date_reported = null;
        endif;
        $this->_status = $dbRow['status'];
        $this->_description = $dbRow['description'];
    }

    public function getPetID()
    {
        return $this->_petID;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getSpecies()
    {
        return $this->_species;
    }

    public function getBreed()
    {
        return $this->_breed;
    }

    public function getColor()
    {
        return $this->_color;
    }

    public function getPhotoUrl()
    {
        return $this->_photo_url;
    }

    public function getDateReported()
    {
        return $this->_date_reported ?? null;
    }

    public function getStatus()
    {
        return $this->_status;
    }

    public function getDescription()
    {
        return $this->_description;
    }
}