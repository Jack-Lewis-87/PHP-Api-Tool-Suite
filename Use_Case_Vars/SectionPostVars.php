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
//Title: Personalization Demo Sidebar
//Use Case: Create Section in Sailthru UI.
//Status:  
//Client: Jack's Test Account

$account_credentials->setAccountById(4909);

//Vars

$block_default_html = array(
	"render_name" => "html",
	"block_id" => "8e086dfc-921c-11e5-88b1-002590d1a41a",
);

$block_default_setup = array(
	"render_name" => "setup",
	"block_id" => "770145ca-921c-11e5-a99a-002590d1a41a",
);

$audience_default = array(
	"feed_id" => "560d6c311aa3125f398b4568",
	"blocks" => array( 
		$block_default_html
	),
);

$audiences = array(
	$audience_default
);

$api_object->setVar("name","Recommended For You - PE Demo");
$api_object->setVar("audiences", $audiences);
$api_object->setVar("create_user", "jlewis@sailthru.com");

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
