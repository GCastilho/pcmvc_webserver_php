<?php
	include 'databaseConnection.php';
	$min_version = 0.1;
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		//Decode JSON input, die if fail
		$input = json_decode(file_get_contents("php://input"));
		if ($input === null) die("Error decoding JSON input");
		//Check if input object follows protocol standards
		if (! property_exists($input, 'signature') ||
			! property_exists($input->data, 'version') ||
			! property_exists($input->data, 'RA') ||
			! property_exists($input->data, 'telemetry') ||
			! is_array($input->data->telemetry) ||
			$input->data->version < $min_version
		) {
			die("Error: Required protocol versions $min_version or higher");
		}
		if (validInputSignature($input)) {
			appendTelemetry($input->data->RA, $input->data->telemetry);
		} else {
			die("Fail to verify message signature, check your API Key");
		}
	}

	function validInputSignature($input) {
		$data = json_encode($input->data);
		$RA = $input->data->RA;
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
		$providedSignature = $input->signature;
		$calculatedSignature =  hash("sha512", $data.$apiKey, false);
		return ($providedSignature === $calculatedSignature);
	}

	function appendTelemetry($RA, $telemetryArray) {
		$database = new DatabaseConnection();
		
		$succeeded_num = 0;
		$errors_num = 0;
		foreach ($telemetryArray as $telemetryArray => $telemetry) {
			//Check if telemetry object follows protocol standards
			if (property_exists($telemetry, 'timestamp') &&
				property_exists($telemetry, 'latitude') &&
				property_exists($telemetry, 'longitude') &&
				property_exists($telemetry, 'windVelocity')
			) {
				$sql = "INSERT INTO Telemetry (RA, timestamp, latitude, longitude, windVelocity)
				VALUES (?, ?, ?, ?, ?)";

				$values = array('issss',
					$RA,
					$telemetry->timestamp,
					$telemetry->latitude,
					$telemetry->longitude,
					$telemetry->windVelocity);

				if ($database->secureQuery($sql, $values) === true) {
					$succeeded_num++;
				} else {
					$errors_num++;
				}
			} else {
				$errors_num++;
				echo "Error on TelemetryArray: telemetry object does not follow protocol standards\n";
			}
		}
		echo "$succeeded_num records created successfully in database\n$errors_num errors\n";
	}
?>