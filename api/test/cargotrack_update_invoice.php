<?php 
require_once '/home/vincyavi/public_html/swiftpac1/simplehtmldom_1_5/simple_html_dom.php';

function updateInvoice(array $invoiceInfo, $updateRequest){

	
// 	var_dump($invoiceInfo);
	//username and password of account
	$username = urlencode("spsupport");
	$password = urldecode("svdAdmin123");
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
	
	
	
	$invoice_post_fields = "&insurance=".$invoiceInfo['insurance']
	."&cr=".$invoiceInfo['cr']
	."&url=".$invoiceInfo['url']
	."&id=".$invoiceInfo['id']
	."&close=".$invoiceInfo['close']
	."&branch=".$invoiceInfo['branch']
	."&currency=".$invoiceInfo['currency']
	
	."&units1=".$invoiceInfo['units']
	."&gl_account1=".$invoiceInfo['gl_account']
	."&cc_number1=".$invoiceInfo['cc_number']
	."&vendor_id1=".$invoiceInfo['vendor_id']
	."&expense1=".$invoiceInfo['expense']
	."&gp1=".$invoiceInfo['gp']
	."&type1=".$invoiceInfo['type']
	."&payableline_id1=".$invoiceInfo['payableline_id']
	."&line_id1=".$invoiceInfo['line_id']
	."&description1=".$invoiceInfo['description']
	."&price_unit1=".$invoiceInfo['price_unit']
	."&subtotal1=".$invoiceInfo['subtotal']
	."&invoice_number=".$invoiceInfo['invoice_number']
	."&date=".$invoiceInfo['date']
	."&client_id=".$invoiceInfo['client_id']
	."&client_name=".$invoiceInfo['client_name']
	."&client_address1=".$invoiceInfo['client_address1']
	."&client_address2=".$invoiceInfo['client_address2']
	."&client_address3=".$invoiceInfo['client_address3']
	."&client_phone=".$invoiceInfo['client_phone']
	."&cy=".$invoiceInfo['currency']
	."&cc=".$invoiceInfo['cc']
	."&shipper=".$invoiceInfo['shipper']
	."&consignee=".$invoiceInfo['consignee']
	."&carrier=".$invoiceInfo['carrier']
	."&entry=".$invoiceInfo['entry']
	."&etd=".$invoiceInfo['etd']
	."&eta=".$invoiceInfo['eta']
	."&credit=".$invoiceInfo['credit']
	."&rs=".$invoiceInfo['rs']
	."&incoterm=".$invoiceInfo['incoterm']
	."&paystatus=".$invoiceInfo['paystatus']
	."&exchange_rate2=".$invoiceInfo['exchange_rate2']
	."&warehouse_id=".$invoiceInfo['warehouse_id']
	."&purchasing_id=".$invoiceInfo['purchasing_id']
	."&house_number=".$invoiceInfo['house_number']
	."&house_id=".$invoiceInfo['house_id']
	."&master_idv=".$invoiceInfo['master_idv']
	."&master_id=".$invoiceInfo['master_id']
	."&master_number=".$invoiceInfo['master_number']
	."&pieces=".$invoiceInfo['pieces']
	."&gweight=".$invoiceInfo['gweight']
	."&volume=".$invoiceInfo['volume']
	."&cweight=".$invoiceInfo['cweight']
	."&origin=".$invoiceInfo['origin']
	."&destination=".$invoiceInfo['destination']
	."&po=".$invoiceInfo['po']
	."&reference=".$invoiceInfo['reference']
	."&total_records=".$invoiceInfo['total_records']
	."&num_lines=".$invoiceInfo['num_lines']
	."&remarks1=".$invoiceInfo['remarks1']
	."&remarks2=".$invoiceInfo['remarks2']
	."&remarks3=".$invoiceInfo['remarks3']
	."&docs=".$invoiceInfo['docs']
	."&action=".$invoiceInfo['action'];
	
	curl_setopt($ch, CURLOPT_URL,"http://swiftpac.cargotrack.net/appl2.0/invoices/update.asp?");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $invoice_post_fields);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	
	
	
	
	//page with the content I want to grab
	// 		curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/index/default.asp");
	
// 	$action = urldecode("new");
// 	$invoice_postinfo = "date=".$date."&client_id=".$client_id."&client_name=".$client_name."&client_address1=".$client_address1
// 	."&client_address2=".$client_address2."&client_address3=".$client_address3."&client_phone=".$client_phone
// 	."&created_by=".$created_by."&updated_by=".$updated_by."&currency=".$currency."&branch=".$branch."&po=".$po."&action=".$action;
// 	curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/invoices/add_invoice.asp");
// 	curl_setopt($ch, CURLOPT_POST, 1);
// 	curl_setopt($ch, CURLOPT_POSTFIELDS, $invoice_postinfo);
// 	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
// 	curl_exec($ch);
	
	//open document
