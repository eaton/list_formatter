<?php

/**
 * @file
 */

namespace Drupal\list_formatter\Plugin\list_formatter\type;

use Drupal\Core\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Plugin implementation of the taxonomy module.
 *
 * @Plugin(
 *   id = "number",
 *   module = "number",
 *   field_types = {"number_integer", "number_decimal", "number_float"}
 * )
 */
class NumberList extends DefaultList {

  /**
   * @todo.
   */
  public function additionalSettings(&$form, &$form_state, $context){
  }

}
