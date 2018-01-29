<?php

namespace Drupal\custom\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller routines for tablesort example routes.
 */
class CustomController extends ControllerBase {

  /**
   * The Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * TableSortExampleController constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * A simple controller method to explain what the tablesort example is about.
   */
  public function description() {
    // We are going to output the results in a table with a nice header.
    $header = [
      // The header gives the table the information it needs in order to make
      // the query calls for ordering. TableSort uses the field information
      // to know what database column to sort by.
      ['data' => t('Name'), 'field' => 't.name'],
      ['data' => t('Email'), 'field' => 't.email'],
      ['data' => t('Gender'), 'field' => 't.gender'],
      ['data' => t('Country'), 'field' => 't.gender'],
    ];

    // Using the TableSort Extender is what tells  the query object that we
    // are sorting.
    $query = $this->database->select('custom', 't')
      ->extend('Drupal\Core\Database\Query\TableSortExtender');
    $query->fields('t');

    // Don't forget to tell the query object how to find the header information.
    $result = $query
      ->orderByHeader($header)
      ->execute();

    $rows = [];
    $vid = 'country';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
   
    foreach ($result as $row) {
      // Normally we would add some nice formatting to our rows
      // but for our purpose we are simply going to add our row
      // to the array.
      foreach ($terms as $term) {
        if($term->tid == $row->country){
          $country_name = $term->name;
        }
      }
      $row->country = $country_name;       
      $rows[] = ['data' => (array) $row];
    }

    // Build the table for the nice output.
    $build = [
      '#markup' => '<p>' . t('The layout here is a themed as a table
           that is sortable by clicking the header name.') . '</p>',
    ];
    $build['tablesort_table'] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];

    return $build;
  }

}