// 	curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/invoices/open.asp?id=47270");
	
	
	
	
	
// 	$payment_info = "units3=".$paymentInfo["units"]. "&gl_account3=". $paymentInfo["gl_account"]. "&cc_number3=". $paymentInfo["cc_number"]
// 	."&vendor_id3". $paymentInfo["vendor_id"]. "&expense3 =". $paymentInfo["expense"]. "&gp3=".$paymentInfo["gp"]. "&type3=".$paymentInfo["type"]
// 	."&payableline_id3=".$paymentInfo["payableline_id"]. "&line_id3=".$paymentInfo["line_id"]."&description3=".$paymentInfo["description"]
// 	."&price_unit3=".$payment_info["price_unit"]."&subtotal=".$payment_info["subtotal"]."&tax=".$paymentInfo["tax"]."&action=".$action;
	
// 	$update = "action=update";
// 	curl_setopt($ch, CURLOPT_URL,"http://swiftpac.cargotrack.net/appl2.0/invoices/update.asp?id=47270#");
// 	curl_setopt($ch, CURLOPT_POST, 1);
// 	curl_setopt($ch, CURLOPT_POSTFIELDS, $update);
// 	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	$resp = curl_exec($ch);
	
// 	curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/print/label_whs_courier.asp?id=128618");
	// curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/client/whs_onhand.asp?sc=12190");
	// curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/client/whs_detail.asp?id=127566");
	//do stuff with the info with DomDocument() etc

// 	$status = curl_getinfo($ch); //info on session that was connected

	//get invoice number and line id
	//Create DOM using data from cargotrack
	$DOM = new DOMDocument();
	// var_dump(strval($resp));
	$DOM->loadHTML($resp);
	// $htmlStr = $DOM->saveHTMLFile(__DIR__.'/cargoTrack.html');
	$htmlStr = $DOM->saveHTML();
	$html = str_get_html($htmlStr);
	// $mytables = $html->find('a');
	$tables = $DOM->getElementsByTagName('table');

	$data = $tables[1];
	
// 	var_dump($data);
// 	foreach ($html->find('table') as $key => $table){
// 		echo $key . "=>" . $table;
// 	}
// 	echo $tables;
	
	$table = $html->find('table')[7];
	$invoiceNumber = $table->children(0)->children(0)->children(0)->children(0)->children(0)->children(0)->children(1)->children(1)->children(0)->value	;
	
	echo $invoiceNumber . "<br>";
	
	$lineId1 = $html->find('*[id=line_id1]')[0]->value;
// 	$table = $html->find('table')[12];
	
// 	$lineId = $table->children(1);
// var_dump($lineId1);
	echo $lineId1 . "line id<br>";
	echo "<br> end of php echo";
	


	// Parse document returned from cargotrack -- Getting$ warehouse numbers from on hand packages

	//Create DOM using data from cargotrack
// 	$DOM = new DOMDocument();
// 	$DOM->loadHTML($resp);
// 	$htmlStr = $DOM->saveHTML();
// 	$html = str_get_html($htmlStr);
	// 	$table = $html->find('table')[7];
	// 		echo $table->children();

	// 	$binary = base64_decode($resp);
// 	file_put_contents(__DIR__."/label2.pdf", $resp);

// 	header('Content-type: application/pdf');
// 	// 	header("Content-Disposition: attachment; filename='". __DIR__."/label.pdf'");

// 	// 	echo "file_get_contents(__DIR__."/label.pdf");

// 	echo "<p><a href = 'label2.pdf'>Click to View Label in PDF Format</a></p>";


}

