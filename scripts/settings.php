<?php
// include AIS decoder
include_once("aisdec.php");

// sql settings and instance
$sql_host 		= "";
$sql_user 		= "";
$sql_pass 		= "";
$sql_db 		= "";
$sql_link 		= new mysqli($sql_host, $sql_user, $sql_pass, $sql_db);
if ($sql_link->connect_error) {	die("Connection failed: " . $sql_link->connect_error . "\n"); }

// rtl_ais settings
$rtl_port		= 10110;
$rtl_host		= "127.0.0.1";

function SeenReport($link,$mmsi) {
	$sql = "SELECT * FROM `reports` WHERE `mmsi` = '{$mmsi}';";
	$result = $link->query($sql);
	$rows = $result->num_rows;
	return ($rows > 0);
}
function UpdateReport($link,$time,$mmsi,$country,$lon,$lat,$sog,$cog,$hdg) {
	$sql = "UPDATE `reports` SET `time` = '{$time}', `lon` = '{$lon}', `lat` = '{$lat}', `sog` = '{$sog}', `cog` = '{$cog}', `hdg` = '{$hdg}' WHERE `reports`.`mmsi` = '{$mmsi}'";
	$link->query($sql);
}
function CreateReport($link,$time,$mmsi,$country,$lon,$lat,$sog,$cog,$hdg) {
	$sql = "INSERT INTO `reports` (`id`, `time`, `mmsi`, `country`, `lon`, `lat`, `sog`, `cog`, `hdg`) VALUES ('0', '{$time}', '{$mmsi}', '{$country}', '{$lon}', '{$lat}', '{$sog}', '{$cog}', '{$hdg}')";
	$link->query($sql);
}

function GetKnownVessels($link) {
	$sql = "SELECT * FROM (SELECT * FROM `reports` ORDER BY `id` DESC) as sub ORDER BY `sub`.`country` DESC LIMIT 50;";
	$stmt = $link->prepare($sql); 
	$stmt->execute();
	$result = $stmt->get_result(); 
	return $result;
}
	
function GetHistory($link,$mmsi) {
	$sql = "SELECT * FROM (SELECT * FROM `history` WHERE `mmsi` = '{$mmsi}') as sub ORDER BY `sub`.`id` DESC LIMIT 5;";
	$stmt = $link->prepare($sql); 
	$stmt->execute();
	$result = $stmt->get_result(); 
	return $result;
}
	
function CreateMap($layers) {
	print("\n");
	print("			var map = L.map('map').setView([0,0], 10);\n"); // your coords of your receiver
	print("			var tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {\n");
	print("			maxZoom: 50,\n");
	print("			attribution: '&copy; OpenStreetMap + AIS Viewer',\n");
	print("			}).addTo(map);\n");
	print("\n");
}

function CreateMapData($link) {
	$index 	= 0;
	$vessels = GetKnownVessels($link);
	while ($row = $vessels->fetch_assoc()) {
		$age 	= abs(time() - $row['time']);
		if ($age<=120) { createMarker($index,$row['mmsi'],$row['country'],$row['lat'],$row['lon'],"green"); }
		if ($age>120) { createMarker($index,$row['mmsi'],$row['country'],$row['lat'],$row['lon'],"purple"); }
		$index += 1;
	}
}


function createMarker($index,$mmsi,$country,$lat,$lon,$color) {
		print("			var VESSEL".$index." = L.circle([".$lat.",".$lon."], { color: '".$color."', stroke: true, fillColor: '".$color."', fillOpacity: 0.3, radius: 50}).addTo(map)");
		print(".bindPopup('<p><b>{$country}</b><br>REF: {$mmsi}</p>');\n");
		return "VESSEL" . $index ;
}

?>
