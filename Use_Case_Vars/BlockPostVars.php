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

// ////////////////////////////////////////////////////////////////////////////////
// //Title: Personalization Demo Side Bar
// //Use Case: Create Section in Sailthru UI.
// //Status:  
// //Client: Jack's Test Account

// $account_credentials->setAccountById(4627);

// //Vars

// $html = "{dedupe = []}
// {content = filter(content, lambda c: contains(dedupe, c.title)?false:(push(\"dedupe\", c.title) || true) && !contains(lower(c.title), \"warranty\"))}

// {content = filter(content, lambda c: contains(c.tags,\"boots\"))}
// {content = horizon_select(content, 6)}

// {foreach slice(content,0,2) as c}
// <li>
//    <a href=\"{c.url}\" title=\"{c.title}\" class=\"product-image\">
//       <img src=\"{c.image}\" width=\"176\" alt=\"{c.title}\">
//     </a>
//     <br>
//     <h3 class=\"product-name\">
//         <a href=\"{c.url}\" title=\"{c.title}\">
//             {c.title} 
//         </a>
//     </h3>
// </li>
// {/foreach}";

// $api_object->setVar("name", "sidebar of sports");
// $api_object->setVar("type", "html");
// $api_object->setVar("content", $html);
// $api_object->setVar("create_user", "jlewis@sailthru.com");


////////////////////////////////////////////////////////////////////////////////
//Title: Personalization Demo HTML
//Use Case: Create Base HTML Block for recommended for you.
//Status:  
//Client: Jack's Test Account

$account_credentials->setAccountById(4909);

//Vars
$html = '
{foreach slice(content,0,6) as c}
<li>
   <a href="{c.url}" title="{c.title}" class="product-image">
      <img src="{c.image}" width="176" alt="{c.title}">
    </a>
    <br>
    <h3 class="product-name">
        <a href="{c.url}" title="{c.title}">
            {c.title} 
        </a>
    </h3>
</li>
{/foreach}';

$api_object->setVar("name", "Recommended For you - Base HTML");
$api_object->setVar("type", "html");
$api_object->setVar("content", $html);
$api_object->setVar("create_user", "jlewis@sailthru.com");



////////////////////////////////////////////////////////////////////////////////
//Title: Personalization Demo Recommendations Setup
//Use Case: Create Setup Block
//Status:  
//Client: Jack's Test Account

$account_credentials->setAccountById(4909);

//Vars

$html = "{dedupe = []}
{content = filter(content, lambda c: contains(dedupe, c.title)?false:(push('dedupe', c.title) || true))}

{content = horizon_select(content, 6)}";
$api_object->setVar("name", "Recommended For you - Base Setup");
$api_object->setVar("type", "setup");
$api_object->setVar("content", $html);
$api_object->setVar("create_user", "jlewis@sailthru.com");

