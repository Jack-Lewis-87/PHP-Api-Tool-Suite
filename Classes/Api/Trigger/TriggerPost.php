<?php

include(dirname(__DIR__)."/Trigger/Trigger.php");         //Call Specifc//Incomplete//

class TriggerPost extends Trigger {
    
    /*
     * The call type
     *
     * @var array
     */
    protected $method = "post";

    /*
     * Call Description
     *
     * @var array
     */
    protected $description = "Create or modify a trigger.";

    /*
     * CLI parameters specific to this Call's method, eg User Get
     *
     * These will overrule any existing params in Parent Call method.
     * Triggers use events for two meanings so action is substituted here
     * and overwritten in the getCallData function in this file to its true
     * API form
     *
     * @var array
     */
    private $cli_params__method = [
    	//returned_var => ["cli_entry_name", "Description"]
        "event" => ["event", "Event: Event Name"],
        "time" => ["time", "Time: Units of time to wait to run trigger after action/event. Must be an integer."],
        "time_unit" => ["unit", "Unit: Units of the time param, eg minutes, hours."],
        "action" => ["action","Action: Event to run trigger on click, open, send, purchase or cancel."],
        "zephyr" => ["zephyr","Zephyr: Valid zephyr syntax and logic for the trigger to run."],
        // "" => ["",""],
    ];

    /*
     * Gives the ability to input arrays in the command line as individual the individual members.
     * Make sure to specify the prefix in the params description 
     *
     * @var array
     */
    protected $api_params_structure__method = [
        //"returned_var_array_name" => "prefix_name_",
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
        "event" => ["trigger_id" => ["time", "time_unit", "zephyr"]],
        "template" => ["trigger_id" => ["time", "time_unit", "zephyr", "action"]],
        "always_required" => ["require_one" => ["template","event"]]
    ];
    
    /*
     * Any Flags specific to this call. 
     *
     * @var array
     */
    private $cli_options__method = [
        //returned_flag => ["cli_entry_name", "Description"],
        // "" => [],
    ];

    public function ingestInput($vars, $skipValidate = false) {
        parent::ingestInput($vars, $skipValidate);
        if (isset($this->api_vars["action"])) {
            $this->api_vars["event"] = $this->api_vars["action"];
            unset($this->api_vars["action"]);
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