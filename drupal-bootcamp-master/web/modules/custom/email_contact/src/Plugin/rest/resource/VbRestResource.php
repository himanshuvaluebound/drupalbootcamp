<?php

namespace Drupal\email_contact\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\node\Entity\Node;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rest\ModifiedResourceResponse;



/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "base_data_rest_resource",
 *   label = @Translation("base players rest resource"),
 *   serialization_class = "",
 *   uri_paths = {
 *     "canonical" = "/api/baseplayersdata/{nid}",
 *     "https://www.drupal.org/link-relations/create" = "/api/baseplayersdata"
 *   }
 * )
 */



class VbRestResource extends ResourceBase
{
  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('custom_rest'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @param string $nid
   *   Content type of node.
   *
   *   Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get($nid = NULL) {
    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    if (!is_numeric($nid)) {
      $entity_storage = $this->entityTypeManager->getStorage('node');
      $nids = $entity_storage->getQuery()->condition('type', 'baseplayer')->execute();
    } else {
      $nids[$nid] = $nid;
    }
    $entities = $this->entityTypeManager
      ->getStorage('node')
      ->loadMultiple($nids);
    foreach ($entities as $entity) {
      $result[$entity->id()]["title"] = $entity->title->value;
      $result[$entity->id()]["name"] = $entity->field_name->value;
      $result[$entity->id()]["body"] = $entity->body->value;
    }

    $response = new ResourceResponse($result);
    // $response->addCacheableDependency($result);
    return $response;
  }

  /**
   * PUT method to update the Node.
   */
  public function put($nid, $data) {
    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException('Access Denied');
    }
    $data = (object) $data;
    $title = $data->title;
    $name = $data->name;
    $body = $data->body;
    if (empty($title)) {
      $response = [
        "data" => "Title is required",
      ];
      return new ResourceResponse($response);
    }
    if (empty($name)) {
      $response = [
        "data" => "Name is required",
      ];
      return new ResourceResponse($response);
    }
    if (empty($body)) {
      $response = [
        "data" => "Body is required",
      ];
      return new ResourceResponse($response);
    }

    $node_storage = $this->entityTypeManager->getStorage('node');
    $node = $node_storage->load($nid);
    $node->title->value = $title;
    $node->field_name->value = $name;
    $node->body->value = $body;
    $node->save();

    $this->logger->notice($this->t("Node with nid @nid Updated!\n", ['@nid' => $nid]));
    return new ResourceResponse($node);
  }

  /**
   * Responds to entity DELETE requests.
   *
   * @param int $nid
   *   The ID of the record.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function delete($nid) {
    $node = $this->entityTypeManager->getStorage('node')->load($nid);

    // Check if node exists with the given nid.
    if ($node) {
      $node->delete();
      $this->logger->notice($this->t("Node with nid @nid saved!\n", ['@nid' => $nid]));
    } else {
      $this->logger->notice($this->t("Following Nid @nid is Invalid!\n", ['@nid' => $nid]));
    }
    return new ModifiedResourceResponse(NULL, 204);
  }

  /**
   * Responds to POST requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException('Access Denied');
    }
    $data = (object) $data;

    $type = $data->type[0]['target_id'];
    $title = $data->title[0]['value'];
    $body = $data->body[0]['value'];
    $name = $data->name[0]['value'];


    if (empty($type)) {
      $response = [
        "data" => "content type is required",
      ];
      return new ResourceResponse($response);
    }
    if (empty($title)) {
      $response = [
        "data" => "Title is required",
      ];
      return new ResourceResponse($response);
    }
    if (empty($name)) {
      $response = [
        "data" => "Name is required",
      ];
      return new ResourceResponse($response);
    }
    if (empty($body)) {
      $response = [
        "data" => "Body is required",
      ];
      return new ResourceResponse($response);
    }

    $node = $this->entityTypeManager->getStorage('node')->create(
      [
        'type' => $type,
        'title' => $title,
        'field_name' => $name,
        'body' => [
          'summary' => '',
          'value' => $body,
          'format' => 'basic_html',
        ],
      ]
    );
    if (!empty($country)) {
      $node->field_country->target_id = $country;
    }
    if (!empty($state)) {
      $node->field_state->target_id = $state;
    }
    if (!empty($city)) {
      $node->field_city->target_id = $city;
    }
    $node->enforceIsNew();
    $node->save();
    $this->logger->notice($this->t("Node with nid @nid saved!\n", ['@nid' => $node->id()]));
    return new ResourceResponse($node);
  }
}
