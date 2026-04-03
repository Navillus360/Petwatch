<?php

use App\models\UserDataSet;

require_once __DIR__ . "/../app/models/UserDataSet.php";
$userDataSet = new UserDataSet();
$userDataSet->Logout();
require_once __DIR__ . "/../app/views/logout.phtml";