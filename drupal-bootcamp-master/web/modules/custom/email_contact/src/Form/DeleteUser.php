<?php

namespace Drupal\email_contact\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;


/**
 * Class DeleteUser
 * @package Drupal\email_contact\Form
 */
class DeleteUser extends ConfirmFormBase
{

	public $id;

	/**
	 * The database connection to be used.
	 *
	 * @var \Drupal\Core\Database\Connection
	 */
	protected $database;

	public function __construct(Connection $database)
	{
		$this->database = $database;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container)
	{
		// Instantiates this form class.
		return new static(
			// Load the service required to construct this class.
			$container->get('database'),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFormId()
	{

		return 'delete_form';
	}

	public function getQuestion()
	{
		return $this->t('Delete data');
	}

	public function getCancelUrl()
	{
		return new Url('email_contact.table');
	}

	public function getDescription()
	{
		return $this->t('Do you want to delete data number %id ?', array('%id' => $this->id));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConfirmText()
	{
		return $this->t('Delete it!');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCancelText()
	{
		return $this->t('Cancel');
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(array $form, FormStateInterface $form_state, $id = NULL)
	{

		$this->id = $id;
		return parent::buildForm($form, $form_state);
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateForm(array &$form, FormStateInterface $form_state)
	{
		parent::validateForm($form, $form_state);
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state)
	{

		$query= $this->database->delete('custom_contact_mail')
			->condition('fid', $this->id)
			->execute();
		\Drupal::messenger()->addStatus('Succesfully deleted.');
		$form_state->setRedirect('email_contact.table');
	}
}
