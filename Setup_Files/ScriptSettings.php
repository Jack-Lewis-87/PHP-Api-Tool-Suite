<?php
require_once(dirname(__DIR__)."/Classes/CliScriptAbstract.php");

class ScriptSettings {

 	protected $default_flags = array(
 		// "isInteractive" => true,
 	);

    public function __construct() {
        foreach ($this->default_flags as $flag => $value) {
        	CliScriptAbstract::$flags[$flag] = $value;
        }
    }
}