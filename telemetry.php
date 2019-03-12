<?php
	include 'databaseConnection.php';
	$min_version = 0.1;
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$input = json_decode(file_get_contents("php://input"));
		//TODO: Adicionar um verificador se o objeto do input segue o padrão q deve seguir
		if ($input->data->version < $min_version) {
			echo "Error: Min protocol version is $min_version";
			return;
		}
		if (validateInputSignature($input)) {
			appendTelemetry($input->data->RA, $input->data->telemetry);
		} else {
			echo "Fail to verify message signature, check your API Key\n";
		}
	}

	function validateInputSignature($input) {
		$data = json_encode($input->data);
		$RA = $input->data->RA;
		$apiKey = (function() use ($RA) {
			$database = new DatabaseConnection();
			$result = $database->secureQuery("SELECT Api_Key FROM Credentials
				WHERE RA = ?;", array("i", $RA));
			//Get only first row data
			if ($result->num_rows > 0 && $row = $result->fetch_assoc()) {
				return $row["Api_Key"];
			} else {
				return null;
			}
		})();
		$providedSignature = $input->signature;
		$calculatedSignature =  hash("sha512", $data.$apiKey, false);
		return ($providedSignature === $calculatedSignature);
	}

	function appendTelemetry($RA, $telemetryArray) {
		$database = new DatabaseConnection();
		
		$succeeded_num = 0;
		$errors_num = 0;
		foreach ($telemetryArray as $telemetryArray => $telemetry) {
			$sql = "INSERT INTO Telemetry (RA, timestamp, latitude, longitude, windVelocity)
				VALUES (?, ?, ?, ?, ?)";

			$values = array('issss',
				$RA,
				$telemetry->timestamp,
				$telemetry->latitude,
				$telemetry->longitude,
				$telemetry->windVelocity);

			if ($database->secureQuery($sql, $values) === TRUE) {
				$succeeded_num++;
			} else {
				$errors_num++;
			}
		}
		echo "$succeeded_num records created successfully in database\n$errors_num errors\n";
	}
?>