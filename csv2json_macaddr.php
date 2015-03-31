<?php

exec("cat macaddr_to_vendors.csv",$data);

$array;

foreach($data as $line) {
	
	$array[explode(",",$line)[0]] = explode(",",$line)[1];
	
}

echo(json_encode($array));

?>