<?php

class Model_Species extends ORM {

  protected $_belongs_to = array(
    'user' => array()
  );

  public function formo() {
    $array = array(
      'id' => array('render' => FALSE)
    );
    foreach (self::fields() as $field => $label) {
      $array[$field]['label'] = $label;
    }
    return $array;
  }

  public static function fields() {
    return array(
      'code'         => 'Code',
      'class'        => 'Class',
      'botanic_name' => 'Botanic Name',
      'trade_name'   => 'Trade Name',
      'fob_price'    => 'FOB Price'
    );
  }

}
