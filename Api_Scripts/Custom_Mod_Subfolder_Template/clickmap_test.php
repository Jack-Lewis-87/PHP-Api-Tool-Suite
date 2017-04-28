<?php
// Base Script to be copied then modified.
//											COPY THEN MODIFY

require_once(dirname(dirname(__DIR__))."/Classes/Api/Account_Credentials/KeysAndSecrets.php"); 	
$account_credentials = new KeysAndSecrets();
$account_credentials->setAccount();

//////////   END VARS

////////////////////   START MAIN PROGRAM
require_once(dirname(dirname(__DIR__))."/Classes/Client_Library/Sailthru_Client.php");

$client = new Sailthru_Client($account_credentials->getKey(), $account_credentials->getSecret(), $account_credentials->getEnvironment);

////Api Call
$response = $client->stats_blast(5502451, ["clickmap"=>1]); 
		
////Status Output						
print json_encode($response, JSON_PRETTY_PRINT)."\n";

////Successful Output
exit(0);
////////   END MAIN PROGRAM