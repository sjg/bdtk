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
					createJSONTwitterTableStore($table, "AutoCreated Table for Twitter Posts", $db);
					createJSONTwitterTableStore($temp_table, "AutoCreated Table for Final Twitter Posts", $db);
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

		$json_string = searchTwitterCURL($search);

		$json_array = json_decode($json_string, 1);

		if(!empty($json_array['results'])){
			foreach($json_array['results'] as $item){
				if(!isItInDB(stripIDTag($item[id],$db), $item, $temp_table ,$db)){
                                      writeJSONToDB($item, $temp_table, $db);
                                      $numResults++;
                                      $count++;
                                }else{
                                      $alreadyStored++;
                               }
			}
		}else{
			$nowEmpty = true;
			echo "empty";
		}

		$count = 0;

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
