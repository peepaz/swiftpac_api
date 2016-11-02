<?php

namespace lib\SwiftPac;

use lib\IABOL\DataTypes\Packages;
use lib\SwiftPac\DataTypes\Package;
use lib\SwiftPac\DataTypes\PackagingType;
use lib\SwiftPac\DataTypes\Shipment;
use lib\SwiftPac\DataTypes\Address;
use lib\SwiftPac\db;
use lib\SwiftPac\DataTypes\InternationalRateDeliveryType;
use lib\SwiftPac\DataTypes\DomesticCommodityType;
use lib\SwiftPac\DataTypes\DomesticRateDeliveryType;
use lib\SwiftPac\DataTypes\CaribbeanRateDeliveryType;
use lib\DataTypes\SwiftPacRate;

class SwiftPacInHouseRates {
	/**
	 * A database connection link to user-defined SwiftPac Rates/API database functions.
	 *
	 * @var db
	 */
	protected $db;
	
	/**
	 * Data that is related to shipment be requested from client.
	 *
	 * @var Shipment
	 */
	protected $shipment;
	
	/**
	 * An array of package(s) that was gathered from the request.
	 *
	 * @var Package Array
	 */
	protected $packages;
	
	/**
	 * The sum total of the actual weight of all the packages in the shipment.
	 *
	 * @var double
	 */
	protected $sumPkgWeight = 0.00;
	
	/**
	 * The sum total of the volulmetric weight of all the packages in the shipment.
	 *
	 * @var double
	 */
	protected $sumVolPkgWeight = 0;
	
	/**
	 * The sum total of the express volulmetric weight of all the packages in the shipment.
	 *
	 * @var double
	 */
	protected $expressSumVolPkgWeight = 0;
	
	/**
	 * The shipment weight that has been determnine to be used in calculation of Air, Ocean, Small Pacakge and MailBox rates.
	 *
	 * @var double
	 */
	protected $chargeableWeight = 0;
	
	/**
	 * The chargeable package weight of packages that are not classified with package type of Barrels or Containers.
	 *
	 * @var integer
	 */
	protected $nonBarrelOrContainerChargeableWeight = 0;
	
	/**
	 * The actual package weight of packages that are not classified with package type of Barrels or Containers.
	 *
	 * @var integer
	 */
	protected $nonBarrelOrContainerActualWeight = 0;
	
	/**
	 * The volumetric package weight of packages that are not classified with package type of Barrels or Containers.
	 *
	 * @var integer
	 */
	protected $nonBarrelOrContainerVolWeight = 0;
	
	/**
	 * The package weight that has been determnine to be used in calculation of Express rates.
	 *
	 * @var double
	 */
	protected $expressChargeableWeight = 0;
	
	/**
	 * The difference between the sum total volumetric weight and the actual weight of the shipment.
	 *
	 * @var double
	 */
	protected $weightDifference = 0;
	
	/**
	 * The celing value of the sum total of the actual shipment weight.
	 *
	 * @var double
	 */
	protected $actualWeight = 0;
	
	/**
	 *
	 * @var unknown
	 */
	protected $cntHazardous = 0;
	
	/**
	 * The sum total of the notified value of each pcakge.
	 *
	 * @var double
	 */
	protected $sumPackageValue = 0;
		
	// ====================== Extra Fees==============================================
	/**
	 * The base rate factor of insurance to be applied to each package.
	 *
	 * @var double
	 */
	protected $insurance = 1.25;
	
	/**
	 * The base rate factor of insurance to be applied to each package destined for caribbean services.
	 *
	 * @var double
	 */
	protected $caribbeanInsurance = 1.25;
	
	/**
	 * The cost to pickup a package from a residential location.
	 *
	 * @var double
	 */
	const RESIDENTIAL_PICKUP_COST = 5.00;
	
	/**
	 * Determines whether or not a package has been flagged for residential pickup.
	 *
	 * @var boolean
	 */
	protected $residentialPickUp = false;
	
	/**
	 * The cost to deliver a package to a residential location.
	 *
	 * @var double
	 */
	const RESIDENTIAL_DELIVERY_COST = 5.00;
	
	/**
	 * Determines whether or not a package bas been flagged for residential delivery.
	 *
	 * @var boolean
	 */
	protected $residentialDelivery = false;
	
	/**
	 * A commodity cost that is used in the freight calculation for US MailBox and Small Packages.
	 *
	 * @var double
	 */
	protected $smallpkgCommodityCost;
	
	/**
	 * A commodity cost that is used in the freight calculation for Express Packages.
	 *
	 * @var double
	 */
	protected $expressCommodityCost;
	
	/**
	 * A commodity cost that is used in the freight calculation for Air/Ocean Cargo.
	 *
	 * @var double
	 */
	protected $airOceanCommodityCost;
	
	/**
	/**
	 * A commodity cost that is used in the freight calculation for US Domestic Cargo.
	 *
	 * @var double
	 */
	protected $usDomesticCommodityCost;
	/**
	 * A domestic cost that is used in the freight calculation for the US MailBox and Small Packages.
	 *
	 * @var double
	 */
	protected $smallPkgDomesticCost;
	
	// ===============Package Type Count==============================================================
	/**
	 * The number of packages that uses a Barrel as its packaging type
	 *
	 * @var integer
	 */
	protected $barrelPkgs = 0;
	
	/**
	 * The number of packages that uses an E-Container as its packaging type
	 *
	 * @var integer
	 */
	protected $econtainerPkgs = 0;
	
	/**
	 * The number of packages that uses an EH-Container as its packaging type
	 *
	 * @var integer
	 */
	protected $ehcontainerPkgs = 0;
	
	/**
	 * The number of packages that are not classified as Barrels Or Containers its packaging type
	 *
	 * @var integer
	 */
	protected $nonBarrelOrContainerPkgs = 0;
	
	/**
	 * The number of packages that uses a letter as its packaging type
	 *
	 * @var integer
	 */
	protected $letterPkgs = 0;
	
	/**
	 * The number of packages that uses a Document as its packaging type
	 *
	 * @var integer
	 */
	protected $documentPkgs = 0;
	
	/**
	 * The number of packages that are not classified as Letters or Document as its packaging type
	 *
	 * @var integer
	 */
	protected $nonletterORdocumentPgks = 0;
	
	// ===========Package Type Item Quantity Count====================================
	/**
	 * The number of items that are in packages of a barrel type.
	 *
	 * @var integer
	 */
	protected $barrelItemQty = 0;
	
	/**
	 * The number of items that are in packages of an E-Container type.
	 *
	 * @var integer
	 */
	protected $econtainerItemQty = 0;
	
	/**
	 * The number of items that are in packages of an EH-Container type.
	 *
	 * @var integer
	 */
	protected $ehcontainerItemQty = 0;
	
	/**
	 * The number of items that are not in packages of a Barrels or Containers types.
	 *
	 * @var integer
	 */
	protected $nonBarrelOrContainerItemQty = 0;
	
	/**
	 * The number of items that are in packages of a Letter type.
	 *
	 * @var integer
	 */
	protected $letterItemQty = 0;
	
	/**
	 * The number of items that are in packages of a Document type.
	 *
	 * @var integer
	 */
	protected $documentItemQty = 0;
	
	/**
	 * The number of items that are not in packages of a Letter or Document type.
	 *
	 * @var integer
	 */
	protected $nonletterOrDocumentItemQty = 0;
	
	// =====================Hazardous Costs========================================
	/**
	 * The basic rate that is used to calcualte the cost of each hazardous package.
	 *
	 * @var double
	 */
	const HAZARDOUS_RATE_PER_PACKAGE = 75.00;
	
	/**
	 * A factor that is used in the calculation of a hazardous package.
	 *
	 * @var double
	 */
	const HAZARDOUS_RATE_FACTOR = 1.5;
	
	/**
	 * The number of hazardous barrel packges in a specified shipment.
	 *
	 * @var integer
	 */
	protected $barrelHazardPkgs = 0;
	
	/**
	 * The number of hazardous E-Container packges in a specified shipment.
	 *
	 * @var integer
	 */
	protected $econtainerHazardPkgs = 0;
	
	/**
	 * The number of hazardous EH-Container packges in a specified shipment.
	 *
	 * @var integer
	 */
	protected $ehcontainerHazardPkgs = 0;
	
	/**
	 * The number of hazardous Non-Barrel or Non-Container packges in a specified shipment.
	 *
	 * @var integer
	 */
	protected $nonBarrelOrContainerHazardPkgs = 0;
	
	/**
	 * The number of hazardous Letter packges in a specified shipment.
	 *
	 * @var integer
	 */
	protected $letterHazardPkgs = 0;
	
	/**
	 * The number of hazardous Document packges in a specified shipment.
	 *
	 * @var integer
	 */
	protected $documentHazardPkgs = 0;
	
	/**
	 * The number of hazardous Non-Letter or Non-Document packges in a specified shipment.
	 *
	 * @var integer
	 */
	protected $nonletterORdocumentHazardPgks = 0;
	
	/**
	 * The total number of hazardous packges in a specified shipment.
	 *
	 * @var integer
	 */
	protected $totalHazardousPkgCount = 0;
	
	// Pickup location Costs
	const FORK_LIFT_BASE_FEE = 25.00;
	
	// ===============End of Extra Fees================
	
	// ================MailBox=================

	/**
	 * Maximum Poundage for mailbox service
	 * @var integer
	 */
	const MAILBOX_MAX_POUNDAGE = 150;
	/**
	 * Minium Poundage for mailbox service
	 */
	const MAILBOX_MIN_POUNDAGE = 0;
	/**
	 * 
	 * @var unknown
	 */
	protected $smallPackageRates;
	
	/**
	 * The cost of freight without any additional fees.
	 *
	 * @var number
	 */
	protected $basicUSMailBoxFreightCost;
	protected $basicAirServiceFreightCost;
	
	/**
	 * Total freight cost for a US MailBox shipment.
	 *
	 * @var number
	 */
	protected $usMailBoxFreightTotal;
	protected $airServiceFreightTotal;
	
	// ===================Express=====================
	protected $expressNonletterORdocumentRates;
	protected $expressDocumentRates;
	protected $expressLetterRates;
	
	//===============Caribbean Express====================	
	protected $caribbeanSameDayExpressNonletterORdocumentRates;
	protected $caribbeanSameDayExpressDocumentRates;
	protected $caribbeanSameDayExpressLetterRates;
	
	protected $caribbeanTwoDayExpressNonletterORdocumentRates;
	protected $caribbeanTwoDayExpressDocumentRates;
	protected $caribbeanTwoDayExpressLetterRates;
	
	// =================Ocean==========================
	protected $nonBarrelContainerOceanRates;
	protected $barrelOceanRates;
	protected $econtainerOceanRates;
	protected $ehcontainerOceanRates;
	protected $barrelDomesticCost = 0;
	protected $airOceanDomesticCost = 0;
	protected $econtainerDomesticCost = 0;
	protected $ehcontainerDomesticCost = 0;
	protected $domesticCostAirOceanEC = 0;
	protected $domesticCostAirOceanEHC = 0;
	protected $sumCubicFeetBarrel;
	protected $sumCubicFeetEcontainer;
	protected $sumCubicFeetEHcontainer;
	protected $sumCubicFeetNonBarrelContainer;
	
	//==================Air =======================
	/**
	 * Determines if a packages contains a barrel type
	 * @var boolean
	 */
	protected $barrelTypeCheck = false;
	
	
	//===================Us Domestic===============
	protected $usDomesticCost;
	
	// ================Total Shipment Charges==================
	/**
	 * Total shipment charges for a US MailBox shipment.
	 *
	 * @var array
	 */
	protected $totalUSMailBoxShipmentCharges;
	
	protected $totalAirServiceShipmentCharges;
	
	/**
	 * Total shipment charges for a Small Packge shipment.
	 *
	 * @var array
	 */
	protected $totalSmallPackageShipmentCharges;
	
