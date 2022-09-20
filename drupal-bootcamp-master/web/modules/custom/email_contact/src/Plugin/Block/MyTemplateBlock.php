<?php

namespace Drupal\email_contact\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'My Template' block.
 *
 * @Block(
 *   id = "my_template_block",
 *   admin_label = @Translation("My Template")
 * )
 */
class MyTemplateBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['label_display' => FALSE];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $renderable = [
      '#theme' => 'my_template',
      '#test_var' => 'test variable',
    ];

    return $renderable;
  }

}