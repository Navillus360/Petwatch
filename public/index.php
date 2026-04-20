<?php
session_set_cookie_params([
    'httponly' => true,
    'secure' => true,
    'samesite' => 'Strict',
]);
session_start();
$page = $_GET['page'] ?? 'home';
$view = null;

switch ($page) {
    case 'login':
        require_once __DIR__ . '/../app/controllers/login.php';
        $view = 'login.phtml';
        break;
    case 'logout':
        require_once __DIR__ . '/../app/controllers/logout.php';
        $view = 'logout.phtml';
        break;
    case 'my_pets':
        require_once __DIR__ . '/../app/controllers/my_pets.php';
        $view = 'my_pets.phtml';
        break;
    case 'register':
        require_once __DIR__ . '/../app/controllers/register.php';
        $view = 'register.phtml';
        break;
    case 'view_sightings':
        require_once __DIR__ . '/../app/controllers/view_sightings.php';
        $view = 'view_sightings.phtml';
        break;
    case 'report_sighting':
        require_once __DIR__ . '/../app/controllers/report_sighting.php';
        $view = 'report_sighting.phtml';
        break;
    case 'update_sightings':
        require_once __DIR__ . '/../app/controllers/update_sightings.php';
        $view = 'update_sightings.phtml';
        break;
    default:
        $view = 'index.phtml';
}
require_once __DIR__ . "/../app/views/" . $view;