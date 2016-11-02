<?php 
/**
 * Name: SwiftPac API PHP Database Connector 
 * Description: Provides and interface between database used and API functions.
 * Author: SwiftPac 
 */

namespace lib\SwiftPac;
use lib\SwiftPac\DataTypes\SwiftpacAccountType;
use lib\SwiftPac\DataTypes\SwiftPacPremiumPaymentMethod;

class db {
	private $swiftPacApiDb;
	private $swiftPacLiveDb;
	private $cargoTrackSwiftPacLiveDb;
	
	public function __construct(){
		
		$servername = "181.224.147.138";
// 		$servername = "sql.vincyaviation.com";
		$username = "vincyavi_api";
		$password = "J3Trg2a%z.0~";
		$dbname = "vincyavi_swiftpac_api";
		
		$this->swiftPacApiDb = new \mysqli($servername, $username, $password, $dbname);
		
		$servername = "swiftpac.com";
		$username = "swiftpac_wp";
		$password = "}ZC}*4p)TqA7";
		$dbname = "swiftpac_live";
		
		$this->swiftPacLiveDb = new \mysqli($servername, $username, $password, $dbname);
	
		$servername = "swiftpac.cargotrack.net";
		$username = "swiftpac";
		$password = "r&dC@rG0#!4";
		$dbname = "swiftpac";
		
		$this->cargoTrackSwiftPacLiveDb = new \mysqli($servername,$username,$password,$dbname);
		
		
	}
	
	public function getUserData($username){
		
		$query = "
				
				SELECT u.user_id, username, password, email_address, company,
				um.user_meta_value AS address1,
				um1.user_meta_value AS city,
				um2.user_meta_value AS phone
				FROM (((users u JOIN user_meta um) JOIN user_meta um1) JOIN user_meta um2)
				WHERE u.user_id = um.user_id AND username = '$username' AND um.user_meta_key = 'address1'
				AND u.user_id = um1.user_id AND um1.user_meta_key = 'city'
				AND u.user_id = um2.user_id AND um2.user_meta_key = 'phone'
				";
		$result = $this->swiftPacApiDb->query($query);
		if (!$result) throw new \SoapFault("500", "Internal Server Error");
		
		return $result->fetch_object();
	}
	
	public function getUserDomains($username){
		
		$query = "
				SELECT http_host 
				FROM domain d,user_domain ud, users u
				WHERE u.user_id = ud.user_id AND d.domain_id = ud.domain_id AND u.username = '$username'
				";
		$result = $this->swiftPacApiDb->query($query);
// 		$domains = new \ArrayObject(array(),ArrayObject::STD_PROP_LIST);
		$domains = array();
		if($result){
	    while ($row = $result->fetch_object()){
	        $domains[] =  $row;
		}
	     // Free result set
	     $result->close();
		}
		
		return  $domains;
	}
	
	public function getMailBoxDifference ($actualWeight){
		
		$query = "
				SELECT difference 
				from wp_sp_volumetric_rules 
				where delivery_type = 'US MailBox' and min_measure <= " . $actualWeight . " and max_measure >= " . $actualWeight."
				";
		$result = $this->swiftPacLiveDb->query($query);
		$row = $result->fetch_object();
		$result->close();
		return $row;
	}

	public function getDomesticRates($pickupZip,$pickupState,$deliveryType,$minMeasure = null,$maxMeasure = null,$deliveryState = null){
	
		$query = "
	
		SELECT r.base_rate, r.additional_rate, r.zones
		FROM wp_sp_rates r INNER JOIN wp_sp_domestic_zones d ON r.zones = d.zones
		WHERE r.delivery_type LIKE '$deliveryType'
		AND d.pickup_zip = '$pickupZip'
		AND d.warehouse_state = '$pickupState'
	
		";
						
		if (isset($minMeasure)){
			$query .= "
					AND r.min_measure <= '$minMeasure'
					";
		}
		if (isset($maxMeasure)){
			$query .= "
					AND r.max_measure >= '$maxMeasure'
					";
		}
		
		if (isset($deliveryState)){
			
			$query .= "AND d.pickup_state = '$deliveryState'";
		}
	
		$rates = array();
		$result = $this->swiftPacLiveDb->query($query);
		if ($result){
			while ($row = $result->fetch_object()){
				$rates[] = $row;
			}
		}
		$result->close();
		return $rates;
	}
	
