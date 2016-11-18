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
//Title: Test For Giordano
//Use Case: Create a content w/ stuff.
//Status: TBD
//Client: Steve Does it all

// $account_credentials->setAccountById(4788);
 
//Vars

// $api_object->setVar("url","http://www.stevedoesitall.com/this/page/isnt/real.html");
// $api_object->setVar("", "extid");


// ////////////////////////////////////////////////////////////////////////////////
// //Title: Demo Example 2
// //Use Case: Post As much stuff as possible
// //Status: 
// //Client: Jack's Test Account

// $account_credentials->setAccountById(4627);
 
// //Vars

// $api_object->setVar("url","http://example.com/product");
// $api_object->setVar("keys",["sku"=>"123abc"]);
// $api_object->setVar("title","This is an Example Title");
// $api_object->setVar("description","This is not a description. It is a paradox.");
// $api_object->setVar("price",2099);
// $api_object->setVar("inventory",42);
// $api_object->setVar("date","2016-06-20 14:30:00 -0400");
// $api_object->setVar("tags","blue, jeans, size-m");
// $api_object->setVar("vars",["var1"=>"var 1 value"]);
// $images = ["full" => "http://example.com/images/product.jpg"];
// $api_object->setVar("images",$images);
// $api_object->setVar("site_name","Store");
// $api_object->setVar("author","Ex Ample");


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
