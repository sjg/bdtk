<?php
	include_once('functions.php');

	$extrafields = "ParsedGeoLocations";


	// Script to write out all Lat Lons from Twitter GeoSearch
	if(CLI){
		if($argc <=  2){
			echo "\nUsage: php parseTweetTable.php <Table> <Output File>\n";
			echo "where options include:\n";
			echo "\t Table:         The Table you want to parse. \n";
			echo "\t Output File:   The CSV you want to write to on the filesystem. Will write over the original file if exists \n\n";
			exit();
		}else{
			$table = $argv[1];
			$output = $argv[2];
		}
	}

	function writeLine($fh, $id, $location, $db){
		global $table;

		$sql = "SELECT * FROM $table WHERE id = $id";
		$res = mysql_query($sql, $db);

		if(!$res)die();

		$item = mysql_fetch_assoc($res);
		$s = "";

		foreach($item as $val){
			$val = str_replace("\"", "\"\"", $val);
			$s .= "\"$val\",";
		}

		if(is_array($location)){
			$s .= stripStrangeChars($location[0]).' '.stripStrangeChars($location[1])."\"\n";
		}else{
			$s .= $location."\"\n";
		}

		fwrite($fh, $s);
	}

	function stripStrangeChars($string){
		return(preg_replace("/^[^0-9\.\-]+/", '', $string));
	}

	function matchLat($lat){
		return(preg_match("/^(\+|-)?(\d\.\d{1,6}|[1-8]\d\.\d{1,6}|90\.0{1,6})$/", $lat) == 1);
	}

	function matchLon($lon){
		return(preg_match("", $lon) == 1);
	}

	function matchedLatLon($string){
		$array = split(",", $string, 2);

		if(sizeof($array) == 2){
			if((double)stripStrangeChars($array[0]) != 0 || (double)stripStrangeChars($array[1]) != 0){
				if(matchLat((double)stripStrangeChars($array[0]))){
					return $array;
				}
			}
		}

		return(false);

	}

	function stripPunc($string){
		return(preg_replace("'(\xBB|\xAB|!|\xA1|%|:|;|\(|\)|\&|\"|\'|\/|\\?|\\\)'", '', $string));
	}

	function stripAllAlphabet($string){
		return(preg_replace("'[a-zA-Z\s]'", '', $string));
	}

	function stripLeadingChr($string){
		return(substr($string, 0, (strlen($string)-1))); 
	}


	function writeCSVTableHeader($fh, $db){
		global $table, $extrafields;
		$sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.Columns where TABLE_NAME = '$table'";
		$res = mysql_query($sql, $db);
		$s = "";

		while($c = mysql_fetch_assoc($res)){
			$s .= $c[COLUMN_NAME].",";
		}

		//Strip Leading Comma
		$s = stripLeadingChr($s);
		$s .= ",".$extrafields;

		$s .= "\n";

		fwrite($fh, $s);
	}

	$db = mysql_connect($db_host, $db_user, $db_pass);
	mysql_select_db($db_name, $db);

	$sql = "SELECT twitterID, twittergeo FROM `$table` Order by twitterID";
	$res = mysql_query($sql, $db);

	if(!$res){
		echo mysql_error();
		die();
	}

	$count = 0;

	$fh = fopen($output, 'w') or die("can't open file");

	writeCSVTableHeader($fh, $db);

	while ($c = mysql_fetch_assoc($res)){
		if($c['twittergeo'] != ''){
			writeLine($fh, $c['id'], $c['twittergeo'], $db);
			$count++;
		}

		unset($c);
		unset($arr);
	}

	fclose($fh);

	echo "Written $count lines to file :- $output\n";
?>
