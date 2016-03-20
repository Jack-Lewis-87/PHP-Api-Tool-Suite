<?php

require_once(dirname(__DIR__)."/CliScriptAbstract.php");

include_once(dirname(__DIR__)."/CliScriptInterface.php");
include_once(dirname(dirname(__DIR__))."/Setup_Files/ScriptSettings.php");

class ApiCallAbstract implements CliScriptInterface {

    /*
     * API vars set for any call
     * @var array
     */
    protected $api_vars = [];

    /*
     * API Parameters for any call
     * @var array
     */
    protected $flag_vars = [];

    /*
     * Object for holding accounts, 
     * in particular Key & Secret
     * @var object
     */
    protected $account;

    /*
     * Object User Interaction. 
     * Only the cli script abstract now.
     * @var object
     */
    protected $IO;

    /*
     * Additional information to be printed in the help message
     *
     * @var object
     */
    protected $OtherInputsDescription = null; 

    /*
     * API Parameters for any call, used for validation. 
     * the cli_params key needs to match this key.
     * @var array
     */
    private $api_params_validation__abstract = [
        //Ex: "api_param" => [["first_dependency", "second_dependency", "etc.."]]   
    ];
    protected $api_params_validation;

    /*
     * Gives the ability to input arrays in the command line as individual the individual members.
     * Also structures arrays as they should be submitted.
     *
     * @var array
     */
    private $api_params_structure__abstract = [
        //Ex: "adjustments" => ["var_prefix"];
    ];
    protected $api_params_structure;

    /*
     * Inheritable. CLI params 
     * @var array
     */
    private $cli_params__abstract = [
        //Ex: returned_var => ["cli_entry_name", "Description"]
    	"api_key" => ["key","API Key, requires Secret."],
    	"api_secret" => ["secret", "API Secret, requires Key."],
        "client_id" => ["client_id", "Client Id for a preconfigured Account, automatically pulls Key & Secret."]
    ];
    protected $cli_params;

    /*
     * Inheritable. CLI flags
     * @var array
     */
    private $cli_options__abstract = [
        //Ex: "cli_entry_name" => ["returned_flag_var", "Description"]
        "-d" => ["isDefault","force the default Account to be used, despite other key/secret/id provided."],
        "-i" => ["isInteractive", "confirm the parameters of the script before they are used."],
        "-v" => ["isVerbose", "show more output during the script's runtime including API Parameters."],
        "-q" => ["isQuiet", "not print the response text to the terminal."],
        "-s" => ["isSilent", "stop any output from printing to the terminal, including confirmations."],
        "-o" => ["isOverrideValidation", "skip validation step with api params."],
        "-p" => ["isListClientIds", "print out configured accounts"],
    ];
    protected $cli_options;

    /*
     * Allow flags set here to effect other flags as well as themselves.
     *
     * @var array
     */
    private $cli_options_modifications__abstract = [
        // "--flag" => ["otherFlagtoeffect", true/false],
        "--client" => ["isListClientIds", true],
        // "-j" => ["isHelp", true],
    ];

    /*
     * Query set. Its convoluted. 
     *
     * @var array
     */
    private $cli_query = [
        //Ex: "cli_entry_name" => ["returned_flag_var", "Description"]
        "source_list" => ["source","Source List: Provide the source list to make the query from. Provide the multi_source as well when using the 'multiple' options."],
        "multiple_source_list" => ["multi_source","Multiple List Source: Comma seperated list of source 'lists'. Eg: \"Master,Test List\""],
        "query_mode" => ["mode", "Mode: For a match, require all criteria to be true 'and' or match if any one is true 'or'."],
        "criteria" => ["criteria", "Criteria: See docs page. Submit a criteria as 'criteriaX=' where X is an integer. Submit the critiria's other requirements (field, value, etc) with that integer. Eg: criteria1=exists field1=first_name"],
        "value" => ["value", "Value: The value of a criteria. Submit as 'valueX=', where X matches the number of the respective criteria's submission. Eg: value2=VIP"],
        "field" => ["field", "Field: The field value of a critera. Submit as 'fieldX=', where X matches the number of the respective criteria's submission. Eg: field2=Status_var"],
        "engagement" => ["engagement", "Engagement: The engagement level of the engagement criterias. Submit as 'engagementX=', where X matches the number of the respective criteria's submission. Eg: engagement3=1"],
        "threshold" => ["threshold", "Threshold: The interest threshold for horizon criterias. Submit as 'thresholdX=', where X matches the number of the respective criteria's submission. Eg: threshold4=32.5"],
        "timerange" => ["trange", "Time range: Criteria timerange. Submit as 'trangeX=' per others. Values of \"ever\",\"never\",\"since_date\",\"not_since_date\",\"since_days\", and \"not_since_days\". Days are integers. Dates are mm/dd/yyyy."],
        "geo_frequency" => ["gfreq", "Geo Frequency: User opens this 'geo_freq' percent of the time. Match to its geo criteria by submitting 'gfreqX=' where X matches the number of the respective criteria's submission."],
        "use_geo" => ["use_geo","Use Geo: For geo_radius criteria. For Sailthru Geo data use 1. To specify a User var use 0. Match to its geo_radius criteria by submitting 'use_geoX=' where X matches the criteria's X."],
        "radius" => ["radius","Radius: Set the radius for geo_radius criteria. Match to its geo_radius criteria by submitting 'use_geoX=' where X matches the number of the respective criteria's submission."],
    ];

