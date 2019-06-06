<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>TelemetryProject</title>
</head>
<body>
	<?php if ($_SERVER["REQUEST_METHOD"] == "GET") { ?>
		<form method="get" action="api.php">
			<button type="submit">Get telemetry data</button>
		</form>
		<form action="" method="post">
			Username: <input name="username" type="text">
			Password: <input name="password" type="password">
			<input type="submit" value="Login">
		</form>
	<?php } else if($_SERVER["REQUEST_METHOD"] == "POST") {
		include 'databaseConnection.php';
		
		$username = test_input($_POST["username"]);
		$password = test_input($_POST["password"]);
		if(validCredentials($username, $password)) {
			echo "Bem-vindo, $username";
			echo "The authentication works!";
		} else {
			die("Invalid username or password!");
		}
	}
	
	function test_input($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}

	function validCredentials($username, $password) {
		if($username === null || $password === null) return false;
		$storedCredentials = (function() use ($username) {
			$database = new DatabaseConnection();
			$result = $database->secureQuery("SELECT Salt,Password_Hash
				FROM Professor WHERE Username = ?;", array("s", $username));
			if ($result->num_rows > 0 && $row = $result->fetch_assoc()) {
				return array($row["Salt"], $row["Password_Hash"]);
			} else {
				return null;
			}
		})();
		if($storedCredentials !== null) {
			$storedSalt = $storedCredentials[0];
			$storedHash = $storedCredentials[1];
			$calculatedHash = hash("sha512", $storedSalt.$password, false);
			return ($calculatedHash === $storedHash);
		} else {
			return false;
		}
	} ?>
</body>
</html>