<?php
require_once('lib/fedex/WebServices/library/fedex-common.php5');

use lib\IABOL\Client;
use lib\IABOL\DataTypes\AbolRate as AbolRate;
use lib\IABOL\DataTypes\Package as ABOLPackage;
use lib\IABOL\DataTypes\Packages as ABOLPackages;
use lib\IABOL\DataTypes\Shipment as ABOLShipment;
use lib\IABOL\DataTypes\Shipmentlist as ABOLShipmentList;
use lib\IABOL\DataTypes as ABOLDataTypes;
use lib\NonceUtil\NonceUtil;
use lib\SwiftPac\DataTypes\Fee;
use lib\SwiftPac\DataTypes\FeeDetails;
use lib\SwiftPac\DataTypes\Service;
use lib\SwiftPac\DataTypes\ShipmentCharges;
use lib\SwiftPac\DataTypes as SwiftPacDataTypes;
use lib\SwiftPac\DataTypes\ErrorDetails;
use lib\SwiftPac\DataTypes\Error;
use lib\SwiftPac\db;
use lib\SwiftPac\SwiftPacInHouseRates;
use lib\SwiftPac\DataTypes\TransitTime;
use lib\DataTypes\InternationalRateDeliveryType;
use lib\DataTypes\Shipment;
use lib\DataTypes\Address;
use lib\DataTypes\SwiftPacRate;
use lib\SwiftPac\DataTypes\DomesticRateDeliveryType;
use lib\SwiftPac\DataTypes\SwiftPacShipmentResponse;
use lib\SwiftPac\DataTypes\ShipResult;
use lib\SwiftPac\DataTypes\PieceList;
use lib\SwiftPac\DataTypes\Piece;
use lib\SwiftPac\DataTypes\SwiftPacRateRateResponse;

define ( 'NONCE_SECRET', 'jvTGophIQ108Pqw9Hej' );

spl_autoload_extensions ( '.php' );
spl_autoload_register ();

// spl_autoload_register(function ($class) {
// include strtolower(__DIR__. '\/'. $class . '.php');
// });

/*
 * Response Classes
 */
// ===================API Response Fucntions============================
// class SwiftPacRateRateResponse {
// 	/**
// 	 *
// 	 * @var RateResponse
// 	 */
// 	public $rateResponse = null;
// }
// class RateResponse {
	
// 	/**
// 	 *
// 	 * @var RateResult
// 	 */
// 	public $rateResult = null;
	
// 	/**
// 	 *
// 	 * @var Error
// 	 */
// 	public $errors = array ();
// }

// ==================End of API Response Functions=======================
class SwiftPacShippingApi {
	
	/**
	 * A list of valid service codes
	 * @var array
	 */
	const SERVICE_CODES = array (
			
			'7900','7901','7902','7903','7904','7905','7906','7907','7908','7909','7910',
			'7911','7912','7913','7914','7915','7916','7917','7918','7919','7920','7921',
			'7922','7923','7924','7925',
	);
	
	
	/**
	 * Determines if a user will be authenticated
	 *
	 * @var boolean
	 */
	protected $isAuthenticatedVal = false;
	
	/**
	 * Determines if the soap client making the request has the Client Header attached.
	 *
	 * @var boolean
	 */
	protected $isAvailClientHeaderVal = false;
	
	/**
	 * Determines if a user is allowed to make a request from the originating domain.
	 *
	 * @var boolean
	 */
	protected $isForbiddenVal;
	
	/**
	 * Determines if nonce returning from client is a valid one.
	 *
	 * @var boolean
	 */
	protected $isValidNonceVal;
	
	/**
	 * Contains information about a user retrieved from the database.
	 *
	 * @var array
	 */
	protected $userData;
	
	/**
	 * Contains a list of domains associated to a user from the database.
	 *
	 * @var array
	 */
	protected $userDomains;
	
	/**
	 * Provides a database connection to the database.
	 *
	 * @var mysqli
	 */
	protected $swiftPacApiDb;
	
	/**
	 * Determines if a US City and/or State is valid.
	 */
	protected $isValidUSCityStateVal = true;
	
	/**
	 * Determines if a non-us country code is valid
	 */
	protected $isValidNonUSCountryCodeVal = true;
	/**
	 * Determines if rates are available from a vaild SwiftPac origin.
	 */
	protected $isRateAvailable = true;
	
	/**
	 * Determine if there is no rate service from a the chosen country of origin.
	 */
	protected $noServiceFromOrigin  = true;
	
	/**
	 * Determines if a the customer has submitted a specific service code 
	 * 
	 * @var boolean
	 */
	protected $isServiceCodeAvailable = false;
	
	/**
	 * Determines if a service code submitted is valid.
	 * 
	 * @var boolean
	 */
	protected $isValidServiceCodeVal = true;
	
	/**
	 * Contains the shipment data object submitted 
	 * @var \lib\SwiftPac\DataTypes\Shipment
	 */
	
	protected $shipment;
	
	/**
	 * Contains the SwiftPac calculated InHouse rates
	 * 
	 * @var SwiftPacInHouseRates
	 */
	
	protected $swiftPacInHouseCharges;
	
	
	function __construct() {
		
		$this->swiftPacApiDb = new db (); // initiates connection to the API users/domain database
		$swiftPacDataTypes = new SwiftPacDataTypes (); // instantiates swiftpac datatypes to be used in API. This must be instantiated first before using SwiftPacDatatypes
		$abolDataTypes = new ABOLDataTypes (); // insanitates ABOL datatypes to uses in in API. THis must be instantiated first before using ABOL Datatypes
	}
	