	public function getInternationalRates($origin,$destination,$deliveryType,$minMeasure = null ,$maxMeasure = null) {
		
		$originDest = $this->mapOriginDestination($origin, $destination);
		$origin = $originDest->origin;
		$destination = $originDest->destination;
		
		$query = "
			SELECT base_rate, markup, additional_rate, FSC, min_measure, max_measure
			FROM wp_sp_rates 
			WHERE delivery_type = '$deliveryType' 
			AND origin = '" . $origin . "' 
			AND destination = '" . $destination . "'";
			
		if (isset($minMeasure)) $query .= " AND min_measure <= " . $minMeasure . "";
		if (isset($maxMeasure)) $query .= " AND max_measure >= " . $maxMeasure. "";
		
		$this->var_error_log(array("rateQry" => $query));
		
		$rates = array();
		$result = $this->swiftPacLiveDb->query($query);
		if ($result){
			while ($row = $result->fetch_object()){
				$rates[] = $row;
			}
		}
// 		$result->close();
		$this->var_error_log(array("minmeas" => $minMeasure, "max_measure"=>$maxMeasure));
		return $rates;
		
	}
	
	public function getCaribbeanExpressRates($destination,$deliveryType,$minMeasure = null, $maxMeasure = null){
		
		$originDest = $this->mapOriginDestination(null, $destination);
		$destination = $originDest->destination;
		
		$query = "
				SELECT base_rate, markup 
				FROM wp_sp_rates 
				WHERE delivery_type = '$deliveryType' 
				AND destination = '$destination'";
				
		if (isset($minMeasure)) $query .= " AND min_measure <= " . $minMeasure . "";
		if (isset($maxMeasure)) $query .= " AND max_measure >= " . $maxMeasure. "";
		
		$this->var_error_log(array("cbeanrates" => $query));
		
		$rates = array();
		$result = $this->swiftPacLiveDb->query($query);
		if ($result){
			while ($row = $result->fetch_object()){
				$rates[] = $row;
			}
		}
		$result->close();
		return $rates;
		
	}
	
	public function getDomesticExpressRates ($origin,$warehouseState,$actualWeight){
		
		$originDest = $this->mapOriginDestination(null, $destination);
		$destination = $originDest->destination;
		
		$query = "
			SELECT r.base_rate, r.additional_rate, r.zones 
			FROM wp_sp_rates r INNER JOIN wp_sp_domestic_zones d ON r.zones = d.zones 
			WHERE r.delivery_type LIKE 'Domestic Express' 
			AND r.min_measure <= " . $actualWeight . " 
			AND r.max_measure >= " . $actualWeight . " 
			AND d.pickup_zip = " . $origin . " 
			AND d.warehouse_state = '" . $warehouseState . "'
				";
		
		$result = $this->swiftPacLiveDb->query($query);
		$rate = $result->fetch_object();
		$result->close();
		return $rate;
	}
	
	public function getDomesticCommodityRates($deliveryType,$packages,$shipment) {
		
		$packageCount = count($packages);
		$pickupZip = $shipment->shipFrom->zip;
		$originState = $shipment->shipFrom->state;
		
		$query = "";
		for ($i =0; $i<$packageCount; $i++){
			
			$actualWeight = $packages[$i]->weight;
			$contentDescription = $packages[$i]->contentDescription;
			$packageClass = $packages[$i]->packageClass;
			$packagingType = $packages[$i]->packagingType;
			
			$query .="
			SELECT r.base_rate, r.additional_rate, r.zones, '" . $actualWeight . "' AS pkg_weight, '" . $packagingType . "' as pkg_type, (
				SELECT f.rate
				FROM wp_sp_freight_class AS f
				WHERE f.class = '" . $packageClass . "' LIMIT 1) AS commodity_rate
			FROM wp_sp_rates AS r  INNER JOIN wp_sp_domestic_zones AS d ON r.zones = d.zones
			WHERE r.delivery_type LIKE '$deliveryType'
			AND r.min_measure <= " . $actualWeight . "  AND r.max_measure >= " . $actualWeight . "  AND d.pickup_zip = " . $pickupZip . "
			AND d.warehouse_state = '" . $originState . "'
					";
			if ($i+1 < $packageCount) $query .= " UNION ALL";
				
		}
				
		$rates = array();
		$result = $this->swiftPacLiveDb->query($query);
		if ($result){
			while ($row = $result->fetch_object()){
				$rates [] = $row;
			}
		}
		$result->close();
		return $rates;
	}
	
