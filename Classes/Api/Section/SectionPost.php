<?php

include(dirname(__DIR__)."/Section/Section.php");         //Call Specifc//Incomplete//

class SectionPost extends Section {
    
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
    protected $description = "Creating a section for Onsite.";

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
        "name" => ["name","Human friendly name for a section."],
        "audiences" => ["audiences", "Audiences: Input a JSON object to match documentations spec, or use the components given below."],
        "feeds" => ["feedX","Feeds: For an arbitrarily numbered audience X, enter the feed id to power the blocks: eg feed1=\"id123abc\". Required."],
        "lists" => ["listX","Lists: In ascending order of priority (1 first, 10 last) Users on listX will recieve blockX's html. Give \"null\" to make an audience the default."],
        "blocks" => ["blockX_","Block: Ties to list and feed 'X', specifies the block type and a pre-existing block id. Should be blockX_type=id. Eg: block1_html=8fec2f68-6937-11e5-aa0d-002590d1a2f6"],
        "create_user" => ["creater","Create User: Email address to associate as the creater of this Section."],
        "modify_user" => ["modifier","Modify User: Email address to associate as this modifier of the Section."],
        // "" => ["", ""],
    ];

    /*
     * Any Flags specific to this call. 
     *
     * @var array
     */
    private $cli_options__method = [
        //returned_flag => ["cli_entry_name", "Description"]
        // "" => ["", ""],
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
        "feeds" => ["always_required" => ["lists","blocks"]],
        "lists" => ["always_required" => ["feeds","blocks"]],
        "blocks" => ["always_required" => ["lists","feeds"]],
        "always_required" => ["section_id" => ["name", "create_user"]]
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
        "feeds" => "feed",
        "lists" => "list",
        "blocks" => "block",
    ];

    public function ingestInput($vars, $skipValidate = false) {
        parent::ingestInput($vars, $skipValidate);
        if (isset($this->api_vars["feeds"]) && isset($this->api_vars["lists"]) && isset($this->api_vars["blocks"])) {
            if (!isset($this->api_vars["audiences"])) {
                $this->api_vars["audiences"] = array();
            }
            foreach ($this->api_vars["blocks"] as $i => $b) {
                $pos = substr($i, 0, strpos($i, "_"));
                $type = substr($i, strpos($i, "_") + 1);
                if (!isset($this->api_vars["audiences"][$pos])) {
                    $this->api_vars["audiences"][$pos] = array("blocks" => array());
                }
                array_push($this->api_vars["audiences"][$pos]["blocks"], array("render_name" => $type, "block_id" => $b));
            }
            foreach ($this->api_vars["lists"] as $i => $b) {
                $pos = substr($i, 0);
                if (!isset($this->api_vars["audiences"][$pos])) {
                    $this->api_vars["audiences"][$pos] = array();
                }
                if ($b === "null") {
                    $b = null;
                }
                $this->api_vars["audiences"][$pos]["list_id"] = $b;
            }
            foreach ($this->api_vars["feeds"] as $i => $b) {
                $pos = substr($i, 0);
                if (!isset($this->api_vars["audiences"][$pos])) {
                    $this->api_vars["audiences"][$pos] = array();
                }
                $this->api_vars["audiences"][$pos]["feed_id"] = $b;
            }            
            unset($this->api_vars["feeds"]);
            unset($this->api_vars["lists"]);
            unset($this->api_vars["blocks"]);
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