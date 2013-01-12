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
 *   id = "taxonomy",
 *   module = "taxonomy",
 *   field_types = {"taxonomy_term_reference"}
 * )
 */
class TaxonomyList implements ListFormatterListInterface {

  /**
   * [fields description]
   * @return [type] [description]
   */
  public function fieldTypes() {
    return array('taxonomy_term_reference');
  }

  /**
   * @todo.
   */
  public function createList($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
    $settings = $display['settings'];
    $list_items = $tids = array();

    // Get an array of tids only.
    foreach ($items as $item) {
      $tids[] = $item['tid'];
    }

    $terms = taxonomy_term_load_multiple($tids);

    foreach ($items as $delta => $item) {
      // Check the term for this item has actually been loaded.
      // @see http://drupal.org/node/1281114
      if (empty($terms[$item['tid']])) {
        continue;
      }
      // Use the item name if autocreating, as there won't be a term object yet.
      $term_name = ($item['tid'] === 'autocreate') ? $item['name'] : $terms[$item['tid']]->name;
      // Check if we should display as term links or not.
      if ($settings['term_plain'] || ($item['tid'] === 'autocreate')) {
        $list_items[$delta] = check_plain($term_name);
      }
      else {
        $uri = $terms[$item['tid']]->uri();
        $list_items[$delta] = l($term_name, $uri['path']);
      }
    }

    return $list_items;
  }

  /**
   * @todo.
   */
  public function additionalSettings(&$form, &$form_state, $context);

}
