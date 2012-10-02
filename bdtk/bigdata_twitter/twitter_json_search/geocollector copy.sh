#!/bin/bash

# Get Arguments from the User
args=("$@")
EXPECTED_ARGS=5

if [ $# -ne $EXPECTED_ARGS ]; then
	echo "Usage: <lat> <lon> <size_of_area> <unit> <DB_Table>"
	echo "where options include:"
	echo "    Lat:                    Latitude of Centre Point "
	echo "    Lon:                   Longitude of Centre Point "
	echo "    Radius:             Radius in Km or Miles of search"
	echo "    Unit:                   Miles or Kilometers (mi | km) "
	echo "    DB Table:         Database Table to Write Twitter Results"	
else
	declare -a ARRAY		

	for arg in "$@" 
	do
		ARRAY[$index]=$arg		
  		let "index+=1"
	done  

         lat=${ARRAY[0]}
         lon=${ARRAY[1]}
         rad=${ARRAY[2]}
         unit=${ARRAY[3]}
         tableName=${ARRAY[4]}
        
         echo "$(date -u): Starting Collectors ..."

	screen -d -m php liveGeoTweetsToDB.php "${lat}" "${lon}" "${rad}" "${unit}" "${tableName}"
	#sleep 1

	screen -d -m php liveGeoTweetsToDB.php "${lat}" "${lon}" "${rad}" "${unit}" "${tableName}"
	#sleep 1

	screen -d -m php liveGeoTweetsToDB.php "${lat}" "${lon}" "${rad}" "${unit}" "${tableName}"
	#sleep 1

	screen -d -m php liveGeoTweetsToDB.php "${lat}" "${lon}" "${rad}" "${unit}" "${tableName}"
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

