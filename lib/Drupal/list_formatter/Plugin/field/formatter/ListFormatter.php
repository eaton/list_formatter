<?php

/**
 * @file
 * Definition of Drupal\list_formatter\Plugin\field\formatter\List;
 */

namespace Drupal\list_formatter\Plugin\field\formatter;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\EntityInterface;
use Drupal\field\Plugin\Type\Formatter\FormatterBase;
use Drupal\list_formatter\Plugin\ListFormatterPluginManager;

/**
 * Plugin implementation of the 'text_default' formatter.
 *
 * @Plugin(
 *   id = "list_formatter",
 *   module = "list_formatter",
 *   label = @Translation("List"),
 *   field_types = "",
 *   settings = {
 *     "type" = "ul",
 *     "class" = "list-formatter-list",
 *     "comma_full_stop" = 0,
 *     "comma_and" = 0,
 *     "comma_tag" = "div",
 *     "term_plain" = 0,
 *     "comma_override" = 0,
 *     "separator_custom" = "",
 *     "separator_custom_tag" = "span",
 *     "separator_custom_class" = "list-formatter-separator",
 *     "contrib" = {""}
 *    }
 * )
 */
class ListFormatter extends FormatterBase {

  /**
   * Implements Drupal\field\Plugin\Type\Formatter\FormatterInterface::settingsForm().
   */
  public function settingsForm(array $form, array &$form_state) {
    $field_name = $this->field['field_name'];

    $elements['type'] = array(
      '#title' => t("List type"),
      '#type' => 'select',
      '#options' => $this->listTypes(),
      '#default_value' => $this->getSetting('type'),
      '#required' => TRUE,
    );
    $elements['comma_and'] = array(
      '#type' => 'checkbox',
      '#title' => t("Include 'and' before the last item"),
      '#default_value' => $this->getSetting('comma_and'),
      '#states' => array(
        'visible' => array(
          ':input[name="fields[' . $field_name . '][settings_edit_form][settings][type]"]' => array('value' => 'comma'),
        ),
      ),
    );
    $elements['comma_full_stop'] = array(
      '#type' => 'checkbox',
      '#title' => t("Append comma separated list with '.'"),
      '#default_value' => $this->getSetting('comma_full_stop'),
      '#states' => array(
        'visible' => array(
          ':input[name="fields[' . $field_name . '][settings_edit_form][settings][type]"]' => array('value' => 'comma'),
        ),
      ),
    );

    //Override Comma with custom separator.
    $elements['comma_override'] = array(
      '#type' => 'checkbox',
      '#title' => t("Override comma separator"),
      '#description' => t("Override the default comma separator with a custom separator string."),
      '#default_value' => $this->getSetting('comma_override'),
      '#states' => array(
        'visible' => array(
          ':input[name="fields[' . $field_name . '][settings_edit_form][settings][type]"]' => array('value' => 'comma'),
        ),
      ),
    );
    $elements['separator_custom'] = array(
      '#type' => 'textfield',
      '#title' => t("Custom separator"),
      '#description' => t("Override default comma separator with a custom separator string. You must add your own spaces in this string if you want them. @example", array('@example' => "E.g. ' + ', or ' => '")),
      '#size' => 40,
      '#default_value' => $this->getSetting('separator_custom'),
      '#states' => array(
        'visible' => array(
          ':input[name="fields[' . $field_name . '][settings_edit_form][settings][comma_override]"]' => array('checked' => TRUE),
        ),
      ),
    );
    $elements['separator_custom_tag'] = array(
      '#type' => 'select',
      '#title' => t("separator HTML wrapper"),
      '#description' => t("An HTML tag to wrap the separator in."),
      '#options' => $this->wrapperOptions(),
      '#default_value' => $this->getSetting('separator_custom_tag'),
      '#states' => array(
        'visible' => array(
          ':input[name="fields[' . $field_name . '][settings_edit_form][settings][comma_override]"]' => array('checked' => TRUE),
        ),
      ),
    );
    $elements['separator_custom_class'] = array(
      '#title' => t("Separator classes"),
      '#type' => 'textfield',
      '#description' => t("A CSS class to use in the wrapper tag for the separator."),
      '#default_value' => $this->getSetting('separator_custom_class'),
      '#element_validate' => array('_list_formatter_validate_class'),
      '#states' => array(
        'visible' => array(
          ':input[name="fields[' . $field_name . '][settings_edit_form][settings][comma_override]"]' => array('checked' => TRUE),
        ),
      ),
    );

    $elements['comma_tag'] = array(
      '#type' => 'select',
      '#title' => t("HTML wrapper"),
      '#description' => t("An HTML tag to wrap the list in. The CSS class below will be added to this tag."),
      '#options' => $this->wrapperOptions(),
      '#default_value' => $this->getSetting('comma_tag'),
      '#states' => array(
        'visible' => array(
          ':input[name="fields[' . $field_name . '][settings_edit_form][settings][type]"]' => array('value' => 'comma'),
        ),
      ),
    );
    $elements['class'] = array(
      '#title' => t("List classes"),
      '#type' => 'textfield',
      '#size' => 40,
      '#description' => t("A CSS class to use in the markup for the field list."),
      '#default_value' => $this->getSetting('class'),
      '#element_validate' => array('_list_formatter_validate_class'),
    );

    $manager = \Drupal::service('plugin.manager.list_formatter.type');
    foreach ($manager->getDefinitions() as $id => $definition) {
      $manager->createInstance($id)->additionalSettings($elements, $this->field, $this->instance, $this);
    }

    return $elements;
  }