	/**
	 * Total shipment charges for an Express shipment.
	 *
	 * @var array
	 */
	protected $totalExpressShipmentCharges;
	/**
	 * Total shipment charges for an Caribbean Same Day Express shipment.
	 *
	 * @var array
	 */
	protected $totalCaribbeanSameDayExpressShipmentCharges;
	/**
	 * Total shipment charges for an Caribbean Two Day Express shipment.
	 *
	 * @var array
	 */
	protected $totalCaribbeanTwoDayExpressShipmentCharges;
	
	/**
	 * Total shipment charges for an Ocean shipment.
	 *
	 * @var array
	 */
	protected $totalOceanShipmentCharges;
	
	/**
	 * Total shipment charges of a Barrel Ocean shipment
	 */
	protected  $totalBarrelOceanShipmentCharges;
	
	/**
	 * Total shipment charges of an Air shipment
	 * @var unknown
	 */
	protected $totalAirShipmentCharges;
	/**
	 * Total shipment charges of a Standard Domestic shipment
	 * @var unknown
	 */
	protected $totalDomesticStdShipmentCharges;
	/**
	 * Total shipment charges of an Expedited Domestic shipment
	 * @var unknown
	 */
	protected $totalDomesticExpediteShipmentCharges;
	/**
	 * Total shipment charges for a Full Service Barrel Ocean shipment
	 */
	protected $totalFullServiceBarrelOceanShipmentCharges;
	
	//===============End of Total Shipment Charges===============================
	
	/**
	 * Determines if shipment is being shipped from wharehouse location
	 *
	 * @var string
	 */
	protected $isWarehouseZip = false;
	/**
	 * Warehouse Zip
	 * @var String
	 */
	static protected $warehouseAddrLine1= "6948 NW 50th";
	static protected $warehouseCity= "Miami";
	static protected $warehouseCountryName= "United States";
	static protected $warehouseZip = "33166";
	static protected $warehouseCountryCode = "US";
	
	
	
	

	public function __construct($params) {
		$this->shipment = $params->rateRequest->rate->requestDetail->shipmentList->shipment;
		$this->db = new db (); // new connection to SwiftPac Api Database
		                       
		// Determines if shipment is being shipped from warehouse
		if ($this->shipment->shipFrom->zip == $this->getWarehouseAddr()->zip)
			$this->isWarehouseZip = true;
			
			// Ensure packages are loaded in an array
		$this->packages = $this->shipment->packageList->packages;
		if (! is_array ( $this->packages ))
			$this->packages = array (
					$this->packages 
			); // places packges in an array
				   
		// Get the sum of packge actual, volumetric and express volumetric weight along with the hazardous counts, .
		foreach ( $this->packages as $package ) {
			
			// set actual weight
			$this->sumPackageValue += $package->notifiedValue;
			$this->sumPkgWeight += $package->weight;
			
			// set volumetric sum weight
			if (! $package->itemQty)
				$pkgItemQty = 1;
			else
				$pkgItemQty = $package->itemQty;
			$volWeight = number_format ( ((($package->dimensions->length * $package->dimensions->width * $package->dimensions->height) / 166) * $pkgItemQty), 2, '.', '' );
			$this->sumVolPkgWeight += $volWeight;
			
			// set express volumteric sum weight
			$expressVolWeight = number_format ( ((($package->dimensions->length * $package->dimensions->width * $package->dimensions->height) / 133) * $pkgItemQty), 2, '.', '' );
			$this->expressSumVolPkgWeight += $expressVolWeight;
			
			//calcuate the cubic feet of the package
			$cubicFeet = number_format ( ((($package->dimensions->length * $package->dimensions->width * $package->dimensions->height) / 1728) * $pkgItemQty), 2, '.', '' );
			
			//Get package type
			$packageType = $package->packagingType;
			if ($packageType == PackagingType::SWIFTPAC_BARREL) $this->barrelTypeCheck = true; //use to avoid air cargo calculations
			// Set non-barrel or container Actual and Volumetric Weight
			if (! ($packageType == PackagingType::SWIFTPAC_BARREL || $packageType == PackagingType::SWIFTPAC_E_CONTAINER || $packageType == PackagingType::SWIFTPAC_EH_CONTAINER)) {
				
				$this->nonBarrelOrContainerActualWeight += $package->weight;
				$this->nonBarrelOrContainerVolWeight += $volWeight;
			}
			
			// get hazardous and non-hazadous count of package type along with the number of items in each package.			
			switch ($packageType) {
				
				case PackagingType::SWIFTPAC_BARREL :
					
					if ($package->hazardous) {
						$this->barrelHazardPkgs ++;
						$this->totalHazardousPkgCount ++;
						$this->nonletterORdocumentHazardPgks ++;
					}
					$this->barrelPkgs ++;
					$this->barrelItemQty = $package->itemQty;
					$this->nonletterORdocumentPgks ++;
					$this->nonletterOrDocumentItemQty = $package->itemQty;
					$this->sumCubicFeetBarrel += ceil($this->sumCubicFeetBarrel + $cubicFeet);
					
					break;
				case PackagingType::SWIFTPAC_E_CONTAINER :
					
					if ($package->hazardous) {
						$this->econtainerHazardPkgs ++;
						$this->totalHazardousPkgCount ++;
						$this->nonletterORdocumentHazardPgks ++;
					}
					$this->econtainerPkgs ++;
					$this->econtainerItemQty = $package->itemQty;
					$this->nonletterORdocumentPgks ++;
					$this->nonletterOrDocumentItemQty = $package->itemQty;
					$this->sumCubicFeetEcontainer = ceil($this->sumCubicFeetEcontainer + $cubicFeet);
					break;
				
				case PackagingType::SWIFTPAC_EH_CONTAINER :
					
					if ($package->hazardous) {
						$this->ehcontainerHazardPkgs ++;
						$this->totalHazardousPkgCount ++;
						$this->nonletterORdocumentHazardPgks ++;
					}
					$this->ehcontainerPkgs ++;
					$this->ehcontainerItemQty = $package->itemQty;
					$this->nonletterORdocumentPgks ++;
					$this->nonletterOrDocumentItemQty = $package->itemQty;
					$this->sumCubicFeetEHcontainer = ceil($this->sumCubicFeetEHcontainer + $cubicFeet);
						
					
					break;
				case PackagingType::SWIFTPAC_DOCUMENT :
					if ($package->hazardous) {
						$this->documentHazardPkgs ++;
						$this->totalHazardousPkgCount ++;
						$this->nonBarrelOrContainerHazardPkgs ++;
					}
					$this->documentPkgs ++;
					$this->documentItemQty = $package->itemQty;
					$this->nonBarrelOrContainerPkgs ++;
					$this->nonBarrelOrContainerItemQty = $package->itemQty;
					
					break;
				case PackagingType::SWIFTPAC_LETTER :
					if ($package->hazardous) {
						$this->letterHazardPkgs ++;
						$this->totalHazardousPkgCount ++;
						$this->nonBarrelOrContainerHazardPkgs ++;
					}
					$this->letterPkgs ++;
					$this->letterItemQty = $package->itemQty;
					$this->nonBarrelOrContainerPkgs ++;
					$this->nonBarrelOrContainerItemQty = $package->itemQty;
				
				default :
					if ($package->hazardous) {
						$this->totalHazardousPkgCount ++;
						$this->nonBarrelOrContainerHazardPkgs ++;
						$this->nonletterORdocumentHazardPgks ++;
					}
					$this->nonBarrelOrContainerPkgs ++;
					$this->nonBarrelOrContainerItemQty = $package->itemQty;
					$this->nonletterORdocumentPgks ++;
					$this->nonletterOrDocumentItemQty = $package->itemQty;
					$this->sumCubicFeetNonBarrelContainer = ceil($this->sumCubicFeetNonBarrelContainer + $cubicFeet);
			}
		}
		
		//Update shipment weight
		$this->shipment->weight = $this->sumPkgWeight;
		
		
		// Set Insurance
		if ($this->sumPackageValue > 100) {
			
			$this->insurance = number_format ( (($this->insurance * $this->sumPackageValue) / 100), 2, '.', '' );
			$caribbean_insurance = number_format ( (($this->caribbeanInsurance * $this->sumPackageValue) / 100), 2, '.', '' );
		}
		// set the actual weight
		$this->actualWeight = ceil ( $this->sumPkgWeight );
		
		// Set the chargeable weight
		if ($this->sumPkgWeight >= $this->sumVolPkgWeight) {
			$this->chargeableWeight = ceil ( $this->sumPkgWeight );
		} else {
			$this->chargeableWeight = ceil ( $this->sumVolPkgWeight );
		}
		
		// set the chargeable express weight
		if ($this->sumPkgWeight >= $this->expressSumVolPkgWeight) {
			
			$this->expressChargeableWeight = ceil ( $this->sumPkgWeight );
		} else
			$this->expressChargeableWeight = ceil ( $this->expressSumVolPkgWeight );
			
			// Set Charageable weight for non barrel and containers packages
		if ($this->nonBarrelOrContainerActualWeight > $this->nonBarrelOrContainerVolWeight) {
			$this->nonBarrelOrContainerChargeableWeight = ceil ( $this->nonBarrelOrContainerActualWeight );
		} else
			$this->nonBarrelOrContainerChargeableWeight = ceil ( $this->nonBarrelOrContainerVolWeight );
			
			// Set Weight Difference between volumetric and actual package weight
		$this->weightDifference = number_format ( ($this->sumVolPkgWeight - $this->sumPkgWeight), 2, '.', '' );
		
		// Set residential pickup value
		if ($this->shipment->shipFrom->residentialFlag)
			$this->residentialPickUp = true;
			
			// Set residential delivery value
		if ($this->shipment->shipTo->residentialFlag)
			$this->residentialDelivery = true;
			
			// Calcuate domestic cost for small packages and us mailbox
		$this->calculateSmallPkgDomesticCost ();
	}
	
