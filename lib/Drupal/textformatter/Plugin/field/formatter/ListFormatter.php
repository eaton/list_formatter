<?php

/**
 * @file
 * Definition of Drupal\textformatter\Plugin\field\formatter\List;
 */

namespace Drupal\textformatter\Plugin\field\formatter;

use Drupal\Core\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\EntityInterface;
use Drupal\field\Plugin\Type\Formatter\FormatterBase;

/**
 * Plugin implementation of the 'text_default' formatter.
 *
 * @Plugin(
 *   id = "list_formatter",
 *   module = "textformatter",
 *   label = @Translation("List"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *     "taxonomy_term_reference"
 *   },
 *   settings = {
 *     "type" = "ul",
 *     "class" = "textformatter-list",
 *     "comma_full_stop" = 0,
 *     "comma_and" = 0,
 *     "comma_tag" = "div",
 *     "term_plain" = 0,
 *     "comma_override" = 0,
 *     "separator_custom" = "",
 *     "separator_custom_tag" = "span",
 *     "separator_custom_class" = "textformatter-separator",
 *     "contrib" = ""
 *    }
 * )
 */
class ListFormatter extends FormatterBase {

  /**
   * Implements Drupal\field\Plugin\Type\Formatter\FormatterInterface::settingsForm().
   */
  public function settingsForm(array $form, array &$form_state) {
    $field = $this->field;

    $elements['type'] = array(
      '#title' => t("List type"),
      '#type' => 'select',
      '#options' => array(
        'ul' => t("Unordered HTML list (ul)"),
        'ol' => t("Ordered HTML list (ol)"),
        'comma' => t("Comma separated list"),
      ),
      '#default_value' => $this->getSetting('type'),
      '#required' => TRUE,
    );
    $elements['comma_and'] = array(
      '#type' => 'checkbox',
      '#title' => t("Include 'and' before the last item"),
      '#default_value' => $this->getSetting('comma_and'),
      '#states' => array(
        'visible' => array(
          ':input[name="fields[' . $field['field_name'] . '][settings_edit_form][settings][type]"]' => array('value' => 'comma'),
          ':input[name="fields[' . $field['field_name'] . '][settings_edit_form][settings][comma_override]"]' => array('checked' => FALSE),
        ),
      ),
    );
    $elements['comma_full_stop'] = array(
      '#type' => 'checkbox',
      '#title' => t("Append comma separated list with '.'"),
      '#default_value' => $this->getSetting('comma_full_stop'),
      '#states' => array(
        'visible' => array(
          ':input[name="fields[' . $field['field_name'] . '][settings_edit_form][settings][type]"]' => array('value' => 'comma'),
          ':input[name="fields[' . $field['field_name'] . '][settings_edit_form][settings][comma_override]"]' => array('checked' => FALSE),
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
          ':input[name="fields[' . $field['field_name'] . '][settings_edit_form][settings][type]"]' => array('value' => 'comma'),
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
          ':input[name="fields[' . $field['field_name'] . '][settings_edit_form][settings][comma_override]"]' => array('checked' => TRUE),
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
          ':input[name="fields[' . $field['field_name'] . '][settings_edit_form][settings][comma_override]"]' => array('checked' => TRUE),
        ),
      ),
    );
    $elements['separator_custom_class'] = array(
      '#title' => t("Separator classes"),
      '#type' => 'textfield',
      '#description' => t("A CSS class to use in the wrapper tag for the separator."),
      '#default_value' => $this->getSetting('separator_custom_class'),
      '#element_validate' => array('_textformatter_validate_class'),
      '#states' => array(
        'visible' => array(
          ':input[name="fields[' . $field['field_name'] . '][settings_edit_form][settings][comma_override]"]' => array('checked' => TRUE),
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
          ':input[name="fields[' . $field['field_name'] . '][settings_edit_form][settings][type]"]' => array('value' => 'comma'),
        ),
      ),
    );
    $elements['class'] = array(
      '#title' => t("List classes"),
      '#type' => 'textfield',
      '#size' => 40,
      '#description' => t("A CSS class to use in the markup for the field list."),
      '#default_value' => $this->getSetting('class'),
      '#required' => FALSE,
      '#element_validate' => array('_textformatter_validate_class'),
    );

    // Taxonomy term ref fields only.
    if ($this->field['type'] == 'taxonomy_term_reference') {
      $elements['textformatter_term_plain'] = array(
        '#type' => 'checkbox',
        '#title' => t("Display taxonomy terms as plain text (Not term links)."),
        '#default_value' => $settings['textformatter_term_plain'],
      );
    }

    // @todo
    // $context = array(
    //   'field' => $field,
    //   'instance' => $instance,
    //   'view_mode' => $view_mode
    // );
    // drupal_alter('textformatter_field_formatter_settings_form', $form, $form_state, $context);

    return $elements;
  }

  /**
   * Implements Drupal\field\Plugin\Type\Formatter\FormatterInterface::settingsForm().
   */
  public function settingsSummary() {
    $summary = array();

    switch ($this->getSetting('type')) {
      case 'ul':
        $summary[] = t("Unordered HTML list");
        break;
      case 'ol':
        $summary[] = t("Ordered HTML list");
        break;
      case 'comma':
        $summary[] = t("Comma separated list");
        break;
    }

    if ($this->getSetting('class')) {
      $summary[] = t("CSS Class") . ': <em>' . check_plain($this->getSetting('class')) . '</em>';
    }

    if ($this->getSetting('comma_override')) {
      $summary[] = '<em>*' . t("Comma separator overridden") . '*</em>';
    }

    return theme('item_list', array('type' => 'ul', 'items' => $summary));
  }

  /**
   * Implements Drupal\field\Plugin\Type\Formatter\FormatterInterface::viewElements().
   */
  public function viewElements(EntityInterface $entity, $langcode, array $items) {
    //$textformatters = textformatter_field_list_info();
    $elements = $list_items = array();

    if (isset($textformatters[$module]) && in_array($field['type'], $textformatters[$module]['fields'])) {
      $function = $textformatters[$module]['callback'];
      if (function_exists($function)) {
        $list_items = $function($entity_type, $entity, $field, $instance, $langcode, $items, $display);
      }
    }
    else {
      foreach ($items as $delta => $item) {
        $list_items = textformatter_default_field_create_list($entity_type, $entity, $field, $instance, $langcode, $items, $display);
      }
    }

    // If there are no list items, return and render nothing.
    if (empty($list_items)) {
      return;
    }

    $type = $this->getSetting('type');

    // CSS classes are checked for validity on submission. drupal_attributes()
    // runs each attribute value through check_plain().
    $classes = explode(' ', $this->getSetting('textformatter_class'));

    switch ($type) {
      case 'ul':
      case 'ol':
        // Render elements as one piece of markup and theme as item list.
        $elements[0] = array(
          '#theme' => 'item_list',
          '#type' => $type,
          '#items' => $list_items,
          '#attributes' => array(
            'class' => $classes,
          ),
        );
      break;
      case 'comma':
        // Render as one element as comma separated list.
        $elements[0] = array(
          '#theme' => 'textformatter_comma',
          '#items' => $list_items,
          '#settings' => $settings,
          '#attributes' => array(
            'class' => $classes,
          ),
        );
      break;
    }

    return $elements;
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
