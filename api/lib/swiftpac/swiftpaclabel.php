<?php 
namespace lib\SwiftPac;

use lib\SwiftPac\DataTypes\Shipment;
use lib\SwiftPac\DataTypes\Address;


require(dirname(__DIR__).'/fpdf/fpdf.php');
require(dirname(__DIR__).'/php_barcode_gen/src/BarcodeGenerator.php');
require(dirname(__DIR__).'/php_barcode_gen/src/BarcodeGeneratorPNG.php');


class SwiftPacLabel {
	
	//Label Text
	static private $shipperLabelText = "Shipper:";
	static private $consigneeLabelText = "Recepient:";
	static private $pkgDestLabelText = "Destination:";
	static private $serviceLabelText = "Service:";
	static private $RouteAgentLabelText = "Agent";
	static private $shipDateLabelText = "Ship Date:";
	static private $shipperAccNumLableText = "Sender's Acc";
	static private $consigneeAccNumLableText = "Recipient Acc";
	
	// Address
	private $addr1;
	private $city;
	private $state;
	private $zip;
	private $telephone;
	
	//Label data
	private $shipperName;
	private $shipperCompany;
	private $shipperAddr1;
	private $shipperAccount;
	private $shipperCity;
	private $shipperState;
	private $shipperZip;
	private $shipperCountryCode;
	private $shipperCountry;
	
	
	private $recipientName;
	private $recipientCompany;
	private $recipientAddr1;
	private $recipientCity;
	private $recipientState;
	private $recipientZip;
	private $recipientCountryCode;
	private $recipientCountry;
	private $recipientAccount;
	
	private $pkgWeight;
	private $pkgDims;
	private $pkgPieces;
	private $pkgDesc;
	private $pkgCost;
	private $pkgLabelId;
	private $pkgDest;
	