	public function getDomesticCommodityRates_beta($packages, $deliveryType, $warehouseState,  $minMeasure, $maxMeasure, $pickupZip, $pickupState) {
		
		$packageCount = count($packages);
// 		$pickupZip = $shipment->shipFrom->zip;
// 		$originState = $shipment->shipFrom->state;
		
		$query = "";
		for ($i =0; $i<$packageCount; $i++){
			
			$packageWeight = $packages[$i]->weight;
			$packageClass = $packages[$i]->packageClass;
			$packagingType = $packages[$i]->packagingType;
			
			$query .="
			SELECT r.base_rate, r.additional_rate, r.zones, '" . $packageWeight . "' AS pkg_weight, '" . $packagingType . "' as pkg_type, (
				SELECT f.rate
				FROM wp_sp_freight_class AS f
				WHERE f.class = '" . $packageClass . "' LIMIT 1) AS commodity_rate
			FROM wp_sp_rates AS r  INNER JOIN wp_sp_domestic_zones AS d ON r.zones = d.zones
			WHERE r.delivery_type LIKE '$deliveryType' ";
			
			if (isset($warehouseState)) $query .= "AND d.warehouse_state = '$warehouseState' "; 
			if (isset($minMeasure)) $query .= "AND r.min_measure <= $minMeasure "; 
			if (isset($maxMeasure)) $query .= "AND r.max_measure >= $maxMeasure "; 
			if (isset($pickupZip)) $query .= "AND d.pickup_zip = $pickupZip "; 
			if (isset($pickupState)) $query .= "AND d.pickup_state = '$pickupState' "; 
					
			if ($i+1 < $packageCount) $query .= " UNION ALL";
				
		}
				
		$rates = array();
		$result = $this->swiftPacLiveDb->query($query);
		if ($result){
			while ($row = $result->fetch_object()){
				$rates [] = $row;
			}
		}
		return $rates;
	}
	
	public function getDomesticSmallPkgRate($actualSumPkgWeight,$shipment){
		
		$pickupZip = $shipment->shipFrom->zip;
		$originState = $shipment->shipFrom->state;
		
		$query = "
				
			SELECT r.base_rate, r.additional_rate, r.zones
			FROM wp_sp_rates r INNER JOIN wp_sp_domestic_zones d ON r.zones = d.zones
			WHERE r.delivery_type LIKE 'Domestic Small Package'
			AND r.min_measure <= '$actualSumPkgWeight' 
			AND r.max_measure >= '$actualSumPkgWeight'
			AND d.pickup_zip = '$pickupZip'
			AND d.warehouse_state = '$originState'
				
			";
		
		$result = $this->swiftPacLiveDb->query($query);
		$row = $result->fetch_object();
		$result->close();
		return $row;
		
	}
	
	public function getAirOceanTransitTime($origin, $destination, $deliveryType){
		
		$originDest = $this->mapOriginDestination($origin, $destination);
		$destination = $originDest->destination;
		$origin = $originDest->origin;
		
		switch ($origin){
			case 'MIA':
				$origin = 'Miami, FL';
			break;
		}
		$query = "
				SELECT Transit_Time, Depart_Days
				FROM wp_sp_transit_times
				WHERE origin = '$origin'
				AND destination = '$destination'
				AND Delivery_Type = '$deliveryType'";
		
// 		$this->(array ("qry" => $query));
		$result = $this->swiftPacLiveDb->query($query);
// 		$this->var_error_log(array ("result" => $result));
		$transitData = $result->fetch_object();
		$result->close();
		return $transitData;
		
		
	}
	private function mapOriginDestination($origin,$destination){
		
		switch ($origin){
			case "33166":
				$origin = "MIA";
				break;
			case "32804":
				$origin = "ORL";
				break;
			case "11234":
				$origin = "NY";
				break;
			case "77010":
				$origin = "HUS";
				break;
			case "30301":
				$origin = "ATL";
				break;
			case "20837":
				$origin = "MD";
				break;
			case "2136":
				$origin = "BOS";
				break;
			case "19140":
				$origin = "PA";
				break;
			case "7032":
				$origin = "NJ";
				break;
		}
		
		switch ($destination){
			case "AG":
				$destination = "Antigua and Barbuda";
				break;
			case "BB":
				$destination = "Barbados";
				break;
			case "BZ":
				$destination = "Belize";
				break;
			case "DM":
				$destination = "Dominica";
				break;
			case "GD":
				$destination = "Grenada";
				break;
			case "GY":
				$destination = "Guyana";
				break;
			case "HT":
				$destination = "Haiti";
				break;
			case "JM":
				$destination = "Jamaica";
				break;
			case "KN":
				$destination = "Saint Kitts and Nevis";
				break;
			case "LC":
				$destination = "Saint Lucia";
				break;
			case "MF":
				$destination = "Saint Maarten";
				break;
			case "VC":
				$destination = "Saint Vincent and the Grenadines";
				break;
			case "VG":
				$destination = "Tortola";
				break;
			case "TT":
				$destination = "Trinidad and Tobago";
				break;
		}
		
		$origDest = new \stdClass();
		$origDest->origin = $origin;
		$origDest->destination = $destination;
		
		return $origDest;
	}
	
