<?php 

/**
 *
 * Interface with the sailthru client library. 
 * Call Retrying
 * Handle File uploads.
 *
 */
$baseUrl = dirname(__FILE__);

require_once($baseUrl."/Sailthru_Client.php");
require_once($baseUrl."/Sailthru_Util.php");
require_once($baseUrl."/Sailthru_Client_Exception.php");

include_once(dirname(__DIR__)."/CliScriptAbstract.php");

ini_set("auto_detect_line_endings", true);


class Sailthru_Implementation_Client {
    /**
     *
     * Sailthru API Key
     * @var string
     */
    public $client;

    /**
     *
     * Base File to be manipulated and uploaded
     * @var string
     */
    protected $file;

    /**
     *
     * Specific Job type to run 
     * @var string
     */
    protected $job_type;

    /**
     *
     * File ending
     *
     * @var string
     */
    protected $format;

    /**
     *
     * File ending
     *
     * @var string
     */
    protected $valid_formats = array("json","csv");    

    /**
     *
     * Various Job params 
     * @var array
     */
    protected $job_data = array();


    /**
     *
     * Exisiting directory to place all others.
     * @var string
     */
    protected $base_dir;

    /**
     *
     * Main Upload Directory 
     * @var string
     */
    protected $file_dir;

    /**
     *
     * Directory holding the file for lines that didn't pass validation. 
     * 
     * @var string
     */
    protected $error_dir;

    /**
     *
     * Directory holding the split files for upload
     * @var string
     */
    protected $upload_dir;

    /**
     *
     * Client's name for file naming conventions
     * @var string
     */
    protected $business_name;

    /**
     *
     * JSON Structure to convert to
     * @var string
     */
    protected $json;

    /**
     *
     * Templates to notify Job Runner. 
     *
     * @var array
     */
    protected $notify_templates = array("start" => "File Upload Start", "fail" => "Job Failure");

    /**
     *
     * Email to notify.
     * 
     * @var string
     */
    protected $notify_email = "jlewis@sailthru.com";

    /**
     *
     * How large each chunk should be, in bytes. Default 20mb (20000000)
     * @var int
     */
    protected $chunk_mem_split = 20000000;

    /**
     *
     * Length of time before deciding a job has stalled. should be 30 minutes per 20 mb chunk. 
     * 666666 is the magic constant that does that. 
     * co-efficient = Bytes/Time
     * @var int
     */
    protected $stallCoefficient = 666666; 

    /**
     *
     * Setting a lower bound to the stall limit. Edit: Seemingly not used.
     *
     * @var int
     */
    protected $stallMin = 240; 

    /**
     *
     * Maximum Jobs that can be run at once. 5-50 is reasonable. Over 1K is indicating don't use a limit. 
     * For reference based on file size and the file chunking size, 20mb for 5gb file = 5K uploads.
     * Set monitor jobs to true if you want this script to watch the jobs after upload. 
     *
     * @var string
     */
    protected $simultaneous_uploads = 5000;
    protected $is_monitor_jobs = false;
    /**
     *
     * Setting a Maximum API calls before failure is declared. 
     * @var int
     */
    protected $retry_limit = 3;

    /**
     *
     * Setting Error codes to stop retry on and just print. 
     * @var int
     */
    protected $known_error_codes = []; //[3,2];

    /**
     *
     * Run File Validation and Content Checks.
     * @var boolean
     */
    protected $is_skip_check = false;

    /**
     *
     * Unified run time reference.
     * @var boolean
     */
    protected $time_of_run;

    /**
     *
     * Default time zone.
     *
     * @var string
     */
    protected $time_zone = "America/New_York";

    /**
     * Instantiate a new Import Class and a Sailthru Client with it. Base directory is used to 
     *
     * @param string $api_key
     * @param string $secret
     * @param string $api_uri
     * @param string $base_dir
     */
    public function  __construct($api_key, $api_secret, $api_uri = false, $retry_limit = null) {
    	if ($api_uri) {
    		$this->client = new Sailthru_Client($api_key, $api_secret, $api_uri);
    	} else {
    		$this->client = new Sailthru_Client($api_key, $api_secret);
    	}
        if ($retry_limit != null) {
            $this->retry_limit = $retry;
        }
        
        $this->setTimeZone();
    }

