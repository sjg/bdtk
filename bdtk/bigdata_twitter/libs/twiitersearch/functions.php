<?php

	include('../mysql-tables-php/sql.php');

	define("DB_NEEDED", 1);

	$db_name = "bigdata";
	$db_host = "localhost";
	$db_user = "bigdata1";
	$db_pass = "bigdata1";

	if(DB_NEEDED){
		$db = mysql_pconnect($db_host, $db_user, $db_pass);
		mysql_select_db($db_name, $db);
	}

	function get_data($url)
	{	
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	function getTime() 
    { 
    	$a = explode (' ',microtime()); 
    	return(double) $a[0] + $a[1]; 
    } 
    
    // Fusion Table Functions
    
    $token = ClientLogin::getAuthToken('frogosteve@gmail.com', decode('i4v5q584f4l4w3d4', 'twitterCollector'));
	$ftclient = new FTClientLogin($token);
    
    function tableExists($table_name){
    	global $ftclient; 
    	
    	//Check if TableName Exists in Fusion Table
    	$csv_String =  $ftclient->query(SQLBuilder::showTables());
    	$table_name_array = str_getcsv($csv_String, "\n");
    	
    	for($i=1; $i<=sizeof($table_name_array); $i++){
    		$line = explode(",", $table_name_array[$i]);
    		if($line[1] == $table_name){
    			//Return the Table ID
    			return $line[0];
    		}
    	}
    	
    	//No Table Exists - Return 0
    	return 0;
    }
    
    
    function encode($string,$key) {
    	$key = sha1($key);
    	$strLen = strlen($string);
    	$keyLen = strlen($key);
    	for ($i = 0; $i < $strLen; $i++) {
        	$ordStr = ord(substr($string,$i,1));
        	if ($j == $keyLen) { $j = 0; }
        	$ordKey = ord(substr($key,$j,1));
        	$j++;
        	$hash .= strrev(base_convert(dechex($ordStr + $ordKey),16,36));
    	}
    	return $hash;
	}

	function decode($string,$key) {
    	$key = sha1($key);
    	$strLen = strlen($string);
    	$keyLen = strlen($key);
    	for ($i = 0; $i < $strLen; $i+=2) {
        	$ordStr = hexdec(base_convert(strrev(substr($string,$i,2)),36,16));
        	if ($j == $keyLen) { $j = 0; }
        	$ordKey = ord(substr($key,$j,1));
        	$j++;
        	$hash .= chr($ordStr - $ordKey);
    	}
    	return $hash;
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////
	// MySQL Code
	////////////////////////////////////////////////////////////////////////////////////////////////
	
	function createTwitterTableStore($tableName, $tableComment, $db){
		if(!doesTableExist($tableName, $db)){
			$createTableSql = "CREATE TABLE IF NOT EXISTS `$tableName` (
  								`twitter_id` int(25) NOT NULL,
  								`tweet` longtext NOT NULL,
  								`from_user_name` varchar(255) NOT NULL,
  								`from_user_id` int(25),
  								`dateT` datetime NOT NULL,
  								`lang` varchar(255) DEFAULT NULL,
  								`geo` varchar(255) NOT NULL,
  								`location` varchar(255),
  								`to_user_name` varchar(255),
  								`to_user_id` int(25),
  								`source` varchar(255),
  								`profile_image` varchar(255),
  								`metadata` varchar(255),
  								`place_id` varchar(255),
  								`place_type` varchar(255),
  								`place_full_name` varchar(255),
  	  							INDEX (twitter_id)
							) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='".$tableComment."'";
			$res = mysql_query($createTableSql, $db);
			return(1);
		}
		return (0);
	}
	
	function doesTableExist($tbl, $db){
    	$q = mysql_query("SHOW TABLES", $db);
    	while ($r = mysql_fetch_array($q)) { $tables[] = $r[0]; }
    	mysql_free_result($q);
    	if (in_array($tbl, $tables)) { return TRUE; }
    	else { return FALSE; }
	} 
	
	function isTweetInDB($twitterID, $item, $table, $db){
		$sql = "SELECT twitter_id FROM `".$table."` WHERE twitter_id = $twitterID";
		$res = mysql_query($sql, $db);
 		$num_rows = mysql_num_rows($res);
 	
		if($num_rows == 0){
			return(false);
		}else{
			return(true);
		}
	}
	
?>