<?php


class CliScriptAbstract {
    

    protected static $script_cli_options = array(
    	"-h" => ["isHelp", "display this help message."],
    	"-r" => ["isRun", "run instead of displaying help."], 
    	"-x" => ["isSuppressConfirm", "suppress any user confirmations."],
    	"-b" => ["isBriefHelp", "print a shorter help message."],
        "-s" => ["isSilent", "stop any output from printing to the terminal, including confirmations."],
        "-c" => ["isColor", "add light grey bars behind parameters for easier reading."],
    	// "--b" => ["isBashHelp", "print a special message for bash initiated scripts."],
    );


    protected static $flagModifications = array(
    	//"flag" => ["flag_to_effect", true/false],
    	"--bash" => ["isBashHelp",true],
    	"--help" => ["isHelp",true],
    	"help" => ["isHelp",true],
    	"-c" => ["isHelp",true], 
    );

    /*
     * Any errors encountered while reading in the 
     * cli inputs.
     *
     * @var string
     */
    protected static $last_error;

    /*
     * Key / Value hash of flags
     *
     * @var array
     */
    public static $flags = array();

    /*
     * Key / Value hash of vars
     *
     * @var array
     */
    public static $vars = array();


    public function confirm($question, $failText, $kill = true) {
    	$response = true;
		if (CliScriptAbstract::$flags["isSuppressConfirm"] || CliScriptAbstract::$flags["isSilent"]) {
			return;
		}
		print $question."\n(y/n)\n";
		$answer = readline();
		if ($answer != "y" && $answer != "yes") 
		{
			if ($kill) {
				die($failText."\n");
			} else {
				$response = false;
				print $failText."\n";
			}
			
		}
		return $response;
	}

	/*	
	 * Reads in Command Line Parameters
	 * Parses out vars based on the extending script's 
	 * $cli_params array
	 *
	 *
	 */
	public static function readCliArguments(array $argv, CliScriptInterface $cli_interface) {
		////Read in flags first.
		//Get flag options (I'm being annoying here so the flags from this file over ride any others but still display last.)
		$cli_options = CliScriptAbstract::$script_cli_options + array_reverse($cli_interface->getCliOptions());
		$cli_options = array_reverse($cli_options);

		//read in, save, then remove flags from argv.
		$argv = CliScriptAbstract::processFlags($argv, $cli_options, CliScriptAbstract::getFlagModifications($cli_interface));
		//make the flags easier to access here by giving them local scope.
		extract(CliScriptAbstract::$flags);

		//Read in all other vars.
		$cli_description = $cli_interface->getCliDescription();
		$cli_params = $cli_interface->getCliParameters();
		$cli_extras = $cli_interface->getOtherInputsDescription();
		
		//I want the full help menu to have precedence over the brief window. 
		if (!$isRun && (count($argv) == 1 || $isHelp)) {
			CliScriptAbstract::print_help($cli_description, $cli_params, $cli_options, $cli_extras);
			die("\n");
		} else if (!$isRun && ($isBriefHelp || $isBashHelp)) {
			$brief = $isBriefHelp?"brief":"bash";
			CliScriptAbstract::print_help($cli_description, $cli_params, $cli_options, $cli_extras, $brief);
			die("\n");
		}

		//Past the help Menu, lets read in some vars
		$vars = array();  //Named Vars sent through in the config as expected 
		$flags = array();  //Flags sent through in the config as expected
		$wildcards = array();  //Named vars that were not expected
		$other_inputs = array();  //Catch all for other input. 

		$exploded_args = array();

		foreach ($argv as $arg) {
			if (strpos($arg, "=") !== FALSE) {
				$e=explode("=",$arg);
				$param_key = $e[0];
				if ($e[1][0] == "{") 
				{
					$e[1] = convertSmartQuotes($e[1]);
					$exploded_args[$param_key] = json_decode($e[1], true);
					if (json_last_error() !== JSON_ERROR_NONE) 
					{
						CliScriptAbstract::$last_error = $e[0];
						CliScriptAbstract::confirm("JSON Object, ".$e[0].", was not successfully decoded: ".json_last_error_msg()."\nContinue anyway?", "Try 'http://www.jsoneditoronline.org/' if you don't know what is wrong.");
						unset($exploded_args[$param_key]);
					}
				}
				else if (strpos($e[1], "(string)") !== false) 
				{
					$exploded_args[$param_key] = substr($e[1], 0, -8);
				}
				else if (is_numeric($e[1])) 
				{
					$exploded_args[$param_key] = ($e[1] + 0);	
				}
				else
				{
					$exploded_args[$param_key] = $e[1];
				}
			} else {
				array_push($other_inputs, $arg);
			}
		}
		foreach ($cli_params as $param_var => $rest) {
			if (isset($exploded_args[$rest[0]])) {
				$vars[$param_var] = $exploded_args[$rest[0]];
				unset($exploded_args[$rest[0]]);
			} 
		}

		CliScriptAbstract::processVars($exploded_args, $cli_params);

		return array("config_vars" => $vars, "wildcard_vars" => $exploded_args, "other_inputs" => $other_inputs);
	}

