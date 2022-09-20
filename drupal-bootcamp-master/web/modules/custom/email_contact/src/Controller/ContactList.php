<?php

namespace Drupal\email_contact\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Block\BlockManagerInterface;

/**
 * ContactList class controller for listing VBDATA.
 */
class ContactList extends ControllerBase {

  /**
   * Constructs a new ContactList object.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   A account connection object.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   */
  public function __construct(AccountInterface $account, BlockManagerInterface $block_manager) {
    $this->account = $account;
    $this->blockManager = $block_manager;
  }

  /**
   * Constructs a new ContactList object.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('plugin.manager.block')
    );
  }

  /**
   * Method to generate listing of content.
   */
  public function listing() {
    // You can hard code configuration or you load from settings.
    $config = [];
    $plugin_block = $this->blockManager->createInstance('contactlist_block', $config);
    $render = $plugin_block->build();
    return $render;
  }

}
