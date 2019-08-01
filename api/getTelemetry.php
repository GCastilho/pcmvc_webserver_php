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

	// create var to be filled with header data
	$csv_header = '';

	// create line with field names
	$csv_header.='matricula,';
	$csv_header.='timestamp,';
	$csv_header.='data,';
	$csv_header.='hora,';
	$csv_header.='latitude,';
	$csv_header.='longitude,';
	$csv_header.='altura,';
	$csv_header.='velocidade_vento;';

	// newline (seems to work both on Linux & Windows servers)
	$csv_header.= '
';

	// Start sending the data by prompting a csv file for download
	$csv_filename = 'db_export_'.date('Y-m-d').'.csv';

	header("Content-type: text/x-csv");
	header("Content-Disposition: attachment; filename=".$csv_filename."");
	echo($csv_header);

	// Start sending the data, line by line, as the query is processed
	if ($result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {
			$csv_data=$row["matricula"].',';
			{
				$date->setTimestamp($row["timestamp"]);
				$csv_data.=$row["timestamp"].',';
				$csv_data.=$date->format('Y/m/d'.',');
				$csv_data.=$date->format('H:i:s').',';
			}
			$csv_data.=$row["latitude"].',';
			$csv_data.=$row["longitude"].',';
			$csv_data.=$row["altura"].',';
			$csv_data.=$row["wind_velocity"].';';
			$csv_data.= '
';
		echo($csv_data);
		}
	}

?>
