<?php

// Country codes
$countries = array();
if (file_exists("scripts/country.json")) { $countries = json_decode(file_get_contents("scripts/country.json")); }

function GetCountry($db,$mmsi) {
	if (strlen($mmsi)>7) {
		$code = substr($mmsi,0,3);
		foreach ($db as $key => $value) {
			if ($key==$code) { return $value[3]; }
		}
	}
	return "Unknown";
}
// Helpers functions
function getDistance($lat1,$lon1,$lat2,$lon2) {
	$r = 6371;
	$dLat = deg2rad($lat2-$lat1);
	$dLon = deg2rad($lon2-$lon1);
	$a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
	$c = 2 * atan2(sqrt($a), sqrt(1-$a));
	$d = $r * $c; 
}
function IsChannelAB($input) { 
	return strtoupper($input)=="A" || strtoupper($input)=="B"; 
}

/*  
	Following code is based on Aaron Gong's AIS library
	2014 - Aaron Gong Hsien-Joen <aaronjxz@gmail.com>
	https://github.com/Ysurac/FlightAirMap/blob/master/require/class.AIS.php
*/

// map of 6-bit AIS STRING char values to 8-bit ascii values
$ais_map = array(
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,   0,   1,
    2,   3,   4,   5,   6,   7,   8,   9,  10,  11,
   12,  13,  14,  15,  16,  17,  18,  19,  20,  21,
   22,  23,  24,  25,  26,  27,  28,  29,  30,  31,
   32,  33,  34,  35,  36,  37,  38,  39,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  40,  41,  42,  43,
   44,  45,  46,  47,  48,  49,  50,  51,  52,  53,
   54,  55,  56,  57,  58,  59,  60,  61,  62,  63,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,   1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,   1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,   1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1
); // char 256

// map of 8-bit ascii values to 6-bit AIS STRING char values
$ais_map64 = array(
   '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
   ':', ';', '<', '=', '>', '?', '@', 'A', 'B', 'C',
   'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
   'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W',
   '`', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i',
   'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's',
   't', 'u', 'v', 'w'
); // char 64

// AIS text 8-bit ascii to 6-bit ascii
$ais_rev_unmap = array(
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  32,  33,  34,  35,  36,  37,  38,  39,
   40,  41,  42,  43,  44,  45,  46,  47,  48,  49,
   50,  51,  52,  53,  54,  55,  56,  57,  -1,  -1,
   -1,  -1,  -1,  -1,   0,   1,   2,   3,   4,   5,
    6,   7,   8,   9,  10,  11,  12,  13,  14,  15,
   16,  17,  18,  19,  20,  21,  22,  23,  24,  25,
   26,  27,  28,  29,  30,  31,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,   1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,   1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,   1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1
); // char 256

// AIS text 6-bit ascii to 8-bit ascii
$ais_unmap = array(
  '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I',
  'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S',
  'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '[', '\\', ']',
  '^', '_', ' ', '!', '\"', '#', '$', '%', '&', '\'',
  '(', ')', '*', '+', ',', '-', '.', '/', '0', '1',
  '2', '3', '4', '5', '6', '7', '8', '9', ':', ';',
  '<', '=', '>', '?',  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,

   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,   1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,   1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1,   1,
   -1,  -1,  -1,  -1,  -1,  -1,  -1,  -1
); // char 256

// AIS decoder function
// we select only a handful of message types for test purposes
// but there are much more message types from AIS we can decode.
// ref: https://faq.spire.com/available-ais-message-types

