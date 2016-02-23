<?php

interface CliScriptInterface {

	/*
	 * 
	 * 
	 */
    public function getCliDescription(); 

	public function getCliParameters($add_params);

	public function getCliOptions($add_options);
	
	//Fully formated description for any other inputs. 
	//Equivalent positioning to "Flags" or "Parameters".
	public function getOtherInputsDescription();
}