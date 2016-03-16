<?php

include_once(dirname(__DIR__)."/Send/Send.php");         //Call Specifc//Incomplete//

class SendPost extends Send {
    
    /*
     * Valid Parameters for a call
     *
     * @var array
     */
    protected $method = "post";

    /*
     * Valid Parameters for a call
     *
     * @var array
     */
    protected $description = "Send a transactional email, or schedule one for the near future.";

    /*
     * cli parameters specific to this Call's method, eg User Get
     * These will overwrite any existing keys in Parent Call method, but not
     * in the base api call abstract class. (This is because I want api key 
     * and secret to be the first params printed in the help menu.) So don't 
     * add stuff to the cli_params var.
     *
     * @var array
     */
    private $cli_params__method = [
    	//returned_var => ["cli_entry_name", "Description"]
        "template" => ["template","Template: The name of the template to send."],
        "email" => ["email","Email: The email address to send to."],
        "schedule_time" => ["time","Schedule Time: When to send the email. Flexible dates, \"2013-09-08 20:00:00\" or relative time \"+5 hours\"."],
        "schedule_time['start_time']" => ["pst_start","Personalize Send Time Start. Same dates requirements as time."],
        "schedule_time['end_time']" => ["pst_end","Personalize Send Time end. Same dates requirements as time."],
        "vars" => ["vars","Vars: The vars to use in the send. Input a JSON object or enter each var name prepended with \"var_\" eg: var_first=jack."],
        "data_feed_url" => ["feed","Data Feed URL: The URL of the data feed."],
        "Cc" => ["cc","Cc Email: Include an email on CC."],
        "Bcc" => ["bcc","Bcc Email: Include an email on BCC."],
        "replyto" => ["reply_to","Reply To Email: Specify a reply to email address."],
        "test" => ["test","set to 1 to denote the email as a test"],
        "behalf" => ["behalf_email","Send From: Use the Sender/From headers to send the email on behalf of a third party."],
        "evars" => ["evars","Multisend Evars: Use a custom script for god's sake."],
        // "" => [],
    ];

    /*
     * Any Flags specific to this call. 
     *
     * @var array
     */
    private $cli_options__method = [
        //returned_flag => ["cli_entry_name", "Description"]
        // "" => [],
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
    private $api_params_validation__method = [
        //api_param => ["negation_param" => ["dependency_1", "dependency_2"], "always_required" => ["dependency_3"]],
        // "" => [],
    ];

    /*
     * Gives the ability to input arrays in the command line as individual the individual members.
     * Make sure to specify the prefix in the params description
     *
     * @var array
     */
    protected $api_params_structure__method = [
        //"returned_var_array_name" => "prefix_name"
        "vars" => "var_"    
    
    ];

    public function ingestInput($vars, $skipValidate = false) {
        parent::ingestInput($vars, $skipValidate);
        if (!isset($this->api_vars["options"])) {
            $this->api_vars["options"] = array();
        } 
        if (isset($this->api_vars["Cc"])) {
            if (!isset($this->api_vars["options"]["headers"])){
                $this->api_vars["options"]["headers"] = array();
            } 
            $this->api_vars["options"]["headers"]["Cc"] = $this->api_vars["Cc"];
            unset($this->api_vars["Cc"]);
        }
        if (isset($this->api_vars["Bcc"])) {
            if (!isset($this->api_vars["options"]["headers"])){
                $this->api_vars["options"]["headers"] = array();
            }             
            $this->api_vars["options"]["headers"]["Bcc"] = $this->api_vars["Bcc"];
            unset($this->api_vars["Bcc"]);
        }
        if (isset($this->api_vars["replyto"])) {
            $this->api_vars["options"]["replyto"] = $this->api_vars["reply_to"];
            unset($this->api_vars["replyto"]);
        }
        if (isset($this->api_vars["test"])) {
            $this->api_vars["options"]["test"] = $this->api_vars["test"];
            unset($this->api_vars["test"]);
        }
        if (isset($this->api_vars["behalf"])) {
            $this->api_vars["options"]["behalf_email"] = $this->api_vars["behalf"];
            unset($this->api_vars["behalf"]);
        }
        if (count($this->api_vars["options"]) === 0) {
            unset($this->api_vars["options"]);
        }
    }

//helper methods
//No need to modify when creating a new class

    public function getCliParameters($child_params = null) {
        //I'm reversing the array so I can have later classes overwrite earlier ones, but the parent classes still display first.
        if ($child_params != null) {
            $cli_params = $child_params + array_reverse($this->cli_params__method);
        } else {
            $cli_params = array_reverse($this->cli_params__method);
        }
        return parent::getCliParameters($cli_params);
    }

    public function getCliOptions($child_options = null) {
        if ($child_options != null) {
            $cli_options = $child_options + $this->cli_options__method;
        } else {
            $cli_options = $this->cli_options__method;
        }
        return parent::getCliOptions($cli_options);
    }

    public function getApiParamValidation($child_param_validation = null) {
        if ($child_param_validation != null) {
            $params_validation = $child_param_validation + $this->api_params_validation__method;
        } else {
            $params_validation = $this->api_params_validation__method;
        }
        return parent::getApiParamValidation($params_validation);
    }

    public function getApiParamStructure($child_params_structure = null) {        
        if ($child_params_structure != null) {
            $params_structure = $child_params_structure + $this->api_params_structure__method;
        } else {
            $params_structure = $this->api_params_structure__method;
        }
        return parent::getApiParamStructure($params_structure);
    }

    public function getCallData() {
        return parent::getCallData();
    }

}