    public function getCall($endpoint, $data) 
    {
        $retry_limit = $this->retry_limit;
        $fail = 0; //Failure counter
        do  //Loop around the actual call as a retry mechanism in case it fails. 
        {
            try 
            { 
                $response = $this->client->apiGet($endpoint, $data);
                $this->knownOrNoError($response)?($fail = $retry_limit + 1):($fail += 1); 
            } 
            catch (Sailthru_Client_Exception $e) 
            {
                $fail += 1;
                $fail==$retry_limit ? ($response = $e) : null;
            }
        }
        while ($retry_limit > $fail);
        return $response;
    }

    public function postCall($endpoint, $data) 
    {
        $retry_limit = $this->retry_limit;
        $fail = 0; //Failure counter
        do  //Loop around the actual call as a retry mechanism in case it fails. 
        {
            try 
            { 
                $response = $this->client->apiPost($endpoint, $data);
                $this->knownOrNoError($response)?($fail = $retry_limit + 1):($fail += 1);  
            }
            catch (Sailthru_Client_Exception $e) 
            {
                $fail += 1;
                $fail==$retry_limit ? ($response = $e) : null;
            }
        }
        while ($retry_limit > $fail);
        return $response;
    }

    public function deleteCall($endpoint, $data) 
    {
        $retry_limit = $this->retry_limit;
        $fail = 0; //Failure counter
        do  //Loop around the actual call as a retry mechanism in case it fails. 
        {
            try 
            { 
                $response = $this->client->apiDelete($endpoint, $data);
                $this->knownOrNoError($response)?($fail = $retry_limit + 1):($fail += 1); 
            }
            catch (Sailthru_Client_Exception $e) 
            {
                $fail += 1;
                $fail==$retry_limit ? ($response = $e) : null;
            }
        }
        while ($retry_limit > $fail);
        return $response;
    }

    private function knownOrNoError($response) 
    {
        if (isset($response["error"])) {
            return in_array($response["error"], $this->known_error_codes);
        }
        return true; //This means there was no error. 
    } 

    public function getAccountId() 
    {
        $response = $this->client->apiGet("settings", array());
        if (isset($response["id"])) 
        {
            return $response["id"];
        }
        return $response;
    }
    

    /*
     * Credit to Chris at clwill dot com
     * via php.net.
     *
     */
    protected function setTimeZone() {
        $timezone = $this->time_zone;
        
        // On many systems (Mac, for instance) "/etc/localtime" is a symlink
        // to the file with the timezone info
        if (is_link("/etc/localtime")) {
            
            // If it is, that file's name is actually the "Olsen" format timezone
            $filename = readlink("/etc/localtime");
            
            $pos = strpos($filename, "zoneinfo");
            if ($pos) {
                // When it is, it's in the "/usr/share/zoneinfo/" folder
                $timezone = substr($filename, $pos + strlen("zoneinfo/"));
            } 
        }
        else {
            // On other systems, like Ubuntu, there's file with the Olsen time
            // right inside it.
            if (file_exists("etc/timezone")) {
                $timezone = file_get_contents("/etc/timezone");
                if (!strlen($timezone)) {
                    $timezone = $this->time_zone;
                }
            }
        }
        date_default_timezone_set($timezone);
    }

    private function confirm($question, $failText) {
        CliScriptAbstract::confirm($question, $failText);
    }

