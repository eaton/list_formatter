<?php

/**
 * @file
 */

namespace Drupal\list_formatter\Plugin\list_formatter\type;

use Drupal\Core\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;
use Drupal\list_formatter\Plugin\ListFormatterListInterface;

/**
 * Plugin implementation of the taxonomy module.
 *
 * @Plugin(
 *   id = "number",
 *   module = "number",
 *   field_types = {"number_integer", "number_decimal", "number_float"}
 * )
 */
class NumberList implements ListFormatterListInterface {

  /**
   * @todo.
   */
  public function createList($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
      $list_items = array();

      // Use our helper function to get the value key dynamically.
      $value_key = _list_formatter_get_field_value_key($this->field);

      foreach ($items as $delta => $item) {
        $list_items[$delta] = check_plain($item[$value_key]);
      }

      return $list_items;
  }

  /**
   * @todo.
   */
  public function additionalSettings(&$form, &$form_state, $context){
  }

}
