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
// //Title: QA on Lists w/ $ ticket
// //Use Case: Break stuff by creating Lists w/ $
// //Status:  
// //Client: Pete QA7

// $account_credentials->setAccountById(4023);
 
// //Vars

// $api_object->setVar("emails","jlewis@sailthru.com, jlewis+1@sailthru.com");
// $api_object->setVar("update", ["lists"=>['$hello'=>1]]);


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