    /**
     * Full import process. Splits a file, validates it's fields, and and uploads in chunks. 
     *
     * @param object $job           :job type w/ extra data. (list)/(json)
     * @param string $params   
     */
    public function uploadFile($job, $params) {
        if ($job !== null) {
            $this->job_data["job"] = $job;
        } else {
            throw new Exception("Job type was null.");
        }

        if (!is_array($params)) {
            throw new Exception("uploadFile expects the second parameter to be an array.");
        }

        if (isset($params["brand_name"])) {
            $this->business_name = $params["brand_name"];
            unset($params["brand_name"]);
        } else {
            throw new Exception("Missing the account/brand identifier: brand_name.");
        }

        if (isset($params["file"])) {
            $this->file = $params["file"];
            unset($params["file"]);
        } else  {
            throw new Exception("Missing Required Parameter: file.");
        }

        if (file_exists($this->file)) {
            $info = pathinfo($this->file);
            $this->file_core = basename($this->file,'.'.$info['extension']);
        } else {
            throw new Exception("File: ".$this->file." does not exist.");
        }

        if (isset($params["base_dir"])) {
            $this->base_dir = $params["base_dir"]."/". $this->business_name."_Uploads";
            unset($params["base_dir"]);
        } else {
            $this->base_dir = dirname($this->file)."/". $this->business_name."_Uploads";
        }

        if (isset($params["split_size"])) {
            $this->chunk_mem_split = $params["split_size"];
            unset($params["split_size"]);
        }

        if (isset($params["is_skip_check"])) {
            $this->is_skip_check = $params["is_skip_check"];
            unset($params["is_skip_check"]);
            print "Error Checking and File Validation Disabled\n";
        }

        if (isset($params["file_type"])) {
            if (in_array(strtolower(trim($params["file_type"])), $this->valid_formats)) {
                $this->format = strtolower(trim($params["file_type"]));
            } else {
                $this->format = "unknown";
            }
            unset($params["file_type"]);
        } else {
            $this->format = "unknown";
        }

        if (isset($params["report_email"])) {
            $this->notify_email = $params["report_email"];
        }

        $this->time_of_run = date("Y_m_d_Hi");

        $this->file_dir = $this->base_dir."/".$this->job_data["job"]."_".$this->file_core."_".$this->time_of_run;

        $api_log_path = $this->file_dir."/api_log_".$this->file_core."_".$this->time_of_run.".log";

        $api_log_str = "";

        $this->job_data = $this->job_data + $params;


        print "\nUpload Details:\n";
        $api_log_str .= "Upload Details:\n";
        
        if (isset($params["list"])) {
            echo "List set: ".$params["list"]."\n"; 
            $api_log_str .= "List set: ".$params["list"]."\n";
        } 

        print ucwords($this->job_data["job"], " _")." with ".$this->file."\n";
        $api_log_str .= ucfirst($this->job_data["job"])." with ".$this->file."\n";

        print "Upload data stored to ".$this->base_dir."\n";
        $api_log_str .= "Upload data stored to ".$this->base_dir."\n";

        $bytes_to_megabytes = 1000000;
        print "Splitting files into ".($this->chunk_mem_split/$bytes_to_megabytes)."mb chunks\n\n";
        $api_log_str .= "Splitting files into ".($this->chunk_mem_split/$bytes_to_megabytes)."mb chunks\n\n";

        $api_log_str = $this->createDirectories($api_log_str);
        
        $api_log = $this->openFile($api_log_path, "w");
        fwrite($api_log, $api_log_str);

        print "Preprocessing File\n";
        fwrite($api_log, "Preprocessing File\n");
        
        try {
            $this->processFile($api_log);
        } catch (Exception $e) {
            fwrite($api_log, print_r($e, true));
            throw $e;
        }

        print "Starting File Upload\n";
        fwrite($api_log, "Starting File Upload\n");
        
        $this->notify($this->notify_templates["start"], $this->file);

        try {
            $result = $this->uploadFiles($api_log);
        } catch (Exception $e) {
            fwrite($api_log, print_r($e, true));
            throw $e;
        }

        return $result;
    }

    //Open a file and assure it doesn't fail. 
    protected function openFile($file, $mode) {
        if (($file = fopen($file, $mode)) === FALSE) {
            throw new Exception("Unable to open ".$file);   
        }
        return $file;
    }
    
    /**
     * Creates the underlying file structure to enable a clean upload. 
     *
     */
    protected function createDirectories($api_log_str) { 
    	$base_dir = $this->base_dir;
        $file_dir = $this->file_dir;
        $this->upload_dir = $upload_dir = $file_dir . "/upload";
    	$this->error_dir = $error_dir = $file_dir . "/error";
        
        print "Creating Directories\n";
        $api_log_str .= "Creating Directories\n";

		if ((file_exists($base_dir)) || (mkdir($base_dir) !== false)) {
    		if (file_exists($file_dir) || mkdir($file_dir) !== false) {
                if (file_exists($upload_dir) || mkdir($upload_dir) !== false) {
    			} else {
        			throw new Exception("3: Unable to make the upload directory for ".$business_name.".");
        		}
        		if (file_exists($error_dir) || mkdir($error_dir) !== false) {
    			} else {
    	    			throw new Exception("4: Unable to make the error directory for ".$business_name.".");
    	   		}	
            } else {
                throw new Exception("2: Unable to make the base directory for ".$business_name.".");
            }
		} else {
			throw new Exception("1: Unable to make the base directory for ".$business_name.".");
    	}

        return $api_log_str;
    }