    /*
     * Vaidation for Submitted Parameters. 
     * Must match the actual cli_params (not the 'friendly' key name).
     *
     * This actually accounts for most validation situations. 
     * Required Params, including conditionally required params are found in the "always_required" key. 
     * Any other param with validation concerns is its own key. 
     *
     * In the Required Parameter's Validation:
     * "always_required" array holds the param(s) that always need to be passed.
     * "require_one" array contains conditionally required params. So long as one is present, validation passes.
     * 
     * In any other Param's Validation:
     * "always_required" array has any other params that always need to be passed along with the initial param.
     * "require_one" array contains conditionally required params. So long as one is present, validation passes.
     * "value_specific" array is a futher array structure with the initial param's potential values and the subsequent required parameters each would require.
     * Negation param array is a key:array pair, where if the key isn't present, the array params are required. Eg: if you aren't using a template to send a blast, the html et all is required.
     *
     * @var array
     */
    private $cli_query_validation = [
        //Req: "always_required" => ["always_required" => ["necessary_param"], "require_one" => ["likely_needed","probably_needed"]],
        //Ex: "api_param" => ["copy_template" => ["html_content"], "always_required" => ["name"], "require_one" => ["file","email"]]  
        "multiple_source_list" => ["always_required" => ["source_list"]],
        "source_list" => ["value_specifics" => [".multiple" => ["multiple_source_list"], ".multiple-all" => ["multiple_source_list"]]]
    ];

    /*
     * Query structure for grouping multiple queries. 
     *
     * @var array
     */
    private $cli_query_structure = [
        //Ex: "api_var" => "prefix_",
        "criteria" => "criteria",
        "value" => "value",
        "field" => "field",
        "engagement" => "engagement",
        "threshold" => "threshold",
        "geo_frequency" => "gfreq",
        "timerange" => "trange",
        "use_geo" => "use_geo",
        "radius" => "radius",
    ];


    public function __construct() {
        $this->cli_params = $this->getCliParameters();
        $this->cli_options = $this->getCliOptions();
        $this->api_params_structure = $this->getApiParamStructure();
        $this->api_params_validation = $this->getApiParamValidation();
    }

    public function setAccount($acct) {
        $this->account = $acct;
    }

    public function setIO($IO) {
        $this->IO = $IO;
    }

    protected function assignClient($client_info) {
        if (isset($client_info["api_key"]) && isset($client_info["api_secret"])) {
            $this->account->setAccount($client_info["api_key"], $client_info["api_secret"], $client_info["client_name"], $client_info["client_id"]);
        } else if (isset($client_info["client_id"])) {
            $this->account->setAccountById($client_info["client_id"]);
        } else if (count($client_info) > 0) {
            CliScriptAbstract::confirm("Only an API Key or Secret was entered. It was ignored and the default, ".$this->account->getName().", is being used.\nContinue Anyway?", "Pass both api_key and api_secret or a client_id to use a particular account.");
        }
    }

    protected function printClients() {
        $this->account->printClients();
    }

    public function ingestInput($vars, $skipValidate = false) {
        $client_info = array();
        $isStructured = false;

        foreach ($vars as $name => $value) {
            if ($name == "client_id" || $name == "api_key" || $name == "api_secret" || $name == "client_name") {
                $client_info[$name] = $value;      
            } else if ($name == "always_required") {
                if ($value == 0) {
                    $this->api_vars[$name] = "This is really a check but I figured I would add some backdoor functionality with it. Passing 0 partially mimics '-o' except it will only ignore independantly requried params.";
                }
            } else {
                foreach ($this->api_params_structure as $true_var => $prefix) {
                    if (strpos($name, $prefix) === 0) {
                        $isStructured = true;
                        $name = substr($name, strlen($prefix));
                        $this->api_vars[$true_var][$name] = $value;
                    }
                }
                if ($isStructured) {
                    $isStructured = false;
                } else {
                    $this->api_vars[$name] = $value;
                }
            }
        }
        
        if (CliScriptAbstract::$flags["isListClientIds"]) {
            $this->printClients();
        } else {
            $this->assignClient($client_info);
        }
        

        if (CliScriptAbstract::$flags["isQueryCall"]) {
            $this->formatQuery();
        }

        if (!$skid_validate) {
            $this->validateVars();
        }
    }