	// =====================SwiftPac Shipping Methods=====================
	/**
	 * Get the shipping charges using the Air Service
	 */
	public function getAirServiceCharges(){
		
		
		// Determine commodity for small packages / mailbox
		$this->calculateSmallPackgeMailBoxCommodityCost ();
		
		// Determine small package domestic cost;
		$this->calculateSmallPkgDomesticCost ();
		
		// Get established mailbox difference
		$mailboxWeightDifference = number_format ( $this->db->getMailBoxDifference ( $this->actualWeight )->difference, 2, '.', '' );
		
		// Determine Mailbox Chargeable Weight
		if ($this->weightDifference > $mailboxWeightDifference) {
			$mailBoxChargeableWeight = ceil ( $this->sumVolPkgWeight );
		} else {
			if ($this->sumPkgWeight <= '0.00') {
				$mailBoxChargeableWeight = ceil ( $this->sumVolPkgWeight );
			} else {
				$mailBoxChargeableWeight = ceil ( $this->sumPkgWeight );
			}
		}
// 		$this->var_error_log(array("MailBox WGT DIFFRENCE" => $mailboxWeightDifference, "wgt difference" => $this->weightDifference, "mailBoxchargeableWgt" => $mailBoxChargeableWeight));
		
		$destination = strtoupper($this->shipment->shipTo->countryCode);
		$origin= $this->shipment->shipFrom->zip;
				
		// MailBox/Air Cargo Calculation
		if ($origin == $this->getWarehouseAddr()->zip) { //Air service must originate from warehouse location
		
// 			$this->var_error_log(array("air svc shipmet" => $this->shipment));
				
			$this->totalAirServiceShipmentCharges = new \stdClass ();
				
			//Mailbox
			if ($this->chargeableWeight <= self::MAILBOX_MAX_POUNDAGE && $this->chargeableWeight > self::MAILBOX_MIN_POUNDAGE ){
				
				//Get Mailbox Rates
				$mailBoxRates = $this->db->getInternationalRates ( $origin, $destination, InternationalRateDeliveryType::US_MAILBOX);
// 				$this->(array("air svc mailbox" => $mailBoxRates));
				
				if (count ( $mailBoxRates ) == 1) $mailBoxRates = $mailBoxRates [0];
				if (empty ( $mailBoxRates )) {
					$airServiceFreightTotal = null;
					
					
				}
				else {
					$wgt = $mailBoxChargeableWeight;
					
					for($i = 0; $i < count ( $mailBoxRates ); $i ++) {
						if ($wgt <= 0) { // calculation is finished.
							break;
						} else {
							$poundRange = (($mailBoxRates [$i]->max_measure - $mailBoxRates [$i]->min_measure) + 1);
							$baseRate = $mailBoxRates [$i]->base_rate; // base rate associated with pound range
					
							// check to see wheather has exceed chargeableweight
							if ($wgt >= $poundRange) {
								$useWeight = $poundRange;
							} else {
								$useWeight = $wgt;
							}
							$mailBoxFreight = number_format ( ($mailBoxFreight + ($useWeight * $baseRate)), 2, '.', '' );
					
							$wgt = $wgt - $useWeight; // decrement chargeable weight by the amount used in freight calculation abouve
						}
					}
					if (($destination == "DM") && ($mailBoxChargeableWeight >= 2)) {
						$mailBoxFreight = number_format ( ($mailBoxFreight - 8), 2, '.', '' );
					} else {
						$mailBoxFreight = number_format ( ($mailBoxFreight), 2, '.', '' );
					}
					
					$this->basicAirServiceFreightCost = $mailBoxFreight; // used for other extra fees cost;
						
					if ($this->totalHazardousPkgCount > 0) { // not yet implemented
						$airServiceFreightTotal = number_format ( (($mailBoxFreight * 1.5) + (self::HAZARDOUS_RATE_PER_PACKAGE * $this->totalHazardousPkgCount)), 2, '.', '' );
						$hazardousCharge = $airServiceFreightTotal - $mailBoxFreight;
						$this->totalAirServiceShipmentCharges->fees ["Hazardous Fee"] = $hazardousCharge;
					} else {
						$airServiceFreightTotal = number_format ( ($mailBoxFreight), 2, '.', '' );
					}
						
					if ($origin != $this->getWarehouseAddr()->zip) {
						$airServiceFreightTotal = number_format ( ($airServiceFreightTotal + $this->smallPkgDomesticCost + $this->smallpkgCommodityCost), 2, '.', '' );
					} else {
						$airServiceFreightTotal = number_format ( ($airServiceFreightTotal + $this->smallpkgCommodityCost), 2, '.', '' );
					}
					
						
					$this->totalAirServiceShipmentCharges->rate = $airServiceFreightTotal;
					$this->totalAirServiceShipmentCharges->baseRate = $this->basicAirServiceFreightCost;
													
// 					$this->var_error_log(array("air svc totals" => $this->totalAirServiceShipmentCharges));
				}
				
			}
			//Air Cargo
			else {
				//Get Air Cargo Rates
				$airCargoRates = $this->db->getInternationalRates ( $origin, $destination, InternationalRateDeliveryType::AIR_CARGO);
				if (count ( $airCargoRates ) == 1) $airCargoRates = $airCargoRates [0];
				if (empty ( $airCargoRates )) {
					$airServiceFreightTotal = null;
						
				}
				else {
					$airCargoPerLb = (($airCargoRates->additional_rate + $airCargoRates->FSC) * $this->chargeableWeight);
					if ($airCargoPerLb >= $airCargoRates->base_rate){
						// 			        $airway = 'Yes';
						$airCargoBasic = number_format(($airCargoPerLb + $this->airOceanDomesticCost + $this->airOceanCommodityCost + 25),2,'.','');
						if($this->totalHazardousPkgCount> 0){
							$airCargoHazard = number_format(((($airCargoPerLb * 1.5) + (75.00 * $this->cntHazardous)) + $this->airOceanDomesticCost + $this->airOceanCommodityCost + 25),2,'.','');
							$this->totalAirServiceShipmentCharges->rate = $airCargoHazard;
							$this->totalAirServiceShipmentCharges->baseRate = $airCargoBasic;
							$this->totalAirServiceShipmentCharges->fees ["Hazardous Fee"] = $airCargoHazard - $airCargoBasic;
						} else {
							// 					$airCargo = $airCargoBasic;
							$this->totalAirServiceShipmentCharges->rate = $airCargoBasic;
							$this->totalAirServiceShipmentCharges->baseRate = $airCargoBasic;
							// 					$airCargo = number_format(($airCargoPerLb + $this->airOceanDomesticCost + $this->airOceanCommodityCost + 25),2,'.','');
						}
					} else {
						// 				$airway = 'No';
						$airCargoBasic = number_format(($airCargoRates->base_rate + $this->airOceanDomesticCost + $this->airOceanCommodityCost),2,'.','');
						if($this->totalHazardousPkgCount > 0){
							$airCargoHazard = number_format(((($airCargoRates->base_rate * 1.5) + (75.00 * $this->cntHazardous)) + $this->airOceanDomesticCost + $this->airOceanCommodityCost),2,'.','');
							$this->totalAirServiceShipmentCharges->rate = $airCargoHazard;
							$this->totalAirServiceShipmentCharges->baseRate = $airCargoBasic;
							$this->totalAirServiceShipmentCharges->fees ["Hazardous Fee"] = $airCargoHazard - $airCargoBasic;
						} else {
							$this->totalAirServiceShipmentCharges->rate = $airCargoBasic;
							$this->totalAirServiceShipmentCharges->baseRate = $airCargoBasic;
							// 					$airCargo =$airCargoBasic;
							// 					$airCargo = number_format(($rate->base_rate + $this->airOceanDomesticCost + $this->airOceanCommodityCost),2,'.','');
						}
					}
					
				}
				
			}
			//Get Extra fees
			$this->totalAirServiceShipmentCharges = $this->addExtraFees($this->totalAirServiceShipmentCharges);
// 			$this->var_error_log(array("new air servce" => $this->totalAirServiceShipmentCharges));
		
		}
		else {
			$airServiceFreightTotal = null;
			$this->totalAirServiceShipmentCharges = $airServiceFreightTotal;
		}
		
		return $this->totalAirServiceShipmentCharges;
		
	}
	/**
	 * Gets the shipment charges using the US mailbox service
	 * 
	 * @return array
	 */
	public function getMailBoxCharges() {
		
// 		// Determine commodity for small packages / mailbox
// 		$this->calculateSmallPackgeMailBoxCommodityCost ();
		
// 		// Determine small package domestic cost;
// 		$this->calculateSmallPkgDomesticCost ();
		
// 		// Get established mailbox difference
// 		$mailboxWeightDifference = number_format ( $this->db->getMailBoxDifference ( $this->actualWeight )->difference, 2, '.', '' );
		
// 		// Determine Mailbox Chargeable Weight
// 		if ($this->weightDifference > $mailboxWeightDifference) {
// 			$mailBoxChargeableWeight = ceil ( $this->sumVolPkgWeight );
// 		} else {
// 			if ($this->sumPkgWeight <= '0.00') {
// 				$mailBoxChargeableWeight = ceil ( $this->sumVolPkgWeight );
// 			} else {
// 				$mailBoxChargeableWeight = ceil ( $this->sumPkgWeight );
// 			}
// 		}
		
// 		// Determine MailBox Rate details
// 		$destination = strtoupper($this->shipment->shipTo->countryCode);
// 		$origin = $this->shipment->shipFrom->zip;
		
// // 		$mailBoxRates = $this->db->getMailBoxRates ( $origin, $destination );
// 		$mailBoxRates = $this->db->getInternationalRates ( $origin, $destination, InternationalRateDeliveryType::US_MAILBOX);
// 		if (count ( $rates ) == 1) $rate = $rates [0];
		
// 		// MailBox Calculation
// 		if ($origin == $this->getWarehouseAddr()->zip) {
// 			if (empty ( $mailBoxRates )) {
// 				$usMailBoxFreightTotal = null;
// 			} else {
// 				if ($this->chargeableWeight > $this->mailBoxMaxPoundage || $this->chargeableWeight <= 0) {
// 					$usMailBoxFreightTotal = null;
// 				} else {
// 					$wgt = $mailBoxChargeableWeight;
// 					$usMailBoxFreightTotal = '0.00';
					
// 					for($i = 0; $i < count ( $mailBoxRates ); $i ++) {
// 						if ($wgt <= 0) { // calculation is finished.
// 							break;
// 						} else {
// 							$poundRange = (($mailBoxRates [$i]->max_measure - $mailBoxRates [$i]->min_measure) + 1);
// 							$baseRate = $mailBoxRates [$i]->base_rate; // base rate associated with pound range
							                                           
// 							// check to see wheather has exceed chargeableweight
// 							if ($wgt >= $poundRange) {
// 								$useWeight = $poundRange;
// 							} else {
// 								$useWeight = $wgt;
// 							}
// 							$usMailBoxFreight = number_format ( ($usMailBoxFreight + ($useWeight * $baseRate)), 2, '.', '' );
							
// 							$wgt = $wgt - $useWeight; // decrement chargeable weight by the amount used in freight calculation abouve
// 						}
// 					}
					
// 					// ===============Total Freight Cost and Fees========================
					
// 					if (($destination == "DM") && ($mailBoxChargeableWeight >= 2)) {
// 						$usMailBoxFreight = number_format ( ($usMailBoxFreight - 8), 2, '.', '' );
// 					} else {
// 						$usMailBoxFreight = number_format ( ($usMailBoxFreight), 2, '.', '' );
// 					}
					
// 					$this->basicUSMailBoxFreightCost = $usMailBoxFreight; // used for other extra fees cost;
// 					$this->totalUSMailBoxShipmentCharges = new \stdClass ();
// 					$this->totalUSMailBoxShipmentCharges->baseRate = $this->basicUSMailBoxFreightCost;
					
// 					if ($this->totalHazardousPkgCount > 0) { // not yet implemented
// 						$usMailBoxFreightTotal = number_format ( (($usMailBoxFreight * 1.5) + (self::HAZARDOUS_RATE_PER_PACKAGE * $this->totalHazardousPkgCount)), 2, '.', '' );
// 						$hazardousCharge = $usMailBoxFreightTotal - $usMailBoxFreight;
// 						$this->totalUSMailBoxShipmentCharges->fees ["Hazardous Fee"] = $hazardousCharge;
// 					} else {
// 						$usMailBoxFreightTotal = number_format ( ($usMailBoxFreight), 2, '.', '' );
// 					}
					
// 					if ($origin != $this->getWarehouseAddr()->zip()) {
// 						$usMailBoxFreightTotal = number_format ( ($usMailBoxFreightTotal + $this->smallPkgDomesticCost + $this->smallpkgCommodityCost), 2, '.', '' );
// 					} else {
// 						$usMailBoxFreightTotal = number_format ( ($usMailBoxFreightTotal + $this->smallpkgCommodityCost), 2, '.', '' );
// 					}
					
// 					$this->totalUSMailBoxShipmentCharges->rate = $usMailBoxFreightTotal;
					
					
// 					$this->totalUSMailBoxShipmentCharges = $this->addExtraFees ( $this->totalUSMailBoxShipmentCharges );
// 				}
// 			}
// 		} else {
// 			$usMailBoxFreightTotal = null;
// 			$this->totalUSMailBoxShipmentCharges = $usMailBoxFreightTotal;
// 		}
// 		return $this->totalUSMailBoxShipmentCharges;
	}
	
