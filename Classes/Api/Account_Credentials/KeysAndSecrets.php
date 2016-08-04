<?php

include(dirname(__FILE__)."/DefaultKeysAndSecrets.php");

/**
*  Holder of Keys and Secrets
*/
class KeysAndSecrets extends DefaultKeysAndSecrets 
{
	protected static $name;
	protected static $number;
	protected static $key;
	protected static $secret;
	protected static $environment;

	public static function setDefaults() {
		KeysAndSecrets::$number = KeysAndSecrets::$acctNumberDefault;
 		KeysAndSecrets::$name = KeysAndSecrets::$acctNameDefault;
 		KeysAndSecrets::$key = KeysAndSecrets::$apiKeyDefault;
 		KeysAndSecrets::$secret = KeysAndSecrets::$apiSecretDefault;
 		KeysAndSecrets::$environment = KeysAndSecrets::$apiEnvironmentDefault;
 	}


 	public static function setAccount($key = null, $secret = null, $name = null, $id = null, $environment = "https://api.sailthru.com") {
		if ($key == null || $key == "default") {
			KeysAndSecrets::setDefaults();
		} else if (is_numeric($key)) {
			KeysAndSecrets::setAccountById($key);
		} else {
			KeysAndSecrets::setKeySecret($key, $secret, $name, $id, $environment);
		}
	}

 	public static function setKeySecret($key, $secret, $name = null, $id = null, $environment = "https://api.sailthru.com") {
 		if ($key == null || $secret == null) {
 			die("Key and Secret need to be set together\n");
 		} else {
 			KeysAndSecrets::$key = $key;
 			KeysAndSecrets::$secret = $secret;
 			KeysAndSecrets::$name = $name; 
			KeysAndSecrets::$number = $id; 
			KeysAndSecrets::$environment = $environment;
 		}
 	} 

 	public static function setAccountById($number) {
 		if (!isset(KeysAndSecrets::$clients[$number])) {
 			die("That client id isn't configured yet. Try running SetupApiAccounts.sh to add more.\n");
 		} else {
 			KeysAndSecrets::$number = $number;
 			KeysAndSecrets::$key = KeysAndSecrets::$clients[$number]["key"];
 			KeysAndSecrets::$secret = KeysAndSecrets::$clients[$number]["secret"];
 			KeysAndSecrets::$name = KeysAndSecrets::$clients[$number]["name"];
 			KeysAndSecrets::$environment = KeysAndSecrets::$clients[$number]["environment"];
 		}
 	}

	public static function getKey($id = null) {
		if ($id === null) {
			return KeysAndSecrets::$key;
		} else {
			return KeysAndSecrets::$clients[$id]["key"];
		}
	}  

	public static function getSecret($id = null) {
		if ($id === null) {
			return KeysAndSecrets::$secret;
		} else { 	
			return KeysAndSecrets::$clients[$id]["secret"];
		}
	}

	public static function getEnvironment($id = null) {
		if ($id === null) {
			return KeysAndSecrets::$environment;
		} else { 	
			return KeysAndSecrets::$clients[$id]["environment"];
		}
	}
	
	public static function getName($id = null) {
		if ($id === null) {
			return KeysAndSecrets::$name;
		} else {
			return KeysAndSecrets::$clients[$id]["name"];
		}
	}	

	public static function getNumber($id = null) {
		if ($id === null) {
			return KeysAndSecrets::$number;
		} else {
			return KeysAndSecrets::$id;
		}
	} 

	public static function printClients() {
		foreach (KeysAndSecrets::$clients as $key => $value) {
			print $key.": ".$value["name"]."\n";
		}
		die();
	}

	public static function getCurrentAccount() {
		return array("id" => KeysAndSecrets::$number, "name" => KeysAndSecrets::$name, "key" => KeysAndSecrets::$key, "secret" => KeysAndSecrets::$secret);
	}
}