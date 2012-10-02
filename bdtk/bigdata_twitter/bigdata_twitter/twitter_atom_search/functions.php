<?php

	//Start of Config Variables
	define("CLI", !isset($_SERVER['HTTP_USER_AGENT']));
	define('MAGPIE_CACHE_ON', 0);
	define("DB_NEEDED", 0);
	define("LIVE_REFRESH", 5);  //Time in Seconds for Live Refresh
	define("AUTO_CREATE", 1);   //Auto Create Tables that Don't exist!

	error_reporting(0);

	//BaseURL of Site (with Trailing Slash)
	$baseURL = "http://localhost/~sjg/TwitterTools/WEB/";

	$db_name = "bigdata";
	$bdtk = "bdtk";
	$db_host = "localhost";
	$db_user = "bigdata1";
	$db_pass = "bigdata1";

	// Visualisation Update - in minutes
	$viz_update = 60;
	$maxNumtoLeave = 100;

	$db = mysql_pconnect($db_host, $db_user, $db_pass);
	mysql_select_db($db_name, $db);

	require_once 'lib/magpieRSS/rss_fetch.inc';

	//Start of Standard Functions
	function searchTwitterCURL($search, $page=1, $num=100, $inc=0){
		//Return RSS Feed from Twitter Result
		$query = $search;

		//Strip any Hash Characters
		$query = str_replace("#", "", $query);
		$query = str_replace("+", " ", $query);

		$search_url = "http://search.twitter.com/search.atom?q=" . urlencode($query) . "&rpp=100";

		echo $search_url;

		$curl = curl_init();
		curl_setopt ($curl, CURLOPT_URL, $search_url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec ($curl);
		curl_close ($curl);

		return($result);
	}

	function searchGeoTwitterCURL($lat, $lon, $rad, $unit, $num=100, $page=1, $inc=0){
		//Return RSS Feed from Twitter Result
		$query = $lat.",".$lon.",".$rad.$unit;

		$search_url = "http://search.twitter.com/search.atom?geocode=" . urlencode($query) . "&rpp=$num";

		$curl = curl_init();
		curl_setopt ($curl, CURLOPT_URL, "$search_url");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec ($curl);
		curl_close ($curl);
		return($result);
	}

	function searchGeoTwitterWithQueryCURL($lat, $lon, $rad, $search, $num=100, $page=1, $inc=0){
		//Return RSS Feed from Twitter Result
		$latquery = $lat.",".$lon.",".$rad."km";

		$query = $search;
		//Strip any Hash Characters
		$query = str_replace("#", "", $query);
		$query = str_replace("+", " ", $query);

		$search_url = "http://search.twitter.com/search.atom?q=" . urlencode($query) . "&geocode=" . urlencode($latquery) . "&rpp=$num";

		$curl = curl_init();
		curl_setopt ($curl, CURLOPT_URL, "$search_url");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec ($curl);
		curl_close ($curl);

		return($result);
	}

	function searchGeoTwitterWithQueryUnitCURL($lat, $lon, $rad, $unit, $search, $num=100, $page=1, $inc=0){
		//Return RSS Feed from Twitter Result
		$latquery = $lat.",".$lon.",".$rad.$unit;

		$query = $search;
		//Strip any Hash Characters
		$query = str_replace("#", "", $query);
		$query = str_replace("+", " ", $query);


		$search_url = "http://search.twitter.com/search.atom?q=" . urlencode($query) . "&geocode=" . urlencode($latquery) . "&rpp=$num"; 

		echo $search_url;


		$curl = curl_init();
		curl_setopt ($curl, CURLOPT_URL, "$search_url");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec ($curl);
		curl_close ($curl);

		return($result);
	}

	function stripIDTag($atomIDfield){
		//Return the Status ID for each Twitter Tag
		//     Example Param  = tag:search.twitter.com,2005:6893772642
		//     Example Return = 6893772642
		$arrayField = split(":", $atomIDfield, 3);
		return ($arrayField[2]);
	}

	function parseDateTime($string){
		//Return DateTime for insert into Database
		//	Example Param  = 2009-12-21T13:58:06Z
		//	Example Return = 2009-12-21 13:58:06
		$string = str_replace('T',' ', $string);
		$string = str_replace('Z','',  $string);
		return ($string);
	}

	function writeToDB($item, $table, $db){
		$post = mysql_escape_string(trim(strip_tags($item[title])));
		$name = mysql_escape_string(trim(strip_tags($item[author_name])));

		$sql = "INSERT INTO `".$table."`(twitterPost, twitterID, dateT, name, link, upage, twittergeo, lang, profile) VALUES('$post','".stripIDTag($item[id])."','$item[published]','$name','$item[link]','$item[author_uri]', '".$item[twitter]['geo']."', '".$item[twitter]['lang']."','".$item['link_image']."')";
		if(!mysql_query($sql, $db)){
			echo $sql."\n";
		}else{
			if(CLI){
				echo $name.":- ".$post."\n"; 
			}
		 	return(1);
		}
	}
	
	function writeGeoItemToDB($item, $table, $db){
		$post = mysql_escape_string(trim(strip_tags($item[title])));
		$name = mysql_escape_string(trim(strip_tags($item[author_name])));
		$atom_content = mysql_escape_string(trim(strip_tags($item[atom_content])));
		$g_location = mysql_escape_string(trim(strip_tags($item[google]['location'])));
		$source = mysql_escape_string(trim(strip_tags($item[twitter]['source'])));
		
		$geopoint = "";
				
		if(trim($item[twitter]['geo']) == ""){
			//Try GeoRSS
			if($item[georss]['geo_point'] != ""){
				$geopoint = $item[georss]['geo_point'];
			}
		}else{
			$geopoint = $item[twitter]['geo'];
		}
		
		
		$sql = "INSERT INTO `".$table."`(twitterPost, twitterID, dateT, name, link, upage, twittergeo, lang, profile, google_location, atom_content, source) VALUES('$post','".stripIDTag($item[id])."','$item[published]','$name','$item[link]','$item[author_uri]', '".$geopoint."', '".$item[twitter]['lang']."','".$item['link_image']."', '".$g_location."', '".$atom_content."', '".$source."')";
		if(!mysql_query($sql, $db)){
			echo $sql."\n";
		}else{
			if(CLI){
				//echo $name.":- ".$post."\n"; 
			}
		 	return(1);
		}
	}
	
	function writeGeoItemToDBTOM($item, $table, $db){
		$post = mysql_escape_string(trim(strip_tags($item[title])));
		$name = mysql_escape_string(trim(strip_tags($item[author_name])));
		$atom_content = mysql_escape_string(trim(strip_tags($item[atom_content])));
		$g_location = mysql_escape_string(trim(strip_tags($item[google]['location'])));
		$source = mysql_escape_string(trim(strip_tags($item[twitter]['source'])));
		
		$geopoint = "";
				
		if(trim($item[twitter]['geo']) == ""){
			//Try GeoRSS
			if($item[georss]['geo_point'] != ""){
				$geopoint = $item[georss]['geo_point'];
			}
		}else{
			$geopoint = $item[twitter]['geo'];
		}
		
		
		$sql = "INSERT INTO `".$table."`(twitterPost, twitterID, dateT, name, link, upage, twittergeo, lang, profile, google_location, atom_content, source) VALUES('$post','".stripIDTag($item[id])."','$item[published]','$name','$item[link]','$item[author_uri]', '".$geopoint."', '".$item[twitter]['lang']."','".$item['link_image']."', '".$g_location."', '".$atom_content."', '".$source."')";
		if(!mysql_query($sql, $db)){
			echo $sql."\n";
		}else{
			if(CLI){
				//echo $name.":- ".$post."\n"; 
			}
		 	return(1);
		}
	}

	function isItInDB($twitterID, $item, $table, $db){
		$sql = "SELECT twitterID FROM `".$table."` WHERE twitterID = '$twitterID'";
		$res = mysql_query($sql, $db);
 		$num_rows = mysql_num_rows($res);
 	
		if($num_rows == 0){
			return(false);
		}else{
			return(true);
		}
	}

	function createTwitterTableStore($tableName, $tableComment, $db){
		global $db_name;
		$createTableSql = "CREATE TABLE IF NOT EXISTS `$tableName` (
  								`twitterID` varchar(255) NOT NULL,
  								`twitterPost` longtext NOT NULL,
  								`dateT` datetime NOT NULL,
  								`name` varchar(255) NOT NULL,
  								`link` longtext NOT NULL,
  								`upage` longtext NOT NULL,
  								`twittergeo` varchar(255) DEFAULT NULL,
  								`lang` varchar(255) DEFAULT NULL,
  								`profile` varchar(255) DEFAULT NULL,
  								`google_location` varchar(255) DEFAULT NULL,
  								`atom_content` varchar(255) DEFAULT NULL,
  								`source` varchar(255) DEFAULT NULL,
  								 INDEX (twitterID)
							) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='".$tableComment."'";
		$res = mysql_query($createTableSql, $db);
			
		if($res){
			echo "Created [$tableName] in Database [$db_name] for storing Twitter Posts\n";
		}else{
			echo "Failed to create [$tableName] in Database [$db_name]!\n\n";
			exit();
		}
		
		return($res);
	}
	
	function createGeoTwitterTableStore($tableName, $tableComment, $db){
		global $db_name;
		$createTableSql = "CREATE TABLE IF NOT EXISTS `$tableName` (
  								`twitterID` varchar(255) NOT NULL,
  								`twitterPost` longtext NOT NULL,
  								`dateT` datetime NOT NULL,
  								`name` varchar(255) NOT NULL,
  								`link` longtext NOT NULL,
  								`upage` longtext NOT NULL,
  								`twittergeo` varchar(255) DEFAULT NULL,
  								`lang` varchar(255) DEFAULT NULL,
  								`profile` varchar(255) DEFAULT NULL,
  								`google_location` varchar(255) DEFAULT NULL,
  								`atom_content` varchar(255) DEFAULT NULL,
  								`source` varchar(255) DEFAULT NULL,
  								 INDEX (twitterID)
  							) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='".$tableComment."'";
		$res = mysql_query($createTableSql, $db);
		
		if($res){
			echo "Created [$tableName] in Database [$db_name] for storing Twitter Posts\n";
		}else{
			echo "Failed to create [$tableName] in Database [$db_name]!\n\n";
			exit();
		}
		
		return($res);
	}
	
	function doesTableExist($tbl, $db){
    		$q = mysql_query("SHOW TABLES", $db);
    		while ($r = mysql_fetch_array($q)) { $tables[] = $r[0]; }
    		mysql_free_result($q);
    		if (in_array($tbl, $tables)) { return TRUE; }
    		else { return FALSE; }
		} 
	
	function getTime() { 
    		$a = explode (' ',microtime()); 
    		return(double) $a[0] + $a[1]; 
   	 } 
    
    	function addCollection($name, $search, $data_table, $db){
   		global $db_name, $bdtk;
   		mysql_select_db($bdtk, $db);
   	   		
   		$now = "";
   		
   		$sql = "INSERT INTO bdtk_twitter_stats (collection_name, search_term, start_date, last_update, data_valid, viz_update, data_table) 
   										VALUES ('$name', '$search', NOW(), NOW(), NOW(), DATE_ADD(now(),interval 1 minute), '$data_table')";
   										
   		$res = mysql_query($sql, $db) or die(mysql_error());
   		
   		if($res){
			echo "Collection Added \n";
		}else{
			echo "Failed to add Collection\n\n";
		}
   		   		
   		//Just incase - Select the dataStore datab
   		mysql_select_db($db_name, $db);
   	}


	function saveAndTruncate($tempTablename,  $db){
        		global $tableArray, $maxNumtoLeave;
		
		//Take "_temp" out of the name and then 
		$masterTable =  str_replace("_temp", "", $tempTablename);
		
		// Loop around each table specified in the table Array
		$sql = "SELECT * FROM `$tempTablename` ORDER BY dateT desc";
		$res = mysql_query($sql, $db);
				
		if($res){												(int)$truncateNum = mysql_num_rows($res) - $maxNumtoLeave;
		
			if($truncateNum > 0){
			$sql = "SELECT * FROM `$tempTablename` group by twitterID ORDER BY dateT asc LIMIT $truncateNum ";
					
			$copySQL = "INSERT INTO `$masterTable` ( twitterID, twitterPost,  dateT, `name`, link, upage, twittergeo, lang, profile, google_location, atom_content, source) $sql";
						
			$res2 = mysql_query($copySQL);
			if($res2){
					$deleteSQL = "DELETE FROM `$tempTablename` ORDER BY dateT asc LIMIT $truncateNum"; 
					$res3 = mysql_query($deleteSQL);
					if($res3){
						echo "\t\t\tTable:  $tempTablename- Truncate Complete! - Copied $truncateNum Records \n";
					}else{
						echo "\t\t\Failed Delete at $tempTablename";
						echo $deleteSQL;
					}
			}else{
				echo "\t\t\Table: $tempTablename- Copy Failed! No Truncation!\n";
				echo $copySQLL;
			}
		}
		}
        }
        
        function deleteDuplicatesFromTable($tempTableName, $db){       
        		$finalTable =  str_replace("_temp", "", $tempTablename);
        	
        		$sql = "CREATE TABLE if not exists ".$finalTable."_final"." LIKE $finalTable"; 
        		$sql2 = "TRUNCATE $finalTable"; 
        		$sql3 = "INSERT into $finalTable SELECT * FROM $finalTable group by twitterID";
        	  	
        		$res = mysql_query($sql, $db);
        		$res = mysql_query($sql2, $db);
        		$res = mysql_query($sql3, $db);
        	
        		return($res);
        }
       
       function updateUI($tablename, $minToNextUpdate ,$db){
       		global $bdtk;
       		$minToNextUpdate = (int)$minToNextUpdate;
       		
       		mysql_select_db($bdtk, $db);
       		
       		$sql = "UPDATE bdtk_twitter_stats SET last_update = Now(), data_valid = now(), viz_update = date_add(now(),interval $minToNextUpdate minute) where data_table = '$tablename'";
       	      		
  		if(mysql_query($sql, $db)){
  			echo "\t\t - Visuals Updated\n\n"; 
  		}else{
   			echo "\t\t - Visual Update Failed - ".mysql_error($db)."!\n\n" ; 
   		} 		
   		
   		//Just incase - Select the dataStore datab
   		mysql_select_db($db_name, $db);
       }
       
        	
?>
