<?php 
require_once '/home/vincyavi/public_html/swiftpac1/simplehtmldom_1_5/simple_html_dom.php';

function createInvoice($date,$client_id,$client_name,$client_address1,$client_address2,$client_address3,$client_phone,
		$created_by,$updated_by,$currency,$branch,$po, array $invoiceInfo){

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

	
	//Add invoice to cargotrack
	$action = urldecode("new");
	$invoice_postinfo = "date=".$date
	."&client_id=".$client_id
	."&client_name=".$client_name
	."&client_address1=".$client_address1
	."&client_address2=".$client_address2
	."&client_address3=".$client_address3
	."&client_phone=".$client_phone
	."&created_by=".$created_by
	."&updated_by=".$updated_by
	."&currency=".$currency
	."&branch=".$branch
	."&po=".$po
	."&action=".$action;
	
	
	
	curl_setopt($ch, CURLOPT_URL, "http://swiftpac.cargotrack.net/appl2.0/invoices/add_invoice.asp");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $invoice_postinfo);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	$resp = curl_exec($ch);
	
	//get invoice number and line id
	$DOM = new DOMDocument();
	$DOM->loadHTML($resp);
	$htmlStr = $DOM->saveHTML();
	$html = str_get_html($htmlStr);
		
	$invoiceNumber = $html->find('*[id=invoice_number]')[0]->value;
	
	$lineId1 = $html->find('*[id=line_id1]')[0]->value;

	//update invoice with invoice details
	$invoice_post_fields = "&insurance=".$invoiceInfo['insurance']
	."&cr=".$invoiceInfo['cr']
	."&url=".$invoiceInfo['url']
	."&id=".$invoiceNumber
	."&close=".$invoiceInfo['close']
	."&branch=".$invoiceInfo['branch']
	."&currency=".$invoiceInfo['currency']
	
	."&units1=".$invoiceInfo['units1']
	."&gl_account1=".$invoiceInfo['gl_account1']
	."&cc_number1=".$invoiceInfo['cc_number1']
	."&vendor_id1=".$invoiceInfo['vendor_id1']
	."&expense1=".$invoiceInfo['expense1']
	."&gp1=".$invoiceInfo['gp1']
	."&type1=".$invoiceInfo['type1']
	."&payableline_id1=".$invoiceInfo['payableline_id1']
	."&line_id1=".$lineId1
	."&description1=".$invoiceInfo['description1']
	."&price_unit1=".$invoiceInfo['price_unit1']
	."&subtotal1=".$invoiceInfo['subtotal1']

	."&invoice_number=".$invoiceNumber
	
	."&date=".$invoiceInfo['date']
	."&client_id=".$invoiceInfo['client_id']
	."&client_name=".$invoiceInfo['client_name']
	."&client_address1=".$invoiceInfo['client_address1']
	."&client_address2=".$invoiceInfo['client_address2']
	."&client_address3=".$invoiceInfo['client_address3']
	."&client_phone=".$invoiceInfo['client_phone']
	
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
	
	$resp = curl_exec($ch);

	echo $resp;

}