if (isset($_POST["submit_invoice"])){
	
// 	echo "data sent";
	
// 	extract($_POST);
	
	foreach ($_POST as $key => $value){
		
// 		echo '."&'.$key. '=".' . '$invoiceInfo['."'".$key."'".']'."<br>";
// 		echo "'".$key. "'=>'". $value."',<br>";
	}
	
	$updateInvoiceInfo = array (
			
	'insurance'=>'',
	'cr'=>'N',
	'url'=>'update.asp',
	'id'=>'47580',
	'close'=>'N',
	'action'=>'update',
	'branch'=>'ITS',
	'invoice_number'=>'47580',
	'date'=>'2016/8/3',
	'client_id'=>'24007',
	'client_name'=>'ITSupport',
	'client_address1'=>'',
	'client_address2'=>'',
	'client_address3'=>'',
	'client_phone'=>'',
	'currency'=>'XCD',
	'cc'=>'0',
	'shipper'=>'',
	'consignee'=>'',
	'carrier'=>'',
	'entry'=>'',
	'etd'=>'',
	'eta'=>'',
	'credit'=>'N',
	'rs'=>'N',
	'incoterm'=>'',
	'paystatus'=>'No',
	'exchange_rate2'=>'0.0000000',
	'warehouse_id'=>'125729',
	'purchasing_id'=>'0',
	'house_number'=>'0',
	'house_id'=>'0',
	'master_idv'=>'',
	'master_id'=>'0',
	'master_number'=>'0',
	'pieces'=>'0',
	'gweight'=>'0',
	'volume'=>'0',
	'cweight'=>'0',
	'origin'=>'',
	'destination'=>'',
	'po'=>'',
	'reference'=>'',
	'units'=>'1',
	'gl_account'=>'1000',
	'cc_number'=>'0',
	'vendor_id'=>'0',
	'expense'=>'0',
	'gp'=>'N',
	'type'=>'',
	'payableline_id'=>'0',
	'line_id'=>'301507',
	'description'=>'Test  payment 1',
	'price_unit'=>'100',
	'subtotal'=>'100.00',
	'total_records'=>'1',
	'num_lines'=>'0',
	'remarks1'=>'',
	'remarks2'=>'',
	'remarks3'=>'',
	'docs'=>''
	);
	
// 	var_dump($_POST);
	
// echo $date;	
updateInvoice($updateInvoiceInfo);
// createWarehouse();


}


?>

<html><!--  -->
<head>

</head>
<body>
<p> update Invoice on CargoTrack Test Form </p>
<form name="form1" method="post" action="cargotrack_update_invoice.php">

<input type = "submit" name ="submit_invoice" value = "submit">
	      <table width="100%" border="0" cellspacing="0" cellpadding="5">
            <tbody><tr>
              <td>
		        <div align="left" class="etext">
		          </div></td>
              
              <td>
			  
              <div align="right">
                
                <input name="insurance" type="hidden" id="insurance" value="">
                <input name="cr" type="hidden" id="cr" value="N">
                <input name="url" type="hidden" id="url2" value="update.asp">                
                <input name="id" type="hidden" id="id" value="47580">
                <input name="close" type="hidden" id="close" value="N">
                <input name="action" type="hidden" id="action" value="update">
				
                <a href="#" onmousedown="javascript:document.form1.url.value='update.asp'" onmouseup="document.getElementById('save').style.display='none';
