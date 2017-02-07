<?php
ini_set('display_errors', 'On');

$authRes = false;
$suppRes = false;
$postRes = false;
$txRes = false;
$email = '';
$supporterDeets = array();
$transactions = array();

$errors = [];

$ch = curl_init();

/*
 * BE SURE TO UPDATE THE ens-auth-token TO A VALID ONE
 */

// authenticate
$authUrl = 'https://www.e-activist.com/ens/service/authenticate';
curl_setopt($ch, CURLOPT_URL, $authUrl);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
'Content-Type: application/json; charset=UTF-8',
'ens-auth-token: b62c9e45-8fc1-4a9a-b83a-163236824e15'
));
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'b62c9e45-8fc1-4a9a-b83a-163236824e15');  // UPDATE
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

$res = curl_exec($ch);
$authRes = $res;
$json = json_decode($res, true);

if(array_key_exists('message', $json)){
	// didnt authenticate
	$errors['auth'] = $json['message'];
	$token = false;
}else{
	$token = $json['ens-auth-token'];
	if($_POST){
		$postVals = $_POST;
		unset($postVals['supporterId']); // can't pass this in
		$postjson = json_encode($postVals);
		// POST swaps spaces for underscores. Undo this.
		$postjson = str_replace('_', ' ', $postjson);

		// POST	
		$postUrl = 'https://www.e-activist.com/ens/service/supporter';
		curl_setopt($ch, CURLOPT_URL, $postUrl);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json; charset=UTF-8',
		'ens-auth-token: ' . $token
		));
		curl_setopt($ch, CURLOPT_POST, count($_POST));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postjson);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$res = curl_exec($ch);
		$postRes = $res;
		$json = json_decode($res, true);
		
		if(array_key_exists('message', $json)){
			// didnt POST
			$errors['post'] = $json['message'];
		}
		
		// add supporterId back to start of array
		$supporterDeets = array('supporterId' => $_POST['supporterId']);
		$supporterDeets = array_merge($supporterDeets, json_decode($postjson, true)); // decoding because we removed underscores
		
		$transactions = array();
		$email = '';

	}else if(array_key_exists('email', $_GET)){
		$email = $_GET['email'];
		$values = [];
		$suppUrl = 'https://www.e-activist.com/ens/service/supporter?email=' . $email;

		// get supporter data
		curl_setopt($ch, CURLOPT_HTTPGET, 1);
		curl_setopt($ch, CURLOPT_URL, $suppUrl);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'ens-auth-token: ' . $token
		));

		$res = curl_exec($ch);
		$suppRes = $res;
		
		$supporterDeets = json_decode($res, true);

		// get transactions	
		$txUrl = 'https://www.e-activist.com/ens/service/supporter/' . $supporterDeets['supporterId'] . '/transactions';

		curl_setopt($ch, CURLOPT_HTTPGET, 1);
		curl_setopt($ch, CURLOPT_URL, $txUrl);

		$res = curl_exec($ch);
		$txRes = $res;
		$transactions = json_decode($res, true);
		
		if(array_key_exists('message', $transactions)){
			// didnt get tx
			$errors['supp'] = $json['message'];
		}

		curl_close($ch);
	}

}

?>
<html>
<head>
    <link href='http://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet' type='text/css'>
    <style>
    html {
        background-color: rgba(0, 168, 199, 0.5);
    }

    body {
        max-width: 800px;
        margin: 0 auto;
        background-color: white;
        padding: 24px;
        font-family: Montserrat;
    }

    .body > div {
        margin-bottom: 24px;
        border-bottom: 1px solid orange;
        padding-bottom: 24px;
    }

    .header__title {
        color: #EB9131;
        font-size: 1.6em;
        display: inline-block;
        padding-top: 24px;
        float: right;
    }

    .header__logo {
        width: 210px;
        height: 100px;
        display: inline-block;
        background: url('http://www.engagingnetworks.net/conference-na/includes/cc15-logosite.jpg') no-repeat center center;
        background-size: 270px 103px;
    }

    .body__supporter__field__label,
    .body__supporter__field__value {
        display: inline-block;
        padding: 6px;
        vertical-align: top;
    }

    .body__supporter__field__label {
        width: 300px;
        overflow: hidden;
    }

    .body__transactions__transaction > div {
        display: inline-block;
        padding: 12px;
        vertical-align: middle;
    }

    .body__transactions__transaction__type {
        width: 24px;
    }

    .body__transactions__transaction__date {
        float: right;
    }

    .body__transactions__transaction {
        border: 1px solid rgba(0, 168, 199, 0.25);
        margin-bottom: 6px;
        border-radius: 12px;
    }

    .type__dc,
    .type__et,
    .type__ef {
        background: url('https://politicalnetworks.com/ea-demo/frontend/build/1.5.1/images/icons/advocacy-32.png') no-repeat center center;
        background-size: 24px 24px;
    }

    .type__EMAIL {
        background: url('https://politicalnetworks.com/ea-demo/frontend/build/1.5.1/images/icons/email/email-single-22x22.gif') no-repeat center center;
    }

    .type__nd {
        background: url('https://politicalnetworks.com/ea-demo/frontend/build/1.5.1/images/icons/fundraising-32.png') no-repeat center center;
        background-size: 24px 24px;
    }
    
    .footer textarea {
		height: 150px;
		width: 100%;
	}
	
	.body__errors {
		color:red;
	}

    </style>
