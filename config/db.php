<?php


class Database
{
    private $host = "localhost";
    private $user = "root";
    private $db_Name = "Events";
    private $password = "";

    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new mysqli(
                $this->host,
                $this->user,
                $this->password,
                $this->db_Name
            );
            if ($this->conn->connect_error) {
                throw new Exception(
                    "Connection failed: " . $this->conn->connect_error
                );
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }

        return $this->conn;
    }
}