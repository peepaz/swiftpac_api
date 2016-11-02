<?php

namespace lib\SwiftPac {

	class DataTypes {

	}
}

namespace lib\SwiftPac\DataTypes {
	
		
	//==============================SwiftPac Request Classes===================
	//SwiftPac Rate
	class SwiftPacRate {	
		/**
		 *
		 * @var RateRequest
		 */
		public $rateRequest = null;
	}
	class RateRequest {
	
		/**
		 *
		 * @var Rate
		 */
		public $rate = null;
	
	}
	class Rate {
		/**
		 *
		 * @var RequestDetail
		 */
		public $requestDetail = null;
	
	}
	class RequestDetail {
		/**
		 *
		 * @var Shipmentlist
		 */
		public $shipmentList = null;
	}
	class ShipmentList {
	
		/**
		 *
		 * @var Shipment
		 */
		public $shipment = null;
	
	
	}
	class Shipment {
		/**
		 *
		 * @var Address
		 */
		public $shipFrom = null;
		/**
		 *
		 * @var LocationService
		 */
		public $pickupLocationService = null;
		/**
		 *
		 * @var Address
		 */
		public $shipTo = null;
		/**
		 *
		 * @var LocationService
		 */
		public $deliveryLocationService = null;
	
		/**
		 *
		 * @var Address
		 */
		public $returnTo = null;
		/**
		 *
		 * @var PackageList
		 */
		public $packageList = null;
		/**
		 *
		 * @var Dimensions
		 */
		public $dimensions = null;
		/**
		 * @var double
		 */
		public $weight = null;
		/**
		 *
		 * @var boolean
		 */
		public $fragileFlag = null;
		/**
		 *
		 * @var double
		 */
		public $declaredValue = null;
		/**
		 *
		 * @var string
		 */
		public $carrierId = null;
		/**
		 *
		 * @var string
		 */
		public $driverInstructions = null;
		/**
		 *
		 * @var string
		 */
		public $serviceCode = null;
		/**
		 *
		 * @var string
		 */
		public $weightUnit = null;
		/**
		 *
		 * @var DateTime
		 */
		public $shipDate = null;
		/**
		 *
		 * @var boolean
		 */
		//	public $hazMat = null;
	
		/**
		 * The account number of the shipper
		 * @var string
		 */
		public $shipperAccNum = null;
		/**
		 * The account number of the recipient
		 * @var string
		 */
		public $recipientAccNum = null;
		
		/**
		 * 
		 * @var PaymentDetail
		 */
		public $paymentDetail =  null;
		
		/**
		 * 
		 * @var string
		 */
		public $shipmentType = null;
	
		/**
		 * 
		 * @var string
		 */
		public $reference = null;
	
	}
	class Package {
	
		/**
		 *
		 * @var string
		 */
		public $shipToAtt = null;
		/**
		 *
		 * @var PackagingType
		 */
		public $packagingType = null;
		/**
		 *
		 * @var string
		 */
		public $contentDescription = null;
		/**
		 *
		 * @var string
		 */
		public $packageClass = null;
	
		/**
		 *
		 * @var Dimensions
		 */
		public $dimensions =  null;
		/**
		 *
		 * @var double
		 */
		//public $length = null;
		/**
		 *
		 * @var double
		 */
		//public $width = null;
		/**
		 *
		 * @var double
		 */
		//public $height = null;
		/**
		 *
		 * @var double
		 */
		public $weight = null;
		// 		/**
		// 		 * @var double
		// 		 */
		// 		public $girth = null;
		/**
		 *
		 * @var string
		 */
		public $referenceNumber = null;
		/**
		 *
		 * @var double
		 */
		public $notifiedValue = null;
	
		/**
		 *
		 * @var boolean
		 */
		public $hazardous = null;
		/**
		 *
		 * @var integer
		 */
		public $itemQty = null;
	
		/**
		 * An identifier for a specific
		 * @var string
		 */
		public $packageLabelId = null;
	
	
	
	}
	class PackageList {
	
		/**
		 *
		 * @var Package
		 */
		public $packages = array();
	