document.getElementById('loading').removeAttribute('style');javascript:document.form1.submit()"><img src="../../icons/document-save@2x.png" width="25" id="save" title="Save" border="0" align="absmiddle"><img src="/images/loader-save.gif" border="0" align="absmiddle" alt="loader" id="loading" style="display:none;"></a>
                
                &nbsp;
				<a href="#" onclick="GP_AdvOpenWindow('../upload_multiple.asp?action=start&amp;id=47580&amp;type=I&amp;directory=Invoice','Upload','fullscreen=no,toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=no,channelmode=no,directories=no',570,400,'center','ignoreLink','',0,'',0,1,5,'');return document.MM_returnValue"><img src="../../icons/paper-clip-mini@2x.png" alt="Document" width="25" border="0" align="absmiddle"></a>&nbsp;<a href="#" onclick="GP_AdvOpenWindow('../scan/scan.asp?doc_id=47580&amp;doc_type=I','Scan','fullscreen=no,toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=no,channelmode=no,directories=no',450,550,'center','ignoreLink','',0,'',0,1,5,'');return document.MM_returnValue"><img src="../../icons/screen-area-7@2x.png" alt="Document" width="25" border="0" align="absmiddle"></a>&nbsp;
				
                <a href="#" onclick="GP_AdvOpenWindow('../remarks.asp?id=47580&amp;line=INVOICE','Remarks','fullscreen=no,toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=no,channelmode=no,directories=no',500,300,'center','ignoreLink','',0,'',0,1,5,'');return document.MM_returnValue"><img src="../../icons/message-multiple-7@2x.png" width="25" title="Remarks" border="0" align="absmiddle"></a>&nbsp;
                
                <a href="#" onclick="GP_AdvOpenWindow('../log.asp?id=47580&amp;type=I','Log','fullscreen=no,toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=no,channelmode=no,directories=no',600,300,'center','ignoreLink','',0,'',0,1,5,'');return document.MM_returnValue"><img src="../../icons/heart-rate-mini@2x.png" width="25" title="Transaction log" border="0" align="absmiddle"></a>
                
                &nbsp;<a href="delete.asp?id=47580&amp;auto=N&amp;cr=N&amp;house_id=0"><img src="../../icons/dustbin-7@2x.png" width="25" title="Delete" border="0" align="absmiddle"></a>
                

				</div>
				
                </td>
                
                
                
            </tr>
          </tbody></table>
	    
	    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="ntext">
          <tbody><tr>
            <td valign="top"><table width="100%" border="0" cellpadding="5" cellspacing="0" class="insert">
              <tbody><tr>
                <td width="60%" valign="top"><table width="100%" border="0" cellpadding="2" cellspacing="0" class="ntext">
                  <tbody><tr>
                    <td width="85">Branch</td>
                    <td>
                    <select name="branch" class="ntext" id="branch">
                        
						<option value="ANU">ANU&nbsp;-&nbsp;Swiftpac ANU</option>
						
						<option value="AXA">AXA&nbsp;-&nbsp;Swiftpac AXA</option>
						
						<option value="BGI">BGI&nbsp;-&nbsp;SwiftPac BGI</option>
						
						<option value="DOM">DOM&nbsp;-&nbsp;SwiftPac DOM</option>
						
						<option value="EIS">EIS&nbsp;-&nbsp;SwiftPac BVI</option>
						
						<option value="GEO">GEO&nbsp;-&nbsp;SwiftPac GUY</option>
						
						<option value="GN2">GN2&nbsp;-&nbsp;Swiftpac Grenada 2</option>
						
						<option value="GND">GND&nbsp;-&nbsp;Swiftpac GND</option>
						
						<option value="ITS" selected="">ITS&nbsp;-&nbsp;SwiftPac ITSD</option>
						
						<option value="MCO">MCO&nbsp;-&nbsp;Swiftpac Orlando</option>
						
						<option value="MIA">MIA&nbsp;-&nbsp;Swiftpac MIA</option>
						
						<option value="NYC">NYC&nbsp;-&nbsp;Swiftpac NY</option>
						
						<option value="POS">POS&nbsp;-&nbsp;Swiftpac TNT</option>
						
						<option value="SKB">SKB&nbsp;-&nbsp;Swiftpac SKB</option>
						
						<option value="SLU">SLU&nbsp;-&nbsp;SwiftPac SLU</option>
						
						<option value="SVD">SVD&nbsp;-&nbsp;SwiftPac SVD</option>
						
						<option value="SVG">SVG&nbsp;-&nbsp;SwiftPac City Office</option>
						
						<option value="USA">USA&nbsp;-&nbsp;Swiftpac USA</option>
						
                      </select>                    
                      </td>
                  </tr>
                  <tr>
                    <td>Invoice</td>
                    <td>
                      
                      <input name="invoice_number" type="text" class="ntext" id="invoice_number" value="47580" size="15" readonly="">
                      
                     </td>
                  </tr>
                  <tr>
                    <td>Date</td>
                    <td>
         
         <input name="date" type="text" class="ntext hasDatepicker" id="datepicker" value="2016/8/1" size="10"><img class="ui-datepicker-trigger" src="/icons/calendar.png" alt="..." title="...">
		                     
         			 </td>
                  </tr>
                  <tr>
                    <td>Account&nbsp;&nbsp;&nbsp;<span class="smallform"><a href="#" onclick="GP_AdvOpenWindow('../accounts/lookup.asp?ref1=client_id&amp;ref2=client_name&amp;ref3=client_address1&amp;ref4=client_address2&amp;ref5=client_phone','Lookup','fullscreen=no,toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=no,channelmode=no,directories=no',500,250,'center','ignoreLink','',0,'',0,1,5,'');return document.MM_returnValue"></a></span></td>
                    <td>
                      
                      <a href="#" onclick="GP_AdvOpenWindow('../accounts/lookup.asp?ref1=client_id&amp;ref2=client_name&amp;ref3=client_address1&amp;ref4=client_address2&amp;ref5=client_phone','Lookup','fullscreen=no,toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=no,channelmode=no,directories=no',500,250,'center','ignoreLink','',0,'',0,1,5,'');return document.MM_returnValue"><img src="../../icons/search-7@2x.png" width="15" height="15" border="0" align="absmiddle"></a>
                      <input name="client_id" type="text" class="ntext" id="client_id" value="24007" size="5" onfocus="checkColor1(this)" onblur="checkColor2(this)">
                      <input name="client_name" type="text" class="ntext" id="client_name" value="ITSupport" size="25" onfocus="checkColor1(this)" onblur="checkColor2(this)">
                      
