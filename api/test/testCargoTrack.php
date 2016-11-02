<?php 
include_once '../../swiftpac1/simplehtmldom_1_5/simple_html_dom.php';

//username and password of account
$username = urlencode("Miami Swiftpac Unknown");
$password = urldecode("SPUSA0011");
$action = urldecode("login");


//login form action url
$url="http://swiftpac.cargotrack.net/default.asp";
$postinfo = "user=".$username."&password=".$password."&action=".$action;


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt ($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postinfo);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_exec($ch);

//page with the content I want to grab
curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/client/default.asp");
// curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/client/whs_onhand.asp?sc=12190");
// curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/client/whs_detail.asp?id=127566");
//do stuff with the info with DomDocument() etc
$resp = curl_exec($ch);

$status = curl_getinfo($ch); //info on session that was connected


// Parse document returned from cargotrack -- Getting warehouse numbers from on hand packages 

// var_dump($html);


//Create DOM using data from cargotrack
$DOM = new DOMDocument();
// var_dump(strval($resp));
$DOM->loadHTML($resp);
// $htmlStr = $DOM->saveHTMLFile(__DIR__.'/cargoTrack.html');
$htmlStr = $DOM->saveHTML();
$html = str_get_html($htmlStr);
// $mytables = $html->find('a');
$tables = $DOM->getElementsByTagName('table');

$data = $tables[7]->nodeValue; // find table containting onhand pakcages 

$dataArrayRows = explode("USA",$data); //capture each row of data 

// $row1 = trim(preg_replace('/[\s\t\n\r\s]+/', ' ', $dataArrayRows[0])); //Clean up each row of data to remove un necessary characaters

$warehouseNumbers = array(); //Use to store warehouse numbers in unkonwn 

//Parse each row and extract the warehouse number column
foreach ($dataArrayRows as $key => $dataRow){
	
	$dataRow = trim(preg_replace('/[\0\x0B\s\t\n\r\s]+/', ' ', $dataRow));//Clean up each row of data to remove un necessary characaters
	$dataCols = explode(" ", $dataRow);
	
// 	var_dump($dataCols);
	
	if (is_numeric($dataCols[1])) $warehouseNumbers[] = $dataCols[1]; //Only capture warehouse numbers that are numeric

}

$table = $html->find('table')[7];
// echo $table->children();
$tableRows = $table->children(1)->children();

$warehousePackageData = array(array());

foreach ($table->children() as $tblKey => $tblRow){
	
// 	echo "tableKey" . $tblKey;
// 	echo $tblRow;
	foreach($tblRow->children() as  $tblRowKey => $tblRowData) {
		
		
// 		echo $tblRowKey;
// 	    echo $tblRowData;
	    $warehousePackageData[$tblKey][$tblRowKey] = $tblRowData->plaintext;
	    
// 	    echo $tblRowKey;
	    if ($tblRowKey == 2 && is_numeric($tblRowData->plaintext)){
	    	
// 	    	echo "in";
// 	    	echo $tblRowData->plaintext;
	    	curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/client/whs_detail.asp?id=" . $tblRowData->plaintext);
	    	// curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/client/whs_onhand.asp?sc=12190");
	    	// curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/client/whs_detail.asp?id=127566");
	    	//do stuff with the info with DomDocument() etc
	    	$resp = curl_exec($ch);
	    	
// 	    	echo $resp;
	    	$status = curl_getinfo($ch); //info on session that was connected
	    	
	    	
	    	// Parse document returned from cargotrack -- Getting warehouse numbers from on hand packages
	    	
	    	// var_dump($html);
	    	
	    	
	    	//Create DOM using data from cargotrack
	    	$DOM = new DOMDocument();
	    	$DOM->loadHTML($resp);
	    	// $htmlStr = $DOM->saveHTMLFile(__DIR__.'/cargoTrack.html');
	    	$htmlStr = $DOM->saveHTML();
	    	$html = str_get_html($htmlStr);
	    	// $mytables = $html->find('a');
	    	// $tables = $DOM->getElementsByTagName('table');
	    	// foreach ($html->find("table") as $key => $tbl){
	    	// 	echo $key;
	    	// 	echo $tbl;
	    	// }
	    	$table1 = $html->find('table')[9]; // table to get tracking
	    	$table2 = $html->find('table')[10]; // to get description
	    	
	    	$deliveredBy = $table1->children()[1]->children(3)->children(0)->children(0)->value;; //tracking number
	    	$trackingNum = $table1->children()[2]->children()[3]->plaintext; //tracking number
	    	$trackingNum = trim(preg_replace('/[\0\x0B\s\t\n\r\s]+/', ' ', $trackingNum));
	    	$description = $table2->children()[0]->children()[1]->plaintext;
	    	$description = trim(preg_replace('/[\0\x0B\s\t\n\r\s]+/', ' ', $description));
	    	
	    	$warehousePackageData[$tblKey][10] = $description;
	    	$warehousePackageData[$tblKey][9]= $trackingNum;
	    	$warehousePackageData[$tblKey][11]= $deliveredBy;
	    	
	    	
	    }
	    
	}
}


var_dump($warehousePackageData);

curl_close($ch);


// $str = $html->save();

// var_dump($str);
// echo "Unknown Warehouse Numbers";
// echo "<ul>";
// foreach ($warehouseNumbers as $whNum){
	
// 	echo "<li><a >$whNum</a></li>";
// }
// echo "</ul>";
// 	var_dump($dataArrayRows);

// foreach($DOM->getElementsByTagName('table') as $key => $table) {
//         # Show the <a href>
        
// 	$value = $DOM->saveHTML($table);
	
//         echo $value;
// // 		var_dump($table);
// //         echo $link->getAttribute('href');
//         echo "<br />";
// }
// parse_str($DOM,$output);

// var_dump($status);
// var_dump($resp);

// $tbodys = $DOM->getElementsByTagName("tbody");
// var_dump($DOM);

// foreach ($tbodys as $key => $tbody){
// 	echo $tbody;
// }


// echo $html;

// echo '<form action="http://swiftpac.cargotrack.net/default.asp" method="post" name="form1" target="_blank" id="form1">
// <input type="hidden" value ="'  . $loggedin_username .  '" id="user" name="user">
// <input type="hidden" value="' . $cargotrackpw_forlogin . '" id="password" name="password" maxlength="128">
// <input class="cargo-track-button" type="submit" value="MANAGE YOUR CARGO" id ="ctBtnLogin" name="Submit">
// <input type="hidden" name="action" value="login"></form>';

?>