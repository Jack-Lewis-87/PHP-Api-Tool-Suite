<?php

include_once(dirname(__DIR__)."/AdPlan/AdPlan.php");         //Call Specifc//Incomplete//

class AdPlanPost extends AdPlan {
    
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
    protected $description = "Create or update an AdFlight plan.";

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
        "list" => ["list","List: List associated with plan."],
        "schedule" => ["schedule","Schedule: Comma seperated list of the days of week the plan runs. Eg: \"Mon, Fri\" "],
        "schedule_days" => ["days","Schedule Days: Array of YYYYMMDD = 0/1/-1 to indicate sending or not. Also see day."],
        "schedule_days_units" => ["day_units", "Schedule Days: Input schedule days by prepending an day represented by YYYYMMDD with \"day_\"."],
        "zones" => ["zones","Zones: Array of the zones within the plan (name, width, height). Input a JSON object or enter a zone fields prepended with \"zoneX_\", x being a zone -up to 10. eg: zone1_name=lead."],
        // "" => ["",""],
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
        "schedule_days" => "day_",
        "zone1" => "zone1_",
        "zone2" => "zone2_",
        "zone3" => "zone3_",
        "zone4" => "zone4_",
        "zone5" => "zone5_",
        "zone6" => "zone6_",
        "zone7" => "zone7_",
        "zone8" => "zone8_",
        "zone9" => "zone9_",
        "zone10" => "zone10_",
    ];

    public function ingestInput($vars, $skipValidate = false) {
        parent::ingestInput($vars, $skipValidate);
        if (isset($this->api_vars["schedule"])) {
            $tmp = explode(',', $this->api_vars["schedule"]);
            foreach ($tmp as $i => $b) {
                $tmp[$i] = trim($b);
            }
            $this->api_vars["schedule"] = $tmp;
        } 
        //Add individual Zones to the "zones" array
        for ($i=0; $i < 10; $i++) { 
            $zone = "zone".$i;
            if (isset($this->api_vars[$zone])) {
                if (!isset($this->api_vars["zones"])) {
                    $this->api_vars["zones"] = array();
                }
                array_push($this->api_vars["zones"], $this->api_vars[$item]);
                unset($this->api_vars[$item]);
            }
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