  /**
   * Implements Drupal\field\Plugin\Type\Formatter\FormatterInterface::settingsSummary().
   */
  public function settingsSummary() {
    $summary = array();

    $types = $this->listTypes();
    $summary[] = $types[$this->getSetting('type')];

    if ($this->getSetting('class')) {
      $summary[] = t("CSS Class") . ': <em>' . check_plain($this->getSetting('class')) . '</em>';
    }

    if ($this->getSetting('comma_override')) {
      $summary[] = '<em>*' . t("Comma separator overridden") . '*</em>';
    }

    return $summary;
  }

  /**
   * Implements Drupal\field\Plugin\Type\Formatter\FormatterInterface::viewElements().
   */
  public function viewElements(EntityInterface $entity, $langcode, array $items) {
    $module = $this->field['module'];
    $field_type = $this->field['type'];
    $list_formatter_info = $this->fieldListInfo(TRUE);
    $elements = $list_items = array();
    $manager = \Drupal::service('plugin.manager.list_formatter.type');

    if (in_array($field_type, $list_formatter_info['field_types'][$module])) {
      if ($plugin = $manager->createInstance($module)) {
        // Support existing function implementations.
        $display = array(
          'type' => $this->getPluginId(),
          'settings' => $this->getSettings(),
          'label' => $this->label,
        );
        $list_items = $plugin->createList($entity->entityType(), $entity, $this->field, $this->instance, $langcode, $items, $display);
      }
    }
    else {
      $plugin = $manager->createInstance('default');
      foreach ($items as $delta => $item) {
        $list_items = $plugin->createList($entity->entityType(), $entity, $this->field, $this->instance, $langcode, $items, $display);
      }
    }

    // If there are no list items, return and render nothing.
    if (empty($list_items)) {
      return;
    }

    $type = $this->getSetting('type');

    // CSS classes are checked for validity on submission. drupal_attributes()
    // runs each attribute value through check_plain().
    $classes = explode(' ', $this->getSetting('class'));

    switch ($type) {
      case 'ul':
      case 'ol':
        // Render as one element, item list.
        $elements[] = array(
          '#theme' => 'item_list',
          '#type' => $type,
          '#items' => $list_items,
          '#attributes' => array(
            'class' => $classes,
          ),
        );
      break;
      case 'comma':
        // Render as one element, comma separated list.
        $elements[] = array(
          '#theme' => 'list_formatter_comma',
          '#items' => $list_items,
          '#formatter' => $this,
          '#attributes' => array(
            'class' => $classes,
          ),
        );
      break;
    }

    return $elements;
  }

  /**
   * Returns an array of info to add to hook_field_formatter_info_alter().
   *
   * This iterates through each item returned from fieldListInfo.
   *
   * @param bool $module_key
   *
   * @return array
   *   An array of fields and settings from hook_list_formatter_field_info data
   *   implementations. Containing an aggregated array from all items.
   */
  static public function fieldListInfo($module_key = FALSE) {
    $manager = \Drupal::service('plugin.manager.list_formatter.type');
    $field_info = array('field_types' => array(), 'settings' => array());

    // Create array of all field types and default settings.
    foreach ($manager->getDefinitions() as $id => $definition) {
      $field_types = array();

      if ($module_key) {
        // @todo Add the module and key by plugin id, so they can be independent.
        $module = $definition['module'];
        // Add field types by module.
        foreach ($definition['field_types'] as $type) {
          $field_types[$module][] = $type;
        }
      }
      // Otherwise just merge this, as is. Don't need mergeDeep here.
      else {
        $field_types = array_merge($field_types, $definition['field_types']);
      }

      $field_info['field_types'] = NestedArray::mergeDeep($field_info['field_types'], $field_types);
      $field_info['settings'] = NestedArray::MergeDeep($field_info['settings'], $definition['settings']);
    }

    return $field_info;
  }

  /**
   * Returns a list of available list types.
   *
   * @return array
   *   An options list of types.
   */
  public function listTypes() {
    return array(
      'ul' => t("Unordered HTML list (ul)"),
      'ol' => t("Ordered HTML list (ol)"),
      'comma' => t("Comma separated list"),
    );
  }

  /**
   * Helper method return an array of html tags; formatted for a select list.
   *
   * @return array
   *   A keyed array of available html tags.
   */
  public function wrapperOptions() {
    return array(
      t('No HTML tag'),
      'div' => t('Div'),
      'span' => t('Span'),
      'p' => t('Paragraph'),
      'h1' => t('Header 1'),
      'h2' => t('Header 2'),
      'h3' => t('Header 3'),
      'h4' => t('Header 4'),
      'h5' => t('Header 5'),
      'h6' => t('Header 6'),
    );
  }

}
