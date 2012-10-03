#!/usr/local/bin/php -q

<?php

	define("CLI", !isset($_SERVER['HTTP_USER_AGENT']));

	if(CLI){
		if($argc <=  6){
			echo "Usage: php createCollector.php <id> <Lat> <lon> <rad> <unit> <file-out>\n";
			echo "where options include:\n";
			echo "\t id:       ID of the Collector  \n";
			echo "\t Lat:      Lat of Centre Point \n";
			echo "\t Lon:      Lon of Centre Point \n";
			echo "\t Rad:      Radius in Km of search \n";
			echo "\t Unit:     Miles or Kilometers (mi | km) \n";
			echo "\t FileOut:  Where to save the file \n\n";
			exit(); 
		}else{
			$id = $argv[1];
			$lat = $argv[2];
			$lon = $argv[3];
			$rad = $argv[4];
			$unit = $argv[5];
			$fileout = $argv[6];

			//Create the Collector 
			$baseCollector = file_get_contents('./base.txt');
			//$baseCollector = file_get_contents('/var/bdtk/core/bdtk/base.txt');

			$collector = str_replace("<<ID>>",   $id,   $baseCollector);
			$collector = str_replace("<<LAT>>",  $lat,  $collector);
			$collector = str_replace("<<LON>>",  $lon,  $collector);
			$collector = str_replace("<<RAD>>",  $rad,  $collector);
			$collector = str_replace("<<UNIT>>", $unit, $collector);

			//Write out file
			$fp = fopen($fileout, 'w');
			fwrite($fp, $collector);
			fclose($fp);

			echo "Collector file has been written to $fileout \n\n";

		}
	}
?>