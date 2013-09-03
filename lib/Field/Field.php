<?php

namespace Drupal\little_helpers\Field;

/**
 * OOP-wrapper for the data-structure used by field_*_field() functions.
 */
class Field {
  public $id = NULL;
  public $entity_types = array();
  public $field_name;
  public $type;
  public $module;
  public $active = 1;
  public $storage = array();
  public $locked = FALSE;
  public $cardinality = 1;
  public $translatable = FALSE;
  public $deleted = 0;
  public $settings = array();

  public static function byName($name) {
    $class = \get_called_class();
    return new $class(\field_read_field($name));
  }

  public static function fromType($type, $name = NULL) {
    $class = \get_called_class();
    $new = new $class(array('field_name' => $name));
    $new->setType($type);
    return $new;
  }

  public function __construct($data) {
    foreach ($data as $k => $v) {
      $this->$k = $v;
    }
  }

  /**
   * Load default data for this field-type.
   * 
   * @see \field_create_field().
   */
  public function setType($type) {
    $field_type = \field_info_field_types($type);
    $this->settings += \field_info_field_settings($type);
    $this->module = $field_type['module'];
    $this->type = $type;
  }

  /**
   * Save field configuration to database.
   * 
   * @see \field_update_field().
   * @see \field_create_field().
   */
  public function save() {
    if (isset($this->id)) {
      \field_update_field((array) $this);
    } else {
      foreach (\field_create_field((array) $this) as $k => $v) {
        $this->$k = $v;
      }
    }
    return $this;
  }

  /**
   * Delete an existing field.
   *
   * @see \field_delete_field().
   */
  public function delete() {
    field_delete_field($this->field_name);
  }
}