	public function createAccountSwiftPacCargoTrack($createAccountList){
		
		$accountResults = array();
		
		foreach ($createAccountList as $createAccount){
				
			//=====================Insert into cargotrack======================
			//insert into client table
			$company = $createAccount->firstName . " ". $createAccount->lastName;
			$address1 = $createAccount->address1;
			$city =  $createAccount->city;
			$state = $createAccount->state;
			$zip = $createAccount->zipCode;
			$email = $createAccount->email;
			$password = $createAccount->password;
			$accountType =  $createAccount->accountType;
			$countryCode = $createAccount->countryCode;
				
			if (strlen ( $createAccount->branch ) > 3) {
				$this->var_error_log(array("'branch" => $createAccount->branch));
				$branchString = explode ( "_", $createAccount->branch );
				$branch = $branchString [0];
				$branchAccountId = $branchString [1];
			}
				
			$licensePlate = date ( "Y:m:d H:i:s" );
			$removethese = array (
					":",
					".",
					" "
			);
			$licensePlate = str_replace ( $removethese, '', $licensePlate );
				
			$companySearch = str_replace ( ' ', '', $company );
			$companySearch = str_replace ( ',', '', $companySearch );
				
			$language = 'EN';
				
			if ($createAccount->phone !== '') {
					
				$pattern = "/[^0-9]/";
				$replacement = "";
				$phone = preg_replace($pattern, $replacement, $createAccount->phone);
				$phoneCC = substr($phone,0,1);
				$phoneAC = substr($phone, 1,3);
				$phoneFinal = substr($phone, 4,7);
			}
				
			if ($createAccount->fax !== '') {
				$pattern = "/[^0-9]/";
				$replacement = "";
				$fax = preg_replace($pattern, $replacement, $createAccount->fax);
					
				$faxCC = substr($fax,0,1);
				$faxAC = substr($fax, 1,3);
				$faxFinal = substr($fax, 4,7);
			}
				
			if ($createAccount->mobile !== '') {
				$pattern = "/[^0-9]/";
				$replacement = "";
				$cellular = preg_replace($pattern, $replacement, $createAccount->mobile);
					
				$cellularCC = substr($cellular, 0,1);
				$cellularAC = substr($cellular, 1,3);
				$cellularFinal = substr($cellular, 4,7);
			}
			
			//validate user
			$validateQuery = "
					
					SELECT email FROM clients WHERE email = '$email'
					";
			$resultValQry = $this->cargoTrackSwiftPacLiveDb->query($validateQuery);
			
			$emails = array();
			if($resultValQry){
				
				while ($row = $resultValQry->fetch_object()){
					$emails[] =  $row;
				}
				
// 				$this->k(array("email"=>$emails));
				
				if (!empty($emails)) return array();
			}
			
			
			$cargoTrackCreateAccountQry = "INSERT INTO clients " . "(company,address1,city,state,zip_code,email,passw,type,country,location,branch,access_control,account_id,license_plate,company_search," . "phone_cc,phone_ac,phone,fax_cc,fax_ac,fax,mobile_cc,mobile_ac,mobile,language,user,client,date) " . "values ('" . $company . "','" . $address1 . "','" . $city . "','" . $state . "','" . $zip . "','" . $email . "','" . $password . "','" . $accountType . "','" . $countryCode . "','" . $branch . "','" . $branch . "','" . $branch . "','" . $branchAccountId . "','" . $licensePlate . "','" . $companySearch . "','" . $phoneCC . "','" . $phoneAC . "','" . $phoneFinal . "','" . $faxCC . "','" . $faxAC . "','" . $faxFinal . "','" . $cellularCC . "','" . $cellularAC . "','" . $cellularFinal . "','" . $language . "','www.swiftpac.com','Y',CurDate())";
				
			$isCargoTrackAccCreated = $this->cargoTrackSwiftPacLiveDb->query($cargoTrackCreateAccountQry);
				
			// Update BoxNo Field
			if ($isCargoTrackAccCreated) {
				$cargoTrackUpdateAccountQry = "Update clients set account=client_id, box_number=concat(branch,client_id) " . "where license_plate = '" . $licensePlate . "' and email = '" . $email . "'";
					
				$this->cargoTrackSwiftPacLiveDb->query ( $cargoTrackUpdateAccountQry );
					
				$getClientInfoQry = "Select client_id, box_number from clients " . "where license_plate = '" . $licensePlate . "' and email = '" . $email . "'";
					
				$clientInforesult = $this->cargoTrackSwiftPacLiveDb->query ( $getClientInfoQry );
				if ($clientInforesult){
					while ($row = $clientInforesult->fetch_object()){
						$clientId = $row->client_id;
						$boxNumber = $row->box_number;					}
				}
		
				$cargoTrackCreateUserQry = "Insert into users (first_name,last_name,email,user,password,type,language,account_id,account,branch,whs_client,shipments_client,invoices_client,prealert_client) " . "Select '" . $createAccount->firstName . "','" . $createAccount->lastName . "','" . $email . "',box_number,'" . $password . "','1','EN',client_id, '" . $company . "'," . "branch,'Y','Y','Y','N'" . "from clients " . "where license_plate = '" . $licensePlate . "' and email = '" . $email . "'";
				$isCreatedUser = $this->cargoTrackSwiftPacLiveDb->query($cargoTrackCreateUserQry);
				
				//validate cargotrack account and user creation
				if ($isCargoTrackAccCreated == true && $isCreatedUser == true){
						
					$isCargoTrackAccInserted = true;
				}
				else $isCargoTrackAccInserted = false;
				//=================================End of Create Account in cargotrack ==================================
		
				//============================Insert user into SwiftPac Db wordpress=============================
				
				//Insert into users table
				$swiftPacCreateUserQry = "
				INSERT INTO wp_users (user_login, user_pass, user_nicename, user_email,user_registered,user_status,display_name)
				VALUES ('$boxNumber',MD5('$password'),'$boxNumber','$email','".date('Y-m-d H:i:s')."','0','".$createAccount->firstName." ".$createAccount->lastName."')";
		
					
				$isSwiftPacCreateAccount = $this->swiftPacLiveDb->query($swiftPacCreateUserQry);
		
				//get user id
// 				$this->var_error_log(array("query insert" => $isSwiftPacCreateAccount ));
				
				if ($isSwiftPacCreateAccount){
					$swiftPacGetUserIdQry = "SELECT id
					FROM wp_users
					WHERE user_login = '$boxNumber'";
					$getUserIdResult = $this->swiftPacLiveDb->query($swiftPacGetUserIdQry);
					
						
					if ($getUserIdResult){
						while ($row = $getUserIdResult->fetch_object()){
							$userId = $row->id;
								
						}
// 						$this->var_error_log(array("user id" => $userId ));
						
						//Insert into usermeta table
						$swiftPacUpdateUserMetaQry = "
						INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
						VALUES ('$userId','nickname','$boxNumber'),
						('$userId','first_name','".$createAccount->firstName."'),
						('$userId','last_name','".$createAccount->lastName."'),
						('$userId','description',''),
						('$userId','rich_editing','true'),
						('$userId','comment_shortcuts','false'),
						('$userId','admin_color','fresh'),
						('$userId','use_ssl','0'),
						('$userId','show_admin_bar_front','true'),
						('$userId','wp_capabilities','a:1:{s:8:"."customer".";b:1;}'),
						('$userId','wp_user_level','0'),
						('$userId','dismissed_wp_pointers','wp360_locks,wp390_widgets'),
						('$userId','cargotrack_id','$clientId'),
						('$userId','cargotrack_pass','$password')
						";
		
						if ($accountType == SwiftpacAccountType::PRIVATE_ACC){
							$swiftPacUpdateUserMetaQry .= ",('$userId','cargotrack_class','FREE')";
						}
		
						else if ($accountType == SwiftpacAccountType::PREMIUM_ACC){
							$swiftPacUpdateUserMetaQry .= ",('$userId','cargotrack_class','PREMIUM')";
							$swiftPacUpdateUserMetaQry .= ",('$userId','premium_date','".date("Y-m-d H:i:s")."')";
							$swiftPacUpdateUserMetaQry .= ",('$userId','payment_method','".$createAccount->paymentMethod."')";
								
							if ($createAccount->paymentMethod == SwiftPacPremiumPaymentMethod::CREDIT_CARD){
		
								$swiftPacUpdateUserMetaQry .= ",('$userId','auto_renewal','yes')";
							}
							else {
								$swiftPacUpdateUserMetaQry .= ",('$userId','auto_renewal','no')";
							}
						}
						$isSwiftPacUpdateAccount = $this->swiftPacLiveDb->query($swiftPacUpdateUserMetaQry);
				
						if ($isSwiftPacCreateAccount == true && $isSwiftPacUpdateAccount == true){
								
							$isSwiftPacAccInserted = true;
						}
						else $isSwiftPacAccInserted = false;
							
					}
						
					//============================End of Insert user into swiftpac db wordpress=============================
				}
			}
				
			$accountResults[$boxNumber] = array(
						
					"isCargoTrackAccInserted" => $isCargoTrackAccInserted,
					"isSwiftPacAccInserted" => $isSwiftPacAccInserted
			);
			
			return $accountResults;
		
		}
		
	}
	
