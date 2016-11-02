<?php 
require('lib/fpdf/fpdf.php');
require('lib/php_barcode_gen/src/BarcodeGenerator.php');
require('lib/php_barcode_gen/src/BarcodeGeneratorPNG.php');

// header("Content-type: image/png");


//Label Text
$shipperLabelText = "Shipper:";
$consigneeLabelText = "Recipient:";
$pkgDestLabelText = "Destination:";
$serviceLabelText = "Service:";
$RouteAgentLabelText = "Agent:";
$shipDateLabelText = "Ship Date:";
$shipperAccNumLableText = "Sender's Acc:";
$shipperAccNumLableText = "Shipper Acc:";
$consigneeAccNumLableText = "Recipient Acc:";

//SwiftPac Address
$swiftPacAddr1 = "6948 NW 50th St.";
$swiftPacCity = "Miami";
$swiftPacState = "FL";
$swiftPacZip = "33166";
$swiftPactTelephone = "Tel: 305-470-8998";

//Label data
$shipperName = "John Doe";
$shipperCompany = "My Company";
$shipperAddr1 = "65 Flatbush Ave.";
$shipperAccount = "USA11111";
$shipperCity= "Kingston";
$shipperState = "NY";
$shipperZip = "12401";
$shipperCountry = "United States";


$recipientName = "Mark Doe";
$recipientCompany = "My Company";
$recipientAddr1 = "6948 NW 50th St.";
$recipientCity = "Miami";
$consigneeState = "FL";
$consigneeZIP = "33166";
$recipientCountryCode = "United States";
$consigneeAccount = "USA00000";

$pkgWeight = "10 LBS";
$pkgDims = "6x5x1";
$pkgPieces = "(1/1)";
$pkgDesc = "Description: Computer";
$pkgCost = "$23.00";
$whNum = "G04444";
$pkgDest = "MIA";

$agent = "SWIFTPAC MIA";
$service = "GROUND";
$shipDate = "3/7/16";



//Create Images
$label = imagecreatetruecolor(444,662);
$logo     = imagecreatefrompng("lib/images/logo_swiftpac.png");
$logoResized = imagecreatetruecolor(170, 42.5);
$generatorPNG = new Picqer\Barcode\BarcodeGeneratorPNG();
file_put_contents('lib/images/barcode.png', $generatorPNG->getBarcode('S'.$swiftPacZip.$whNum, $generatorPNG::TYPE_CODE_128,2,70));
$barcode = imagecreatefrompng('lib/images/barcode.png');


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
imagecopy($label,$logo,getWidthPercent(65, $label),getHeightPercent(3, $label),0,0,getWidthPercent(100, $logo),getHeightPercent(100, $logo));
imagecopy($label,$barcode,getWidthPercent(11, $label),getHeightPercent(83, $label),0,0,getWidthPercent(100, $barcode),getHeightPercent(100, $barcode));


//Fonts
$morningTypeLight = "lib/fonts/Morningtype Light.ttf";
$morningType = "lib/fonts/Morningtype.ttf";
$morningTypeBold = "lib/fonts/Morningtype Bold.ttf";

$robotoReg = "lib/fonts/roboto/Roboto-Regular.ttf";
$robotoLight = "lib/fonts/roboto/Roboto-Light.ttf";
$robotoRegular = "lib/fonts/roboto/Roboto-Regular.ttf";
$robotoBold = "lib/fonts/roboto/Roboto-Bold.ttf";

$openSansReg = "lib/fonts/openSans/OpenSans-Regular.ttf";
$openSansLight = "lib/fonts/openSans/OpenSans-Light.ttf";
$openSansBold = "lib/fonts/openSans/OpenSans-Bold.ttf";

//Default fonts
$defaultFontNormal = $openSansReg;
$defaultFontBold = $openSansBold;
$defaultFontLight = $openSansLight;

