<?php
	$min_version = 0.1;
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$data = json_decode(file_get_contents("php://input"));
		if ($data->version < $min_version) {
			echo "Min protocol version is $min_version";
			return;
			//replace with sending ONLY a specific status code
		}
		//print_r($data);
		//check if signature is valid from given username before inserting into DB
		appendTelemetry($data->RA, $data->telemetry);
	}

	//function to verify signature from username (username might be a pubKey) stored in specific table

	function appendTelemetry($RA, $telemetryArray) {
		$servername = "localhost";
		$username = "root";
		$password = "";
		$dbname = "fatec_campinas";

		// Create connection
		$conn = new mysqli($servername, $username, $password, $dbname);
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}
		
		$succeeded_num = 0;
		foreach ($telemetryArray as $telemetryArray => $telemetry) {
			$sql = "INSERT INTO Telemetry (RA, timestamp, latitude, longitude, windVelocity)
			VALUES ('$RA',
			'$telemetry->timestamp',
			'$telemetry->latitude',
			'$telemetry->longitude',
			'$telemetry->windVelocity')";

			if ($conn->query($sql) === TRUE) {
				$succeeded_num++;
			} else {
				echo "Error: " . $sql . "\n" . $conn->error;
			}
		}
		echo "$succeeded_num records created successfully\n";

		$conn->close();
	}
?>