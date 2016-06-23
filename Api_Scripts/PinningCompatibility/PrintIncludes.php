<?php
// Base Script to be copied then modified.
//											COPY THEN MODIFY
ini_set("auto_detect_line_endings", true);

	$bad_zephyr_functions = array(
		preg_quote("filter("),
		preg_quote("slice("),
		preg_quote("sort("),
		preg_quote("bucket_list("),
		preg_quote("filter_content("),
		preg_quote("content_intersect("),
		preg_quote("intersect("),
		preg_quote("first("),
		preg_quote("map("),
		preg_quote("purchased_items("),
		preg_quote("push("),
		preg_quote("values("),
	);


require_once(dirname(dirname(__DIR__))."/Classes/Api/Account_Credentials/KeysAndSecrets.php"); 	
$account_credentials = new KeysAndSecrets();
$account_credentials->setAccount();

// Create API Objects
require_once(dirname(dirname(__DIR__))."/Classes/Api/Template/TemplateGet.php");				
$template_get = new TemplateGet();												
$template_get->setAccount($account_credentials);

require_once(dirname(dirname(__DIR__))."/Classes/Api/Include/IncludeGet.php");				
$include_get = new IncludeGet();	
$include_get->setAccount($account_credentials);
// End Create API Objects


require_once(dirname(dirname(__DIR__))."/Classes/CliScriptAbstract.php");	
$script = new CliScriptAbstract();	

require_once(dirname(dirname(__DIR__))."/Classes/Client_Library/Sailthru_Implementation_Client.php");

include_once(dirname(dirname(__DIR__))."/Setup_Files/ScriptSettings.php");			
new ScriptSettings();


//Read in CLI Vars
//Add new parameters to print out in the help screen that are exclusive to this custom Script. Can use the simpler format here or the format api classes use. 
// $cli_params = ["example" => "What example should be used to do"];
// $api_object->createCliParameters($cli_params);
$template_get->setDescription("Return a how compatible a given template is, or an Account's templates are, with regards to Pinning.");
$input_vars = $script->readCliArguments($argv, $template_get);
$template_get->ingestInput($input_vars["config_vars"] + $input_vars["wildcard_vars"], CliScriptAbstract::$flags["isOverride"]);  //Validates and Assigns Vars

////////////////////   START MAIN PROGRAM
////Create Client
if (CliScriptAbstract::$flags["isDefaults"]) {
	$account_credentials->setAccount("defaults");
}
$client = new Sailthru_Implementation_Client($account_credentials->getKey(), $account_credentials->getSecret(), $account_credentials->getEnvironment);

////Designate Call Parameters
$call_data = $template_get->getCallData();
$bad_includes = [];
$horizon_includes = [];

////Status Output
CliScriptAbstract::$flags["isSilent"]?:print "Starting\n";
 
if ((CliScriptAbstract::$flags["isVerbose"] || CliScriptAbstract::$flags["isInteractive"]) && (!CliScriptAbstract::$flags["isQuiet"] && !CliScriptAbstract::$flags["isSilent"])) {
	if ($account_credentials->getNumber() && $account_credentials->getKey() == $account_credentials->getKey($account_credentials->getNumber())) {
		print "Account ".$account_credentials->getNumber().": ".$account_credentials->getName()."\n";
	}
	print "Key: ".$account_credentials->getKey()."\n";
	print "Secret: ".$account_credentials->getSecret()."\nValues:";
	print json_encode($call_data, JSON_PRETTY_PRINT)."\n";

	if (CliScriptAbstract::$flags["isInteractive"]){
		//Confirm + screen output if user decides to kills the script.
		$script->confirm("Proceed?", "Add the '-h' option for more details on valid inputs.");
	}
	//Seperate input from output
	print "\n\nCall Response\n";
}

////Api Call
$responses = $client->getCall("include", $include_get->getCallData());
$includes_saved = $responses["includes"];
$count = 0;
var_dump($includes_saved);
echo "horizon\n";

if (isset($call_data["template"])) {
	$response = $client->getCall("template", $call_data); 
	inspectTemplateForIncludes($response, $includes_saved);
} else {
	$responses = $client->getCall("template", $call_data); 
	foreach ($responses["templates"] as $template) {
		$response = $client->getCall("template", array_merge(["template_id" => $template["template_id"]] + $call_data)); 
		inspectTemplateForIncludes($response, $includes_saved);
	} 
}
	

////Status Output						
// if (!CliScriptAbstract::$flags["isQuiet"] && !CliScriptAbstract::$flags["isSilent"]) {
// 	print json_encode($print_out, JSON_PRETTY_PRINT)."\n";
// }
CliScriptAbstract::$flags["isSilent"]?:print"\nFinished\n";

////Successful Output
exit(0);
////////   END MAIN PROGRAM


/////// Functions 

function inspectForZephyr($text_to_search) {
	$test = "/include \".*\"/";

	if (preg_match_all($test, $text_to_search, $hits)) {
	    var_dump($hits[0]);
	} 
}

function inspectTemplateForIncludes($response, $includes_saved) {
	print $response["name"]."\n";
	inspectForZephyr($response["content_html"]);
	inspectForZephyr($response["setup"], $bad_zephyr_functions);
}