	// ==============API Interface Request Functions================================
	/**
	 * Calculate and return all rates from SwiftPac and other carriers.
	 *
	 * @param SwiftPacRate $params        	
	 * @return mixed|boolean|unknown|SwiftPacRateRateResponse
	 */
	function SwiftPacRate($params) {
		
		$this->shipment = new \lib\SwiftPac\DataTypes\Shipment();
// 		$shipment->packageList->packages[0]->dimensions->
		$this->shipment = $params->rateRequest->rate->requestDetail->shipmentList->shipment;
		$shipToCountryCode = strtoupper($this->shipment->shipTo->countryCode);
		$shipFromCountryCode = strtoupper($this->shipment->shipFrom->countryCode);
		$shipFromZip = $this->shipment->shipFrom->zip;
		
		
		$swiftPacRateResponse = new SwiftPacRateRateResponse ();
		
		$respErrorObj = $this->returnErrorResponse($swiftPacRateResponse->rateResponse->errors, $this->shipment);
		if ($respErrorObj != null){
			$swiftPacRateResponse->rateResponse->errors = $respErrorObj;
			return $swiftPacRateResponse;
		}
		else {	
		// ================Listing SwiftPac Inhouse Rates=================
		
			$this->swiftPacInHouseCharges = new SwiftPacInHouseRates ( $params );
			If ($this->isSwiftPacLocation($shipFromCountryCode) && $this->isSwiftPacLocation($shipToCountryCode)){//Countries were service originates and terminates
				
				if ($this->isUSCountryCode($shipFromCountryCode)){// Countries where Mailbox, Ocean, Air, SmallPackages, Express originates
					
					//=================Display Domestic Services From US====================
					if ($this->isUSCountryCode($shipToCountryCode)){//Us Domestic Services
						
						//=======================Display SwiftPac Domestic==============
						if ($this->isServiceCodeAvailable){
							if ($requestedServiceCode == '7900' || $requestedServiceCode == '7901'){
								$swiftpacDomestic = $this->swiftPacInHouseCharges->getDomesticCharges();
							}
							else $swiftpacDomestic = null;
						}else{
							$swiftpacDomestic = $this->swiftPacInHouseCharges->getDomesticCharges();
						}
						if ($swiftpacDomestic){
								
							if(($requestedServiceCode == "7900" && isset($swiftpacDomestic["totalDomesticStdShipmentCharges"])) || $this->isServiceCodeAvailable == false){
								$domesticStdFreightCharges = $swiftpacDomestic['totalDomesticStdShipmentCharges'];
								$domesticStdService = new Service ();
								$domesticStdShipmentCharges = new ShipmentCharges ();
								$domesticStdService->carrier = "SwiftPac";
								$domesticStdService->serviceCode = "7900";
								$domesticStdService->serviceDesc = "SwiftPac Domestic Standard Freight";
								$domesticStdService->transitTime = TransitTime::DOMESTIC;
									
								$domesticStdShipmentCharges->rate = $domesticStdFreightCharges->rate;
								$domesticStdShipmentCharges->baseRate = $domesticStdFreightCharges->baseRate;
									
								foreach ( $domesticStdFreightCharges->fees as $feeName => $feeVal ) {
						
									$domesticStdFee = new Fee ();
									$domesticStdFeeDetail = new FeeDetails ();
									$domesticStdFeeDetail->name = $feeName;
									$domesticStdFeeDetail->charge = $feeVal;
									$domesticStdFee->feeDetails = $domesticStdFeeDetail;
									$domesticStdShipmentCharges->fees [] = $domesticStdFee;
								}
									
								$domesticStdService->shipmentCharges = $domesticStdShipmentCharges;							
								$swiftPacRateResponse->rateResponse->rateResult->serviceList [] = $domesticStdService;
							}
							if (($requestedServiceCode == "7901" && isset($swiftpacDomestic["totalDomesticExpediteShipmentCharges"])) || $this->isServiceCodeAvailable == false){
								$domesticExpediteFreightCharges = $swiftpacDomestic['totalDomesticExpediteShipmentCharges'];
								$domesticExpediteService = new Service ();
								$domesticExpediteShipmentCharges = new ShipmentCharges ();
								$domesticExpediteService->carrier = "SwiftPac";
								$domesticExpediteService->serviceCode = "7901";
								$domesticExpediteService->serviceDesc = "SwiftPac Domestic Expedite Freight";
								$domesticExpediteService->transitTime = TransitTime::DOMESTIC_EXPEDITED;
								
								$domesticExpediteShipmentCharges->rate = $domesticExpediteFreightCharges->rate;
								$domesticExpediteShipmentCharges->baseRate = $domesticExpediteFreightCharges->baseRate;
									
								foreach ( $domesticExpediteFreightCharges->fees as $feeName => $feeVal ) {
						
									$domesticExpediteFee = new Fee ();
									$domesticExpediteFeeDetail = new FeeDetails ();
									$domesticExpediteFeeDetail->name = $feeName;
									$domesticExpediteFeeDetail->charge = $feeVal;
									$domesticExpediteFee->feeDetails = $domesticExpediteFeeDetail;
									$domesticExpediteShipmentCharges->fees [] = $domesticExpediteFee;
								}
									
								$domesticExpediteService->shipmentCharges = $domesticExpediteShipmentCharges;							
								$swiftPacRateResponse->rateResponse->rateResult->serviceList [] = $domesticExpediteService;
							}
						}
						//=======================End of SwiftPac Domestic==============
						
						//======================Display USPS Rates======================
						$uspsRates = $this->getUSPSRates ();
						if ($this->isServiceCodeAvailable){
							if ($requestedServiceCode == '7902') {
								$uspsRates = array(0 =>$uspsRates[0]);
							}
							else if ($requestedServiceCode == '7903'){
								$uspsRates = array(1 => $uspsRates[1]);
								
							}
							else $uspsRates = null;
						}
						foreach ( $uspsRates as $key => $uspsRate ) {
							$uspsService = new Service ();
							$uspsShipmentCharges = new ShipmentCharges ();
							$uspsFee = new Fee ();
							$uspsFeeDetail = new FeeDetails ();
							$serviceCode = $prostarRate->ServiceCode;
							$uspsService->carrier = "SwiftPac";
							$uspsService->estimatedDeliveryDate = $uspsRate->estimatedDeliveryDate;
// 							$uspsService->estimatedDeliveryDate = $uspsRate ['COMMITMENTDATE'];
							$uspsService->transitTime = $uspsRate->transitTime;
// 							$uspsService->transitTime = $uspsRate ['TRANSITTIME'];
							$uspsShipmentCharges->rate = $uspsRate->rate;
						
							$uspsShipmentCharges->baseRate = $uspsRate->rate;
// 							$uspsShipmentCharges->baseRate = $uspsRate ['RATE'];
							$uspsService->shipmentCharges = $uspsShipmentCharges;
							switch ($key) {
								case 0 :
									$uspsService->serviceCode = "7902";
									$uspsService->serviceDesc = "USPS Priority Mail";
									break;
								case 1 :
									$uspsService->serviceCode = "7903";
									$uspsService->serviceDesc = "USPS Priority Mail Express";
									break;
							}
						
							$swiftPacRateResponse->rateResponse->rateResult->serviceList [] = $uspsService;
							
						}
						//=======================End of USPS==================================
						//========================Display FedEx Ground========================
						if ($this->isServiceCodeAvailable){
							if ($requestedServiceCode == '7916'){
								$fedexGrndCharges =$this->getFedexRates(DomesticRateDeliveryType::FEDEX_GROUND);
							}
							else $fedexGrndCharges = null;
						}else{
							$fedexGrndCharges =$this->getFedexRates(DomesticRateDeliveryType::FEDEX_GROUND);
								
							if ($fedexGrndCharges) {
									
								$fedexGrndService = new Service ();
								$fedexGrndShipmentCharges = new ShipmentCharges ();
								$fedexGrndService->carrier = "SwiftPac";
								$fedexGrndService->serviceCode = "7916";
								$fedexGrndService->serviceDesc = "FedEx Ground";
									
								$fedexGrndShipmentCharges->rate = $fedexGrndCharges->rate;
								$fedexGrndShipmentCharges->baseRate = $fedexGrndCharges->baseRate;
								$fedexGrndService->estimatedDeliveryDate = $fedexGrndCharges->estimatedDeliveryDate;
								$fedexGrndService->transitTime = $fedexGrndCharges->transitTime;
									
								foreach ( $fedexGrndCharges->fees as $feeName => $feeVal ) {
										
									$fedexGrndFee = new Fee ();
									$fedexGrndFeeDetail = new FeeDetails ();
									$fedexGrndFeeDetail->name = $feeName;
									$fedexGrndFeeDetail->charge = $feeVal;
									$fedexGrndFee->feeDetails = $fedexGrndFeeDetail;
									$fedexGrndShipmentCharges->fees [] = $fedexGrndFee;
								}
								$fedexGrndService->shipmentCharges = $fedexGrndShipmentCharges;
									
								$swiftPacRateResponse->rateResponse->rateResult->serviceList [] = $fedexGrndService;
							}
						}
						//========================End of FedEx Ground========================
					}
					//============================End of domestic Services Display========================
					//===========================Display International Services From US==========================
					else {
						
						//=======================Display International Express ===================
						if ($this->isServiceCodeAvailable){
							if ($requestedServiceCode == '7904'){
								$expressCharges = $this->swiftPacInHouseCharges->getExpressCharges();
							}
							else $expressCharges = null;
						}else{
							$expressCharges = $this->swiftPacInHouseCharges->getExpressCharges();
						}
						if ($expressCharges) {
								
							$expressService = new Service ();
							$expressShipmentCharges = new ShipmentCharges ();
							$expressService->carrier = "SwiftPac";
							$expressService->serviceCode = "7904";
							$expressService->serviceDesc = "SwiftPac Express";
								
							$expressShipmentCharges->rate = $expressCharges->rate;
							$expressShipmentCharges->baseRate = $expressCharges->baseRate;
							$nextShipmentDateTransitTime = $this->getNextShipmentDate($shipFromZip, $shipToCountryCode, "ItnlExpress");
							$expressService->estimatedDeliveryDate = $nextShipmentDateTransitTime['estDelDate'];
							$expressService->transitTime = $nextShipmentDateTransitTime['transitTime'];
								
							foreach ( $expressCharges->fees as $feeName => $feeVal ) {
						
								$expressFee = new Fee ();
								$expressFeeDetail = new FeeDetails ();
								$expressFeeDetail->name = $feeName;
								$expressFeeDetail->charge = $feeVal;
								$expressFee->feeDetails = $expressFeeDetail;
								$expressShipmentCharges->fees [] = $expressFee;
							}
							$expressService->shipmentCharges = $expressShipmentCharges;
							
							$swiftPacRateResponse->rateResponse->rateResult->serviceList [] = $expressService;
						}
						//=====================End of Express=========================
						
						//=================Display Air Freight========================
// 						if ($this->isServiceCodeAvailable){
// 							if ($requestedServiceCode == '79055'){
// 								$airServiceCharges = $swiftPacInHouseCharges->getAirServiceCharges();
// 							}
// 							else $airServiceCharges = null;
// 						}else{
// 							$airServiceCharges = $swiftPacInHouseCharges->getAirServiceCharges();
// 						}
// 						if ($airServiceCharges){
								
// 							$airServiceService = new Service ();
// 							$airServiceShipmentCharges = new ShipmentCharges ();
// 							$airServiceService->carrier = "SwiftPac";
// 							$airServiceService->serviceCode = "7905";
// 							$airServiceService->serviceDesc = "SwiftPac Air Service Freight";
// 							$nextShipmentDateTransitTime = $this->getNextShipmentDate($shipFromZip,$shipToCountryCode,"Air");
// 							$airServiceService->estimatedDeliveryDate = $nextShipmentDateTransitTime['estDelDate'];;
// 							$airServiceService->transitTime = $nextShipmentDateTransitTime['transitTime'];
							
// 							$airServiceShipmentCharges->rate = $airServiceCharges->rate;
// 							$airServiceShipmentCharges->baseRate = $airServiceCharges->baseRate;
						
// 							foreach ( $airServiceCharges->fees as $feeName => $feeVal ) {
									
// 								$airServiceFee = new Fee ();
// 								$airServiceFeeDetail = new FeeDetails ();
// 								$airServiceFeeDetail->name = $feeName;
// 								$airServiceFeeDetail->charge = $feeVal;
// 								$airServiceFee->feeDetails = $airServiceFeeDetail;
// 								$airServiceShipmentCharges->fees [] = $airServiceFee;
// 							}
						
// 							$airServiceService->shipmentCharges = $airServiceShipmentCharges;
// 							$swiftPacRateResponse->rateResponse->rateResult->serviceList [] = $airServiceService;
								
// 						}
						//=====================End of Air Freight===========================
						//=================Display Air Freight========================
						if ($this->isServiceCodeAvailable){
							if ($requestedServiceCode == '7905'){
								$airCharges = $this->swiftPacInHouseCharges->getAirServiceCharges();
							}
							else $airCharges = null;
						}else{
							$airCharges = $this->swiftPacInHouseCharges->getAirServiceCharges();
						}
						if ($airCharges){
								
							$airService = new Service ();
							$airShipmentCharges = new ShipmentCharges ();
							$airService->carrier = "SwiftPac";
							$airService->serviceCode = "7905";
							$airService->serviceDesc = "SwiftPac Air Freight";
							$nextShipmentDateTransitTime = $this->getNextShipmentDate($shipFromZip,$shipToCountryCode,"Air");
							$airService->estimatedDeliveryDate = $nextShipmentDateTransitTime['estDelDate'];;
							$airService->transitTime = $nextShipmentDateTransitTime['transitTime'];
							
							$airShipmentCharges->rate = $airCharges->rate;
							$airShipmentCharges->baseRate = $airCharges->baseRate;
						
							foreach ( $airCharges->fees as $feeName => $feeVal ) {
									
								$airFee = new Fee ();
								$airFeeDetail = new FeeDetails ();
								$airFeeDetail->name = $feeName;
								$airFeeDetail->charge = $feeVal;
								$airFee->feeDetails = $airFeeDetail;
								$airShipmentCharges->fees [] = $airFee;
							}
						
							$airService->shipmentCharges = $airShipmentCharges;
							$swiftPacRateResponse->rateResponse->rateResult->serviceList [] = $airService;
								
						}
						//=====================End of Air Freight===========================
						//=====================Display ProStar Charges====================
						if ($this->isServiceCodeAvailable){
															
							switch ($requestedServiceCode){
								
								case "7906":
									$params->rateRequest->rate->requestDetail->shipmentList->shipment->serviceCode = "4452";
								break;
								case "7907":
									$params->rateRequest->rate->requestDetail->shipmentList->shipment->serviceCode = "4454";
								break;
								case "7908":
									$params->rateRequest->rate->requestDetail->shipmentList->shipment->serviceCode = "4455";
								break;									
							}
							$prostarRates = $this->getProStarRates ();
						}else{
							$prostarRates = $this->getProStarRates ();
						}
						if ($prostarRates){
							foreach ( $prostarRates as $prostarRate ) {
								$proStarService = new Service ();
								$proStarShipmentCharges = new ShipmentCharges ();
								$proStarFee = new Fee ();
								$proStarFeeDetail = new FeeDetails ();
									
								$serviceCode = $prostarRate->ServiceCode;
									
								$proStarService->carrier = "SwiftPac";
								$proStarService->estimatedDeliveryDate = substr($prostarRate->DeliveryDate,0,10);
								$proStarService->estiamtedDeliveryTime = $prostarRate->DeliveryTime;
								$proStarService->guaranteedService = $prostarRate->GuaranteedService;
								$proStarShipmentCharges->rate = $prostarRate->ShipmentCharges->Rate;
								$proStarShipmentCharges->baseRate = $prostarRate->ShipmentCharges->BaseRate;
								$proStarFeeDetail->name = $prostarRate->ShipmentCharges->Fees->Fee->FeeDetail->name;
								$proStarFeeDetail->charge = $prostarRate->ShipmentCharges->Fees->Fee->FeeDetail->charge;
								$proStarFee->feeDetails = $proStarFeeDetail;
								$proStarShipmentCharges->fees [] = $proStarFee;
								$proStarService->shipmentCharges = $proStarShipmentCharges;
								switch ($serviceCode) {
							
									case '4452' :
										$proStarService->serviceDesc = "DHL Express Envelope";
										$proStarService->serviceCode = "7906";
										break;
									case '4454' :
										$proStarService->serviceDesc = "DHL Express WorldWide (WPX, Non Doc)";
										$proStarService->serviceCode = "7907";
										break;
									case '4455' :
										$proStarService->serviceDesc = "DHL WorldWide (WPX, Doc)";
										$proStarService->serviceCode = "7908";
										break;
								}								
								$swiftPacRateResponse->rateResponse->rateResult->serviceList [] = $proStarService;
							}
						}
						//=====================End of Prostar ==============================
						
						//=====================Display FedEx Internationanl================
						if ($this->isServiceCodeAvailable){
							if ($requestedServiceCode == '7917'){
								$fedexIntlPriCharges =$this->getFedexRates(\lib\SwiftPac\DataTypes\InternationalRateDeliveryType::FEDEX_INTERNATIONAL_PRIORITY);
							}
							else $fedexIntlPriCharges = null;
						}else{
							$fedexIntlPriCharges =$this->getFedexRates(\lib\SwiftPac\DataTypes\InternationalRateDeliveryType::FEDEX_INTERNATIONAL_PRIORITY);
							
							if ($fedexIntlPriCharges) {
							
								$fedexIntlPriService = new Service ();
								$fedexIntlPriShipmentCharges = new ShipmentCharges ();
								$fedexIntlPriService->carrier = "SwiftPac";
								$fedexIntlPriService->serviceCode = "7917";
								$fedexIntlPriService->serviceDesc = "FedEx International Priority";
							
								$fedexIntlPriShipmentCharges->rate = $fedexIntlPriCharges->rate;
								$fedexIntlPriShipmentCharges->baseRate = $fedexIntlPriCharges->baseRate;
								$fedexIntlPriService->estimatedDeliveryDate = $fedexIntlPriCharges->estimatedDeliveryDate;
								$fedexIntlPriService->transitTime = $fedexIntlPriCharges->transitTime;
							
								foreach ( $fedexIntlPriCharges->fees as $feeName => $feeVal ) {
							
									$fedexIntlPriFee = new Fee ();
									$fedexIntlPriFeeDetail = new FeeDetails ();
									$fedexIntlPriFeeDetail->name = $feeName;
									$fedexIntlPriFeeDetail->charge = $feeVal;
									$fedexIntlPriFee->feeDetails = $fedexIntlPriFeeDetail;
									$fedexIntlPriShipmentCharges->fees [] = $fedexIntlPriFee;
								}
								$fedexIntlPriService->shipmentCharges = $fedexIntlPriShipmentCharges;
									
								$swiftPacRateResponse->rateResponse->rateResult->serviceList [] = $fedexIntlPriService;
							}
						}
						
						//=====================End of FedEx Internaional================================
						
						//========================Display SwiftPac Intl Expedite=======================
						if ($this->isServiceCodeAvailable){
							if ($requestedServiceCode == '7918'){
								$intlExpediteCharges =$this->getSwiftPacIntlExp();
							}
							else $intlExpediteCharges = null;
						}else{
							$intlExpediteCharges =$this->getSwiftPacIntlExp();
						
							if ($intlExpediteCharges) {
									
								$intlExpediteService = new Service ();
								$intlExpediteShipmentCharges = new ShipmentCharges ();
								$intlExpediteService->carrier = "SwiftPac";
								$intlExpediteService->serviceCode = "7918";
								$intlExpediteService->serviceDesc = "Swiftpac International Expedite";
									
								$intlExpediteShipmentCharges->rate = $intlExpediteCharges->rate;
								$intlExpediteShipmentCharges->baseRate = $intlExpediteCharges->baseRate;
								$intlExpediteService->estimatedDeliveryDate = $intlExpediteCharges->estimatedDeliveryDate;
								$intlExpediteService->transitTime = $intlExpediteCharges->transitTime;
									
								foreach ( $intlExpediteCharges->fees as $feeName => $feeVal ) {
						
									$intlExpediteFee = new Fee ();
									$intlExpediteFeeDetail = new FeeDetails ();
									$intlExpediteFeeDetail->name = $feeName;
									$intlExpediteFeeDetail->charge = $feeVal;
									$intlExpediteFee->feeDetails = $intlExpediteFeeDetail;
									$intlExpediteShipmentCharges->fees [] = $intlExpediteFee;
								}
								$intlExpediteService->shipmentCharges = $intlExpediteShipmentCharges;
									
								$swiftPacRateResponse->rateResponse->rateResult->serviceList [] = $intlExpediteService;
							}
						}
						//========================End of SwiftPac Intl Expedite========================
						
						//========================Display Swiftpac Intl Reg=======================
						if ($this->isServiceCodeAvailable){
							if ($requestedServiceCode == '7918'){
								$intlRegCharges =$this->getSwiftPacIntlReg();
							}
							else $intlRegCharges = null;
						}else{
							$intlRegCharges =$this->getSwiftPacIntlReg();
						
							if ($intlRegCharges) {
									
								$intlRegService = new Service ();
								$intlRegShipmentCharges = new ShipmentCharges ();
								$intlRegService->carrier = "SwiftPac";
								$intlRegService->serviceCode = "7919";
								$intlRegService->serviceDesc = "Swiftpac International Regular";
									
								$intlRegShipmentCharges->rate = $intlRegCharges->rate;
								$intlRegShipmentCharges->baseRate = $intlRegCharges->baseRate;
								$intlRegService->estimatedDeliveryDate = $intlRegCharges->estimatedDeliveryDate;
								$intlRegService->transitTime = $intlRegCharges->transitTime;
									
								foreach ( $intlRegCharges->fees as $feeName => $feeVal ) {
						
									$intlRegFee = new Fee ();
									$intlRegFeeDetail = new FeeDetails ();
									$intlRegFeeDetail->name = $feeName;
									$intlRegFeeDetail->charge = $feeVal;
									$intlRegFee->feeDetails = $intlRegFeeDetail;
									$intlRegShipmentCharges->fees [] = $intlRegFee;
								}
								$intlRegService->shipmentCharges = $intlRegShipmentCharges;
									
								$swiftPacRateResponse->rateResponse->rateResult->serviceList [] = $intlRegService;
							}
						}
						//========================End of Swiftpac Intl Reg====================================
						
						//===================Display Air Service Charges======================================
						if ($this->isPartOfAirRoute($shipToCountryCode)){//Countries where AirCargo / Courier terminates
							
							if ($this->isServiceCodeAvailable){
								if ($requestedServiceCode == '7909'){
									$airCharges = $this->swiftPacInHouseCharges->getMailBoxCharges();
								}
								else $airCharges = null;
							}else{
								$airCharges = $this->swiftPacInHouseCharges->getMailBoxCharges();
							}
							if ($airCharges) {
								$airService = new Service ();
								$airShipmentCharges = new ShipmentCharges ();
								
								$airService->carrier = "SwiftPac";
								$airService->serviceCode = "7909";
								$airService->serviceDesc = "SwiftPac Air Freight";
								$airService->transitTime = TransitTime::US_MAIL_BOX;
								$nextShipmentDateTransitTime = $this->getNextShipmentDate($shipFromZip,$shipToCountryCode,"Mailbox");
								$airService->estimatedDeliveryDate = $nextShipmentDateTransitTime['estDelDate'];
								$airService->transitTime = $nextShipmentDateTransitTime['transitTime'];								
								
								$airShipmentCharges->rate = $airCharges->rate;
								$airShipmentCharges->baseRate = $airCharges->baseRate;
									
								foreach ( $airCharges->fees as $feeName => $feeVal ) {
										
									$airFee = new Fee ();
									$airFeeDetail = new FeeDetails ();
									$airFeeDetail->name = $feeName;
									$airFeeDetail->charge = $feeVal;
									$airFee->feeDetails = $airFeeDetail;
									$airShipmentCharges->fees [] = $airFee;
								}
								$airService->shipmentCharges = $airShipmentCharges;
								$swiftPacRateResponse->rateResponse->rateResult->serviceList [] = $airService;
									
							}
							//==================End of MailBox Charges==========================
								
							//===================Display Small Package Charges==================
// 							if ($this->isServiceCodeAvailable){
// 								if ($requestedServiceCode == '7910'){
// 									$smallPackageCharges = $swiftPacInHouseCharges->getSmallPackageCharges();
// 								}
// 								else $smallPackageCharges = null;
// 							}else{
// 								$smallPackageCharges = $swiftPacInHouseCharges->getSmallPackageCharges();
// 							}
// 							if ($smallPackageCharges) {
// 								$smallPackgeService = new Service ();
// 								$smallPackageShipmentCharges = new ShipmentCharges ();
// 								$smallPackgeService->carrier = "SwiftPac";
// 								$smallPackgeService->serviceCode = "7910";
// 								$smallPackgeService->serviceDesc = "SwiftPac Small Package";
								
// 								$nextShipmentDateTransitTime = $this->getNextShipmentDate($shipFromZip, $shipToCountryCode, "smallPkg");
								
// 								$smallPackgeService->estimatedDeliveryDate = $nextShipmentDateTransitTime['estDelDate'];
// 								$smallPackgeService->transitTime = $nextShipmentDateTransitTime['transitTime'];
									
// 								$smallPackageShipmentCharges->rate = $smallPackageCharges->rate;
// 								$smallPackageShipmentCharges->baseRate = $smallPackageCharges->baseRate;
									
// 								foreach ( $smallPackageCharges->fees as $feeName => $feeVal ) {
										
// 									$smallPackageFee = new Fee ();
// 									$smallPackageFeeDetail = new FeeDetails ();
// 									$smallPackageFeeDetail->name = $feeName;
// 									$smallPackageFeeDetail->charge = $feeVal;
// 									$smallPackageFee->feeDetails = $smallPackageFeeDetail;
// 									$smallPackageShipmentCharges->fees [] = $smallPackageFee;
// 								}
// 								$smallPackgeService->shipmentCharges = $smallPackageShipmentCharges;
// 								$swiftPacRateResponse->rateResponse->rateResult->serviceList [] = $smallPackgeService;
									
// 							}
							//==================End of Small Package Charges=======================
						}
							//======================Display Ocean Charges========================
						if ($this->isPartOfOceanRoute($shipToCountryCode)){//Countries where Ocean terminates
							
							if ($this->isServiceCodeAvailable){
								if ($requestedServiceCode == '7911' || $requestedServiceCode == '7912'|| $requestedServiceCode == '7913' ){
									$oceanCharges = $this->swiftPacInHouseCharges->getOceanCharges();
								}
								else $oceanCharges = null;
							}else{
								$oceanCharges = $this->swiftPacInHouseCharges->getOceanCharges();
							}
							$oceanCharges = $this->swiftPacInHouseCharges->getOceanCharges();
							if ($oceanCharges) {
									
								if ($oceanCharges['totalOceanShipmentCharges']){
							
									$oceanFreightCharges = $oceanCharges['totalOceanShipmentCharges'];
									$oceanService = new Service ();
									$oceanShipmentCharges = new ShipmentCharges ();
									$oceanService->carrier = "SwiftPac";
									$oceanService->serviceCode = "7911";
									$oceanService->serviceDesc = "SwiftPac Ocean Freight";
									$nextShipmentDateTransitTime = $this->getNextShipmentDate($shipFromZip,$shipToCountryCode,"Ocean");
									$oceanService->estimatedDeliveryDate = $nextShipmentDateTransitTime['estDelDate'];
									$oceanService->transitTime = $nextShipmentDateTransitTime['transitTime'];
										
									$oceanShipmentCharges->rate = $oceanFreightCharges->rate;
									$oceanShipmentCharges->baseRate = $oceanFreightCharges->baseRate;
										
									foreach ( $oceanFreightCharges->fees as $feeName => $feeVal ) {
							
										$oceanFee = new Fee ();
										$oceanFeeDetail = new FeeDetails ();
										$oceanFeeDetail->name = $feeName;
										$oceanFeeDetail->charge = $feeVal;
										$oceanFee->feeDetails = $oceanFeeDetail;
										$oceanShipmentCharges->fees [] = $oceanFee;
									}
										
									$oceanService->shipmentCharges = $oceanShipmentCharges;
									
									$swiftPacRateResponse->rateResponse->rateResult->serviceList [] = $oceanService;
	
								}
								else {
									
									if (($requestedServiceCode == "7912" && isset($oceanCharges['totalFullServiceBarrelOceanShipmentCharges'])) || $this->isServiceCodeAvailable == false ){
											
										$barrelFullServiceoceanCharges = $oceanCharges['totalFullServiceBarrelOceanShipmentCharges'];
										$oceanBarrelFullService = new Service ();
										$oceanBarrelFullServiceShipmentCharges = new ShipmentCharges ();
										$oceanBarrelFullService->carrier = "SwiftPac";
										$oceanBarrelFullService->serviceCode = "7912";
										$oceanBarrelFullService->serviceDesc = "SwiftPac Ocean Barrel Full Service";
										$nextShipmentDateTransitTime = $this->getNextShipmentDate($shipFromZip,$shipToCountryCode,"Ocean");
										$oceanBarrelFullService->estimatedDeliveryDate = $nextShipmentDateTransitTime['estDelDate'];
										$oceanBarrelFullService->transitTime = $nextShipmentDateTransitTime['transitTime'];
										
							
										$oceanBarrelFullServiceShipmentCharges->rate = $barrelFullServiceoceanCharges->rate;
										$oceanBarrelFullServiceShipmentCharges->baseRate = $barrelFullServiceoceanCharges->baseRate;
							
										foreach ( $barrelFullServiceoceanCharges->fees as $feeName => $feeVal ) {
												
											$oceanBarrelFullServiceFee = new Fee ();
											$oceanBarrelFullServiceFeeDetail = new FeeDetails ();
											$oceanBarrelFullServiceFeeDetail->name = $feeName;
											$oceanBarrelFullServiceFeeDetail->charge = $feeVal;
											$oceanBarrelFullServiceFee->feeDetails = $oceanBarrelFullServiceFeeDetail;
											$oceanBarrelFullServiceShipmentCharges->fees [] = $oceanBarrelFullServiceFee;
										}
							
										$oceanBarrelFullService->shipmentCharges = $oceanBarrelFullServiceShipmentCharges;
										
										$swiftPacRateResponse->rateResponse->rateResult->serviceList [] = $oceanBarrelFullService;
										
									}
									if (($requestedServiceCode == "7913" && isset($oceanCharges['totalBarrelOceanShipmentCharges'])) || $this->isServiceCodeAvailable == false){
											
										$barrelOceanShipmentCharges = $oceanCharges['totalBarrelOceanShipmentCharges'];
										$oceanBarrel = new Service ();
										$oceanBarrelShipmentCharges = new ShipmentCharges ();
										$oceanBarrel->carrier = "SwiftPac";
										$oceanBarrel->serviceCode = "7913";
										$oceanBarrel->serviceDesc = "SwiftPac Ocean Barrel Service";
										$nextShipmentDateTransitTime = $this->getNextShipmentDate($shipFromZip,$shipToCountryCode,"Ocean");
										$oceanBarrel->estimatedDeliveryDate = $nextShipmentDateTransitTime['estDelDate'];
										$oceanBarrel->transitTime = $nextShipmentDateTransitTime['transitTime'];
							
										$oceanBarrelShipmentCharges->rate = $barrelOceanShipmentCharges->rate;
										$oceanBarrelShipmentCharges->baseRate = $barrelOceanShipmentCharges->baseRate;
							
										foreach ( $barrelOceanShipmentCharges->fees as $feeName => $feeVal ) {
												
											$oceanBarrelFee = new Fee ();
											$oceanBarrelFeeDetail = new FeeDetails ();
											$oceanBarrelFeeDetail->name = $feeName;
											$oceanBarrelFeeDetail->charge = $feeVal;
											$oceanBarrelFee->feeDetails = $oceanBarrelFeeDetail;
											$oceanBarrelShipmentCharges->fees [] = $oceanBarrelFee;
										}
							
										$oceanBarrel->shipmentCharges = $oceanBarrelShipmentCharges;
										
										$swiftPacRateResponse->rateResponse->rateResult->serviceList [] = $oceanBarrel;
										
									}
								}
							}
						}
						//========================End of Ocean Charges========================================
					}
				}
				//=====================Display Inter-Regional Charges originating from the region==================
				else if ($this->isPartOfRegionalRoute($shipFromCountryCode)){//Countries where SwiftPac Regional originates and terminates
					
					//=======================Display Caribbean Express Same Day ===================
					if ($this->isServiceCodeAvailable){
						if ($requestedServiceCode == '7914'){
							$caribbeanExpressSameDayCharges = $this->swiftPacInHouseCharges->getCaribbeanSameDayExpressCharges();
						}
						else $caribbeanExpressSameDayCharges = null;
					}else{
						$caribbeanExpressSameDayCharges = $this->swiftPacInHouseCharges->getCaribbeanSameDayExpressCharges();
					}
					if ($caribbeanExpressSameDayCharges) {
					
						$caribbeanExpressSameDayService = new Service ();
						$caribbeanExpressSameDayShipmentCharges = new ShipmentCharges ();
						$caribbeanExpressSameDayService->carrier = "SwiftPac";
						$caribbeanExpressSameDayService->serviceCode = "7914";
						$caribbeanExpressSameDayService->serviceDesc = "SwiftPac Caribbean Express Same Day";
						$nextShipmentDateTransitTime = $this->getNextShipmentDate($shipFromZip, $shipToCountryCode, "CbeanExpressSameDay");
						$caribbeanExpressSameDayService->estimatedDeliveryDate = $nextShipmentDateTransitTime['estDelDate'];
						$caribbeanExpressSameDayService->transitTime = $nextShipmentDateTransitTime['transitTime'];
						
						$caribbeanExpressSameDayShipmentCharges->rate = $caribbeanExpressSameDayCharges->rate;
						$caribbeanExpressSameDayShipmentCharges->baseRate = $caribbeanExpressSameDayCharges->baseRate;
					
						foreach ( $caribbeanExpressSameDayCharges->fees as $feeName => $feeVal ) {
					
							$caribbeanExpressSameDayFee = new Fee ();
							$caribbeanExpressSameDayFeeDetail = new FeeDetails ();
							$caribbeanExpressSameDayFeeDetail->name = $feeName;
							$caribbeanExpressSameDayFeeDetail->charge = $feeVal;
							$caribbeanExpressSameDayFee->feeDetails = $caribbeanExpressSameDayFeeDetail;
							$caribbeanExpressSameDayShipmentCharges->fees [] = $caribbeanExpressSameDayFee;
						}
						$caribbeanExpressSameDayService->shipmentCharges = $caribbeanExpressSameDayShipmentCharges;
							
						$swiftPacRateResponse->rateResponse->rateResult->serviceList [] = $caribbeanExpressSameDayService;
					}
					//=====================End of Display Caribbean Express Same Day=========================
	
					//=======================Display Caribbean Express Two Day ===================
					if ($this->isServiceCodeAvailable){
						if ($requestedServiceCode == '7915'){
							$caribbeanExpressTwoDayCharges = $this->swiftPacInHouseCharges->getCaribbeanTwoDayExpressCharges();
						}
						else $caribbeanExpressTwoDayCharges = null;
					}else{
						$caribbeanExpressTwoDayCharges = $this->swiftPacInHouseCharges->getCaribbeanTwoDayExpressCharges();
					}
					if ($caribbeanExpressTwoDayCharges) {
					
						$caribbeanExpressTwoDayService = new Service ();
						$caribbeanExpressTwoDayShipmentCharges = new ShipmentCharges ();
						$caribbeanExpressTwoDayService->carrier = "SwiftPac";
						$caribbeanExpressTwoDayService->serviceCode = "7915";
						$caribbeanExpressTwoDayService->serviceDesc = "SwiftPac Caribbean Express Two Day";
						
						$nextShipmentDateTransitTime = $this->getNextShipmentDate($shipFromZip, $shipToCountryCode, "CbeanExpressTwoDay");
						$caribbeanExpressTwoDayService->estimatedDeliveryDate = $nextShipmentDateTransitTime['estDelDate'];
						$caribbeanExpressTwoDayService->transitTime = $nextShipmentDateTransitTime['transitTime'];
					
						$caribbeanExpressTwoDayShipmentCharges->rate = $caribbeanExpressTwoDayCharges->rate;
						$caribbeanExpressTwoDayShipmentCharges->baseRate = $caribbeanExpressTwoDayCharges->baseRate;
					
						foreach ( $caribbeanExpressTwoDayCharges->fees as $feeName => $feeVal ) {
					
							$caribbeanExpressTwoDayFee = new Fee ();
							$caribbeanExpressTwoDayFeeDetail = new FeeDetails ();
							$caribbeanExpressTwoDayFeeDetail->name = $feeName;
							$caribbeanExpressTwoDayFeeDetail->charge = $feeVal;
							$caribbeanExpressTwoDayFee->feeDetails = $caribbeanExpressTwoDayFeeDetail;
							$caribbeanExpressTwoDayShipmentCharges->fees [] = $caribbeanExpressTwoDayFee;
						}
						$caribbeanExpressTwoDayService->shipmentCharges = $caribbeanExpressTwoDayShipmentCharges;
							
						$swiftPacRateResponse->rateResponse->rateResult->serviceList [] = $caribbeanExpressTwoDayService;
					}
					//=====================End of Display Caribbean Express Two Day=========================		
				}
				else {//No service originating from the selected location
						
					$this->isRateAvailable = false;
					$respErrorObj = $this->returnErrorResponse($swiftPacRateResponse->rateResponse->errors,$this->shipment);
					
					$swiftPacRateResponse->rateResponse->errors = $respErrorObj;
					
					return $swiftPacRateResponse;
					
				}
			}
			else {
				
				$swiftPacRateResponse->rateResponse->rateResult->serviceList = null;
				
			}
			// ===================End of Listing SwiftPac Rates===================
			
			
			$this->swiftPacApiDb->closeConn (); // terminates db connection
			
			return $swiftPacRateResponse;
		}
	}
	