	public function convertSmartQuotes($string) {
        $search = array(chr(145),
        chr(146),
        chr(147),
        chr(148));
         
        $replace = array("'",
        "'",
        '"',
        '"');
         
        return str_replace($search, $replace, $string);
    }	
    /* Thanks JR, this code is pretty much yours. 
     * Credit : http://www.if-not-true-then-false.com/2010/php-class-for-coloring-php-command-line-cli-scripts-output-php-output-colorizing-using-bash-shell-colors/
     */
    public function printColor($toPrint, $color) {
		 // Set up shell colors
    	 $foreground_colors = [];
		 $foreground_colors['black'] = '0;30';
		 $foreground_colors['dark_gray'] = '1;30';
		 $foreground_colors['blue'] = '0;34';
		 $foreground_colors['light_blue'] = '1;34';
		 $foreground_colors['green'] = '0;32';
		 $foreground_colors['light_green'] = '1;32';
		 $foreground_colors['cyan'] = '0;36';
		 $foreground_colors['light_cyan'] = '1;36';
		 $foreground_colors['red'] = '0;31';
		 $foreground_colors['light_red'] = '1;31';
		 $foreground_colors['purple'] = '0;35';
		 $foreground_colors['light_purple'] = '1;35';
		 $foreground_colors['brown'] = '0;33';
		 $foreground_colors['yellow'] = '1;33';
		 $foreground_colors['light_gray'] = '0;37';
		 $foreground_colors['white'] = '1;37';
		 if (isset($foreground_colors[$color])) {
		 	echo "\033[".$foreground_colors[$color]."m".$toPrint."\033[0m"; 
		 } else {
		 	echo $toPrint;
		 }
    }

	protected static function print_help($cli_description, $cli_params, $options, $cli_extras, $brief = false) {
		print $cli_description;

		if ($cli_params != null) {
			$gap_length = 20;
			$i = 0;
			if (CliScriptAbstract::$flags["isColor"]) {
				$color1 = "0";	//Black = "0;30"
				$color2 = "0;30m\033[47";	//Black "0;30" on Light Grey "47"
				$color3 = "0";	//Blue = "0;34"
			} else {
				$color1 = $color2 = $color3 = "0";
			}

			print "\nParameter(s):"; 
			foreach ($cli_params as $forms) 
			{
				$i++;
				$color = ($i%3==0)?$color1:($i%3==1?$color2:$color3);
				$gap = $gap_length - strlen($forms[0]);
				$gap = $gap <= 0?5:$gap;
				echo "\033[".$color."m".sprintf("\n   ".$forms[0]."=%".$gap."s ".$forms[1], "--")."\033[0m"; 
			}
		}

		if ($brief == "brief") {
			return;
		}

		if ($cli_params != null) {
			print "\n\nFlag(s):";
			foreach ($options as $flag => $option) {
				printf("\n   Provide   ".$flag."   to ".$option[1]);
			}
		}

		if ($cli_extras != null) {
			print "\n\n";
			print $cli_extras;
		}

		print "\n\nSurround values containing spaces in single qoutes, e.g.:"; 
		print "\n\tPostList.php list='Master List'";

		print "\n\nTo force a parameter to be a string instead of an number, append \"(string)\" to it. Single qoutes are required. e.g.:";
		print "\n\tPostList.php listId='0123(string)'";

		print "\n\nInput complex data as a json object. Single qoutes are required. e.g.:";
		print "\n\tPostList.php lists='{\"Master\":1}'";
	}


	public function processFlags($argv, $cli_options, $modifications) {
        foreach ($cli_options as $handle => $rest) {
            if(($key = array_search($handle, $argv)) !== false) {
                CliScriptAbstract::$flags[$rest[0]] = true;
                if (!isset($modifications[$handle])) {
                	unset($argv[$key]);
                }                
            } else if (!isset(CliScriptAbstract::$flags[$rest[0]])) {
                CliScriptAbstract::$flags[$rest[0]] = false;
            }
        }
        foreach ($modifications as $handle => $rest) {
        	if(($key = array_search($handle, $argv)) !== false) {
                CliScriptAbstract::$flags[$rest[0]] = $rest[1];
                unset($argv[$key]);
            }
        }
        return $argv;
    }

    private function getFlagModifications($cli_interface) {
    	$modifications = CliScriptAbstract::$flagModifications + $cli_interface->getFlagModifications();
    	return $modifications;
    }

   	public function processVars($vars, $cli_params) {
        foreach ($cli_params as $handle => $rest) {
            if (isset($vars[$handle])) {
                CliScriptAbstract::$vars[$handle] = true;
            } else if (!isset(CliScriptAbstract::$vars[$handle])) {
                CliScriptAbstract::$vars[$handle] = false;
            }
        }
    }
}