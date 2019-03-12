<?php
	include 'databaseConnection.php';
	$min_version = 0.1;
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$input = json_decode(file_get_contents("php://input"));
		if ($input->data->version < $min_version) {
			echo "Min protocol version is $min_version";
			return;
			//replace with sending ONLY a specific status code
		}
		//print_r($input);
		if (validateInputSignature($input)) {
			appendTelemetry($input->data->RA, $input->data->telemetry);
		} else {
			echo "Fail to autenticate user, check your API Key\n";
		}
	}

	function validateInputSignature($input) {
		$data = json_encode($input->data);
		$RA = $input->data->RA;
		{
			$database = new DatabaseConnection();
			$result = $database->query("SELECT Api_Key FROM Credentials WHERE RA = $RA;");
			// get data of first row
			if ($result->num_rows > 0 && $row = $result->fetch_assoc()) {
				$apiKey = $row["Api_Key"];
			} else {
				$apiKey = null;
			}
		}
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
			VALUES ('$RA',
			'$telemetry->timestamp',
			'$telemetry->latitude',
			'$telemetry->longitude',
			'$telemetry->windVelocity')";

			if ($database->query($sql) === TRUE) {
				$succeeded_num++;
			} else {
				$errors_num++;
			}
		}
		echo "$succeeded_num records created successfully in database\n$errors_num errors\n";
	}
?>