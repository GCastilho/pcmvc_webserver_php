<?php
	include '../database/databaseConnection.php';
	$date = new DateTime();
	$date->setTimezone(new DateTimeZone('America/Sao_Paulo'));

	//Parse GET query and create SQL query
	$SqlQuery = "select * from telemetry";
	$SqlValues = array();
	$bindParameters = '';	//string que diz o q cada '?' é na hora do bind;  i -> integer; s -> string

	if ($_GET["matricula"] != 'all'
		|| $_GET["date_filter_type"] != 'none'
		|| $_GET["localizacao"] != 'all'
	) {
		$SqlQuery.=" WHERE";

		$matricula = $_GET["matricula"];
		if ($matricula != 'all') {
			$SqlQuery.=" matricula = ?"." AND";
			array_push($SqlValues, $matricula);
			$bindParameters = $bindParameters."i";
		}

		$date_filter_type = $_GET["date_filter_type"];
		if ($date_filter_type == 'range') {
			$startTime = $date->setDate(...explode("-", $_GET["dinicial"]))->getTimestamp();
			$endTime = $date->setDate(...explode("-", $_GET["dfinal"]))->getTimestamp();
	
			$SqlQuery.=" timestamp > ? AND timestamp <= ?"." AND";
			array_push($SqlValues, $startTime, $endTime);
			$bindParameters = $bindParameters."ii";
		} elseif ($date_filter_type == 'last_x') {
			$last_x = $_GET["last_x"];
			$x_unity = $_GET["x_unity"];
			if ($x_unity == 'dias' || $x_unity == 'meses') {
				$unity = $x_unity == 'dias' ? 'day' : 'month';
				$days_ago = strtotime(date("Y-m-d", strtotime("-$last_x $unity")));

				$SqlQuery.=" timestamp > ?"." AND";
				array_push($SqlValues, $days_ago);
				$bindParameters = $bindParameters."i";
			} else {
				// se a unidade for 'medidas', reverse order by timestamp e pega as primeiras X
				$SqlQuery.=" LIMIT ?";	//Se só tem esse filtro o WHERE dá problema
				array_push($SqlValues, $last_x);	//LIMIT deve ser colocado no fim da query
				$bindParameters = $bindParameters."i";
			}
		}

		$localizacao = $_GET["localizacao"];
		if ($localizacao != 'all') {
			$latitude = explode(",", $localizacao)[0];
			$longitude = explode(",", $localizacao)[1];
	
			$SqlQuery.=" latitude = ? AND longitude = ?"." AND";
			array_push($SqlValues, $latitude, $longitude);
			$bindParameters = $bindParameters."ss";
		}

		$SqlQuery = rtrim($SqlQuery, " AND");	// Remove o último 'AND'
	}
	$SqlQuery.=" ORDER BY timestamp";

	$database = new DatabaseConnection();
	if ($bindParameters == '') {
		$result=$database->query($SqlQuery);
	} else {
		$result=$database->secureQuery($SqlQuery, array_merge(array($bindParameters), $SqlValues));
	}

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