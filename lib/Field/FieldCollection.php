<?php

namespace Drupal\little_helpers\Field;

class FieldCollection extends Field implements \Drupal\little_helpers\Interfaces\Bundle {
  public function getBundleName() { return $this->field_name; }
  public function getEntityType() { return 'field_collection'; }
}
