<?php

/**
 * @file
 * Definition of Drupal\textformatter\Tests\TestBase.
 */

namespace Drupal\textformatter\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Test the rendered output of list fields.
 */
abstract class TestBase extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('textformatter');

  protected function setUp() {
    parent::setUp();

    $this->admin_user = $this->drupalCreateUser(array('bypass node access'));

    $this->field_name = drupal_strtolower($this->randomName() . '_field_name');
    $this->field = array('field_name' => $this->field_name, 'type' => 'text', 'cardinality' => -1);
    $this->field = field_create_field($this->field);

    $this->field_id = $this->field['id'];

    $this->instance = array(
      'field_name' => $this->field_name,
      'entity_type' => 'node',
      'bundle' => 'page',
      'label' => $this->randomName() . '_label',
      'description' => $this->randomName() . '_description',
      'weight' => mt_rand(0, 127),
      'settings' => array(
        'max_length' => 255,
      ),
      'widget' => array(
        'type' => 'text_textfield',
        'label' => 'Test Field',
      ),
      'display' => array(
        'default' => array(
          'label' => 'above',
          'module' => 'textformatter',
          'settings' => array(
            'class' => 'textformatter-list',
            'comma_and' => 0,
            'comma_full_stop' => 0,
            'comma_override' => 0,
            'comma_tag' => 'div',
            'contrib' => array(),
            'separator_custom' => '',
            'separator_custom_class' => 'textformatter-separator',
            'separator_custom_tag' => 'span',
            'term_plain' => 0,
            'type' => 'ul',
          ),
          'type' => 'list_formatter',
          'weight' => '10',
        ),
      ),
    );
    field_create_instance($this->instance);
  }

}