	public function getSwiftPacUser($userLogin = null, $userEmail = null, $firstName = null, $lastName = null){
		
		$query = "
				SELECT client_id, box_number as user_login, 
				company as first_last_name, address1, address2, 
				city, state, zip_code,country as country_code,email, TYPE AS account_type, 
				CONCAT(mobile_cc,mobile_ac,mobile) AS mobile_number, passw as password
				FROM clients
				WHERE TRUE = TRUE 
				
				";
		if (isset($firstName) && !isset($lastName)){
			$query .= "AND company LIKE '%$firstName%'";

		}
		if (isset($lastName) && !isset($firstName)){
			$query .= "AND company LIKE '%$lastName%'";

		}
		if (isset($lastName) && isset($firstName)){
			$query .= "AND company LIKE '$firstName"." "."$lastName'";

		}
		if (isset($userLogin)){
				
			$query .= "AND box_number = '$userLogin'";
		}
		if (isset($userEmail)){
			$query .= "AND email LIKE '%$userEmail%'";

		}
				
// 		$this->(array("querycargotrack" => $query));
		$result = $this->cargoTrackSwiftPacLiveDb->query($query);
		
		$swiftPacUsers = array();
		
// 		$this->var_error_log(array("query" =>$query));
		if($result){
			while ($row = $result->fetch_object()){
				$swiftPacUsers[] =  $row;
			}
			// Free result set
			$result->close();
		}
		
		
		return  $swiftPacUsers;
	}
	
	public function closeConn(){
		$this->swiftPacApiDb->close();
	}
	
	public function closeSwiftPacLiveConn(){
		$this->swiftPacLiveDb->close();
		
	}
	public function closeCargoTrackSwiftPacLiveConn(){
		$this->cargoTrackSwiftPacLiveDb->close();
		
	}

	public function var_error_log( $object=null ){
		ob_start();                    // start buffer capture
		var_dump( $object );           // dump the values
		$contents = ob_get_contents(); // put the buffer into a variable
		ob_end_clean();                // end capture
		error_log( $contents,3,"switpac_api_log.log" );        // log contents of the result of var_dump( $object )
	}
	
	public function print_error_log ($object = null){
		error_log(print_r($object,true),3,"switpac_api_log.log");
	}
	
	public function arrayToObject($array) {
		if (! is_array ( $array )) {
			return $array;
		}
	
		$object = new \stdClass ();
		if (is_array ( $array ) && count ( $array ) >= 0) {
			foreach ( $array as $name => $value ) {
				$name = strtolower ( trim ( $name ) );
				if (! empty ( $name )) {
					$object->$name = $this->arrayToObject ( $value );
				}
			}
			return $object;
		} else {
			return FALSE;
		}
	}
}

?>