//Default Font size
$defaultFontSize = 11;
$defaultStartWidthEdgeOfLabel = getWidthPercent(1, $label);
$defaultEndWidthEdgeOfLabel = getWidthPercent(99, $label);



//=======================================Draw Data and  lines on label=========================================
//bordering
$borderTopX1 = getWidthPercent(1, $label);
$borderTopY1 = getHeightPercent(1, $label);
$borderTopX2 = getWidthPercent(99, $label);
$borderTopY2 = getHeightPercent(1, $label);

$borderBottomX1 = getWidthPercent(1, $label);
$borderBottomY1 = getHeightPercent(99, $label);
$borderBottomX2 = getWidthPercent(99, $label);
$borderBottomY2 = getHeightPercent(99, $label);

$borderLeftX1 = getWidthPercent(1, $label);
$borderLeftY1 = getHeightPercent(1, $label);
$borderLeftX2 = getWidthPercent(1, $label);
$borderLeftY2 = getHeightPercent(99, $label);

$borderRightX1 = getWidthPercent(99, $label);
$borderRightY1 = getHeightPercent(1, $label);
$borderRightX2 = getWidthPercent(99, $label);
$borderRightY2 = getHeightPercent(99, $label);

imagelinethick($label, $borderTopX1, $borderTopY1, $borderTopX2, $borderTopY2, $black,2); //hr line 1
imagelinethick($label, $borderBottomX1, $borderBottomY1, $borderBottomX2, $borderBottomY2, $black,2); //hr line 1
imagelinethick($label, $borderLeftX1, $borderLeftY1, $borderLeftX2, $borderLeftY2, $black,2); //vr line 1
imagelinethick($label, $borderRightX1, $borderRightY1, $borderRightX2, $borderRightY2, $black,2); //vr line 1


//Swiftpac address
imagettftext($label, 9, 0, getWidthPercent(70, $label), getHeightPercent(11, $label),$labelBlack, $defaultFontLight, $swiftPacAddr1);//shipper 
imagettftext($label, 9, 0, getWidthPercent(70, $label), getHeightPercent(13, $label),$labelBlack, $defaultFontLight, $swiftPacCity . " ". $swiftPacState. " " .$swiftPacZip );//shipper 
imagettftext($label, 9, 0, getWidthPercent(70, $label), getHeightPercent(15, $label),$labelBlack, $defaultFontLight, $swiftPactTelephone);//shipper 

//Shipper Text
imagettftext($label, 12, 0, getWidthPercent(4, $label), getHeightPercent(7, $label),$labelBlack, $defaultFontBold, $shipperLabelText);//shipper label text
imagettftext($label, $defaultFontSize, 0, getWidthPercent(6, $label), getHeightPercent(10, $label),$labelBlack, $defaultFontNormal, $shipperName);//shipper 
imagettftext($label, $defaultFontSize, 0, getWidthPercent(6, $label), getHeightPercent(12, $label),$labelBlack, $defaultFontNormal, $shipperCompany);//shipper 
imagettftext($label, $defaultFontSize, 0, getWidthPercent(6, $label), getHeightPercent(14, $label),$labelBlack, $defaultFontNormal, $shipperAddr1);//shipper 
imagettftext($label, $defaultFontSize, 0, getWidthPercent(6, $label), getHeightPercent(16, $label),$labelBlack, $defaultFontNormal, $shipperCity . " ". $shipperState. " ". $shipperZip);//shipper 
imagettftext($label, $defaultFontSize, 0, getWidthPercent(6, $label), getHeightPercent(18, $label),$labelBlack, $defaultFontNormal, $shipperCountry);//shipper 

imagelinethick($label, $defaultStartWidthEdgeOfLabel, getHeightPercent(19, $label), $defaultEndWidthEdgeOfLabel, getHeightPercent(19, $label), $black,2); //hr line 2

