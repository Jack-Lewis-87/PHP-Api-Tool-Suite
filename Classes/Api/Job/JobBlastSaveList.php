<?php

include_once(dirname(__DIR__)."/Job/JobPost.php");         //Call Specifc//Incomplete//

class JobBlastSaveList extends JobPost {

    /*
     * Stats type
     *
     * @var array
     */
    protected $job = "blast_save_list";

    /*
     * Valid Parameters for a call
     *
     * @var array
     */
    protected $description = "Query your users that received a specific campaign, and save that new segment to a new or existing list.";


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
        "blast_id" => ["id","Blast Id: The blast id of the campaign to pull stats for."],
        "list" => ["list","List: The name of the list to add users to (if it does not exist, it will be created)."],
        "query" => ["query","Query: A query or search for Users in the specified campaign. Input a JSON object, or use the rules set out below."],
        // "" => ["",""],
    ];

    /*
     * Any Flags specific to this call. 
     *
     * @var array
     */
    private $cli_options__job = [
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
    private $api_params_validation__job = [
        //api_param => ["negation_param" => ["dependency_1", "dependency_2"], "always_required" => ["dependency_3"]],
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
    ];

    public function __construct() {
        $this->api_vars["job"] = $this->job;
        $this->useQueryCLI();
        parent::__construct();
    }

//helper methods
//No need to modify when creating a new class

    public function useQueryCLI() {
        parent::useQueryCLI();
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

    public function getCliOptions($child_options = null) {
        if ($child_options != null) {
            $cli_options = $child_options + $this->cli_options__job;
        } else {
            $cli_options = $this->cli_options__job;
        }
        return parent::getCliOptions($cli_options);
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

    public function getCallData() {
        return parent::getCallData();
    }

}