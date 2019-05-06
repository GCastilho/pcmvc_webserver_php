<?php
	include 'databaseConnection.php';
	$min_version = 0.4;
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		//Decode PSV input, die if fail
		$input = explode("|", file_get_contents("php://input"));
		if ($input[0] === null || $input[1] === null) die ("Error decoding input");
		$message = json_decode($input[0]);
		$signature = $input[1];

		//Check if messa follows protocol standards
		if (! property_exists($message, 'version') ||
			! property_exists($message, 'RA') ||
			! property_exists($message, 'lat') ||
			! property_exists($message, 'lon') ||
			! property_exists($message, 'hgt') ||
			! property_exists($message, 'wind') ||
			$message->version < $min_version
		) {
			die ("Error: Required protocol versions $min_version or higher");
		}
		if (validInputSignature($input[0], $signature)) {
			appendTelemetry($message);
		} else {
			die ("Fail to verify message signature, check your API Key");
		}
	}

	function validInputSignature($message, $signature) {
		$data = json_decode($message);
		$RA = $data->RA;
		$apiKey = (function() use ($RA) {
			$database = new DatabaseConnection();
			$result = $database->secureQuery("SELECT Api_Key FROM Aluno
				WHERE RA = ?;", array("i", $RA));
			//Get only first row data
			if ($result->num_rows > 0 && $row = $result->fetch_assoc()) {
				return $row["Api_Key"];
			} else {
				return null;
			}
		})();
		if ($apiKey === null) return false;
		$calculatedSignature =  hash("sha256", $message.$apiKey, false);
		return ($signature === $calculatedSignature);
	}

	function appendTelemetry($message) {
		$database = new DatabaseConnection();

		$sql = "INSERT INTO Telemetry (RA, timestamp, latitude, longitude, windVelocity)
		VALUES (?, ?, ?, ?, ?)";

		$values = array('issss',
			$message->RA,
			0, //TODO: Striger pra adicionar timestamp automaticamente
			$message->lat,
			$message->lon,
			//TODO: Adicionar hgt
			$message->wind);

		if ($database->secureQuery($sql, $values) === true) {
			echo "Successfully inserted telemetry in database";
		} else {
			echo "Error while inserting telemetry into database";
		}
	}
?>