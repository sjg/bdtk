#!/usr/local/bin/php -q

<?php

	include_once('functions.php');

	if(CLI){
		if($argc <=  7){
			echo "Usage: php liveGeoSearchToDB.php <Search> <Lat> <lon> <rad> <unit> <DB_Table> <CollectionName> [<number of results> <TwitterID> ]\n";
			echo "where options include:\n";
			echo "\t Search:       Text to search with \n";
			echo "\t Lat:         Lat of Centre Point \n";
			echo "\t Lon:         Lon of Centre Point \n";
			echo "\t Rad:         Radius in Km of search \n";
			echo "\t Unit:        Miles or Kilometers (mi | km) \n";
			echo "\t DB_Table:           Database Table to Write Twitter Results\n";
			echo "\t Collection:           Name for this collection\n";
			exit(); 
		}else{
			$search = $argv[1];
			$lat = $argv[2];
			$lon = $argv[3];
			$rad = $argv[4];
			$unit = $argv[5];
			$table = $argv[6];
			$collection_name = $argv[7];
			
			if($table != "")
				$dbExist = doesTableExist($table, $db);
			
			if($table == "" || ($dbExist == 0) ){
				if(AUTO_CREATE && $table != ""){
					createGeoTwitterTableStore($table, "AutoCreated Table for Geo Twitter Posts", $db);
					addCollection($collection_name, $search, $table, $db);
				}else{
					exit("The Table [$table] does not exist in the Database [$db_name]! \nPlease Create using createTable.php script.\n\n");
				}
			}
		}
	}

	$alreadyStored = 0;
	$numResults = 0;

	echo "Performing Live Geo Query on Twitter for [$search] on Center [$lat, $lon, $rad $unit] every ".LIVE_REFRESH." seconds ...\n";
	echo "Results: \n\n";
	
	$search = urlencode($search);
	
	while(true){
		$start = getTime();
		$url = $baseURL."searchGeoTwitter.php?lat=$lat&lon=$lon&rad=$rad";
		
		$json_string =  searchGeoTwitterWithQueryUnitCURL($lat, $lon, $rad, $unit, $search); 
		
		$json = json_decode($json_string);
							
		if(sizeof($json->results) != 0){
			foreach($json->results as $item){	
    	    		
    	    			if( !isItInDB( $item->id ,$table ,$db) ){	
    	    				writeJSONGeoItemToDB($item, $table, $db);
					$numResults++;
				}else{
					$alreadyStored++;
				}
	    	}

		}else{
			$nowEmpty = true; 
		}
	
		$end = getTime();
	
		$total += $numResults;

		echo "Current Total number of Tweets: ".$total."\n"; 
		echo "\t - Total Tweets this Refresh: ".$numResults."\n";
		echo "\t - Time to Process : ".number_format(($end - $start),4)." secs\n"; 
		
		$numResults = 0;
				
		if(LIVE_REFRESH == "" || LIVE_REFRESH == 0){
			sleep(10);
		}else{
			sleep(LIVE_REFRESH);
		}
	}
?>
