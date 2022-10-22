<?php
	class ViennaSmartmeter{

		private $username;
		private $password;
		private $AUTHURL;
		private $API_URL_WSTW;
		private $API_URL_WN;
		private $access_token;
		private $cookieData;
		private $debug;
		public function __construct($username, $password, $debug=false){
			$this->username = $username;
			$this->password = $password;
			$this->AUTHURL = "https://log.wien/auth/realms/logwien/protocol/openid-connect/";
			$this->API_URL_WSTW = "https://api.wstw.at/gateway/WN_SMART_METER_PORTAL_API_B2C/1.0/";
    		$this->API_URL_WN = "https://service.wienernetze.at/rest/smp/1.0/";
    		$this->access_token = "";
    		$this->debug = $debug;
		}

		public function login(){
			$args = array(
				"client_id" => "wn-smartmeter",
				"redirect_uri" => "https://www.wienernetze.at/wnapp/smapp/",
				"response_mode" => "fragment",
				"response_type" => "code",
				"scope" => "openid",
				"nonce" => "",
				"prompt" => "login"
			);
			$params = "";
			foreach($args as $key=>$val){
				$params .= "&".$key."=".urlencode($val);
			}
			$login_url = $this->AUTHURL . "auth?" . substr($params, 1, strlen($params));

			$ch = curl_init ($login_url);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$content = $this->splitCookies(curl_exec($ch), $cookieData);
			$this->cookieData = $cookieData;
			curl_close($ch);

			$matches = array();
			preg_match('/action="(.*)"/', $content, $matches);
			$action = str_replace('action="', '', $matches[0]);
			$action = str_replace("&amp;", "&", substr($action, 0, strlen($action)-1));


			//print_r($action);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $action);
			curl_setopt($ch, CURLOPT_HEADER, 1);	
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_COOKIE, $cookieData);
			curl_setopt($ch, CURLOPT_POSTFIELDS,"username=".$this->username."&password=".$this->password); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
			$content = curl_exec($ch);
			curl_error($ch);
			curl_close ($ch);

			//print_r($content);

			if(!strstr($content, "Location:")){
				//echo "Login Error.<br />";
			}else{
				//echo "Login OK<br />";
			}

			$matches = array();
			preg_match('/code=(.*)/', $content, $matches);
			$code = trim(str_replace("code=", "", $matches[0]));

			//print_r($code);

			$data = array(
				"code" => $code,
				"grant_type" => "authorization_code",
				"client_id" => "wn-smartmeter",
				"redirect_uri" => "https://www.wienernetze.at/wnapp/smapp/"
			);

			//print_r($data);


			$headers = array(
				'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
				'Accept: application/x-www-form-urlencoded'
			);

            //print_r($headers);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->AUTHURL."token");
			curl_setopt($ch, CURLOPT_HEADER, 1);	
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_COOKIE, $cookieData);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "code=".$code."&grant_type=authorization_code&client_id=wn-smartmeter&redirect_uri=https://www.wienernetze.at/wnapp/smapp/"); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$content = curl_exec($ch);
			echo curl_error($ch);
			curl_close ($ch);

			//print_r($content);

			$parts = explode("\r\n\r\n", $content);
			$body = json_decode($parts[1]);

			$this->access_token = $body->access_token;
		}

		public function wn($endpoint, $params=null, $method="GET"){
			$base_url = $this->API_URL_WN;
			$url = $this->API_URL_WN.$endpoint;

			$headers = array(
				"Authorization: Bearer ".$this->access_token
			);

			if($method=="GET"){
				if($params){
					$append = "?";
					foreach($params as $key=>$val){
						$append .= "".$key."=".$val."&";
					}
					substr($append, 0, strlen($append)-1);
					$url = $url.$append;
				}
			}

			//print_r($headers);
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HEADER, true);
			if($method=="GET"){
				curl_setopt($ch, CURLOPT_HTTPGET, 1);
			}elseif($method=="POST"){
				$headers[] = "Content-Type: application/json";
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
			}elseif($method="DELETE"){
				 curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$content = curl_exec($ch);
			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			curl_close($ch);

			if($this->debug)
				print_r($content);

			$header = substr($content, 0, $header_size);
			$body = substr($content, $header_size);

			return json_decode($body);
		}

		public function wstw($endpoint, $params=null, $method="GET"){
			$base_url = $this->API_URL_WSTW;
			$url = $this->API_URL_WSTW.$endpoint;

			$headers = array(
				"Authorization: Bearer ".$this->access_token,
				"X-Gateway-APIKey: afb0be74-6455-44f5-a34d-6994223020ba"
			);

			if($method == "GET"){
				if($params){
					$append = "?";
					foreach($params as $key=>$val){
						$append .= "".$key."=".$val."&";
					}
					substr($append, 0, strlen($append)-1);
					$url = $url.$append;
				}
			}

			//print_r($url);
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HEADER, true);
			if($method == "GET"){
				curl_setopt($ch, CURLOPT_HTTPGET, 1);
			}elseif($method == "DELETE"){
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			}elseif($method == "POST"){
				$headers[] = "Content-Type: application/json";
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$content = curl_exec($ch);
			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			curl_close($ch);

			if($this->debug)
				print_r($content);

			$header = substr($content, 0, $header_size);
			$body = substr($content, $header_size);

			return json_decode($body);
		}

		public function getProfile(){
			return $this->wn("w/user/profile");
		}

		public function welcome(){
			return $this->wstw("zaehlpunkt/default/welcome");
		}

		public function getConsumption($meterpoint, $start, $end){
			//Date Format: "%Y-%m-%dT%H:%M:%S.%f"
			$start = str_replace(" ", "T", $start).".000Z";
			$end = str_replace(" ", "T", $end).".999Z";

			$endpoint = "messdaten/zaehlpunkt/".$meterpoint."/verbrauch";
			$params = array(
	            "dateFrom" => $start,
	            "dateTo" => $end,
	            "period" => "DAY",
	            "dayViewResolution" => "QUARTER-HOUR",
	            "offset" => "0",
	            "accumulate" => "false"
	        );
	        return $this->wstw($endpoint, $params);
		}

		public function getEvents($meterpoint, $start, $end){
			$start = $this->formatDate($start, "start");
			$end = $this->formatDate($end, "end");
			$params = array(
				"zaehlpunkt" => $meterpoint, 
				"dateFrom" => $start,
				"dateUntil" => $end
			);
			$endpoint = "w/user/ereignisse";

			return $this->wn($endpoint, $params);
		}

		public function createEvent($meterpoint, $name, $start, $end=null){
			if(!$end){
				$type = "ZEITPUNKT";
				$end = "None";
			}else{
				$type = "ZEITSPANNE"; 
				$end = $this->formatDate($end, "end");
			}
			$start = $this->formatDate($start, "start");

			$params = array(
				"name" => $name,
				"startAt" => $start,
				"endAt" => $end,
				"typ" => $type, 
				"zaehlpunkt" => $meterpoint
			);

			$endpoint = "w/user/ereignis";

			$this->wn($endpoint, $params, "POST");
		}

		public function deleteEvent($meterpoint, $id){
			$this->wn("w/user/ereignis/".$id, null, "DELETE");
		}

		public function getLimits(){
			$endpoint = "radar/benachrichtigungen";
			return $this->wstw($endpoint, null);
		}

		public function deleteLimit($id){
			$endpoint = "radar/benachrichtigung/".$id;
			$method = "DELETE";

			return $this->wstw($endpoint, null, $method);
		}

		public function createLimit($name, $end, $period, $threshold, $type, $meterpoint){
			$end = $this->formatDate($end, "end");
			//period can take d or m for day or month.
			if($period == "d") $period = "DAY";
			else $period = "MONTH";
			//threshold in Watt per Hour, not kWh
			//type can take lt ( less than ) and gt ( greater than)
			if($type == "lt") $type = "FALLING_BELOW";
			else $type = "EXCEEDING";

			$endpoint = "radar/benachrichtigung";
			$method = "POST";
			$params = array(
				"name" => $name,
				"notifyUntil" => $end,
				"periode" => $period,
				"threshold" => $threshold, 
				"type" => $type,
				"zaehlpunktnummer" => $meterpoint
			);

			return $this->wstw($endpoint, $params, $method);
		}

		public function getNotifications($limit, $order){
			$endpoint = "radar/ereignisse?limit=".$limit."&order=".$order;
			return $this->wstw($endpoint);
			
		}

		private function splitCookies($rawResponse, &$cookieData){
			// Separate header and body
			list($curlHeader, $curlBody) = preg_split("/\R\R/", $rawResponse, 2);

			// Split out data from Set-Cookie headers
			preg_match_all("/^Set-Cookie:\s+(.*);/mU", $curlHeader, $cookieMatchArray);
			$cookieData = implode(';', $cookieMatchArray[1]);

			return $curlBody;
		}

		private function formatDate($date, $type=null){
			if($type == "start")
				return str_replace(" ", "T", $date).".000Z";
			elseif($type == "end")
				return str_replace(" ", "T", $date).".999Z";
			else
				return str_replace(" ", "T", $date).".000Z";
		}

		public function __destruct(){

		}

	}