<input name="client_address1" type="hidden" id="client_address1">
                      <input name="client_address2" type="hidden" id="client_address2">
                      <input name="client_address3" type="hidden" id="client_address3">
                      <input name="client_phone" type="hidden" id="client_phone"></td>
                  </tr>
                  <tr>
                    <td>Currency</td>
                    <td><select name="currency" class="ntext" id="currency">
                      
					  <option value="BBD">BBD&nbsp;-&nbsp;Barbados Dollar</option>
					  
					  <option value="TTD">TTD&nbsp;-&nbsp;Trinidad &amp; Tobago Dollar</option>
					  
					  <option value="USD">USD&nbsp;-&nbsp;US Dollar</option>
					  
					  <option value="XCD" selected="">XCD&nbsp;-&nbsp;Eestern Caribbean Dollar</option>
					  
                    </select>
                      </td>
                  </tr>
                  <tr>
                    <td>Total Invoiced</td>
                    <td><input name="textfield" type="text" class="extralarge" id="textfield" value="0.00" size="15" disabled="true">&nbsp;

					</td>
                  </tr>
                    <tr>
                      <td>Total Paid</td>
                      <td><div align="left"><strong>
                        <input name="textfield2" type="text" class="large" id="textfield2" value="0.00" size="15" disabled="true">
                      </strong>&nbsp;
                          <a href="update.asp?id=47580&amp;currency=XCD&amp;action=zero">Set to balance</a>
                          </div></td>
                    </tr>
                    
                    <tr>
                      <td>Cost Center</td>
                      <td><select name="cc" class="ntext" id="cc">
                        <option value="0"></option>
                        
                        <option value="1">0&nbsp;-&nbsp;DM</option>
                        
                      </select>
                      </td>
                    </tr>
                    <tr>
                      <td>Shipper</td>
                      <td><div align="left">
                        <input name="shipper" type="text" class="ntext" id="shipper" value="" size="35">
                        </div></td>
                    </tr>
                    <tr>
                      <td>Consignee</td>
                      <td><input name="consignee" type="text" class="ntext" id="consignee" value="" size="35"></td>
                    </tr>
                    <tr>
                      <td>Carrier</td>
                      <td><input name="carrier" type="text" class="ntext" id="carrier" value="" size="35"></td>
                    </tr>
                    <tr>
                      <td>Entry</td>
                      <td><input name="entry" type="text" class="ntext" id="entry" value="" size="20">                      </td>
                    </tr>
                    <tr>
                      <td>ETD</td>
                      <td>
                        
                        <input name="etd" type="text" class="ntext hasDatepicker" id="datepicker3" value="" size="10"><img class="ui-datepicker-trigger" src="/icons/calendar.png" alt="..." title="...">
                        </td>
                    </tr>
                    <tr>
                      <td>ETA</td>
                      <td>
                        <input name="eta" type="text" class="ntext hasDatepicker" id="datepicker4" value="" size="10"><img class="ui-datepicker-trigger" src="/icons/calendar.png" alt="..." title="...">
                        </td>
                    </tr>
                    
                    <input name="credit" type="hidden" id="credit" value="N">
                    
                    <input name="rs" type="hidden" id="rs" value="N">
                    
                   <tr>
                      <td>Incoterm</td>
                      <td>
                      <select name="incoterm" class="ntext" id="incoterm">
                        <option value=""></option>
                        <option value="F.O.B.">F.O.B.</option>
                        <option value="C.I.F.">C.I.F.</option>
                        <option value="C.F.">C.F.</option>
                        <option value="F.D.M.">F.D.M.</option>
                      </select>
                      </td>
                    </tr>
                  </tbody></table></td>
                <td valign="top">                    
                    <input name="paystatus" type="hidden" id="paystatus" value="No">
                    <span class="textbgorange">No payment has been received&nbsp;</span><br>
                    <br>
                    
                    <table width="100%" border="0" cellpadding="2" cellspacing="0" class="ntext">
                      <tbody><tr>
                        <td width="75">Exchange</td>
                        <td>USD
                          <input name="exchange_rate2" type="text" class="formnum" id="exchange_rate2" value="0.0000000" size="10">
                          </td>
                      </tr>
                      
                      <tr>
                        <td>Warehouse</td>
                        <td><input name="warehouse_id" type="text" class="ntext" id="warehouse_id" value="" size="15">
                          <a href="refresh.asp?id=47580&amp;type=whs&amp;item_id="><img src="../../icons/arrow-reload-7@2x.png" width="20" height="20" border="0" align="absmiddle"></a></td>
                      </tr>
                      <tr>
                        <td>Purchase</td>
                        <td><input name="purchasing_id" type="text" class="ntext" id="purchasing_id" value="0" size="11"></td>
                      </tr>
                      <tr>
                        <td>House</td>
                        <td><input name="house_number" type="text" class="ntext" id="house_number" value="0" size="10">
                          <input name="house_id" type="hidden" id="house_id" value="0"></td>
                      </tr>
                      <tr>
                        <td>File</td>
                        <td>
                          <input name="master_idv" type="text" id="master_idv" value="" size="6" class="ntext">
                          <input name="master_id" type="hidden" id="master_id" value="0">
                          <input name="master_number" type="text" class="ntext" id="master_number" value="0" size="14"></td>
                      </tr>
                      <tr>
                        <td>Pieces</td>
                        <td><input name="pieces" type="text" class="formnum" id="pieces" value="0" size="6">
                          Weight G.
                          <input name="gweight" type="text" class="formnum" id="gweight" value="0" size="6"></td>
                      </tr>
                      <tr>
                        <td>Volume</td>
                        <td><input name="volume" type="text" class="formnum" id="volume" value="0" size="6">
                          Weight C.
                          <input name="cweight" type="text" class="formnum" id="cweight" value="0" size="6"></td>
                      </tr>
                      <tr>
                        <td>Origin</td>
                        <td><input name="origin" type="text" class="ntext" id="origin" value="" size="6">
                          Dest
                          <input name="destination" type="text" class="ntext" id="destination" value="" size="6"></td>
                      </tr>
                    </tbody></table>
                    <table width="100%" border="0" cellspacing="0" cellpadding="2">
                      <tbody><tr>
                        <td width="75">PO</td>
                        <td><input name="po" type="text" class="ntext" id="po" value="" size="25"></td>
                      </tr>
                      <tr>
                        <td>Reference</td>
                        <td><input name="reference" type="text" class="ntext" id="reference" value="" size="25"></td>
                      </tr>
                      <tr>
                        <td><div align="right">

                          <input name="bank_info" type="checkbox" id="bank_info" value="Y">
                        </div></td>
                        <td>Bank Information</td>
                      </tr>
                      
					  <tr>
                        <td><div align="right">
                          <input name="bu" type="checkbox" id="bu" value="Y">
                        </div></td>
                        <td>Print including backup</td>
                      </tr>
                      <tr>
                        <td><div align="right">
                          <input name="pg" type="checkbox" id="pg" value="Y">
                        </div></td>
                        <td>Print grouped by concepts</td>
                      </tr>
                      <tr>
                        <td><div align="right">
                          <input name="flag2" type="checkbox" id="flag2" value="Y">
                        </div></td>
                        <td>Print&nbsp;Exchange</td>
                      </tr>
					  
                    </tbody></table>
                    <div align="right"></div></td></tr>
            </tbody></table>
            <br>
            
