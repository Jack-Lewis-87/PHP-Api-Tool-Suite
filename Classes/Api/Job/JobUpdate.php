<?php

include_once(dirname(__DIR__)."/Job/JobPost.php");         //Call Specifc//Incomplete//

class JobUpdate extends JobPost {

    /*
     * Stats type
     *
     * @var array
     */
    protected $job = "update";

    /*
     * Valid Parameters for a call
     *
     * @var array
     */
    protected $description = "Update or create any number of user profiles.";


    /*
     * cli parameters specific to this Job.
     * These will overwrite any existing keys in Parent Call method, but not
     * in the base api call abstract class. (This is because I want api key 
     * and secret to be the first params printed in the help menu.) So don't 
     * add stuff to the cli_params var.
     *
     * @var array
     */
    private $cli_params__job = [
        //returned_var => ["cli_entry_name", "Description"]
        "file" => ["file","File: Local path to a csv file with user information. Will be automatically split up and uploaded in chunks."],
        "file_type" => ["file_type", "File Type: JSON or CSV. "],
        "brand_name" => ["brand", "Brand: A human readable name of the client. Used for folder creation when uploading files."],
        "emails" => ["emails","Emails: A comma seperated string of emails to update."],
        "url" => ["url","URL: A url pointing to a downloadable csv less than 100mbs."],
        "vars" => ["vars","Global Vars: Vars to set on every user in the update. Input a JSON object or each var prepended with 'var_'. Eg: var_source=upload"],
        "lists" => ["lists","Global Lists: Add or remove every user from these lists, 1 to add, 0 to remove. Input a JSON object, or prepend each list with 'list_'."],
        "optout" => ["optout","Global Optout: Change every update user's optout status. Use 'all', 'blast', 'basic', any othe value will opt-in so be careful with spelling."],
        "delete_vars" => ["delete","Global delete vars: Comma seperate list of vars to remove from all profiles in the update."],
        "signup_date" => ["signup","Global Signup: Set one signup date for all users. Individual signup dates can be specified in a JSON file upload."],
        // "" => ["",""],
    ];

    /*
     * Any Flags specific to this call. 
     *
     * @var array
     */
    private $cli_options__job = [
        //returned_flag => ["cli_entry_name", "Description"]
        "-j" => ["isJobDescription","return more information about this job."],
        "-a" => ["isValidateFile", "skip checks on file before upload."]
        // "" => [],
    ];

    /*
     * Allow flags set here to effect other flags as well as themselves.
     *
     * @var array
     */
    private $cli_options_modifications__job = [
        "-j" => ["isHelp", true],
    ];

    /*
     * Dependencies a parameter requires to function. As dependencies are often inter-related,
     * we need a structure to show that relationship. I don't like my solution, but oh well. 
     *
     * The main key is the parameter that has dependencies. The array holds sub collections. 
     * The "always_required" sub array will always be validated against. The other arrays will only
     * be checked against, if the key is not present. 
     *
     * All keys are the api var names, or the final var names. Do not use the cli names. 
     *
     * @var array
     */
    private $api_params_validation__job = [
        //api_param => ["negation_param" => ["dependency_1", "dependency_2"], "always_required" => ["dependency_3"]],
        "file" => ["always_required" => ["file_type"]],
        // "" => [],
    ];

    /*
     * Gives the ability to input arrays in the command line as individual the individual members.
     * Make sure to specify the prefix in the params description
     *
     * @var array
     */
    protected $api_params_structure__job = [
        //"returned_var_array_name" => "prefix_name"
        "vars" => "var_",
        "lists" => "list_",
    ];

    public function __construct() {
        $this->api_vars["job"] = $this->job;
        parent::__construct();
    }
    
