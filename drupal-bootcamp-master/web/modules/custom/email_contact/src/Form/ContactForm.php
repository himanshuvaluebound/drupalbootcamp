<?php

namespace Drupal\email_contact\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements a codimth Simple Form API.
 */
class ContactForm extends FormBase
{

	/**
	 * @param array $form
	 * @param FormStateInterface $form_state
	 * @return array
	 */

	/**
	 * The Messenger service.
	 *
	 * @var \Drupal\Core\Messenger\MessengerInterface
	 */
	protected $messenger;

	/**
	 * MyModuleService constructor.
	 *
	 * @param \Drupal\Core\Messenger\MessengerInterface $messenger
	 *   The messenger service.
	 */

	/**
	* The database connection to be used.
	*
	* @var \Drupal\Core\Database\Connection
	*/
 protected $database;
  /**
   * @var AccountInterface $account
   */
  protected $account;

 /**
	* @param \Drupal\Core\Database\Connection $database
	*   The database connection to be used.
	*/
	public function __construct(MessengerInterface $messenger, Connection $database, AccountInterface $account) {
		$this->messenger = $messenger;
		$this->database = $database;
		$this->account = $account;

	}

	/**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('messenger'),
			$container->get('database'),
			$container->get('current_user'),
    );
  }

	public function buildForm(array $form, FormStateInterface $form_state) {
		// Item
		$form['description'] = [
			'#type' => 'item',
			'#markup' => $this->t('This is Conact Form'),
		];

		// Textfield.
		$form['title'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Title'),
			'#size' => 60,
			'#maxlength' => 128,
		];

		// CheckBoxes.
		$form['test_checkboxes'] = [
			'#type' => 'checkboxes',
			'#options' => ['drupal' => $this->t('Drupal'), 'wordpress' => $this->t('Wordpress')],
			'#title' => $this->t('Are you developer :'),
		];

		// name.
		$form['fullname'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Fullname'),
		];

		// email.
		$form['email'] = [
			'#type' => 'email',
			'#title' => $this->t('Email'),
		];


		// Add a submit button
		$form['actions']['submit'] = [
			'#type' => 'submit',
			'#value' => $this->t('Submit'),
		];


		return $form;
	}

	/**
	 * @return string
	 */
	public function getFormId()
	{
		return 'contact_form_api';
	}

	/**
	 * @param array $form
	 * @param FormStateInterface $form_state
	 */
	public function validateForm(array &$form, FormStateInterface $form_state)
	{
		$title = $form_state->getValue('title');
		if (strlen($title) < 15) {
			$form_state->setErrorByName('title', $this->t('The title must be at least 15 characters long.'));
		}
	}

	/**
	 * @param array $form
	 * @param FormStateInterface $form_state
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {

		$record = array();
		$field = $form_state->getValues();
		$title = $field['title'];
		$fname = $field['fullname'];
		$email = $field['email'];
		$checkboxes = $field['test_checkboxes'];


		$field  = [
			'title'   =>  $title,
			'fullname' =>  $fname,
			'email' =>  $email,
			'test_checkboxes' => serialize($checkboxes),
		];

		$query = $this->database->insert('custom_contact_mail')
			->fields($field)
			->execute();
		$this->messenger->addMessage("succesfully saved");
		$mailManager = \Drupal::service('plugin.manager.mail');
		$module = 'email_contact';
		$key = 'contact_user';
		$system_site_config = \Drupal::config('system.site');
 		$site_email = $system_site_config->get('mail');
		$params['message'] = 'this is contact Form ';
		$params['fname'] = $fname;
		$params['title'] = $title;
		$params['email'] = $email;
		$params['Tech'] = serialize($checkboxes);
		$send = true;

		$result = $mailManager->mail($module, $key, $site_email, NULL, $params, NULL, $send);
		if ($result['result'] !== true) {
			$this->messenger->addMessage($this->t('There was a problem sending your message and it was not sent.'), 'error');
		} else {
			$this->messenger->addMessage($this->t('Your message has been sent.'));
		}
	}
}
