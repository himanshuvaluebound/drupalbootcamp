<?php


/**
* @file providing the service that say hello world and hello 'given name'.
*
*/

namespace  Drupal\custom_common;
use Drupal\Core\Database\Connection;
use Drupal\node\Entity\Node;


class GetUser {

	protected $userid;
	private $Database;

	public function __construct(Connection $connection) {
	 $this->userid = 1;
	 $this->database = $connection;
	}

	public function  getnodelist($uid = ''){
	 if (empty($uid)) {
	   return $this->userid;
	 }
	 else {
	   	$uid = $this->userid;
			$my_article = Node::create(['type' => 'article']);
			$my_article->set('title', 'article from service');
			$my_article->set('body', 'My text');
			$my_article->set('uid', $uid);
			$my_article->enforceIsNew();
			$my_article->save();
			return;
	 }
	}
	  /**
   * Returns list of nids from node table.
   */
  public function drupalise ($uid) {
  	$query = $this->database->select('node_field_data', 'nfd');
  	$query->fields('nfd', ['nid','title','type']);
  	$query->condition('nfd.uid',$uid,'=');
  	$result = $query->execute()->fetchAll();

    return $result;
  }

}