	private $agent;
	private $service;
	private $shipDate;
	
	
	public function __construct(Shipment $shipment, Address $labelAddress = null, $pkgNumPiece, $serviceType){
		
		if ($shipment == null) throw new \Exception("Shipment parameter cannot be null");
		
		//Adding  address on label
		if ($labelAddress == null){
			$this->addr1 ="6948 NW 50th St.";
			$this->city = "Miami";
			$this->state = "FL";
			$this->zip = "33166";
			$this->telephone = "305-470-8998";
		}
		else {
			$this->addr1 = $labelAddress->addressLine1;
			$this->city = $labelAddress->city;
			$this->state = $labelAddress->state;
			$this->zip = $labelAddress->zip;
			$this->telephone = "305-470-8998";
				
		}
		
		$this->var_error_log(array("direc" =>dirname(__DIR__).'/php_barcode_gen/src/BarcodeGeneratorPNG.php'));
		
		//Adding customer and package info
		//Sender
		$this->shipperName = $shipment->shipFrom->name;
		$this->shipperCompany = $shipment->shipFrom->company;
		$this->shipperAddr1 = $shipment->shipFrom->addressLine1;
		$this->shipperCity = $shipment->shipFrom->city;
		$this->shipperState = $shipment->shipFrom->state;
		$this->shipperZip = $shipment->shipFrom->zip;
		$this->shipperAccount = $shipment->shipperAccNum;
		$this->shipperCountryCode = $shipment->shipFrom->countryCode;
		
		//Recipient
		$this->recipientName = $shipment->shipTo->name;
		$this->recipientCompany = $shipment->shipTo->company;
		$this->recipientAddr1 = $shipment->shipTo->addressLine1;
		$this->recipientCity = $shipment->shipTo->city;
		$this->recipientState = $shipment->shipTo->state;
		$this->recipientZip = $shipment->shipTo->zip;
		$this->recipientAccount = $shipment->recipientAccNum;
		$this->recipientCountryCode = $shipment->shipTo->countryCode;
		
		//Package Info
		$pkgCount = count($shipment->packageList->packages);
		if ($pkgNumPiece == null)throw new \Exception('Package number cannot be null');
		if ($pkgNumPiece > $pkgCount)throw new \Exception('Package number exceeds maximum number of packges in shipment');
		
		$packages = $shipment->packageList->packages;
		if (!is_array($packages)) $packages = array($packages);
		$pkgPiece = $packages[$pkgNumPiece-1]; //a specific package from the packge list
		$this->pkgWeight = $pkgPiece->weight . "Lbs";
		$this->pkgDims = $pkgPiece->dimensions->length . "x". $pkgPiece->dimensions->width. "x". $pkgPiece->dimensions->height;
		$this->pkgPieces = "(". $pkgNumPiece . "/". $pkgCount. ")";
		$this->pkgDesc = $pkgPiece->contentDescription;
		$this->pkgCost = "$". $pkgPiece->notifiedValue;
		$this->pkgLabelId = $pkgPiece->packageLabelId;
		$this->pkgDest = $this->countryCodeToAirportCode($shipment->shipTo->countryCode);
		$this->agent = "SWIFTPAC " . $this->pkgDest;
		if ($serviceType == null) throw new \Exception("ServiceType cannot be null");
		$this->service = $serviceType;
		$this->shipDate = $shipment->shipDate;
		
		$this->var_error_log(array("serviceType" => $serviceType));
		
		
		//Create Images
		$label = imagecreatetruecolor(444,662);
		$logo     = imagecreatefrompng(dirname(__DIR__)."/images/logo_swiftpac.png");
		$logoResized = imagecreatetruecolor(170, 42.5);
		$generatorPNG = new \Picqer\Barcode\BarcodeGeneratorPNG();
		
		file_put_contents(dirname(__DIR__).'/images/barcode.png', $generatorPNG->getBarcode('S'.$swiftPacZip.$this->pkgLabelId, $generatorPNG::TYPE_CODE_128,2,70));
		$barcode = imagecreatefrompng(dirname(__DIR__).'/images/barcode.png');
		
		//colors
		$labelOrange = imagecolorallocate($label, 220, 210, 60);
		$labelBlack = imagecolorallocate($label, 0, 0, 0);
		$labelWhite = imagecolorallocate($label, 255, 255, 255);
		$lableTransWhite = imagecolorallocatealpha($label, 255, 255, 255, 127);
		$logoResizedWhite = imagecolorallocate($logoResized, 255, 255, 255);
		
		
		//Fill Images Background
		imagefill($logoResized, 0, 0, $logoResizedWhite);
		imagefill($label,0,0,$labelWhite);
		
		
		//New Container for resized logo
		imagecopyresized($logoResized, $logo, 0, 0, 0, 0, 170, 42.5, imagesx($logo), imagesy($logo));
		$logo = $logoResized;
		
		//Copy images on to lable
		imagecopy($label,$logo,$this->getWidthPercent(65, $label),$this->getHeightPercent(3, $label),0,0,$this->getWidthPercent(100, $logo),$this->getHeightPercent(100, $logo));
		imagecopy($label,$barcode,$this->getWidthPercent(11, $label),$this->getHeightPercent(83, $label),0,0,$this->getWidthPercent(100, $barcode),$this->getHeightPercent(100, $barcode));
		
		//Fonts
		$morningTypeLight = dirname(__DIR__)."/fonts/Morningtype Light.ttf";
		$morningType = dirname(__DIR__)."/fonts/Morningtype.ttf";
		$morningTypeBold = dirname(__DIR__)."/fonts/Morningtype Bold.ttf";
		
		$robotoReg = dirname(__DIR__)."/fonts/roboto/Roboto-Regular.ttf";
		$robotoLight = dirname(__DIR__)."/fonts/roboto/Roboto-Light.ttf";
		$robotoRegular = dirname(__DIR__)."/fonts/roboto/Roboto-Regular.ttf";
		$robotoBold = dirname(__DIR__)."/fonts/roboto/Roboto-Bold.ttf";
		
		$openSansReg = dirname(__DIR__)."/fonts/openSans/OpenSans-Regular.ttf";
		$openSansLight = dirname(__DIR__)."/fonts/openSans/OpenSans-Light.ttf";
		$openSansBold = dirname(__DIR__)."/fonts/openSans/OpenSans-Bold.ttf";
		
		//Default fonts
		$defaultFontNormal = $openSansReg;
		$defaultFontBold = $openSansBold;
		$defaultFontLight = $openSansLight;
		
		//Default Font size
		$defaultFontSize = 11;
		$defaultStartWidthEdgeOfLabel = $this->getWidthPercent(1, $label);
		$defaultEndWidthEdgeOfLabel = $this->getWidthPercent(99, $label);
		
		$borderTopX1 = $this->getWidthPercent(1, $label);
		$borderTopY1 = $this->getHeightPercent(1, $label);
		$borderTopX2 = $this->getWidthPercent(99, $label);
		$borderTopY2 = $this->getHeightPercent(1, $label);
		
		$borderBottomX1 = $this->getWidthPercent(1, $label);
		$borderBottomY1 = $this->getHeightPercent(99, $label);
		$borderBottomX2 = $this->getWidthPercent(99, $label);
		$borderBottomY2 = $this->getHeightPercent(99, $label);
		
		$borderLeftX1 = $this->getWidthPercent(1, $label);
		$borderLeftY1 = $this->getHeightPercent(1, $label);
		$borderLeftX2 = $this->getWidthPercent(1, $label);
		$borderLeftY2 = $this->getHeightPercent(99, $label);
		
		$borderRightX1 = $this->getWidthPercent(99, $label);
		$borderRightY1 = $this->getHeightPercent(1, $label);
		$borderRightX2 = $this->getWidthPercent(99, $label);
		$borderRightY2 = $this->getHeightPercent(99, $label);
		
		$this->imagelinethick($label, $borderTopX1, $borderTopY1, $borderTopX2, $borderTopY2, $black,2); //hr line 1
		$this->imagelinethick($label, $borderBottomX1, $borderBottomY1, $borderBottomX2, $borderBottomY2, $black,2); //hr line 1
		$this->imagelinethick($label, $borderLeftX1, $borderLeftY1, $borderLeftX2, $borderLeftY2, $black,2); //vr line 1
		$this->imagelinethick($label, $borderRightX1, $borderRightY1, $borderRightX2, $borderRightY2, $black,2); //vr line 1
		
		
		//Swiftpac address
		imagettftext($label, 9, 0, $this->getWidthPercent(70, $label), $this->getHeightPercent(11, $label),$labelBlack, $defaultFontLight, $this->addr1); //address 1
		imagettftext($label, 9, 0, $this->getWidthPercent(70, $label), $this->getHeightPercent(13, $label),$labelBlack, $defaultFontLight, $this->city . " ". $this->state. " " .$this->zip);//City State and ZIp
		imagettftext($label, 9, 0, $this->getWidthPercent(70, $label), $this->getHeightPercent(15, $label),$labelBlack, $defaultFontLight, $this->telephone);//telephone
		
		//Shipper Text
		imagettftext($label, 12, 0, $this->getWidthPercent(4, $label), $this->getHeightPercent(7, $label),$labelBlack, $defaultFontBold, self::$shipperLabelText);//shipper label text
		imagettftext($label, $defaultFontSize, 0, $this->getWidthPercent(6, $label), $this->getHeightPercent(10, $label),$labelBlack, $defaultFontNormal, $this->shipperName);//shipper Name
		imagettftext($label, $defaultFontSize, 0, $this->getWidthPercent(6, $label), $this->getHeightPercent(12, $label),$labelBlack, $defaultFontNormal, $this->shipperCompany);//shipper company
		imagettftext($label, $defaultFontSize, 0, $this->getWidthPercent(6, $label), $this->getHeightPercent(14, $label),$labelBlack, $defaultFontNormal, $this->shipperAddr1);//shipper Address 1
		imagettftext($label, $defaultFontSize, 0, $this->getWidthPercent(6, $label), $this->getHeightPercent(16, $label),$labelBlack, $defaultFontNormal, $this->shipperCity . " ". $this->shipperState. " ". $this->shipperZip);//shipper city state and zip
		imagettftext($label, $defaultFontSize, 0, $this->getWidthPercent(6, $label), $this->getHeightPercent(18, $label),$labelBlack, $defaultFontNormal, $this->shipperCountryCode);//shipper country
		
		$this->imagelinethick($label, $defaultStartWidthEdgeOfLabel, $this->getHeightPercent(19, $label), $defaultEndWidthEdgeOfLabel, $this->getHeightPercent(19, $label), $black,2); //hr line 2
		
		//Recipent Text
		imagettftext($label, 12, 0, $this->getWidthPercent(4, $label), $this->getHeightPercent(22, $label),$labelBlack, $defaultFontBold, self::$consigneeLabelText);// Consignee Label text
		imagettftext($label, $defaultFontSize, 0, $this->getWidthPercent(6, $label), $this->getHeightPercent(25, $label),$labelBlack, $defaultFontBold, $this->recipientName);// Consignee Name
		imagettftext($label, $defaultFontSize, 0, $this->getWidthPercent(6, $label), $this->getHeightPercent(27, $label),$labelBlack, $defaultFontBold, $this->recipientCompany);// Consignee Company
		imagettftext($label, $defaultFontSize, 0, $this->getWidthPercent(6, $label), $this->getHeightPercent(29, $label),$labelBlack, $defaultFontBold, $this->recipientAddr1);//Consignee Address
		imagettftext($label, $defaultFontSize, 0, $this->getWidthPercent(6, $label), $this->getHeightPercent(31, $label),$labelBlack, $defaultFontBold, $this->recipientCity . " ". $this->recipientState. " ". $this->recipientZip);//
		imagettftext($label, $defaultFontSize, 0, $this->getWidthPercent(6, $label), $this->getHeightPercent(33, $label),$labelBlack, $defaultFontBold, $this->recipientCountryCode);//Consignee Country code
		
		//Shipper / Recipient detials
		imagettftext($label, $defaultFontSize, 0, $this->getWidthPercent(55, $label), $this->getHeightPercent(22, $label),$labelBlack, $defaultFontBold, self::$shipperAccNumLableText. ": " .$this->shipperAccount);// Shipper's Account Number
		imagettftext($label, $defaultFontSize, 0, $this->getWidthPercent(55, $label), $this->getHeightPercent(25, $label),$labelBlack, $defaultFontBold, self::$consigneeAccNumLableText .": " .$this->recipientAccount);// Consignee Account Number
		imagettftext($label, $defaultFontSize, 0, $this->getWidthPercent(55, $label), $this->getHeightPercent(28, $label),$labelBlack, $defaultFontBold, self::$shipDateLabelText  .$this->shipDate);// ship date
		
		
		$this->imagelinethick($label, $defaultStartWidthEdgeOfLabel, $this->getHeightPercent(34, $label), $defaultEndWidthEdgeOfLabel, $this->getHeightPercent(34, $label), $black,2); //hr line 3
		
		
		//Data to be centered on label
		$charToPercentFactor = 2.5;
		$charToPercentWhNumFactor = 9;
		$weightDimsPieces = $this->pkgWeight . "  ". $this->pkgDims. "  ". $this->pkgPieces;
		$pkgDescCost = $this->pkgDesc . " ". $this->pkgCost;
		
		$labelWidthMidPoint = $this->getWidthPercent(50, $label);
		
		$weightDimsPiecesLenMidPoint = (strlen($weightDimsPieces) * $charToPercentFactor)/2;
		$pkgDescLenMidPoint = (strlen($pkgDescCost) * $charToPercentFactor)/2;
		$whNumMidPoint = (strlen($this->pkgLabelId)*$charToPercentWhNumFactor)/2;
		$agentMidPoint = (strlen($this->agent)*$charToPercentFactor)/2;
		
		$weightDimsPiecesStartPos = $labelWidthMidPoint - $this->getWidthPercent($weightDimsPiecesLenMidPoint,$label);
		$pkgDescStartPos = $labelWidthMidPoint - $this->getWidthPercent($pkgDescLenMidPoint, $label);
		$whNumStartPos = $labelWidthMidPoint - $this->getWidthPercent($whNumMidPoint, $label);
		$agentStartPos = $labelWidthMidPoint - $this->getWidthPercent($agentMidPoint, $label) ;
		
		// var_error_log(array("weightdimsstr" => $weightDimsPiecesStartPos,"weightdimsLen" => $weightDimsPiecesLen, "labelMidPoint" => $labelWidthMidPoint, "weightDimsPiecesLenMidPoint" => $weightDimsPiecesLenMidPoint));
		
		//Package details
		imagettftext($label, 17, 0, $weightDimsPiecesStartPos,
				$this->getHeightPercent(38, $label),$labelBlack, $defaultFontNormal, $weightDimsPieces);// Consignee Label text
		imagettftext($label, 17, 0, $pkgDescStartPos, 
				$this->getHeightPercent(41, $label),$labelBlack, $defaultFontNormal, $pkgDescCost);// Consignee Label text
		
		$this->imagelinethick($label, $defaultStartWidthEdgeOfLabel, 
				$this->getHeightPercent(42, $label), $defaultEndWidthEdgeOfLabel, $this->getHeightPercent(42, $label), $black,2); //hr line 4
		
		//Warehouse Number
		imagettftext($label, 50, 0, $whNumStartPos,
				$this->getHeightPercent(57, $label),$labelBlack, $defaultFontBold, $this->pkgLabelId);// Warehouse Number
		
		
		//Package Destination and Agent
		$this->imagelinethick($label, $defaultStartWidthEdgeOfLabel,
				$this->getHeightPercent(58, $label), $defaultEndWidthEdgeOfLabel, $this->getHeightPercent(58, $label), $black,2); //hr line 5
		imagettftext($label, 12, 0, $this->getWidthPercent(4, $label),
				$this->getHeightPercent(61, $label),$labelBlack, $defaultFontNormal, self::$pkgDestLabelText);// package description
		imagettftext($label, 40, 0, $this->getWidthPercent(6, $label),
				$this->getHeightPercent(68, $label),$labelBlack, $defaultFontBold, $this->pkgDest);// package destination
		
		$this->imagelinethick($label, $this->getWidthPercent(35, $label), 
				$this->getHeightPercent(58, $label), $this->getWidthPercent(35, $label), $this->getHeightPercent(69, $label), $black,2); //vr line 1
		
		imagettftext($label, 12, 0, $this->getWidthPercent(36, $label),
				$this->getHeightPercent(61, $label),$labelBlack, $defaultFontNormal, self::$RouteAgentLabelText);//pakage label route
		imagettftext($label, 20, 0, $this->getWidthPercent(36, $label),
				$this->getHeightPercent(68, $label),$labelBlack, $defaultFontBold, $this->agent);// agent of package
		
		//Package Service
		$this->imagelinethick($label, $defaultStartWidthEdgeOfLabel,
				$this->getHeightPercent(69, $label), $defaultEndWidthEdgeOfLabel, $this->getHeightPercent(69, $label), $black,2); //hr line 6
		imagettftext($label, 12, 0, $this->getWidthPercent(4, $label),
				$this->getHeightPercent(72, $label),$labelBlack, $defaultFontNormal, self::$serviceLabelText);// Service Label text
		imagettftext($label, 40, 0, $this->getWidthPercent(10, $label),
				$this->getHeightPercent(82, $label),$labelBlack, $defaultFontBold, $this->service);//
		
		
		//SwiftPac label as png
		imagepng($label,dirname(dirname(__DIR__)).'/labels/splabel_'.$this->pkgLabelId.'.png',0);
		// imagepng($label);
		
		//Free up resources
		imagedestroy($barcode);
		imagedestroy($label);
		imagedestroy($logo);
		
		//Generate and save label as pdf
		$pdf = new \FPDF('p','mm',array(118,176));
		$pdf->AddPage();
		$pdf->Image(dirname(dirname(__DIR__)).'/labels/splabel_'.$this->pkgLabelId.'.png', 0,0,null,null,'PNG');
		$pdf->Output("F",dirname(dirname(__DIR__)).'/labels/splabel_'.$this->pkgLabelId.'.pdf');
		
		
	}
	