//Recipent Text
imagettftext($label, 12, 0, getWidthPercent(4, $label), getHeightPercent(22, $label),$labelBlack, $defaultFontBold, $consigneeLabelText);// Consignee Label text 
imagettftext($label, $defaultFontSize, 0, getWidthPercent(6, $label), getHeightPercent(25, $label),$labelBlack, $defaultFontBold, $recipientName);// Consignee Name
imagettftext($label, $defaultFontSize, 0, getWidthPercent(6, $label), getHeightPercent(27, $label),$labelBlack, $defaultFontBold, $recipientCompany);// Consignee Name
imagettftext($label, $defaultFontSize, 0, getWidthPercent(6, $label), getHeightPercent(29, $label),$labelBlack, $defaultFontBold, $recipientAddr1);// 
imagettftext($label, $defaultFontSize, 0, getWidthPercent(6, $label), getHeightPercent(31, $label),$labelBlack, $defaultFontBold, $recipientCity . " ". $consigneeState. " ". $consigneeZIP);// 
imagettftext($label, $defaultFontSize, 0, getWidthPercent(6, $label), getHeightPercent(33, $label),$labelBlack, $defaultFontBold, $recipientCountryCode);//

//Shipper / Recipient detials
imagettftext($label, $defaultFontSize, 0, getWidthPercent(55, $label), getHeightPercent(22, $label),$labelBlack, $defaultFontBold, $shipperAccNumLableText  .$shipperAccount);// Consignee Account Number
imagettftext($label, $defaultFontSize, 0, getWidthPercent(55, $label), getHeightPercent(25, $label),$labelBlack, $defaultFontBold, $consigneeAccNumLableText  .$consigneeAccount);// Consignee Account Number
imagettftext($label, $defaultFontSize, 0, getWidthPercent(55, $label), getHeightPercent(28, $label),$labelBlack, $defaultFontBold, $shipDateLabelText  .$shipDate);// Consignee Account Number


imagelinethick($label, $defaultStartWidthEdgeOfLabel, getHeightPercent(34, $label), $defaultEndWidthEdgeOfLabel, getHeightPercent(34, $label), $black,2); //hr line 3


//Data to be centered on label
$charToPercentFactor = 2.5;
$charToPercentWhNumFactor = 16;
$weightDimsPieces = $pkgWeight . "  ". $pkgDims. "  ". $pkgPieces;
$pkgDescCost = $pkgDesc . " ". $pkgCost;

$labelWidthMidPoint = getWidthPercent(50, $label);

$weightDimsPiecesLenMidPoint = (strlen($weightDimsPieces) * $charToPercentFactor)/2;
$pkgDescLenMidPoint = (strlen($pkgDescCost) * $charToPercentFactor)/2;
$whNumMidPoint = (strlen($whNum)*$charToPercentWhNumFactor)/2;
$agentMidPoint = (strlen($agent)*$charToPercentFactor)/2;

$weightDimsPiecesStartPos = $labelWidthMidPoint - getWidthPercent($weightDimsPiecesLenMidPoint,$label);
$pkgDescStartPos = $labelWidthMidPoint - getWidthPercent($pkgDescLenMidPoint, $label);
$whNumStartPos = $labelWidthMidPoint - getWidthPercent($whNumMidPoint, $label);
$agentStartPos = $labelWidthMidPoint - getWidthPercent($agentMidPoint, $label) ;

// var_error_log(array("weightdimsstr" => $weightDimsPiecesStartPos,"weightdimsLen" => $weightDimsPiecesLen, "labelMidPoint" => $labelWidthMidPoint, "weightDimsPiecesLenMidPoint" => $weightDimsPiecesLenMidPoint));

//Package details
imagettftext($label, 17, 0, $weightDimsPiecesStartPos, getHeightPercent(38, $label),$labelBlack, $defaultFontNormal, $weightDimsPieces);// Consignee Label text 
imagettftext($label, 17, 0, $pkgDescStartPos, getHeightPercent(41, $label),$labelBlack, $defaultFontNormal, $pkgDescCost);// Consignee Label text 


