#!/usr/local/bin/php -q
<?php
	include_once('./functions.php');

	if(CLI){
		if($argc <=  1){
			echo "Usage: php tableTruncate.php <minutes> <DB_Table> \n";
			echo "where options include:\n";
			echo "\t DB_Table:           Database Table to carry out the truncate\n\n";
			exit(); 
		}else{
			$table_m = $argv[1];
		}
	}
		
  	//Truncate Temp Tables 
	$table = $table_m."_temp";

	echo "\n\t - Truncating Temp Tables [$table]\n";
	saveAndTruncate($table,  $db);
	
	// Distinct Sort
	echo "\t - Sorting Master Table [$table]\n";
	//deleteDuplicatesFromTable($db);

	// Build Graphs		
	echo "\t - Building Graphs Data for [$table]\n";	
	updateUI($table_m, 5, $db)		


?>
