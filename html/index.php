
<?php include_once("../scripts/settings.php"); ?>

<!DOCTYPE html>
<html>
	<head>
		<title>AIS Radar</title>
		<meta charset="utf-8" />
		<meta http-equiv="refresh" content="120; URL=#">
		<link rel="stylesheet" href="leaflet.css"/>
		<script src="leaflet.js"></script>
		<script src="chart.min.js"></script>
		<style>
			html, body, #map {
				height: 100%;
				width: 100%;
				margin: 0;
			}
			.leaflet-container {
				height: 400px;
				width: 600px;
				max-width: 100%;
				max-height: 100%;
			}
			.container {
				display: flex;
				align-items: center;
				display: flex;
				align-items: center;
			}
		</style>
	</head>
	<body>
	<div align="center"> 
		<table width="100%" height="1000" border="0">
			<tr>
				<td><div id="map"></div></td>
			</tr>
		</table>
	</div>
	<script>
			<?php
			CreateMap($sql_link);
			CreateMapData($sql_link); 
			?>
	</script>
	</body>
</html>
<?php $sql_link->close(); ?>
