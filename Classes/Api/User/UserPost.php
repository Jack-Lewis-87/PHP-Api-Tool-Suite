<?php

include_once(dirname(__DIR__)."/User/User.php");         //Call Specifc//Incomplete//

class UserPost extends User {
    
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
    protected $description = "Create or modify a user.";

    /*
     * cli parameters specific to this Call's method, eg User Get
     * These will over rule any existing keys in Parent Call method.
     *
     * @var array
     */
    private $cli_params__method = [
    	//returned_var => ["cli_entry_name", "Description"]
        "vars" => ["vars", "User Var: Add a custom user var. Input a JSON object or enter each var name prepended with \"var_\" eg: var_first=jack."],
        "lists" => ["lists", "Lists: Add or remove users from list. Input a JSON object or enter each list name prepended with \"list_\" eg list_master=1."],
        "keys" => ["keys", "Keys: Add or Edit user keys. Input a JSON object or enter each key prepended with \"key_\" eg key_extid=123ab."],
        "keysconflict" => ["keysconflict", "Conflict: If you change keys to existing keys, you create a conflict. Resolve with \"error\" or \"merge\""],
        "optout_email" => ["optout", "Optout: Set Optout Status -- \"none\", \"valid\", \"all\", \"basic\", \"blast\""],
        "optout_templates" => ["optout_templates", "Template Optout: Opt a user in or out of a template (0 or 1). Enter template name prepended with \"template_\""],
        "cookies" => ["cookies", "Cookies: Convert the stored browser session data into user profile data. JSON or prepend \"cookie_\""],
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
        "vars" => "var_",
        "lists" => "list_",
        "keys" => "key_",
        "optout_templates" => "template_",
        "cookies" => "cookie_",
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
        "keysconfict" => ["always_required" => ["keys"]],
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