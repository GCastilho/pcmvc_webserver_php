<?php
	class DatabaseConnection {
		private $conn = null;
		//TODO: Avoid SQL injection
		
		function __construct() {
			$servername = "localhost";
			$username = "root";
			$password = "";
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

		function __destruct() {
			$this->conn->close();
		}
	}
?>