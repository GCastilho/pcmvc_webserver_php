<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="root/index.css">
		<title>TelemetryProject</title>
	</head>

	<body>
		<header>
			<h1>TelemetryProject</h1>

			<div class="top_bar">
				<div class="top_bar_left" style="float: left">
					<div class="welcome_message">
							<p>Bem vindo, visitante</p>
					</div>
					<div class="nav_bar">
						<a href="#">Home</a>
						<a href="#">Sobre</a>
					</div>
					
				</div>
		
				<div style="float: right" class="dashboard_login">
					<p>Login to dashboard</p>
					<form action="dashboard/login.php" method="post">
						Username: <input name="username" type="text">
						Password: <input name="password" type="password">
						<input type="submit" value="Login">
					</form>
				</div>
			</div>
		</header>
		
		<div style="clear: both" class="container">
			<div class="telemetry_collect">
				<h2>Coletar dados de telemetria</h2>
				<h4>Filtros: (deixe em branco para não aplicar filtros)</h4>
				<form method="GET" action="api/getTelemetry.php">
					Matrícula: <select name="matricula">
						<!-- dinamicamente preencher com tabela de usuários-->
						<!-- qdo filtrar por ra, subfiltrar a geolocalização -->
						<option value="all">Todos</option>
						<option value="2760000000">2760000000</option>
					</select>
					<br>

					<div id="date_filter">
						Data: <select id="date_filter_type" name="date_filter_type" onchange="update_data_filter()">
							<option value="none">Todas</option>
							<option value="range">de X a Y</option>
							<option value="last_x">últimas X</option>
						</select>

						<div style="display:inline" id="date_filter_box"></div>

						<script>
							function update_data_filter() {
								let index = document.getElementById('date_filter_type').selectedIndex;
								let option = document.getElementById('date_filter_type').options[index].value; 
								let filter_box;
								if (option === 'range') {
									filter_box = 'Data: de <input type="date" name="dinicial">' +
													'até <input type="date" name="dfinal">';
								} else if (option === 'last_x') {
									filter_box = 'últimas <input name="last_x" type="number">' +
													'<select name="x_unity">' +
													'<option value="medidas">medidas</option>' +
													'<option value="dias">dias</option>' +
													'<option value="meses">meses</option>' +
													'</select>';
								} else {
									filter_box = null;
								}
								document.getElementById('date_filter_box').innerHTML = filter_box;
							}
						</script>
					</div>

					Localização: <select name="localizacao">
						<!-- dinamicamente preencher com tabela de localizações (lat-lon) -->
						<option value="all">Todos</option>
						<option value="-22.8044635,-47.3158102">-22.8044635,-47.3158102</option>
					</select>
					<br>

					<button type="submit">Coletar dados de telemetria</button>
				</form>
			</div>
		</div>

	</body>
</html>