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

// $account_credentials->setAccountById(4909);

// //Vars
// $html = '
// {foreach slice(content,0,6) as c}
// <li>
//    <a href="{c.url}" title="{c.title}" class="product-image">
//       <img src="{c.image}" width="176" alt="{c.title}">
//     </a>
//     <br>
//     <h3 class="product-name">
//         <a href="{c.url}" title="{c.title}">
//             {c.title} 
//         </a>
//     </h3>
// </li>
// {/foreach}';

// $api_object->setVar("name", "Recommended For you - Base HTML");
// $api_object->setVar("type", "html");
// $api_object->setVar("content", $html);
// $api_object->setVar("create_user", "jlewis@sailthru.com");


////////////////////////////////////////////////////////////////////////////////
//Title: Personalization Demo HTML
//Use Case: Create javascript to add to params to links when they are clicked.
//Status:  
//Client: Jack's Test Account

// $account_credentials->setAccountById(4909);

// //Vars
// $html ='window.onclick = function(e) { 
//   if (e.target.localName == "a") {
//     var str = e.target.getAttribute("href");
//     if (str.indexOf("?") > -1) {
//       location.href = str + "&plikely={num(predictions.purchase_1.num*10000)}&zip={user_geo_home()}&abtester=false"; 
//       e.preventDefault();
//     } else {
//       location.href = str + "?plikely={num(predictions.purchase_1.num*10000)}&zip={user_geo_home()}&abtester=false"; 
//       e.preventDefault();
//     }
//   }
// };';

// $api_object->setVar("name", "PC JS");
// $api_object->setVar("type", "js");
// $api_object->setVar("content", $html);
// $api_object->setVar("create_user", "jlewis@sailthru.com");

// $api_object->setVar("block_id", "b1f5e7b0-bb59-11e5-b14a-002590d1a2f6");


////////////////////////////////////////////////////////////////////////////////
//Title: Personalization Demo Recommendations Setup
//Use Case: Create Setup Block
//Status:  
//Client: Jack's Test Account

// $account_credentials->setAccountById(4909);

// //Vars

// $html = "{dedupe = []}
// {content = filter(content, lambda c: contains(dedupe, c.title)?false:(push('dedupe', c.title) || true))}

// {content = horizon_select(content, 6)}";
// $api_object->setVar("name", "Recommended For you - Base Setup");
// $api_object->setVar("type", "setup");
// $api_object->setVar("content", $html);
// $api_object->setVar("create_user", "jlewis@sailthru.com");



////////////////////////////////////////////////////////////////////////////////
//Title: Product Camp Related Pants Setup
//Use Case: Create Setup Block
//Status:  
//Client: Ecom Demo

// $account_credentials->setAccountById(4909);

// //Vars

// $html = "{dedupe = []}
// {content = filter(content, lambda c: contains(dedupe, c.title)?false:(push('dedupe', c.title) || true))}
// {content = filter(content, lambda c: contains(c.tags, 'pants'))}
// {content = horizon_select(content, 6)}";

// $api_object->setVar("name", "Product Camp Related Setup");
// $api_object->setVar("type", "setup");
// $api_object->setVar("content", $html);
// $api_object->setVar("create_user", "jlewis@sailthru.com");




