<?php

/**
 * @file
 * Contains ....
 */

namespace Drupal\list_formatter\Plugin\list_formatter\type;

use Drupal\Core\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;
use Drupal\list_formatter\Plugin\ListFormatterListInterface;

/**
 * Default list implementation plugin.
 *
 * @Plugin(
 *   id = "default",
 *   module = "list_formatter"
 * )
 */
class DefaultList implements ListFormatterListInterface {

  /**
   * Implements \Drupal\list_formatter\Plugin\ListFormatterListInterface::createList().
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

}
