<?php

namespace Drupal\email_contact\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\email_contact\Event\CustomDataEvent;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class EntityTypeSubscriber.
 *
 * @package Drupal\email_contact\EventSubscriber
 */
class CustomEventSubscriber implements EventSubscriberInterface {
  use StringTranslationTrait;

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Drupal\Core\Mail\MailManagerInterface defination.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new VBForm object.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory object.
   */
  public function __construct(MessengerInterface $messenger, MailManagerInterface $mail_manager, ConfigFactoryInterface $config_factory) {
    $this->messenger = $messenger;
    $this->mailManager = $mail_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('plugin.manager.mail'),
      $container->get('config')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      CustomDataEvent::EVENT_NAME => 'onVbDataSubmit',
    ];
  }

  /**
   * Subscribe to the user login event dispatched.
   *
   * @param \Drupal\email_contact\Event\CustomDataEvent $event
   *   The event object.
   */
  public function onVbDataSubmit(CustomDataEvent $event) {
    $email = $this->configFactory->getEditable('system.site')->get('mail');
    $module = 'email_contact';
    $key = 'email_contact_mail';
    $to = $email;
    $params['title'] = $this->t('@title (@nid)',
    [
      '@title' => $event->title,
      '@nid' => $event->nid,
    ]);
    $params['body'] = $this->t('Content @title has been @operation : @nid',
    [
      '@title' => $event->title,
      '@operation ' => $event->operation,
      '@nid' => $event->nid,
    ]);
    $params['subject'] = $this->t('VB Data');
    $langcode = "EN";

    $result = $this->mailManager->mail($module, $key, $to, $langcode, $params);

    if ($result['result'] == 1) {
      $this->messenger->addStatus('Notification has been sent to the Site Administrator.');
    }
    else {
      $this->messenger->addStatus('Enable to send email.');
    }
  }

}
