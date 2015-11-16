<?php

include(dirname(__DIR__)."/Blast/Blast.php");         //Call Specifc//Incomplete//

class BlastPost extends Blast {
    
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
    protected $description = "Create, update, cancel and/or schedule a blast.";

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
        "name" => ["name","Name: The name to give to this new blast."],
        "list" => ["list","List: The mailing list name to send to."],
        "schedule_time" => ["time","Schedule Time: When the blast should send. Flexible dates, \"2013-09-08 20:00:00\" or relative time \"+5 hours\"."],
        "from_name" => ["from","From Name: The name appearing in the “From” of the email."],
        "from_email" => ["from_email","From Email: The email address to use as the “from” – choose from any of your verified emails"],
        "subject" => ["subject","Subject: The subject line of the email."],
        "content_html" => ["html","HTML: The HTML section of the email. Can include Zephyr."],
        "content_text" => ["text_only","Text Version: The text only version of the email. Can include Zephyr."],
        "blast_id" => ["id","Blast Id: Modify and existing Blast."],
        "copy_blast" => ["copy_blast","Copy Blast: The blast_id of a previous blast that you wish to copy the fields of."],
        "copy_template" => ["copy_template","Copy Template: The name of a template that you wish to copy the fields of. Zephyr will persist and resolve at send time."],
        "eval_template" => ["eval_template","Evaluated Template: Resolve all of the zephyr in a template and create a static campaign from it."],
        "replyto" => ["reply_to","Reply To: The Reply-To header to use in the email."],
        "report_email" => ["report_email","Report Email: An email address to receive a short report when the blast has finished sending."],
        "is_link_tracking" => ["track","Link Tracking: 1 if you want to use link-tracking rewrites in the email, 0 if not (default 0)."],
        "is_google_analytics" => ["ga","Google Analytics: 1 if you want to use automatic Google Analytics tracking, 0 if not (default 0).is_public"],
        "is_public" => ["public","Public: 1 if you want to have thistest_vars mass mail be publicly viewable by anyone, 0 if not (default 0)."],
        "suppress_list" => ["suppression","Suppression List: The name of a suppression list to use. Emails in the suppression list will not be emailed."],
        "test_vars" => ["test_vars","Test Vars: Vars for the GUI interface. Input as JSON object or enter each test var name prepended with \"tvar_\" eg: tvar_first=jack."],
        "email_hour_range" => ["pst","Personal Send Time Range: The number of hours to distribute the delivery out to, using Personalized Send Time."],
        "data_feed_url" => ["feed","Data Feed URL: The URL of a data feed to pull prior to sending the blast."],
        "vars" => ["vars","Vars: Key/value hash of variables to directly pass into the blast without using a data feed. Input JSON object, or prepend each var with \"var_\"."],
        "setup" => ["setup","Setup: The Setup section, a block of Zephyr code that will run prior to any other evaluation."],
        "link_params" => ["link_append","Auto Append Link Parameters: The Auto Append Link Parameters that will be added to every rewritten (tracked) link. Zephyr values with brackets should be passed as strings. This will overwrite, not add to, existing values."],
        "ad_plan" => ["ad","Ad Plan: The name of an adFlight Ad Plan to use with this blast."],
        "autoconvert_text" => ["convert","Autoconvert Text: Generate the content_text by doing a simple conversion on content_html."],
        "test_email" => ["test_email","Test Email: Send a test blast to the given email address."],
        "status" => ["status","Status: Set to \"draft\" to force a scheduled email to be a draft."],
        "content_app" => ["content_app","Push Notifications: The content contained inside the push notification alert text."],
        "app_badge" => ["app_badge","Badge: The number in the corner of the application. (iOS only)"],
        "app_sound" => ["app_sound","Sound:  The sound that will play when an push notification is received."],
        "app_data" => ["app_data","An array of key value pairs that represents the data intended to send to the app."],
        "app_id" => ["app_id","App Id: Push Notifications – This is the app_id that the message is intended to go to."],
        "labels" => ["labels","Labels: Array of Labels. Input a JSON Object or each label prepended with \"label_\". Eg: label_addLabel=1"],
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
        "schedule_time" => ["copy_template" => ["name","list","from_name","from_email","subject","content_html","content_text"], "copy_blast" => ["name","list","from_name","from_email","subject","content_html","content_text"], "eval_template" => ["name","list","from_name","from_email","subject","content_html","content_text"]],
        // "" => [],
    ];

    /*
     * Gives the ability to input arrays in the command line as individual the individual members.
     * Make sure to specify the prefix in the params description
     *
     * @var array
     */
    protected $api_params_structure__method = [
        //"returned_var_array_name" =>s "prefix_name"
        "test_vars" => "tvar_",
        "vars" => "var_",
        "labels" => "label_",
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