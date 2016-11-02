<?php
// Copyright 2009, FedEx Corporation. All rights reserved.
// Version 2.0.1   

require_once('../library/fedex-common.php5');

//The WSDL is not included with the sample code.
//Please include and reference in $path_to_wsdl variable.
$path_to_wsdl = "../wsdl/AddressValidationService_v4.wsdl"; 

ini_set("soap.wsdl_cache_enabled", "0");

$client = new SoapClient($path_to_wsdl, array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information

$request['WebAuthenticationDetail'] = array(
	'ParentCredential' => array(
		'Key' => getProperty('parentkey'), 
		'Password' => getProperty('parentpassword')
	),
	'UserCredential' => array(
		'Key' => getProperty('key'), 
		'Password' => getProperty('password')
	)
);

$request['ClientDetail'] = array(
	'AccountNumber' => getProperty('shipaccount'), 
	'MeterNumber' => getProperty('meter')
);
$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Address Validation Request using PHP ***');
$request['Version'] = array(
	'ServiceId' => 'aval', 
	'Major' => '4', 
	'Intermediate' => '0', 
	'Minor' => '0'
);
$request['InEffectAsOfTimestamp'] = date('c');

$request['AddressesToValidate'] = array(
	0 => array(
		'ClientReferenceId' => 'ClientReferenceId1',
     	'Address' => array(
     		'StreetLines' => array('100 Nickerson RD'),
           	'PostalCode' => '01752',
     		'City' => 'Marlborough',
     		'StateOrProvinceCode' => 'MA',
           	'CountryCode' => 'US'
		)
	),
	1 => array(
		'ClientReferenceId' => 'ClientReferenceId2',
       	'Address' => array(
       		'StreetLines' => array('167 PROSPECT HIGHWAY'),
       		'City' => 'New SOUTH WALES',
          	'PostalCode' => '2147',
           	'CountryCode' => 'AU'
		)
	),
	2 => array(
		'ClientReferenceId' => 'ClientReferenceId3',
		'Address' => array(
			'StreetLines' => array('3 WATCHMOOR POINT', 'WATCHMOOR ROAD'),
			'PostalCode' => 'GU153AQ',
			'City' => 'CAMBERLEY',
			'CountryCode' => 'GB'
		)
	),
	3 => array(
		'ClientReferenceId' => 'ClientReferenceId4',
		'Address' => array(
			'StreetLines' => array('6207 Westbrook'),
			'PostalCode' => '',
			'City' => '',
			'StateOrProvinceCode' => '',	
			'CountryCode' => 'US'
		)
	),

);

try {
	if(setEndpoint('changeEndpoint')){
		$newLocation = $client->__setLocation(setEndpoint('endpoint'));
	}

    $response = $client ->addressValidation($request);

    if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){
        foreach($response -> AddressResults as $addressResult){
        	echo 'Client Reference Id: ' . $addressResult->ClientReferenceId . Newline;
        	echo 'State: ' . $addressResult->State . Newline;
        	echo 'Classification: ' . $addressResult->Classification . Newline;
        	if($addressResult->EffectiveAddress){
        		echo 'Proposed Address:' . Newline;
        		echo '<table border="1">';
        		printAddress($addressResult->EffectiveAddress);
        		echo '</table>';
        	}
        	if(array_key_exists("Attributes", $addressResult)){
        		echo Newline . 'Address Attributes' . Newline;
        		echo '<table border="1">';
        		foreach($addressResult->Attributes as $attribute){
        			echo '<tr><td>' . $attribute -> Name . '</td><td>' . $attribute -> Value . '</td></tr>'; 
        		}
        		echo '</table>';
        	}
        	echo Newline;
        }
    	
    	printSuccess($client, $response);
    }else{
        printError($client, $response);
    } 
    
    writeToLog($client);    // Write to log file   
} catch (SoapFault $exception) {
    printFault($exception, $client);
}

function printAddress($addressLine){
	foreach ($addressLine as $key => $value){
		if(is_array($value) || is_object($value)){
			printAddress($value);
		}else{ 
			echo '<tr><td>'. $key . '</td><td>' . $value . '</td></tr>';
		}
	}
}

?>