	/**
	 * Gets the shipment charges using the Ocean service
	 * 
	 * @return array
	 */
	public function getOceanCharges() {
		$origin = $this->shipment->shipFrom->zip;
		$destination = strtoupper($this->shipment->shipTo->countryCode);
		
		if ($origin == $this->getWarehouseAddr()->zip) { //Ocean service must originate from warehouse location
			
// 			$this->var_error_log(array("Ocean Shipment" => $this->shipment));
			//Calcualte the necessary domestics costs
			$this->calculateOceanBarrelDomesticCost();
			$this->calculateAirOceanDomesticCost();
			$this->calculateAirOceanCommodityCost();
			
			//Get ocean charges
			$barrelOcean = 0;
			$ehcontainerOcean = 0;
			$econtainerOcean = 0;
			$nonBarrelOrContainer = 0;
			
			if ($this->barrelPkgs > 0)
				$barrelOcean = $this->getBarrelOceanCharges ( $origin, $destination, InternationalRateDeliveryType::OCEAN_CARGO_BARREL );
			
			if ($this->ehcontainerPkgs > 0)
				$ehcontainerOcean = $this->getEHContainerOceanCharges ( $origin, $destination, InternationalRateDeliveryType::OCEAN_CARGO_EH_CONTAINER );
			
			if ($this->econtainerPkgs > 0)
				$econtainerOcean = $this->getEContainerOceanCharges ( $origin, $destination, InternationalRateDeliveryType::OCEAN_CARGO_E_CONTAINER );
			
			if ($this->nonBarrelOrContainerPkgs > 0 && $this->sumCubicFeetNonBarrelContainer > 0)
				$nonBarrelOrContainer = $this->getNonBarrelOrContainerCharges($origin,$destination,InternationalRateDeliveryType::OCEAN_CARGO,$this->sumCubicFeetNonBarrelContainer,$this->sumCubicFeetNonBarrelContainer);
				
				$this->var_error_log(array("nonbarrel"=>$nonBarrelOrContainer));
			//Calcualte total ocean freight
			$totalOceanFreight = number_format ( ($barrelOcean + $econtainerOcean + $ehcontainerOcean + $nonBarrelOrContainer), 2, '.', '' );
		
		
			$fullServiceBarrelOceanFreight = number_format ( ($totalOceanFreight + (50.00 * $this->barrelItemQty)), 2, '.', '' );
			$this->totalBarrelOceanShipmentCharges = new \stdClass();
// 			if ($this->nonBarrelOrContainerItemQty > 0 || $this->ehcontainerItemQty > 0 || $this->econtainerItemQty > 0) {
			if ($nonBarrelOrContainer > 0 || $ehcontainerOcean > 0 || $econtainerOcean > 0) {
				$this->totalOceanShipmentCharges->rate = $totalOceanFreight;
				$this->totalOceanShipmentCharges->baseRate = $totalOceanFreight;
				$this->totalOceanShipmentCharges = $this->addExtraFees($this->totalOceanShipmentCharges);
				
// 				$this->var_error_log(array("oceantest"=>$this->totalOceanShipmentCharges, "items"));
				return array (
						'totalOceanShipmentCharges' =>  $this->totalOceanShipmentCharges
				);
					
			}
			else if ($this->barrelItemQty > 0){
				$this->totalFullServiceBarrelOceanShipmentCharges->rate = $fullServiceBarrelOceanFreight;
				$this->totalFullServiceBarrelOceanShipmentCharges->baseRate = $fullServiceBarrelOceanFreight;
				$this->totalFullServiceBarrelOceanShipmentCharges = $this->addExtraFees($this->totalFullServiceBarrelOceanShipmentCharges,"barrel");
				
				$this->totalBarrelOceanShipmentCharges->rate = $totalOceanFreight;
				$this->totalBarrelOceanShipmentCharges->baseRate = $totalOceanFreight;
				$this->totalBarrelOceanShipmentCharges = $this->addExtraFees($this->totalBarrelOceanShipmentCharges,"barrel");
				
				return array (
						"totalFullServiceBarrelOceanShipmentCharges" => $this->totalFullServiceBarrelOceanShipmentCharges,
						"totalBarrelOceanShipmentCharges" => $this->totalBarrelOceanShipmentCharges
				);
			}
			else {
				return null; //An ocean packging type has not been selected
			}
		}
		else {
			return null; //Orign is not from warehouse
		}
	}
	protected function getBarrelOceanCharges($origin, $destination, $deliveryType) {
		$barrelOceanFreight = 0;
		$rates = $this->db->getInternationalRates ( $origin, $destination, $deliveryType );
		$this->barrelOceanRates = $rates[0];
		if (count ( $rates ) == 1)
			$rate = $rates [0];
		if (empty ( $rates )) {
			return null;
		} else {
			
			if ($this->barrelHazardPkgs > 0) {
				$barrelOceanFreight = number_format ( ((((($rate->base_rate + $rate->markup) *
						$this->barrelItemQty) * 1.5) + (75.00 * $this->barrelHazardPkgs)) + 
						($this->barrelDomesticCost + (($this->barrelDomesticCost * 0.8) * 
								($this->barrelItemQty - 1)))), 2, '.', '' );
			} else {
				$barrelOceanFreight = number_format ( ((($rate->base_rate + $rate->markup) *
						$this->barrelItemQty) + ($barrelDomesticCost + (($barrelDomesticCost * 0.8) * 
								($this->barrelItemQty - 1)))), 2, '.', '' );
			}
		}
		return $barrelOceanFreight;
	}
	protected function getEContainerOceanCharges($origin, $destination, $deliveryType) {
		$econtainerOceanFreight = 0;
		$rates = $this->db->getInternationalRates ( $origin, $destination, $deliveryType );
		$this->econtainerOceanRates  = $rates[0];
		if (count ( $rates ) == 1)
			$rate = $rates [0];
		if (empty ( $rates )) {
			return null;
		} else {
							
			if ($this->econtainerHazardPkgs > 0) {
				$econtainerOceanFreight = number_format ( ((((($this->sumCubicFeetEcontainer * $rate->additional_rate) + ($rate->markup * $this->econtainerItemQty)) * 1.5) + (75.00 * $this->econtainerHazardPkgs)) + $this->domesticCostAirOceanEC), 2, '.', '' );
			} else {
				$econtainerOceanFreight = number_format ( ((($this->sumCubicFeetEcontainer * $rate->additional_rate) + ($rate->markup * $this->econtainerItemQty)) + $this->domesticCostAirOceanEC), 2, '.', '' );
			}
		}
		return $econtainerOceanFreight;
		
	}
	protected function getEHContainerOceanCharges($origin, $destination, $deliveryType) {
		$ehcontainerOceanFreight = 0;
		$rates = $this->db->getInternationalRates ( $origin, $destination, $deliveryType );
		$this->econtainerOceanRates  = $rates[0];
		if (count ( $rates ) == 1)
			$rate = $rates [0];
		if (empty ( $rates )) {
			return null;
		} else {
			
			if ($this->ehcontainerHazardPkgs > 0) {
				$ehcontainerOceanFreight = number_format ( (((($rate->base_rate * $this->ehcontainerItemQty) * 1.5) + (75.00 * $this->ehcontainerHazardPkgs)) + $this->domesticCostAirOceanEHC), 2, '.', '' );
			} else {
				$ehcontainerOceanFreight = number_format ( (($rate->base_rate * $this->ehcontainerItemQty) + $this->domesticCostAirOceanEHC), 2, '.', '' );
			}
			
		}
		return $ehcontainerOceanFreight;
		
	}
	protected function getNonBarrelOrContainerCharges($origin, $destination, $deliveryType,$minMeasure,$maxMeasure){
		
		$nonBarrelContainerOceanFreight = 0;
		$rates = $this->db->getInternationalRates ( $origin, $destination, $deliveryType,$minMeasure,$maxMeasure );
		$this->var_error_log(array("oceanrates"=>$rates));
		$this->nonBarrelContainerOceanRates  = $rates[0];
		if (count ( $rates ) == 1)
			$rate = $rates [0];
		if (empty ( $rates )) {
			return null;
		} else {
			
			if ($this->nonBarrelOrContainerHazardPkgs > 0) {
				$nonBarrelContainerOceanFreight = number_format ( (((($rate->base_rate + ($this->sumCubicFeetNonBarrelContainer * $rate->additional_rate)) * 1.5) + (75.00 * $this->nonBarrelOrContainerHazardPkgs)) + $this->airOceanDomesticCost + $this->airOceanCommodityCost), 2, '.', '' );
			} else {
				$nonBarrelContainerOceanFreight = number_format ( (($rate->base_rate  + ($this->sumCubicFeetNonBarrelContainer * $rate->additional_rate)) + $this->airOceanDomesticCost + $this->airOceanCommodityCost), 2, '.', '' );
			}
		}
		return $nonBarrelContainerOceanFreight;
		
	}
	
