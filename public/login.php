<?php

use App\models\UserDataSet;

require_once __DIR__ . "/../app/models/UserDataSet.php";
$userDataSet = new UserDataSet();
session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'Strict',
]);
session_start();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["loginBtn"])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        if (empty($username) || empty($password)) $_SESSION["errorMessage"] = "Please fill in all the required fields.";
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) die('Invalid CSRF token');
        $user = $userDataSet->login($username, $password);
        if ($user) {
            session_regenerate_id();
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