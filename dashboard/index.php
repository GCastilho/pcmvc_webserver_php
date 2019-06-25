<?php
	// Se a sessionID não for válida, redireciona para a home
	include '../database/databaseConnection.php';
	$database = new DatabaseConnection();

	if (!$database->validCookie($_COOKIE['sessionID'])) {
		header('Location: /');
		die();
	};
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>TelemetryProject Dashboard</title>
	</head>

	<body>
		<h1>Welcome to the Dashboard</h1>
		<p>There is nothing here...</p>
	</body>
</html> 