    protected function validateEmail($address) {
    	if (!$address) {
            return false;
        }

        $address = strtolower(trim($address));
        if (!$address) {
            return false;
        }
        if ($address[0] == '-') {
            return false;
        }
        // facebook proxymails are too long by one character
        if (strpos($address, '@proxymail.facebook.com')) {
            $result = filter_var(substr($address, 2), FILTER_VALIDATE_EMAIL);
        } else {
            $result = filter_var($address, FILTER_VALIDATE_EMAIL);
        }
        if (!$result) {
            return false;
        }
        return true;
    }

    protected function checkFile($main_file, $format, $is_skip_check) {
        if (($line = fgets($main_file)) !== FALSE) {
            if ($format == "unknown") {
                $char = $line[0];
                if ($char == "{") {
                    $format = "json";
                } else {
                    $format = "csv";
                }
                $this->confirm("Infering File is a ".$format." file.\nContinue?", "Provide file_type to set the format.");
            }
            if ($is_skip_check) {
                return $format;
            }
            if (!mb_check_encoding($line, "UTF-8")) {
                throw new Exception("File needs to be UTF8.");
            }
        } else {
            throw new Exception("The file cannot be read.");
        }
        rewind($main_file);
        return $format;
    }

    protected function processFile($api_log) {
        $file = $this->file;
        $format = $this->format;
        $upload_dir = $this->upload_dir;
        $chunk_mem_split = $this->chunk_mem_split;
        $day = $this->time_of_run;
        $is_skip_check = $this->is_skip_check;

        $row = 0;
        $encoding_check = true;
    	$sub_file_num = 1;
    	$current_mem_sub_file = 0;   	
    	$HEADER_ROW = 0;
        $headerArray = array();
    	$log_invalids = !$is_skip_check;

        $main_file = $this->openFile($file, "r");

        $format = $this->checkFile($main_file, $format, $is_skip_check);

    	$error_file = $this->openFile($this->error_dir ."/invalids_".$this->file_core."_".$day.".".$format, "w");
        $sub_file = $this->openFile($upload_dir."/".$this->file_core."_".$sub_file_num."_".$day.".".$format, "w");
        fwrite($api_log, "Split file ".$sub_file_num." created.\n");
		$sub_file_num += 1;

		$isError = false;

		while (($line = fgets($main_file)) !== FALSE) {

            // VALIDATION
            if ($this->file_type == "csv") {
                $data = str_getcsv($line);
    			foreach ($data as $i => $field) {
    				if ($row == $HEADER_ROW) {
						$header_memory = strlen($line);
    					if (!$is_skip_check && $i == 0 && (strtolower(trim($field)) != "email" && strtolower(trim($field)) != "extid")) {
                            fclose($error_file);
                            fclose($sub_file);
                            //unlink deletes the file. 
                            unlink($this->error_dir ."/invalids_".$this->file_core."_".$day.".".$format);
                            unlink($upload_dir."/".$this->file_core."_".($sub_file_num - 1)."_".$day.".".$format);
                            throw new Exception("The first column needs to be 'email' or 'extid'.");
    					}
    					$headerArray[$i] = $field;
    					$header_line = $line;
    				} else {
    					if ($headerArray[$i] == "email") {
    						if (!$is_skip_check && !$this->validateEmail($field)) {
    							$isError = true;
    						}
    					} else if (!$is_skip_check) {
    						//Any other validation
    					}
    				}
    			} //Loop to next field
            } else if ($this->file_type == "json") {
                $data = json_decode($line, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $isError = true;
                } else if (isset($data["email"]) && !$this->validateEmail($data["email"])) {
                    $isError = true;
                }
                $line;
            }

			if ($isError && $log_invalids) {
				$isError = false;
				fwrite($error_file, $line);
			}

			if (($current_mem_sub_file + strlen($line)) > $chunk_mem_split) {
				fclose($sub_file);
                $sub_file_size = filesize($upload_dir."/".$this->file_core."_".($sub_file_num - 1)."_".$day.".".$format);
                if ($sub_file_size > $chunk_mem_split) {
                    throw new Exception("Split files are larger than expected. Check encoding?");
                }
				$sub_file = $this->openFile($upload_dir."/".$this->file_core."_".$sub_file_num."_".$day.".".$format, "w");
    			fwrite($api_log, "Split file ".$sub_file_num." created.\n");
                $sub_file_num += 1;

                $current_mem_sub_file = 0;

                if ($this->file_type == "csv") {
                    fwrite($sub_file, $header_line);
                    $current_mem_sub_file = $header_memory;
                }   
			}

			$current_mem_sub_file += strlen($line);
			fwrite($sub_file, $line);

            $row += 1;  
	    } //Loop to next line	
        print "File split into ".($sub_file_num - 1)." files.\n";
        fwrite($api_log, "File split into ".($sub_file_num - 1)." files.\n");
	    fclose($sub_file);
		fclose($main_file);
    }