	/**
	 * Get the shipment charges using the Air service
	 * 
	 * @return array
	 */
	public function getAirCharges() {
		
// 		if (!$this->barrelTypeCheck || $this->chargeableWeight > 0){
// 			$origin = $this->shipment->shipFrom->zip;
// 			$destination = strtoupper($this->shipment->shipTo->countryCode);
// 			$deliveryType = InternationalRateDeliveryType::AIR_CARGO;
// 			$rates = $this->db->getInternationalRates($origin, $destination, $deliveryType);	
			
// 			if (count ( $rates ) == 1)$rate = $rates [0];
// 				if (empty ( $rates )) {
// 					return null;
// 				}
// 				$this->totalAirShipmentCharges = new \stdClass();
				
// 			$airCargoPerLb = (($rate->additional_rate + $rate->FSC) * $this->chargeableWeight);
// 			if ($airCargoPerLb >= $rate->base_rate){
// // 			        $airway = 'Yes';
// 				$airCargoBasic = number_format(($airCargoPerLb + $this->airOceanDomesticCost + $this->airOceanCommodityCost + 25),2,'.','');
// 				if($this->totalHazardousPkgCount> 0){
// 						$airCargoHazard = number_format(((($airCargoPerLb * 1.5) + (75.00 * $this->cntHazardous)) + $this->airOceanDomesticCost + $this->airOceanCommodityCost + 25),2,'.','');
// 						$this->totalAirShipmentCharges->rate = $airCargoHazard;
// 						$this->totalAirShipmentCharges->baseRate = $airCargoBasic;
// 						$this->totalAirShipmentCharges->fees ["Hazardous Fee"] = $airCargoHazard - $airCargoBasic;
// 				} else {
// // 					$airCargo = $airCargoBasic;
// 					$this->totalAirShipmentCharges->rate = $airCargoBasic;
// 					$this->totalAirShipmentCharges->baseRate = $airCargoBasic;
// // 					$airCargo = number_format(($airCargoPerLb + $this->airOceanDomesticCost + $this->airOceanCommodityCost + 25),2,'.','');
// 				}
// 			} else {
// // 				$airway = 'No';
// 				$airCargoBasic = number_format(($rate->base_rate + $this->airOceanDomesticCost + $this->airOceanCommodityCost),2,'.','');
// 				if($this->totalHazardousPkgCount > 0){
// 					$airCargoHazard = number_format(((($rate->base_rate * 1.5) + (75.00 * $this->cntHazardous)) + $this->airOceanDomesticCost + $this->airOceanCommodityCost),2,'.','');
// 					$this->totalAirShipmentCharges->rate = $airCargoHazard;
// 					$this->totalAirShipmentCharges->baseRate = $airCargoBasic;
// 					$this->totalAirShipmentCharges->fees ["Hazardous Fee"] = $airCargoHazard - $airCargoBasic;
// 				} else {
// 					$this->totalAirShipmentCharges->rate = $airCargoBasic;
// 					$this->totalAirShipmentCharges->baseRate = $airCargoBasic;
// // 					$airCargo =$airCargoBasic;
// // 					$airCargo = number_format(($rate->base_rate + $this->airOceanDomesticCost + $this->airOceanCommodityCost),2,'.','');
// 				}
// 			}
// 			$this->totalAirShipmentCharges = $this->addExtraFees($this->totalAirShipmentCharges);
			
// 			return $this->totalAirShipmentCharges;
			
// 		}
// 		else return null;
			
	}
	/**
	 * Get the shipment charges using the Small Package service
	 * 
	 * @return array
	 */
	public function getSmallPackageCharges() {
		// Determine commodity for small packages / mailbox
		$this->calculateSmallPackgeMailBoxCommodityCost ();
		
		// Determine small package domestic cost;
		$this->calculateSmallPkgDomesticCost ();
		
		$origin = $this->shipment->shipFrom->zip;
		$destination = strtoupper($this->shipment->shipTo->countryCode);
		$chargeableWeight = $this->chargeableWeight;
		
		$rates = $this->db->getInternationalRates($origin, $destination, InternationalRateDeliveryType::SMALL_PACKAGE, $chargeableWeight, $chargeableWeight);
		if (count ( $rates ) == 1) {
			$rate = $rates [0];
			$this->smallPackageRates = $rate;
		}
		else {
		$this->smallPackageRates = $rates;
			
		}
				
		if (empty ( $rate )) {
			return null;
		} else {
			if ($origin != '33166') {
				if ($chargeableWeight <= 50) {
					// =============Total Small Package Frieight and Fees================
					$smallPackageBasicFreight = ($rate->base_rate + ($rate->additional_rate * $chargeableWeight) + $rate->markup);
					
					$this->totalSmallPackageShipmentCharges = new \stdClass ();
					$this->totalSmallPackageShipmentCharges->baseRate = $smallPackageBasicFreight;
					
					if ($this->totalHazardousPkgCount > 0) {
						$smallPackageFreight = number_format ( (($smallPackageBasicFreight * self::HAZARDOUS_RATE_FACTOR) + (self::HAZARDOUS_RATE_PER_PACKAGE * $this->totalHazardousPkgCount)), 2, '.', '' );
						$hazardousFee = $smallPackageFreight - $smallPackageBasicFreight;
						$this->totalSmallPackageShipmentCharges->fees ["Hazardous Fee"] = $hazardousFee;
					} else {
						$smallPackageFreight = number_format ( $smallPackageBasicFreight, 2, '.', '' );
					}
					$smallPackageFreightTotal = number_format ( ($smallPackageFreight + $this->smallPkgDomesticCost + $this->smallpkgCommodityCost), 2, '.', '' );
					
					$this->totalSmallPackageShipmentCharges->rate = $smallPackageFreightTotal;
					$this->totalSmallPackageShipmentCharges = $this->addExtraFees ( $this->totalSmallPackageShipmentCharges );
					
					return $this->totalSmallPackageShipmentCharges;
				} else {
					return null;
				}
			} else {
				return null;
			}
		}
	}
	/**
	 * Get the shipment charges using the International Express service
	 * 
	 * @return array
	 */
	public function getExpressCharges() {
		if ($this->chargeableWeight > 40) {
			return null;
		} else {
			
			$origin = $this->shipment->shipFrom->zip;
			$destination = strtoupper($this->shipment->shipTo->countryCode);
			$expressChargeableWeight = $this->expressChargeableWeight;
			
			$this->calculateExpressCommodityCost ();
			
			$this->totalExpressShipmentCharges = new \stdClass ();
			
			if ($this->nonletterORdocumentPgks > 0)
				$expressNonLetterOrDocumentCharges = $this->getExpressNonLetterOrDocumentCharges ( $origin, $destination, InternationalRateDeliveryType::EXPRESS, $expressChargeableWeight, $expressChargeableWeight );
			
			if ($this->letterPkgs > 0)
				$expressFreightLetterCharges = $this->getExpressLetterCharges ( $origin, $destination, InternationalRateDeliveryType::EXPRESS_LETTER );

			if ($this->documentPkgs > 0)
				if ($this->documentItemQty <= 5 && $this->documentItemQty > 0) {
					$documentDeliveryType = str_replace ( '1', count ( $this->documentItemQty ), InternationalRateDeliveryType::EXPRESS_DOCUMENT_1 );
					$expressFreightDocumentCharges = $this->getExpressDocumentCharges ( $origin, $destination, $documentDeliveryType );
					$expressNonLetterOrDocumentCharges = $this->getExpressNonLetterOrDocumentCharges ( $origin, $destination, InternationalRateDeliveryType::EXPRESS, $expressChargeableWeight, $expressChargeableWeight );

				}
			
			$expressFreight = number_format ( ($expressFreightLetterCharges + $expressFreightDocumentCharges + $expressNonLetterOrDocumentCharges), 2, '.', '' );
			
			if ($expressFreight == '0.00')
				return null;
			
			if (! $this->isWarehouseZip) {
				$domesticExpressRates = $this->db->getDomesticExpressRates ( $origin, WarehouseLocation::MIAMI_FL, $this->actualWeight );
				
				$expressFreightTotal = number_format ( ($expressFreight + $domesticExpressRates->base_rate + $this->expressCommodityCost), 2, '.', '' );
			} else {
				$expressFreightTotal = number_format ( ($expressFreight + $this->expressCommodityCost), 2, '.', '' );
			}
			
			$this->totalExpressShipmentCharges->rate += $expressFreightTotal;
			$this->totalExpressShipmentCharges = $this->addExtraFees ( $this->totalExpressShipmentCharges );
			
			return $this->totalExpressShipmentCharges;
		}
	}
	protected function getExpressLetterCharges($origin, $destination, $deliveryType) {
		$rates = $this->db->getInternationalRates ( $origin, $destination, $deliveryType );
		if (count ( $rates ) == 1)
			$rate = $rates [0];
		if (empty ( $rate )) {
			$expressFreightLetter = 0;
		} else {
			$this->expressLetterRates = $rates;
			$expressBasicFreightLetter = $rate->base_rate + $rate->markup;
			if ($this->letterHazardPkgs > 0) {
				$expressFreightLetter = number_format ( (((($expressBasicFreightLetter) * $this->letterItemQty) * 1.5) + (75.00 * $this->letterHazardPkgs)), 2, '.', '' );
				$hazardousFee = $expressFreightLetter - $expressBasicFreightLetter;
				$this->totalExpressShipmentCharges->fees ["Hazardous Fee"] += $hazardousFee;
			} else {
				$expressFreightLetter = number_format ( (($expressBasicFreightLetter) * $this->letterItemQty), 2, '.', '' );
				$this->totalExpressShipmentCharges->baseRate += $expressBasicFreightLetter;
			}
			$this->totalExpressShipmentCharges->baseRate += $expressBasicFreightLetter;
		}
		return $expressFreightLetter;
	}
	protected function getExpressDocumentCharges($origin, $destination, $deliveryType) {
		$rates = $this->db->getInternationalRates ( $origin, $destination, $deliveryType );
		if (count ( $rates ) == 1)
			$rate = $rates [0];
		
		if (empty ( $rates )) {
			$expressFreightDocument = 0;
		} else {
			$this->expressDocumentRates = $rates;
			$expressBasicFreightDocument = $rate->base_rate + $rate->markup;
			if ($this->documentHazardPkgs > 0) {
				$expressFreightDocument = number_format ( ((($expressBasicFreightDocument) * 1.5) + (75.00 * $this->documentHazardPkgs)), 2, '.', '' );
				$hazardousFee = $expressFreightDocument - $expressBasicFreightDocument;
				$this->totalExpressShipmentCharges->fees ["Hazardous Fee"] += $hazardousFee;
			} else {
				$expressFreightDocument = number_format ( ($expressBasicFreightDocument), 2, '.', '' );
			}
			$this->totalExpressShipmentCharges->baseRate += $expressBasicFreightDocument;
		}
		
		return $expressFreightDocument;
	}
	protected function getExpressNonLetterOrDocumentCharges($origin, $destination, $deliveryType, $minMeasure, $maxMeasure) {
		$rates = $this->db->getInternationalRates ( $origin, $destination, $deliveryType, $minMeasure, $maxMeasure );
		if (count ( $rates ) == 1)
			$rate = $rates [0];
		
		if (empty ( $rates )) {
			$expressFreightNonletterORdocument = 0;
		} else {
			$this->expressNonletterORdocumentRates = $rates;
			$expressBasicFreightNonletterORdocument = $rate->base_rate + $rate->markup;
			
			if ($this->nonletterORdocumentHazardPgks > 0) {
				$expressFreightNonletterORdocument = number_format ( (($expressBasicFreightNonletterORdocument * 1.5) + (75.00 * $this->nonletterORdocumentHazardPgks)), 2, '.', '' );
				$hazardousFee = $expressFreightNonletterORdocument - $expressBasicFreightNonletterORdocument;
				$this->totalExpressShipmentCharges->fees ["Hazardous Fee"] += $hazardousFee;
			} else {
				$expressFreightNonletterORdocument = number_format ( $expressBasicFreightNonletterORdocument, 2, '.', '' );
			}
			
			$this->totalExpressShipmentCharges->baseRate += $expressBasicFreightNonletterORdocument;
		}
		
		return $expressFreightNonletterORdocument;
	}
	
