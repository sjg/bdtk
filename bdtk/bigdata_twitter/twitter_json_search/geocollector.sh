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

	screen -d -m php /var/bdtk/bigdata_twitter/twitter_json_search/liveGeoTweetsToDB.php "${lat}" "${lon}" "${rad}" "${unit}" "${tableName}"
	sleep 1

	screen -d -m php /var/bdtk/bigdata_twitter/twitter_json_search/liveGeoTweetsToDB.php "${lat}" "${lon}" "${rad}" "${unit}" "${tableName}"
	sleep 1

	screen -d -m php /var/bdtk/bigdata_twitter/twitter_json_search/liveGeoTweetsToDB.php "${lat}" "${lon}" "${rad}" "${unit}" "${tableName}"
	sleep 1

	screen -d -m php /var/bdtk/bigdata_twitter/twitter_json_search/liveGeoTweetsToDB.php "${lat}" "${lon}" "${rad}" "${unit}" "${tableName}"
	sleep 1

	#Start The Cleaners in 5 minutes
	echo "$(date -u): Starting Table Truncates in 5 minues ..."
	sleep 0

	counter=0;

	while(true)
	do
		let "counter += 1"
                echo ${counter};

	        echo "$(date -u): Starting Table Truncates (every 1 minutes from now) ..."
		php /var/bdtk/bigdata_twitter/twitter_json_search/tableTruncate.php ${tableName}
		echo "$(date -u): Waiting for 1 minute ..."
		sleep 60

                if [ $counter -eq 2 ]; then
                        echo "$(date -u): Dumping Table ..."
			NOW=$(date +"%d-%m-%Y-%H-%M")
			mysqldump -u bdtk -pbdtk bigdata ${tableName} > /home/sjg/${tableName}_$NOW.sql
                        let "counter = 0"
                fi
	done

fi

