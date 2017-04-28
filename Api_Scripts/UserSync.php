<?php

require_once(dirname(__DIR__)."/Classes/Client_Library/Sailthru_Implementation_Client.php");

////////////////////   START MAIN PROGRAM
////Create Client
$client = new Sailthru_Implementation_Client("de3e220a8f3dbfeb6e41defa2bbaaaa8", "1d9de66ea2ca549507d8110aa4f13de1");

$response = $client->postCall("job/integrations/user_sync", ["list"=>"nope"]); 
		
print json_encode($response, JSON_PRETTY_PRINT)."\n";


////Successful Output
exit(0);
////////   END MAIN PROGRAM