    /*
     *  Validate the exisitng API parameters.
     *
     *  Uses the class provided validation arrays to make check 
     *  parameter dependencies are met. Plain required params will 
     *  be caught by Sailthru and returned in response. 
     */
    protected function validateVars() {
        $criteria = $this->api_params_validation;
        $display = $this->cli_params;

        foreach ($criteria as $key => $value) {
            if (isset($this->api_vars[$key]) || $key == "always_required") { //Proceed to validate if the key is set, or the key is the required parameters key. Unlike below, the "always_required" naming is important.
                foreach ($value as $negation => $dependencies) {
                    if (!isset($this->api_vars[$negation]) && $negation != "require_one" && $negation != "value_specifics") { //As "always_required" (or any other arbitrary name) won't be set, any dependencies of a nonsense name will always run. Unless some asshole submits always_required as a param.. Edit: If passed as a 0, now works as a feature.
                        foreach ($dependencies as $dependency) {
                            if (!isset($this->api_vars[$dependency])) { 
                                if ($key == "always_required") {
                                    CliScriptAbstract::confirm("You are missing a required parameter: ".$display[$dependency][0].".\nContinue anyway?", "Add the '-h' option for more details on valid inputs.");
                                } else {
                                    CliScriptAbstract::confirm("To use ".$display[$key][0]." you should also provide ".$display[$dependency][0].".\nContinue anyway?", "Add the '-h' option for more details on valid inputs.");
                                }
                            }
                        }
                    } else if ($negation == "require_one") {
                        $mix = array_intersect($dependencies, array_keys($this->api_vars));
                        if (count($mix) > 1 || count($mix) == 0) {
                            CliScriptAbstract::confirm("One, and only one, of the following must be submitted: ".implode(", ", $dependencies).".\nContinue anyway?","Add the '-h' option for more details on valid inputs.");
                        }
                    } else if ($negation == "value_specifics") {
                        foreach ($dependencies as $value => $value_dependencies) {
                            if ($this->api_vars[$key] == $value) {
                                foreach ($value_dependencies as $dependency) {
                                    if (!isset($this->api_vars[$dependency])) {
                                        CliScriptAbstract::confirm("To use ".$display[$key][0]." as ".$value." you should also provide ".$display[$dependency][0].".\nContinue anyway?", "Add the '-h' option for more details on valid inputs.");
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

////Helper Functions

    public function getCallData() {
        if (isset($this->api_vars["always_required"])) {
            unset($this->api_vars["always_required"]);
        }
        return $this->api_vars;
    }

    public function getEndpoint() {
        return $this->endpoint;
    }

    public function getMethod($client_function = null) {
        if ($client_function != null) {
            $result = $client_function;
        } else {
            $result = $this->method."Call";
        }
        return $result;
    }

    public function getCliParameters($child_params = null) {
        if ($child_params != null) {
            $cli_params = $child_params + array_reverse($this->cli_params__abstract);
        } else {
            $cli_params = array_reverse($this->cli_params__abstract);
        }
        if (CliScriptAbstract::$flags["isQueryCall"]) {
            $cli_params = $this->cli_query + $cli_params;
        }
        return array_reverse($cli_params);
    }

    public function createCliParameters($params) {
        if (is_array(array_values($params)[0])) {
            $this->cli_params__abstract = array_merge($this->cli_params__abstract, $params);
        } else {
            foreach ($params as $ref => $desc) {
                $params[$ref] = [$ref, $desc];
            }
            $this->cli_params__abstract = array_merge($this->cli_params__abstract, $params);
        }
    }

    public function getCliOptions($child_options = null) {
        if ($child_options != null) {
            $cli_options = $child_options + $this->cli_options__abstract;
        } else {
            $cli_options = $this->cli_options__abstract;
        }
        return $cli_options;
    }

    public function getFlagModifications($child_modifications = null) {
        if ($child_modifications != null) {
            $option_modifications = $child_modifications + $this->cli_options_modifications__abstract;
        } else {
            $option_modifications = $this->cli_options_modifications__abstract;
        }
        return $option_modifications;
    }

    public function getCliDescription() {
        $cli_description = ucfirst($this->endpoint)." API: ".ucfirst($this->method)." - ".$this->description;
        return $cli_description;
    }

    public function GetOtherInputsDescription($input = null) {
        if ($this->OtherInputsDescription != null) {
            if ($input != null) {
                $result = $input."\n\n".$this->OtherInputsDescription; 
            } else {
                $result = $this->OtherInputsDescription; 
            }
        } else {
            $result = $input;
        }
        return $result;
    }

    public function setVar($name, $value, $is_validate = true) {
    		$this->api_vars[$name] = $value;
    }

    public function setDescription($description) {
            $this->description = $description;
    }

    public function getApiParamValidation($child_params_validation = null) {
        if ($child_params_validation != null) {
            $params_validation = $child_params_validation + $this->api_params_validation__abstract;
        } else {
            $params_validation = $this->api_params_validation__abstract;
        }
        if (CliScriptAbstract::$flags["isQueryCall"]) {
            $params_validation = $params_validation + $this->cli_query_validation;
        }
        return $params_validation;
    }

    public function getApiParamStructure($child_params_structure = null) {
        if ($child_params_structure != null) {
            $params_structure = $child_params_structure + $this->api_params_structure__abstract;
        } else {
            $params_structure = $this->api_params_structure__abstract;
        }
        if (CliScriptAbstract::$flags["isQueryCall"]) {
            $params_structure = $params_structure + $this->cli_query_structure;
        }
        return $params_structure;
    }

    protected function useQueryCLI() {
        CliScriptAbstract::$flags["isQueryCall"] = true;
    }

    private function formatQuery() {
        if (!isset($this->api_vars["query"])) {
            $this->api_vars["query"] = array();
        }

        //Switch comma delimited string to array.
        if (isset($this->api_vars["multiple_source_list"])) {
            $tmp = explode(',', $this->api_vars["multiple_source_list"]);
            foreach ($tmp as $i => $b) {
                $tmp[$i] = trim($b);
            }
            $this->api_vars["source_list"] = ".multiple";
            $this->api_vars["multiple_source_list"] = $tmp;
        } 
        
        //Sort then save only the values as an array.
        if (isset($this->api_vars["criteria"])) {
            ksort($this->api_vars["criteria"]);
            $this->api_vars["query"]["criteria"] = $this->api_vars["criteria"];
            unset($this->api_vars["criteria"]);
        }

        //Sort then save only the values as an array.
        if (isset($this->api_vars["value"])) {
            ksort($this->api_vars["value"]);
            $this->api_vars["query"]["value"] = $this->api_vars["value"];
            unset($this->api_vars["value"]);
        }

        //Sort then save only the values as an array.
        if (isset($this->api_vars["field"])) {
            ksort($this->api_vars["field"]);
            $this->api_vars["query"]["field"] = $this->api_vars["field"];
            unset($this->api_vars["field"]);
        }

        //Sort then save only the values as an array.
        if (isset($this->api_vars["engagement"])) {
            ksort($this->api_vars["engagement"]);
            $this->api_vars["query"]["engagement"] = $this->api_vars["engagement"];
            unset($this->api_vars["engagement"]);
        }

        //Sort then save only the values as an array.
        if (isset($this->api_vars["threshold"])) {
            ksort($this->api_vars["threshold"]);
            $this->api_vars["query"]["threshold"] = $this->api_vars["threshold"];
            unset($this->api_vars["threshold"]);
        }

        //Sort then save only the values as an array.
        if (isset($this->api_vars["geo_frequency"])) {
            ksort($this->api_vars["geo_frequency"]);
            $this->api_vars["query"]["geo_frequency"] = $this->api_vars["geo_frequency"];
            unset($this->api_vars["geo_frequency"]);
        }

        //Sort then save only the values as an array.
        if (isset($this->api_vars["timerange"])) {
            ksort($this->api_vars["timerange"]);
            $this->api_vars["query"]["timerange"] = $this->api_vars["timerange"];
            unset($this->api_vars["timerange"]);
        }

        //Sort then save only the values as an array.
        if (isset($this->api_vars["use_geo"])) {
            ksort($this->api_vars["use_geo"]);
            $this->api_vars["query"]["use_geo"] = $this->api_vars["use_geo"];
            unset($this->api_vars["use_geo"]);
        }

        //Sort then save only the values as an array.
        if (isset($this->api_vars["radius"])) {
            ksort($this->api_vars["radius"]);
            $this->api_vars["query"]["radius"] = $this->api_vars["radius"];
            unset($this->api_vars["radius"]);
        }

        if (count($this->api_vars["query"]) == 0) {
            unset($this->api_vars["query"]);
        }
    }
	
}