<table width="100%" border="0" cellpadding="5" cellspacing="0">
  <tbody><tr bgcolor="#f3f3f3">
                <td class="ntextrow" width="25" bgcolor="#f3f3f3"><div align="center">Units</div></td>
                <td class="ntextrow" bgcolor="#f3f3f3"><div align="center">G/L Account</div></td>
                <td class="ntextrow" width="10"><div align="center">CC</div></td>
                <td class="ntextrow"><div align="center">Description</div></td>
                
                <td class="ntextrow" width="35" bgcolor="#f3f3f3"><div align="center">Price</div></td>
                <td class="ntextrow" width="35" bgcolor="#f3f3f3"><div align="center">Amount</div></td>
                <td class="ntextrow" width="10"><div align="center">Tax %</div></td>
                <td class="ntextrow" width="20" bgcolor="#f3f3f3"><div align="right"><a href="#" onmousedown="javascript:document.form1.url.value='update.asp'" onmouseup="javascript:document.form1.submit()"><img src="../../icons/dustbin-7@2x.png" width="20" height="20" border="0" align="absmiddle" title="Delete"></a>
                    </div></td>
                </tr>

              <tr>
                <td class="ntextrow"><div align="center">
                    <input name="units1" type="text" class="formnum" id="units1" value="0" size="2">
			</div></td>
                <td class="ntextrow"><div align="center">
                  <select name="gl_account1" class="ntext" id="gl_account1" onfocus="checkColor1(this)" onblur="checkColor2(this)">
                    
                    <option value="4302">
					
					&nbsp;(4302)
</option>
                    
                    <option value="2000">
					Accounts Payabl$ 
					&nbsp;(2000)
</option>
                    
                    <option value="1200">
					Accounts Receiv
					&nbsp;(1200)
</option>
                    
                    <option value="4100">
					Air Freight
					&nbsp;(4100)
</option>
                    
                    <option value="4129">
					AMS Transmissio
					&nbsp;(4129)
