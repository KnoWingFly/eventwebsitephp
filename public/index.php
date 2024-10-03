<?php
session_start();
require_once "../Controller/UserController.php";
require_once "../Controller/EventController.php";

$userController = new UserController();
$eventController = new EventController();

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$currentRoute = $_SERVER["REQUEST_URI"];

$publicRoutes = ["/login", "/signup", "/register"];

if (!isset($_SESSION["user_id"]) && !in_array($path, $publicRoutes)) {
	header("Location: /login");
	exit();
}

// Root redirection
if ($path == "/") {
	header("Location: " . (isset($_SESSION["user_id"]) ? "/home" : "/login"));
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
				$userController->login($_POST["email"], $_POST["password"]);
			} else {
				echo "Email or password is missing.";
			}
		} else {
			require __DIR__ . "/../views/login/login.php";
		}
		break;

	case "/logout":
		$userController->logout();
		break;

	case "/signup":
		require __DIR__ . "/../views/login/signup.php";
		break;

	case "/register":
		if ($_SERVER["REQUEST_METHOD"] === "POST") {
			if (
				isset(
					$_POST["name"],
					$_POST["email"],
					$_POST["password"],
					$_POST["confirm_password"]
				)
			) {
				$userController->register(
					$_POST["name"],
					$_POST["email"],
					$_POST["password"],
					$_POST["confirm_password"]
				);
			} else {
				echo "Name, email, password, or confirm password is missing.";
			}
		}
		break;

	case "/events":
		if (
			$_SERVER["REQUEST_METHOD"] === "POST" &&
			isset($_POST["create_event"])
		) {
			$eventController->listAvailableEvents(); // This will include event creation
		} else {
			$eventController->listAvailableEvents();
		}
		break;

	case "/create-event":
		if (
			$_SERVER["REQUEST_METHOD"] === "POST" &&
			isset($_POST["event_title"], $_POST["event_description"])
		) {
			// Process the event creation
			$eventController->createEvent(
				$_POST["event_title"],
				$_POST["event_description"],
				$_POST["event_date"],
				$_POST["event_location"]
			);

			header("Location: /events");
			exit();
		} else {
			require __DIR__ . "/../views/event/create_event.php";
		}
		break;

	case "/event-details":
		if (isset($_GET["id"])) {
			$eventController->showEventDetails($_GET["id"]);
		} else {
			echo "Event ID is missing.";
		}
		break;

	case "/register-event":
		if (
			$_SERVER["REQUEST_METHOD"] === "POST" &&
			isset($_POST["event_id"])
		) {
			$eventController->registerForEvent(
				$_SESSION["user_id"],
				$_POST["event_id"]
			);
		} else {
			echo "Invalid request or event ID is missing.";
		}
		break;

	case "/cancel-registration":
		if (
			$_SERVER["REQUEST_METHOD"] === "POST" &&
			isset($_POST["event_id"])
		) {
			$eventController->cancelRegistration(
				$_SESSION["user_id"],
				$_POST["event_id"]
			);
		} else {
			echo "Invalid request or event ID is missing.";
		}
		break;

	case "/registered-events":
		$eventController->listRegisteredEvents($_SESSION["user_id"]);
		break;

	default:
		http_response_code(404);
		echo "404 - Page not found.";
		break;
}