		/**
		 *
		 * @param Package $package
		 */
		public function addPackage(Package $package){
			$this->packages[] = $package;
		}
	}
	class Address {
		/**
		 *
		 * @var string
		 */
		public $name = null;
		/**
		 *
		 * @var string
		 */
		public $company = null;
		/**
		 *
		 * @var string
		 */
		public $attn = null;
		/**
		 *
		 * @var string
		 */
		public $addressLine1 = null;
		/**
		 *
		 * @var string
		 */
		public $addressLine2 = null;
		/**
		 *
		 * @var string
		 */
		public $state = null;
		/**
		 *
		 * @var string
		 */
		public $zip = null;
		/**
		 *
		 * @var string
		 */
		public $city = null;
		/**
		 *
		 * @var string
		 */
		public $countryName = null;
		/**
		 *
		 * @var string
		 */
		public $countryCode = null;
		/**
		 *
		 * @var boolean
		 */
		public $residentialFlag = null;
		/**
		 *
		 * @var string
		 */
		public $email = null;
		/**
		 *
		 * @var string
		 */
		public $mobile = null;
		/**
		 *
		 * @var string
		 */
		public $phone = null;
		/**
		 *
		 * @var string
		 */
		public $fax = null;
		/**
		 *
		 * @var boolean
		 */
		protected  $businessFlag = true;
	
	}
	class LocationService {
		/**
		 *
		 * @var boolean
		 */
		public $inside = null;
		/**
		 *
		 * @var boolean
		 */
		public $stairs = null;
		/**
		 *
		 * @var boolean
		 */
		public $liftGate = null;
		/**
		 *
		 * @var boolean
		 */
		public $forkLift = null;
		/**
		 *
		 * @var boolean
		 */
		public $keepFrozen = null;
		/**
		 *
		 * @var boolean
		 */
		public $perishable = null;
		/**
		 *
		 * @var boolean
		 */
		public $saturday = null;
		/**
		 *
		 * @var boolean
		 */
		public $holiday = null;
	}
	class Dimensions {
		/**
		 *
		 * @var double
		 */
		public $length;
		/**
		 *
		 * @var double
		 */
		public $width;
		/**
		 *
		 * @var double
		 */
		public $height;
		/**
		 *
		 * @var double
		 */
		public $girth;
		/**
		 *
		 * @var double
		 */
		public $unit;
	}
	class Fee {
	
		/**
		 *
		 * @var FeeDetails
		 */
		public $feeDetails = null;
	}
	class FeeDetails {
	
		/**
		 *
		 * @var string
		 */
		public $name = null;
	
		/**
		 *
		 * @var string
		 */
		public $charge = null;
	}
	
	//SwiftPac Create Account
	class SwiftPacCreateAccount {	
		/**
		 * 
		 * @var string
		 */
		public $createAccountRequest = null;
	}
	class CreateAccountRequest {
		/**
		 * 
		 * @var CreateAccount
		 */
		public $createAccountList = array();
		
		function addCreateAccount (CreateAccount $createAccount) {
			$this->createAccountList[] = $createAccount;
		}
	}
	class CreateAccount {
		
		/**
		 * 
		 * @var string
		 */
		public $username = null;
		
		/**
		 * 
		 * @var string
		 */
		public $password = null;
		
		/**
		 * 
		 * @var string
		 */
		public $branch = null;
		
		/**
		 * 
		 * @var string
		 */
		public $firstName = null;
		
		/**
		 * 
		 * @var string
		 */
		public $lastName = null;
		
		/**
		 * 
		 * @var string
		 */
		public $address1 = null;
		
		/**
		 * 
		 * @var string
		 */
		public $address2 = null;
		
		/**
		 * 
		 * @var string
		 */
		public $city = null;
		
		/**
		 * 
		 * @var string
		 */
		public $state = null;
		
		/**
		 * 
		 * @var string
		 */
		public $zipCode = null;
		
		/**
		 * 
		 * @var string
		 */
		public $country = null;
		
		/**
		 * 
		 * @var string
		 */
		public $phone = null;
		
		/**
		 * 
		 * @var string
		 */
		public $fax = null;
		
		/**
		 * 
		 * @var string
		 */
		public $mobile = null;
		
		/**
		 * 
		 * @var string
		 */
		public $email = null;
		
		/**
		 * 
		 * @var string
		 */
		public $language = null;
		
		/**
		 * 
		 * @var string
		 */
		public $paymentMethod = null;
		/**
		 * 
		 * @var string
		 */
		public $accountType = null;
		/**
		 * 
		 * @var string
		 */
		public $countryCode = null;
		
	}
	