</option>
                    
                    <option value="4102">
					AWB Fee
					&nbsp;(4102)
</option>
                    
                    <option value="4140">
					B/L Fee
					&nbsp;(4140)
</option>
                    
                    <option value="1001">
					Bank Account
					&nbsp;(1001)
</option>
                    
                    <option value="4131">
					Bond
					&nbsp;(4131)
</option>
                    
                    <option value="4123">
					Break Bulk
					&nbsp;(4123)
</option>
                    
                    <option value="4130">
					Brokerage
					&nbsp;(4130)
</option>
                    
                    <option value="4141">
					Bunker
					&nbsp;(4141)
</option>
                    
                    <option value="1000">
					Cash
					&nbsp;(1000)
</option>
                    
                    <option value="4110">
					Certificate of 
					&nbsp;(4110)
</option>
                    
                    <option value="4128">
					Chasis Charge
					&nbsp;(4128)
</option>
                    
                    <option value="4111">
					COD Fee
					&nbsp;(4111)
</option>
                    
                    <option value="4133">
					Custom Duty
					&nbsp;(4133)
</option>
                    
                    <option value="4125">
					DAD
					&nbsp;(4125)
</option>
                    
                    <option value="4126">
					Demurrage
					&nbsp;(4126)
</option>
                    
                    <option value="4101">
					Documentation
					&nbsp;(4101)
</option>
                    
                    <option value="4120">
					Drayage
					&nbsp;(4120)
</option>
                    
                    <option value="2005">
					Federal Income $ 
					&nbsp;(2005)
</option>
                    
                    <option value="2003">
					Federal Withhol$ 
					&nbsp;(2003)
</option>
                    
                    <option value="2002">
					FICA$ 
					&nbsp;(2002)
</option>
                    
                    <option value="4132">
					Fish &amp; Wildlife
					&nbsp;(4132)
</option>
                    
                    <option value="4134">
					Food &amp; Drug
					&nbsp;(4134)
</option>
                    
                    <option value="4137">
					Forwarding Fee
					&nbsp;(4137)
</option>
                    
                    <option value="4104">
					Fuel Surcharge
					&nbsp;(4104)
</option>
                    
                    <option value="2001">
					FUI$ 
					&nbsp;(2001)
</option>
                    
                    <option value="4136">
					Handling
					&nbsp;(4136)
</option>
                    
                    <option value="4139">
					HazMat Surcharg
					&nbsp;(4139)
</option>
                    
                    <option value="4142">
					Import Fee
					&nbsp;(4142)
</option>
                    
                    <option value="4143">
					Inspection
					&nbsp;(4143)
</option>
                    
                    <option value="4112">
					Insurance
					&nbsp;(4112)
</option>
                    
                    <option value="4124">
					Loading
					&nbsp;(4124)
</option>
                    
                    <option value="2006">
					Loan Payables$ 
					&nbsp;(2006)
</option>
                    
                    <option value="2004">
					Medicare$ 
					&nbsp;(2004)
</option>
                    
                    <option value="4300">
					Misc.
					&nbsp;(4300)
</option>
                    
                    <option value="4200">
					Ocean Freight
					&nbsp;(4200)
</option>
                    
                    <option value="4138">
					Packing or Repa
					&nbsp;(4138)
</option>
                    
                    <option value="4113">
					Pakya Personal 
					&nbsp;(4113)
</option>
                    
                    <option value="4106">
					Pickup &amp; Delive
					&nbsp;(4106)
</option>
                    
                    <option value="4135">
					Profit Share
					&nbsp;(4135)
</option>
                    
                    <option value="4105">
					Security
					&nbsp;(4105)
</option>
                    
                    <option value="4103">
					SED
					&nbsp;(4103)
</option>
                    
                    <option value="4119">
					Shipping &amp; Hand
					&nbsp;(4119)
</option>
                    
                    <option value="4107">
					Storage
					&nbsp;(4107)
</option>
                    
                    <option value="4109">
					Swift Pac Expre
					&nbsp;(4109)
</option>
                    
                    <option value="2007">
					Tax Income$ 
					&nbsp;(2007)
</option>
                    
                    <option value="4301">
					TEST
					&nbsp;(4301)
</option>
                    
                    <option value="4121">
					THC
					&nbsp;(4121)
</option>
                    
                    <option value="4108">
					Warehouse In &amp; 
					&nbsp;(4108)
</option>
                    
                    <option value="4127">
					Wharfage
					&nbsp;(4127)
