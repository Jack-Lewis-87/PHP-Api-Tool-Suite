<?php

include_once(dirname(__DIR__)."/Stats/Stats.php");         //Call Specifc//Incomplete//

class StatsBlast extends Stats {
    
    /*
     * Valid Parameters for a call
     *
     * @var array
     */
    protected $method = "get";

    /*
     * Stats type
     *
     * @var array
     */
    protected $stat = "blast";

    /*
     * Valid Parameters for a call
     *
     * @var array
     */
    protected $description = "Return information about a particular campaign or aggregated information from all campaigns over a specified date range.";

    /*
     * cli parameters specific to this Call's method, eg User Get
     * These will overwrite any existing keys in Parent Call method, but not
     * in the base api call abstract class. (This is because I want api key 
     * and secret to be the first params printed in the help menu.) So don't 
     * add stuff to the cli_params var.
     *
     * @var array
     *
     */
    private $cli_params__method = [
    	//returned_var => ["cli_entry_name", "Description"]
        "blast_id" => ["id", "Blast Id: The id of the blast to pull information from."],
        "start_date" => ["start","Start Date: The beginning of the date range from which to pull aggregated blast stats. Format is flexible, but yyyy-mm-dd is solid."],
        "end_date" => ["end","End Date: The end of the date range from which to pull aggregated blast stats. Format is flexible, but yyyy-mm-dd is solid."],
        "list" => ["list","List: Specify this option if you want to pull blast stats from one particular list."],
        "beacon_times" => ["open_times","Open times: Specify 1 to pull information about when a particular blast was opened."],
        "click_times" => ["click_times","Click times: Specify 1 to pull information about when links were clicked."],
        "clickmap" => ["clickmap","Clickmap: Specify 1 to pull click map information."],
        "domain" => ["domain","Domain: Specify 1 to pull information based on recipients’ email domains."],
        "engagement" => ["engagement","Engagement: Specify 1 to pull information based on levels of engagement."],
        "signup" => ["signup","Signup: Specify 1 to pull information based on signup dates."],
        "subject" => ["subject","Subject: Specify 1 to pull information based on subject lines."],
        "urls" => ["urls","Urls: Specify 1 to pull information based on urls."],
        "device" => ["device","Device: Specify 1 to pull information based on the number of clicks by device."],
        "topusers" => ["topusers","Topusers: Specify 1 to pull information based on the top users based on clicks, opens and purchases."],
        "banners" => ["banners","Ad Flight: Specify 1 to pull information based on your AdFlight banner performance."],
        "purchase_times" => ["purchase_times","Purchase times: Specify 1 to pull information based on the purchase times for transactions related to this campaign."],
        "purchase_items" => ["purchase_items","Purchase items: Specify 1 to pull information based on the items purchased from this campaign (quantity, url, etc.)"],
        // "" => ["",""],
    ];

    /*
     * Any Flags specific to this call. 
     *
     * @var array
     *
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
        "list" => ["always_required" => ["start_date"]],
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
    ];

    public function __construct() {
        $this->api_vars["stat"] = $this->stat;
        parent::__construct();
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