	//SwiftPac Edit  Account 
	class SwiftPacEditAccount {
		/**
		 *
		 * @var EditAccountRequest
		 */
		public $editAccountRequest = null;
	}
	class EditAccountRequest {
		/**
		 *
		 * @var EditAccount
		 */
		public $editAccountList = array();
		
		/**
		 * 
		 * @var string
		 */
		public $accountUsername = null;
	
		function addEditAccount (EditAccount $editAccount) {
			$this->editAccountList[] = $editAccount;
		}
	}
	class EditAccount {
		
		
		/**
		 *
		 * @var string
		 */
		public $password = null;
		
		/**
		 *
		 * @var string
		 */
		public $branch = null;
		
		/**
		 *
		 * @var string
		 */
		public $firstName = null;
		
		/**
		 *
		 * @var string
		 */
		public $lastName = null;
		
		/**
		 *
		 * @var string
		 */
		public $address1 = null;
		
		/**
		 *
		 * @var string
		 */
		public $address2 = null;
		
		/**
		 *
		 * @var string
		 */
		public $city = null;
		
		/**
		 *
		 * @var string
		 */
		public $state = null;
		
		/**
		 *
		 * @var string
		 */
		public $zipCode = null;
		
		/**
		 *
		 * @var string
		 */
		public $country = null;
		
		/**
		 *
		 * @var string
		 */
		public $phone = null;
		
		/**
		 *
		 * @var string
		 */
		public $fax = null;
		
		/**
		 *
		 * @var string
		 */
		public $mobile = null;
		
		/**
		 *
		 * @var string
		 */
		public $email = null;
		
		/**
		 *
		 * @var string
		 */
		public $language = null;
		
		/**
		 *
		 * @var string
		 */
		public $paymentMethod = null;
		/**
		 *
		 * @var string
		 */
		public $accountType = null;
		/**
		 *
		 * @var string
		 */
		public $countryCode = null;
		
		
	}
	
	
	//SwiftPac Account Search
	class SwiftPacAccountSearch{
		/**
		 * 
		 * @var AccountSearchRequest
		 */
		public $accountSearchRequest = null;
	}
	class AccountSearchRequest{
		
		/**
		 * 
		 * @var AccountSearch
		 */
		public $accountSearch = null;
	}
	class AccountSearch{
		
		/**
		 * 
		 * @var string
		 */
		public $userLogin = null;
		/**
		 * 
		 * @var string
		 */
		public $email = null;
		/**
		 * 
		 * @var string
		 */
		public $firstName = null;
		/**
		 * 
		 * @var string
		 */
		public $lastName = null;
		
	}
	
	//SwiftPac Shipment
	class SwiftPacShipment {
		/**
		 *
		 * @var ShipmentRequest
		 */
		public $shipmentRequest = null;
	}
	class ShipmentRequest {
		/**
		 *
		 * @var RequestDetail
		 */
		public $requestDetail = null;
	}
	
	//Payment Options
	class PaymentOption {
		
		/**
		 * 
		 * @var CreditCard
		 */
		public $creditCard = null;
		
		/**
		 * 
		 * @var string
		 */
		public $cash = null;
		
		/**
		 * 
		 * @var PayPal
		 */
		public $payPal = null;
	}
	
	//Paypal
	class PayPal {
		
	}
	
	class PaymentDetail {
		
		/**
		 * 
		 * @var PaymentOption
		 */
		public $paymentOption = null;
		
		/**
		 * 
		 * @var string
		 */
		public $paymentCurrency = null;
	}
	//SwiftPac Account Validation
	class SwiftPacAccountValidation{
		
		/**
		 * 
		 * @var AccountValidationRequest
		 */
		public $accountValidationRequest = null;
	}
	class AccountValidationRequest{
		/**
		 * 
		 * @var string
		 */
		public $username = null;
		/**
		 * 
		 * @var string
		 */
		public $password = null;
	}
	
	
	//SwiftPac Credit Card payment
	class SwiftPacCreditCardPayment{
		
		/**
		 * 
		 * @var CreditCardPaymentRequest
		 */
		public $creditCardPaymentRequest = null;
	}
	class CreditCardPaymentRequest {
		/**
		 * 
		 * @var CreditCard
		 */
		public $creditCard = null;	
		/**
		 *
		 * @var double
		 */
		public $chargeAmount = null;
		/**
		 *
		 * @var string
		 */
		public $currency = null;
	}
	class CreditCard {
		/**
		 * 
		 * @var string
		 */
		public $expMonth;
		/**
		 *
		 * @var string
		 */
		public $expYear;
		/**
		 * 
		 * @var string
		 */
		public $number;
		/**
		 *
		 * @var string
		 */
		public $cvc;
		
