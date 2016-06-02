<?php

/**
 * @file
 * Contains \...
 */

namespace Drupal\list_formatter\Plugin\list_formatter\type;

use Drupal\Component\Annotation\Plugin;
use Drupal\list_formatter\Plugin\ListFormatterListInterface;

/**
 * Plugin implementation of the taxonomy module.
 *
 * @Plugin(
 *   id = "options",
 *   module = "options",
 *   field_types = {"list_boolean", "list_float", "list_integer", "list_text"}
 * )
 */
class OptionsList implements ListFormatterListInterface {

  /**
   * @todo.
   */
  public function createList($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
    $settings = $display['settings'];
    $list_items = array();

    // Get allowed values for the field.
    $allowed_values = options_allowed_values($field);
    foreach ($items as $delta => $item) {
      if (isset($allowed_values[$item['value']])) {
        $list_items[$delta] = field_filter_xss($allowed_values[$item['value']]);
      }
    }

    return $list_items;
  }

  /**
   * @todo.
   */
  public function additionalSettings(&$elements, $field, $instance, $formatter) {
  }

}
