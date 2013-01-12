<?php

/**
 * @file
 * Contains \Drupal\list_formatter\Plugin\ListFormatterPluginManager.
 */

namespace Drupal\list_formatter\Plugin;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Component\Plugin\Discovery\DerivativeDiscoveryDecorator;
use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Component\Plugin\Discovery\ProcessDecorator;
use Drupal\Core\Plugin\Discovery\AlterDecorator;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\Plugin\Discovery\CacheDecorator;

/**
 * Plugin type manager for all views plugins.
 */
class ListFormatterPluginManager extends PluginManagerBase {

  /**
   * Constructs a ListFormatterPluginManager object.
   */
  public function __construct() {
    $this->discovery = new AnnotatedClassDiscovery('list_formatter', 'type');
    $this->discovery = new ProcessDecorator($this->discovery, array($this, 'processDefinition'));
    $this->discovery = new AlterDecorator($this->discovery, 'list_formatter_list_plugins');
    $this->discovery = new CacheDecorator($this->discovery, 'list_formatter:list_plugins', 'cache');

    $this->factory = new DefaultFactory($this);

    $this->defaults += array(
      'module' => 'list_formatter',
      'field_types' => array(),
      'settings' => array(),
    );
  }

}
