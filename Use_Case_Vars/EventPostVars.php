<?php

	/*
	 * Vars for specific use cases
	 *
	 * A Hardcoded alternative to the CLI. Neater and better preserved than using infile vars.
	 * Assumes you have setup clients any new clients with setup.sh, or directly added them to
	 * Default_Keys_And_Secrets.php
	 */

// Comment out code sections after using them by wrapping them in comments
/*
these comment markers
*/

////////////////////////////////////////////////////////////////////////////////
/*
//Title: Demo 1
//Use Case: Initiate an event named "PHP Demo"
//Status: Working

//Client: Jack's Test Account

$account_credentials->setAccountById(4627);
 
//Vars

$api_object->setVar("event","PHP Demo");
$api_object->setVar("id", "jlewis@sailthru.com");

$vars = array(
	"first" => "Jack",
	"last" => "Lewis",
	"related_sku" => "235ac35",
	);

$api_object->setVar("vars", $vars);
*/


////////////////////////////////////////////////////////////////////////////////
//Title: Demo Example 2
//Use Case: Get user profile.
//Status: Working
//Client: Jack's Test Account
/*
$account_credentials->setAccountById(4627);
*/ 
//Vars
/*
$api_object->setVar("id","jlewis@sailthru.com");
*/