    //TBF
    protected function validateCSVFile() {

    }

    protected function validateJSONFile() {
       
    }

    protected function uploadFiles($api_log) {
        $data = $this->job_data; //Full Data for the API call
        $job = $this->job_data["job"];
        $dir = $this->upload_dir;
        $retry_limit = $this->retry_limit;
        $simultaneous_uploads = $this->simultaneous_uploads;

        $running_jobs = array();

        $retry_files_path = $this->file_dir."/retry_files_".$this->file_core."_".$this->time_of_run.".log";
    	$error_files_path = $this->file_dir."/error_files_".$this->file_core."_".$this->time_of_run.".log";
        $SUCCESS_INDICATOR = 10; //arbitrary constant to indicate didn't need to retry api call.
    	$SLEEP_TIME = 120; //seconds
        $still_sleeping = false;
        $uploading = true;
        $count_upload_success = 0;
        $count_upload_errors = 0;
        $count_upload_failures = 0;
	
    	if (!is_dir($dir)) {
    		throw new Exception("There is no upload directory.");	
    	}
		if (($dh = opendir($dir)) === false) {
			throw new Exception("Unable to open the file's subfiles directory.");
		}

        fwrite($api_log, "\nRecord of every upload attempt plus full response.\n");
        $retry_files = $this->openFile($retry_files_path, "w");
        $error_files = $this->openFile($error_files_path, "w");

        while ($uploading) {
            //Try to get another file for upload if the current running number is below the throttle limit.
        	if (count($running_jobs) < $simultaneous_uploads) {
                if ($still_sleeping) {
                    $still_sleeping = false;
                    print "\n";
                }
                //Try to find another file in the directory.
        		if (($file = readdir($dh)) !== false) {
                    //Skip default . and .. files
                    if (strpos($file, ".") === 0) {
                        continue;
                    }

                    echo"\nPulled $file from dir\n";

                    $data['file'] = $this->upload_dir."/".$file;

        			//upload file
        			$retry = 0;
        			while ($retry < $retry_limit) {
                        //echo"About to attempt an upload\n";
        				try {					
         					$response = $this->client->apiPost('job', $data, array('file')); 
                            $retry = $SUCCESS_INDICATOR;
						} catch (Exception $e) {
							++$retry;
                            if ($retry == $retry_limit) {
                                $fail_e = $e;
                                $fail_message = $e->getMessage();
                            }
						}
					}
					if ($retry == $SUCCESS_INDICATOR && !isset($response["error"])) {
                        $tmp_id = $response["job_id"];
                        //add job_id to $running jobs
                        $running_jobs[$tmp_id] = time();
                        $count_upload_success += 1;
                        print "$file succesfully uploaded: ID ".$tmp_id."\n";
					    fwrite($api_log, "\n$file succesfully uploaded: ID ".$tmp_id."\n");
                        fwrite($api_log, print_r($response, true));
					} else if (isset($response["error"])) {
                        $count_upload_errors += 1;
                        print "ERROR WITH ".$file.": ".$response["errormsg"]."\n";
                        fwrite($api_log, "\nERROR WITH ".$file.": ".$response["errormsg"]."\n");
                        fwrite($api_log, print_r($response, true));
                        fwrite($error_files, $data["file"]."\n");
                    } else {
                        $count_upload_failures += 1;
                        print "FAILURE WITH ".$file.": ".$fail_message."\n";
                        fwrite($api_log, "\nFAILURE WITH ".$file.": ".$fail_message."\n");
                        fwrite($api_log, print_r($fail_e, true));
                        fwrite($retry_files, $data["file"]."\n");
					}
        		} else {
                    if ($count_upload_errors == 0 && $count_upload_failures == 0) {
                        unlink($retry_files_path);
                        unlink($error_files_path);
                        print "All Files Successfully Uploaded.\n";
                    } else if ($count_upload_failures == 0) {
                        unlink($retry_files_path);
                        print "All Files Uploaded.\n";
                    } else if ($count_upload_errors == 0) {
                        unlink($error_files_path);
                        print "All Files Attempted.\n";
                    } else {
                        print "All Files Attempted.\n";
                    }
                    fwrite($api_log, "All Files Attempted");
        			$uploading = false;
        		}
        	} else {
                echo "sleep,";
                $still_sleeping = true;
        		//sleep 2 minutes
        		sleep($SLEEP_TIME);
        		//update the status of $running_jobs
        		$running_jobs = $this->isFinished($running_jobs);
        	}
	    }
		closedir($dh);

        //FAIR QUEUE edits mean we can drop as many jobs in an account as we want now. So,
        //this finishing code is doubly extraneous. I've set a magic check of 500 simultaneous
        //jobs to indicate I'm running a lot of jobs as once and don't want this to wait.
        while (count($running_jobs) > 0 && $this->is_monitor_jobs) {
            if ($uploading == false) {
                print "Waiting for remaining jobs to finish.\n";
                fwrite($api_log, "Waiting for remaining jobs to finish.\n");
                //This may not be the best solution, but its a now abandoned boolean that serves nicely to give a one time message. I don't want any more booleans.
                $uploading = true; 
            }
            print "sleep, ";
            $still_sleeping = true;
            sleep($SLEEP_TIME);
            $running_jobs = $this->isFinished($running_jobs);
        }
        if ($still_sleeping) {
            print "\n";
        }
        print "\n";

        $result = array("Successful Uploads" => $count_upload_success, "Error Response" => $count_upload_errors, "Failed to Upload" => $count_upload_failures);
        fwrite($api_log, print_r($result, true));

        return $result;
    }

