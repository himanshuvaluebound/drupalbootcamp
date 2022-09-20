<?php

namespace Drupal\email_contact\Controller;

class MakeConnection {
	public static  function MakeConnection(){
		$con = GetConnection::get_instance();
		$new = $con->getConnection();
		$new2 = $con->getConnection();

		return $new;
	}
}