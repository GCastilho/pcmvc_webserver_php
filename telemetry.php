<?php
	/* O arduino irá procurar por um arquivo em 'host:80/telemetry.php
	como definido no arquivo 'network.cpp', função 'post'
		"client->println("POST /telemetry.php HTTP/1.1");"
	Lembrando que alterar a localização do .cpp não muda o código que já está rodando
	em outros arduinos, então tenha em mente que vc deverá manter eles compatíveis //*/

	include 'database/databaseConnection.php';
	$min_version = 1.0;
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		//Decode PSV input, die if fail
		$input = explode("|", file_get_contents("php://input"));
		$message = json_decode($input[0]);
		$signature = $input[1];
		if ($message === null || $signature === null) die ("Error decoding input");

		//die("Server not receiving inputs at the moment");

		if ($message->version >= $min_version) {
			if (validInputProtocol($message, $message->version)) {
				if (validInputSignature($input[0], $signature)) {
					appendTelemetry($message);
				} else die ("Error: Fail to verify message signature, check your API Key");
			} else die ("Error: Message uses protocol version $message->version but does not follow it's standards");
		} else die ("Error: Required protocol versions $min_version or higher");
	}

	// Função para permitir múltiplas versões do protocolo ao mesmo tempo
	// para manter retrocompatibilidade (note que a appendTelemetry() tbm
	// deve oferecer suporte para as versões acima da min_version)
	function validInputProtocol($message, $version) {
		if ($version == 1.0) {
			return property_exists($message, 'RA') &&
				property_exists($message, 'lat') &&
				property_exists($message, 'lon') &&
				property_exists($message, 'hgt') &&
				property_exists($message, 'wind');
		} else {
			die ("Unreconized protocol version");
		}
	}

	function validInputSignature($message, $signature) {
		$data = json_decode($message);
		$RA = $data->RA;
		$apiKey = (function() use ($RA) {
			$database = new DatabaseConnection();
			$result = $database->secureQuery("SELECT api_key FROM api_credential
				WHERE matricula = ?;", array("i", $RA));
			//Get only first row data
			if ($result->num_rows > 0 && $row = $result->fetch_assoc()) {
				return $row["api_key"];
			} else {
				return null;
			}
		})();
		if ($apiKey === null) return false;
		$calculatedSignature = hash("sha256", $message.$apiKey, false);
		return ($signature === $calculatedSignature);
	}

	function appendTelemetry($message) {
		$database = new DatabaseConnection();

		$sql = "INSERT INTO telemetry (matricula, timestamp, latitude, longitude, altura, wind_velocity)
		VALUES (?, ?, ?, ?, ?, ?)";

		$values = array('isssss',
			$message->RA,
			time(),
			$message->lat,
			$message->lon,
			$message->hgt,
			$message->wind);

		if ($database->secureQuery($sql, $values) === true) {
			echo "Successfully inserted telemetry in database";
		} else {
			echo "Error while inserting telemetry into database";
		}
	}
?>
