<?php

include(dirname(__DIR__)."/Content/Content.php");         //Call Specifc//Incomplete//

class ContentPost extends Content {
    
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
    protected $description = "Create or update a piece of content in Sailthru.";

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
        "url" => ["url", "URL: The unique URL of the content. (Must include full URL i.e. http://www…)."],
        "title" => ["title","Title: The human-friendly title of the content."],
        "date" => ["date","Date: The created time of the content (if not provided, defaults to current time). Format is flexible, but yyyy-mm-dd is solid."],
        "expire_date" => ["expires","Expires: Day the content should expire. Once this date is reached the content will not be recommended. Format is flexible, but yyyy-mm-dd is solid."],
        "tags" => ["tags","Tags: A comma seperated list of tags. Eg: \"sailthru,lift,breakout-success\""],
        "vars" => ["vars","Vars: Arbitrary vars associated with the content. Input a JSON object or enter each var name prepended with \"var_\" eg: var_section=promotion."],
        "image" => ["image","Image: The main image URL for a piece of content. Other arbitrary image types can be added to the image array by prepending them with \"image_\". Will overwrite existing values."],
        "full_image" => ["full","Full Image: A large version's URL of the content's image. Images array will overwrite existing images array."],
        "thumb_image" => ["thumb","Thumbnail Image: A thumbnail version's URL of the content's image. Images array will overwrite existing images array."],
        "location" => ["location","Locale: Pass [latitude,longitude] to specify location of the content. Eg: [40.697299,-74.003906]"],
        "price" => ["price","Price: The price of the content in your Account's currency in cents."],
        "description" => ["desc","Description: Can be used for a brief summary-type description of the content."],
        "site_name" => ["site_name","Site Name: The name of the site that the content belongs to (useful for multiple brands)."],
        "author" => ["author","Author: The name of the author of the piece of content."],
        "spider" => ["spider","Respider: Pass 1 to force a respidering of the content within a few minutes. Pass 0 to prevent an automatic spidering on first call."],
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
        "always_required" => ["always_required" => ["url"]],
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
        "vars" => "var_",
        "tags" => "tag_",
        "images" => "image_",
    ];

    public function ingestInput($vars, $skipValidate = false) {
        parent::ingestInput($vars, $skipValidate);
        if (isset($this->api_vars["images"])) {
            $tmp = array();
            foreach ($this->api_vars["images"] as $a => $b) {
                $tmp[$a] = array("url" => $b);
                unset($this->api_vars[$a]);
            }
            $this->api_vars["images"] = $tmp;
            unset($tmp);
        }
        if (isset($this->api_vars["full_image"])) {
            if ($this->api_vars["images"]) {
                $this->api_vars["images"]["full"] = array("url" => $this->api_vars["full_image"]);
            } else {
                $this->api_vars["images"] = array("full" => array("url" => $this->api_vars["full_image"]));
            }
        }
        if (isset($this->api_vars["thumb_image"])) {
            if ($this->api_vars["images"]) {
                $this->api_vars["images"]["thumb"] = array("url" => $this->api_vars["thumb_image"]);
            } else {
                $this->api_vars["images"] = array("thumb" => array("url" => $this->api_vars["thumb_image"]));
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