<?php

namespace Drupal\email_contact\Controller;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\user\Entity\User;

/**
 * Class HomeController.
 */
class CacheCont extends ControllerBase {

  /**
   * The cache backend service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;


  /**
   * Constructs a new HomeController object.
   */
  public function __construct(CacheBackendInterface $cache_backend) {
    $this->cacheBackend = $cache_backend;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('email_contact.my_cache')
    );
  }

  /**
   * Build the hierarchical tree.
   *
   * @return array
   *   Return the render array of the hierarchical tree.
   */
  public function useraccess() {
    $user = User::load(\Drupal::currentUser()->id());

    $cid = 'email_contact:' . $user->id();
    // Check if there is any cache item associated with this cache tag.
    $data_cached = $this->cacheBackend->get($cid);

    if (!$data_cached) {
      // Build the user dynamic data.
      $data = $user->getAccountName() . ' Email' .$user->getEmail().' last accessed at ' . date('H:i', $user->getLastAccessedTime());

      // Merge the entity cache of an user entity with our custom tag.
      $tags = Cache::mergeTags(['user:' . $user->id()], [$cid]);

      // // Store the data into the cache.
      $this->cacheBackend->set($cid, $data, CacheBackendInterface::CACHE_PERMANENT, $tags);
    } else {
      $data = $data_cached->data;
      $tags = $data_cached->tags;
    }

    $build = [
      '#theme' => 'user_data',
      '#user' => $user->id(),
      '#data' => $data,
      '#cache' => [
        'tags' => $tags,
        'context' => ['user'],
      ],
    ];
    return $build;
  }

}
