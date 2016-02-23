<?php

include(dirname(__DIR__)."/BlastRepeat/BlastRepeat.php");         //Call Specifc//Incomplete//

class BlastRepeatPost extends BlastRepeat {
    
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
    protected $description = "Create or update a repeating campaign.";

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
        "name" => ["name","Name: Human Friendly name for the repeating Campaign. Will be displayed in the UI."],
        "list" => ["list","List: The mailer list to send the repeat campaign to."],
        "template" => ["template","Template: Base template the campaign will be built from. Its fields will be used."],
        "data_feed_url" => ["feed","Data Feed URL: The URL of a data feed to pull prior to sending the blast."],
        "days" => ["days_week","Days: Comma seperated string containing the days of the week when the campaign will be sent out.Eg: \"Mon, Fri, Sat, Sun\""],
        "days_month" => ["days_month","Days in Month: Comma seperated string containing the days in the month to send the campaign. Eg: \"1, 8, 15, 22\""],
        "send_time" => ["send_time","Send Time: The time when the campaign will be sent out. Eg: \"9:00 am\""],
        "generate_time" => ["generate","Generate Time: The campaign will generate this many hours prior to sending."],
        "report_email" => ["report_email","Report Email: This email will be emailed a copy of the campaign when it generates, and will receive a report when it has finished sending."],
        "start_date" => ["start_date","Begin date: The date the repeating campaign will take effect."],
        "end_date" => ["end_date","Stop Date: The date the repeating campaign will stop taking effect."],
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
        "always_required" => ["days_month" => ["days"], "days" => ["days_month"], "always_required" => ["name","list","template","send_time","generate_time","report_email"]],
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

    public function ingestInput($vars, $skipValidate = false) {
        parent::ingestInput($vars, $skipValidate);
        if (isset($this->api_vars["days"])) {
            $tmp = explode(',', $this->api_vars["days"]);
            foreach ($tmp as $i => $b) {
                $tmp[$i] = trim($b);
            }
            $this->api_vars["days"] = $tmp;
        } 
        if (isset($this->api_vars["days_month"])) {
            $tmp = explode(',', $this->api_vars["days_month"]);
            foreach ($tmp as $i => $b) {
                $tmp[$i] = trim($b);
            }
            $this->api_vars["days_month"] = $tmp;
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