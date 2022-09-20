<?php

namespace  Drupal\email_contact\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Connection;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "contactlist_block",
 *   admin_label = @Translation("contact  listing block"),
 * )
 */
class ContactListing extends BlockBase implements ContainerFactoryPluginInterface {

    /**
     * The current user.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $account;

    /**
     * VbDataListing constructor.
     *
     * @param array $configuration
     *   The plugin configuration.
     * @param string $plugin_id
     *   The plugin ID.
     * @param mixed $plugin_definition
     *   The plugin definition.
     * @param \Drupal\Core\Session\AccountProxyInterface $account
     *   The current user.
     * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
     *   The entity type manager.
     */
		  /**
		 * The database connection to be used.
		 *
		 * @var \Drupal\Core\Database\Connection
		 */
			protected $database;

    public function __construct(
      array $configuration,
      $plugin_id,
      $plugin_definition,
      AccountProxyInterface $account,
      EntityTypeManagerInterface $entity_type_manager, Connection $database) {
      parent::__construct($configuration, $plugin_id, $plugin_definition);
      $this->account = $account;
      $this->entityTypeManager = $entity_type_manager;
			$this->database = $database;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
      return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->get('current_user'),
        $container->get('entity_type.manager'),
				$container->get('database'),
      );
    }

    /**
     * {@inheritdoc}
     */
    public function build() {
      // Create table header.
      $header_table = [
        'title' => $this->t('Title'),
        'name' => $this->t('FullName'),
        'email' => $this->t('Email'),
        'Tech' => $this->t('test_checkboxes'),
        'action' => $this->t("Action"),
      ];


			$query = $this->database->select('custom_contact_mail', 'ccm');
			$query->fields('ccm', ['fid', 'title', 'fullname', 'email', 'test_checkboxes']);
			$results = $query->execute()->fetchAll();

      $rows = [];
      foreach ($results as $data) {
				$checkbox  = implode(',', unserialize($data->test_checkboxes));
        $rows[] = [
          'title' => $data->title,
          'fullname' => $data->fullname,
          'email' => $data->email,
          'test_checkboxes' =>$checkbox,
        ];
      }
      // Display data in site.
      $form['table'] = [
        '#type' => 'table',
        '#header' => $header_table,
        '#rows' => $rows,
        '#empty' => $this->t('No list found'),
      ];
      return $form;
    }

    /**
     * {@inheritdoc}
     */
    protected function blockAccess(AccountInterface $account) {
      return AccessResult::allowedIfHasPermission($account, 'access content');
    }

    public function getCacheTags() {
      //With this when your node change your block will rebuild
      if ($node = \Drupal::routeMatch()->getParameter('node')) {
        //if there is node add its cachetag
        return Cache::mergeTags(parent::getCacheTags(), array('node:' . $node->id()));
      } else {

        //Return default tags instead.
        return parent::getCacheTags();
      }
    }

    public function getCacheContexts() {
      //if you depends on \Drupal::routeMatch()
      //you must set context of this block with 'route' context tag.
      //Every new route this block will rebuild
      return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
    }

  }