if (isset($_POST["submit_invoice"])){
	
// 	echo "data sent";
	
	extract($_POST);
	
	$updateInvoiceInfo = array (
			
	'insurance'=>'',
	'cr'=>'N',
	'url'=>'update.asp',
	'id'=>'47580',
	'close'=>'N',
	'action'=>'update',
	'branch'=> $branch,
// 	'invoice_number'=>'47580',
	'date'=>$date,
	'client_id'=>$client_id,
	'client_name'=>$client_name,
	'client_address1'=>'',
	'client_address2'=>'',
	'client_address3'=>'',
	'client_phone'=>'',
	'currency'=>$currency,
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
	'warehouse_id'=>$warehouse_id,
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
	'po'=>$po,
	'reference'=>$reference,
	'units1'=>$units1,
	'gl_account1'=>$gl_account1,
	'cc_number1'=>$cc_number,
	'vendor_id1'=>'0',
	'expense1'=>'0',
	'gp1'=>'N',
	'type1'=>'',
	'payableline_id1'=>'0',
	'line_id1'=>'',
	'description1'=>$description1,
	'price_unit1'=>$price_unit1,
	'subtotal1'=>$subtotal1,
	'total_records'=>'1',
	'num_lines'=>'0',
	'remarks1'=>'',
	'remarks2'=>'',
	'remarks3'=>'',
	'docs'=>''
	);
	
	
// echo $date;	
createInvoice($date, $client_id, $client_name, $client_address1, $client_address2, $client_address3,
		$client_phone, $created_by, $updated_by, $currency, $branch, $po, $updateInvoiceInfo);

header('Location: confirmation.php');

}


?>

<html><!--  -->
<head>

