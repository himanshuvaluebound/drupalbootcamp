<?php

namespace Drupal\email_contact\Controller;

class GetConnection {
	private static $instance = Null;
	private $mem = Null;

	//the constructor is private
	//to prevent initiation with outer code

	private function __construct(){
		$this->mem = [
			'#markup' 	=> spl_object_hash($this)
		];
	}

	public function getConnection(){
		return $this->mem;
	}

	public static function get_instance(){
		if(empty(self::$instance)){
			self::$instance = new getConnection();
		}
		return self::$instance;
	}
}