		/**
		 * 
		 * @var Address
		 */
		public $cardHolderAddress = null;
	}
	
	//==============================End of SwiftPac Request Classes============
	// ===================SwiftPac Response Clases=============================
	//SwiftPac Rate
	class SwiftPacRateRateResponse {
		/**
		 *
		 * @var RateResponse
		 */
		public $rateResponse = null;
	}
	class RateResponse {
	
		/**
		 *
		 * @var RateResult
		 */
		public $rateResult = null;
	
		/**
		 *
		 * @var Error
		 */
		public $errors = array();
	}
	class RateResult {
	
		/**
		 *
		 * @var Service
		 */
		public $serviceList = array ();
	}
	class ServiceRateResult {
	
		/**
		 *
		 * @var Service
		 */
		public $service = array ();
	}
	class Service {
	
		/**
		 *
		 * @var string
		 */
		public $carrier = null;
		/**
		 *
		 * @var string
		 */
		public $serviceCode = null;
		/**
		 *
		 * @var string
		 */
		public $serviceDesc = null;
		/**
		 *
		 * @var string
		 */
		public $serviceType = null;
		/**
		 *
		 * @var DateTime
		 */
		public $estimatedDeliveryDate = null;
		/**
		 *
		 * @var string
		 */
		public $estimatedDeliveryTime = null;
		/**
		 *
		 * @var boolean
		 */
		public $guaranteedService = null;
		/**
		 *
		 * @var ShipmentCharges
		 */
		public $shipmentCharges = null;
		/**
		 * @var TransitTime
		 */
		public $transitTime = null;
	}
	class ShipmentCharges {
	
		/**
		 *
		 * @var double
		 */
		public $rate = null;
		/**
		 *
		 * @var double
		 */
		public $baseRate = null;
		/**
		 *
		 * @var Fee
		 */
		public $fees = array();
	}
	
	//Swiftpac Create Account
	class SwiftPacCreateAccountResponse{
		
		/**
		 * 
		 * @var CreateAccountResponse
		 */
		public $createAccountResponse = null;
	}
	class CreateAccountResponse{
		
		
		/**
		 * 
		 * @var CreateAccountResult
		 */
		public $createAccountResultList = array();
		/**
		 *
		 * @var Error
		 */
		public $errors = null;
		public function addCreateAccountResult($createAccountResult){
			
			$this->createAccountResultList [] = $createAccountResult;
		}
	}
	class CreateAccountResult{
		
		/**
		 * 
		 * @var string
		 */
		public $username = null;
		/**
		 * 
		 * @var boolean
		 */
		public $isCreated = null;
		/**
		 *
		 * @var Error
		 */
		public $errors = null;
		
	}
	
	//SwiftPac Account Edit
	class SwiftPacEditAccountResponse{
	
		/**
		 *
		 * @var EditAccountResponse
		 */
		public $editAccountResponse = null;
	}
	class EditAccountResponse{
	
		/**
		 *
		 * @var EditAccountResult
		 */
		public $editAccountResultList = array();
		/**
		 * 
		 * @var Error
		 */
		public $errors;
	
		public function addEditAccountResult($editAccountResult){
				
			$this->editAccountResultList [] = $editAccountResult;
		}
	}
	class EditAccountResult{
	
		/**
		 *
		 * @var string
		 */
		public $username = null;
		/**
		 *
		 * @var boolean
		 */
		public $isEdited = null;
		/**
		 *
		 * @var Error
		 */
		public $errors = null;
	
	}
	
	//SwiftPac Account Search
	class SwiftPacAccountSearchResponse {
		
		/**
		 * 
		 * @var AccountSearchResponse
		 */
		public $accountSearchResponse = null;
	}
	class AccountSearchResponse {
		
		/**
		 * 
		 * @var AccountSearchResult
		 */
		public $accountSearchResultList = array();
		
		function addAccountSearchResult($accountSearchResult){
			
			$this->accountSearchResultList [] = $accountSearchResult;
		}
		
