<?php

/**
 * @file
 * Contains \Drupal\list_formatter\Plugin\ListFormatterPluginManager.
 */

namespace Drupal\list_formatter\Plugin;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Plugin type manager for all views plugins.
 */
class ListFormatterPluginManager extends DefaultPluginManager {

  /**
   * Constructs the FieldTypePluginManager object
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typed_data_manager
   *   The typed data manager.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/list_formatter', $namespaces, $module_handler, ListFormatterListInterface::class, 'Drupal\list_formatter\Annotation\ListFormatter');
    $this->alterInfo('field_info');
    $this->setCacheBackend($cache_backend, 'list_formatter_plugins');
  }

  /**
   * Returns an array of info to add to hook_field_formatter_info_alter().
   *
   * This iterates through each item returned from fieldListInfo.
   *
   * @return array
   *   An array of fields and settings from hook_list_formatter_field_info data
   *   implementations. Containing an aggregated array from all items.
   */
   public function fieldListInfo() {
    $field_info = [
      'field_types' => [],
      'settings' => [],
    ];

    $field_types = [];
    // Create array of all field types and default settings.
    foreach ($this->getDefinitions() as $id => $definition) {
      foreach ($definition['field_types'] as $type) {
        $field_info['field_types'][$type] = $definition['id'];
      }
      $field_info['settings'] = NestedArray::mergeDeep($field_info['settings'], $definition['settings']);
    }

    return $field_info;
  }

}
