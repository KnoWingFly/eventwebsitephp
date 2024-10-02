<?php
session_start();
require_once "../Controller/UserController.php";

$userController = new UserController();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentRoute = $_SERVER['REQUEST_URI'];

$publicRoutes = ["/login", "/signup", "/register"];

if (!isset($_SESSION['user_id']) && !in_array($path, $publicRoutes)) {
    header("Location: /login");
    exit();
}

// Root redirection
if ($path == "/") {
    var_dump($_SESSION); // Debugging: Check if session is being set correctly
    header("Location: " . (isset($_SESSION['user_id']) ? "/home" : "/login"));
    exit();
}


// Routing
switch ($path) {

    case "/home":
        require __DIR__ . "/../views/menu/home.php";
        break;

    case "/login":
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (isset($_POST["email"], $_POST["password"])) {
                $email = $_POST["email"];
                $password = $_POST["password"];
                $userController->login($email, $password);
            } else {
                echo "Email or password is missing.";
            }
        } else {
            require __DIR__ . "/../views/login/login.php";
        }
        break;

    case "/signup":
        require __DIR__ . "/../views/login/signup.php";
        break;

    case "/register":
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (isset($_POST["name"], $_POST["email"], $_POST["password"], $_POST["confirm_password"])) {
                $name = $_POST["name"];
                $email = $_POST["email"];
                $password = $_POST["password"];
                $confirm_password = $_POST["confirm_password"];
                $userController->register($name, $email, $password, $confirm_password);
            } else {
                echo "Name, email, password, or confirm password is missing.";
            }
        }
        break;

        case "/events":
            $eventController = new EventController($db);
            $eventController->listAvailableEvents();
            break;
        
        case "/event-details":
            if (isset($_GET['id'])) {
                $eventController = new EventController($db);
                $eventController->showEventDetails($_GET['id']);
            } else {
                echo "Event ID is missing.";
            }
            break;
        
        case "/register-event":
            if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['event_id'])) {
                $eventController = new EventController($db);
                $eventController->registerForEvent($_SESSION['user_id'], $_POST['event_id']);
            } else {
                echo "Invalid request or event ID is missing.";
            }
            break;
        
        case "/cancel-registration":
            if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['event_id'])) {
                $eventController = new EventController($db);
                $eventController->cancelRegistration($_SESSION['user_id'], $_POST['event_id']);
            } else {
                echo "Invalid request or event ID is missing.";
            }
            break;
        
        case "/registered-events":
            $eventController = new EventController($db);
            $eventController->listRegisteredEvents($_SESSION['user_id']);
            break;

    default:
        echo "404 - Page not found.";
        break;
}
