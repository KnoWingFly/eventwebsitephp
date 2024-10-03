<?php
require_once "../config/db.php";

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

class UserController
{
	private $db;

	public function __construct()
	{
		$database = new Database();
		$this->db = $database->getConnection();
	}

	public function register($name, $email, $password, $confirm_password)
	{
		if ($password !== $confirm_password) {
			echo "Passwords do not match.";
			return;
		}

		$hashed_password = password_hash($password, PASSWORD_DEFAULT);

		$stmt = $this->db->prepare(
			"INSERT INTO users (name, email, password) VALUES (?, ?, ?)"
		);
		$stmt->bind_param("sss", $name, $email, $hashed_password);

		if ($stmt->execute()) {
			header("Location: /login");
			exit();
		} else {
			echo "Error: " . $stmt->error;
		}
	}

	public function login($email, $password)
	{
		$stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
		$stmt->bind_param("s", $email);
		$stmt->execute();

		$result = $stmt->get_result();
		$user = $result->fetch_assoc();

		if ($user && password_verify($password, $user["password"])) {
			$_SESSION["user_id"] = $user["Id_user"];
			header("Location: /home");
			exit();
		} else {
			echo "Invalid email or password.";
		}
	}

	public function logout()
	{
		session_start();
		session_destroy();
		header("Location: /login");
		exit();
	}
}
