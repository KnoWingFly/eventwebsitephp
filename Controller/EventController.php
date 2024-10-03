<?php
require_once "../Model/Event.php";
require_once "../config/db.php";

class EventController
{
	private $eventModel;
	private $db;
	private $conn;

	public function __construct()
	{
		$this->eventModel = new Event();
		$database = new Database();
		$this->conn = $database->getConnection();
		$this->db = $this->conn; 
	}

	public function listAvailableEvents()
	{
		$allEvents = $this->eventModel->getAllEvents();
		$eventCreated = false;

		if (
			$_SERVER["REQUEST_METHOD"] === "POST" &&
			isset($_POST["create_event"])
		) {
			$title = $_POST["event_title"] ?? null;
			$description = $_POST["event_description"] ?? null;
			$event_date = $_POST["event_date"] ?? null;
			$location = $_POST["event_location"] ?? null;

			if ($title && $description && $event_date && $location) {
				$this->createEvent(
					$title,
					$description,
					$event_date,
					$location
				);
				$eventCreated = true;
			}
		}

		require __DIR__ . "/../views/menu/home.php";
	}

	public function showEventDetails($id)
	{
		$event = $this->eventModel->getEventById($id);
		require "../views/events/details.php";
	}

	public function registerForEvent()
	{
		if (
			$_SERVER["REQUEST_METHOD"] == "POST" &&
			isset($_POST["register_event"])
		) {
			$userId = $_SESSION["user_id"] ?? null;
			$eventId = $_POST["event_id"] ?? null;

			if ($userId && $eventId) {
				$this->eventModel->registerUserForEvent($userId, $eventId);
				header("Location: index.php?action=listRegisteredEvents");
				exit();
			}
		}
	}

	public function cancelRegistration($userId, $eventId)
	{
		$this->eventModel->cancelRegistration($userId, $eventId);
		header("Location: index.php?action=listRegisteredEvents");
		exit();
	}

	public function listRegisteredEvents($userId)
	{
		$userId = $_SESSION["user_id"] ?? null;
		if ($userId) {
			$registeredEvents = $this->eventModel->getRegisteredEvents($userId);
			require "../views/events/registered.php";
		} else {
			header("Location: index.php?action=login");
			exit();
		}
	}

	public function createEvent($title, $description, $event_date, $location)
	{
		$query =
			"INSERT INTO events (title, description, event_date, location) VALUES (?, ?, ?, ?)";
		$stmt = $this->db->prepare($query);
		$stmt->execute([$title, $description, $event_date, $location]);
	}
}
