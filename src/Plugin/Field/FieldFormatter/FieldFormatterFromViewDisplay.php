<?php

/**
 * @file
 * Contains \Drupal\field_formatter\Plugin\Field\FieldFormatter\FieldFormatter.
 */

namespace Drupal\field_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'link' formatter.
 *
 * @FieldFormatter(
 *   id = "field_formatter_from_view_display",
 *   label = @Translation("Field formatter"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class FieldFormatterFromViewDisplay extends FieldFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = [
      'view_display_id' => '',
      'field_name' => '',
    ];
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['view_display_id'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'entity_view_mode',
      '#default_value' => EntityViewMode::load($this->getSetting('view_display_id')),
    ];

    $entity_type_id = $this->fieldDefinition->getSetting('target_type');
    $bundle_id = $this->fieldDefinition->getTargetBundle();
    $field_names = array_map(function (FieldDefinitionInterface $field_definition) {
      return $field_definition->getLabel();
    }, \Drupal::entityManager()->getFieldDefinitions($entity_type_id, $bundle_id));

    $form['field_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Field name'),
      '#default_value' => $this->getSetting('field_name'),
      '#options' => $field_names,
    ];

    return $form;
  }

  protected function getViewDisplay() {
    if (!isset($this->viewDisplay)) {
      /** @var \Drupal\Core\Entity\FieldableEntityInterface $entity */
      $bundle_id = $entity->bundle();

      $field_name = $this->getSetting('field_name');
      // Odd that this is needed.
      list($entity_type_id, $view_mode) = explode('.', $this->getSetting('view_display_id'));
      if (($view_display_id = $this->getSetting('view_display_id')) && $view_display = EntityViewDisplay::load($entity_type_id . '.' . $bundle_id . '.' . $view_mode)) {
        $components = $view_display->getComponents();
        foreach ($components as $component_name => $component) {
          if ($component_name != $field_name) {
            $view_display->removeComponent($component_name);
            $this->viewDisplay = $view_display;
          }
        }
      }
    }
    return $this->viewDisplay;
  }

}