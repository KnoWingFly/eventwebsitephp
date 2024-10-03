<?php
require_once "../config/db.php";

class Event
{
	private $conn;

	public function __construct()
	{
		$database = new Database();
		$this->conn = $database->getConnection();
	}

	public function getAllEvents()
	{
		$query =
			"SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC";
		$result = $this->conn->query($query);

		if (!$result) {
			echo "Query error: " . $this->conn->error;
			return [];
		}

		$events = $result->fetch_all(MYSQLI_ASSOC);

		return $events;
	}

	public function getEventById($id)
	{
		$query = "SELECT * FROM events WHERE id = ?";
		$stmt = $this->conn->prepare($query);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		return $result->fetch_assoc();
	}

	public function registerUserForEvent($userId, $eventId)
	{
		$query =
			"INSERT INTO event_registrations (user_id, event_id, registration_date) VALUES (?, ?, NOW())";
		$stmt = $this->conn->prepare($query);
		$stmt->bind_param("si", $userId, $eventId);
		return $stmt->execute();
	}

	public function cancelRegistration($userId, $eventId)
	{
		$query =
			"DELETE FROM event_registrations WHERE user_id = ? AND event_id = ?";
		$stmt = $this->conn->prepare($query);
		$stmt->bind_param("si", $userId, $eventId);
		return $stmt->execute();
	}

	public function getRegisteredEvents($userId)
	{
		$query = "SELECT e.* FROM events e
                  INNER JOIN event_registrations er ON e.id = er.event_id
                  WHERE er.user_id = ?
                  ORDER BY e.event_date ASC";
		$stmt = $this->conn->prepare($query);
		$stmt->bind_param("s", $userId);
		$stmt->execute();
		$result = $stmt->get_result();
		return $result->fetch_all(MYSQLI_ASSOC);
	}

	public function createEvent(
		$title,
		$description,
		$schedule,
		$location,
		$eventDate
	) {
		$query = "INSERT INTO events (title, description, schedule, location, event_date) 
                  VALUES (?, ?, ?, ?, ?)";
		$stmt = $this->conn->prepare($query);
		$stmt->bind_param(
			"sssss",
			$title,
			$description,
			$schedule,
			$location,
			$eventDate
		);
		return $stmt->execute();
	}

	public function updateEvent(
		$id,
		$title,
		$description,
		$schedule,
		$location,
		$eventDate
	) {
		$query = "UPDATE events 
                  SET title = ?, description = ?, schedule = ?, 
                      location = ?, event_date = ? 
                  WHERE id = ?";
		$stmt = $this->conn->prepare($query);
		$stmt->bind_param(
			"sssssi",
			$title,
			$description,
			$schedule,
			$location,
			$eventDate,
			$id
		);
		return $stmt->execute();
	}

	public function deleteEvent($id)
	{
		$query = "DELETE FROM events WHERE id = ?";
		$stmt = $this->conn->prepare($query);
		$stmt->bind_param("i", $id);
		return $stmt->execute();
	}
}
