<?php
	class DatabaseConnection {
		private $conn = null;
		
		function __construct() {
			$servername = "localhost";
			$username = "telemetry_server";
			$password = "kPI6dBZLbRVbPT2P0Q7M";
			$dbname = "telemetryProject";

			// Create connection
			$this->conn = new mysqli($servername, $username, $password, $dbname);
			
			// Check connection
			if ($this->conn->connect_error) {
				die("Error comunicating with database");
			}
		}

		function secureQuery($query, $values) {
			$stmt = $this->conn->prepare($query);
			$stmt->bind_param(...$values);
			$success = $stmt->execute();
			$result = $stmt->get_result();
			return $success && is_bool($result) ? true : $result;
		}

		function __destruct() {
			$this->conn->close();
		}
	}
?>