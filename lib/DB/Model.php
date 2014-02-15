<?php

namespace Drupal\little_helpers\DB;

abstract class Model {
  protected static $table = '';
  protected static $key = array();
  protected static $values = array();
  protected static $serial = TRUE;
  protected static $serialize = array();

  public function __construct($data = array()) {
    foreach ($data as $k => $v) {
      $this->$k = (is_string($v) && !empty(static::$serialize[$k])) ? unserialize($v) : $v;
    }
  }

  public function isNew() {
    foreach (static::$key as $key) {
      if (isset($this->{$key})) {
        return FALSE;
      }
    }
    return TRUE;
  }

  public function save() {
    $new = TRUE;
    if ($this->isNew()) {
      $this->insert();
    } else {
      $this->update();
    }
  }

  protected function update() {
    $stmt = db_update(static::$table);
    foreach (static::$key as $key) {
      $stmt->condition($key, $this->{$key});
    }
    $stmt->fields($this->values(static::$values))
      ->execute();
  }

  protected function insert() {
    $cols = static::$values;
    if (!static::$serial) {
      $cols = array_merge($cols, static::$key);
    }
    $ret = db_insert(static::$table)
      ->fields($this->values($cols))
      ->execute();
    if (static::$serial) {
      $this->{static::$key[0]} = $ret;
    }
  }

  protected function values($keys) {
    $data = array();
    foreach ($keys as $k) {
      $data[$k] = isset($this->{$k}) ? (empty(static::$serialize[$k]) ? $this->{$k} : serialize($this->{$k})) : NULL;
    }
    return $data;
  }
}