    public function ingestInput($vars, $skipValidate = false) {
        parent::ingestInput($vars, $skipValidate);
        //Prep file upload data
        if (isset($this->api_vars["file"])) {
            $this->method = "uploadFile";
            $this->endpoint = $this->job;
            if (!isset($this->api_vars["brand_name"])) {
                $this->api_vars["brand_name"] = $this->account->getName();
                if ($this->api_vars["brand_name"] == null) {
                    CliScriptAbstract::confirm("You need to provide a brand if you are not using a preconfigured account.\nContinue anyway?","Add the '-h' option for more details on valid inputs."); 
                    $this->api_vars["brand_name"] = "unknown";                
                }
            }
            if (CliScriptAbstract::$flags["isValidateFile"]) {
                $this->api_vars["is_skip_check"] = true;
            }
        }
        //Correctly manage updates.
        if (isset($this->api_vars["vars"])) {
            if (!isset($this->api_vars["update"])) {
                $this->api_vars["update"] = array();
            }
            $this->api_vars["update"]["vars"] = $this->api_vars["vars"];
            unset($this->api_vars["vars"]);
        }
        if (isset($this->api_vars["lists"])) {
            if (!isset($this->api_vars["update"])) {
                $this->api_vars["update"] = array();
            }
            $this->api_vars["update"]["lists"] = $this->api_vars["lists"];
            unset($this->api_vars["lists"]);
        }
        if (isset($this->api_vars["optout"])) {
            if (!isset($this->api_vars["update"])) {
                $this->api_vars["update"] = array();
            }
            $this->api_vars["update"]["optout"] = $this->api_vars["optout"];
            unset($this->api_vars["optout"]);
        }
        if (isset($this->api_vars["delete_vars"])) {
            if (!isset($this->api_vars["update"])) {
                $this->api_vars["update"] = array();
            }
            $tmp = explode(",",$this->api_vars["delete_vars"]);
            foreach ($tmp as $i => $c) {
                $tmp[$i] = trim($c);
            }
            $this->api_vars["update"]["delete_vars"] = $tmp;
            unset($this->api_vars["delete_vars"]);
        }
        if (isset($this->api_vars["signup_date"])) {
            if (!isset($this->api_vars["update"])) {
                $this->api_vars["update"] = array();
            }
            $this->api_vars["update"]["signup_date"] = $this->api_vars["signup_date"];
            unset($this->api_vars["signup_date"]);
        }
    }

    public function getMethod() {
        if (!isset($this->method)) {
            $this->method = "postCall";
        }
        return parent::getMethod($this->method);
    }

//helper methods

    public function useQueryCLI() {
        parent::useQueryCLI();
    }

    public function getOtherInputsDescription() {
        if (CliScriptAbstract::$flags["isJobDescription"]) {
            $fd = "hi!";

            return parent::getOtherInputsDescription($fd);
        }
        return parent::getOtherInputsDescription();
    }

    public function getCliParameters($child_params = null) {
        //I'm reversing the array so I can have later classes overwrite earlier ones, but the parent classes still display first.
        if ($child_params != null) {
            $cli_params = $child_params + array_reverse($this->cli_params__job);
        } else {
            $cli_params = array_reverse($this->cli_params__job);
        }
        return parent::getCliParameters($cli_params);
    }

    public function getApiParamValidation($child_param_validation = null) {
        if ($child_param_validation != null) {
            $params_validation = $child_param_validation + $this->api_params_validation__job;
        } else {
            $params_validation = $this->api_params_validation__job;
        }
        return parent::getApiParamValidation($params_validation);
    }

    public function getApiParamStructure($child_params_structure = null) {        
        if ($child_params_structure != null) {
            $params_structure = $child_params_structure + $this->api_params_structure__job;
        } else {
            $params_structure = $this->api_params_structure__job;
        }
        return parent::getApiParamStructure($params_structure);
    }

    public function getCliOptions($child_options = null) {
        if ($child_options != null) {
            $cli_options = $child_options + $this->cli_options__job;
        } else {
            $cli_options = $this->cli_options__job;
        }
        return parent::getCliOptions($cli_options);
    }

    public function getFlagModifications($child_modifications = null) {
        if ($child_modifications != null) {
            $option_modifications = $child_modifications + $this->cli_options_modifications__job;
        } else {
            $option_modifications = $this->cli_options_modifications__job;
        }
        return $option_modifications;
    }

    public function getCallData() {
        return parent::getCallData();
    }

}