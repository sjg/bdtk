var mysql = require('mysql');
var io_client = require('socket.io-client');
var Step = require('step');

//Get Command Line Args
var arguments = process.argv.splice(2);
if(arguments.length <= 4){
	console.log("Usage:   node client.js collector-id lat lon radius units");
	process.exit(code=0);
}

var tablename = arguments[0];
var lat =  arguments[1];
var lon = arguments[2];
var radius = arguments[3];
var units = arguments[4];

//Define Master Server collecting Stats
host = '128.40.111.232';
port = 8008;

var client;

function connect(){
	console.log("Attempting to connect with server@" + host + ":" + port);

	//Connect to Master Server
	var url = "http://" + host + ":" + port;
	client = io_client.connect(url);
}

var checkForServer = setTimeout(connect(), 10000);

function on_client_connect()
{
        console.log("Contact with server@" + host + ":" + port + " established.");
	if(checkForServer){
		clearTimeout(checkForServer)
	}
	
}

function on_client_message()
{
	var mysqlconnection = mysql.createConnection({
 		 host     : '127.0.0.1',
		 port	  : '3306',
  		 user     : 'bdtk',
  		 password : 'bdtk',
  		 database : 'bigdata',
		 socket: '/var/run/mysqld/mysqld.sock',
	});	

	//Make our Queries and send the data back
	Step(
		function getAllTweetsCount(){
			mysqlconnection.query('SELECT distinct(twitterID) as totalTweets FROM ' + tablename + ' group by twitterID', this.parallel());
			mysqlconnection.query('SELECT twitterID FROM `' + tablename + '_temp` WHERE `dateT` BETWEEN DATE_SUB(NOW() , INTERVAL 1 MINUTE) AND NOW() group by twitterID', this.parallel());
			mysqlconnection.query('SELECT twitterID FROM `' + tablename + '` WHERE `dateT` BETWEEN DATE_SUB(NOW() , INTERVAL 5 MINUTE) AND NOW() group by twitterID', this.parallel());
			mysqlconnection.query('SELECT twitterID FROM `' + tablename + '` WHERE `dateT` BETWEEN DATE_SUB(NOW() , INTERVAL 10 MINUTE) AND NOW() group by twitterID', this.parallel());			
		},

		function sendData(err, allTweetsCollected, lastMinute, last5Minute, last10Minutes){
			//console.log("Sending: " + allTweetsCollected.length);
			//console.log("Sending: " + lastMinute.length);
			//console.log("Sending: " + last5Minute.length);
			//console.log("Sending: " + last10Minutes.length);

			var message = {
					"collector": {
							    "id": tablename,
						           "lat": lat,
							   "lon": lon,
							"radius": radius, 
							  "unit": units, 
						     },
					"allTweets": allTweetsCollected.length,
					"lastMinute" : lastMinute.length,
					"last5Minute" : last5Minute.length,
					"last10Minutes": last10Minutes.length,
			};
		
			console.log("Sending: " + JSON.stringify(message));
			client.emit("collectorStats", JSON.stringify(message));
		}
	);
	
	mysqlconnection.end();
}

function on_client_disconnect()
{
        console.log("Contact with server@" + host + ":" + port + " lost.");
	if(!checkForServer){
                clearTimeout(checkForServer)
        }
}

function on_client_close()
{
        console.log("Server closed the connection.");
	if(!checkForServer){
                clearTimeout(checkForServer)
        }
}

function on_client_connect_failed(err)
{
        console.log("Failed to connect to server@" + host + ":" + port + ". Error: " + err);
	if(!checkForServer){
                clearTimeout(checkForServer)
        }
}

function on_client_welcome(){
	console.log('Rodger, I have you!'); 
}

client.on('connect', on_client_connect);
client.on('connect_failed', on_client_connect_failed);
client.on('stats', on_client_message);
client.on('welcome', on_client_welcome);
client.on('disconnect', on_client_disconnect);
client.on('close', on_client_close);

var stdin = process.openStdin();

process.on('SIGINT', function () {
	process.exit(code=0);
});
