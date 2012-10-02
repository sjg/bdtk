#!/bin/bash

# Get Arguments from the User
args=("$@")
EXPECTED_ARGS=3

if [ $# -ne $EXPECTED_ARGS ]; then
	echo "Usage: <Search Tag> <DB_Table> <collection_name>\n"
	echo "where options include:\n"
	echo "    Search Tag:         NB: HashTags have to be wrapped in Quotes (eg. \"#uksnow\")\n"
	echo "    DB_Table:           Database Table to Write Twitter Results\n"
	echo "    Collection Name:    Name for this Collection"	
else
	declare -a ARRAY		

	for arg in "$@" 
	do
		ARRAY[$index]=$arg		
  		let "index+=1"
	done  


	search=${ARRAY[0]}
        tableName=${ARRAY[1]}
        collection_name=${ARRAY[2]}

	echo "$(date -u): Starting Collectors ..."

	screen -d -m php liveTweetsToDB.php \'"${search}"\' \'"${tableName}"\' \'"${collection_name}"\'
	sleep 1

	screen -d -m php liveTweetsToDB.php \'"${search}"\' \'"${tableName}"\' \'"${collection_name}"\'
	sleep 1

	screen -d -m php liveTweetsToDB.php \'"${search}"\' \'"${tableName}"\' \'"${collection_name}"\'
	sleep 1

	screen -d -m php liveTweetsToDB.php \'"${search}"\' \'"${tableName}"\' \'"${collection_name}"\'	
	sleep 1

	#Start The Cleaners in 15 minutes
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