		/**
		 * 
		 * @var Error
		 */
		public $errors = null;
		
	}
	class AccountSearchResult {
		
		/**
		 * 
		 * @var string
		 */
		public $userLogin = null;
		/**
		 * 
		 * @var string
		 */
		public $email = null;
		/**
		 * 
		 * @var string
		 */
		public $firstName = null;
		/**
		 * 
		 * @var string
		 */
		public $lastName = null;
		/**
		 * 
		 * @var string
		 */
		public $cargoTrackClass = null;
		/**
		 * 
		 * @var string
		 */
		public $cargoTrackId = null;
		/**
		 * 
		 * @var string
		 */
		public $cargoTrackPass = null;
		/**
		 * 
		 * @var string
		 */
		public $address1 = null;
		/**
		 * 
		 * @var string
		 */
		public $address2 = null;
		/**
		 * 
		 * @var string
		 */
		public $city = null;
		/**
		 * 
		 * @var string
		 */
		public $state = null;
		/**
		 * 
		 * @var string
		 */
		public $zipCode = null;
		/**
		 * 
		 * @var string
		 */
		public $countryCode = null;
		/**
		 * 
		 * @var string
		 */
		public $mobileNumber = null;
		
	}
	
	//SwiftPac Shipment
	class SwiftPacShipmentResponse {
	
		/**
		 *
		 * @var ShipmentResponse
		 */
		public $shipmentResponse = null;
	}
	class ShipmentResponse {
		/**
		 * 
		 * @var ShipResult
		 */
		public $shipResult = null;
		
		/**
		 * 
		 * @var Error
		 */
		public $errors = null;
	}
	class ShipResult {
	
		/**
		 *
		 * @var double
		 */
		public $shipmentCharge = null;
		/**
		 *
		 * @var LocationServiceFee
		 */
		public $pickupFee = null;
		/**
		 *
		 * @var LocationServiceFee
		 */
		public $deliveryFee = null;
		/**
		 *
		 * @var double
		 */
		public $fuelCharge = null;
		/**
		 *
		 * @var double
		 */
		public $insuranceCharge = null;
		/**
		 *
		 * @var double
		 */
		public $dangerousGoodFee = null;
		/**
		 *
		 * @var DocumentList
		 */
		public $documentList = null;
		/**
		 *
		 * @var PieceList
		 */
		public $pieceList = null;
		/**
		 *
		 * @var double
		 */
		public $shipmentWeight = null;
		/**
		 *
		 * @var string
		 */
		public $trackingNumber = null;
		/**
		 *
		 * @var string
		 */
		public $label = null;
		/**
		 *
		 * @var string
		 */
		public $referenceNumber = null;
	
	}
	class Piece {	
		/**
		 *
		 * @var string
		 */
		public $trackingNumber = null;
	
		/**
		 *
		 * @var string
		 */
		public $label = null;
	
		/**
		 *
		 * @var ShipmentCharges
		 */
		public $shipmentCharges = null;
	
		/**
		 *
		 * @var string
		 */
		public $referenceNumber = null;
	
	}
	class PieceList {
	
		/**
		 *
		 * @var PieceList
		 */
		public $pieceList = array();
	}
	class Document {
	
		/**
		 *
		 * @var string
		 */
		public $type = null;
	
		/**
		 *
		 * @var string
		 */
		public $url = null;
	}
	class DocumentList {
		/**
		 *
		 * @var Document
		 */
		public $documentList = array();
	}
	class LocationServiceFee {
	
		/**
		 *
		 * @var double
		 */
		public $residentialFee = null;
		/**
		 *
		 * @var double
		 */
		public $saturdayFee = null;
		/**
		 *
		 * @var double
		 */
		public $insideFee = null;
		/**
		 *
		 * @var double
		 */
		public $forkLiftFee = null;
		/**
		 *
		 * @var double
		 */
		public $stairsFee = null;
		/**
		 *
		 * @var double
		 */
		public $perishableFee = null;
		/**
		 *
		 * @var double
		 */
		public $keepFrozenFee = null;
		/**
		 *
		 * @var double
		 */
		public $holidayFee = null;
	
	}
	
	
	//SwiftPac Account Validation
	class SwiftPacAccountValidationResponse{
		
		/**
		 * 
		 * @var AccountValidationResponse
		 */
		public $accountValidationResponse =  null;
	}
	class AccountValidationResponse{
				
