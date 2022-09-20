<?php

namespace Drupal\email_contact\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Database\Connection;
use Drupal\Component\Render\FormattableMarkup;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Routing\TrustedRedirectResponse;

class EmailController extends ControllerBase
{

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

  public function contact_form()
  {
    $build = [];
    $build['form'] = $this->formBuilder()->getForm('Drupal\email_contact\Form\ContactForm');
    return $build;
  }

  public function simpal_ajax_form()
  {
    $build = [];
    $build['form'] = $this->formBuilder()->getForm('Drupal\email_contact\Form\AjaxExampleForm');
    return $build;
  }


  /**
   * Display.
   *
   * @return string
   *   Return Hello string.
   */
  public function display()
  {
    //create table header
    $header_table = array(
      'fid' =>    $this->t('SrNo'),
      'title' => $this->t('Title'),
      'name' => $this->t('FullName'),
      'email' => $this->t('Email'),
      'Tech' => $this->t('test_checkboxes'),
      'action' => $this->t("Action")
    );

    //select records from table
    $query = $this->database->select('custom_contact_mail', 'ccm');
    $query->fields('ccm', ['fid', 'title', 'fullname', 'email', 'test_checkboxes']);
    $results = $query->execute()->fetchAll();
    $rows = array();
    foreach ($results as $data) {
      $url_delete = Url::fromRoute('email_contact.delete_list', ['id' => $data->fid], []);
      $link = $url_delete->toString();
      $checkbox  = implode(',', unserialize($data->test_checkboxes));
      //print the data from table
      $rows[] = array(
        'fid' => $data->fid,
        'title' => $data->title,
        'fullname' => $data->fullname,
        'email' => $data->email,
        'test checkboxes' => $checkbox,
        'delete' => new FormattableMarkup(
          '<a href=":link">@name</a>',
          [
            ':link' => $link,
            '@name' => 'Delete'
          ]
        ),
      );
    }
    //display data in site
    $form['table'] = [
      '#type' => 'table',
      '#header' => $header_table,
      '#rows' => $rows,
      '#empty' => $this->t('No users found'),
    ];
    return $form;
  }

  public function nodelist($id)
  {
    $service = \Drupal::service('email_contact.get_data_node_list');
    $all_node = $service->drupalise($id);
    $header = [
      'uid' => $this->t('Uid'),
      'title' => $this->t('title'),
      'type' => $this->t('Type'),
    ];
    $rows = [];
    if (!empty($all_node)) {
      foreach ($all_node as $key => $value) {
        $rows[] = [$value->nid, $value->title, $value->type];
      }
    } else {
      $rows[] = ['No Record Found'];
    }
    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
  }

  public function get_node_api()
  {

    $database = \Drupal::database();
    $query = $database->select('node_field_data','nfd');
    $query->leftjoin('node__body', 'nb','nb.entity_id = nfd.nid');
    $query->leftjoin('node__field_name', 'nfn','nfn.entity_id = nfd.nid');
    $query->fields('nfd', ['title']);
    $query->fields('nfn', ['field_name_value']);
    $query->fields('nb', ['body_value']);
    $query->condition('nfd.type', 'baseplayer');
    $result = $query->execute()->fetchAll();

    if (empty($result)) {
      throw new NotFoundHttpException('No result Found ');
    }
    return new JsonResponse(['data' => $result, 'method' => 'GET', 'status' => 200]);
  }
}
