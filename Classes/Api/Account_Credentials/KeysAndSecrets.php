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

	public static function setDefaults() {
		KeysAndSecrets::$number = KeysAndSecrets::$acctNumberDefault;
 		KeysAndSecrets::$name = KeysAndSecrets::$acctNameDefault;
 		KeysAndSecrets::$key = KeysAndSecrets::$apiKeyDefault;
 		KeysAndSecrets::$secret = KeysAndSecrets::$apiSecretDefault;
 	}


 	public static function setAccount($key = null, $secret = null, $name = null, $id = null) {
		if ($key == null || $key == "default") {
			KeysAndSecrets::setDefaults();
		} else if (is_numeric($key)) {
			KeysAndSecrets::setClientById($key);
		} else {
			KeysAndSecrets::setKeySecret($key, $secret, $name, $id);
		}
	}

 	public static function setKeySecret($key, $secret, $name = null, $id = null) {
 		if ($key == null || $secret == null) {
 			die("Key and Secret need to be set together\n");
 		} else {
 			KeysAndSecrets::$key = $key;
 			KeysAndSecrets::$secret = $secret;
 			KeysAndSecrets::$name = $name; 
			KeysAndSecrets::$number = $id; 
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
}