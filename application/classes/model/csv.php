<?php

class Model_CSV extends ORM {

  protected $_table_name = 'csv';

  protected $_belongs_to = array(
    'file'     => array(),
    'operator' => array(),
    'site'     => array(),
    'block'    => array(),
    'user'     => array()
  );

  protected function _initialize() {
    parent::_initialize();
    $this->_object_plural = 'csv';
  }

  public function set($column, $value) {
    switch ($column) {
      case 'values':
        if (is_array($value)) {
          $this->operator_id = ($operator_id = SGS::lookup_operator($value['operator_tin'], TRUE)) ? $operator_id : NULL;
          $this->site_id     = ($site_id     = SGS::lookup_site($value['site_name'], TRUE)) ? $site_id : NULL;
          $this->block_id    = ($block_id    = SGS::lookup_block($value['site_name'], $value['block_name'], TRUE)) ? $block_id : NULL;
        }
      case 'errors':
      case 'suggestions':
      case 'duplicates':
        if ($value) $value = is_string($value) ? $value : serialize($value);
        else $value = NULL;
      default:
        parent::set($column, $value);
    }
  }

  public function __get($column) {
    switch ($column) {
      case 'values':
      case 'errors':
      case 'suggestions':
      case 'duplicates':
        $value = parent::__get($column);
        return is_string($value) ? unserialize($value) : $value;
      default:
        return parent::__get($column);
    }
  }

//  public function save(Validation $validation = NULL) {
//    if ($this->form_data_id and $this->_original_values['form_data_id']) Notify::msg('Imported data accepted as form data cannot be saved. You must first delete the related form data.', 'error', TRUE);
//    else parent::save($validation);
//  }
//
//  public function update(Validation $validation = NULL) {
//    if ($this->form_data_id and $this->_original_values['form_data_id']) Notify::msg('Imported data accepted as form data cannot be updated. You must first delete the related form data.', 'error', TRUE);
//    else parent::update($validation);
//  }
//
//  public function create(Validation $validation = NULL) {
//    if ($this->form_data_id and $this->_original_values['form_data_id']) Notify::msg('Imported data accepted as form data cannot be re-created. You must first delete the related form data.', 'error', TRUE);
//    else parent::create($validation);
//  }
//
//  public function delete() {
//    if ($this->form_data_id and $this->_original_values['form_data_id']) Notify::msg('Imported data accepted as form data cannot be deleted. You must first delete the related form data.', 'error');
//    else return parent::delete();
//  }

}
