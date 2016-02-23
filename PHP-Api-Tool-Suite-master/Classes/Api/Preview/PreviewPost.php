<?php

include(dirname(__DIR__)."/Preview/Preview.php");         //Call Specifc//Incomplete//

class PreviewPost extends Preview {
    
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
    protected $description = "Send a test email or return a preview of a send's HTML for a given user.";

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
        "email" => ["email","Email: The email address of the user for whom to generate dynamic content."],
        "send" => ["send","Test Send or HMTL Preview: 1 to send an email, 0 to return an html Preview. Default is 0 (html preview)."],
        "template" => ["template", "Template: The name of the template to preview."],
        "blast_id" => ["blast_id", "Blast Id: The id of a draft or scheduled blast to preview."],
        "blast_repeat_id" => ["repeat_id", "Repeat Blast Id: The id of the repeating blast to preview."],
        "content_html" => ["html","Content HTML: A block of text to parse and preview. Essentially the 'Code' tab."],
        "data_feed_url" => ["feed","Data Feed URL: The url of a live data feed."],
        "day" => ["day","Day: The date to preview. Format is flexible but yyyy-mm-dd is solid."],
        "test_vars" => ["test_vars","Test Vars: Variables for the template. Mimics Send vars. Pass a JSON object or each test var prepended with 'var_'."],
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
     *
     */
    private $api_params_validation__method = [
        //api_param => ["negation_param" => ["dependency_1", "dependency_2"], "always_required" => ["dependency_3"]],
        "always_required" => ["require_one" => ["template","blast_id","blast_repeat_id","content_html"], "always_required" => ["email"]], //Also moved to the helper method, so it only applies to this file and not children.
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
        "test_vars" => "var_",
    ];

    public function ingestInput($vars, $skipValidate = false) {
        parent::ingestInput($vars, $skipValidate);
        //This may not look correct, but the param configuration creates preview html as the default, so this contrivance turns the send test into the default. 
        if (isset($this->api_vars["send"])) {
            if ($this->api_vars["send"] == 1) {
                $this->api_vars["send_email"] = $this->api_vars["email"];
                unset($this->api_vars["email"]);
            } 
            unset($this->api_vars["send"]);
        }
    }
    
//helper methods
//No need to modify when creating a new class

    public function useQueryCLI() {
        parent::useQueryCLI();
    }
    
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