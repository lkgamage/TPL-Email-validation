<?php

/** Email addresses to send  notifications */
$centers = array(
	'National Center' => 'global@ajc.org',
	'Atlanta' => 'atlanta@ajc.org',
	'Chicago' => 'chicago@ajc.org',
	'Cincinnati' => 'cincinnati@ajc.org',
	'Cleveland' => 'cleveland@ajc.org',
	'Dallas' => 'dallas@ajc.org',
	'JCRC | AJC Detroit' => 'detroit@ajc.org',
	'Houston' => 'houston@ajc.org',
	'JCRB | AJC Kansas City' => 'kansascity@ajc.org',
	'Long Island' => 'longisland@ajc.org',
	'Los Angeles' => 'losangeles@ajc.org',
	'Miami/Broward County' => 'reinhardn@ajc.org',
	'New England' => 'newengland@ajc.org',
	'New Jersey' => 'newjersey@ajc.org',
	'New York' => 'newyork@ajc.org',
	'Palm Beach County' => 'palmbeach@ajc.org',
	'Philadelphia/Southern New Jersey' => 'philadelphia@ajc.org',
	'St. Louis' => 'stlouis@ajc.org',
	'San Francisco' => 'sanfrancisco@ajc.org',
	'Seattle' => 'seattle@ajc.org',
	'Washington, D.C. ' => 'washington@ajc.org',
	'Westchester/Fairfield' => 'westchester@ajc.org',
	'West Coast Florida' => 'sarasota@ajc.org',
	'ACCESS' => 'access@ajc.org',
	'Young Leadership' => 'youngleadership@ajc.org',
	'Campus Affairs' => 'campus@ajc.org',
	'Africa Institute' => 'africa@ajc.org',
	'Berlin Ramer Institute' => 'berlin@ajc.org',
	'Belfer Institute for Latino and Latin America Affairs' => 'billa@ajc.org',
	'Project Interchange' => 'baileyc@ajc.org',
	'Interreligious' => 'iir@ajc.org'
);

/**
* Validate email address
*/
function validateEmail ($email) {
	
	$headers = array();
	$headers[] = 'Authorization: ApiKey: '.Config::$briteverify_key;
	$headers[] = 'Content-Type: application/json';
	
	$url = 'https://bpi.briteverify.com/api/v1/fullverify';
	$data = '{"email": "'.$email.'"}';
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
	$response = curl_exec($ch);
	
	return $response;
	
}

function buildReport ($data){

	$txt = '<table class="email_list"  width="100%" border="0" cellspacing="0" cellpadding="5" >
				  <tr class="email_header">
					<td>Cons. ID</td>
					<td>Name</td>
					<td>Email</td>
					<td>Reason</td>
				  </tr>';
	
	foreach ($data as $item){
		
		if(!isset($item['status'])) {
			continue;	
		}
		
		$txt .= '<tr>
					<td>'.$item['Constituent ID'].'</td>
					<td>'.$item['First Name'].' '.$item['Last Name'].'</td>
					<td>'.$item['Email'].'</td>
					<td>'.$item['status']['error'].'</td>
				  </tr>';
		
	}
	
	
	$txt .= '</table>';
	
	return $txt;
}


function buildEmail ($data){
	
	ob_start();
	
	$content = buildReport($data);
	
	include('email_report.php');
	
	return ob_get_clean();
	
}

?>