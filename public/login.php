<?php

use App\models\UserDataSet;

require_once __DIR__ . "/../app/models/UserDataSet.php";

session_start();
$userDataSet = new UserDataSet();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["loginBtn"])) {
        //Validate csrf
        $username = is_string(trim($_POST['username']));
        $password = is_string(trim($_POST['password']));
        $accountExists = $userDataSet->login($username, $password);
        if ($accountExists) {
            $user = $userDataSet->Login($username, $password);
            $_SESSION['username'] = $user->GetUsername();
            $_SESSION['loggedIn'] = true;
            $_SESSION['userID'] = $user->GetUserID();
            $_SESSION['role'] = $user->GetRole();
        } else {
            http_response_code(401);
            $_SESSION['error'] = true;
        }
    }
    if (isset($_POST["logoutBtn"])) $userDataSet->Logout();
}
require_once __DIR__ . "/../app/views/login.phtml";