	/**
	 * Get the shipment charges for same day caribbean express service
	 * 
	 * @return array
	 */
	public function getCaribbeanSameDayExpressCharges() {
		if ($this->chargeableWeight > 50) {
			return null;
		} else {
// 			$origin = $this->shipment->shipFrom->zip;
			$destination = strtoupper($this->shipment->shipTo->countryCode);
			$expressChargeableWeight = $this->expressChargeableWeight;
			
			$this->totalCaribbeanSameDayExpressShipmentCharges = new \stdClass ();
			
			if ($this->nonletterORdocumentPgks > 0)
				$caribbeanExpressNonLetterOrDocumentCharges = 
					$this->getCaribbeanSameDayExpressNonLetterOrDocumentCharges($destination, CaribbeanRateDeliveryType::CARIBBEAN_EXPRESS_SAME_DAY_NON_DOC_LTR, $expressChargeableWeight, $expressChargeableWeight);
			
			if ($this->letterPkgs > 0)
				$caribbeanExpressLetterCharges = 
					$this->getCaribbeanSameDayExpressLetterCharges($destination, CaribbeanRateDeliveryType::CARIBBEAN_EXPRESS_SAME_DAY_LTR, $expressChargeableWeight, $expressChargeableWeight);
			
			if ($this->documentPkgs > 0){
				$documentDeliveryType = CaribbeanRateDeliveryType::CARIBBEAN_EXPRESS_SAME_DAY_DOC . $this->documentItemQty;
				$caribbeanExpressDocumentCharges = $this->getCaribbeanSameDayExpressDocumentCharges($destination, $documentDeliveryType);
				
				$caribbeanExpressNonLetterOrDocumentCharges =
				$this->getCaribbeanSameDayExpressNonLetterOrDocumentCharges($destination, CaribbeanRateDeliveryType::CARIBBEAN_EXPRESS_SAME_DAY_NON_DOC_LTR, $expressChargeableWeight, $expressChargeableWeight);
					
			}
			
			$caribbeanExpressSameDayTotal = number_format($caribbeanExpressNonLetterOrDocumentCharges + $caribbeanExpressLetterCharges + $caribbeanExpressDocumentCharges, 2, '.','');
				
			
			$this->totalCaribbeanSameDayExpressShipmentCharges->rate += $caribbeanExpressSameDayTotal;
			$this->totalCaribbeanSameDayExpressShipmentCharges = $this->addExtraFees($this->totalCaribbeanSameDayExpressShipmentCharges);
			
			return $this->totalCaribbeanSameDayExpressShipmentCharges;
		}
		
	}
	protected function getCaribbeanSameDayExpressNonLetterOrDocumentCharges($destination, $deliveryType, $minMeasure= null, $maxMeasure = null) {
		$rates = $this->db->getCaribbeanExpressRates($destination, $deliveryType,$minMeasure,$maxMeasure);
		if (count ( $rates ) == 1) $rate = $rates [0];
		
		if (empty ( $rates )) {
			$caribbeanExpressSameDayFreightNonletterORdocument = 0;
		} else {
			$this->caribbeanSameDayExpressNonletterORdocumentRates = $rates;
			
			$basicRate = $rate->base_rate + $rate->markup;
			if ($this->nonletterORdocumentHazardPgks > 0){
				$caribbeanExpressSameDayFreightNonletterORdocument = number_format ( ((($basicRate) * 1.5) + (75.00 * $this->nonletterORdocumentHazardPgks)), 2, '.', '' );		
				$this->totalCaribbeanSameDayExpressShipmentCharges->fees["Hazardous Fee"] += $caribbeanExpressSameDayFreightNonletterORdocument - $basicRate;
				
			}else {
				$caribbeanExpressSameDayFreightNonletterORdocument = number_format ( (($basicRate)), 2, '.', '' );
			}
			$this->totalCaribbeanSameDayExpressShipmentCharges->baseRate +=	$basicRate;
		}
		
		return $caribbeanExpressSameDayFreightNonletterORdocument;
	}
	protected function getCaribbeanSameDayExpressLetterCharges($destination, $deliveryType, $minMeasure= null, $maxMeasure = null) {
		$rates = $this->db->getCaribbeanExpressRates($destination, $deliveryType,$minMeasure,$maxMeasure);
		if (count ( $rates ) == 1)
			$rate = $rates [0];
		
			if (empty ( $rates )) {
				$caribbeanExpressDayFreightLetter = 0;
			} else {
				$this->caribbeanSameDayExpressLetterRates = $rates;

				$basicRate = $rate->base_rate + $rate->markup;
				if ($this->letterHazardPkgs > 0){
					$caribbeanExpressDayFreightLetter = number_format ( ((($basicRate) * 1.5) + (75.00 * $this->letterHazardPkgs)), 2, '.', '' );
					$this->totalCaribbeanSameDayExpressShipmentCharges->fees ["Hazardrous Fee"] += $caribbeanExpressDayFreightLetter -$basicRate;
				}
				else {
					$caribbeanExpressDayFreightLetter = number_format ( (($basicRate)), 2, '.', '' );;
				}
				$this->totalCaribbeanSameDayExpressShipmentCharges->baseRate += $basicRate;
				
			}
			return $caribbeanExpressDayFreightLetter;
	}
	protected function getCaribbeanSameDayExpressDocumentCharges($destination, $deliveryType, $minMeasure = null, $maxMeasure = null) {
		$rates = $this->db->getCaribbeanExpressRates($destination, $deliveryType,$minMeasure,$maxMeasure);
		if (count ( $rates ) == 1)
			$rate = $rates [0];
		
			if (empty ( $rates )) {
				$caribbeanExpressSameDayFreightDocument = 0;
			} else {
				$this->caribbeanSameDayExpressDocumentRates = $rates;
				
				$basicRate = $rate->base_rate + $rate->markup;
				if ($this->documentHazardPkgs > 0){
					$caribbeanExpressSameDayFreightDocument = number_format ( ((($basicRate) * 1.5) + (75.00 * $this->documentHazardPkgs)), 2, '.', '' );
					$this->totalCaribbeanSameDayExpressShipmentCharges->fees ["Hazardrous Fee"] += $caribbeanExpressSameDayFreightDocument -$basicRate;
				}
				else {
					$caribbeanExpressSameDayFreightDocument = number_format ( (($basicRate)), 2, '.', '' );;
				}
				$this->totalCaribbeanSameDayExpressShipmentCharges->baseRate += $basicRate;
			}
			return $caribbeanExpressSameDayFreightDocument;
	}
	
	/**
	 * Get the shipment charges for two day caribbean express service
	 *
	 * @return array
	 */
	public function getCaribbeanTwoDayExpressCharges() {
		
		if ($this->chargeableWeight > 50) {
			return null;
		} else {
			// 			$origin = $this->shipment->shipFrom->zip;
			$destination = strtoupper($this->shipment->shipTo->countryCode);
			$expressChargeableWeight = $this->expressChargeableWeight;
				
			$this->totalCaribbeanTwoDayExpressShipmentCharges = new \stdClass ();
				
			if ($this->nonletterORdocumentPgks > 0)
				$caribbeanTwoDayExpressNonLetterOrDocumentCharges =
				$this->getCaribbeanTwoDayExpressNonLetterOrDocumentCharges($destination, CaribbeanRateDeliveryType::CARIBBEAN_EXPRESS_TWO_DAY_NON_DOC_LTR, $expressChargeableWeight, $expressChargeableWeight);
					
			if ($this->letterPkgs > 0)
				$caribbeanTwoDayExpressLetterCharges =
				$this->getCaribbeanTwoDayExpressLetterCharges($destination, CaribbeanRateDeliveryType::CARIBBEAN_EXPRESS_TWO_DAY_LTR, $expressChargeableWeight, $expressChargeableWeight);
					
			if ($this->documentPkgs > 0){
				$documentDeliveryType = CaribbeanRateDeliveryType::CARIBBEAN_EXPRESS_TWO_DAY_DOC . $this->documentItemQty;
				$caribbeanTwoDayExpressDocumentCharges = $this->getCaribbeanTwoDayExpressDocumentCharges($destination, $documentDeliveryType);

				$caribbeanTwoDayExpressNonLetterOrDocumentCharges =
				$this->getCaribbeanTwoDayExpressNonLetterOrDocumentCharges($destination, CaribbeanRateDeliveryType::CARIBBEAN_EXPRESS_TWO_DAY_NON_DOC_LTR, $expressChargeableWeight, $expressChargeableWeight);
					
			}
				
			$caribbeanTwoDayExpressTotal = number_format($caribbeanTwoDayExpressNonLetterOrDocumentCharges + $caribbeanTwoDayExpressLetterCharges + $caribbeanTwoDayExpressDocumentCharges, 2, '.','');
					
			$this->totalCaribbeanTwoDayExpressShipmentCharges->rate += $caribbeanTwoDayExpressTotal;
			$this->totalCaribbeanTwoDayExpressShipmentCharges = $this->addExtraFees($this->totalCaribbeanTwoDayExpressShipmentCharges);
				
			return $this->totalCaribbeanTwoDayExpressShipmentCharges;
		}
		
	}
	protected function getCaribbeanTwoDayExpressNonLetterOrDocumentCharges($destination, $deliveryType, $minMeasure = null, $maxMeasure = null) {
		$rates = $this->db->getCaribbeanExpressRates($destination, $deliveryType,$minMeasure,$maxMeasure);
		
		$this->var_error_log(array("cbeandest"=>$destination,"deltype" =>$deliveryType));
		if (count ( $rates ) == 1) $rate = $rates [0];
		
		if (empty ( $rates )) {
			$caribbeanExpressTwoDayFreightNonletterORdocument = 0;
		} else {
			$this->caribbeanTwoDayExpressNonletterORdocumentRates = $rates;
				
			$basicRate = $rate->base_rate + $rate->markup;
			if ($this->nonletterORdocumentHazardPgks > 0){
				$caribbeanExpressTwoDayFreightNonletterORdocument = number_format ( ((($basicRate) * 1.5) + (75.00 * $this->nonletterORdocumentHazardPgks)), 2, '.', '' );
				$this->totalCaribbeanTwoDayDayExpressShipmentCharges->fees["Hazardous Fee"] += $caribbeanExpressTwoDayFreightNonletterORdocument - $basicRate;
		
			}else {
				$caribbeanExpressTwoDayFreightNonletterORdocument = number_format ( (($basicRate)), 2, '.', '' );
			}
			$this->totalCaribbeanTwoDayExpressShipmentCharges->baseRate +=	$basicRate;
		}
		
		return $caribbeanExpressTwoDayFreightNonletterORdocument;
	}
	protected function getCaribbeanTwoDayExpressLetterCharges($destination, $deliveryType, $minMeasure = null, $maxMeasure = null) {
		$rates = $this->db->getCaribbeanExpressRates($destination, $deliveryType,$minMeasure,$maxMeasure);
		if (count ( $rates ) == 1)
			$rate = $rates [0];
		
			if (empty ( $rates )) {
				$caribbeanTwoDayExpressFreightLetter = 0;
			} else {
				$this->caribbeanTwoDayExpressLetterRates = $rates;
		
				$basicRate = $rate->base_rate + $rate->markup;
				if ($this->letterHazardPkgs > 0){
					$caribbeanTwoDayExpressFreightLetter = number_format ( ((($basicRate) * 1.5) + (75.00 * $this->letterHazardPkgs)), 2, '.', '' );
					$this->totalCaribbeanTwoDayExpressShipmentCharges->fees ["Hazardrous Fee"] += $caribbeanTwoDayExpressFreightLetter -$basicRate;
				}
				else {
					$caribbeanTwoDayExpressFreightLetter = number_format ( (($basicRate)), 2, '.', '' );;
				}
				$this->totalCaribbeanTwoDayExpressShipmentCharges->baseRate += $basicRate;
		
			}
			return $caribbeanTwoDayExpressFreightLetter;
	}
	protected function getCaribbeanTwoDayExpressDocumentCharges($destination, $deliveryType, $minMeasure = null, $maxMeasure = null) {
		$rates = $this->db->getCaribbeanExpressRates($destination, $deliveryType,$minMeasure,$maxMeasure);
		if (count ( $rates ) == 1)
			$rate = $rates [0];
		
			if (empty ( $rates )) {
				$caribbeanExpressTwoDayFreightDocument = 0;
			} else {
				$this->caribbeanTwoDayExpressDocumentRates = $rates;
		
				$basicRate = $rate->base_rate + $rate->markup;
				if ($this->documentHazardPkgs > 0){
					$caribbeanExpressTwoDayFreightDocument = number_format ( ((($basicRate) * 1.5) + (75.00 * $this->documentHazardPkgs)), 2, '.', '' );
					$this->totalCaribbeanTwoDayExpressShipmentCharges->fees ["Hazardrous Fee"] += $caribbeanExpressTwoDayFreightDocument -$basicRate;
				}
				else {
					$caribbeanExpressTwoDayFreightDocument = number_format ( (($basicRate)), 2, '.', '' );;
				}
				$this->totalCaribbeanTwoDayExpressShipmentCharges->baseRate += $basicRate;
			}
			return $caribbeanExpressTwoDayFreightDocument;
	}