</head>
<body>
	<div class="header">
		<div class="header__logo"></div>
		<div class="header__title">Engaging Networks Services</div>
	</div>
	<div class="body" >
		<div class="body__search">
			<p>Search for an email address supporter</p>
			<form action="ens-demo.php">
				<input name="email" id="body__search--input" type="text" value="<?php echo $email; ?>" />
				<input type="submit" value="Submit" />
			</form>
		</div>
		<div class="body__supporter" >

<?php if(count($errors) > 0){ ?>
	<div class="body__errors" >
		<h3>Errors:</h3>
	<?php foreach($errors as $error){ ?>
		<div class="body__errors__error" ><?php echo $error; ?></div>
	<?php } ?>
	</div>
<?php } ?>
	
<?php if($supporterDeets && count($supporterDeets) > 0){ ?>
<form action="ens-demo.php" method="POST">
<?php foreach($supporterDeets as $deet => $value){ 
		if(preg_match('/_/', $deet) == 0){ // remove fields with _ in them for purposes of this demo ?>
				<div class="body__supporter__field" >
					<div class="body__supporter__field__label" ><?php echo $deet; ?></div>
					<div class="body__supporter__field__value" ><input name="<?php echo $deet; ?>" value="<?php echo $value; ?>" /></div>
				</div>
	<?php }
	} ?>
	<input type="submit" value="Update supporter" />
</form>
<?php }else if($supporterDeets === null){
	echo 'Supporter not found';
} ?>
		</div>
		<div class="body__transactions" >
<?php
if($transactions){
foreach($transactions as $tx){ ?>
			<div class="body__transactions__transaction" >
				<div class="body__transactions__transaction__type type__<?php echo $tx['type']; ?>" ></div>
				<div class="body__transactions__transaction__name" ><?php echo $tx['name']; ?></div>
				<div class="body__transactions__transaction__date" >
<?php
if(array_key_exists('createdOn', $tx)){
	echo $tx['createdOn'];
}else if(array_key_exists('createdDate', $tx)){
	echo date('d/m/Y', $tx['createdDate'] / 1000);
}else{
	echo '-';
}
?>
				</div>
			</div>
<?php
}
}
?>
		</div>
	</div>
	<div class="footer" >
		<h2>Raw</h2>
		<h3>Authorisation</h3>
		<h5><?php echo $authUrl; ?></h5>
		<textarea><?php print_r($authRes); ?></textarea>
<?php if($postRes){ ?>
		<h3>Update supporter</h3>
		<h5><?php echo $postUrl; ?></h5>
		<h6>POST</h6>
		<textarea><?php print_r($postjson); ?></textarea>
		<h6>Response</h6>
		<textarea><?php print_r($postRes); ?></textarea>
<?php } ?>
<?php if($suppRes){ ?>
		<h3>Get supporter data</h3>
		<h5><?php echo $suppUrl; ?></h5>
		<textarea><?php print_r($suppRes); ?></textarea>
<?php } ?>
<?php if($transactions){ ?>
		<h3>Transactions</h3>
		<h5><?php echo $txUrl; ?></h5>
		<textarea><?php print_r($txRes); ?></textarea>
<?php } ?>
	</div>
</body>
</html>
