<?php

namespace Drupal\email_contact\Controller;

class ContentFactory{
	protected $user;

	public function makeUserwithRole($role=NULL){
		if(strtolower($role) == 'authenticated'){
			return $this->user = new UserRoleauthenticated();
		}else{
			return $this->user = new UserRoleOther();
		}
	}
}
