<?php

/**
 * @file
 * Definition of Drupal\list_formatter\Tests\OutputTest.
 */

namespace Drupal\list_formatter\Tests;

/**
 * Test the rendered output of list fields.
 */
class OutputTest extends TestBase {

  public static function getInfo() {
    return array(
      'name' => 'Test list output',
      'description' => 'Tests the output markup of list_formatter list formatters.',
      'group' => 'List formatter',
    );
  }

  /**
   * Test the general output of the display formatter.
   */
  public function testOutput() {
    $this->drupalLogin($this->adminUser);

    $field_values = array(LANGUAGE_NOT_SPECIFIED => array());
    for ($i = 0; $i < 10; $i++) {
      $field_values[LANGUAGE_NOT_SPECIFIED][] = array('value' => $this->randomName());
    }

    $node = $this->drupalCreateNode(array($this->fieldName => $field_values, 'type' => $this->contentType->type));
    $this->verbose('Node: ' . var_export($node, TRUE));

    $page = $this->drupalGet('node/' . $node->nid);

    $this->verbose('Page: ' . $page);

    $this->drupalSetContent($page);
    $this->assertResponse(200);

    foreach ($field_values[LANGUAGE_NOT_SPECIFIED] as $delta => $item) {
      $this->assertText($item['value'], t('Field value !delta output on node.', array('!delta' => $delta)));
    }

    $items = array();
    foreach ($field_values[LANGUAGE_NOT_SPECIFIED] as $item) {
      $items[] = $item['value'];
    }

    // Test the default ul list.
    $options = array(
      'type' => 'ul',
      'items' => $items,
      'attributes' => array(
        'class' => array('list_formatter-list'),
      ),
    );
    $expected = theme('item_list', $options);

    $this->assertRaw($expected, 'The expected unordered list markup was produced.');

    // Update the field settings for ol list.
    $field_instance = field_info_instance('node', $this->fieldName, $node->type);
    $field_instance['display']['default']['settings']['type'] = 'ol';
    field_update_instance($field_instance);

    // Get the node page again.
    $this->drupalGet('node/' . $node->nid);

    // Test the default ol list.
    $options['type'] = 'ol';
    $expected = theme('item_list', $options);

    $this->assertRaw($expected, 'The expected ordered list markup was produced.');

    // Update the field settings for comma list.
    $field_instance['display']['default']['settings']['type'] = 'comma';
    field_update_instance($field_instance);

    // Get the node page again.
    $this->drupalGet('node/' . $node->nid);

    // Test the default comma list.
    unset($options['type']);
    // Get the field formatter plugin to pass into the theme function.
    $options['formatter'] = $field_instance->getFormatter('default');
    $expected = theme('list_formatter_comma', $options);

    $this->assertRaw($expected, 'The expected comma list markup was produced.');
  }

}