</option>
                    
                  </select>
                  
                </div></td>
                <td class="ntextrow"><select name="cc_number1" class="ntext" id="cc_number1">
                  <option value="0"></option>
                  
                  <option value="1">0</option>
                  
                </select></td>
                <td class="ntextrow"><div align="center">
                  
                  <input name="vendor_id1" type="hidden" id="vendor_id1" value="0">
                  <input name="expense1" type="hidden" id="expense1" value="0">
                  
<input name="gp1" type="hidden" id="gp1" value="N">
                  <input name="type1" type="hidden" id="type1" value="">
                  <input name="payableline_id1" type="hidden" id="payableline_id1" value="0">
                  <input name="line_id1" type="hidden" id="line_id1" value="301459">
                  <input name="description1" type="text" class="ntext" id="description1" value="" size="25" onfocus="checkColor1(this)" onblur="checkColor2(this)">
                </div></td>
                
                <td class="ntextrow"><div align="center">
                    <input name="price_unit1" type="text" class="formnum" id="price_unit1" value="0" size="4">
</div></td>
                <td class="ntextrow"><div align="center">
                    <input name="subtotal1" type="text" class="formnum" id="subtotal1" value="0.00" size="12">
                </div></td>
                <td class="ntextrow"><div align="center">
                    <input name="tax1" type="checkbox" id="tax1" value="Y">
                </div></td>
                <td class="ntextrow"><div align="center">
                    <input name="action1" type="checkbox" id="action1" value="deleteline">
                </div></td>
                </tr>
              
              <tr>
                <td></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td><div align="right">
                </div></td>
                
                <td><div align="right">&nbsp; </div></td>
                <td><div align="center">Tax %</div></td>
                <td><input name="total_records" type="hidden" id="total_records" value="1"></td>
                <td><div align="center"><a href="#" onmousedown="javascript:document.form1.num_lines.value='1';document.form1.url.value='update.asp'" onmouseup="javascript:document.form1.submit()">
                  
                  <img src="../../icons/plus-circle-7@2x.png" width="20" title="Create additional lines" border="0" align="absmiddle">
                  
                </a></div></td>
              </tr>
          </tbody></table>
			<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bgdark">
              <tbody><tr>
                <td><div align="right">
                  <input name="num_lines" type="text" class="ntext" id="num_lines" value="0" size="2">
                  Create additional lines</div></td>
              </tr>
            </tbody></table>
			
			<br>
			<table width="100%" border="0" cellpadding="5" cellspacing="0">
              <tbody><tr>
                <td class="ntextrow" bgcolor="#f3f3f3">Remarks</td>
                <td class="ntextrow" bgcolor="#f3f3f3">Documents</td>
              </tr>
              <tr>
                <td class="ntextrow">
                  <div align="left">
                    <input name="remarks1" type="text" class="ntext" id="remarks1" value="" size="70" maxlength="100">
                    <input name="remarks2" type="text" class="ntext" id="remarks2" value="" size="70" maxlength="100">
                    <input name="remarks3" type="text" class="ntext" id="remarks3" value="" size="70" maxlength="100">
                      </div></td><td class="ntextrow"><textarea name="docs" cols="15" rows="3" class="ntext" id="docs"></textarea></td>
              </tr>
            </tbody></table>
			<br>
			<table width="100%" border="0" cellpadding="5" cellspacing="0">
			  <tbody><tr>
			    <td bgcolor="#f3f3f3" class="ntextrow">Remarks</td>
			    </tr>
			  
			  </tbody></table>
			
            <table width="100%" border="0" cellpadding="5" cellspacing="0" class="ntextrowred">
			  <tbody><tr>
			    <td>No records found</td>
			    </tr>
			  </tbody></table>
              
<br>
			<table width="100%" border="0" cellpadding="5" cellspacing="0">
              <tbody><tr>
                <td bgcolor="#f3f3f3" class="ntextrow">File</td>
                <td bgcolor="#f3f3f3" class="ntextrow">User</td>
                <td bgcolor="#f3f3f3" class="ntextrow">Date</td>
                <td width="16" bgcolor="#f3f3f3" class="ntextrow">&nbsp;</td>
                <td width="16" bgcolor="#f3f3f3" class="ntextrow">&nbsp;</td>
              </tr>
              
            </tbody></table>
			
            <table width="100%" border="0" cellpadding="5" cellspacing="0" class="ntextrowred">
              <tbody><tr>
                <td>No records found</td>
              </tr>
            </tbody></table>
            
            <br>
            <div align="right" class="ftext">
			Created by: &nbsp;SwiftPac Support&nbsp;8/3/2016&nbsp;2:32:04 PM&nbsp;&nbsp;-&nbsp;JE0
            </div></td>
          </tr>
        </tbody></table>
		</form>


</body>


</html>