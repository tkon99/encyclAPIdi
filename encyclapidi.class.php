<?php
class encyclapidi{
	private $api_base = "http://www.encyclo.nl/app/zoek2.php?woord=";
	private $suggest_base = "http://www.encyclo.nl/autoComplete/rpc.php";
	private $device = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";

	public function __construct(){
		if($this->get('test') == false){
			throw new Exception('Encyclo is down, or not accepting requests on: "'.$this->api_base.'", stopping script.');
			exit();
		}
	}

	public function getWord($word = false, $strip = false){
		if($word == false){
			throw new Exception('function getWord() needs a word as parameter.');
		}else{
			$raw = $this->get($word);
			$suggestions = array();
			$results = array();
			if(array_key_exists("resultaten", $raw)){
				//word found
				$results = $raw["resultaten"];
				if($strip !== false){
					for($i = 0; $i < sizeof($results); $i++){
						$results[$i]["betekenis"] = strip_tags($results[$i]["betekenis"]);
					}
				}
				$suggestions = $this->suggest($word, true);
			}else{
				//word not found
				if(array_key_exists("welindedatabase", $raw)){
					if(trim($raw["welindedatabase"]) !== ""){
						//suggestions
						foreach(explode('|', trim($raw["welindedatabase"])) as $suggestion){
							if(trim($suggestion) !== "" && !empty($suggestion)){
								$suggestions[] = $suggestion;
							}
						}
					}else{
						//no suggestions, try suggestions engine
						$suggestions = $this->suggest($word, true);
					}
				}else{
					//no suggestions, try suggestions engine
					$suggestions = $this->suggest($word, true);
				}
			}
			$return = array();
			$return["results"] = $results;
			$return["suggest"] = $suggestions;

			return $return;
		}
	}

	public function suggest($word = false, $ignoreword = true){
		if($word == false){
			throw new Exception('function suggest() needs a word as parameter.');
		}else{
			$url = $this->suggest_base;
			$device = $this->device;

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "woord=".urlencode($word));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, $device);
			$resp = curl_exec ($ch);

			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			 
			curl_close($ch);

			if($httpcode>=200 && $httpcode<300){
				//Success
				$return = array();

				$items = explode("<span>", $resp);
				for($i = 0; $i < count($items); $i++){
					$items[$i] = str_replace("</span>", "", $items[$i]);
					preg_match("/<a (?:.+)>(.+)<font(?:.+)>\((.+)x\)<\/font><\/a>/", $items[$i], $match);
					if($match[1] !== null){
						if($ignoreword == true){
							if(strtolower(trim($match[1])) !== strtolower(trim($word))){
								$return[] = trim($match[1]);
							}
						}else{
							$return[] = trim($match[1]);
						}
					}
				}

				return $return;
			}else{
				return false;
			}
		}
	}

	private function get($param = ''){
		$getUrl = $this->api_base.$param;
		$device = $this->device;

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $getUrl,
			CURLOPT_USERAGENT => $device
		));
		$resp = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		//echo($httpcode);
		curl_close($curl);

		if($httpcode>=200 && $httpcode<300) return json_decode($resp, 1);
		else return false;
	}
}
?>