	/**
	 * Get the shipment charges using the Domestic Service
	 */
	public function getDomesticCharges(){
		$pickupZip = $this->shipment->shipTo->zip;
		$pickupState = $this->shipment->shipFrom->state;
		$deliveryState = $this->shipment->shipTo->state;
		$deliveryType = DomesticRateDeliveryType::DOMESTIC;
		
		$this->calculateUsDomesticCommodityCost();
		
		
		$rates = $this->db->getDomesticRates ( $pickupZip, $pickupState, $deliveryType,$this->actualWeight,$this->actualWeight,$deliveryState );
		$rate = $rates [0];
		
		$rateCalculated = number_format ( ($this->actualWeight * $rate->additional_rate), 2, '.', '' );
		if ($rateCalculated < $rate->base_rate)
			$this->usDomesticCost = $rate->base_rate;
			else
				$this->usDomesticCost = $rateCalculated;
			
		if($this->cntHazardous > 0){
			$domesticFreight = number_format((($this->usDomesticCost * 1.5) + (75.00 * $this->cntHazardous) + $this->usDomesticCommodityCost),2,'.','');
			$domesticExpedited = number_format(((($this->usDomesticCost + ($this->usDomesticCost * 0.2)) * 1.5) + (75.00 * $this->cntHazardous) + $this->usDomesticCommodityCost),2,'.','');
		}else{
			$domesticFreight = number_format(($this->usDomesticCost+ $this->usDomesticCommodityCost),2,'.','');
			$domesticExpedited = number_format(($this->usDomesticCost + ($this->usDomesticCost * 0.2) + $this->usDomesticCommodityCost),2,'.','');
		}
		
		$this->totalDomesticStdShipmentCharges = new \stdClass();
		$this->totalDomesticExpediteShipmentChargesShipmentCharges = new \stdClass();
		
		$this->totalDomesticStdShipmentCharges->rate = $domesticFreight;
		$this->totalDomesticStdShipmentCharges->base_rate = $domesticFreight;
		$this->totalDomesticStdShipmentCharges = $this->addExtraFees($this->totalDomesticStdShipmentCharges);
		
		
		$this->totalDomesticExpediteShipmentCharges->rate = $domesticExpedited;
		$this->totalDomesticExpediteShipmentCharges->base_rate = $domesticExpedited;
		$this->totalDomesticExpediteShipmentCharges = $this->addExtraFees($this->totalDomesticExpediteShipmentCharges);
		
		
		return array (
				
				"totalDomesticStdShipmentCharges" => $this->totalDomesticStdShipmentCharges,
				"totalDomesticExpediteShipmentCharges" => $this->totalDomesticExpediteShipmentCharges
		);
		
		
	}
	// =============================End of SwiftPac Rate Services==============================
	// ============================Helper Functions============================================
	/**
	 * Calculates and updates the Small Package Commodity Cost
	 */
	private function calculateSmallPackgeMailBoxCommodityCost() {
		$this->smallpkgCommodityCost = '0.00';
		$rates = $this->db->getDomesticCommodityRates ( DomesticCommodityType::SMALL_PACKAGE, $this->packages, $this->shipment );
		
		foreach ( $rates as $rate ) {
			$smallpkgPerLBRate = number_format ( ($rate->additional_rate * $rate->pkgweight), 2, ',', '' );
			
			if ($smallpkgPerLBRate >= $rate->base_rate) {
				$smallpkgDomesticCommodity = $smallpkgPerLBRate;
			} else {
				$smallpkgDomesticCommodity = $rate->base_rate;
			}
			$this->smallpkgCommodityCost = number_format ( ($this->smallpkgCommodityCost + ($smallpkgDomesticCommodity * ($rate->commodity_rate / 100))), 2, '.', '' );
		}
	}
	/**
	 * Calculates and updates the Small Package Domestic Cost
	 */
	private function calculateSmallPkgDomesticCost() {
		$rate = $this->db->getDomesticSmallPkgRate ( $this->actualWeight, $this->shipment );
		$rateCalculated = number_format ( ($this->actualWeight * $rate->additional_rate), 2, '.', '' );
		
		$originZip = $this->shipment->shipFrom->zip;
		if ($originZip != '33166' || $originZip != '32804' || $originZip != '11234' || $originZip != '2136' || $originZip != '77010' || $originZip != '30301' || $originZip != '20837' || $originZip != '19140' || $originZip != '7032') {
			
			if ($rateCalculated < $rate)
				$this->smallPkgDomesticCost = $rate->base_rate;
			else
				$this->smallPkgDomesticCost = $rateCalculated;
		} else
			$this->smallPkgDomesticCost = $rate->base_rate;
	}
	
	/**
	 * Calculates and updates the Small Package Domestic Cost
	 */
	private function calculateExpressCommodityCost() {
		$this->expressCommodityCost = '0.00';
		$rates = $this->db->getDomesticCommodityRates ( DomesticCommodityType::EXPRESS, $this->packages, $this->shipment );
		
		foreach ( $rates as $rate ) {
			
			$expressPerLBRate = number_format ( ($rate->additional_rate * $rate->pkgweight), 2, ',', '' );
			
			if ($expressPerLBRate >= $rate->base_rate) {
				$expressDomesticCommodity = $expressPerLBRate;
			} else {
				$expressDomesticCommodity = $rate->base_rate;
			}
			$this->expressCommodityCost = number_format ( ($this->expressCommodityCost + ($expressDomesticCommodity * ($rate->commodity_rate / 100))), 2, '.', '' );
		}
	}
	
	/**
	 * Calcualtes and updates the Ocean Barrel Domestic Cost
	 */
	private function calculateOceanBarrelDomesticCost() {
		$pickupZip = $this->shipment->shipFrom->zip;
		$warehouseState = $this->shipment->shipFrom->state;
		$deliveryType = DomesticRateDeliveryType::DOMESTIC_BARREL;
		$rates = $this->db->getDomesticRates ( $pickupZip, $warehouseState, $deliveryType );
		$rate = $rates [0];
		
		$rateCalculated = number_format ( ($this->actualWeight * $rate->additional_rate), 2, '.', '' );
		
		if ($originZip != '33166' || $originZip != '32804' || $originZip != '11234' || $originZip != '2136' || $originZip != '77010' || $originZip != '30301' || $originZip != '20837' || $originZip != '19140' || $originZip != '7032') {
		
			if ($rateCalculated < $rate->base_rate)
				$this->barrelDomesticCost= $rate->base_rate;
				else
					$this->barrelDomesticCost = $rateCalculated;
		} else
			$this->barrelDomesticCost = $rate->base_rate;
				
	}
	/**
	 * Calcualtes and updates the Air and Ocean Cargo Domestic Cost
	 */
	private function calculateAirOceanDomesticCost(){
		$pickupZip = $this->shipment->shipFrom->zip;
		$warehouseState = $this->shipment->shipFrom->state;
		$deliveryType = DomesticRateDeliveryType::DOMESTIC_AIR_OCEAN;
		$rates = $this->db->getDomesticRates ( $pickupZip, $warehouseState, $deliveryType );
		$rate = $rates [0];
		
// 		foreach($rates as $rate){
			
			$rateCalculated = number_format ( ($this->actualWeight * $rate->additional_rate), 2, '.', '' );
			
			if ($originZip != '33166' || $originZip != '32804' || $originZip != '11234' || $originZip != '2136' || $originZip != '77010' || $originZip != '30301' || $originZip != '20837' || $originZip != '19140' || $originZip != '7032') {
					
				if ($rateCalculated < $rate)
					$this->airOceanDomesticCost = $rate->base_rate;
					else
						$this->airOceanDomesticCost = $rateCalculated;
			} else
				$this->airOceanDomesticCost = $rate->base_rate;
// 		}
		
	}
	
	/**
	 * Calcualtes and updates the Air/Ocean commodity cost
	 */
	private function calculateAirOceanCommodityCost(){
		$this->airOceanCommodityCost = 0.00;
		$rates = $this->db->getDomesticCommodityRates ( DomesticCommodityType::AIR_OCEAN, $this->packages, $this->shipment );
		
		foreach ( $rates as $rate ) {
			$airOceanPerLBRate = number_format ( ($rate->additional_rate * $rate->pkg_weight), 2, ',', '' );
				
			if ($airOceanPerLBRate >= $rate->base_rate) {
				$airOceanDomesticCommodity = $airOceanPerLBRate;
			} else {
				$airOceanDomesticCommodity = $rate->base_rate;
			}
			if ($rate->pkg_type == PackagingType::SWIFTPAC_E_CONTAINER) $this->airOceanCommodityCost += 0.00;
			else if ($rate->pkg_type == PackagingType::SWIFTPAC_EH_CONTAINER) $this->airOceanCommodityCost += 0.00;
			else if ($rate->pkg_type == PackagingType::SWIFTPAC_BARREL) $this->airOceanCommodityCost += 0.00;
			else $this->airOceanCommodityCost = number_format ( ($this->airOceanCommodityCost + ($airOceanDomesticCommodity * ($rate->commodity_rate / 100))), 2, '.', '' );
		}
	}
	
	
	/**
	 * Calcualtes and updats the US Domestic Commodity Cost
	 */
	private function calculateUsDomesticCommodityCost (){
		
		$this->usDomesticCommodityCost = 0.00;
		
		$packages = $this->packages;
		$deliveryType = DomesticCommodityType::DOMESTIC;
		$warehouseState = $this->shipment->shipFrom->state;
		$minMeasure = $this->actualWeight;
		$maxMeasure = $this->actualWeight;
		$pickupZip = $this->shipment->shipTo->zip;
		$pickupState = $this->shipment->shipTo->state;
		
		
		$rates = $this->db->getDomesticCommodityRates_beta($packages, $deliveryType, $warehouseState, $minMeasure, $maxMeasure,$pickupZip , $pickupState);
		
		foreach ($rates as $rate)  {
			$usDomesticPerLbRate = number_format(($rate->additional_rate * $rate->pkg_weight ),2,'.','');
		
			if ($usDomesticPerLbRate >= $rate->base_rate){
				$usDomesticCommodity = $usDomesticPerLbRate;
			}else{
				$usDomesticCommodity = $rate->base_rate;
			}
			$this->usDomesticCommodityCost = number_format(($this->usDomesticCommodityCost + ($usDomesticCommodity * ($rate->commodity_rate/100))),2,'.','');
		}	
	}
	/**
	 * Compute and add additional fees to the base freight cost of the said service
	 * @var object
	 * @return object
	 */
	private function addExtraFees($finalRates, $svcType = null) {
		
		// Determines which function makes a call to addExtraFees
		$serviceFunctionName = debug_backtrace () [1] ['function'];
		
		// Add Insurance fee
		if ($svcType != "barrel") {
			$finalRates->rate += $this->insurance;
			$finalRates->fees ["Insurance"] = $this->insurance;
		}
		// Add residential pickup fee
		if ($this->residentialPickUp) {
			$finalRates->rate += self::RESIDENTIAL_PICKUP_COST;
			$finalRates->fees ["Residential Pickup Cost"] = self::RESIDENTIAL_PICKUP_COST;
		}
		// Add residential delivery fee
		if ($this->residentialDelivery) {
			$finalRates->rate += self::RESIDENTIAL_DELIVERY_COST;
			$finalRates->fees ["Residential Delivery Cost"] = self::RESIDENTIAL_DELIVERY_COST;
		}
		
		// ======================Add pickup location services========================
		if ($this->shipment->pickupLocationService->forkLift) {
			$forkLiftPickupFee = $this->getCalForkLiftFee ( $serviceFunctionName );
			$finalRates->rate += $forkLiftPickupFee;
			$finalRates->fees ["Fork Lift Pickup Service"] = $forkLiftPickupFee;
		}
		
		if ($this->shipment->pickupLocationService->inside) {
			$insidePickupFee = $this->getCalInsideFee ();
			$finalRates->rate += $insidePickupFee;
			$finalRates->fees ["Inside Pickup Service"] = $insidePickupFee;
		}
		
		if ($this->shipment->pickupLocationService->keepFrozen) {
			$keepFrozenPickupFee = $this->getCalKeepFrozenFee ( $serviceFunctionName );
			$finalRates->rate += $keepFrozenPickupFee;
			$finalRates->fees ["Keep Frozen Pickup Service"] = $keepFrozenPickupFee;
		}
		
		if ($this->shipment->pickupLocationService->liftGate) {
			
			$liftGatePickupFee = $this->getCalLiftGateFee ( $serviceFunctionName );
			$finalRates->rate += $liftGatePickupFee;
			$finalRates->fees ["Lift Gate Pickup Service"] = $liftGatePickupFee;
		}
		
		if ($this->shipment->pickupLocationService->perishable) {
			
			$perishablePickupFee = $this->getCalPerishableFee ( $serviceFunctionName );
			$finalRates->rate += $perishablePickupFee;
			$finalRates->fees ["Perishable Pickup Service"] = $perishablePickupFee;
		}
		if ($this->shipment->pickupLocationService->saturday) {
			
			$finalRates->fees ["Saturday Pickup Service"] = "N/A";
		}
		if ($this->shipment->pickupLocationService->stairs) {
			
			$stairsPickupFee = $this->getCalStairsFee ();
			$finalRates->rate += $stairsPickupFee;
			$finalRates->fees ["Stairs Pickup Service"] = $stairsPickupFee;
		}
		
		if ($this->shipment->pickupLocationService->holiday) {
			
			$finalRates->fees ["Holiday Pickup Service"] = "N/A";
		}
		// ==========================End of adding location services===========================
		// =========================Add delivery location services============================
		
		if ($this->shipment->deliveryLocationService->forkLift) {
			
			$forkLiftDeliveryFee = $this->getCalForkLiftFee ( $serviceFunctionName );
			$finalRates->rate += $forkLiftDeliveryFee;
			$finalRates->fees ["Fork Lift Delivery Service"] = $forkLiftDeliveryFee;
		}
		if ($this->shipment->deliveryLocationService->inside) {
			
			$insideDeliveryFee = $this->getCalInsideFee ();
			$finalRates->rate += $insideDeliveryFee;
			$finalRates->fees ["Inside Delivery Service"] = $insidePickupFee;
		}
		if ($this->shipment->deliveryLocationService->holiday) {
			
			$finalRates->fees ["Holiday Delivery Service"] = "N/A";
		}
		if ($this->shipment->deliveryLocationService->keepFrozen) {
			
			$keepFrozenDeliveryFee = $this->getCalKeepFrozenFee ( $serviceFunctionName );
			$finalRates->rate += $keepFrozenDeliveryFee;
			$finalRates->fees ["Keep Frozen Delivery Service"] = $keepFrozenDeliveryFee;
		}
		if ($this->shipment->deliveryLocationService->liftGate) {
			
			$liftGateDeliveryFee = $this->getCalLiftGateFee ( $serviceFunctionName );
			$finalRates->rate += $liftGateDeliveryFee;
			$finalRates->fees ["Lift Gate Delivery Service"] = $liftGateDeliveryFee;
		}
		if ($this->shipment->deliveryLocationService->perishable) {
			$perishableDeliveryFee = $this->getCalPerishableFee ( $serviceFunctionName );
			$finalRates->rate += $perishableDeliveryFee;
			$finalRates->fees ["Perishable Delivery Service"] = $perishableDeliveryFee;
		}
		if ($this->shipment->deliveryLocationService->saturday) {
			
			$finalRates->fees ["Saturday Delivery Service"] = "N/A";
		}
		if ($this->shipment->deliveryLocationService->stairs) {
			
			$stairsDeliveryFee = $this->getCalStairsFee ();
			$finalRates->rate += $stairsDeliveryFee;
			$finalRates->fees ["Stairs Delivery Service"] = $stairsDeliveryFee;
		}
		// ==========================End of Adding Delivery location Services===================
		
		return $finalRates;
	}
	