	/**
	 * Returns the shipment result a submited shipment request
	 */
	function SwiftPacShipment($params){
		
		$this->shipment = $params->shipmentRequest->requestDetail->shipmentList->shipment;
		$swiftPacShipmentResponse = new SwiftPacShipmentResponse();
		$respErrorObj = $this->returnErrorResponse($swiftPacShipmentResponse->shipmentResponse->errors, $this->shipment);
		if ($respErrorObj != null){
			 $swiftPacShipmentResponse->shipmentResponse->errors = $respErrorObj;
			 return $swiftPacShipmentResponse;
		}
		
		
// 		$this->var_error_log(array("Shipment Request" => $params));
		
		$shipResult = new ShipResult();
		$shipResult->shipmentWeight = $this->shipment->weight;
		
		$pieceList = new PieceList();
		
		$piece = new Piece();
		
		
		$shipResult->shipmentCharge = 200;
		$swiftPacShipmentResponse->shipmentResponse->shipResult = $shipResult;
		return $swiftPacShipmentResponse;
		
	}
	// ===============End of API Interface Request Functions
	
	// ============Soap Header Functions===============
	/**
	 * Captures authentication info to execute MD5 digest validation.
	 *
	 * @param SoapObj $headerInfo        	
	 */
	public function ClientAuth($headerInfo) {
		$this->isAvailClientHeaderVal = true;
		// ==============Verification of Credientials==============
		$authFromClient = $headerInfo->Auth;
		$nonceFromClient = $headerInfo->Nonce;
		$realmFromClient = $headerInfo->Realm;
		$clientUsername = $headerInfo->Username;
		$clientPassword = "";
		
		if (NonceUtil::check ( NONCE_SECRET, $nonceFromClient )) {
			
			$this->isValidNonceVal = true;
			if ($this->getUserData( $clientUsername )) {
				
				$clientPassword = $this->userData->password;
				
				if ($this->isValidRealm ( $realmFromClient, $clientUsername )) {
					
						
					$this->isForbiddenVal = false;
					$secret = md5 ( $clientUsername . ":" . $clientPassword . ":" . $realmFromClient );
					$auth = md5 ( $secret . ":" . $nonceFromClient );
						
					if ($auth == $authFromClient) {
						$this->isAuthenticatedVal = true;
					}
				} else {
					$this->isForbiddenVal = true;
				}
			}
		} else {
			$this->isValidNonceVal = false;
		}
		// =============End Of Verification===================
	}
	// ==============End of Soap Header Functions============
	// ==============Helper Functions================
	/**
	 * Returns to client an error response for a specific client request
	 *
	 * @param unknown $responseObj, $shipmentObj       	
	 * @return mixed (Error, boolean)
	 */
	protected function returnErrorResponse($respErrorObj,$shipmentObj) {
		
		
		$this->var_error_log(array("shipment object" => $shipmentObj));
		$shipToCountryCode = strtoupper($shipmentObj->shipTo->countryCode);
		$shipFromCountryCode = strtoupper($shipmentObj->shipFrom->countryCode);
		$shipFromZip = $shipmentObj->shipFrom->zip;
		
		$requestedServiceCode = $shipmentObj->serviceCode;
		if ($requestedServiceCode) $this->isServiceCodeAvailable = true;
		if ($shipFromCountryCode == "US") $this->isValidUSCityStateVal = $this->isValidUSCityState ( $shipmentObj);
		else $this->isValidNonUSCountryCodeVal = $this->isValidNonUSCountryCode($shipmentObj);
		if ($this->isServiceCodeAvailable)$this->isValidServiceCodeVal = $this->isValidServiceCode($requestedServiceCode);
		
			
		if (! $this->isValidNonceVal && $this->isAvailClientHeaderVal) {
			$authError = new Error ();
			$authErrorDetails = new ErrorDetails ();
			$authErrorDetails->type = "417";
			$authErrorDetails->message = "Expectation Failed: Nonce cannot be validated";
			$authError->errorDetails = $authErrorDetails;
			$respErrorObj[] = $authError;
			
			return $respErrorObj;
		} else if ($this->isForbiddenVal && $this->isAvailClientHeaderVal) {
			$authError = new Error ();
			$authErrorDetails = new ErrorDetails ();
			$authErrorDetails->type = "403";
			$authErrorDetails->message = "Forbidden: The server address is not authorized.";
			$authError->errorDetails = $authErrorDetails;
			$respErrorObj[] = $authError;
			
			return $respErrorObj;
		} else if (!$this->isAuthenticatedVal) {
			
				
			$authError = new Error ();
			$authErrorDetails = new ErrorDetails ();
			$authErrorDetails->type = "401";
			$authErrorDetails->message = "Unauthorized: Incorrect username and/or password.";
			$authErrorDetails->nonce = NonceUtil::generate ( NONCE_SECRET, 1 );
			$authError->errorDetails = $authErrorDetails;
			$respErrorObj[] = $authError;
			
			return $respErrorObj;
		} else if (! $this->isValidUSCityStateVal) {
			
			$error = new Error ();
			$errorDetails = new ErrorDetails ();
			$errorDetails->type = "420";
			$errorDetails->message = "Invalid Origin or Destination Address";
			$error->errorDetails = $errorDetails;
			$respErrorObj[] = $error;
			
			return $respErrorObj;
		}else if (! $this->isValidNonUSCountryCodeVal){
			
			$authError = new Error ();
			$authErrorDetails = new ErrorDetails ();
			$authErrorDetails->type = "421";
			$authErrorDetails->message = "Invalid country code or country is not currenlty being shipped to";
			$authError->errorDetails = $authErrorDetails;
			$respErrorObj[] = $authError;
			
			return $respErrorObj;
				
		}
		else if (!$this->isRateAvailable){
			
			$authError = new Error ();
			$authErrorDetails = new ErrorDetails ();
			$authErrorDetails->type = "422";
			$authErrorDetails->message = "Rates are not currently avilable from from origin";
			$authError->errorDetails = $authErrorDetails;
			$respErrorObj[] = $authError;
				
			return $respErrorObj;
			
		}
		else if (!$this->isValidServiceCodeVal){
			$error = new Error ();
			$errorDetails = new ErrorDetails ();
			$errorDetails->type = "423";
			$errorDetails->message = "Invaild SwiftPac Service Code.";
			$error->errorDetails = $errorDetails;
			$respErrorObj[] = $error;
			return $respErrorObj;
			
		}
		
		return null;
	}
	/**
	 * Retrieves and saves a user's registered information to an instance.
	 *
	 * @param string $username        	
	 */
	protected function saveUserData($username) {
		$this->userData = $this->swiftPacApiDb->getUserData ( $username );
	}
	/**
	 * Retrieves and saves a list of domains associated with a user's account to an instance.
	 *
	 * @param string $username        	
	 */
	protected function saveUserDomains($username) {
		$this->userDomains = $this->swiftPacApiDb->getUserDomains ( $username );
	}
	/**
	 *  Get user data 
	 * @param string $username        	
	 * @return boolean
	 */
	protected function getUserData($username) {
		if (! isset ( $this->userData ))
			$this->saveUserData ( $username );
		return $this->userData->username == $username;
	}
	/**
	 * Validates a domain from which a user is making a request.
	 *
	 * @param string $realm        	
	 * @param string $clientUsername        	
	 * @return boolean
	 */
	protected function isValidRealm($realm, $clientUsername) {
		if (! isset ( $this->userDomains ))
			$this->saveUserDomains ( $clientUsername );
		
		foreach ( $this->userDomains as $domain ) {
			if ($domain->http_host == $realm)
				return true;
		}
		return false;
	}
	/**
	 * Get rates from ABOL - ProStar Rates
	 *
	 * @param SwiftPacRates $params        	
	 * @return object
	 */
	protected function getProStarRates() {
		$shipment = $this->shipment;
		
		$wsdlUrl = 'http://us.iabol.com/api/v3/abolapi.asmx?WSDL';
		$abolAuthentication = array (
				'ActivationKey' => 'SPL12282015-6BFED2F5-D236-4752-8D24-3A0F495C7EC5',
				'LoginName' => 'swiftpac',
				'Password' => 'swiftpac1' 
		);
		
		$abolClient = new Client ( $wsdlUrl );
		$abolRate = new AbolRate ();
		$abolShipments = new ABOLShipmentList ();
		$abolPackages = new ABOLPackages ();
		
		$abolShipment = new ABOLShipment ();
		$abolShipment->ShipFrom->City = $shipment->shipFrom->city;
		$abolShipment->ShipFrom->StateCode = $shipment->shipFrom->state;
		$abolShipment->ShipFrom->Zip = $shipment->shipFrom->zip;
		$abolShipment->ShipFrom->CountryCode = $shipment->shipFrom->countryCode;
		$abolShipment->ShipFrom->CountryName = $shipment->shipFrom->countryName;
		
		$abolShipment->ShipTo->City = $shipment->shipTo->city;
		$abolShipment->ShipTo->StateCode = null;
		$abolShipment->ShipTo->Zip = null;
		$abolShipment->ShipTo->CountryCode = $shipment->shipTo->countryCode;
		$abolShipment->ShipTo->CountryName = $shipment->shipTo->countryName;
		$abolShipment->ShipDate = $shipment->shipDate;
		$abolShipment->ServiceCode = "4454";
		
		if (isset($shipment->serviceCode)) $abolShipment->ServiceCode = $shipment->serviceCode;
		
		// Creating packages
		$packages = $shipment->packageList->packages;
		
		if (! is_array ( $packages ))
			$packages = array (
					$packages 
			);
		foreach ( $packages as $package ) {
			$abolPackage = new ABOLPackage ();
			$abolPackage->Length = $package->dimensions->length;
			$abolPackage->Height = $package->dimensions->height;
			$abolPackage->Width = $package->dimensions->width;
			$abolPackage->PackagingType = 105;
			$abolPackage->ReferenceNumber = 'PackageReference';
			$abolPackage->Weight = $package->weight;
			
			$abolPackages->addPackage ( $abolPackage );
		}
		$abolShipment->PackageList = $abolPackages;
		$abolShipment->CarrierId = 'PROSTAR';
		$abolRate->request->Rate->RequestDetail->ShipmentList->Shipment = $abolShipment;
		
		// Assigning Authentication details to the request
		$abolRate->request->Authentication = $abolAuthentication;
		$response = $abolClient->AbolRate ( $abolRate );
		$results = $response->AbolRateResult->Rate_Result->RateResultList->RateResult->Service;
		
		if (is_object ( $results )) {
			$obj = new stdClass ();
			$obj->results = $results;
			$results = ( array ) $obj;
		}
		
		return $results;
	}
	/**
	 * Get USPS Priority Mail Rates
	 *
	 * @param SwiftPacRates $params        	
	 * @return object
	 */
	protected function getUSPSRates() {
		
		// This script was written by Mark Sanborn at http://www.marksanborn.net
		// If this script benefits you are your business please consider a donation
		// You can donate at http://www.marksanborn.net/donate.
		
		// This script was further modified by SwiftPac Developers to acommodate priority USPS Rates.
		
		// ========== CHANGE THESE VALUES TO MATCH YOUR OWN ===========
		$userName = '030CARIB2806'; // Your USPS Username
		$shipment = $this->shipment;
		
		$orig_zip = $shipment->shipFrom->zip;
		;
		$dest_zip = $shipment->shipTo->zip;
		$shipDate = $shipment->shipDate;
		
		//=============== DON'T CHANGE BELOW THIS LINE ===============
		
		// Create USPS rate request xml data string
		$data = "API=RateV4&XML=
		<RateV4Request USERID='$userName'>
		<Revision>2</Revision>";
		
		// get packages
		$packages = $shipment->packageList->packages;
		if (! is_array ( $packages ))
			$packages = array (
					$packages 
			);
		
			foreach ( $packages as $key => $package ) {
			$USPSContainer = $package->packagingType;
			if ($USPSContainer == null)
				$USPSContainer = "RECTANGULAR";
			if (!($USPSContainer == "RECTANGULAR" && $USPSContainer == "NONRECTANGULAR" && $USPSContainer == "VARIABLE" )){
				
				$USPSContainer = "RECTANGULAR";
				
			}
			$weight = $package->weight;
			$width = $package->dimensions->width;
			$length = $package->dimensions->length;
			$height = $package->dimensions->height;
			$girth = $package->dimensions->girth;
			
			$data .= "<Package ID='$key'>
			<Service>ONLINE</Service>";
			if ($orig_zip)
				$data .= "<ZipOrigination>$orig_zip</ZipOrigination>";
			if ($dest_zip)
				$data .= "<ZipDestination>$dest_zip</ZipDestination>";
			if ($weight)
				$data .= "<Pounds>$weight</Pounds>";
			$data .= "<Ounces>0</Ounces>";
			$data .= "<Container>$USPSContainer</Container>";
			$data .= "<Size>LARGE</Size>";
			if ($width)
				$data .= "<Width>$width</Width>";
			if ($length)
				$data .= "<Length>$length</Length>";
			if ($height)
				$data .= "<Height>$height</Height>";
			if ($USPSContainer == "NONRECTANGULAR")
				$data .= "<Girth>$girth</Girth>";
			$data .= "<Machinable>true</Machinable>";
			if ($shipDate)
				$data .= "<ShipDate>$shipDate</ShipDate>";
			$data .= "</Package>";
		}
		
		$data = $data . "</RateV4Request>";
		
		$params = $this->postToUSPS ( $data );
		
		// Parse rate response and get commercial and standard rates for priority services.
		$uspsPriorityService = array ();
		
		if ($params['RATEV4RESPONSE'][0]['ERROR']) {
			$this->var_error_log(array("USPS_ERROR" => $params['RATEV4RESPONSE'][0]['ERROR']));
			
			$uspsPriorityService = null;
			
		}
		
		else {
// 			$this->var_error_log(array("USPS" => $params["RATEV4RESPONSE"]));

			$priorityMail = new stdClass();
			$priorityMailExpress = new stdClass();
		
			foreach ( $params ['RATEV4RESPONSE'] as $i => $service ) {
				
				// priority mail 2-day
				if (!isset($priorityMail->rate)) {
// 				if (! array_key_exists ( 0, $priority_express_rates )) {
// 					$priority_express_rates [0] = $service [1];
// 					$estDelDate = $priority_express_rates[0]["COMMITMENTDATE"];
					$estDelDate = $service[1]["COMMITMENTDATE"];
// 					$priority_express_rates [0]["TRANSITTIME"] = $this->getTransitTime($estDelDate);
					$priorityMail->estimatedDeliveryDate = $estDelDate;
					$priorityMail->transitTime = $this->getTransitTime($estDelDate);
					$priorityMail->rate = $service[1]["RATE"];
						
				} else {
// 					$priority_express_rates [0] ["RATE"] += $service [1] ["RATE"];
// 					$priority_express_rates [0] ["COMMERCIALRATE"] += $service [1] ["COMMERCIALRATE"];
					$priorityMail->rate += $service[1]["RATE"];
					$priorityMail->rate += $service[1]["COMMERCIALRATE"];
				}
				// priority mail express
				if (!isset($priorityMailExpress->rate)) {
// 				if (! array_key_exists ( 1, $priority_express_rates )) {
// 					$priority_express_rates [1] = $service [3];
					$estDelDate = $service[3]["COMMITMENTDATE"];
// 					$estDelDate = $priority_express_rates[1]["COMMITMENTDATE"];
// 					$priority_express_rates [1]["TRANSITTIME"] = $this->getTransitTime($estDelDate);
					$priorityMailExpress->estimatedDeliveryDate = $estDelDate;
					$priorityMailExpress->transitTime = $this->getTransitTime($estDelDate);
					$priorityMailExpress->rate = $service[3]["RATE"];
						
				} else {
// 					$priority_express_rates [1] ["RATE"] += $service [3] ["RATE"];
// 					$priority_express_rates [1] ["COMMERCIALRATE"] += $service [3] ["COMMERCIALRATE"];
					$priorityMailExpress->rate += $service[3]["RATE"];
					$priorityMailExpress->rate += $service[3]["COMMERCIALRATE"];
				}
			}
// 			return array(
// 					$priorityMail,
// 					1 => $priorityMailExpress
// 			);

			$uspsPriorityService[0] = $priorityMail;
			$uspsPriorityService[1] = $priorityMailExpress;
			
		}
// 		$transitTime = new stdClass();

// 			$this->var_error_log(array("USPS "=>$uspsPriorityService));
		return $uspsPriorityService;
	}
	
