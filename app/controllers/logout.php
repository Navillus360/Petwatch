<?php

namespace App\controllers;

use App\models\UserDataSet;

require_once __DIR__ . "/../models/UserDataSet.php";
$userDataSet = new UserDataSet();
$userDataSet->Logout();