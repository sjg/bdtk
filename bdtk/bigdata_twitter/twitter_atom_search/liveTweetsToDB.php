#!/usr/local/bin/php -q

<?php

	include_once('./functions.php');

	if(CLI){
		if($argc <=  2){
			echo "Usage: php liveTweetsToDB.php <Search Tag> <DB_Table> [<number of results> <TwitterID> ]\n";
			echo "where options include:\n";
			echo "\t Search Tag:         NB: HashTags have to be wrapped in Quotes (eg. \"#uksnow\")\n";
			echo "\t DB_Table:           Database Table to Write Twitter Results\n";
			echo "\t Collection Name: 	 Name for this Collection";
			echo "\t Number of Results:  Number of results to return query (Default: 100 - Max 100) \n";
			echo "\t TwitterID:          Return results posted after specific Twitter Status Identifier\n\n";
			exit(); 
		}else{
			$search = $argv[1];
			$table = $argv[2];
			$collection = $argv[3];
			$num =  (int)$argv[4];
			$inc = (int)$argv[5];		
			
			$table = str_replace("'", "", $table);
			$collection = str_replace("'", "", $collection);
			$search = str_replace("'", "", $search);  

			// Create the temp Table for data collection
			$temp_table =  $table."_temp";
		
			if($table != "")
				$dbExist = doesTableExist($table, $db);
				
			if($collection == "")
				$collection = $search;
			
			if($table == "" || ($dbExist == 0) ){
				if(AUTO_CREATE && $table != ""){
					createTwitterTableStore($table, "AutoCreated Table for Twitter Posts", $db);
					createTwitterTableStore($temp_table, "AutoCreated Table for Final Twitter Posts", $db);
					addCollection($collection, $search, $table, $db);
				}else{
					exit("The Table [$table] does not exist in the Database [$db_name]! \nPlease Create using createTable.php script.\n\n");
				}
			}
		}
	}
	
	$alreadyStored = 0;
	$numResults = 0;
	$countZero = 0;

	echo "Performing Live Query on Twitter of [$search] every ".LIVE_REFRESH." seconds ...\n";
	echo "Results: \n\n";
		
	while(true){
		$start = getTime(); 
					
		
		$rss_string = searchTwitterCURL($search);
		$rss = new MagpieRSS($rss_string);
		
		if(!empty($rss->items)){
			
			foreach($rss->items as $item){	
    	    	if(!isItInDB(stripIDTag($item[id],$db), $item, $temp_table ,$db)){	
					writeGeoItemToDB($item, $temp_table, $db);
					$numResults++;
					$count++;
				}else{
					$alreadyStored++;
				}
	    	}

		}else{
			$nowEmpty = true; 
		}
				
		//$tpm = (int)($count * (60 / LIVE_REFRESH));
		//echo "Current Tweets per Minute [$search] == $tpm\n";
		
		$count = 0;
		
		//if($tpm == 0){
		//	$countZero++; 
		//}else{
		//	$countZero = 0; 
		//}
		
		//if($countZero == 2){
		//	$sql = "UPDATE tweetometer SET tpm=0, totalposts=$count WHERE city = 'allCClive'";
		//	mysql_query($sql, $db2);
		//	echo "Flatline! \n";
		//	$countZero = 0;
		//}
		
		
		//if($tpm != 0){
		//	$sql = "UPDATE tweetometer SET tpm=$tpm, totalposts=$count WHERE city = 'allCClive'";
		//	mysql_query($sql, $db2);
		//}
	
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