imagelinethick($label, $defaultStartWidthEdgeOfLabel, getHeightPercent(42, $label), $defaultEndWidthEdgeOfLabel, getHeightPercent(42, $label), $black,2); //hr line 4

//Warehouse Number
imagettftext($label, 90, 0, $whNumStartPos, getHeightPercent(57, $label),$labelBlack, $defaultFontBold, $whNum);// Consignee Label text 


//Package Destination and Agent
imagelinethick($label, $defaultStartWidthEdgeOfLabel, getHeightPercent(58, $label), $defaultEndWidthEdgeOfLabel, getHeightPercent(58, $label), $black,2); //hr line 5
imagettftext($label, 12, 0, getWidthPercent(4, $label), getHeightPercent(61, $label),$labelBlack, $defaultFontNormal, $pkgDestLabelText);//  
imagettftext($label, 40, 0, getWidthPercent(6, $label), getHeightPercent(68, $label),$labelBlack, $defaultFontBold, $pkgDest);//  

imagelinethick($label, getWidthPercent(35, $label), getHeightPercent(58, $label), getWidthPercent(35, $label), getHeightPercent(69, $label), $black,2); //vr line 1

imagettftext($label, 12, 0, getWidthPercent(36, $label), getHeightPercent(61, $label),$labelBlack, $defaultFontNormal, $RouteAgentLabelText);//  
imagettftext($label, 20, 0, getWidthPercent(36, $label), getHeightPercent(68, $label),$labelBlack, $defaultFontBold, $agent);// Consignee Label text 

//Package Service
imagelinethick($label, $defaultStartWidthEdgeOfLabel, getHeightPercent(69, $label), $defaultEndWidthEdgeOfLabel, getHeightPercent(69, $label), $black,2); //hr line 6
imagettftext($label, 12, 0, getWidthPercent(4, $label), getHeightPercent(72, $label),$labelBlack, $defaultFontNormal, $serviceLabelText);//  
imagettftext($label, 40, 0, getWidthPercent(10, $label), getHeightPercent(82, $label),$labelBlack, $defaultFontBold, $service);//  


//SwiftPac label as png
imagepng($label,'labels/splabel_'.$whNum.'.png',0);
// imagepng($label);

//Free up resources
imagedestroy($barcode);
imagedestroy($label);
imagedestroy($logo);

//Generate and save label as pdf
$pdf = new FPDF('p','mm',array(118,176));
$pdf->AddPage();
$pdf->Image('labels/splabel_'.$whNum.'.png', 0,0,null,null,'PNG');
// $pdf->Output("F",'labels/splabel_'.$whNum.'.pdf'); 
$pdf->Output(); 

echo "<p><a href = 'labels/splabel_".$whNum.".pdf'>Click to View Label in PDF Format</a></p>";
echo "<p><a href = 'labels/splabel_".$whNum.".png'>Click to View Label in PNG Format</a></p>";

//=================================Helper Functions ======================================
function getWidthPercent($px,$im){
	
	$imgWidth = imagesx($im);
	$pxPercent = number_format($px/100,2);
	
	return $imgWidth * $pxPercent;
	
}

function getHeightPercent($px,$im){
	
	
	$imgHeight = imagesy($im);
	$pxPercent = number_format($px/100,2);
	
	return $imgHeight * $pxPercent;
}


function barcode( $filepath="", $text="0", $size="20", $orientation="horizontal", $code_type="code128", $print=false ) {
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

function imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1)
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
function var_error_log($object = null) {
	ob_start (); // start buffer capture
	var_dump ( $object ); // dump the values
	$contents = ob_get_contents (); // put the buffer into a variable
	ob_end_clean (); // end capture
	error_log ( $contents, 3, "switpac_api_log.log" ); // log contents of the result of var_dump( $object )
}

//====================================End of Helper Functions=============================================
?>