function process_ais_itu($_itu, $_len, $_filler,$cdb) {
	$x_a 	= array();
	$r 		= array();
	for ($i = 0; $i<$_len; $i++) $x_a[] = ord($_itu[$i]);
	$id = (int)ais2int($x_a, 6, 0);
	$mmsi = 0;
	$name = '';
	$imo = '';
	$csign = '';
	$hdg = 0;
	$sog = -1.0;
	$cog = 0.0;
	$lon = 0.0;
	$lat = 0.0;
	$cls = 0;
	
	if ($id >= 1 && $id <= 3) {						// Class A vessels (=1~3)
		print("*** Vessel: Class A1~3\n");
		$mmsi = ais2int($x_a, 30, 8);
		$lon = make_lonf( ais2int($x_a, 28, 61) );
		$lat = make_latf( ais2int($x_a, 27, 89) );
		$sog = (float)ais2int($x_a, 10, 50) / 10.0;
		$cog = (float)ais2int($x_a, 12, 116) / 10.0;
		$hdg = ais2int($x_a, 9, 128);
		$cls = 1;
	} else if ($id == 4) {							// Base stations
		print("*** Base Station\n");
		$mmsi = ais2int($x_a, 30, 8);
		$lon = make_lonf( ais2int($x_a, 79,28) );
		$lat = make_latf( ais2int($x_a, 107,27) );
		$cls = 1;
	} else if ($id == 5) {							// Class A vessels
		print("*** Vessel: Class A\n");
		$mmsi = ais2int($x_a, 30, 8);
		$imo = ais2int($x_a, 30, 40);
		$csign = ais2char($x_a, 7, 70);
		$name = ais2char($x_a, 20, 112);
		$name = str_replace ( '@' , '', $name );
		$cls = 1;
	} else if ($id == 6) {							// Addressed binary message
		print("*** Transmission\n");
	} else if ($id == 7) {							// Binary acknowledge
		print("*** Transmission\n");
	} else if ($id == 8) {							// Binary broadcast message
		print("*** Transmission\n");
	} else if ($id == 9) {							// Standard search and rescue aircraft position report
		print("*** Search & Rescue\n");
	} else if ($id == 10) {							// Coordinated universal time and date inquiry
		print("*** Transmission\n");
	} else if ($id == 11) {							// Coordinated universal time/date response
		print("*** Transmission\n");
	} else if ($id == 12) {							// Addressed safety related message
		print("*** Transmission\n");
	} else if ($id == 14) {							// Safety related broadcast message
		print("*** Transmission\n");
	} else if ($id == 15) {							// Interrogation
		print("*** Transmission\n");
	} else if ($id == 16) {							// Assigned mode command
		print("*** Transmission\n");
	} else if ($id == 17) {							// Global navigation-satellite system broadcast binary message
		print("*** Transmission\n");
	} else if ($id == 18) {							// Class B vessels
		print("*** Vessel: Class B\n");
		$mmsi = ais2int($x_a, 30, 8);
		$lon = make_lonf( ais2int($x_a, 28, 57) );
		$lat = make_latf( ais2int($x_a, 27, 85) );
		$sog = (float)ais2int($x_a, 10, 46) / 10.0;
		$cog = (float)ais2int($x_a, 12, 112) / 10.0;
		$hdg = ais2int($x_a, 9, 124);
		$cls = 2;
	} else if ($id == 19) {							// Class B vessels Extended (Combined with similar data from types 18, 24A, and 24B)
		print("*** Vessel: Class B+\n");
		$mmsi = ais2int($x_a, 30, 8);
		$lon = make_lonf( ais2int($x_a, 28, 61) );
		$lat = make_latf( ais2int($x_a, 27, 89) );
		$sog = (float)ais2int($x_a, 10, 46) / 10.0;
		$cog = (float)ais2int($x_a, 12, 112) / 10.0;
		$hdg = ais2int($x_a, 9, 124);
		$name = ais2char($x_a, 20, 143);
		$name = str_replace ( '@' , '', $name );
		$cls = 2;
	} else if ($id == 20) {							// Data link management message
		print("*** Transmission\n");
	} else if ($id == 21) {							// Aids-to-navigation report
		print("*** Transmission\n");
	} else if ($id == 24) {							// Class B vessels
		print("*** Vessel: Class B\n");
		$mmsi = ais2int($x_a, 30, 8);
		$pn = ais2int($x_a, 2, 38);
		if ($pn == 0) {
			$name = ais2char($x_a, 20, 40);
			$name = str_replace ( '@' , '', $name );
		}
		$cls = 2;
	} else if ($id == 27) {							// Class A or B vessels (for long-range applications)
		print("*** Vessel: Class LR A+B\n");
	}
	
	// If valid entry, populate locals
	if ($mmsi > 0 && $mmsi<1000000000) {
		$utc = time();
		$r['id'] = $id;
		$r['imo'] = $imo;
		$r['mmsi'] = $mmsi;
		$r['name'] = $name;
		$r['utc'] = $utc;
		$r['lon'] = $lon;
		$r['lat'] = $lat;
		$r['sog'] = $sog;
		$r['cog'] = $cog;
		$r['cls'] = $cls;
		$r['hdg'] = $hdg;
		$r['country'] = GetCountry($cdb,$mmsi);
	}
	//return object
	return $r;
}

