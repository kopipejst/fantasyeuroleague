<?php

	/**
	 * Class for scrapping players data from euroleague fantasy challenge
	 */

	class EL {

		private $username	= "******"; 
		private $password	= "******"; 
		private $cookie 	= "cookie.txt";
		private $data 		= array();
		private $count 		= 0;

		public function __construct(){
			$this->login();
		}

		private function login(){

			$url = "http://fantasychallenge.euroleague.net/index.php";

			$postData = "usuario=".$this->username."&clave=".$this->password."&CMD=1&entrar=Enter"; 

			$this->getRemoteData($url, $postData);

		}

		/**
		 * Data for player are on 3 different pages depending on player position
		 * @param  integer $position [description]
		 * @return [type]            [description]
		 */
		private function getPlayerData($position = 1) {

			$url = array(
				'1' => 'http://fantasychallenge.euroleague.net/playermarket.php',
				'2' => 'http://fantasychallenge.euroleague.net/playermarket.php?id_pos=2',
				'3' => 'http://fantasychallenge.euroleague.net/playermarket.php?id_pos=3'

			);

			$result = $this->getRemoteData($url[$position]);

			return $result;

		}

		/**
		 * Filter HTML page for player data
		 * @param  integer $position [description]
		 * @return [type]            [description]
		 */
		private function filterData($position = 1){

			$data = $this->getPlayerData($position);

			$dom = new Zend_Dom_Query($data);
			$results = $dom->query('.datocontenido');

			$fields = array('Player', 'Team', 'Record', 'Score', 'Price', 'Opponent');
			$count = $this->count;
			$res = array();


			foreach($results as $result){
				$field  = 0;

				foreach($result->childNodes as $part){
					if(trim($part->nodeValue) != ""){
						$res[$this->count][$fields[$field]] = $part->nodeValue;
						$field++;
					}
				}
				//$res[$count]['position'] = $position;
				//
				$this->count++;

			}

			unset($res[$count]);

			$this->data += $res;

		}


		/**
		 * Returns all players in desired format
		 * @param  string $format [description]
		 * @return [type]         [description]
		 */
		public function getAllData($format = 'csv'){
			$this->filterData('1');
			$this->filterData('2');
			$this->filterData('3');
			
			if($format == 'csv'){
				$this->printCsv();
			} else {
				$this->printJson();
			}

		}

		private function getRemoteData($url, $postData = ""){

			$ch = curl_init(); 
			
			curl_setopt($ch, CURLOPT_URL, $url); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6"); 
			curl_setopt($ch, CURLOPT_TIMEOUT, 60); 
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie); 
			curl_setopt($ch, CURLOPT_REFERER, $url); 
			
			if($postData){
				curl_setopt ($ch, CURLOPT_POSTFIELDS, $postData); 
				curl_setopt ($ch, CURLOPT_POST, 1); 				
			}

			$result = curl_exec ($ch); 

			curl_close($ch);

			return $result;

		}

		private function printJson(){
			header("Content-type: application/json");
			echo json_encode($this->data);
		}

		private function printCsv(){

			$fp = fopen('euroleague.csv', 'w');
			//var_dump($this->data);
			foreach ($this->data as $fields) {
    			fputcsv($fp, $fields);
			}

			fclose($fp);
			header('Content-Disposition: attachment; filename="euroleague.csv"');
			readfile('euroleague.csv');

		}

	}







