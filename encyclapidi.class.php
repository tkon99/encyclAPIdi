<?php
class encyclapidi{
	private $api_base = "http://www.encyclo.nl/app/zoek2.php?woord=";
	private $api_device = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";

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
						//no suggestions
					}
				}else{
					//no suggestions
				}
			}
			$return = array();
			$return["results"] = $results;
			$return["suggestions"] = $suggestions;

			return $return;
		}
	}

	private function get($param = ''){
		$getUrl = $this->api_base.$param;
		$device = $this->api_device;

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