<pre><?php
date_default_timezone_set('America/New_York');

include ('lib/config.php');
include ('lib/functions.php');
include ('lib/luminate.php');
include ('lib/ConvioOpenAPI.php');
include ('lib/mailer.php');


$report_name = 'New Constituents For Email Validation';

$mailer = new Mailer();


$luminate = new Luminate(Config::$luminate_username, Config::$luminate_password);


if(!$luminate->login()){
	
	$error = "Luminate login failed";
	
	ob_start();
	include('lib/email_error.php');
	$email = ob_get_clean();
	
	echo $email;	
		
	$mailer->to = 'lasantha@zurigroup.com,molly@zurigroup.com,bryan.vance@tpl.org';
	$mailer->subject = 'TFPL Email Validation Process';
	$mailer->html = $email;
	//$mailer->send();
	
	exit("Luminate admin login failed");
}


$res = $luminate->runReport($report_name);

if($res === false){
	
	$error = $luminate->error;
	
	ob_start();
	include('lib/email_error.php');
	$email = ob_get_clean();
	
	echo $email;	
		
	$mailer->to = 'lasantha@zurigroup.com,molly@zurigroup.com,bryan.vance@tpl.org';
	$mailer->subject = 'TFPL Email Validation Process';
	$mailer->html = $email;
	//$mailer->send();
	
	exit('Report download error');
}


// if there is an aeeror downlaoding report, will break before comming to here

//   further processing/validating emails
// load csv file
// order of the headers
$res = 43384;
$file = fopen("report_".$res.".csv","r");

$emails = array();

// remove headers
$headers = fgetcsv($file);


while(! feof($file)){

	$line = fgetcsv($file);
	$row = array();
	
	foreach ($headers as $i => $h){
		
		$row[$h] = $line[$i];
		
	}
	
	if(empty($row['Email'])){
		continue;	
	}
	
	if(!isset($emails[$row['Email']])){
		$emails[$row['Email']] = array();
	}
	
	$emails[$row['Email']][] = $row;
}

// validating emails

$errors = array();
$summery = "";
$log = "Email,Status,Disposable,Role Address,Error Code,Error\n";

$i = 0;

foreach ($emails as $email => $data){
	
	
	$res = validateEmail($email);

	$res = json_decode($res, true);
	
	$log .= '"'.str_replace('"', '\"', $email).'",'.$res['email']['status'].','.($res['email']['disposable'] ? 'true' : 'false').','.($res['email']['role_address'] ? 'true' : 'false').','.(isset($res['email']['error_code']) ? $res['email']['error_code'] : '').','.(isset($res['email']['error']) ? $res['email']['error'] : '').','."\n";
	
	if($res['email']['status'] == 'invalid'){		
		$errors[$email] = $res;		
	}
	
	
	
	$i++;	
	
}

file_put_contents('validaton_log.csv', $log);



// prepare reports

if(!empty($errors)){
	
	
	$convioAPI = new ConvioOpenAPI;
	$convioAPI->host       = 'secure3.convio.net'; //secure2.convio.net';
	$convioAPI->short_name = '';
	$convioAPI->api_key    = Config::$luminate_api_key;
	$convioAPI->login_name     = Config::$luminate_api_username;
	$convioAPI->login_password = Config::$luminate_api_password;
	$convioAPI->response_format = 'json';
	
	
	$reports = array();
	$remians = array();
	
	
	foreach ($errors as $email => $error){
		
		foreach ($emails[$email] as $item){
		
			
			$item['status'] = $error['email'];
	
			$reports[] = $item;
		
			
			// disable email
			$params = array('cons_id' => $item['Constituent ID'], 'email.accepts_email' => 'No' );
			$json = $convioAPI->call('SRConsAPI_update', $params);
		}
		
	}// each email
	
print_r($reports);
	
	if(!empty($reports)){
	
		// generating emails
		$email = buildEmail($reports );
		echo $email;
		// sending email
		$mailer->to = 'lasantha@zurigroup.com,molly@zurigroup.com,bryan.vance@tpl.org';
		$mailer->subject = "TFPL Email Validation Report";
		$mailer->html = $email;
		$mailer->send();
	}
	
	echo "Process complted. Email sent";
	
}

// send notificaton email to acknowladge process has been run

$num_emails = count($errors);

ob_start();
$content = buildReport($data);
include('lib/email_ack.php');
$email = ob_get_clean();

echo $email;

	
$mailer->to = 'lasantha@zurigroup.com,molly@zurigroup.com,bryan.vance@tpl.org';
$mailer->subject = 'TFPL Email Validation Process';
$mailer->html = $email;
$mailer->send();			



?></pre>