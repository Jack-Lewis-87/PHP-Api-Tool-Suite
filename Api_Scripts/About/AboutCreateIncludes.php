<?php
// Early Usage. Not a good Model for going forward. 
//											COPY THEN MODIFY
ini_set("auto_detect_line_endings", true);

if (!(date_default_timezone_set('America/New_York'))) {
	die("Time Zone Not Set");
}

$experts_path = "/Users/johnlewis/Desktop/Experts_latin.txt";
if (($file = fopen($experts_path, "r")) === false) {
	die("Couldn't Open the file.\n");
}
$log_path = "/Users/johnlewis/Desktop/Expert_lookup_log.log";
if (($log = fopen($log_path, "w")) === false) {
	die("Couldn't Open the log.\n");
}
$result_log_path = "/Users/johnlewis/Desktop/Expert_lookup_results_log.log";
if (($results = fopen($result_log_path, "w")) === false) {
	die("Couldn't Open the log.\n");
}


require_once(dirname(dirname(__DIR__))."/Classes/Api/Account_Credentials/KeysAndSecrets.php"); 	
$account_credentials = new KeysAndSecrets();
$account_credentials->setAccount(4777);



//START CALL SPECIFIC 
require_once(dirname(dirname(__DIR__))."/Classes/Api/Include/IncludePost.php");				//Call Specifc//Incomplete//
$api_object = new IncludePost();												//Call Specifc//Incomplete//
$api_object->setAccount($account_credentials);
//END CALL SPECIFIC 


require_once(dirname(dirname(__DIR__))."/Classes/CliScriptAbstract.php");	
$script = new CliScriptAbstract();	

require_once(dirname(dirname(__DIR__))."/Classes/Client_Library/Sailthru_Implementation_Client.php");

include_once(dirname(dirname(__DIR__))."/Setup_Files/ScriptSettings.php");			
new ScriptSettings();


////////////////////   START MAIN PROGRAM
////Create Client
if (CliScriptAbstract::$flags["isDefaults"]) {
	$account_credentials->setAccount("defaults");
}

var_dump($account_credentials->getCurrentAccount());
$client = new Sailthru_Implementation_Client($account_credentials->getKey(), $account_credentials->getSecret());
////Designate Call Parameters
$call_data = $api_object->getCallData();
$endpoint = $api_object->getEndpoint();
$method = $api_object->getMethod();

////Status Output
print "Starting\n";


//Iterate through file and make a call for each row.
$row = 0;
while (($data = fgetcsv($file, 0, ",")) !== FALSE) {
	$row++;
	echo "Processsed Row: ".$row."\n";
	fwrite($log, "Processsed Row: ".$row."\nInput: \n");
	fwrite($results, "Processsed Row: ".$row."\n");
	fwrite($log, print_r($data, true));
	$info = [];
	//Iterate through each field
	foreach ($data as $i => $field) 
	{
		//Save headers into reference, don't touch
		if ($row == 1) {
			$headerArray[$i] = strtolower($field);
		} 
		//CUSTOMIZE HERE!!!!!!!!!!!!!!!!!!!!!
		//You can search against a header and choose how to save it into an array structure. The array structure will then be used to JSONify. This default code is to make a 0 depth JSON Structure with the CSV header as key. 
		else {
			$info[$headerArray[$i]] = $field;
		}
	}
	if ($row != 1) {
		$name = "Expert Details: ".$info["site"];
		$body = "{channel = \"".$info["channel (old)"]."\"}  				 	{*Channel name reference value*}
{channelDisplay = \"".$info["channel display"]."\"}				{*Not used*}{*Readable Channel name*}
{author_gif = \"".$info["author id"]."\"} 	 			{*Author's numerical value for picture gif*}
{author = \"".$info["author"]."\"}			{*Readable Author's name*}
{site = \"".$info["site"]."\"}					{*Not used*}{*Expert topic reference value*}
{siteDisplay = \"".$info["site display"]."\"}   				{*Readable expert topic name*}
{expert_time_back = \"-7 days\"}			{*Time since last send, only content published since then will be used*}";
		$call_data["include"] = $name;
		$call_data["content_html"] = mb_convert_encoding($body, "UTF-8");
		////Api Call
		fwrite($log, print_r($call_data, true));
		fwrite($log, "Result \n");
		$response = $client->$method($endpoint, $call_data); 
		fwrite($log, print_r($response, true));
		fwrite($results, print_r($response, true));
	}
}

print"\nFinished\n";

////Successful Output
exit(0);
////////   END MAIN PROGRAM