	// =======================Pickup Location Helper funcitons============================
	/**
	 * Calculates the forklift service fee
	 *
	 * @param string $originFuncCall        	
	 * @return number
	 */
	private function getCalForkLiftFee($serviceFunctionName) {
		switch ($serviceFunctionName) {
			
			case 'getMailBoxCharges' :
			case 'getSmallPackageCharges' :
			case 'getOceanCharges' :
			case 'getAirCharges' :
				$calForkLiftPickupFee = number_format ( ($this->chargeableWeight * 0.10), 2, '.', '' );
				
				break;
			case 'getExpressCharges' :
				
				$calForkLiftPickupFee = number_format ( ($this->expressChargeableWeight * 0.10), 2, '.', '' );
		}
		if ($calForkLiftPickupFee > self::FORK_LIFT_BASE_FEE) {
			return $calForkLiftPickupFee;
		}
		return self::FORK_LIFT_BASE_FEE;
	}
	/**
	 * Calculates the Inside service fee
	 *
	 * @return number
	 */
	private function getCalInsideFee() {
		$barrelCost = 0;
		$eContainerCost = 0;
		$ehContainerCost = 0;
		$nonBarrelContainerCost = 0;
		$nonBarrelContainerDomesticCost = 0;
		
		if ($this->barrelItemQty > 0) {
			$barrelCost = number_format ( (5 * $this->barrelItemQty), 2, '.', '' );
		}
		if ($this->econtainerItemQty > 0) {
			$eContainerCost = number_format ( (5 * $this->econtainerItemQty), 2, '.', '' );
		}
		if ($this->ehcontainerItemQty > 0) {
			$ehContainerCost = number_format ( (5 * $this->ehcontainerItemQty), 2, '.', '' );
		}
		if ($this->nonBarrelOrContainerItemQty > 0) {
			$nonBarrelContainerCost = number_format ( (0.05 * $this->nonBarrelOrContainerChargeableWeight), 2, '.', '' );
		}
		
		return $barrelCost + $eContainerCost + $ehContainerCost + $nonBarrelContainerCost;
	}
	/**
	 * Calculates the Stairs service fee
	 *
	 * @return number
	 */
	private function getCalStairsFee() {
		
		// Inside fee is equivalent to stairs fee
		return $this->getCalInsideFee ();
	}
	/**
	 * Calculates the Lift Gate service fee
	 *
	 * @param string $serviceFunctionName        	
	 * @return number
	 */
	private function getCalLiftGateFee($serviceFunctionName) {
		
		// Forklift fee is equivalent to liftgate fee
		return $this->getCalForkLiftFee ( $serviceFunctionName );
	}
	/**
	 * Calculates the Keep Frozen service fee
	 *
	 * @param string $serviceFunctionName        	
	 * @return number
	 */
	private function getCalKeepFrozenFee($serviceFunctionName) {
		
		// Perishable Fee is equivalent to Frozen fee
		return $this->getCalPerishableFee ( $serviceFunctionName );
	}
	/**
	 * Calculates the Perishable service fee
	 *
	 * @param string $serviceFunctionName        	
	 * @return number
	 */
	private function getCalPerishableFee($serviceFunctionName) {
		$numOfPackages = count ( $this->packages );
		
		switch ($serviceFunctionName) {
			
			case 'getMailBoxCharges' :
				
				$usMailBoxFreight = $this->basicUSMailBoxFreightCost;
				$mailBoxCost = number_format ( (($usMailBoxFreight * 0.5) + (75.00 * $numOfPackages)), 2, '.', '' );
				return $mailBoxCost;
			
			case 'getSmallPackageCharges' :
				$smallPackageRate = $this->smallPackageRates;
				$smallPkgCost = number_format ( ((($smallPackageRate->base_rate + $smallPackageRate->markup) * 0.5) + (75.00 * $numOfPackages)), 2, '.', '' );
				return $smallPkgCost;
			case 'getOceanCharges' :
				
				$barrelOceanRate = $this->barrelOceanRates;
				$econtainerOceanRate = $this->econtainerOceanRates;
				$ehcontainerOceanRate = $this->ehcontainerOceanRates;
				$nonBarrelContainerOceanRate = $this->nonBarrelContainerOceanRates;
				
				$perishableBarrelOceanCost = number_format ((((($barrelOceanRate->base_rate + $barrelOceanRate->markup) + ((($barrelOceanRate->base_rate + $barrelOceanRate->markup) * 0.8) * ($this->barrelItemQty-1))) * 0.5) + (75.00 * $this->barrelPkgs)), 2, '.', '' );
				$perishableEcontainerOceanCost = number_format ( ((((($this->sumCubicFeetEcontainer * $econtainerOceanRate->additional_rate) + $econtainerOceanRate->markup)) * 0.5) + (75.00 * $this->econtainerPkgs)), 2, '.', '' );
				$perishableEHcontainerOceanCost = number_format ( ((($ehcontainerOceanRate->base_rate * $this->ehcontainerItemQty) * 0.5) + (75.00 * $this->ehcontainerPkgs)), 2, '.', '' );
				$perishableNonBarrelContainerOceanCost = number_format ( ((($nonBarrelContainerOceanRate->base_rate + ($this->sumCubicFeetNonBarrelContainer * $nonBarrelContainerOceanRate->additional_rate)) * 0.5) + (75.00 * $this->nonBarrelOrContainerPkgs)), 2, '.', '' );
				
				$oceanCost = number_format ( ($perishableBarrelOceanCost + $perishableEcontainerOceanCost + $perishableEHcontainerOceanCost + $perishableNonBarrelContainerOceanCost), 2, '.', '' );
				return $oceanCost;
				
			case 'getAirCharges' :
				$airCost = number_format ( ((($air_cost * 0.5) + (75.00 * $numberofpieces))), 2, '.', '' );
				return $airCost;

			case 'getExpressCharges' :
			case 'getCaribbeanSameDayExpressCharges':
			case 'getCaribbeanTwoDayExpressCharges':
				
				// Caribbean Express Non-Letter or Non-Document cost
				// Use express rates fetch from previous call to database
				$expressNonletterORdocumentRates = $this->expressNonletterORdocumentRates;
				if (count ( $expressNonletterORdocumentRates ) == 1)
					$expressNonletterORdocumentRate = $expressNonletterORdocumentRates [0];
				if ($this->nonletterORdocumentPgks > 0) {
					$nonletterORdocumentExpressCost = number_format ( ((($expressNonletterORdocumentRate->base_rate + $expressNonletterORdocumentRate->markup) * 0.5) + (75.00 * $this->nonletterORdocumentPgks)), 2, '.', '' );
				} else {
					$nonletterORdocumentExpressCost = 0;
				}
				
				// CaribbeanExpress Letter Cost
				// Use express letter rates fetch from previous call to database
				$expressLetterRates = $this->expressLetterRates;
				if (count ( $expressLetterRates ) == 1)
					$expressLetterRate = $expressLetterRates [0];
				if ($this->letterPkgs > 0) {
					$letterExpressCost = number_format ( (((($expressLetterRate->base_rate + $expressLetterRate->markup) * $this->letterItemQty) * 0.5) + (75.00 * $this->letterPkgs)), 2, '.', '' );
				} else {
					$letterExpressCost = 0;
				}
				
				// Caribbean Express Document Cost
				// Use express rates fetch from previous call to database
				$expressDocumentRates = $this->expressDocumentRates;
				if (count ( $expressDocumentRates ) == 1)
					$expressDocumentRate = $expressDocumentRates [0];
				if ($this->documentPkgs > 0) {
					$documentExpressCost = number_format ( ((($expressDocumentRate->base_rate + $expressDocumentRate->markup) * 0.5) + (75.00 * $this->documentPkgs)), 2, '.', '' );
				} else {
					$documentExpressCost = 0;
				}
				$expressCost = number_format ( ($letterExpressCost + $documentExpressCost + $nonletterORdocumentExpressCost), 2, '.', '' );
				return $expressCost;
								
			default :
		}
	}
	
	public function getWarehouseZip(){
		
		return self::$warehouseZip;
	}
	public function getWarehouseAddr(){
		$addr = new Address();
		
		$addr->addressLine1 = self::$warehouseAddrLine1;
		$addr->city = self::$warehouseCity;
		$addr->zip = self::$warehouseZip;
		$addr->countryName = self::$warehouseCountryName;
		$addr->countryCode = self::$warehouseCountryCode;
		
		return $addr;
	}
	public function setShipmentFromAddress(Address $address){
		
// 		$this->shipment->shipFrom = $this->getWarehouseAddr();
		
// 		$this->var_error_log(array("whaddr" => $this->getWarehouseAddr()));
	}
	public function setShipmentToAddress(Address $address){
		
// 		$this->shipment->shipTo = $address;
	}
	// ==========================Debugging Helper Functions=============================
	function var_error_log($object = null) {
		ob_start (); // start buffer capture
		var_dump ( $object ); // dump the values
		$contents = ob_get_contents (); // put the buffer into a variable
		ob_end_clean (); // end capture
		error_log ( $contents, 3, "switpac_api_log.log" ); // log contents of the result of var_dump( $object )
	}
	
	// ===========================End of Helpers =========================================================
}
abstract class WarehouseLocation {
	const MIAMI_FL = "33166";
	const ORLANDO_FL = "32804";
	const BROOKLYN_NY = "11234";
	const BOSTON_MA = "2136";
	const HUSTON_TX = "77010";
	const ATLANTA_GA = "30301";
	const MARYLAND = "20837";
	const PHILADELPHIA = "19140";
	const KEARNY = "7032";
}
?>