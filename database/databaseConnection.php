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

		function query($sql) {
			return $this->conn->query($sql);
		}

		function secureQuery($query, $values) {
			$stmt = $this->conn->prepare($query);
			$stmt->bind_param(...$values);
			$success = $stmt->execute();
			$result = $stmt->get_result();
			return $success && is_bool($result) ? true : $result;
		}

		function makeCookie($username) {
			$sessionID = hash("sha512", $username.uniqid(rand(), true), false);
			return $this->query("INSERT INTO cookie(username, sessionID)
				VALUES(\"$username\", \"$sessionID\")"
			) ? $sessionID : header('HTTP/1.1 500 Internal Server Error');
		}

		function validCookie($cookie) {
			$result = $this->query("SELECT sessionID FROM cookie
				WHERE sessionID = \"$cookie\"");
			/*	Se existir uma linha no result, é porque ele achou aquele
				sessionID específico, portanto é um sessionID válido //*/
			return $result->num_rows > 0;
		}

		function __destruct() {
			$this->conn->close();
		}
	}
?>