	/**
	 * Post XML created string to USPS and returns a response
	 * @param data
	 */
	private function postToUSPS($data) {
		$start_level = 1;
		$php_stmt = '';
		$url = "http://Production.ShippingAPIs.com/ShippingAPI.dll";
		$ch = curl_init ();
	
		// set the target url
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_HEADER, 1 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
	
		// parameters to post
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		// send the POST values to USPS
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
	
		$result = curl_exec ( $ch );
		$data = strstr ( $result, '<?' );
		// echo '<!-- '. $data. ' -->'; // Uncomment to show XML in comments
		$xml_parser = xml_parser_create ();
		xml_parse_into_struct ( $xml_parser, $data, $vals, $index );
		xml_parser_free ( $xml_parser );
		$params = array ();
		$level = array ();
	
		// Rebuild xml response using php arrays/object
		foreach ( $vals as $xml_elem ) {
				
			if ($xml_elem ['type'] == 'open') {
				if (array_key_exists ( 'attributes', $xml_elem )) {
						
					list ( $level [$xml_elem ['level']] ) = array_values ( $xml_elem ['attributes'] );
				} else {
					$level [$xml_elem ['level']] = $xml_elem ['tag'];
				}
			}
			if ($xml_elem ['type'] == 'complete') {
				$start_level = 1;
				$php_stmt = '$params';
				while ( $start_level < $xml_elem ['level'] ) {
					$php_stmt .= '[$level[' . $start_level . ']]';
					$start_level ++;
				}
				$php_stmt .= '[$xml_elem[\'tag\']] = $xml_elem[\'value\'];';
	
				eval ( $php_stmt );
			}
		}
		curl_close ( $ch );
		return $params;
	}
	
