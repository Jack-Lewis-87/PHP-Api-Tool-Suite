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
$count = 0;
echo "Checking Includes: ";
foreach ($responses["includes"] as $include) {
	echo $count.", ";
	$count = $count + 1;
	$response = $client->getCall("include", ["include" => $include["name"]]);
	inspectIncludeForZephyr($response, $bad_includes, $horizon_includes, $bad_zephyr_functions);
}
echo "horizon\n";
var_dump($horizon_includes);
echo "\n\nTemplates: \nYellow = No Horizon, Green = Good for Pinning, Red = Trouble Template, Cyan = Swap out filter_content()\n";


if (isset($call_data["template"])) {
	$response = $client->getCall("template", $call_data); 
	inspectTemplateForZephyr($response, $bad_includes, $horizon_includes, $bad_zephyr_functions);
} else {
	$responses = $client->getCall("template", $call_data); 
	foreach ($responses["templates"] as $template) {
		$response = $client->getCall("template", array_merge(["template_id" => $template["template_id"]] + $call_data)); 
		inspectTemplateForZephyr($response, $bad_includes, $horizon_includes, $bad_zephyr_functions);
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

function inspectForZephyr($text_to_search, $bad_zephyr_functions) {
	$relevant_zephyr_functions = array_merge(array(
		preg_quote("horizon_select("),
		preg_quote("include \""),
	), $bad_zephyr_functions);

	$test = "/(\b".implode("\b)|(\b", $relevant_zephyr_functions)."\b)/";

	if (preg_match_all($test, $text_to_search, $hits)) {
	    return $hits[0];
	} 

	return false;
}

function inspectTemplateForZephyr($response, $bad_includes, $horizon_includes, $bad_zephyr_functions) {
	$html_hit = inspectForZephyr($response["content_html"], $bad_zephyr_functions);
	$setup_hit = inspectForZephyr($response["setup"], $bad_zephyr_functions);
	if ($setup_hit || $html_hit) {

		if (is_array($setup_hit)) {
			if (is_array($html_hit)) {
				$hits_array = array_merge($html_hit, $setup_hit);
			} else {
				$hits_array = $setup_hit;
			}
		} else {
			$hits_array = $html_hit;
		}
		$preg_hits_array =[];
		foreach ($hits_array as $tmp) {
			array_push($preg_hits_array, preg_quote($tmp));
		}
		$bad_functions = array_intersect($bad_zephyr_functions, $preg_hits_array);
		if ($bad_functions) {
			if (count($bad_functions) == 1 && in_array("filter\(", $bad_functions)) {
				CliScriptAbstract::printColor($response["name"]." >> Reason: Bad Function: ".implode($bad_functions, ", ")."\n", "cyan");
				return;
			}
			CliScriptAbstract::printColor($response["name"]." >> Reason: Bad Function: ".implode($bad_functions, ", ")."\n", "red");
			return;
		}

		if (in_array("include \"", $hits_array) && count($bad_includes) > 0) {

			$test = "/(\b".implode("\b)|(\b", $bad_includes)."\b)/";
			if (preg_match_all($test, $response["content_html"], $hits)) {
				CliScriptAbstract::printColor($response["name"]." >> Reason: Bad Include: ".implode($hits[0], ", ")."\n", "red");
				return;
			} else if (preg_match_all($test, $response["setup"], $hits)) {
				CliScriptAbstract::printColor($response["name"]." >> Reason: Bad Include: ".implode($hits[0], ", ")."\n", "red");
				return;
			}

			$test = "/(\b".implode("\b)|(\b", $horizon_includes)."\b)/";
			if (preg_match_all($test, $response["content_html"], $hits)) {
				CliScriptAbstract::printColor($response["name"]."\n", "green");
				return;
			} else if (preg_match_all($test, $response["setup"], $hits)) {
				CliScriptAbstract::printColor($response["name"]."\n", "green");
				return;
			}
		}

		if (in_array("horizon_select(", $hits_array)) {
			CliScriptAbstract::printColor($response["name"]."\n", "green");
			return;
		}

		CliScriptAbstract::printColor($response["name"]."\n", "yellow");
		return;

	} else {
		CliScriptAbstract::printColor($response["name"]." -- \n", "yellow");
	}
}

function inspectIncludeForZephyr($response, &$bad, &$horizon, $bad_zephyr_functions) {

	$hits = inspectForZephyr($response["content_html"], $bad_zephyr_functions);

	if ($hits) {
		$preg_hits_array =[];
		foreach ($hits as $tmp) {
			array_push($preg_hits_array, preg_quote($tmp));
		}
		$bad_functions = array_intersect($bad_zephyr_functions, $preg_hits_array);
		if ($bad_functions) {
			array_push($bad, $response["name"]);
		}
		if (in_array("horizon_select(", $hits)) {
			array_push($horizon, $response["name"]);
		}

	} 
	return;
}


	// if (rand(1,10) > 5) {
	// 	CliScriptAbstract::printColor($text_to_search."\n", "green");
	// } else {
	// 	CliScriptAbstract::printColor("hello!\n", "red");
	// }

