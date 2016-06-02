<?php

/**
 * @file
 * Contains \Drupal\list_formatter\Plugin\ListFormatterListInterface.
 */

namespace Drupal\list_formatter\Plugin;

interface ListFormatterListInterface {

  /**
   * [createList description]
   *
   * @param  [type] $entity_type
   * @param  [type] $entity
   * @param  [type] $field
   * @param  [type] $instance
   * @param  [type] $langcode
   * @param  [type] $items
   * @param  [type] $display
   *
   * @return array
   */
  public function createList($entity_type, $entity, $field, $instance, $langcode, $items, $display);

  /**
   * [additionalSettings description]
   *
   * @param  [type] $form
   * @param  [type] $form_state
   * @param  [type] $context
   */
  public function additionalSettings(&$elements, $field, $instance, $formatter);

}
