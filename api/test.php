<?php
$date = new DateTime ();
// $date->add ( new DateInterval ( 'P10D' ) );
// echo $date->format ( 'Y-d-m' ) . "\n";

echo $date->format ( 'N' ) . "day \n";

$days = "Tue";

$days = explode ( "/", $days );

// var_dump($days);

$days = array (
// 		'Mon' => 1,
		'Tue' => 2,
// 		'Wed' => 3,
// 		'Thur' => 4,
// 		'Fri' => 5,
// 		'Sat' => 6,
// 		'Sun' => 7 
);

$date = new DateTime ();
$currDay = $date->format ( 'N' );
// $currDay = 1;

$shipDay = 0;
$lastVal = $days[count($days) -1];
while (true) {
	
	if ($currDay < current($days)){
		$shipDay = current($days);
		break;
	}
	else if ($currDay == current ($days)){
		if (! next ( $days )) {
			reset($days);
			$shipDay = current($days);
			break;
			
		}
		else {
			$shipDay = current($days);
			break;
		}
	}
	else {
		
		$shipDay = current($days);
		if ($lastVal == $shipDay){
			reset($days);
			$shipDay = current($days);
			break;
		}
		next($days);
		
	}
	
}

echo $shipDay . "\n";
$dayDiffrence = $shipDay - $currDay;
echo $dayDiffrence;
if ($dayDiffrence > 0){
	
	$dayToAdd = $dayDiffrence;
	$date->add ( new DateInterval ( 'P'.$dayToAdd.'D' ) );
	
}
else {
	$dayToAdd = (7 - $currDay) + $shipDay;
	$date->add ( new DateInterval ( 'P'.$dayToAdd.'D' ) );
	
}

echo $date->format ( 'Y-d-m' ) . "\n";

$loggedin_username = "svd24007";
$cargotrackpw_forlogin = "svdAdmin123";


echo '<form action="http://swiftpac.cargotrack.net/default.asp" method="post" name="form1" target="_blank" id="form1">
<input type="hidden" value ="'  . $loggedin_username .  '" id="user" name="user">
<input type="hidden" value="' . $cargotrackpw_forlogin . '" id="password" name="password" maxlength="128">
<input class="cargo-track-button" type="submit" value="MANAGE YOUR CARGO" id ="ctBtnLogin" name="Submit">
<input type="hidden" name="action" value="login"></form>';
?>


<script type="text/javascript">


</script>


<div class="span3" id= 'submit_claim_anchor'>
	<div id="id = 'submit_claim'" class="sidebar">
		<div id="sidebar-claim-submission" class="widget widget_wp_sidebarlogin">
			<h3 href = "https://swiftpac.com/sign-up-for-your-swiftpac-account/" target = '_blank' class="widgettitle title">
				<span>Claim Submission<br>No Account? Click to Sign-Up
				</span>
			</h3>
			<form name="claimSubmissionForm" id="claimSubmissionForm"
				action="#">

				<p class="login-username">
					<label for="account-id">Account No:</label> <input type="text"
						name="account-id" id="account-id" class="input" value="" size="20">
				</p>
				<p class="claim-submit-fname">
					<label for="fname">First Name:</label> <input type="text"
						name="fname" id="fname" class="input" value="" size="20">
				</p>
				<p class="claim-submit-lname">
					<label for="lname">Last Name:</label> <input type="text"
						name="lname" id="lname" class="input" value="" size="20">
				</p>
				<p class="claim-submit-country">
					<label for="country">Country of package destination:</label>
					<select name="country" id="country" class="input" value="" size="20">
						<option value ='SVD'>St. Vincent</option>
						<option value ='BGI'>Barbados</option>
						<option value ='SLU'>St. Lucia</option>
					</select>
				</p>
				<p class="claim-submit-email">
					<label for="email">Email</label> <input type="text"
						name="pwd" id="user_pass" class="input" value="" size="20">
				</p>

				
				<p class="login-submit">
					<input type="submit" name="wp-submit" id="wp-submit"
						class="button-primary" value="Login »"> <input type="hidden"
						name="redirect_to"
						value="https://swiftpac.com/upgrade-swiftpac-account">
				</p>

			</form>
		</div>
	</div>
</div>
