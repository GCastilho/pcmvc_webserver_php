<?php
	include 'database/databaseConnection.php';
	$database = new DatabaseConnection();
	$result=$database->query("select * from telemetry");

	// create var to be filled with export data
	$csv_export = '';

	// create line with field names
	$csv_export.='matricula,';
	$csv_export.='timestamp,';
	$csv_export.='data,';
	$csv_export.='hora,';
	$csv_export.='latitude,';
	$csv_export.='longitude,';
	$csv_export.='altura,';
	$csv_export.='velocidade_vento;';

	// newline (seems to work both on Linux & Windows servers)
	$csv_export.= '
';

	if ($result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {
			$csv_export.=$row["matricula"].',';
			{
				$date = new DateTime();
				$date->setTimezone(new DateTimeZone('America/Sao_Paulo'));
				$date->setTimestamp($row["timestamp"]);
				$csv_export.=$row["timestamp"].',';
				$csv_export.=$date->format('Y/m/d'.',');
				$csv_export.=$date->format('H:i:s').',';
			}
			$csv_export.=$row["latitude"].',';
			$csv_export.=$row["longitude"].',';
			$csv_export.=$row["altura"].',';
			$csv_export.=$row["wind_velocity"].';';
			$csv_export.= '
';
		}
	}

	// Export the data and prompt a csv file for download
	$csv_filename = 'db_export_'.date('Y-m-d').'.csv';

	header("Content-type: text/x-csv");
	header("Content-Disposition: attachment; filename=".$csv_filename."");
	echo($csv_export);

?>