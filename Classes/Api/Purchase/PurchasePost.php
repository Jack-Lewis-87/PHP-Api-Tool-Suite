<?php

include_once(dirname(__DIR__)."/Purchase/Purchase.php");         //Call Specifc//Incomplete//

class PurchasePost extends Purchase {
    
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
    protected $description = "Associate a purchase or abandoned cart with a user.";

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
        "email" => ["email","Email: Purchasing customer’s email."],
        "items" => ["items","Items: Enter the items array as a JSON Object, or each item seperately by prepended item attributes with \"itemX_\" where X is 1,2,3, etc up to 10 items. Eg. \"item1_qty\"."],
        "incomplete" => ["incomplete","Incomplete: Pass a 1 for abandoned cart."],
        "reminder_template" => ["reminder_template","Reminder Template: The Abandoned cart template in Sailthru."],
        "reminder_time" => ["reminder_time","Reminder Time: The delay to wait for a purchase update before sending reminder template."],
        "send_template" => ["send_template","Send Template: Confirmation template to send upon a complete purchase."],
        "vars" => ["vars", "Vars: Input a JSON Object or each var name prepended with \"var_\"."],
        "message_id" => ["message_id","Sailthru_bid: Pass the value of the \"sailthru_bid\" cookie to associate this purchase to a campaign."],
        "date" => ["date","Date: Pass in specific date for a purchase. Normally used for historic purchases. Format is flexible, but yyyy-mm-dd is solid."],
        "adjustments" => ["adjustments","Adjustments: Pass the proper arrrays as a JSON Object, or each the title and price prepended by \"adjust_\". Eg: \"adjust_sale=4.00\""],
        "tenders" => ["tenders","Tenders: Pass the proper arrrays as a JSON Object, or each the title and price prepended by \"tender_\". Eg: \"tender_Amex=4.00\""],
        "channel" => ["channel","Channel: Where the purchase took place. Options are app, online (default), or offline."],
        "cookies" => ["cookies","Cookies: Currently only used to pass sailthru_pc. Pass a JSON Object, the cookie name prepended with \"cookie_\", or use the sailthru_pc param"],
        "sailthru_pc" => ["pc","PC Cookie: The sailthru_pc cookie value."],
        "device_id" => ["device_id","Device ID: Available when channel is set to app. This is a unique device identifier (UDID)."],
        "user_agent" => ["user_agent","User Agent: The user agent string of the user making the purchase"],
        "purchase_keys" => ["purchase_keys","Keys: Currently only extid. Submit a valid JSON object or a key prepended with \"key_\". Or use the extid param."],
        "extid" => ["extid","Extid: A unique identifier for this purchase."],
        // "" => [],
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
        "always_required" => ["always_required" => ["email"], "item1" => ["items"]],
        "device_id" => ["always_required" => ["channel","extid","cookies"]],
        "reminder_time" => ["always_required" => ["reminder_template", "incomplete"]],
        "reminder_template" => ["always_required" => ["reminder_time", "incomplete"]],
        // "" => ["" => [""]],
    ];

    /*
     * Gives the ability to input arrays in the command line as individual the individual members.
     * Make sure to specify the prefix in the params description
     *
     * @var array
     */
    protected $api_params_structure__method = [
        //"returned_var_array_name" => "prefix_name"
        "item1" => "item1_",
        "item2" => "item2_",
        "item3" => "item3_",
        "item4" => "item4_",
        "item5" => "item5_",
        "item6" => "item6_",
        "item7" => "item7_",
        "item8" => "item8_",
        "item9" => "item9_",
        "item10" => "item10_",   //This is as many 'easy' entries as I cared to include. Should probably generalize this solution. To increase hardcoded limit, add more here and to getCallData()
        "adjustments_raw" => "adjust_",
        "tenders_raw" => "tender_",
        "cookies" => "cookie_",
        "purchase_keys" => "key_",
        "vars" => "var_"
    ];

    public function ingestInput($vars, $skipValidate = false) {
        parent::ingestInput($vars, $skipValidate);
        // Assemble the keys array
        if (isset($this->api_vars["extid"])) {
            if (isset($this->api_vars["purchase_keys"])) {
                $this->api_vars["purchase_keys"]["extid"] = $this->api_vars["extid"];
            } else {
                $this->api_vars["purchase_keys"] = array("extid" => $this->api_vars["extid"]);
            }
            unset($this->api_vars["extid"]);
        }
        // Assemble the cookies array
        if (isset($this->api_vars["sailthru_pc"])) {
            if (isset($this->api_vars["cookies"])) {
                $this->api_vars["cookies"]["sailthru_pc"] = $this->api_vars["sailthru_pc"];
            } else {
                $this->api_vars["cookies"] = array("sailthru_pc" => $this->api_vars["sailthru_pc"]);
            }  
            unset($this->api_vars["sailthru_pc"]);
        }
        //Assemble the adjustments array
        if (isset($this->api_vars["adjustments_raw"])) {
            if (!isset($this->api_vars["adjustments"])) {
                $this->api_vars["adjustments"] = array();
            }
            foreach ($this->api_vars["adjustments_raw"] as $title => $price) {
                array_push($this->api_vars["adjustments"], array("title" => $title, "price" => $price));
            }
            unset($this->api_vars["adjustments_raw"]);
        }
        //Assemble the tenders array
        if (isset($this->api_vars["tenders_raw"])) {
            if (!isset($this->api_vars["tenders"])) {
                $this->api_vars["tenders"] = array();
            }
            foreach ($this->api_vars["tenders_raw"] as $title => $price) {
                array_push($this->api_vars["tenders"], array("title" => $title, "price" => $price));
            }
            unset($this->api_vars["tenders_raw"]);
        }
        //Add individual Items to the "items" array
        for ($i=0; $i < 10; $i++) { 

            $item = "item".$i;
            if (isset($this->api_vars[$item])) {
                if (!isset($this->api_vars["items"])) {
                    $this->api_vars["items"] = array();
                }
                array_push($this->api_vars["items"], $this->api_vars[$item]);
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
