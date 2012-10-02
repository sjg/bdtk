<?php 

	include_once('./functions.php'); 

	$search = "twitter";
	$base_url = "http://search.twitter.com/search.json";
	$extenstion = "?q=".urlencode($search);
	
	//$extenstion = "?geocode=37.781157,-122.398720,1mi";
	//$extenstion = "?geocode=51.5001524,-0.1262362,30mi";
	
	$table_name = "twitter_collection_".$search;

	echo "\n\nTable Check: ".$table_name."\n";
	$tableID = tableExists($table_name);
	
	if($tableID == 0){
		//No Table Exists - Create Table
		echo "No Table - Creating New Twitter Table";
		$sql =  (SQLBuilder::createTable(array($table_name => array('twitter_id'=>'NUMBER',
																	'tweet' => 'STRING',
																	'from_user_name' => 'STRING',
																	'from_user_id' => 'NUMBER',
																	'date' => 'DATETIME', 
																	'lang' => 'STRING',
																	'geo' => 'LOCATION',
																	'location' => 'STRING',
																	'to_user_name' => 'STRING ',
																	'to_user_id' => 'NUMBER',
																	'source' => 'STRING',
																	'profile_image' => 'STRING',
																	'metadata' => 'STRING',
																	'place_id' => 'STRING',
																	'place_type' => 'STRING',
																	'place_full_name' => 'STRING'
																	))));
		$tableID = $ftclient->query($sql); 
		$tableID = str_replace("tableid\n", "",$tableID);
		echo "\nCurrent Table ID:- ".$tableID."\n"; 
	}else{
		echo "Yip Table is there - Table ID: ".$tableID."\n";
	}
	
	
	while(true){
		$start = getTime(); 
				
		$twitter_json = get_data($base_url.$extenstion."&rpp=100");
		$dataArray = json_decode($twitter_json, true);
		$raw_data = $dataArray['results'];
		echo "Total Tweets this Refresh: ".sizeof($raw_data)."\n"; 
	
		if(sizeof($dataArray['results']) != 0){
			foreach($dataArray['results'] as $key => $result) {		
			
				$insert_sql = SQLBuilder::insert($tableID, array('twitter_id'=> $result['id'],
															 'tweet' => addslashes($result['text']),
															 'from_user_name' => $result['from_user'],
															 'from_user_id' => $result['from_user_id'],
															 'date' => $result['created_at'], 
															 'lang' => $result['iso_language_code'],
															 'geo' => $result['geo']['coordinates'][0].",".$result['geo']['coordinates'][1],
															 'location' => $result['location'],
															 'to_user_name' => $result['to_user'],
															 'to_user_id' => $result['to_user_id'],
															 'source' => $result['source'],
															 'profile_image' => addslashes($result['profile_image_url']),
															 'metadata' => $result['metadata']['result_type'],
															 'place_id' => $result['place']['id'],
															 'place_type' => $result['place']['type'],
															 'place_full_name' => $result['place']['full_name']
															 ));
				
				$rowID_raw =  $ftclient->query($insert_sql);
				$rowID_compare = str_replace("rowid\n", "",$rowID_raw);
								
				if((int)($rowID_compare) == 0){
					//echo " Error: ".$rowID_raw."\n";
					//echo "RawSQL: ".$insert_sql."\n";
				}else{
					//echo "Good Insert! id:- ".$rowID_compare."\n";
				}
			}
		}
	
		$extenstion = $dataArray['refresh_url'];
	
		$end = getTime(); 
		echo "\t - Time to Process : ".number_format(($end - $start),4)." secs\n"; 
	
		sleep(4); 
	}

?>