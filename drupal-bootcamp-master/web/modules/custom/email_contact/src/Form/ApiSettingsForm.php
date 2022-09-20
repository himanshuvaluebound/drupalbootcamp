<?php
/**
* @file
*  Contains Drupal\email_contact\Form\ApiSettingsForm.
*/


namespace Drupal\email_contact\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class ApiSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'Apis.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'api_key_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['api_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Name'),
      '#default_value' => $config->get('api_name'),
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API'),
      '#default_value' => $config->get('api_key'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('api_name', $form_state->getValue('api_name'))
      // You can set multiple configurations at once by making
      // multiple calls to set().
      ->set('api_key', $form_state->getValue('api_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}