		/**
		 * 
		 * @var ValidationResult
		 */
		public $validationResult = null;
		
		/**
		 *
		 * @var Error
		 */
		public $errors = null;
	}
	class ValidationResult{
		
		/**
		 * 
		 * @var boolean
		 */
		public $isValidAccount = null;
	}
	
	
	//SwiftPac Credit Card payment
	class SwiftPacCreditCardPaymentResponse {
		
		/**
		 *
		 * @var CreditCardPaymentResponse
		 */
		public $creditCardPaymentResponse = null;
		
	}
	class CreditCardPaymentResponse {
		
		/**
		 * 
		 * @var CreditCardPaymentResult
		 */
		public $creditCardPaymentResult = null;
		/**
		 * 
		 * @var Error
		 */
		public $errors = null;
	}
	class CreditCardPaymentResult {
		/**
		 * 
		 * @var string
		 */
		public $name = null;
		/**
		 * 
		 * @var boolean
		 */
		public $captured = null;
		/**
		 * 
		 * @var double
		 */
		public $amount = null;
		/**
		 *
		 * @var boolean
		 */
		public $paid = null;
		/**
		 *
		 * @var string
		 */
		public $status = null;
		/**
		 *
		 * @var string
		 */
		public $last4 = null;
		/**
		 *
		 * @var string
		 */
		public $currency = null;
		
	}
	
	// ==================End of Swiftpac Response Classes======================
	
	//==================Shared Classes=========================================
	class Error {
	
		/**
		 *
		 * @var ErrorDetails
		 */
		public $errorDetails = null;
	
	}
	
	class ErrorDetails {		
		/**
		 *
		 * @var string
		 */
		public $type = null;
		/**
		 *
		 * @var string
		 */
		public $message = null;
		/**
		 *
		 * @var string
		 */
		public $nonce = null;
		/**
		 *
		 * @var string
		 */
	
		public $digestRealm = null;
	
		/**
		 *
		 * @var AddressVerification
		 */
		public $addressVerification = null;
	}

	class AddressVerification {
		/**
		 *
		 * @var string
		 */
		public $type = null;
	
		/**
		 *
		 * @var string
		 */
		public $proposedName = null;
		/**
		 *
		 * @var string
		 */
		public $proposedAddressLine1 = null;
		/**
		 *
		 * @var string
		 */
		public $proposedCity = null;
		/**
		 *
		 * @var string
		 */
		public $proposedState = null;
		/**
		 *
		 * @var string
		 */
		public $proposedZip = null;
		/**
		 *
		 * @var string
		 */
		public $proposedCountryCode = null;
	}
	
	//===================End of Shared Classes=================================
	
	//==============Constants==================================================
	
	abstract class PackagingType {
		const USPS_LARGE_RECTANGULAR = "RECTANGULAR";
		const USPS_LARGE_NONRECTANGULAR = "NONRECTANGULAR";
		const SWIFTPAC_BARREL = "BARREL";
		const SWIFTPAC_E_CONTAINER = "E_CONTAINER";
		const SWIFTPAC_EH_CONTAINER = "EH_CONTAINER";
		const SWIFTPAC_LETTER = "LETTER";
		const SWIFTPAC_DOCUMENT = "DOCUMENT";
		const BOX = "BOX";
		const BAG = "BAG";
		const PALLET = "PALLET";
		
	}
	abstract class DomesticRateDeliveryType {
		
