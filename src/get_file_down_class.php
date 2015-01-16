<?php

	
	class get_file_down{
	
	
		static private $_instance = '';
		
		private $source_url = '';
		private $root = '';
		private $pattern = '';
		private $subffix = '';
		private $dir = '';
		private $direction = '';
		private $next_pattern = '';
		private $depth = 0;
		private $base = '';
		private $use_depth = false;
		private $max_depth = 10;
		
		const DS = DIRECTORY_SEPARATOR;
		
		
		static public function get_instance(){
			if(self::$_instance == ''){
				self::$_instance = new get_file_down();
			}
			return self::$_instance;
		}
		
		public function init_depth($use,$max){
			$this->use_depth = $use;
			$this->max_depth = $max;
		}
		public function init($s_url,$r,$p,$s,$d,$direction,$next_pattern,$base){
			
			$this->source_url = $s_url;
			$this->root = $r;
			$this->pattern = $p;
			$this->subffix = $s;
			$this->dir = $d;
			$this->direction = $direction;
			$this->next_pattern = $next_pattern;
			$this->depth = 0;
			$this->base = $base;

			set_time_limit(0);
			ignore_user_abort(true);

		}
		
		
		public function down(){
		
			do{
				if($this->use_depth){

					if($this->depth > $this->max_depth)
					break;
				}
				
				$data = $this->_get_raw_data();
				
				echo $this->source_url;
				echo '<br/>';
				
				$keys = $this->_get_pattern_data($data);
				$this->source_url = $this->_get_next_url($data);
				if(!empty($this->source_url)){
					if(strpos($this->source_url,'http') === FALSE){
						$this->source_url = $this->base.'/'.$this->source_url;	
					}
				}
				
				/*
				foreach($keys as $key){
					echo $key.'
					<br/>				
					';
				}*/
				
				$this->_down($keys);
				$this->depth++;

			}while(!empty($this->source_url));
			
			
		}
		
		private function _get_raw_data(){
		
			$handle = curl_init();
			curl_setopt($handle,CURLOPT_URL,$this->source_url);
			curl_setopt($handle,CURLOPT_HEADER,0);
			curl_setopt($handle,CURLOPT_RETURNTRANSFER,1);
			$data = curl_exec($handle);
			curl_close($handle);
			
			return $data;
		}

		private function _get_next_url($data){

				if(empty($this->direction)){
					return '';
				}
				$matches = array();
				preg_match($this->next_pattern,$data,$matches);
				if(!empty($matches)){
					return $matches[0];
				}
				return '';	
		}

		private function _get_pattern_data($data){
		
			$matches = array();
			$files = array();
			$result = preg_match_all($this->pattern,$data,$matches);
			try{
				if($result !== FALSE){
					if(count($result)<=0){
						throw new Exception('not found any matches.');
					}
					$files = $matches[0];
					if($this->root != ''){
						foreach($files as &$key){
							$key = $this->root.$key;
						}
					}
				}else{
					throw new Exception('preg_match_all failed.');
				}
			}catch(Exception $exp){
				echo $exp->getMessage();
				return array();
			}
			return $files;
		}
		
		private function _down($keys){
		
			$conn = array();
			$fp = array();
			$mh = curl_multi_init();
			
			foreach($keys as $key){
			
				$file_key = basename($key,$this->subffix);
				$file = ($this->dir).self::DS.basename($key);
				
				if(!is_file($file)){
					$conn[$file_key] = curl_init($key);
					$fp[$file_key] = fopen($file,'wb');
					curl_setopt($conn[$file_key],CURLOPT_FILE,$fp[$file_key]);
					curl_setopt($conn[$file_key],CURLOPT_HEADER,0);
					curl_setopt($conn[$file_key],CURLOPT_TIMEOUT,600);
					
					curl_multi_add_handle($mh,$conn[$file_key]);
				}
				
			}
			
			do{
				$m_r = curl_multi_exec($mh,$active);
			}while($active);
			
			foreach($conn as $c){
				curl_multi_remove_handle($mh,$c);
				curl_close($c);
			}
			foreach($fp as $f){
				fclose($f);
			}
			
			curl_multi_close($mh);
			
			return true;
		}
		
	}
	
?>