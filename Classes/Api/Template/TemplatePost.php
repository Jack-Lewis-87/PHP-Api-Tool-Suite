<?php

include_once(dirname(__DIR__)."/Template/Template.php");         //Call Specifc//Incomplete//

class TemplatePost extends Template {
    
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
    protected $description = "Create or modify a template.";

    /*
     * cli parameters specific to this Call's method, eg User Get
     * These will over rule any existing keys in Parent Call method.
     *
     * @var array
     */
    private $cli_params__method = [
    	//returned_var => ["cli_entry_name", "Description"]
        "subject" => ["subject","Subject Line: The subject line of the email."],
        "content_html" => ["html","HTML: The HTML of the email."],
        "setup" => ["setup","Setup: Setup tab for preprocessing zephyr logic. Input as a string. Eg: '{content = horizon_select(content, 10)}'"],
        "data_feed_url" => ["feed","Data Feed: The URL of the data feed to use in the template."],
        "public_name" => ["public_name","Public Name: The name that appears as the sender of the template."],
        "from_name" => ["from_name","From Name: The name appearing in the from section of the email."],
        "from_email" => ["from_email","From Email: The email address to use as the “from” – choose from only your verified emails."],
        "replyto_email" => ["reply_to","Reply To: The reply to Email address – this should not be “noreply”."],
        "is_link_tracking" => ["tracking","Link Tracking: 1 if you want to use link-tracking rewrites in the email, default 0."],
        "is_google_analytics" => ["ga","Google Analytics: 1 if you want to use automatic Google Analytics tracking, default 0."],
        "revision" => ["revision", "Revision Id: Reverts a template back to the prior version of the revision ID."],
        "sample" => ["sample","Sample ID: The sample name (for A/B test templates)"],
        "content_text" => ["text","Text: The text only version of the email."],
        "content_sms" => ["sms","SMS: The content of the SMS message."],
        "verify_post_url" => ["verify_post_url","Postback: If the message is used as double optin and the verification is successful, the send_id and email address will be posted to this url"],
        "link_params" => ["append","URL Auto Append: Json string containing the keys and values of the link params, or prepend the key with append_"],
        "success_url" => ["success_url","Success URL: For Transactionals, success postback. May be depracated."],
        "error_url" => ["error_url","Error URL: For Transactionals, error postback. May be depracated."],
        "content_app" => ["content_app","Push Notifications: The content contained inside the push notification alert text."],
        "app_badge" => ["app_badge","Badge: The number in the corner of the application. (iOS only)"],
        "app_sound" => ["app_sound","Sound:  The sound that will play when an push notification is received."],
        "app_data" => ["app_data","An array of key value pairs that represents the data intended to send to the app."],
        // "template_vars" => ["vars","This is a hidden input. There are template vars, but I'm not sure I want to open this can of hidden worms."],
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
        "link_params" => "append_",
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
        // "keysconfict" => ["always_required" => ["keys"]],
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