function make_latf($temp) {
	$flat;
	$temp = $temp & 0x07FFFFFF;
	if ($temp & 0x04000000) {
		$temp = $temp ^ 0x07FFFFFF;
		$temp += 1;
		$flat = (float)($temp / (60.0 * 10000.0));
		$flat *= -1.0;
	} else {
		$flat = (float)($temp / (60.0 * 10000.0));
	}
	return $flat;
}

function make_lonf($temp) {
	$flon;
	$temp = $temp & 0x0FFFFFFF;
	if ($temp & 0x08000000) {
		$temp = $temp ^ 0x0FFFFFFF;
		$temp += 1;
		$flon = (float)($temp / (60.0 * 10000.0));
		$flon *= -1.0;
	} else {
		$flon = (float)($temp / (60.0 * 10000.0));
	}
	return $flon;
}

function ToHex($ais_str, $_num_bits, $bit_start_pos) {
	$bytes = $_num_bits / 8;
	$remainder = $_num_bits % 8;
	if ($remainder != 0) $bytes = $bytes + 1;
	for ($i=0; $i<$bytes; $i++) {
		$val;
		if ( $remainder && ($i == ($bytes -1))) {
			$val = ais2int($ais_str, $remainder, $bit_start_pos + ($i * 8) );
		} else {
			$val = ais2int($ais_str, 8, $bit_start_pos + ($i * 8) );
		}
	}
	return false;
}


function ais2char($ais_str, $_len, $bit_start_pos) {
	GLOBAL $ais_unmap;
	$buf = "";
	for ($i=0; $i<$_len; $i++) {
		$_tval = (int)ais2int( $ais_str, 6, $bit_start_pos + ($i * 6) );
		$tbuf = $ais_unmap[ $_tval ];
		$buf = $buf.$tbuf;
	}
	return $buf;
}

function ais2int($ais_str, $bit_size, $bit_start_pos) {
	GLOBAL $ais_map;
	$BYTE6_SIZE = 6;
	$val = 0;
	$num_byte6 = (int)($bit_size / 6);
	$remainder = $bit_size % 6;

	$start_byte6 = (int)($bit_start_pos / 6);
	$start_bit6 = $bit_start_pos % 6;
	if ($remainder)	{
		$num_byte6+=1;
		if ( ($BYTE6_SIZE - $start_bit6) < $remainder )
			$num_byte6+=1;
	} else {
		if ($start_bit6 != 0) $num_byte6+=1;
	}
	if ($num_byte6 > 1)	{
		$num_first_bit = -1;
		$num_last_bit = -1;
		$num_first_bit = $BYTE6_SIZE - $start_bit6;
		$num_last_bit = $bit_size - (($num_byte6 - 2) * $BYTE6_SIZE) - $num_first_bit;
		$temp;
		for ($i = ($num_byte6-1); $i>=0; $i--) {
			$xx = (int)$ais_map[ $ais_str[$start_byte6 + $i] ];
			if ($i == ($num_byte6-1)) {
				$xx = $xx >> (6 - $num_last_bit);
				$temp = (int)$xx;
			} else if ( $i == 0 ) {
				$xx = $xx << ($start_bit6 + 2);
				$xx = $xx & 0x000000ff;
				$xx = $xx >> ($start_bit6 + 2);
				$temp = (int)$xx;
				$temp = $temp << (( ($num_byte6-2) * 6) + $num_last_bit);
			} else {
				$temp = (int)$xx;
				$temp = $temp << (( (($num_byte6-2) - $i) * 6) + $num_last_bit);
			}
			$val = $val | (int)$temp;
		}
	} else {
		$shl = ($start_bit6 + 2);
		$xx = $ais_map [ $ais_str[$start_byte6] ] << $shl;
		$xx = $xx & 0x000000ff;
		$shr = $shl + (8 - $shl - $bit_size);
		$xx = $xx >> $shr;
		$val = (int)$xx;
	}
	return $val;
}


?>
