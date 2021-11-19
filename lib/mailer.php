<?php
/*****************************************************
*	Mailgun Email delivering class
*	Delivers email through mailgun message api
*
*	Uage
*	-----
*	$email = new Mailer();
*	$email->subject = "Sending from email class";
*	$email->text = "This is my message";
*	$email->to = "lasantha@zurigroup.com, lkgamage@yahoo.com";
*	$email->send();
*	print_r($email->response);
******************************************************/

class Mailer {

	// Email sender address
	public $from = "system@zurigroup.com";	
	
	
	// Receipients
	public $to = "";
	
	
	// email subject
	public $subject = "";
	
	
	// email body
	public $text = "";
	public $html = "";
	
	
	//  result
	public $response = "";

	
	function __construct (){
		
	}
	
	
	public function send (){
		
		$emails = array();
		
		if(!empty($this->to)){
			$emails = preg_split('/[,; ]/', $this->to);
			
			foreach ($emails as $i => $e){
				
				if(empty($e)){
					continue;	
				}
				
				$emails[$i] = trim($e);	
			}
		}
		else{
			return false;	
		}
		
		
		
		// set email headers
		$headers = array();
		$headers[] = "Authorization: Basic ".base64_encode('api:'.Config::$mailgun_key);
		$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		$url = "https://api.mailgun.net/v3/mg.zurigroup2.com/messages";
		
		// set data
		$data = 'from='.$this->from;
		$data .= '&to='.implode(', ',$emails);	
			
		$data .= '&subject='.urlencode($this->subject);
		
		if(!empty($this->text)) {
			$data .= '&text='.urlencode($this->text);
		}
		elseif(!empty($this->html)) {
			$data .= '&html='.urlencode($this->html);
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER,0);
		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$response =  curl_exec($ch);
		//$info = curl_getinfo($ch);
		curl_close($ch);
		
		$this->response = json_decode($response, true);
		
		if(isset($this->response['id'])){
			return true;	
		}
		
		
		return false;
	}
	
	
}

?>