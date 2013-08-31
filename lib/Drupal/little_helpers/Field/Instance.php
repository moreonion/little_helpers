<?php

namespace Drupal\little_helpers\Field;

use \Drupal\little_helpers\Interfaces;

class Instance {
  public $id = NULL;
  public $field;
  // @TODO: replace these with a bundle class
  public $bundle;
  
  public $settings = array();
  public $display = array('default' => array());
  public $widget = array();
  public $required = FALSE;
  public $label = NULL;
  public $description = '';
  public $deleted = 0;
  
  public function __construct($data) {
    foreach ($data as $k => $v) {
      $this->$k = $v;
    }
    if (isset($data['field_name']) && !isset($this->field)) {
      $this->field = Field::byName($data['field_name']);
    }
    if (isset($this->field)) {
      $this->setField($this->field);
    }
  }
  
  public static function load($field_name, $entity_type, $bundle) {
    $data = \field_read_instance($entity_type, $field_name, $bundle);
    $class = \get_called_class();
    return new $class($data);
  }
  
  public static function fromField(Field $field, Bundle $bundle = NULL) {
    $data = array();
    $class = \get_called_class();
    $instance = new $class($data);
    $instance->setField($field);
    $instance->bundle = $bundle;
    return $instance;
  }
  
  /**
   * Set field and update default values accordingly.
   *
   * @see _field_write_instance().
   */
  public function setField(Field $field) {
    $this->field = $field;
    $this->settings += \field_info_instance_settings($field->type);
    $field_type = \field_info_field_types($field->type);
    if (!isset($this->widget['type'])) {
      $this->setWidgetType($field_type['default_widget']);
    }
    foreach ($this->display as $view_mode => &$settings) {
      if (!isset($settings['type'])) {
        $this->setFormatter($view_mode, isset($field_type['default_formatter']) ? $field_type['default_formatter'] : 'hidden');
      }
    }
  }
  
  /**
   * Set formatter type and update defaults accordingly.
   *
   * @see _field_write_instance().
   */
  public function setFormatter($view_mode, $formatter_name) {
    $display = &$this->display[$view_mode];
    if (!$display)
      $display = array();
    $display += array(
      'label' => 'above',
      'type' => $formatter_name,
      'settings' => array(),
    );
    if ($formatter_name != 'hidden') {
      $formatter_type = \field_info_formatter_types($display['type']);
      $display['module'] = $formatter_type['module'];
      $display['settings'] += \field_info_formatter_settings($display['type']);
    }
  }
  
  /**
   * Set widget type and update defaults accordingly.
   *
   * @see _field_write_instance().
   */
  public function setWidgetType($widget_type_name) {
    $this->widget['type'] = $widget_type_name;
    $this->widget['settings'] = array();
    $widget_type = \field_info_widget_types($widget_type_name);
    $this->widget['module'] = $widget_type['module'];
    $this->widget['settings'] += \field_info_widget_settings($widget_type_name);
  }
  
  /**
   * Save field instance to database.
   * 
   * @see \field_update_instance().
   * @see \field_create_instance().
   */
  public function save() {
    $data = (array) $this;
    unset($data['field']);
    $data['field_name'] = $this->field->field_name;
    $data['field_id'] = $this->field->id;
    unset($data['bundle']);
    $data['bundle'] = $this->bundle->getBundleName();
    $data['entity_type'] = $this->bundle->getEntityType();

    if (isset($this->id)) {
      \field_update_instance($data);
    } else {
      foreach (\field_create_instance($data) as $k => $v) {
        $this->$k = $v;
      }
    }
    return $this;
  }

  /**
   * Delete an existing field instance.
   *
   * @see \field_delete_instance().
   */
  public function delete() {
    $instance = array(
      'field_name' => $this->field->field_name,
      'entity_type' => $this->bundle->getEntityType(),
      'bundle' => $this->bundle->getBundleName(),
    );
    \field_delete_instance($instance);
  }
}
