#!/usr/local/bin/php -q

<?php
	set_include_path("/var/bdtk/bigdata_twitter/twitter_json_search");
	include_once('functions.php');

	if(CLI){
		if($argc <=  5){
			echo "Usage: php liveGeoTweetsToDB.php <Lat> <lon> <rad> <unit> <DB_Table> [<number of results> <TwitterID> ]\n";
			echo "where options include:\n";
			echo "\t Lat:         Lat of Centre Point \n";
			echo "\t Lon:         Lon of Centre Point \n";
			echo "\t Rad:         Radius in Km of search \n";
			echo "\t Unit:        Miles or Kilometers (mi | km) \n";
			echo "\t DB_Table:           Database Table to Write Twitter Results\n";
			echo "\t Number of Results:  Number of results to return query (Default: 100 - Max 100) \n";
			echo "\t TwitterID:          Return results posted after specific Twitter Status Identifier\n\n";
			exit(); 
		}else{
			$lat = $argv[1];
			$lon = $argv[2];
			$rad = $argv[3];
			$unit = $argv[4];
			$table = $argv[5];
			$num =  (int)$argv[6];
			$inc = (int)$argv[7];

			// Create the temp Table for data collection
                        $temp_table =  $table."_temp";

			if($table != "")
				$dbExist = doesTableExist($table, $db);

			if($table == "" || ($dbExist == 0) ){
				if(AUTO_CREATE && $table != ""){
					createJSONTwitterTableStore($table, "AutoCreated Table for Twitter Posts", $db);
                                        createJSONTwitterTableStore($temp_table, "AutoCreated Table for Final Twitter Posts", $db);
					addCollection($collection_name, $search, $table, $db);
				}else{
					exit("The Table [$table] does not exist in the Database [$db_name]! \nPlease Create using createTable.php script.\n\n");
				}
			}
		}
	}

	$alreadyStored = 0;
	$numResults = 0;

	echo "Performing Live Geo Query on Twitter of Center [$lat, $lon, $rad $unit] every ".LIVE_REFRESH." seconds ...\n";
	echo "Results: \n\n";

	$search = urlencode($search);

	while(true){
		$start = getTime();
		$url = $baseURL."searchGeoTwitter.php?lat=$lat&lon=$lon&rad=$rad";

		$json_string = searchGeoTwitterCURL($lat, $lon, $rad, $unit);

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
                        //echo "empty";
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
