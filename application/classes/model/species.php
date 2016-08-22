<?php

class Model_Species extends ORM {

  const FOB_PRICE_CM = 80;

  protected $_belongs_to = array(
    'user' => array()
  );

  public function formo() {
    $array = array(
      'id'         => array('render' => FALSE),
      'is_deleted' => array('render' => FALSE),
      'fob_price'  => array('render' => FALSE)
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
      'fob_price'    => 'Old FOB Price',
      'fob_price_low'  => 'FOB Low Price',
      'fob_price_high' => 'FOB High Price',
      'min_diameter' => 'Minimum Diameter'
    );
  }

  public function get_fob_price($diameter = NULL) {
    if ($diameter and $diameter < self::FOB_PRICE_CM) {
      return $this->fob_price_low;
    } else return $this->fob_price_high;
  }
  
  public static function fob_price_sql() {
    $fob_price_cm = self::FOB_PRICE_CM;
    return "case when (fob_price is not null) and (diameter < $fob_price_cm) then fob_price_low else fob_price_high end";    
  }

}