	public function getPDFHtlmLink(){
		
		echo "<p><a href = '../../labels/splabel_".$this->pkgLabelId.".pdf'>Click to View Label in PDF Format</a></p>";
	}
	
	public function getPNGHtmlLink(){
		echo "<p><a href = '../../labels/splabel_".$this->pkgLabelId.".png'>Click to View Label in PNG Format</a></p>";
		
	}
	
	
	private function getWidthPercent($px,$im){
	
		$imgWidth = imagesx($im);
		$pxPercent = number_format($px/100,2);
	
		return $imgWidth * $pxPercent;
	
	}
	
	private function getHeightPercent($px,$im){
	
	
		$imgHeight = imagesy($im);
		$pxPercent = number_format($px/100,2);
	
		return $imgHeight * $pxPercent;
	}
	
	
	private function barcode( $filepath="", $text="0", $size="20", $orientation="horizontal", $code_type="code128", $print=false ) {
		$code_string = "";
		// Translate the $text into barcode the correct $code_type
		if ( in_array(strtolower($code_type), array("code128", "code128b")) ) {
			$chksum = 104;
			// Must not change order of array elements as the checksum depends on the array's key to validate final code
			$code_array = array(" "=>"212222","!"=>"222122","\""=>"222221","#"=>"121223","$"=>"121322","%"=>"131222","&"=>"122213","'"=>"122312","("=>"132212",")"=>"221213","*"=>"221312","+"=>"231212",","=>"112232","-"=>"122132","."=>"122231","/"=>"113222","0"=>"123122","1"=>"123221","2"=>"223211","3"=>"221132","4"=>"221231","5"=>"213212","6"=>"223112","7"=>"312131","8"=>"311222","9"=>"321122",":"=>"321221",";"=>"312212","<"=>"322112","="=>"322211",">"=>"212123","?"=>"212321","@"=>"232121","A"=>"111323","B"=>"131123","C"=>"131321","D"=>"112313","E"=>"132113","F"=>"132311","G"=>"211313","H"=>"231113","I"=>"231311","J"=>"112133","K"=>"112331","L"=>"132131","M"=>"113123","N"=>"113321","O"=>"133121","P"=>"313121","Q"=>"211331","R"=>"231131","S"=>"213113","T"=>"213311","U"=>"213131","V"=>"311123","W"=>"311321","X"=>"331121","Y"=>"312113","Z"=>"312311","["=>"332111","\\"=>"314111","]"=>"221411","^"=>"431111","_"=>"111224","\`"=>"111422","a"=>"121124","b"=>"121421","c"=>"141122","d"=>"141221","e"=>"112214","f"=>"112412","g"=>"122114","h"=>"122411","i"=>"142112","j"=>"142211","k"=>"241211","l"=>"221114","m"=>"413111","n"=>"241112","o"=>"134111","p"=>"111242","q"=>"121142","r"=>"121241","s"=>"114212","t"=>"124112","u"=>"124211","v"=>"411212","w"=>"421112","x"=>"421211","y"=>"212141","z"=>"214121","{"=>"412121","|"=>"111143","}"=>"111341","~"=>"131141","DEL"=>"114113","FNC 3"=>"114311","FNC 2"=>"411113","SHIFT"=>"411311","CODE C"=>"113141","FNC 4"=>"114131","CODE A"=>"311141","FNC 1"=>"411131","Start A"=>"211412","Start B"=>"211214","Start C"=>"211232","Stop"=>"2331112");
			$code_keys = array_keys($code_array);
			$code_values = array_flip($code_keys);
			for ( $X = 1; $X <= strlen($text); $X++ ) {
				$activeKey = substr( $text, ($X-1), 1);
				$code_string .= $code_array[$activeKey];
				$chksum=($chksum + ($code_values[$activeKey] * $X));
			}
			$code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];
	
			$code_string = "211214" . $code_string . "2331112";
		} elseif ( strtolower($code_type) == "code128a" ) {
			$chksum = 103;
			$text = strtoupper($text); // Code 128A doesn't support lower case
			// Must not change order of array elements as the checksum depends on the array's key to validate final code
			$code_array = array(" "=>"212222","!"=>"222122","\""=>"222221","#"=>"121223","$"=>"121322","%"=>"131222","&"=>"122213","'"=>"122312","("=>"132212",")"=>"221213","*"=>"221312","+"=>"231212",","=>"112232","-"=>"122132","."=>"122231","/"=>"113222","0"=>"123122","1"=>"123221","2"=>"223211","3"=>"221132","4"=>"221231","5"=>"213212","6"=>"223112","7"=>"312131","8"=>"311222","9"=>"321122",":"=>"321221",";"=>"312212","<"=>"322112","="=>"322211",">"=>"212123","?"=>"212321","@"=>"232121","A"=>"111323","B"=>"131123","C"=>"131321","D"=>"112313","E"=>"132113","F"=>"132311","G"=>"211313","H"=>"231113","I"=>"231311","J"=>"112133","K"=>"112331","L"=>"132131","M"=>"113123","N"=>"113321","O"=>"133121","P"=>"313121","Q"=>"211331","R"=>"231131","S"=>"213113","T"=>"213311","U"=>"213131","V"=>"311123","W"=>"311321","X"=>"331121","Y"=>"312113","Z"=>"312311","["=>"332111","\\"=>"314111","]"=>"221411","^"=>"431111","_"=>"111224","NUL"=>"111422","SOH"=>"121124","STX"=>"121421","ETX"=>"141122","EOT"=>"141221","ENQ"=>"112214","ACK"=>"112412","BEL"=>"122114","BS"=>"122411","HT"=>"142112","LF"=>"142211","VT"=>"241211","FF"=>"221114","CR"=>"413111","SO"=>"241112","SI"=>"134111","DLE"=>"111242","DC1"=>"121142","DC2"=>"121241","DC3"=>"114212","DC4"=>"124112","NAK"=>"124211","SYN"=>"411212","ETB"=>"421112","CAN"=>"421211","EM"=>"212141","SUB"=>"214121","ESC"=>"412121","FS"=>"111143","GS"=>"111341","RS"=>"131141","US"=>"114113","FNC 3"=>"114311","FNC 2"=>"411113","SHIFT"=>"411311","CODE C"=>"113141","CODE B"=>"114131","FNC 4"=>"311141","FNC 1"=>"411131","Start A"=>"211412","Start B"=>"211214","Start C"=>"211232","Stop"=>"2331112");
			$code_keys = array_keys($code_array);
			$code_values = array_flip($code_keys);
			for ( $X = 1; $X <= strlen($text); $X++ ) {
				$activeKey = substr( $text, ($X-1), 1);
				$code_string .= $code_array[$activeKey];
				$chksum=($chksum + ($code_values[$activeKey] * $X));
			}
			$code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];
	
