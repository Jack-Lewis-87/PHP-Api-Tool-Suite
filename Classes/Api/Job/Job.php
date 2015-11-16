<?php

include(dirname(__DIR__)."/ApiCallAbstract.php");        

class Job extends ApiCallAbstract {

    /*
     * Endpoint
     * @var array
     */
    protected $endpoint = "job";

    /*
     *
     * @var array
     */
    protected $cli_params__endpoint = [
        //returned_var_name => ["cli_user_entry_name", "Description"],
        // "" => ["", ""],
    ];

    /*
     * Gives the ability to input arrays in the command line by the individual members.
     *
     * @var array
     */
    protected $api_params_structure__endpoint = [
        //"returned_var_array_name" => "prefix_name",
    ];

    /*
     * Any Flags specific to this call. 
     *
     * @var array
     */
    private $cli_options__endpoint = [
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
    private $api_params_validation__endpoint = [
        //api_param => ["negation_param" => ["dependency_1", "dependency_2"], "always_required" => ["dependency_3"]],
    ];

    public function getCliParameters($child_params = null) {
        if ($child_params != null) {
            $cli_params = $child_params + array_reverse($this->cli_params__endpoint);
        } else {
            $cli_params = array_reverse($this->cli_params__endpoint);
        }
        return parent::getCliParameters($cli_params);
    }

    public function getCliOptions($child_options = null) {
        if ($child_options != null) {
            $cli_options = $child_options + $this->cli_options__endpoint;
        } else {
            $cli_options = $this->cli_options__endpoint;
        }
        return parent::getCliOptions($cli_options);
    }

    public function getApiParamValidation($child_param_validation = null) {
        if ($child_param_validation != null) {
            $param_validation = $child_param_validation + $this->api_params_validation__endpoint;
        } else {
            $param_validation = $this->api_params_validation__endpoint;
        }
        return parent::getApiParamValidation($param_validation);
    }

    public function getApiParamStructure($child_params_structure = null) {
        if ($child_params_structure != null) {
            $params_structure = $child_params_structure + $this->api_params_structure__endpoint;
        } else {
            $params_structure = $this->api_params_structure__endpoint;
        }
        return parent::getApiParamStructure($params_structure);
    }

    public function useQueryCLI() {
        parent::useQueryCLI();
    }
    
    
    public function getCallData() {
        return parent::getCallData();
    }

}