	/**
	 * Get Fedex Rates
	 * 
	 */
	protected function getFedexRates($serviceType){
		
		//========Fedex Rate Helper functions=========
		if (!function_exists("addShipper")){
			
			function addShipper($shipment){
				$shipper = array(
						'Contact' => array(
								'PersonName' => 'SwiftPac',
								'CompanyName' => $shipment->shipFrom->company,
								'PhoneNumber' => '13054709986'
						),
						'Address' => array(
								'StreetLines' => array($shipment->shipFrom->addressLine1),
								'City' => $shipment->shipFrom->city,
								'StateOrProvinceCode' => $shipment->shipFrom->state,
								'PostalCode' => $shipment->shipFrom->zip,
								'CountryCode' => $shipment->shipFrom->countryCode
						)
				);
				return $shipper;
			}
		}
		if (!function_exists("addRecipient")){
			
			function addRecipient($shipment){
				$recipient = array(
						'Contact' => array(
								'PersonName' => 'SwiftPac',
								'CompanyName' => $shipment->shipTo->company,
								'PhoneNumber' => '13054709986'
						),
						'Address' => array(
								'StreetLines' => array($shipment->shipTo->addressLine1),
								'City' => $shipment->shipTo->city,
								'StateOrProvinceCode' => $shipment->shipTo->state,
								'PostalCode' => $shipment->shipTo->zip,
								'CountryCode' => $shipment->shipTo->countryCode,
								'Residential' => $shipment->shipTo->residentialFlag
						)
				);
				return $recipient;
			}
		}

		if (!function_exists("getTransitTimeAsNum")){
			
			function getTransitTimeAsNum($transitTimeAsText){
				switch ($transitTimeAsText){
					
					case 'ONE_DAY':
						return "1";
					case 'TWO_DAYS':
						return "2";
					case 'THREE_DAYS':
						return "3";
					case 'FOUR_DAYS':
						return "4";
					case 'FIVE_DAYS':
						return "5";
									
				}
			}
		}
		
		if (!function_exists("addShippingChargesPayment")){
			
			function addShippingChargesPayment(){
				$shippingChargesPayment = array(
						'PaymentType' => 'SENDER', // valid values RECIPIENT, SENDER and THIRD_PARTY
						'Payor' => array(
								'ResponsibleParty' => array(
										'AccountNumber' => getProperty('billaccount'),
										'CountryCode' => $shipment->shipTo->countryCode
								)
						)
				);
				return $shippingChargesPayment;
			}
		}
		if (!function_exists("addLabelSpecification")){
		
			function addLabelSpecification(){
				$labelSpecification = array(
						'LabelFormatType' => 'COMMON2D', // valid values COMMON2D, LABEL_DATA_ONLY
						'ImageType' => 'PDF',  // valid values DPL, EPL2, PDF, ZPLII and PNG
						'LabelStockType' => 'PAPER_7X4.75'
				);
				return $labelSpecification;
			}
		}
		
		if (!function_exists("addSpecialServices")){
		
			function addSpecialServices(){
				$specialServices = array(
						'SpecialServiceTypes' => array('COD'),
						'CodDetail' => array(
								'CodCollectionAmount' => array(
										'Currency' => 'USD',
										'Amount' => 150
								),
								'CollectionType' => 'ANY' // ANY, GUARANTEED_FUNDS
						)
				);
				return $specialServices;
			}
		}
		
		if (!function_exists("addPackageLineItem1")){
		
			function addPackageLineItem1($shipment){
				$packageLineItem = array(
						'SequenceNumber'=>1,
						'GroupPackageCount'=>1,
						'Weight' => array(
								'Value' => $shipment->packageList->packages->weight,
								'Units' => 'LB'
						),
						'Dimensions' => array(
								'Length' => $shipment->packageList->packages->dimensions->length ,
								'Width' => $shipment->packageList->packages->dimensions->width,
								'Height' => $shipment->packageList->packages->dimensions->height,
								'Units' => 'IN'
						)
				);
				return $packageLineItem;
			}
		}
		//=========End of Fedex Rate Helper functions==========
		
		$shipment = $this->shipment;
		
		$origZip = $shipment->shipFrom->zip;
		$destZip = $shipment->shipTo->zip;
		$shipDate = $shipment->shipDate;
		$shipToCountryCode = strtoupper($shipment->shipTo->countryCode);
		$shipFromCountryCode = strtoupper($shipment->shipFrom->countryCode);
		$warehouseZip = $this->swiftPacInHouseCharges->getWarehouseAddr()->zip;
		
		
		//Please include and reference in $path_to_wsdl variable.
		$path_to_wsdl = "lib/fedex/WebServices/wsdl/RateService_v18.wsdl";
	
		ini_set("soap.wsdl_cache_enabled", "0");
		
		$client = new SoapClient($path_to_wsdl, array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information
		
		$request['WebAuthenticationDetail'] = array(
				'ParentCredential' => array(
						'Key' => getProperty('key'),
						'Password' => getProperty('password')
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
		$request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Rate Request using PHP ***');
		$request['Version'] = array(
				'ServiceId' => 'crs',
				'Major' => '18',
				'Intermediate' => '0',
				'Minor' => '0'
		);
		$request['ReturnTransitAndCommit'] = true;
		$request['RequestedShipment']['DropoffType'] = 'REGULAR_PICKUP'; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
		$request['RequestedShipment']['ShipTimestamp'] = date('c');
		
// 		if ($shipToCountryCode == 'US' && $destZip != $warehouseZip){
// 		if ($serviceType == DomesticRateDeliveryType::FEDEX_GROUND){
// 			$request['RequestedShipment']['ServiceType'] = 'FEDEX_GROUND'; // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
// 		}
// // 		else if ($shipToCountryCode =="US" && $destZip == $warehouseZip){
// 		else if ($serviceType == DomesticRateDeliveryType::FEDEX_STANDARD_OVERNIGHT){
			
// 			$request['RequestedShipment']['ServiceType'] = 'STANDARD_OVERNIGHT'; // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
					
// 		}
// 		else if ($serviceType == DomesticRateDeliveryType::FEDEX_INTERNATIONAL_PRIORITY) {
// 			$request['RequestedShipment']['ServiceType'] = 'INTERNATIONAL_PRIORITY'; // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
			
// 		}
		$request['RequestedShipment']['ServiceType'] = $serviceType;
		
		$request['RequestedShipment']['PackagingType'] = 'YOUR_PACKAGING'; // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
		$request['RequestedShipment']['TotalInsuredValue']=array(
				'Ammount'=>100,
				'Currency'=>'USD'
		);
		$request['RequestedShipment']['Shipper'] = addShipper($shipment);
		$request['RequestedShipment']['Recipient'] = addRecipient($shipment);
		$request['RequestedShipment']['ShippingChargesPayment'] = addShippingChargesPayment();
		$request['RequestedShipment']['PackageCount'] = '1';
		$request['RequestedShipment']['RequestedPackageLineItems'] = addPackageLineItem1($shipment);
// $this->var_error_log(array("shiment" =>$shipment));
		
		$response = $client -> getRates($request);
		
		$totalShipmentCharges = new stdClass();
		if ($response -> HighestSeverity == 'FAILURE' || $response -> HighestSeverity == 'ERROR'){
			
			writeToLog($client); //write error to log if there is an error
			$this->var_error_log(array("FEDEX_ERROR" => $client));
			$totalShipmentCharges = null; // Fedex Can't provide rate
			
		}
		else {
			
// 			$this->var_error_log(array("fedex" => $response));
// 			$this->var_error_log(array("fedexSurchageq" => $response -> RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail->Surcharges));
				
			
			$rateReply = $response -> RateReplyDetails;
			if($rateReply->RatedShipmentDetails && is_array($rateReply->RatedShipmentDetails)){ // multiple services returned
				$totalBaseChargeAmount = number_format($rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->TotalBaseCharge->Amount,2);
				$totalSurchargesAmount = number_format($rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->TotalSurcharges->Amount,2);
				$totalNetChargeAmount = number_format($rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount,2,".","");
				$surcharges = $rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->Surcharges;
				
			}
			else {
				$totalBaseChargeAmount = number_format($rateReply->RatedShipmentDetails->ShipmentRateDetail->TotalBaseCharge->Amount,2);
				$totalSurchargesAmount = number_format($rateReply->RatedShipmentDetails->ShipmentRateDetail->TotalSurcharges->Amount,2);
				$totalNetChargeAmount = number_format($rateReply->RatedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Amount,2,".","");
				$surcharges = $rateReply->RatedShipmentDetails->ShipmentRateDetail->Surcharges;
				
			}
// 			$this->var_error_log(array("fedex_request" => "test1"));
// 			$this->var_error_log(array("surchages2" => $surcharges));
			
			$totalShipmentCharges->rate = $totalNetChargeAmount;
			$totalShipmentCharges->baseRate = $totalBaseChargeAmount;
			
			if ($surcharges && is_array($surcharges)){
				foreach ($surcharges as $surcharge){
					$totalShipmentCharges->fees[$surcharge->SurchargeType] = number_format($surcharge->Amount->Amount,2,".",",");
				}
			}
			else{
				$totalShipmentCharges->fees[$surcharges->SurchargeType] = number_format($surcharges->Amount->Amount,2,".",",");
				
			}
			
			if ($request['RequestedShipment']['ServiceType'] == "FEDEX_GROUND"){
				
				$transitTime = getTransitTimeAsNum($rateReply->TransitTime);
// 				$this->var_error_log(array("transit" => $transitTime));
				
				$totalShipmentCharges->transitTime = $transitTime . " day(s)";
				$dateDiff = new DateInterval('P'.$transitTime. 'D');
				$currDate = new DateTime();
				$estDateDel = $currDate->add($dateDiff);
				$totalShipmentCharges->estimatedDeliveryDate = $estDateDel->format("Y-m-d");			
			}
			else {
				if ($rateReply->DeliveryTimestamp){
					$estDateDel = substr($rateReply->DeliveryTimestamp, 0,10);
					$currDate = new DateTime();
					$estDelDate = date_create($estDateDel);
					
					$interval = date_diff($estDelDate, $currDate);
					
					
					$transitTime = $interval->format("%a") . " day(s)";
				}
				else{
					$estDateDel = "N/A";
					$transitTime = "N/A";
				}
				$totalShipmentCharges->estimatedDeliveryDate = $estDateDel;
				$totalShipmentCharges->transitTime = $transitTime;
				
				
			}
					
		}
		
// 		$this->var_error_log(array("fedexTotal" => $totalShipmentCharges));
		
		return $totalShipmentCharges;
		
		
	}
		
	/**
	 * Get SwiftPac International Expedited Rates
	 */
	protected function getSwiftPacIntlExp(){
		
		$shipment = $this->shipment;
		$origin_zip = $shipment->shipFrom->zip;
		$warehouseZip = $this->swiftPacInHouseCharges->getWarehouseZip();
// 		$this->var_error_log(array("warehousezip" =>$warehouseZip, "originzip" => $origin_zip));
		
		
		
		if ($origin_zip != $warehouseZip){
			
			$totalShipmentCharges = new stdClass();
			$expeditedDomesticSvcToWarehouse = $this->getBestExpeditedDomesticService();
			$expeditedIntlSvcFromWarehouse = $this->getBestExpeditedIntlService();
			
			if ($expeditedDomesticSvcToWarehouse == null || $expeditedIntlSvcFromWarehouse == null) {
				
				$totalShipmentCharges = null;
			}
			
			else {
				$totalShipmentCharges->rate = $expeditedDomesticSvcToWarehouse->rate + $expeditedIntlSvcFromWarehouse->rate;
				if (isset($expeditedDomesticSvcToWarehouse->baseRate)){
					$totalShipmentCharges->baseRate = $expeditedDomesticSvcToWarehouse->baseRate + $expeditedIntlSvcFromWarehouse->baseRate;
				}
				else {
					$totalShipmentCharges->baseRate =  $expeditedIntlSvcFromWarehouse->baseRate + $expeditedDomesticSvcToWarehouse->rate;
					
				}
				
				if (isset($expeditedDomesticSvcToWarehouse->fees)){
					
					foreach ($expeditedDomesticSvcToWarehouse->fees as $key => $feeVal){
						$totalShipmentCharges->fees[$key] = $feeVal;
							
					}
				}
				
				if (isset($expeditedIntlSvcFromWarehouse->fees)){
					
					foreach ($expeditedIntlSvcFromWarehouse->fees as $key => $feeVal){
						$totalShipmentCharges->fees[$key] = $feeVal;
							
					}
				}
				
	// 			$totalShipmentCharges->transitTime = $expeditedIntlSvcFromWarehouse->estimatedDeliveryDateTransitTime["transitTime"];
				$totalShipmentCharges->estimatedDeliveryDate = $expeditedIntlSvcFromWarehouse->estimatedDeliveryDateTransitTime["estDelDate"];
				
				
	// 			$this->var_error_log(array("Domestic Svc"=>$expeditedDomesticSvcToWarehouse, "Intl Svc"=>$expeditedIntlSvcFromWarehouse));
				
	// 			$swiftPacAirFromWarehouse = $this->swiftPacInHouseCharges->getAirServiceCharges();
				
			
			}
		}
		else {
			
			$totalShipmentCharges = null; //service cannot originate from warehouse location
		}
		
	
		$this->var_error_log(array("totalExpedited" => $totalShipmentCharges));
		
		return $totalShipmentCharges;
		
		
		
	}
	
	/**
	 * Get the best Expedited domestic service - the best service is chosen on cheapest rate of the fastest delivery times
	 */
	private function getBestExpeditedDomesticService(){
	
// 		$originalShipTo = $this->shipment->shipTo; //save original ship to address
// 		$this->shipment->shipTo = $this->swiftPacInHouseCharges->getWarehouseAddr(); //change ship to address to warehouse address
		
// 		$today = new DateTime("today");
// 		$todayTimeStamp = $today->getTimestamp();
		
// 		//Store Servies Data
// 		$services["fedexExp"] = $this->getFedexRates();
// 		$services["uspsPriExp"] = $this->getUSPSRates()[1];
		
// 		//Extract Rate and Date from servivces
// 		$fedexExpToWarehouse = $services["fedexExp"];
// 		$fedexExpToWarehouseRate = $fedexExpToWarehouse->rate;
// 		$fedexExpToWarehouseEstDate = new DateTime($fedexExpToWarehouse->estimatedDeliveryDate);
// 		$fedexExpToWarehouseTimeStampWithRespectToToday = $fedexExpToWarehouseEstDate->getTimestamp() - $todayTimeStamp;
		
// // 		$this->var_error_log(array("usps" => $services["uspsPriExp"]));
// 		$uspsPriorityExpresToWarehouse = $services["uspsPriExp"];
// 		$uspsPriorityExpresToWarehouseRate = $uspsPriorityExpresToWarehouse->rate;
// 		$uspsPriorityExpresToWarehouseEstDate = new DateTime($uspsPriorityExpresToWarehouse->estimatedDeliveryDate);
// 		$uspsPriorityExpresToWarehouseTimeStampWithRespectToToday = $uspsPriorityExpresToWarehouseEstDate->getTimestamp() - $todayTimeStamp;
		
// 		//Compare time and rates to determine best delivery rate
// 		$domesticRateTime["fedexExp"]["rate"] = $fedexExpToWarehouseRate;
// 		$domesticRateTime["fedexExp"]["time"] = $fedexExpToWarehouseTimeStampWithRespectToToday;
// 		$domesticRateTime["uspsPriExp"]["rate"] = $uspsPriorityExpresToWarehouse->rate;
// 		$domesticRateTime["uspsPriExp"]["time"] = $uspsPriorityExpresToWarehouseTimeStampWithRespectToToday;

		$servicesOnRoute = $this->getServicesOnRoute(DomesticRateDeliveryType::DOMESTIC_EXPEDITED);
		$domesticRateTime = $servicesOnRoute["domesticRateTime"];
		$services = $servicesOnRoute["services"];
			
		$fastestDeliveryTimeChk = false ;
		$bestDeliveryServiceChk = false ;
		$fastestDeliveryServices = array();
			
// 		$this->var_error_log(array("domesticRateTIme" => $domesticRateTime));
		
		//Get first fastest service
		foreach ($domesticRateTime as $serviceName => $service ){
				
			if (!$fastestDeliveryTimeChk){
				$fastestDeliveryTime = $service["time"];
				$service["serviceName"] = $serviceName;
				$fastestDeliveryServices[0] = $service;
				$fastestDeliveryTimeChk = true;
			}
			if ($service["time"] < $fastestDeliveryTime){
					
				$fastestDeliveryTime = $service["time"];
				$service["serviceName"] = $serviceName;
				$fastestDeliveryServices[0] = $service;
			}
		
		
				
		}
		unset($domesticRateTime[$fastestDeliveryServices[0]["serviceName"]]);
			
		//Get other services that are matching with that of the fastest
		foreach ($domesticRateTime as $serviceName => $service ){
			if ($service["time"] == $fastestDeliveryTime){
		
				$fastestDeliveryTime = $service["time"];
				$service["serviceName"] = $serviceName;
				$fastestDeliveryServices[] = $service;
			}
		}
			
		//Get the cheaptest of the fast servivces
		foreach ($fastestDeliveryServices as $service){
		
		
			if (!$bestDeliveryServiceChk){
				$bestRate = $service["rate"];
				$bestDeliveryService = $service;
				$bestDeliveryServiceChk = true;
			}
			if ($service["rate"] < $bestRate){
					
				$bestRate = $service["rate"];
				$bestDeliveryService = $service;
			}
		}
		
// 		$this->var_error_log(array("domesticRateTime" => $domesticRateTime));
// 		$this->var_error_log(array("fastestDelSvcs" => $fastestDeliveryServices));
// 		$this->var_error_log(array("services" => $services));
// 		$this->var_error_log(array("bestDelSvc" => $services[$bestDeliveryService["serviceName"]]));
		
// 		$this->shipment->shipTo = $originalShipTo; //replace warehouse address with original ship to address
		$this->shipment->shipDate = $services[$bestDeliveryService["serviceName"]]->estimatedDeliveryDate; //set shipment date the estimated arrival date from domestic
		
		
		return $services[$bestDeliveryService["serviceName"]];
	}
	
	/**
	 * Get the best Expedited International service - the best service is chosen on delivery time
	 * 
	 */
	private function getBestExpeditedIntlService(){
			
			$address = new \lib\SwiftPac\DataTypes\Address();
			$address = $this->swiftPacInHouseCharges->getWarehouseAddr();
			$originalShipFromAddr = $this->shipment->shipFrom;
			$this->shipment->shipFrom = $address; //change ship from address to warehouse
		
			$serviceCharges = $this->swiftPacInHouseCharges->getAirServiceCharges();
			$serviceCharges->estimatedDeliveryDateTransitTime = $this->getNextShipmentDate($this->shipment->shipFrom->zip, $this->shipment->shipTo->countryCode, 
					"Air",$this->shipment->shipDate);
// 			$serviceCharges->transitTime = $this->getNextShipmentDate($this->shipment->shipFrom->zip, $this->shipment->shipTo->countryCode, "Air");
			
			$this->shipment->shipFrom = $originalShipFromAddr;

			return $serviceCharges;
			
		
		
	}
	
	/**
	 * Get SwiftPac International Regualar Rates
	 */
	protected function getSwiftPacIntlReg(){
		
		$shipment = $this->shipment;
		$origin_zip = $shipment->shipFrom->zip;
		$warehouseZip = $this->swiftPacInHouseCharges->getWarehouseZip();
		
		$totalShipmentCharges = new stdClass();
		
		if ($origin_zip != $warehouseZip){
			
			
			$regularDomesticSvcToWarehouse = $this->getBestRegularDomesticService();
			$regularIntlSvcFromWarehouse = $this->getBestRegularIntlService();
			
			
			$this->var_error_log(array("regular" =>$regularIntlSvcFromWarehouse));
			
			if ($regularDomesticSvcToWarehouse == null || $regularIntlSvcFromWarehouse == null) {
			
				$totalShipmentCharges = null;
			}
			else {
				
				$totalShipmentCharges->rate = $regularDomesticSvcToWarehouse->rate + $regularIntlSvcFromWarehouse->rate;
				if (isset($regularDomesticSvcToWarehouse->baseRate)){
					$totalShipmentCharges->baseRate = $regularDomesticSvcToWarehouse->baseRate + $regularIntlSvcFromWarehouse->baseRate;
				}
				else {
					$totalShipmentCharges->baseRate =  $regularIntlSvcFromWarehouse->baseRate + $regularDomesticSvcToWarehouse->rate;
						
				}
				
				if (isset($regularDomesticSvcToWarehouse->fees)){
						
					foreach ($regularDomesticSvcToWarehouse->fees as $key => $feeVal){
						$totalShipmentCharges->fees[$key] = $feeVal;
							
					}
				}
				
				if (isset($regularIntlSvcFromWarehouse->fees)){
						
					foreach ($regularIntlSvcFromWarehouse->fees as $key => $feeVal){
						$totalShipmentCharges->fees[$key] = $feeVal;
							
					}
				}
				
				$totalShipmentCharges->estimatedDeliveryDate = $regularIntlSvcFromWarehouse->estimatedDeliveryDateTransitTime["estDelDate"];
				
			}
				
			
		}
		else {
				
			$totalShipmentCharges = null; //service cannot originate from warehouse location
		}
		
		return $totalShipmentCharges;
		
	}
	
	/**
	 * Get the best Regular domestic service - the best service is chosen on the fastest delivery time of the cheapest rates
	 * 
	 */
	private function getBestRegularDomesticService(){
		
		$servicesOnRoute = $this->getServicesOnRoute(DomesticRateDeliveryType::DOMESTIC_REGULAR);
		
		$domesticRateTime = $servicesOnRoute["domesticRateTime"];
		$services = $servicesOnRoute["services"];
		
		$cheapestDeliveryRateChk = false ;
		$bestDeliveryServiceChk = false ;
		$cheapestDeliveryServices = array();
			
				$this->var_error_log(array("domesticRateTImeReg" => $domesticRateTime));
		
		
		//Get first cheapeast service
		foreach ($domesticRateTime as $serviceName => $service ){
		
			if (!$cheapestDeliveryRateChk){
				$cheapestDeliveryRate = $service["rate"];
				$service["serviceName"] = $serviceName;
				$cheapestDeliveryServices[0] = $service;
				$cheapestDeliveryRateChk = true;
			}
			if ($service["rate"] < $cheapestDeliveryRate){
					
				$cheapestDeliveryRate = $service["rate"];
				$service["serviceName"] = $serviceName;
				$cheapestDeliveryServices[0] = $service;
			}
		
		
		
		}
		unset($domesticRateTime[$cheapestDeliveryServices[0]["serviceName"]]); //remove the cheapest service from the list of domestic rates 
			
		//Get other services that are matching with that of the cheapest
		foreach ($domesticRateTime as $serviceName => $service ){
			if ($service["rate"] == $cheapestDeliveryRate){
		
				$cheapestDeliveryRate = $service["rate"];
				$service["serviceName"] = $serviceName;
				$cheapestDeliveryServices[] = $service; //add other services to list
			}
		}
		
		//Get the fastest of the cheapest servivces
		foreach ($cheapestDeliveryServices as $service){
		
		
			if (!$bestDeliveryServiceChk){
				$bestTime = $service["time"];
				$bestDeliveryService = $service;
				$bestDeliveryServiceChk = true;
			}
			if ($service["time"] < $bestTime){
					
				$bestRate = $service["rate"];
				$bestDeliveryService = $service;
			}
		}
		
		
		
		// 		$this->var_error_log(array("domesticRateTime" => $domesticRateTime));
				$this->var_error_log(array("cheapeastDelSvcs" => $cheapestDeliveryServices));
		// 		$this->var_error_log(array("services" => $services));
				$this->var_error_log(array("bestDelSvc" => $services[$bestDeliveryService["serviceName"]]));
		
		$this->shipment->shipDate = $services[$bestDeliveryService["serviceName"]]->estimatedDeliveryDate; //set shipment date the estimated arrival date from domestic
		
		
		return $services[$bestDeliveryService["serviceName"]];
		
	}
	
	/**
	 * 	Get the best Regular International service - the best service is chosen on cheapest rate
	 * @return mixed
	 */
	private function getBestRegularIntlService(){
		$address = new \lib\SwiftPac\DataTypes\Address();
		$address = $this->swiftPacInHouseCharges->getWarehouseAddr();
		$originalShipFromAddr = $this->shipment->shipFrom;
		$this->shipment->shipFrom = $address; //change ship from address to warehouse
		
		$this->var_error_log(array("oceanReg" => $this->shipment));
		$serviceCharges = $this->swiftPacInHouseCharges->getOceanCharges();
		// 			$serviceCharges->transitTime = $this->getNextShipmentDate($this->shipment->shipFrom->zip, $this->shipment->shipTo->countryCode, "Air");
		
		$estDelDate = $this->getNextShipmentDate($this->shipment->shipFrom->zip, $this->shipment->shipTo->countryCode,"Ocean",$this->shipment->shipDate);
		$serviceCharges["totalOceanShipmentCharges"]->estimatedDeliveryDateTransitTime = $estDelDate;
		
		$this->shipment->shipFrom = $originalShipFromAddr;
		
		$this->var_error_log(array("IntlRegOcean" => $serviceCharges, "estDelOceanDate" =>$estDelDate));
		
		return $serviceCharges["totalOceanShipmentCharges"];
	}
	
	/**
	 * All Domestic and International services involve with the domestic/international  shipping services
	 * @return Array with list of domestic anservices
	 */
	private function getServicesOnRoute($serviceType){
		
		$originalShipTo = $this->shipment->shipTo; //save original ship to address
		$this->shipment->shipTo = $this->swiftPacInHouseCharges->getWarehouseAddr(); //change ship to address to warehouse address
		
		$today = new DateTime("today");
		$todayTimeStamp = $today->getTimestamp();
		
		//Store Servies Data
		if ($serviceType == DomesticRateDeliveryType::DOMESTIC_EXPEDITED){
			
			$services["fedex"] = $this->getFedexRates(DomesticRateDeliveryType::FEDEX_STANDARD_OVERNIGHT);
			$services["usps"] = $this->getUSPSRates()[1];
		}
		else {
			
			$services["fedex"] = $this->getFedexRates(DomesticRateDeliveryType::FEDEX_GROUND);
			$services["usps"] = $this->getUSPSRates()[0];
			
		}
		
		//Extract Rate and Date from servivces
		$fedexExpToWarehouse = $services["fedex"];
		$fedexExpToWarehouseRate = $fedexExpToWarehouse->rate;
		$fedexExpToWarehouseEstDate = new DateTime($fedexExpToWarehouse->estimatedDeliveryDate);
		$fedexExpToWarehouseTimeStampWithRespectToToday = $fedexExpToWarehouseEstDate->getTimestamp() - $todayTimeStamp;
		
		// 		$this->var_error_log(array("usps" => $services["uspsPriExp"]));
		$uspsPriorityExpresToWarehouse = $services["usps"];
		$uspsPriorityExpresToWarehouseRate = $uspsPriorityExpresToWarehouse->rate;
		$uspsPriorityExpresToWarehouseEstDate = new DateTime($uspsPriorityExpresToWarehouse->estimatedDeliveryDate);
		$uspsPriorityExpresToWarehouseTimeStampWithRespectToToday = $uspsPriorityExpresToWarehouseEstDate->getTimestamp() - $todayTimeStamp;
		
		//Compare time and rates to determine best delivery rate
		$domesticRateTime["fedex"]["rate"] = $fedexExpToWarehouseRate;
		$domesticRateTime["fedex"]["time"] = $fedexExpToWarehouseTimeStampWithRespectToToday;
		$domesticRateTime["usps"]["rate"] = $uspsPriorityExpresToWarehouse->rate;
		$domesticRateTime["usps"]["time"] = $uspsPriorityExpresToWarehouseTimeStampWithRespectToToday;
		
		$this->shipment->shipTo = $originalShipTo;
		
		$this->var_error_log(array("shipmentTo" => $this->shipment->shipTo));
		
		return array (
				"domesticRateTime" => $domesticRateTime,
				"services" => $services
				);
		
	}
	
	/**
	 * Validates the city and the state of a given US zipcode
	 * @param string
	 * @return boolean
	 */
	protected function isValidUSCityState() {
// 		$isValidOriginUsAddress = true;
// 		$isValidDestUsAddress = true;
		$userName = '030CARIB2806'; // Your USPS Username
		$origZip = $this->shipment->shipFrom->zip;
		$origCity = strtoupper($this->shipment->shipFrom->city);
		$origState = strtoupper($this->shipment->shipFrom->state);
		
		$countryCode = strtoupper($this->shipment->shipTo->countryCode);
		
		$destZip = $this->shipment->shipTo->zip;
		$destCity = strtoupper($this->shipment->shipTo->city);
		$destState = strtoupper($this->shipment->shipTo->state);
		
		// set the target url
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_HEADER, 1 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		
		// parameters to post
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		
		// Create USPS City and State lookup request xml data string
		$data = "API=CityStateLookup&XML=
		<CityStateLookupRequest USERID='$userName'>
		<ZipCode ID='0'>
		<Zip5>$origZip</Zip5>
		</ZipCode>";
		
		if ($countryCode == "US"){
			$data .= "<ZipCode ID='1'>
						<Zip5>$destZip</Zip5>
						</ZipCode>";
		}
		
		$data .= "</CityStateLookupRequest>";
		
		$params = $this->postToUSPS ( $data );
		
		$oriStateRes = strtoupper($params ["CITYSTATELOOKUPRESPONSE"] [0] ["STATE"]);
		$oriCityRes = strtoupper($params ["CITYSTATELOOKUPRESPONSE"] [0] ["CITY"]);
		
		if ($countryCode == "US"){
			$destStateRes = strtoupper($params ["CITYSTATELOOKUPRESPONSE"] [1] ["STATE"]);
			$destCityRes = strtoupper($params ["CITYSTATELOOKUPRESPONSE"] [1] ["CITY"]);
		}

		if ($oriStateRes != $origState || $oriCityRes != $origCity) {
			
			return false;
		}
		else if ($countryCode == "US" && ($destCityRes != $destCity || $destStateRes != $destState)){
			return  false;
				
		}
		else return true;
			//do nothing
	}
	/**
	 * Validates a non-us zip code;
	 * @param array $shipment
	 * @return boolean
	 */
	protected function isValidNonUSCountryCode () {
		$isValidShipTo = false;
		$isValidShipFrom = false;
		$shipToCountryCode = strtoupper($this->shipment->shipTo->countryCode);
		$shipToCountryName = strtoupper($this->shipment->shipTo->countryName);
		$shipFromCountryCode = strtoupper($this->shipment->shipFrom->countryCode);
		$shipFromCountryName = strtoupper($this->shipment->shipFrom->countryName);
		
		$validCountryCodes = array(
				'ANTIGUA AND BARBUDA'=> 'AG',
				'ARUBA'=>'AW',
				'BAHAMAS' => 'BS',
				'BARBADOS' => 'BB',
				'BELIZE' => 'BZ',
				'BERMUDA' => 'BM',
				'CANADA' => 'CA',
				'CAYMAN ISLAND' => 'KY',
				'COLUMBIA' => 'CO',
				'COSTA RICA' => 'CR',
				'DOMINICA' => 'DM',
				'DOMINICAN REPUBLIC' => 'DO',
				'GRENADA' => 'GD',
				'GUADELOUPE' => 'GP',
				'GUYANA' => 'GY',
				'HAITI' => 'HT',
				'JAMAICA' => 'JM',
				'MARTINIQUE' => 'MQ',
				'MONTSERRAT' => 'MS',
				'SAINT KITTS AND NEVIS' => 'KN',
				'SAINT LUCIA' => 'LC',
				'SAINT MAARTEN' => 'MF',
				'SAINT VINCENT AND THE GRENADINES' => 'VC',
				'TORTOLA' => 'VG',
				'TRINIDAD AND TOBAGO' => 'TT',
				'TURKS AND CAICOS' => 'TC',
				'US VIRGIN ISLAND' => 'VI',
				'VENEZUELA' => 'VE'
				
		);
		
		foreach ($validCountryCodes as $countryName => $countryCode){
			
			if ($shipFromCountryCode == $countryCode){
				$isValidShipTo = true;
				break;
			}
		}
		foreach ($validCountryCodes as $countryName => $countryCode ){
			
			if ($shipToCountryCode == $countryCode){
				$isValidShipFrom = true;
				break;
			}
		}
		if ($isValidShipTo && $isValidShipFrom) return true;
		return false;
		
	}
	
	/**
	 * Validates a country code to be either US or not
	 * @param string $countryCode
	 * @return boolean
	 */
	private function isUSCountryCode($countryCode){
		return strtoupper($countryCode) == "US";
	}

	/**
	 * Validates a country code to be either a SwiftPac location  or not
	 * @param string $countryCode
	 * @return boolean
	 */
	private function isSwiftPacLocation($countryCode){
		switch (strtoupper($countryCode)){
			
			case 'AI':
				return true;
			case 'AW':
				return true;
			case 'BS':
				return true;
			case 'BZ':
				return true;
			case 'BM':
				return true;
			case 'CA':
				return true;
			case 'CU':
				return true;
			case 'CO':
				return true;
			case 'CR':
				return true;
			case 'KY':
				return true;
			case 'DM':
				return true;
			case 'DO':
				return true;
			case 'GD':
				return true;
			case 'GP':
				return true;
			case 'GY':
				return true;
			case 'HT':
				return true;
			case 'JM':
				return true;
			case 'MQ':
				return true;
			case 'MS':
				return true;
			case 'KN':
				return true;
			case 'LC':
				return true;
			case 'MF':
				return true;
			case 'VC':
				return true;
			case 'VG':
				return true;
			case 'TT':
				return true;
			case 'TC':
				return true;
			case 'US':
				return true;
			case 'VI':
				return true;
			case 'VE':
				return true;
				
			default:
				return false;
		}
	}
	/**
	 * Validates a country code to be either part of the mailbox route or not
	 * @param string $countryCode
	 * @return boolean
	 */
	private function isPartOfAirRoute($countryCode){
		
		switch (strtoupper($countryCode)){
			
			case 'VC':
				return true;
			case 'LC':
				return true;
			case 'GD':
				return true;
			case 'DM':
				return true;
			case 'TT':
				return true;
			case 'BB':
				return true;
			case 'KN':
				return true;
			case 'GY':
				return true;
			case 'JM':
				return true;
			case 'VG':
				return true;
			default:
				return false;
		}
		
	}
	/**
	 * Validates a country code to be either part of the regional route or not
	 * @param string $countryCode
	 * @return boolean
	 */
	private function isPartOfRegionalRoute($countryCode){
		
		switch (strtoupper($countryCode)){
				
			case 'VC':
				return true;
			case 'LC':
				return true;
			case 'GD':
				return true;
			case 'DM':
				return true;
			case 'TT':
				return true;
			case 'BB':
				return true;
			case 'KN':
				return true;
			default:
				return false;
		}
		
	}
	/**
	 * Validates a country code to be either part of the Ocean route or not
	 * @param string $countryCode
	 * @return boolean
	 */
	private function isPartOfOceanRoute ($countryCode){
		
		switch (strtoupper($countryCode)){
		
			case 'AG':
				return true;
			case 'BZ':
				return true;
			case 'LC':
				return true;
			case 'GD':
				return true;
			case 'HT':
				return true;
			case 'JM':
				return true;
			case 'GY':
				return true;
			case 'DM':
				return true;
			case 'TT':
				return true;
			case 'BB':
				return true;
			case 'KN':
				return true;
			case 'MF':
				return true;
			case 'VG':
				return true;
			case 'VC':
				return true;
			default:
				return false;
		}
		
	}
	/**
	 * Validates a country code to be either part of the Air route or not
	 * @param string $countryCode
	 * @return boolean
	 */
// 	private function isPartOfAirRoute($countryCode){
// 		return $this->isPartOfOceanRoute($countryCode);
// 	}
	
	/**
	 * Validates a service code 
	 * @param unknown $requestingServiceCode
	 * @return boolean
	 */
	private function isValidServiceCode($requestingServiceCode){
		
		foreach (self::SERVICE_CODES as $serviceCode){
			if ($requestingServiceCode == $serviceCode ) return true;
		}
		return false;
		
	}
	/**
	 * Return The estimated next Shipment date available for a given service 
	 * @param unknown $shipFromZip
	 * @param unknown $shipToCountryCode
	 * @param unknown $serviceType
	 * @return string
	 */
	private function getNextShipmentDate($shipFromZip,$shipToCountryCode,$serviceType,$shipDateAsStr = null ){
		
		switch ($serviceType){
		
			case 'Air':
			case 'Ocean':
			case 'Mailbox':
				
				if ($serviceType == 'Mailbox') $serviceType = 'Air';
				$transitData = $this->swiftPacApiDb->getAirOceanTransitTime($shipFromZip, $shipToCountryCode,$serviceType);
				$departDaysArray = explode ( "/", $transitData->Depart_Days );
				$transitTime = $transitData->Transit_Time;
				if ($shipDate == null)$currDate = new DateTime (); //current date and time
				else $currDate = new DateTime($shipDateAsStr); //set current shipment date to specific value;
				$currDayAsNumber = $currDate->format ( 'N' ); //Current day express as a number between 1 - 7 where 1 = monday and 7 = sunday
				$nextShipDay = 0; //The day of the next scheduled shipment
				$sortedDepartDaysArray = array();
				
				//Sort departing days for service
				foreach ($departDaysArray as $key => $day){
					switch ($day){
						
						case 'Mon':
							$sortedDepartDaysArray['Mon'] = 1; 
							
							break;
						case 'Tue':
							$sortedDepartDaysArray['Tue'] = 2;
							break;
						case 'Wed':
							$sortedDepartDaysArray['Wed'] = 3;
							break;
						case 'Thur':
							$sortedDepartDaysArray['Thur'] = 4;
							break;
						case 'Fri':
							$sortedDepartDaysArray['Fri'] = 5;
							break;
						case 'Sat':
							$sortedDepartDaysArray['Sat'] = 6;
							break;
						case 'Sun':
							$sortedDepartDaysArray['Sun'] = 7;
							break;
					}
				}
				$lastVal = $sortedDepartDaysArray[count($sortedDepartDaysArray) -1]; //The number associated  with the last day of shipment of shipment days.
				
				//Determines the next available ship day as a number 1-7 inclusive
				while (true) {
				
					if ($currDayAsNumber < current($sortedDepartDaysArray)){
						$nextShipDay = current($sortedDepartDaysArray);
						break;
					}
					else if ($currDayAsNumber == current ($sortedDepartDaysArray)){
						if (! next ( $sortedDepartDaysArray )) { //if it is not possible to go to next day in the array
							reset($sortedDepartDaysArray);
							$nextShipDay = current($sortedDepartDaysArray);
							break;
								
						}
						else {
							$nextShipDay = current($sortedDepartDaysArray);
							break;
						}
					}
					else {
				
						$nextShipDay = current($sortedDepartDaysArray);
						if ($lastVal == $nextShipDay){
							reset($sortedDepartDaysArray);
							$nextShipDay = current($sortedDepartDaysArray);
							break;
						}
						next($sortedDepartDaysArray);
				
					}
				
				}
				
				//Determines the next ship date
				$dayDiffrence = $nextShipDay - $currDayAsNumber;
				if ($dayDiffrence > 0){
				
					$dayToAdd = $dayDiffrence + $transitTime;
					$currDate->add ( new DateInterval ( 'P'.$dayToAdd.'D' ) );
				
				}
				else {
					$dayToAdd = (7 - $currDayAsNumber) + $nextShipDay + $transitTime;
					$currDate->add ( new DateInterval ( 'P'.$dayToAdd.'D' ) );
				
				}
				
				return array (
						'estDelDate' => $currDate->format ( 'Y-m-d' ),
						'transitTime' => $transitTime." day(s)"
				);
			
			case 'ItnlExpress':
			
				$currDate = new DateTime();
				$currDate->add ( new DateInterval ( 'P2D' ) );
				return array (
						'estDelDate' => $currDate->format ( 'Y-m-d' ),
						'transitTime' => "2 day(s)"
				);
			case 'CbeanExpressSameDay':
				
				$currDate = new DateTime();
				return array (
						'estDelDate' => $currDate->format ( 'Y-m-d' ),
						'transitTime' => "0 day(s)"
				);
				
			case 'CbeanExpressTwoDay':
				
				$currDate = new DateTime();
				$currDate->add ( new DateInterval ( 'P2D' ) );

				return array (
						'estDelDate' => $currDate->format ( 'Y-m-d' ),
						'transitTime' => "2 day(s)"
				);
				
			case 'SmallPkg':
				
				return array (
						'estDelDate' => "N/A",
						'transitTime' => "N/A"
				);
		}

	}
	
	/**
	 * Retruns the transit time of a shipment
	 * @param String $estDelDate
	 */
	private function getTransitTime($estDelDateAsStr){
		
		$currDate = new DateTime();
// 		$estDelDate = date_create($estDateDel);
		$estDelDate = date_create($estDelDateAsStr);
		
		$interval = date_diff($estDelDate, $currDate);
		
// 		$this->var_error_log(array("transitTimeVals" => array("strdate" => $estDelDate, "CurrDate" => $currDate, "estDate" => $) ))
		$days = $interval->format("%a");
		if ($days == 0) return "next day";
		return $interval->format("%a") . " day(s)";
		
	}
	// ==================Debugging Functions===========================
	private function var_error_log($object = null) {
		ob_start (); // start buffer capture
		var_dump ( $object ); // dump the values
		$contents = ob_get_contents (); // put the buffer into a variable
		ob_end_clean (); // end capture
		error_log ( $contents, 3, "switpac_api_log.log" ); // log contents of the result of var_dump( $object )
	}
	private function print_error_log($object = null) {
		error_log ( print_r ( $object, true ), 3, "switpac_api_log.log" );
	}
	// ==============End of Helper Functions-============
}

$soap_server = new \SoapServer ( 'SwiftPacShippingWDSL.wsdl', array (
		'cache_wsdl' => WSDL_CACHE_NONE 
) );

$soap_server->setClass ( SwiftPacShippingApi );
$soap_server->handle ();

?>