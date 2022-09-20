<?php

namespace Drupal\email_contact\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event that is fired when a user logs in.
 */
class CustomDataEvent extends Event {

  const EVENT_NAME = 'custom_events_subscriber';

  /**
   * The node id.
   *
   * @var nid
   */
  public $nid;

  /**
   * The node title.
   *
   * @var title
   */
  public $title;

  /**
   * The operation performed.
   *
   * @var operation
   */
  public $operation;

  /**
   * Constructs the object.
   */
  public function __construct($nid, $title, $operation) {
    $this->nid = $nid;
    $this->title = $title;
    $this->operation = $operation;
  }
}