			$code_string = "211412" . $code_string . "2331112";
		} elseif ( strtolower($code_type) == "code39" ) {
			$code_array = array("0"=>"111221211","1"=>"211211112","2"=>"112211112","3"=>"212211111","4"=>"111221112","5"=>"211221111","6"=>"112221111","7"=>"111211212","8"=>"211211211","9"=>"112211211","A"=>"211112112","B"=>"112112112","C"=>"212112111","D"=>"111122112","E"=>"211122111","F"=>"112122111","G"=>"111112212","H"=>"211112211","I"=>"112112211","J"=>"111122211","K"=>"211111122","L"=>"112111122","M"=>"212111121","N"=>"111121122","O"=>"211121121","P"=>"112121121","Q"=>"111111222","R"=>"211111221","S"=>"112111221","T"=>"111121221","U"=>"221111112","V"=>"122111112","W"=>"222111111","X"=>"121121112","Y"=>"221121111","Z"=>"122121111","-"=>"121111212","."=>"221111211"," "=>"122111211","$"=>"121212111","/"=>"121211121","+"=>"121112121","%"=>"111212121","*"=>"121121211");
	
			// Convert to uppercase
			$upper_text = strtoupper($text);
	
			for ( $X = 1; $X<=strlen($upper_text); $X++ ) {
				$code_string .= $code_array[substr( $upper_text, ($X-1), 1)] . "1";
			}
	
			$code_string = "1211212111" . $code_string . "121121211";
		} elseif ( strtolower($code_type) == "code25" ) {
			$code_array1 = array("1","2","3","4","5","6","7","8","9","0");
			$code_array2 = array("3-1-1-1-3","1-3-1-1-3","3-3-1-1-1","1-1-3-1-3","3-1-3-1-1","1-3-3-1-1","1-1-1-3-3","3-1-1-3-1","1-3-1-3-1","1-1-3-3-1");
	
			for ( $X = 1; $X <= strlen($text); $X++ ) {
				for ( $Y = 0; $Y < count($code_array1); $Y++ ) {
					if ( substr($text, ($X-1), 1) == $code_array1[$Y] )
						$temp[$X] = $code_array2[$Y];
				}
			}
	
			for ( $X=1; $X<=strlen($text); $X+=2 ) {
				if ( isset($temp[$X]) && isset($temp[($X + 1)]) ) {
					$temp1 = explode( "-", $temp[$X] );
					$temp2 = explode( "-", $temp[($X + 1)] );
					for ( $Y = 0; $Y < count($temp1); $Y++ )
						$code_string .= $temp1[$Y] . $temp2[$Y];
				}
			}
	
			$code_string = "1111" . $code_string . "311";
		} elseif ( strtolower($code_type) == "codabar" ) {
			$code_array1 = array("1","2","3","4","5","6","7","8","9","0","-","$",":","/",".","+","A","B","C","D");
			$code_array2 = array("1111221","1112112","2211111","1121121","2111121","1211112","1211211","1221111","2112111","1111122","1112211","1122111","2111212","2121112","2121211","1121212","1122121","1212112","1112122","1112221");
	
			// Convert to uppercase
			$upper_text = strtoupper($text);
	
			for ( $X = 1; $X<=strlen($upper_text); $X++ ) {
				for ( $Y = 0; $Y<count($code_array1); $Y++ ) {
					if ( substr($upper_text, ($X-1), 1) == $code_array1[$Y] )
						$code_string .= $code_array2[$Y] . "1";
				}
			}
			$code_string = "11221211" . $code_string . "1122121";
		}
	
		// Pad the edges of the barcode
		$code_length = 20;
		if ($print) {
			$text_height = 30;
		} else {
			$text_height = 0;
		}
	
		for ( $i=1; $i <= strlen($code_string); $i++ ){
			$code_length = $code_length + (integer)(substr($code_string,($i-1),1));
		}
	
		if ( strtolower($orientation) == "horizontal" ) {
			$img_width = $code_length;
			$img_height = $size;
		} else {
			$img_width = $size;
			$img_height = $code_length;
		}
	
		$image = imagecreate($img_width, $img_height + $text_height);
		$black = imagecolorallocate ($image, 0, 0, 0);
		$white = imagecolorallocate ($image, 255, 255, 255);
	
		imagefill( $image, 0, 0, $white );
		if ( $print ) {
			imagestring($image, 5, 31, $img_height, $text, $black );
		}
	
		$location = 10;
		for ( $position = 1 ; $position <= strlen($code_string); $position++ ) {
			$cur_size = $location + ( substr($code_string, ($position-1), 1) );
			if ( strtolower($orientation) == "horizontal" )
				imagefilledrectangle( $image, $location, 0, $cur_size, $img_height, ($position % 2 == 0 ? $white : $black) );
				else
					imagefilledrectangle( $image, 0, $location, $img_width, $cur_size, ($position % 2 == 0 ? $white : $black) );
					$location = $cur_size;
		}
		// 	var_dump($image);
	
		return $image;
		// 	Draw barcode to the screen or save in a file
		// 	if ( $filepath=="" ) {
		// 		header ('Content-type: image/png');
		// 		imagepng($image);
		// 		imagedestroy($image);
		// 	} else {
		// 		imagepng($image,$filepath);
		// 		imagedestroy($image);
		// 	}
	}
	
	private function imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1)
	{
		/* this way it works well only for orthogonal lines
		 imagesetthickness($image, $thick);
		 return imageline($image, $x1, $y1, $x2, $y2, $color);
		 */
		if ($thick == 1) {
			return imageline($image, $x1, $y1, $x2, $y2, $color);
		}
		$t = $thick / 2 - 0.5;
		if ($x1 == $x2 || $y1 == $y2) {
			return imagefilledrectangle($image, round(min($x1, $x2) - $t), round(min($y1, $y2) - $t), round(max($x1, $x2) + $t), round(max($y1, $y2) + $t), $color);
		}
		$k = ($y2 - $y1) / ($x2 - $x1); //y = kx + q
		$a = $t / sqrt(1 + pow($k, 2));
		$points = array(
				round($x1 - (1+$k)*$a), round($y1 + (1-$k)*$a),
				round($x1 - (1-$k)*$a), round($y1 - (1+$k)*$a),
				round($x2 + (1+$k)*$a), round($y2 - (1-$k)*$a),
				round($x2 + (1-$k)*$a), round($y2 + (1+$k)*$a),
		);
		imagefilledpolygon($image, $points, 4, $color);
		return imagepolygon($image, $points, 4, $color);
	}
	
	private function countryCodeToAirportCode($countryCode){
		
		$countryCode = strtoupper($countryCode);
		switch ($countryCode){
			
			case "VC":
				return "SVD";
			case "US":
				switch ($this->city){
					case "Miami":
						return "MIA";
					
				}
			case "TT":
				return "POS";
			case "LC":
				return "SLU";
			case "GD":
				return "GND";
			case "BB":
				return "BGI";
			
		}
	}
	
	// ==================Debugging Functions===========================
	private function var_error_log($object = null) {
		ob_start (); // start buffer capture
		var_dump ( $object ); // dump the values
		$contents = ob_get_contents (); // put the buffer into a variable
		ob_end_clean (); // end capture
		error_log ( $contents, 3, "switpac_api_log.log" ); // log contents of the result of var_dump( $object )
	}
}

?>