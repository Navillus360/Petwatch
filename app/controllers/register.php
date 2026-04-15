<?php

use App\models\UserDataSet;

require_once __DIR__ . "/../models/UserDataSet.php";

$userDataSet = new UserDataSet();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["registerBtn"])) {
        //Validate csrf
        $username = is_string(trim($_POST['username']));
        $password = is_string(trim($_POST['password']));
        $accountRegistered = $userDataSet->Register($username, $password);
        if ($accountRegistered) {
            http_response_code(409);
            $_SESSION['error'] = true;
        } else {
            $user = $userDataSet->Login($username, $password);
            $_SESSION['username'] = $user->GetUsername();
            $_SESSION['loggedIn'] = true;
            $_SESSION['userID'] = $user->GetUserID();
            $_SESSION['role'] = 'user';
        }
    }
    if (isset($_POST["logoutBtn"])) $userDataSet->Logout();
}