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
     * @var string
     */
    protected $format;


    /**
     *
     * Various Job params 
     * @var array
     */
    protected $job_data = array();


    /**
     *
     * Directory holding the split files for upload
     * @var string
     */
    protected $base_dir;

    /**
     *
     * Directory holding the split files for upload
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
     * Setting a lower bound to the stall limit. 
     * @var int
     */
    protected $stallMin = 240; 

    /**
     *
     * Maximum Jobs that can be run at once.
     * @var string
     */
    protected $simultaneous_uploads = 5000;

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
    protected $known_error_codes = [3,2];
    // [,"Missing required parameter:"]

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

    /**
     * Full import process. Splits a file, validates it's fields, and and uploads in chunks. 
     *
     * @param string $brand_name    :client or brand name used as identifier for directory creation
     * @param string $file 
     * @param object $job           :job type w/ extra data. (list)/(json)
     * @param string $base_dir      :directory to work out of. A folder (named after brand) is created to do actual import work in. Default is the folder of the import file.    
     * @param string $split_size   
     */
    public function uploadFile($job, $params) {
        if (is_array($params)) {
            if (!isset($params["brand_name"])) {
                throw new Exception("Missing the account/brand identifier: brand_name.");
            } else {
                $brand_name = $params["brand_name"];
            }
            if (!isset($params["file"])) {
                throw new Exception("Missing Required Parameter: file.");
            } else  {
                $file = $params["file"];
            }
            $base_dir = isset($params["base_dir"])?$params["base_dir"]:null; 
            $split_size = isset($params["split_size"])?$params["split_size"]:null; 
            $is_skip_check = isset($params["is_skip_check"])?$params["is_skip_check"]:null; 
        } else {
            throw new Exception("uploadFile expects the second parameter to be an array.");
        }
    	
        print "\nUpload Details:\n";

        $this->job_type = $job;
        $this->time_of_run = time();

        if (isset($params["list"])) {
            $this->format = "csv";
            $this->job_data["list"] = $params["list"];
            echo "List set: ".$this->job_data["list"]."\n";
        } else if (isset($params["file_type"])) {
            $this->format = strtolower(trim($params["file_type"]));
        } else {
            $this->format = "unknown";
        }

        if (file_exists($file)) {
    		$this->file = $file;
    	} else {
    		throw new Exception("File: ".$file." does not exist.");
    	}
        print ucfirst($this->job_type)." with $file\n";

        if ($base_dir != null) {
    	   $this->base_dir = $base_dir;
        } else {
            $this->base_dir = dirname($file);
        }

        print "Upload data stored to ".$this->base_dir."\n";

        if ($split_size != null) {
    	   $this->chunk_mem_split = $split_size;
        }

        if ($is_skip_check != null) {
           $this->is_skip_check = $is_skip_check;
            print "Error Checking and File Validation Disabled\n";
        }

        if (isset($params["report_email"])) {
            $this->notify_email = $params["report_email"];
        }

    	$this->business_name = $brand_name;

        $bytes_to_megabytes = 1000000;
        print "Splitting files into ".($this->chunk_mem_split/$bytes_to_megabytes)."mb chunks\n\n";

    	$this->createDirectories();
        
        //Supply a map for csv to json. Not functional yet.
    	$data = array();
	    if (isset($params["json"])) {
    		$data["json"] = $params["json"];
		} 

        print "Preprocessing File\n";
        //split and convert file. Conversion not built yet. 
    	try {
            $this->processFile($data);
        } catch (Exception $e) {
            throw $e;
        }
    	
        print "Starting File Upload\n";
        
        $this->notify($this->notify_templates["start"], $file);

        $this->uploadFiles();
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
    protected function createDirectories() { 
    	$business_name = $this->business_name;
    	$base_dir = $this->base_dir ."/". $business_name."_Uploads";
        $info = pathinfo($this->file);
        $file_dir = $base_dir."/".basename($this->file,'.'.$info['extension'])."_".$this->time_of_run;
    	$this->upload_dir = $upload_dir = $file_dir . "/upload";
    	$this->error_dir = $error_dir = $file_dir . "/error";
        
        print "Creating Directories\n";
		if ((file_exists($base_dir)) || (mkdir($base_dir) !== false)) {
    		if (file_exists($file_dir) || mkdir($file_dir) !== false) {
                if (file_exists($upload_dir) || mkdir($upload_dir) !== false) {
    			} else {
                    echo "3 \n";
        			throw new Exception("Unable to make the upload directory for ".$business_name.".");
        		}
        		if (file_exists($error_dir) || mkdir($error_dir) !== false) {
    			} else {
                        echo "4 \n";
    	    			throw new Exception("Unable to make the error directory for ".$business_name.".");
    	   		}	
            } else {
                echo "1 \n";
                throw new Exception("Unable to make the base directory for ".$business_name.".");
            }
		} else {
            echo "1 \n";
			throw new Exception("Unable to make the base directory for ".$business_name.".");
    	}
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

    protected function checkFileType($main_file) {
        if (($char = fgetc($main_file)) !== FALSE) {
            if ($char == "{") {
                $format = "json";
            } else {
                $format = "csv";
            }
        } else {
            throw new Exception("The file cannot be read.");
        }
        rewind($main_file);
        Print "Infering File is a ".$format." file.\n";
        return $format;
    }

    protected function processFile($data) {
        $format = $this->format;
        $encoding_check = true;
        $file = $this->file;
    	$business_name = $this->business_name;
    	$upload_dir = $this->upload_dir;
    	$chunk_mem_split = $this->chunk_mem_split;
    	$sub_file_num = 1;
    	$current_mem_sub_file = 0;
    	$day = $this->time_of_run;
    	$HEADER_ROW = 0;
    	$header = [];
        $headerArray = array();
    	$row = 0;
    	$log_invalids = $is_skip_check?false:true;

        $main_file = $this->openFile($file, "r");

        if ($format == "unknown") {
            $format = $this->checkFileType($main_file);
            $this->format = $format;
        }
    	$error_file = $this->openFile($this->error_dir ."/invalids_".$business_name."_".$day.".".$format, "w");
    	$sub_file = $this->openFile($upload_dir."/".$business_name."_".$sub_file_num."_".$day.".".$format, "w");
		$sub_file_num += 1;

		$isError = false;
        //print "Reading in data\n";
		while (($line = fgets($main_file)) !== FALSE) {

			$data = str_getcsv($line);

            // VALIDATION????
            if ($this->job_type == "import") {
    			foreach ($data as $i => $field) {
    				if ($row == $HEADER_ROW) {
    					if ($encoding_check && !$is_skip_check && !mb_check_encoding($line, "UTF-8")) {
                            fclose($error_file);
                            fclose($sub_file);
                            unlink($this->error_dir ."/invalids_".$business_name."_".$day.".".$format);
                            unlink($upload_dir."/".$business_name."_".($sub_file_num-1)."_".$day.".".$format);
    						throw new Exception("File needs to be UTF8.");
    					} else {
                            $encoding_check = false;
    						$header_memory = strlen($line);
    					}
    					if (!$is_skip_check && $i == 0 && (strtolower(trim($field)) != "email" && strtolower(trim($field)) != "extid")) {
                            fclose($error_file);
                            fclose($sub_file);
                            unlink($this->error_dir ."/invalids_".$business_name."_".$day.".".$format);
                            unlink($upload_dir."/".$business_name."_".($sub_file_num-1)."_".$day.".".$format);
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
            }  else {
                foreach ($data as $i => $field) {
                    if ($encoding_check && !$is_skip_check && !mb_check_encoding($line, "UTF-8")) {
                        fclose($error_file);
                        fclose($sub_file);
                        unlink($this->error_dir ."/invalids_".$business_name."_".$day.".".$format);
                        unlink($upload_dir."/".$business_name."_".($sub_file_num-1)."_".$day.".".$format);
                        throw new Exception("File needs to be UTF8.");
                    } else {
                        $encoding_check = false;
                        $header_memory = strlen($line);
                    }
                } //Loop to next field
            }

			if ($isError && $log_invalids) {
				$isError = false;
				fwrite($error_file, $line);
			}

			if (($current_mem_sub_file + strlen($line)) > $chunk_mem_split) {
				fclose($sub_file);
                $sub_file_size = filesize($upload_dir."/".$business_name."_".($sub_file_num - 1)."_".$day.".".$format);
                if ($sub_file_size > $chunk_mem_split) {
                    throw new Exception("Split files are larger than expected. Check encoding?");
                }
				$sub_file = $this->openFile($upload_dir."/".$business_name."_".$sub_file_num."_".$day.".".$format, "w");
    			++$sub_file_num;
                if ($this->job_type == "import") {
                    fwrite($sub_file, $header_line);
                }   
    			$current_mem_sub_file = $header_memory;
                //print $sub_file_num." File created\n";
			}

			$current_mem_sub_file += strlen($line);
			fwrite($sub_file, $line);

            $row += 1;  
	    } //Loop to next line	
        print "File split into ".($sub_file_num - 1)." files.\n";
	    fclose($sub_file);
		fclose($main_file);
    }

    //TBF
    protected function validateImportFile() {

    }

    protected function validateUpdateFile() {
       
    }

    protected function uploadFiles() {
    	$retry_limit = $this->retry_limit;
    	$SUCCESS_INDICATOR = 10; //arbitrary constant to indicate didn't need to retry api call.
    	$SLEEP_TIME = 120; //seconds
        $still_sleeping = false;
        $simultaneous_uploads = $this->simultaneous_uploads;
        $job = $this->job_type;
        $job_data = $this->job_data;
        $data = array('job' => $job);

    	$dir = $this->upload_dir;
    	if (!is_dir($dir)) {
    		throw new Exception("There is no upload directory.");	
    	}
		if (($dh = opendir($dir)) === false) {
			throw new Exception("Unable to open the split file's directory.");
		}

		$running_jobs = array();
		$uploading = true;

        while ($uploading) {
            //echo"entering import/update\n";
        	if (count($running_jobs) < $simultaneous_uploads) {
                if ($still_sleeping) {
                    $still_sleeping = false;
                    print "\n";
                }
                //echo"Run another job\n";
        		if (($file = readdir($dh)) !== false) {
                    if (strpos($file, ".") === 0) {
                        continue;
                    }

                    echo"Pulled $file from dir\n";

                    $data['file'] = $this->upload_dir."/".$file;
                    if ($job == "import") {
                        //echo"Doing an import\n";
                        $data['list'] = $job_data["list"];
                    }
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
                            }
						}
					}
					if ($retry == $SUCCESS_INDICATOR && !isset($response["error"])) {
                        $tmp_id = $response["job_id"];
                        print "$file succesfully uploaded: ID ".$tmp_id."\n";
						//add job_id to $running jobs
						$running_jobs[$tmp_id] = time();
					} else if (isset($response["error"])) {
                        var_dump($response);
                    } else {
                        var_dump($fail_e);
						//Log issue.
					}
        		} else {
                    print "All Files Transfered, waiting for remaining jobs to finish.\n";
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
        //this finishing code is doubly extraneous. I've set a magic check of 1K simultaneous
        //jobs to indicate the hack I've put in place to quickly get around my throttling code.
        while (count($running_jobs) > 0 && $simultaneous_uploads > 1000) {
            print "sleep, ";
            sleep($SLEEP_TIME);
            $running_jobs = $this->isFinished($running_jobs);
        }
        print "\n";
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
                    echo "Retry Check on $id\n";
					++$retry;
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
