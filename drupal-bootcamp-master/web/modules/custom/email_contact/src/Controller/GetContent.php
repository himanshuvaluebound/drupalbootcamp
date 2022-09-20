<?php

namespace Drupal\email_contact\Controller;

use DrupalCodeGenerator\Helper\Dumper;

class GetContent{
	protected $users = array();
	protected $content;

	//First create the carFactory object in the constructor.

	public function  __construct(){
		$this->conternt = new ContentFactory();
	}

	public function getContent($role=null){
		$content = $this->content->makeUSerwithRole($role);
		$this->users = $content->getUserNameWithRole();
		return [
			'#markup' => $this->users,
		];
	}
}