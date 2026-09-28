<?php 
/*

Version 1.0 - 2014.11.01

    Uses icon from http://www.streamlineicons.com/ Free Pack via https://www.iconfinder.com/icons/185036/remote_control_streamline_icon#size=128
    (c) 2014 Barry Schiffer. Based on code provided by Barry Schiffer at http://www.barryschiffer.com/using-synology-disk-station-wake-lan
    (c) 2014 Manuel Azevedo <azevedo.manuel@gmail.com> 

*/


flush();
// __DIR__, damit die Datei auch von der Kommandozeile (cron.php) aus geht,
// wo das Arbeitsverzeichnis ein beliebiges sein kann.
require_once __DIR__ . '/config.php';
 
/*
  Schickt das Magic Packet an mehrere Ziele statt nur an eines: an die
  Broadcast-Adresse aus config.php, an den allgemeinen Broadcast
  255.255.255.255 und - falls eine IPv4 hinterlegt ist - direkt an das Gerät,
  jeweils auf dem eingestellten Port sowie auf 9 und 7. Manche Netzwerkkarten
  und Switches verschlucken ein einzelnes Paket; der direkte Versand hilft,
  wenn das Gerät in einem anderen Subnetz liegt und der Router Unicast-WoL
  weiterleitet. Gilt als erfolgreich, sobald mindestens ein Paket rausging -
  der direkte Versand scheitert bei einem schlafenden Gerät im selben Netz
  regelmässig, weil niemand auf die ARP-Anfrage antwortet.
*/
function WakeOnLan($addr, $mac, $socket_number, $device_ip = '') {

	if (strlen($mac) != 17)
		return FALSE;

	if (preg_match('/[^A-Fa-f0-9:]/',$mac))
		return FALSE;

	$addr_byte = explode(':', $mac);
	$hw_addr   = '';

	for ($a=0; $a <6; $a++)
		$hw_addr .= chr(hexdec($addr_byte[$a]));

	$msg = chr(255).chr(255).chr(255).chr(255).chr(255).chr(255);

	for ($a = 1; $a <= 16; $a++)
		$msg .= $hw_addr;

	$hosts = [$addr, '255.255.255.255'];
	if (filter_var($device_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false)
		$hosts[] = $device_ip;
	$hosts = array_unique($hosts);
	$ports = array_unique([(int)$socket_number, 9, 7]);

	$s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
	if ($s === FALSE)
		return FALSE;

	if (!socket_set_option($s, SOL_SOCKET, SO_BROADCAST, TRUE)) {
		socket_close($s);
		return FALSE;
	}

	$sent = 0;
	foreach ($hosts as $host)
		foreach ($ports as $p)
			if (@socket_sendto($s, $msg, strlen($msg), 0, $host, $p) !== FALSE)
				$sent++;

	socket_close($s);
	return $sent > 0;
}
 
function PopulateMACList($maclist) {

	// Takes an array in the format "key" => "mac"
	// Cycles through the array
	// Checks if MAC contains only valid caracters ( Aa-Ff 0-9 . : - )
	// If valid, removes any ". : -" it finds
	// Creates a <option> tag with XX:XX:XX:XX:XX:XX format
	
	foreach ($maclist as $host => $mac) 
		if (!preg_match('/[^A-Fa-f0-9\.-:]/',$mac)) {
			$mac=preg_replace('/[^A-Fa-f0-9]/', "",$mac);
			$mac=join(":",str_split($mac,2));
			if (strlen($mac) == 17) {
				echo "<option value=\"".$mac."\">".$host."</option>\n";
			}
		}
}

?>