    protected function isFinished($jobs) {
    	$retry_limit = $this->retry_limit;
    	$SUCCESS_INDICATOR = 10; //arbitrary constant to indicate didn't need to retry api call.
        $SECONDS_IN_MINUTE = 60;
        $STALL_MIN = $this->stallMin; 
    	$stallLimit = intval($this->chunk_mem_split/$this->stallCoefficient); //Stall Limit is ~ 1 hour/40 MBs 
        $stallLimit = ($stallLimit < $STALL_MIN)?$STALL_MIN:$stallLimit;  //Create a lower bound on stallLimit of 20 minutes
        $stallLimit = $SECONDS_IN_MINUTE*$stallLimit;  //Put stall limit in seconds.

    	$still_active = array();
        $retry = 0;
    	foreach ($jobs as $id => $time_stamp) {
	        while ($retry < $retry_limit) {
				try {
 					$response = $this->client->getJobStatus($id);
					if ($response["status"] != "completed") {
						if ((time() - $time_stamp) > $stallLimit) {
							$this->notify($this->$notify_template["fail"], $id);
						} else {
							$still_active[$id] = $time_stamp;
						}
					}
					$retry = $SUCCESS_INDICATOR;
				} catch (Exception $e) {
					$retry += 1;
				}
			}
            $retry = 0;
    	}

    	return $still_active;
	}

    protected function notify($template, $var = null) {
        $retry_limit = $this->retry_limit;
        $SUCCESS_INDICATOR = 10; //arbitrary constant to indicate didn't need to retry api call.
        $email = $this->notify_email;
        $retry = 0;
        while ($retry < $retry_limit) {
            try {
                $response = $this->client->send($template, $email, array("var" => $var)); 
                return;
            } catch (Exception $e) {
                ++$retry;
            }
            echo "Failed to send Notify Template.\n";
            return;
        }
    }

}
