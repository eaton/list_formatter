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
 *   id = "entityreference",
 *   module = "entityreference",
 *   settings = {
 *     "entityreference_link" = "1"
 *   }
 * )
 */
class EntityReferenceList implements ListFormatterListInterface {

  /**
   * Implements \Drupal\list_formatter\Plugin\ListFormatterListInterface::createList().
   */
  public function createList($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
    // Load the target type for the field instance.
    $target_type = $field['settings']['target_type'];
    $contrib_settings = $display['settings']['list_formatter_contrib'];
    $list_items = $target_ids = $target_entities = array();

    // Get an array of entity ids.
    foreach ($items as $delta => $item) {
      $target_ids[] = $item['target_id'];
    }

    // Load them all.
    if ($target_ids) {
      $target_entities = entity_load($target_type, $target_ids);
    }

    // Create a list item for each entity.
    foreach ($target_entities as $id => $entity) {
      // Only add entities to the list that the user will have access to.
      if (isset($item['target_id']) && entity_access('view', $target_type, $entity)) {
        $label = entity_label($target_type, $entity);
        if ($contrib_settings['entityreference_link']) {
          $uri = entity_uri($target_type, $entity);
          $target_type_class = drupal_html_class($target_type);
          $classes = array($target_type_class, $target_type_class . '-' . $id, 'entityreference');
          $list_items[$id] = l($label, $uri['path'], array('attributes' => array('class' => $classes)));
        }
        else {
          $list_items[$id] = field_filter_xss($label);
        }
      }
    }

    return $list_items;
  }

  /**
   * @todo.
   */
  public function additionalSettings(&$form, &$form_state, $context) {
    if ($context['field']['type'] == 'entityreference') {
      $form['list_formatter_contrib']['entityreference_link'] = array(
        '#type' => 'checkbox',
        '#title' => t("Link list items to their @entity entity.", array('@entity' => $field['settings']['target_type'])),
        '#description' => t("Generate item list with links to the node page"),
        '#default_value' => isset($settings['list_formatter_contrib']['entityreference_link']) ? $settings['list_formatter_contrib']['entityreference_link'] : TRUE,
      );
    }
  }

}
