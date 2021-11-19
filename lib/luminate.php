<?php
set_time_limit(0);
/********************************************************
*			Luminate Report helper class				*
*********************************************************/
include_once('webrequest.php');

class Luminate {
	
	private $username = "";
	private $password = "";
	
	private $request;
	
	public $error = '';
	
	private $debug = true;
	
	public $filename = '';
	
	private $baseurl = 'https://secure3.convio.net/tpl';
	
	
	
	function Luminate ($username, $password ){
		
		$this->username = $username;
		$this->password = $password;
		
		$this->request = new WebRequest('luminate');
		
	}
	
	
	public function login (){
		
		$this->request->clear();
		
		$page = $this->request->Get($this->baseurl.'/admin/AdminLogin');

		echo $page;

		$parts = explode('id="USERNAME_',$page);
		$parts = explode('"', $parts[1]);

	//	$parts = array('1490046946282');

		$username = "USERNAME_".$parts[0];
		

		$password = "Password_".$parts[0];
		
		$post =  $username."=".$this->username."&".$password."=".$this->password."&NEXTURL=";
		//$post =  $username."=".$this->username."&".$password."=".$this->password."&NEXTURL=&ADDITIONAL_AUTH_".$parts[0]."=459473";
		//echo $post;
		
		$page = $this->request->Post($this->baseurl."/admin/AdminLogin?", $post);
		
		echo $page;

		
		if(strpos($page, 'Administrator Home Page') !== false){
			return true;	
		}
		
		return false;
				
	}
	
	
	public function runReport( $report_name){
		
		file_put_contents('report.csv', '');
		
		// get report result page
		$page = $this->request->get($this->baseurl.'/admin/ReportWriterManager?rptwrt.manager=result_list');
		
		$page = $this->request->get($this->baseurl.'/admin/ReportWriterManager?rptwrt.manager=result_list&report_schedule_id=0&lcmd=filter&tree.node=1200&lcmd_cf=');
						
		$result_id = $this->getLatestResultID($page, $report_name);
		
		// test report id
		//$result_id = 1181;
		
		if(empty($result_id)){
			$this->error = "Results not found";
			return false;
		}
		
		// downloading report

		$report = $this->request->get($this->baseurl.'/admin/ReportWriterManager?rptwrt.manager=download_result_csv&mfc_pref=T&action=download_csv&report_result_id='.$result_id.'&mfc_popup=t');
		
		
		if(strpos($report, '<head>') !== false){
			$this->error = 'No data in the report';
			return false;
		}

		// save report in a csv formt
		$this->filename = 'report_'.$result_id.'.csv'; 
		file_put_contents($this->filename, $report);
		echo "Report downloaded";
		
		return $result_id;
		
	}
	
	
	public function getLatestResultID ($html, $report_name){
	
		$dom = new DomDocument();
		$dom->loadHTML($html);
		
		$trs = $dom->getElementsByTagName('tr');
		
		if($trs->length > 0){
		
			foreach ($trs as $tr){
			
				$tds = $tr->childNodes;
				
				if($tds->length > 0){
					
					$cv = $tds[0]->nodeValue;
					$cv = trim($cv);
					
					if(strpos($cv, $report_name) !== false){
						// report result found, find the download url
						//echo $tds[2]->nodeValue;
						$links = $tds[2]->childNodes[1]->childNodes;
						//print_r($links);
						if($links->length > 0){
							
							$link = $links[1]->attributes;
							
							foreach ($link as $attr){
								
								if($attr->name == 'href'){
									$url = $attr->value;
									
									// extract id
									$up = explode('?', $url);
									$up = explode('&', $up[1]);
									
									foreach ($up as $part){
										
										$kv = explode('=', $part);
										
										if($kv[0] == 'report_result_id'){
											return $kv[1];	
										}
									}
								}
								
							}
								
						}// links
						
					}
					
					/*foreach ($tds as $cell){
					
						echo $cell->nodeValue;
						
						
					}// cell
					*/
				}// tds
				
			}// has tds
			
		}
		
		
		return NULL;
	}
	
	
	public function parseCSV ( $filename ){
		
		$link = fopen(dirname(__FILE__)."/../".$filename,'r+');
		
		//get header rows
		$hd = fgetcsv($link, 1000, ",");
		$headrs = array();
		
		foreach ($hd as $h){
			
			$headrs[] = str_replace(' ','_',strtolower(trim($h)));
				
		}
		
		$data = array();
		
		
		 while (($row = fgetcsv($link, 1000, ",")) !== FALSE) {
			 
			 	$set = array();
				
				foreach ($headrs as $index => $header){
					
					if(isset($row[$index])){
						$set[$header] = trim($row[$index]);	
					}
					else{
						$set[$header] = "";	
					}
						
				}
				
				$data[] = $set;
		 }
		
		
		fclose($link);
		
		return $data;
		
	}
	
	
	public function parseReportResult ($html){
		
		
		$rows = explode('<tr', $html);
		$header_row = $rows[2];
		
		//echo $rows[2];
		//exit();
		
		// extracting headers
		$ths = explode('<th', $header_row);
		array_shift($ths);
		
		
		$headers = array();
		foreach ($ths as $th){
			
			$headers[] = str_replace(' ','_',strtolower(trim(strip_tags('<th'.$th))));
				
		}	
		
		// extracting rows
		$data = array();		
		
		$i = 0;
		
		foreach ($rows as $item){
			$i++;
			
			if($i <= 3){
				continue;	
			}
			
			$tds = explode('<td', $item);
			array_shift($tds);
			
			$set = array();
			
			foreach ($headers as $index => $header){
				
				$set[$header] = trim(strip_tags('<td'.$tds[$index]));
				
			}
			
			$data[] = $set;			
		}
				
		return $data;
		
	}
	
	
	
	
	
	
} // class