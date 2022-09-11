<?php

// Load defined functions and mapping
require_once("scripts/settings.php");

// initialize global variables
$raw 		= "";
$data		= array();

// create a UDP socket
if (!($sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))) {
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    die("Couldn't create socket: [$errorcode] $errormsg \n");
}
// bind the source address
if (!socket_bind($sock, $rtl_host, $rtl_port) ) {
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    die("Could not bind socket : [$errorcode] $errormsg \n");
}
// Listener
while(1) {
	if(socket_recv($sock,$raw,2045,MSG_WAITALL)===FALSE) {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);
        die("Could not receive data: [$errorcode] $errormsg \n");
    }
	$check		= 0;
	if (strlen($raw)>0) {
		$check 	= strrpos($raw,'*');
		if ($check>0) {
			$sum = 0;
			$filler = 0;
			$data = explode(",",$raw);
			$ais_channel	= $data[4];
			$ais_payload	= $data[5];
			$ais_padding	= $data[6];
			if (IsChannelAB($ais_channel)) {
				$cs = trim(substr($raw,$check + 1));
				if (strlen($cs)==2) {
					$dcs = (int)hexdec($cs);
					for ($alias=1; $alias<$check; $alias++) { $sum ^= ord($raw[$alias]); }
					if ($sum == $dcs ){
						$filler += (int)$ais_padding[0];
						$result = array();
						$result =  process_ais_itu($ais_payload,strlen($ais_payload),$filler,$countries);
						if (count($result)>0) {
							if (!$result['lon']==0&&!$result['lat']==0) {
								if (SeenReport($sql_link,$result['mmsi'])==0) {
									print("-> '{$result['mmsi']}' from {$result['country']} at {$result['lat']}°, {$result['lon']}°\n");
									CreateReport($sql_link,$result['utc'],$result['mmsi'],$result['country'],$result['lon'],$result['lat'],$result['sog'],$result['cog'],$result['hdg']);
								} else {
									print("<- '{$result['mmsi']}' from {$result['country']} at {$result['lat']}°, {$result['lon']}°\n");
									UpdateReport($sql_link,$result['utc'],$result['mmsi'],$result['country'],$result['lon'],$result['lat'],$result['sog'],$result['cog'],$result['hdg']);
								}
							}
						}
					}
				}
			}
		}
	}
}
// Close link
$sql_link->close();
?>
 