		const DOMESTIC_EXPRESS = "Domestic Express";
		const DOMESTIC_SMALL_PACKAGE = "Domestic Small Package";
		const DOMESTIC_BARREL = "Domestic Barrel";
		const DOMESTIC_AIR = "Domestic";
		const DOMESTIC_OCEAN = "Domestic";
		const DOMESTIC_AIR_OCEAN = "Domestic";
		const DOMESTIC = "Domestic";
		const FEDEX_GROUND = "FEDEX_GROUND";
		const FEDEX_STANDARD_OVERNIGHT = "STANDARD_OVERNIGHT";
		const DOMESTIC_EXPEDITED = "Domestic Expedited";
		const DOMESTIC_REGULAR = "Domestic Regular";
		
	}
	abstract class InternationalRateDeliveryType {
		const US_MAILBOX = "US MailBox";
		const SMALL_PACKAGE = "Small Package";
		const AIR_CARGO = "Air Cargo";
		const OCEAN_CARGO = "Ocean Cargo";
		const OCEAN_CARGO_BARREL = "Ocean Cargo Barrel";
		const OCEAN_CARGO_E_CONTAINER = "Ocean Cargo E Container";
		const OCEAN_CARGO_EH_CONTAINER = "Ocean Cargo EH Container";
		const EXPRESS = "Express";
		const EXPRESS_LETTER = "Express Letter";
		const EXPRESS_DOCUMENT_1 = "Express Document1";
		const EXPRESS_DOCUMENT_2 = "Express Document2";
		const EXPRESS_DOCUMENT_3 = "Express Document3";
		const EXPRESS_DOCUMENT_4 = "Express Document4";
		const EXPRESS_DOCUMENT_5 = "Express Document5";
		const FEDEX_INTERNATIONAL_PRIORITY = "INTERNATIONAL_PRIORITY";
		const INTERNATIONAL_EXPEDITED = "International Expedited";
		const INTERNATIONAL_REGULAR = "International Regular";
		
	}
	abstract class DomesticCommodityType {
		const SMALL_PACKAGE = "Domestic Small Package";
		const EXPRESS = "Domestic Express";
		const AIR_OCEAN = "Domestic";
		const DOMESTIC = "Domestic";
		
	}
	abstract class CaribbeanRateDeliveryType {
		
		const CARIBBEAN_EXPRESS_SAME_DAY_NON_DOC_LTR = "Caribbean Same Day Express";
		const CARIBBEAN_EXPRESS_SAME_DAY_LTR = "Caribbean Same Day Express Letter";
		const CARIBBEAN_EXPRESS_SAME_DAY_DOC = "Caribbean Same Day Express Document";
		const CARIBBEAN_EXPRESS_TWO_DAY_NON_DOC_LTR = "Caribbean 2 Day Express";
		const CARIBBEAN_EXPRESS_TWO_DAY_LTR = "Caribbean 2 Day Express Letter";
		const CARIBBEAN_EXPRESS_TWO_DAY_DOC = "Caribbean 2 Day Express Document";
	}
	abstract class TransitTime {
		const SERVICE_NOT_AVAIL = "N/A";
		const US_MAIL_BOX = "1 to 7 days";
		const SMALL_PKG = "";
		const EXPRESS_INTERNATIONAL = "Overnight to 2 days";
		const EXPRESS_CARIBBEAN_SAME_DAY = "Same Day";
		const EXPRESS_CARIBBEAN_TWO_DAY = "2 days";
		const DOMESTIC = "3 to 6 days";
		const DOMESTIC_EXPEDITED = "Overnight to 3 days";
		
	}
	abstract class SwiftPacBranch{
		
		const ANTIGUA = "ANU_3080";
		const BARBADOS = "BGI_10189";
		const DOMINICA = "DOM_2786";
		const DOMINICAN_REPUBLIC = "SDQ_16137";
		const GRENADA = "GND_4611";
		const USA_MIAMI = "USA_11233";
		const USA_NEW_YORK = "USA_5687";
		const USA_ORLANDO = "MCO_22759";
		const VENEZUELA = "CCS_16138";
		const ST_VINCENT = "SVD_12177";
		const ST_LUCIA = "SLU_15693";
		const TRINIDAD = "POS_10217";
		const GUYANA = "GEO";
		const HONDURAS = "TGU_16140";
		const JAMAICA = "KIN_16135";
		const PANAMA = "PAN_16139";
		const PUERTO_RICO = "SJU_16141";
		const ST_KITTS_NEVIS = "SKB_4520";
		const ST_MAARTEN = "SXM";
		const TORTOLA = "EIS";
	}
	abstract class SwiftPacPremiumPaymentMethod {
	
		const CASH = "Cash";
		const CREDIT_CARD = "Credit/Debit Card";
		const PAYPAL = "Paypal";
	}
	abstract class SwiftpacAccountType {
	
		const PRIVATE_ACC = "PRIVATE";
		const PREMIUM_ACC = "PREMIUM";
	}
	abstract class SwiftPacCurrency {
		const USD = "USD";
		const XCD = "XCD";
		const TTD = "TTD";
		const BBD = "BBD";
	}
	abstract class SwiftPacShipmentType {
		const PREPAY = "PREPAY";
		const FREIGHT_COLLECT = "FREIGHT_COLLECT";
		
	}
//=====================End of Constants=========================================
}


?>