</head>
<body>
<p> Create Invoice on CargoTrack Test Form: 
Please note that validation is not done on this form. It is assumed that all data entered is valid </p>
<form name="form1" action= "test_cargotrack_create_invoice.php" method = "post">
		<table width="100%" border="0" cellpadding="5" cellspacing="0" >
          <tbody><tr>
            <td valign="top">
              <table width="100%" border="0" cellpadding="5" cellspacing="0">
                
                <tbody><tr>
                  <td width="100">Date</td>
                  <td>
                  	<input name="date" type="date" class="ntext hasDatepicker" id="datepicker" value="" size="10" >
                    </td>
                  <td><div align="right"></div></td>
                </tr>
                <tr>
                  <td>Account</td>
                  <td><span class="smallform"><a href="#"></span>
                    <input name="client_id" type="text" class="ntext" id="client_id"   value="" size="5" autocomplete="off" style="background-color: rgb(255, 255, 255);">
                    <input name="client_name" type="text" class="ntext" id="autocomplete1" value="" size="50">
			  
                    <input name="client_address1" type="hidden" id="client_address1">
                    <input name="client_address2" type="hidden" id="client_address2">
                    <input name="client_address3" type="hidden" id="client_address3">
                    <input name="client_phone" type="hidden" id="client_phone">
                    <input name="created_by" type="hidden" id="created_by" value="SwiftPac Support">
                    <input name="updated_by" type="hidden" id="updated_by" value="SwiftPac Support"></td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td>Currency</td>
                  <td colspan="2">

                  <select name="currency" id="currency">
                    
                    <option value="BBD">BBD&nbsp;-&nbsp;
                      Barbados Dollar
                      </option>
                    
                    <option value="TTD">TTD&nbsp;-&nbsp;
                      Trinidad &amp; Tobago Dollar
                      </option>
                    
                    <option value="USD">USD&nbsp;-&nbsp;
                      US Dollar
                      </option>
                    
                    <option value="XCD" selected="">XCD&nbsp;-&nbsp;
                      Eestern Caribbean Dollar
                      </option>
                    
                  </select>
                  </td>
                </tr>
                <tr>
                  <td>Branch</td>
                  <td colspan="2">

                  <select name="branch" class="ntext" id="branch" >
                    
                    <option value="ANU">ANU&nbsp;-&nbsp;Swiftpac ANU</option>
                      
                    <option value="AXA">AXA&nbsp;-&nbsp;Swiftpac AXA</option>
                      
                    <option value="BGI">BGI&nbsp;-&nbsp;SwiftPac BGI</option>
                      
                    <option value="DOM">DOM&nbsp;-&nbsp;SwiftPac DOM</option>
                      
                    <option value="EIS">EIS&nbsp;-&nbsp;SwiftPac BVI</option>
                      
                    <option value="GEO">GEO&nbsp;-&nbsp;SwiftPac GUY</option>
                      
                    <option value="GN2">GN2&nbsp;-&nbsp;Swiftpac Grenada 2</option>
                      
                    <option value="GND">GND&nbsp;-&nbsp;Swiftpac GND</option>
                      
                    <option value="ITS">ITS&nbsp;-&nbsp;SwiftPac ITSD</option>
                      
                    <option value="MCO">MCO&nbsp;-&nbsp;Swiftpac Orlando</option>
                      
                    <option value="MIA">MIA&nbsp;-&nbsp;Swiftpac MIA</option>
                      
                    <option value="NYC">NYC&nbsp;-&nbsp;Swiftpac NY</option>
                      
                    <option value="POS">POS&nbsp;-&nbsp;Swiftpac TNT</option>
                      
                    <option value="SKB">SKB&nbsp;-&nbsp;Swiftpac SKB</option>
                      
                    <option value="SLU">SLU&nbsp;-&nbsp;SwiftPac SLU</option>
                      
                    <option value="SVD" selected="">SVD&nbsp;-&nbsp;SwiftPac SVD</option>
                      
                    <option value="SVG">SVG&nbsp;-&nbsp;SwiftPac City Office</option>
                      
                    <option value="USA">USA&nbsp;-&nbsp;Swiftpac USA</option>
                      
                  </select>
                  </td>
                </tr>
                <tr>
                   <td>Warehouse</td>
                   <td><input name="warehouse_id" type="text" class="ntext" id="warehouse_id" value="" size="15"></td>
                </tr>
                
                <tr>
                    <td>Reference</td>
                     <td><input name="reference" type="text"id="reference" value="" size="25" ></td>
                </tr>

				<tr bgcolor="#f3f3f3">
					<td class="ntextrow" width="25" bgcolor="#f3f3f3"><div
							align="center">Units</div></td>
					<td class="ntextrow" bgcolor="#f3f3f3"><div align="center">G/L
							Account</div></td>
					<td class="ntextrow" width="10"><div align="center">CC</div></td>
					<td class="ntextrow"><div align="center">Description</div></td>

					<td class="ntextrow" width="35" bgcolor="#f3f3f3"><div
							align="center">Price</div></td>
					<td class="ntextrow" width="35" bgcolor="#f3f3f3"><div
							align="center">Amount</div></td>
				</tr>

				<tr>
					<td class="ntextrow"><div align="center">
							<input name="units1" type="text" class="formnum" id="units1"
								value="0" size="2">
						</div></td>
					<td class="ntextrow"><div align="center">
							<select name="gl_account1" class="ntext" id="gl_account1"
								onfocus="checkColor1(this)" onblur="checkColor2(this)">

								<option value="4302">&nbsp;(4302)</option>

								<option value="2000">Accounts Payabl$ &nbsp;(2000)</option>

								<option value="1200">Accounts Receiv &nbsp;(1200)</option>

								<option value="4100">Air Freight &nbsp;(4100)</option>

								<option value="4129">AMS Transmissio &nbsp;(4129)</option>

								<option value="4102">AWB Fee &nbsp;(4102)</option>

								<option value="4140">B/L Fee &nbsp;(4140)</option>

								<option value="1001">Bank Account &nbsp;(1001)</option>

								<option value="4131">Bond &nbsp;(4131)</option>

								<option value="4123">Break Bulk &nbsp;(4123)</option>

								<option value="4130">Brokerage &nbsp;(4130)</option>

								<option value="4141">Bunker &nbsp;(4141)</option>

								<option value="1000">Cash &nbsp;(1000)</option>

								<option value="4110">Certificate of &nbsp;(4110)</option>

								<option value="4128">Chasis Charge &nbsp;(4128)</option>

								<option value="4111">COD Fee &nbsp;(4111)</option>

								<option value="4133">Custom Duty &nbsp;(4133)</option>

								<option value="4125">DAD &nbsp;(4125)</option>

								<option value="4126">Demurrage &nbsp;(4126)</option>

								<option value="4101">Documentation &nbsp;(4101)</option>

								<option value="4120">Drayage &nbsp;(4120)</option>

								<option value="2005">Federal Income $ &nbsp;(2005)</option>

								<option value="2003">Federal Withhol$ &nbsp;(2003)</option>

								<option value="2002">FICA$ &nbsp;(2002)</option>

								<option value="4132">Fish &amp; Wildlife &nbsp;(4132)</option>

								<option value="4134">Food &amp; Drug &nbsp;(4134)</option>

								<option value="4137">Forwarding Fee &nbsp;(4137)</option>

								<option value="4104">Fuel Surcharge &nbsp;(4104)</option>

								<option value="2001">FUI$ &nbsp;(2001)</option>

								<option value="4136">Handling &nbsp;(4136)</option>

								<option value="4139">HazMat Surcharg &nbsp;(4139)</option>

								<option value="4142">Import Fee &nbsp;(4142)</option>

								<option value="4143">Inspection &nbsp;(4143)</option>

								<option value="4112">Insurance &nbsp;(4112)</option>

								<option value="4124">Loading &nbsp;(4124)</option>

								<option value="2006">Loan Payables$ &nbsp;(2006)</option>

								<option value="2004">Medicare$ &nbsp;(2004)</option>

								<option value="4300">Misc. &nbsp;(4300)</option>

								<option value="4200">Ocean Freight &nbsp;(4200)</option>

								<option value="4138">Packing or Repa &nbsp;(4138)</option>

								<option value="4113">Pakya Personal &nbsp;(4113)</option>

								<option value="4106">Pickup &amp; Delive &nbsp;(4106)</option>

								<option value="4135">Profit Share &nbsp;(4135)</option>

								<option value="4105">Security &nbsp;(4105)</option>

								<option value="4103">SED &nbsp;(4103)</option>

								<option value="4119">Shipping &amp; Hand &nbsp;(4119)</option>

								<option value="4107">Storage &nbsp;(4107)</option>

								<option value="4109">Swift Pac Expre &nbsp;(4109)</option>

								<option value="2007">Tax Income$ &nbsp;(2007)</option>

								<option value="4301">TEST &nbsp;(4301)</option>

								<option value="4121">THC &nbsp;(4121)</option>

								<option value="4108">Warehouse In &amp; &nbsp;(4108)</option>

								<option value="4127">Wharfage &nbsp;(4127)</option>

							</select>

						</div></td>
					<td class="ntextrow"><select name="cc_number1" class="ntext"
						id="cc_number1">
							<option value="0"></option>

							<option value="1">0</option>

					</select></td>
					<td class="ntextrow"><div align="center">

							<input name="vendor_id1" type="hidden" id="vendor_id1"
								value="0"> <input name="expense1" type="hidden"
								id="expense1" value="0"> <input name="gp1" type="hidden"
								id="gp1" value="N"> <input name="type1" type="hidden"
								id="type1" value=""> <input name="payableline_id1"
								type="hidden" id="payableline_id1" value="0"> <input
								name="line_id1" type="hidden" id="line_id1" value="301459">
							<input name="description1" type="text" class="ntext"
								id="description1" value="" size="25">
						</div></td>

					<td class="ntextrow"><div align="center">
							<input name="price_unit1" type="text" class="formnum"
								id="price_unit1" value="0" size="4">
						</div></td>
					<td class="ntextrow"><div align="center">
							<input name="subtotal1" type="text" class="formnum"
								id="subtotal1" value="0.00" size="12">
						</div></td>
					
				</tr>

				<tr>
					
					<td><input name="total_records" type="hidden"
						id="total_records" value="1"></td>
				</tr>



				<tr>
                  
                  <td><input name = "submit_invoice" id = "submit_invoice" type ="submit"/></td>
                
                </tr>
              </tbody></table>
            </td>
          </tr>
        </tbody></table>
        
        </form>


</body>


</html>