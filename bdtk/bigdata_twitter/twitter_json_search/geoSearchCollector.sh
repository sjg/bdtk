#!/bin/bash

# Get Arguments from the User
args=("$@")
EXPECTED_ARGS=7

if [ $# -ne $EXPECTED_ARGS ]; then
	echo "Usage: <search> <lat> <lon> <size_of_area> <unit> <DB_Table> <Collection>"
	echo "where options include:"
	echo "    Search:             Textual Search Term (hashtag etc) "
	echo "    Lat:                    Latitude of Centre Point "
	echo "    Lon:                   Longitude of Centre Point "
	echo "    Radius:             Radius in Km or Miles of search"
	echo "    Unit:                   Miles or Kilometers (mi | km) "
	echo "    DB Table:         Database Table to Write Twitter Results"
	echo "    Collection: 	     Collection Name for Dashboard"	
else
	declare -a ARRAY		

	for arg in "$@" 
	do
		ARRAY[$index]=$arg		
  		let "index+=1"
	done  

	search=${ARRAY[0]}
         lat=${ARRAY[1]}
         lon=${ARRAY[2]}
         rad=${ARRAY[3]}
         unit=${ARRAY[4]}
         tableName=${ARRAY[5]}
         collection=${ARRAY[6]}
        
         echo "$(date -u): Starting Collectors ..."  
	
	echo php liveGeoSearchToDB.php \'"${search}"\' "${lat}" "${lon}" "${rad}" "${unit}" "${tableName}" \'"${collection}"\'  
	
	screen -d -m php liveGeoSearchToDB.php "${search}" "${lat}" "${lon}" "${rad}" "${unit}" "${tableName}" "${collection}"      
	sleep 1

	screen -d -m php liveGeoSearchToDB.php "${search}" "${lat}" "${lon}" "${rad}" "${unit}" "${tableName}" "${collection}"  
	sleep 1

	screen -d -m php liveGeoSearchToDB.php "${search}" "${lat}" "${lon}" "${rad}" "${unit}" "${tableName}" "${collection}"      
	sleep 1

	screen -d -m php liveGeoSearchToDB.php "${search}" "${lat}" "${lon}" "${rad}" "${unit}" "${tableName}" "${collection}"       
	sleep 1
	
	#Start The Cleaners in 5 minutes
	echo "$(date -u): Starting Table Truncates in 5 minues ..."
	sleep 300
	
	while(true)
	do
	        echo "$(date -u): Starting Table Truncates (every 1 minutes from now) ..."
		php tableTruncate.php ${tableName}
		echo "$(date -u): Waiting for 1 minute ..."
		sleep 60
	done
	
fi

